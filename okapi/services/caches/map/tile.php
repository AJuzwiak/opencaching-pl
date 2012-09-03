<?php

namespace okapi\services\caches\map\tile;

use Exception;
use okapi\Okapi;
use okapi\Settings;
use okapi\Cache;
use okapi\Db;
use okapi\OkapiRequest;
use okapi\OkapiHttpResponse;
use okapi\ParamMissing;
use okapi\InvalidParam;
use okapi\BadRequest;
use okapi\DoesNotExist;
use okapi\OkapiInternalRequest;
use okapi\OkapiInternalConsumer;
use okapi\OkapiServiceRunner;
use okapi\OkapiLock;

class WebService
{
	private static $FLAG_STAR = 0x01;
	
	public static function options()
	{
		return array(
			'min_auth_level' => 1
		);
	}
	
	private static function require_uint($request, $name, $min_value = 0)
	{
		$val = $request->get_parameter($name);
		if ($val === null)
			throw new ParamMissing($name);
		$ret = intval($val);
		if ($ret < 0 || ("$ret" !== $val))
			throw new InvalidParam($name, "Expecting non-negative integer.");
		return $ret;
	}
	
	public static function call(OkapiRequest $request)
	{
		# Make sure the request is internal.
		
		if (!in_array($request->consumer->key, array('internal', 'facade')))
			throw new BadRequest("Your Consumer Key has not been allowed to access this method.");
		
		# Import required tile-specific parameters.
		
		$x = self::require_uint($request, 'x');
		$y = self::require_uint($request, 'y');
		$zoom = self::require_uint($request, 'z');
		if ($zoom > 21)
			throw new InvalidParam('z', "Maximum value for this parameter is 21.");

		# Prepare the list of found caches (to be marked as found when drawing)
		
		$cache_key = "found/".$request->token->user_id;
		$found_cache_dict = Cache::get($cache_key);
		if ($found_cache_dict === null)
		{
			$rs = Db::query("
				select distinct cache_id
				from cache_logs
				where user_id = '".mysql_real_escape_string($request->token->user_id)."'
			");
			$found_cache_dict = array();
			while (list($cache_id) = mysql_fetch_row($rs))
				$found_cache_dict[$cache_id] = true;
			Cache::set($cache_key, $found_cache_dict, 60);
		}
		
		# Get caches within the tile (+ those around the borders). All filtering
		# options need to be applied here.
		
		$rs = self::query_fast($zoom, $x, $y);
		
		# Draw the image. WRTODO: Selective PNG cache.
		
		$response = new OkapiHttpResponse();
		$response->content_type = "image/png";
		$im = imagecreatetruecolor(256, 256);
		imagealphablending($im, false); 
		$transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
		imagefilledrectangle($im, 0, 0, 256, 256, $transparent);
		imagealphablending($im, true);
		while ($row = mysql_fetch_row($rs))
			self::draw_cache($im, $zoom, $row, $found_cache_dict);
		ob_start();
		imagesavealpha($im, true);
		imagepng($im);
		imagedestroy($im);
		$response->body = ob_get_clean();

		return $response;
	}
	
	/**
	 * Return null if not computed, 1 if computed and empty, 2 if computed and not empty.
	 */
	private static function get_tile_status($zoom, $x, $y)
	{
		return Db::select_value("
			select status
			from okapi_tile_status
			where
				z = '".mysql_real_escape_string($zoom)."'
				and x = '".mysql_real_escape_string($x)."'
				and y = '".mysql_real_escape_string($y)."'
		");
	}
	
	/**
	 * Return MySQL's result set iterator over all caches matched by your query.
	 *
	 * Each row is an array of the following format:
	 * list(cache_id, $pixel_x, $pixel_y, status, type, rating, flags, count).
	 *
	 * Note that $pixels can also be negative or >=256 (up to a margin of 32px).
	 * Count is the number of other caches "eclipsed" by this geocache (such
	 * eclipsed geocaches are not included in the result).
	 */
	private static function query_fast($zoom, $x, $y)
	{
		# First, we check if the cache-set for this tile was already computed
		# (and if it was, was it empty).
		
		$status = self::get_tile_status($zoom, $x, $y);
		if ($status === null)  # Not yet computed.
		{
			# Note, that computing the tile does not involve taking any
			# filtering parameters.
			
			$status = self::compute_tile($zoom, $x, $y);
		}
		if ($status === 1)  # Computed and empty.
		{
			# This tile was already computed and it is empty.
			return array();
		}
		
		# If we got here, then the tile is computed and not empty (status 2).
		# Since all search parameters are aggregated, we just need a simple
		# SQL query to get the filtered result.
		
		$tile_upper_x = $x << 8;
		$tile_leftmost_y = $y << 8;
		
		return Db::query("
			select
				cache_id, cast(z21x >> (21 - $zoom) as signed) - $tile_upper_x, cast(z21y >> (21 - $zoom) as signed) - $tile_leftmost_y,
				status, type, rating, flags, count(*)
			from okapi_tile_caches
			where
				z = '".mysql_real_escape_string($zoom)."'
				and x = '".mysql_real_escape_string($x)."'
				and y = '".mysql_real_escape_string($y)."'
			group by
				z21x >> (3 + (21 - $zoom)),
				z21y >> (3 + (21 - $zoom))
		");
	}
	
	/**
	 * Precache the ($zoom, $x, $y) slot in the okapi_tile_caches table.
	 */
	private static function compute_tile($zoom, $x, $y)
	{
		if ($zoom <= 2)
		{
			# Confirm the status is uncomputed (multiple processes may try to compute
			# tiles simulatanously. For low zoom levels we will protect this with
			# semaphores.
			
			$lock = OkapiLock::get("tile-computation-$zoom-$x-$y");
			$lock->acquire();
			$status = self::get_tile_status($zoom, $x, $y);
			if ($status !== null)
				return $status;
		}
		
		if ($zoom === 0)
		{
			# When computing zoom zero, we don't have a parent to speed up
			# the computation. We need to use the caches table. Note, that
			# zoom level 0 contains *entire world*, so we don't have to use
			# any WHERE condition in the following query.
			
			# This can be done a little faster (without the use of internal requests),
			# but there is *no need* to - this query is run seldom and is cached.
			
			$params = array();
			$params['status'] = "Available|Temporarily unavailable|Archived";  # we want them all
			$params['limit'] = "10000000";  # no limit
			
			$internal_request = new OkapiInternalRequest(new OkapiInternalConsumer(), null, $params);
			$internal_request->skip_limits = true;
			$response = OkapiServiceRunner::call("services/caches/search/all", $internal_request);
			$cache_codes = $response['results'];
			
			$internal_request = new OkapiInternalRequest(new OkapiInternalConsumer(), null, array(
				'cache_codes' => implode('|', $cache_codes),
				'fields' => 'internal_id|code|name|location|type|status|rating|recommendations|founds'
			));
			$internal_request->skip_limits = true;
			$caches = OkapiServiceRunner::call("services/caches/geocaches", $internal_request);
			
			foreach ($caches as $cache)
			{
				list($lat, $lon) = explode("|", $cache['location']);
				list($z21x, $z21y) = self::latlon_to_z21xy($lat, $lon);
				$flags = 0;
				if (($cache['founds'] > 6) && (($cache['recommendations'] / $cache['founds']) > 0.3))
					$flags |= self::$FLAG_STAR;
				Db::execute("
					replace into okapi_tile_caches (
						z, x, y, cache_id, z21x, z21y, status, type, rating, flags
					) values (
						0, 0, 0,
						'".mysql_real_escape_string($cache['internal_id'])."',
						'".mysql_real_escape_string($z21x)."',
						'".mysql_real_escape_string($z21y)."',
						'".mysql_real_escape_string(Okapi::cache_status_name2id($cache['status']))."',
						'".mysql_real_escape_string(Okapi::cache_type_name2id($cache['type']))."',
						".(($cache['rating'] === null) ? "null" : $cache['rating']).",
						'".mysql_real_escape_string($flags)."'
					);
				");
			}
			$status = 2;
		}
		else
		{
			# We will use the parent tile to compute the contents of this tile.
			
			$parent_zoom = $zoom - 1;
			$parent_x = $x >> 1;
			$parent_y = $y >> 1;
			
			$status = self::get_tile_status($parent_zoom, $parent_x, $parent_y);
			if ($status === null)  # Not computed.
			{
				$status = self::compute_tile($parent_zoom, $parent_x, $parent_y);
			}
			if ($status === 1)  # Computed and empty.
			{
				# No need to check.
			}
			else  # Computed, not empty.
			{
				$scale = 8 + 21 - $zoom;
				$parentcenter_z21x = (($parent_x << 1) | 1) << $scale;
				$parentcenter_z21y = (($parent_y << 1) | 1) << $scale;
				$margin = 1 << ($scale - 3);
				$left_z21x = (($parent_x << 1) << $scale) - $margin;
				$right_z21x = ((($parent_x + 1) << 1) << $scale) + $margin;
				$top_z21y = (($parent_y << 1) << $scale) - $margin;
				$bottom_z21y = ((($parent_y + 1) << 1) << $scale) + $margin;
				
				# Choose the right quarter.
				# |1 2|
				# |3 4|
				
				if ($x & 1)  # 2 or 4
					$left_z21x = $parentcenter_z21x - $margin;
				else  # 1 or 3
					$right_z21x = $parentcenter_z21x + $margin;
				if ($y & 1)  # 3 or 4
					$top_z21y = $parentcenter_z21y - $margin;
				else  # 1 or 2
					$bottom_z21y = $parentcenter_z21y + $margin;
				
				# Cache the result.
				
				Db::execute("
					replace into okapi_tile_caches (
						z, x, y, cache_id, z21x, z21y, status, type, rating, flags
					)
					select
						'".mysql_real_escape_string($zoom)."',
						'".mysql_real_escape_string($x)."',
						'".mysql_real_escape_string($y)."',
						cache_id, z21x, z21y, status, type, rating, flags
					from okapi_tile_caches
					where
						z = '".mysql_real_escape_string($parent_zoom)."'
						and x = '".mysql_real_escape_string($parent_x)."'
						and y = '".mysql_real_escape_string($parent_y)."'
						and z21x between $left_z21x and $right_z21x
						and z21y between $top_z21y and $bottom_z21y
				");
			}
		}
		
		# Mark tile as computed.
		
		Db::execute("
			replace into okapi_tile_status (z, x, y, status)
			values (
				'".mysql_real_escape_string($zoom)."',
				'".mysql_real_escape_string($x)."',
				'".mysql_real_escape_string($y)."',
				'".mysql_real_escape_string($status)."'
			);
		");
		
		if ($zoom <= 2)
		{
			# Resume other processes which begun low-zoom tile computation.
			$lock->release();
		}
		return $status;
	}
	
	private static $images = array();
	private static function get_image($key)
	{
		if (!isset(self::$images[$key]))
			self::$images[$key] = imagecreatefrompng(
				$GLOBALS['rootpath']."okapi/static/tilemap/$key.png");
		return self::$images[$key];
	}
	
	private static function draw_cache($im, $zoom, &$cache_struct, &$found_cache_dict)
	{
		list($cache_id, $px, $py, $status, $type, $rating, $flags, $count) = $cache_struct;
		switch ($type) {
			case 2: $key = 'traditional'; break;
			case 3: $key = 'multi'; break;
			case 6: $key = 'event'; break;
			case 7: $key = 'quiz'; break;
			case 4: $key = 'virtual'; break;
			default: $key = 'other'; break;
		}
		if ($zoom <= 9)
		{
			$marker = self::get_image("tiny_$key");
			$width = 10;
			$height = 10;
			$center_x = 5;
			$center_y = 6;
			$markercenter_x = 5;
			$markercenter_y = 6;
		}
		elseif ($zoom <= 12)
		{
			$marker = self::get_image("medium_$key");
			$width = 14;
			$height = 14;
			$center_x = 7;
			$center_y = 8;
			$markercenter_x = 7;
			$markercenter_y = 8;
		}
		else
		{
			$marker = self::get_image("large_$key");
			$width = 40;
			$height = 32;
			$center_x = 12;
			$center_y = 26;
			$markercenter_x = 12;
			$markercenter_y = 12;
		}
		
		# If cache covers more caches, then put two markers instead of one.
		
		if ($count > 1)
			imagecopy($im, $marker, $px - $center_x + 3, $py - $center_y - 2, 0, 0, $width, $height);
		
		# Put the marker.
		
		imagecopy($im, $marker, $px - $center_x, $py - $center_y, 0, 0, $width, $height);
		
		# Mark unavailable caches with an X.
		
		if ($zoom >= 10 && ($status != 1))
		{
			$icon = self::get_image("status_unavailable");
			imagecopy($im, $icon, $px - ($center_x - $markercenter_x) - 7,
				$py - ($center_y - $markercenter_y) - 8, 0, 0, 16, 16);
		}
		
		# Put the rating smile. :)
		
		if ($zoom >= 13)
		{
			if ($rating >= 4.2)
			{
				if ($flags & self::$FLAG_STAR) {
					$icon = self::get_image("rating_grin");
					imagecopy($im, $icon, $px - 7 - 6, $py - $center_y - 8, 0, 0, 16, 16);
					$icon = self::get_image("rating_star");
					imagecopy($im, $icon, $px - 7 + 6, $py - $center_y - 8, 0, 0, 16, 16);
				} else {
					$icon = self::get_image("rating_grin");
					imagecopy($im, $icon, $px - 7, $py - $center_y - 8, 0, 0, 16, 16);
				}
			}
			elseif ($rating >= 3.6) {
				$icon = self::get_image("rating_smile");
				imagecopy($im, $icon, $px - 7, $py - $center_y - 8, 0, 0, 16, 16);
			}
		}
		
		# Mark found caches with V.
		
		if ($zoom >= 10 && (isset($found_cache_dict[$cache_id])))
		{
			$icon = self::get_image("found");
			if ($zoom >= 13) {
				imagecopy($im, $icon, $px - 2, $py - $center_y - 3, 0, 0, 16, 16);
			} else {
				imagecopy($im, $icon, $px - ($center_x - $markercenter_x) - 7,
					$py - ($center_y - $markercenter_y) - 9, 0, 0, 16, 16);
			}
		}
	}
	
	private static function latlon_to_z21xy($lat, $lon)
	{
		$offset = 128 << 21;
		$x = round($offset + ($offset * $lon / 180));
		$y = round($offset - $offset/pi() * log((1 + sin($lat * pi() / 180)) / (1 - sin($lat * pi() / 180))) / 2);
		return array($x, $y);
	}
}
