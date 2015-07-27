<?php
	/***************************************************************************
															./lib/search.gpx.inc.php
																-------------------
			begin                : November 1 2005 
			copyright            : (C) 2005 The OpenCaching Group
			forum contact at     : http://www.opencaching.com/phpBB2

		***************************************************************************/

	/***************************************************************************
		*                                         				                                
		*   This program is free software; you can redistribute it and/or modify  	
		*   it under the terms of the GNU General Public License as published by  
		*   the Free Software Foundation; either version 2 of the License, or	    	
		*   (at your option) any later version.
		*
		***************************************************************************/

	/****************************************************************************
		           
		Unicode Reminder ăĄă˘
                              				                                
		GPX search output
		
	****************************************************************************/

	global $content, $bUseZip, $sqldebug, $hide_coords, $usr;

	$txtLine = "Nazwa: {cachename} przez {owner}
Wspolrzedne: {lon} {lat}
Status: {status}

Zalozona: {time}
Waypoint: {waypoint}
Kraj: {country}
Typ: {type}
Wielkosc: {container}
Z/T: {difficulty}/{terrain}
Online: http://www.opencaching.pl/viewcache.php?wp={waypoint}

Krotki opis: {shortdesc}

Pelny opis{htmlwarn}:
<===================>
{desc}
{rr_comment}
<===================>

Kodowane uwagi:
<===================>
{hints}
<===================>
A|B|C|D|E|F|G|H|I|J|K|L|M
N|O|P|Q|R|S|T|U|V|W|X|Y|Z

LOGi:
{logs}
";

	$txtLogs = "<===================>
{username} / {date} / {type}

{text}
";

	if( $usr || !$hide_coords )
	{
		//prepare the output
		$caches_per_page = 20;
		
		$sql = 'SELECT '; 
		
		if (isset($lat_rad) && isset($lon_rad))
		{
			$sql .= getSqlDistanceFormula($lon_rad * 180 / 3.14159, $lat_rad * 180 / 3.14159, 0, $multiplier[$distance_unit]) . ' `distance`, ';
		}
		else
		{
			if ($usr === false)
			{
				$sql .= '0 distance, ';
			}
			else
			{
				//get the users home coords
				$rs_coords = sql("SELECT `latitude`, `longitude` FROM `user` WHERE `user_id`='&1'", $usr['userid']);
				$record_coords = sql_fetch_array($rs_coords);
				
				if ((($record_coords['latitude'] == NULL) || ($record_coords['longitude'] == NULL)) || (($record_coords['latitude'] == 0) || ($record_coords['longitude'] == 0)))
				{
					$sql .= '0 distance, ';
				}
				else
				{
					//TODO: load from the users-profile
					$distance_unit = 'km';

					$lon_rad = $record_coords['longitude'] * 3.14159 / 180;   
					$lat_rad = $record_coords['latitude'] * 3.14159 / 180; 

					$sql .= getSqlDistanceFormula($record_coords['longitude'], $record_coords['latitude'], 0, $multiplier[$distance_unit]) . ' `distance`, ';
				}
				mysql_free_result($rs_coords);
			}
		}
		$sql .= '`caches`.`cache_id` `cache_id`, `caches`.`status` `status`, `caches`.`type` `type`, `caches`.`size` `size`, `caches`.`longitude` `longitude`, `caches`.`latitude` `latitude`, `caches`.`user_id` `user_id`
					FROM `caches`
					WHERE `caches`.`cache_id` IN (' . $sqlFilter . ')';
		
		$sortby = $options['sort'];
		if (isset($lat_rad) && isset($lon_rad) && ($sortby == 'bydistance'))
		{
			$sql .= ' ORDER BY distance ASC';
		}
		else if ($sortby == 'bycreated')
		{
			$sql .= ' ORDER BY date_created DESC';
		}
		else // by name
		{
			$sql .= ' ORDER BY name ASC';
		}

		//startat?
		$startat = isset($_REQUEST['startat']) ? $_REQUEST['startat'] : 0;
		if (!is_numeric($startat)) $startat = 0;
		
		if (isset($_REQUEST['count']))
			$count = $_REQUEST['count'];
		else
			$count = $caches_per_page;
		
		$maxlimit = 1000000000;
		
		if ($count == 'max') $count = $maxlimit;
		if (!is_numeric($count)) $count = 0;
		if ($count < 1) $count = 1;
		if ($count > $maxlimit) $count = $maxlimit;

		$sqlLimit = ' LIMIT ' . $startat . ', ' . $count;

		// temporĂ¤re tabelle erstellen
		sql('CREATE TEMPORARY TABLE `txtcontent` ' . $sql . $sqlLimit, $sqldebug);

		$rsCount = sql('SELECT COUNT(*) `count` FROM `txtcontent`');
		$rCount = sql_fetch_array($rsCount);
		mysql_free_result($rsCount);
		
		if ($rCount['count'] == 1)
		{
			$rsName = sql('SELECT `caches`.`wp_oc` `wp_oc` FROM `txtcontent`, `caches` WHERE `txtcontent`.`cache_id`=`caches`.`cache_id` LIMIT 1');
			$rName = sql_fetch_array($rsName);
			mysql_free_result($rsName);
			
			$sFilebasename = $rName['wp_oc'];
		}
		else {
			if ($options['searchtype'] == 'bywatched') {
				$sFilebasename = 'watched_caches';
			} elseif ($options['searchtype'] == 'bylist') {
				$sFilebasename = 'cache_list';
			} else {
				$rsName = sql('SELECT `queries`.`name` `name` FROM `queries` WHERE `queries`.`id`= &1 LIMIT 1', $options['queryid']);
				$rName = sql_fetch_array($rsName);
				mysql_free_result($rsName);
				if (isset($rName['name']) && ($rName['name'] != '')) {
					$sFilebasename = trim($rName['name']);
					$sFilebasename = str_replace(" ", "_", $sFilebasename);
				} else {
					$sFilebasename = 'ocpl' . $options['queryid'];
				}
			}
		}

		$bUseZip = ($rCount['count'] > 50);
		$bUseZip = $bUseZip || (isset($_REQUEST['zip']) && ($_REQUEST['zip'] == '1'));
		$bUseZip = false;
		if ($bUseZip == true)
		{
			$content = '';
			require_once($rootpath . 'lib/phpzip/ss_zip.class.php');
			$phpzip = new ss_zip('',6);
		}

		// ok, ausgabe starten
		
		if ($sqldebug == false)
		{
			if ($bUseZip == true)
			{
				header("content-type: application/zip");
				header('Content-Disposition: attachment; filename=' . $sFilebasename . '.zip');
			}
			else
			{
				header("Content-type: text/plain");
				header("Content-Disposition: attachment; filename=" . $sFilebasename . ".txt");
			}
		}

		// ok, ausgabe ...
		
		$rs = sql('SELECT `txtcontent`.`cache_id` `cacheid`, `txtcontent`.`longitude` `longitude`, `txtcontent`.`latitude` `latitude`, `caches`.`wp_oc` `waypoint`, `caches`.`date_hidden` `date_hidden`, `caches`.`name` `name`, `caches`.`country` `country`, `caches`.`terrain` `terrain`, `caches`.`difficulty` `difficulty`, `caches`.`desc_languages` `desc_languages`, `cache_size`.`pl` `size`, `cache_type`.`pl` `type`, `cache_status`.`pl` `status`, `user`.`username` `username`, `cache_desc`.`desc` `desc`, `cache_desc`.`short_desc` `short_desc`, `cache_desc`.`hint` `hint`, `cache_desc`.`desc_html` `html`, `cache_desc`.`rr_comment`, `caches`.`logpw` FROM `txtcontent`, `caches`, `user`, `cache_desc`, `cache_type`, `cache_status`, `cache_size` WHERE `txtcontent`.`cache_id`=`caches`.`cache_id` AND `caches`.`cache_id`=`cache_desc`.`cache_id` AND `caches`.`default_desclang`=`cache_desc`.`language` AND `txtcontent`.`user_id`=`user`.`user_id` AND `caches`.`type`=`cache_type`.`id` AND `caches`.`status`=`cache_status`.`id` AND `caches`.`size`=`cache_size`.`id`');
		while($r = sql_fetch_array($rs))
		{
			$thisline = $txtLine;
			
			$lat = sprintf('%01.5f', $r['latitude']);
			$thisline = str_replace('{lat}', help_latToDegreeStr($lat), $thisline);
			
			$lon = sprintf('%01.5f', $r['longitude']);
			$thisline = str_replace('{lon}', help_lonToDegreeStr($lon), $thisline);

			$time = date('d.m.Y', strtotime($r['date_hidden']));
			$thisline = str_replace('{time}', $time, $thisline);
			$thisline = str_replace('{waypoint}', $r['waypoint'], $thisline);
			$thisline = str_replace('{cacheid}', $r['cacheid'], $thisline);
			$thisline = str_replace('{cachename}', $r['name'], $thisline);
			$thisline = str_replace('{country}', db_CountryFromShort($r['country']), $thisline);
			
			if ($r['hint'] == '')
				$thisline = str_replace('{hints}', '', $thisline);
			else
				$thisline = str_replace('{hints}', str_rot13_html(strip_tags($r['hint'])), $thisline);
			
			$logpw = ($r['logpw']==""?"":"UWAGA! W skrzynce znajduje się hasło - pamiętaj o jego zapisaniu!<br>");			
			
			$thisline = str_replace('{shortdesc}', $r['short_desc'], $thisline);
			
			if ($r['html'] == 0)
			{
				$thisline = str_replace('{htmlwarn}', '', $thisline);
				$thisline = str_replace('{desc}', strip_tags($logpw.$r['desc']), $thisline);
			}
			else
			{
				$thisline = str_replace('{htmlwarn}', ' (Bez HTML)', $thisline);
				$thisline = str_replace('{desc}', html2txt($logpw.$r['desc']), $thisline);
			}
			
			if( $r['rr_comment'] == '' )
				$thisline = str_replace('{rr_comment}', '', $thisline);
			else
				$thisline = str_replace('{rr_comment}', html2txt("<br><br>--------<br>".$r['rr_comment']), $thisline);
				
			$thisline = str_replace('{type}', $r['type'], $thisline);
			$thisline = str_replace('{container}', $r['size'], $thisline);
			$thisline = str_replace('{status}', $r['status'], $thisline);
			
			$difficulty = sprintf('%01.1f', $r['difficulty'] / 2);
			$thisline = str_replace('{difficulty}', $difficulty, $thisline);

			$terrain = sprintf('%01.1f', $r['terrain'] / 2);
			$thisline = str_replace('{terrain}', $terrain, $thisline);

			$thisline = str_replace('{owner}', $r['username'], $thisline);

			// logs ermitteln
			$logentries = '';
			$rsLogs = sql("SELECT `cache_logs`.`id`, `cache_logs`.`text_html`, `log_types`.`pl` `type`, `cache_logs`.`date`, `cache_logs`.`text`, `user`.`username` FROM `cache_logs`, `user`, `log_types` WHERE `cache_logs`.`deleted`=0 AND `cache_logs`.`user_id`=`user`.`user_id` AND `cache_logs`.`type`=`log_types`.`id` AND `cache_logs`.`cache_id`=&1 ORDER BY `cache_logs`.`date` DESC LIMIT 20", $r['cacheid']);
			while ($rLog = sql_fetch_array($rsLogs))
			{
				$thislog = $txtLogs;
				
				$thislog = str_replace('{id}', $rLog['id'], $thislog);
				$thislog = str_replace('{date}', date('d.m.Y', strtotime($rLog['date'])), $thislog);
				$thislog = str_replace('{username}', $rLog['username'], $thislog);
				
				$logtype = $rLog['type'];
				
				$thislog = str_replace('{type}', $logtype, $thislog);
				if ($rLog['text_html'] == 0)
					$thislog = str_replace('{text}', $rLog['text'], $thislog);
				else
					$thislog = str_replace('{text}', html2txt($rLog['text']), $thislog);

				$logentries .= $thislog . "\n";
			}
			$thisline = str_replace('{logs}', $logentries, $thisline);

			$thisline = lf2crlf($thisline);

			if($bUseZip == false)
				echo $thisline;
			else
			{
				$phpzip->add_data($r['waypoint'] . '.txt', $thisline);
			}
		}
		mysql_free_result($rs);
		
		if ($sqldebug == true) sqldbg_end();
		
		// phpzip versenden
		if ($bUseZip == true)
		{
			echo $phpzip->save($sFilebasename . '.zip', 'b');
		}
	}
	exit;
	
	function html2txt($html)
	{
		$str = str_replace("\r\n", '', $html);
		$str = str_replace("\n", '', $str);
		$str = str_replace('<br />', "\n", $str);
		$str = str_replace('<br>', "\n", $str);
		$str = strip_tags($str);
		return $str;
	}
	
	function lf2crlf($str)
	{
		return str_replace("\r\r\n" ,"\r\n" , str_replace("\n" ,"\r\n" , $str));
	}
?>