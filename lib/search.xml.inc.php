<?php
	/***************************************************************************
		*                                         				                                
		*   This program is free software; you can redistribute it and/or modify  	
		*   it under the terms of the GNU General Public License as published by  
		*   the Free Software Foundation; either version 2 of the License, or	    	
		*   (at your option) any later version.
		*
		***************************************************************************/

	/****************************************************************************
		                                         				                                
		XML search output
		
	****************************************************************************/

	global $content, $bUseZip, $sqldebug;
	
	$lang = 'PL';
	$encoding = 'UTF-8';

	$xmlLine = "	<cache>
		<name><![CDATA[{cachename}]]></name>
		<owner><![CDATA[{owner}]]></owner>
		<id>{cacheid}</id>
		<waypoint>{waypoint}</waypoint>
		<hidden>{time}</hidden>
		<status>{status}</status>
		<lon>{lon}</lon>
		<lat>{lat}</lat>
		<distance unit=\"".$distance_unit."\">{distance}</distance>
		<type>{type}</type>
		<difficulty>{difficulty}</difficulty>
		<terrain>{terrain}</terrain>
		<size>{container}</size>
		<country>{country}</country>
		<link><![CDATA[http://www.opencaching.pl/viewcache.php?wp={waypoint}]]></link>
		<desc><![CDATA[{shortdesc}]]></desc>
		<hints><![CDATA[{hints}]]></hints>
	</cache>
";

	$txtLogs = "";

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
	
	if ($count == 'max') $count = 500;
	if (!is_numeric($count)) $count = 0;
	if ($count < 1) $count = 1;
	if ($count > 500) $count = 500;

	$sqlLimit .= ' LIMIT ' . $startat . ', ' . $count;

	// tempor?re tabelle erstellen
	sql('CREATE TEMPORARY TABLE `xmlcontent` ' . $sql . $sqlLimit, $sqldebug);
	//$rsCount = sql('SELECT COUNT(*) `count` FROM `xmlcontent`');
	$rsCount = sql('SELECT COUNT(cache_id) `count` FROM ('.$sql.') query;');
	$rCount = sql_fetch_array($rsCount);
	mysql_free_result($rsCount);

	// ok, ausgabe starten
	
	if ($sqldebug == false)
	{
		header("Content-type: application/xml; charset=".$encoding);
		//header("Content-Disposition: attachment; filename=" . $sFilebasename . ".txt");
	}
	
	echo "<?xml version=\"1.0\" encoding=\"".$encoding."\"?>\n";
	echo "<result>\n";
	
	echo "	<docinfo>\n";
	echo "		<results>" . $rCount['count'] . "</results>\n";
	echo "		<startat>" . $startat . "</startat>\n";
	echo "		<perpage>" . $count . "</perpage>\n";
	echo "	</docinfo>\n";

	// ok, ausgabe ...
	
	$rs = sql('SELECT `xmlcontent`.`cache_id` `cacheid`, `xmlcontent`.`longitude` `longitude`, `xmlcontent`.`latitude` `latitude`, `caches`.`wp_oc` `waypoint`, `caches`.`date_hidden` `date_hidden`, `caches`.`name` `name`, `caches`.`country` `country`, `caches`.`terrain` `terrain`, `caches`.`difficulty` `difficulty`, `caches`.`desc_languages` `desc_languages`, `cache_size`.`'.$lang.'` `size`, `cache_type`.`'.$lang.'` `type`, `cache_status`.`'.$lang.'` `status`, `user`.`username` `username`, `cache_desc`.`desc` `desc`, `cache_desc`.`short_desc` `short_desc`, `cache_desc`.`hint` `hint`, `cache_desc`.`desc_html` `html`, `xmlcontent`.`distance` `distance` FROM `xmlcontent`, `caches`, `user`, `cache_desc`, `cache_type`, `cache_status`, `cache_size` WHERE `xmlcontent`.`cache_id`=`caches`.`cache_id` AND `caches`.`cache_id`=`cache_desc`.`cache_id` AND `caches`.`default_desclang`=`cache_desc`.`language` AND `xmlcontent`.`user_id`=`user`.`user_id` AND `caches`.`type`=`cache_type`.`id` AND `caches`.`status`=`cache_status`.`id` AND `caches`.`size`=`cache_size`.`id`');
	while($r = sql_fetch_array($rs))
	{
		$thisline = $xmlLine;
		
		$lat = sprintf('%01.5f', $r['latitude']);
		$thisline = str_replace('{lat}', help_latToDegreeStr($lat), $thisline);
		
		$lon = sprintf('%01.5f', $r['longitude']);
		$thisline = str_replace('{lon}', help_lonToDegreeStr($lon), $thisline);

		$time = date('d.m.Y', strtotime($r['date_hidden']));
		$thisline = str_replace('{time}', $time, $thisline);
		$thisline = str_replace('{waypoint}', $r['waypoint'], $thisline);
		$thisline = str_replace('{cacheid}', $r['cacheid'], $thisline);
		$thisline = str_replace('{cachename}', filterevilchars($r['name']), $thisline);
		$thisline = str_replace('{country}', db_CountryFromShort($r['country']), $thisline);
		
		if ($r['hint'] == '')
			$thisline = str_replace('{hints}', '', $thisline);
		else
			$thisline = str_replace('{hints}', str_rot13_html(filterevilchars(strip_tags($r['hint']))), $thisline);
		
		$thisline = str_replace('{shortdesc}', filterevilchars($r['short_desc']), $thisline);
		
		if ($r['html'] == 0)
		{
			$thisline = str_replace('{htmlwarn}', '', $thisline);
			$thisline = str_replace('{desc}', filterevilchars(strip_tags($r['desc'])), $thisline);
		}
		else
		{
			$thisline = str_replace('{htmlwarn}', ' (Text p�eveden z HTML)', $thisline);
			$thisline = str_replace('{desc}', html2txt(filterevilchars($r['desc'])), $thisline);
		}
		
		$thisline = str_replace('{type}', $r['type'], $thisline);
		$thisline = str_replace('{container}', $r['size'], $thisline);
		$thisline = str_replace('{status}', $r['status'], $thisline);
		
		$difficulty = sprintf('%01.1f', $r['difficulty'] / 2);
		$thisline = str_replace('{difficulty}', $difficulty, $thisline);

		$terrain = sprintf('%01.1f', $r['terrain'] / 2);
		$thisline = str_replace('{terrain}', $terrain, $thisline);

		$thisline = str_replace('{owner}', filterevilchars($r['username']), $thisline);
		$thisline = str_replace('{distance}', htmlspecialchars(sprintf("%01.1f", $r['distance'])), $thisline);

		// logs ermitteln
		$logentries = '';
		/*$rsLogs = sql("SELECT `cache_logs`.`id`, `log_types`.`'.$lang.'` `type`, `cache_logs`.`date`, `cache_logs`.`text`, `user`.`username` FROM `cache_logs`, `user`, `log_types` WHERE `cache_logs`.`user_id`=`user`.`user_id` AND `cache_logs`.`type`=`log_types`.`id` AND `cache_logs`.`cache_id`=&1 ORDER BY `cache_logs`.`date` DESC LIMIT 20", $r['cacheid']);
		while ($rLog = sql_fetch_array($rsLogs))
		{
			$thislog = $txtLogs;
			
			$thislog = str_replace('{id}', $rLog['id'], $thislog);
			$thislog = str_replace('{date}', date('d.m.Y', strtotime($rLog['date'])), $thislog);
			$thislog = str_replace('{username}', filterevilchars($rLog['username']), $thislog);
			
			$logtype = $rLog['type'];
				
			$thislog = str_replace('{type}', $logtype, $thislog);
			$thislog = str_replace('{text}', filterevilchars($rLog['text']), $thislog);

			$logentries .= $thislog . "\n";
		}
		$thisline = str_replace('{logs}', $logentries, $thisline);
*/
		$thisline = lf2crlf($thisline);

		echo $thisline;
	}
	mysql_free_result($rs);
	
	if ($sqldebug == true) sqldbg_end();
	echo "</result>\n";
	
	exit;
	
	function html2txt($html)
	{
		$str = str_replace("\r\n", '', $html);
		$str = str_replace("\n", '', $str);
		$str = str_replace('<br />', "\n", $str);
		$str = strip_tags($str);
		return $str;
	}
	
	function lf2crlf($str)
	{
		return str_replace("\r\r\n" ,"\r\n" , str_replace("\n" ,"\r\n" , $str));
	}
	
	function filterevilchars($str)
	{
		$evilchars = array(31 => 31, 30 => 30, 
											29 => 29, 28 => 28, 27 => 27, 26 => 26, 25 => 25, 24 => 24, 
											23 => 23, 22 => 22, 21 => 21, 20 => 20, 19 => 19, 18 => 18, 
											17 => 17, 16 => 16, 15 => 15, 14 => 14, 12 => 12, 11 => 11, 
											9 => 9, 8 => 8, 7 => 7, 6 => 6, 5 => 5, 4 => 4, 3 => 3, 
											2 => 2, 1 => 1, 0 => 0);	

		foreach ($evilchars AS $ascii)
			$str = str_replace(chr($ascii), '', $str);
		
		$str = preg_replace('/&([a-zA-Z]{1})caron;/', '\\1', $str);
		$str = preg_replace('/&([a-zA-Z]{1})acute;/', '\\1', $str);

		return $str;
	}
?>
