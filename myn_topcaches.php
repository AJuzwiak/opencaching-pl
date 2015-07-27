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

   Unicode Reminder ăĄă˘

	 include the newcaches HTML file

 ****************************************************************************/
	global $lang, $rootpath, $usr;
	//prepare the templates and include all neccessary
	require_once('./lib/common.inc.php');
	require_once('./lib/cache_icon.inc.php');
	require_once($rootpath . 'lib/caches.inc.php');
	require_once($stylepath . '/lib/icons.inc.php');
	
	//Preprocessing
	if ($error == false)
	{
		//get the news
		$tplname = 'myn_topcaches';
//		require('tpl/stdstyle/newcaches.inc.php');
		require($stylepath . '/newcaches.inc.php');

		$startat = isset($_REQUEST['startat']) ? $_REQUEST['startat'] : 0;
		$startat = $startat + 0;

		$perpage = 50;
		$startat -= $startat % $perpage;

	        function cleanup_text($str)
        {
          $str = strip_tags($str, "<li>");
	      $from[] = '&nbsp;'; $to[] = ' ';
          $from[] = '<p>'; $to[] = '';
         $from[] = '\n'; $to[] = '';
         $from[] = '\r'; $to[] = '';
          $from[] = '</p>'; $to[] = "";
          $from[] = '<br>'; $to[] = "";
          $from[] = '<br />'; $to[] = "";
    	 $from[] = '<br/>'; $to[] = "";
            
          $from[] = '<li>'; $to[] = " - ";
          $from[] = '</li>'; $to[] = "";
          
          $from[] = '&oacute;'; $to[] = 'o';
          $from[] = '&quot;'; $to[] = '"';
          $from[] = '&[^;]*;'; $to[] = '';
          
          $from[] = '&'; $to[] = '';
          $from[] = '\''; $to[] = '';
          $from[] = '"'; $to[] = '';
          $from[] = '<'; $to[] = '';
          $from[] = '>'; $to[] = '';
          $from[] = '('; $to[] = ' -';
          $from[] = ')'; $to[] = '- ';
          $from[] = ']]>'; $to[] = ']] >';
	 $from[] = ''; $to[] = '';
              
          for ($i = 0; $i < count($from); $i++)
            $str = str_replace($from[$i], $to[$i], $str);
                                 
          return filterevilchars($str);
        }
        
	
        function filterevilchars($str)
	{
		return str_replace('[\\x00-\\x09|\\x0A-\\x0E-\\x1F]', '', $str);
	}

		
			//get user record
			$user_id = $usr['userid'];
			tpl_set_var('userid',$user_id);	
$latitude =sqlValue("SELECT `latitude` FROM user WHERE user_id='" . sql_escape($usr['userid']) . "'", 0);
$longitude =sqlValue("SELECT `longitude` FROM user WHERE user_id='" . sql_escape($usr['userid']) . "'", 0);

if (($longitude==NULL && $latitude==NULL) ||($longitude==0 && $latitude==0) ) {tpl_set_var('info','<br><div class="notice" style="line-height: 1.4em;font-size: 120%;"><b>'.tr("myn_info").'</b></div><br>');} else { tpl_set_var('info','');}

if ($latitude==NULL || $latitude==0) $latitude=52.24522;
if ($longitude==NULL || $longitude==0) $longitude=21.00442;

$distance =sqlValue("SELECT `notify_radius` FROM user WHERE user_id='" . sql_escape($usr['userid']) . "'", 0);
if ($distance==0) $distance=35;
$distance_unit = 'km';
$radius=$distance;	

			//get the users home coords
//			$rs_coords = sql("SELECT `latitude` `lat`, `longitude` `lon` FROM `user` WHERE `user_id`='&1'", $usr['userid']);
//			$record_coords = sql_fetch_array($rs_coords);
	
				$lat = $latitude;
				$lon = $longitude;
				$lon_rad = $lon * 3.14159 / 180;   
				$lat_rad = $lat * 3.14159 / 180; 
				
				
				//all target caches are between lat - max_lat_diff and lat + max_lat_diff
				$max_lat_diff = $distance / 111.12;
				
				//all target caches are between lon - max_lon_diff and lon + max_lon_diff
				//TODO: check!!!
				$max_lon_diff = $distance * 180 / (abs(sin((90 - $lat) * 3.14159 / 180 )) * 6378  * 3.14159);
				sql('DROP TEMPORARY TABLE IF EXISTS local_caches'.$user_id.'');							
				sql('CREATE TEMPORARY TABLE local_caches'.$user_id.' ENGINE=MEMORY 
										SELECT 
											(' . getSqlDistanceFormula($lon, $lat, $distance, $multiplier[$distance_unit]) . ') AS `distance`,
											`caches`.`cache_id` AS `cache_id`,
											`caches`.`wp_oc` AS `wp_oc`,
											`caches`.`type` AS `type`,
											`caches`.`name` AS `name`,
											`caches`.`longitude` `longitude`,
											`caches`.`latitude` `latitude`,
											`caches`.`date_hidden` `date_hidden`,
											`caches`.`date_created` `date_created`,
											`caches`.`country` `country`,
											`caches`.`difficulty` `difficulty`,
											`caches`.`terrain` `terrain`,
											`caches`.`status` `status`,
											`caches`.`user_id` `user_id` 
										FROM `caches` 
										WHERE `caches`.`cache_id` NOT IN (SELECT `cache_ignore`.`cache_id` FROM `cache_ignore` WHERE `cache_ignore`.`user_id`=\''.$user_id .'\') 
											AND `longitude` > ' . ($lon - $max_lon_diff) . ' 
											AND `longitude` < ' . ($lon + $max_lon_diff) . ' 
											AND `latitude` > ' . ($lat - $max_lat_diff) . ' 
											AND `latitude` < ' . ($lat + $max_lat_diff) . '
										HAVING `distance` < ' . $distance);
				sql('ALTER TABLE local_caches'.$user_id.' ADD PRIMARY KEY ( `cache_id` ),
				ADD INDEX(`cache_id`), ADD INDEX (`wp_oc`), ADD INDEX(`type`), ADD INDEX(`name`), ADD INDEX(`user_id`), ADD INDEX(`date_hidden`), ADD INDEX(`date_created`)');


				$file_content ='';
	$rs =sql('SELECT `user`.`user_id` `userid`,
				`user`.`username` `username`,
				`caches`.`cache_id` `cacheid`,
				`caches`.`name` `cachename`,
				`caches`.`longitude` `longitude`,
				`caches`.`latitude` `latitude`,
				`caches`.`date_hidden` `date`,
				`caches`.`date_created` `date_created`,
				`caches`.`country` `country`,
				`caches`.`difficulty` `difficulty`,
				`caches`.`terrain` `terrain`,
				`cache_type`.`icon_large` `icon_large`,
				count(`cache_rating`.`cache_id`) `toprate`
        FROM local_caches'.$user_id.' `caches` INNER JOIN `user` ON (`caches`.`user_id`=`user`.`user_id`) LEFT JOIN `cache_rating` ON (`caches`.`cache_id`=`cache_rating`.`cache_id`), `cache_type`
        WHERE `caches`.`type`!=6
			 AND `cache_rating`.`cache_id`=`caches`.`cache_id`
			  AND `caches`.`status`=1
			  AND `caches`.`type`=`cache_type`.`id`
			  GROUP BY `caches`.`cache_id`
			ORDER BY `toprate` DESC, `caches`.`name` ASC LIMIT  ' . ($startat+0) . ', ' . ($perpage+0));


		while ($r = sql_fetch_array($rs))
		{
				$file_content .= '<tr>';
				$file_content .= '<td style="width: 90px;">'. date('Y-m-d', strtotime($r['date'])) . '</td>';	
				$file_content .= '<td style="width: 22px;"><span style="font-weight:bold;color: green;">'. $r['toprate'] . '</span></td>';							
				$file_content .= '<td width="22">&nbsp;<img src="tpl/stdstyle/images/' .getSmallCacheIcon($r['icon_large']) . '" border="0" alt=""/></td>';
				$file_content .= '<td><b><a class="links" href="viewcache.php?cacheid=' . htmlspecialchars($r['cacheid'], ENT_COMPAT, 'UTF-8') . '">' . htmlspecialchars($r['cachename'], ENT_COMPAT, 'UTF-8') . '</a></b></td>';
				$file_content .= '<td width="32"><b><a class="links" href="viewprofile.php?userid='.htmlspecialchars($r['userid'], ENT_COMPAT, 'UTF-8') . '">' .htmlspecialchars($r['username'], ENT_COMPAT, 'UTF-8'). '</a></b></td>';

	$rs_log = sql("SELECT cache_logs.id AS id, cache_logs.cache_id AS cache_id,
	                          cache_logs.type AS log_type,
	                          DATE_FORMAT(cache_logs.date,'%Y-%m-%d') AS log_date,
				cache_logs.text AS log_text,
	                          user.username AS user_name,
				user.user_id AS user_id,
				log_types.icon_small AS icon_small, COUNT(gk_item.id) AS geokret_in
			FROM (cache_logs INNER JOIN caches ON (caches.cache_id = cache_logs.cache_id)) INNER JOIN user ON (cache_logs.user_id = user.user_id) INNER JOIN log_types ON (cache_logs.type = log_types.id)
							LEFT JOIN	gk_item_waypoint ON gk_item_waypoint.wp = caches.wp_oc
							LEFT JOIN	gk_item ON gk_item.id = gk_item_waypoint.id AND
							gk_item.stateid<>1 AND gk_item.stateid<>4 AND gk_item.typeid<>2 AND gk_item.stateid !=5				
			WHERE cache_logs.deleted=0 AND cache_logs.cache_id=&1
			 GROUP BY cache_logs.id ORDER BY cache_logs.date_created DESC LIMIT 1",$r['cacheid']);

			if (mysql_num_rows($rs_log) != 0)
			{
			$r_log = sql_fetch_array($rs_log);

				$file_content .= '<td style="width: 80px;">'. htmlspecialchars(date("Y-m-d", strtotime($r_log['log_date'])), ENT_COMPAT, 'UTF-8') . '</td>';			

				$file_content .= '<td width="22"><b><a class="links" href="viewlogs.php?logid=' . htmlspecialchars($r_log['id'], ENT_COMPAT, 'UTF-8') . '" onmouseover="Tip(\''; 
				$file_content .= '<b>'.$r_log['user_name'].'</b>:<br/>';
				$data = cleanup_text(str_replace("\r\n", " ", $r_log['log_text']));
				$file_content .= str_replace("\n", " ",$data);
				$file_content .= '\',OFFSETY, 25, OFFSETX, -135, PADDING,5, WIDTH,280,SHADOW,true)" onmouseout="UnTip()"><img src="tpl/stdstyle/images/' . $r_log['icon_small'] . '" border="0" alt=""/></a></b></td>';
				$file_content .= '<td>&nbsp;&nbsp;<b><a class="links" href="viewprofile.php?userid=' . htmlspecialchars($r_log['user_id'], ENT_COMPAT, 'UTF-8') . '">' . htmlspecialchars($r_log['user_name'], ENT_COMPAT, 'UTF-8') . '</a></b></td>';

					}				
			mysql_free_result($rs_log);
			$file_content .= "</tr>";
		}
		mysql_free_result($rs);
	        tpl_set_var('file_content',$file_content);

		$rs = sql('SELECT COUNT(*) `count` FROM (local_caches'.$user_id.' caches)');
		$r = sql_fetch_array($rs);
		$count = $r['count'];
		mysql_free_result($rs);

		$frompage = $startat / 100 - 3;
		if ($frompage < 1) $frompage = 1;

		$topage = $frompage + 8;
		if (($topage - 1) * $perpage > $count)
			$topage = ceil($count / $perpage);

		$thissite = $startat / 100 + 1;

		$pages = '';
		if ($startat > 0)
			$pages .= '<a href="myn_topcaches.php?startat=0">{first_img}</a> <a href="myn_topcaches.php?startat=' . ($startat - 100) . '">{prev_img}</a> ';
		else
			$pages .= '{first_img_inactive} {prev_img_inactive} ';

		for ($i = $frompage; $i <= $topage; $i++)
		{
			if ($i == $thissite)
				$pages .= $i . ' ';
			else
				$pages .= '<a href="myn_topcaches.php?startat=' . ($i - 1) * $perpage . '">' . $i . '</a> ';
		}
		if ($thissite < $topage)
			$pages .= '<a href="myn_topcaches.php?startat=' . ($startat + $perpage) . '">{next_img}</a> <a href="myn_topcaches.php?startat=' . (ceil($count / 100) * 100 - 100) . '">{last_img}</a>';
		else
			$pages .= '{next_img_inactive} {last_img_inactive}';

		$pages = mb_ereg_replace('{prev_img}', $prev_img, $pages);
		$pages = mb_ereg_replace('{next_img}', $next_img, $pages);
		$pages = mb_ereg_replace('{last_img}', $last_img, $pages);
		$pages = mb_ereg_replace('{first_img}', $first_img, $pages);
		
		$pages = mb_ereg_replace('{prev_img_inactive}', $prev_img_inactive, $pages);
		$pages = mb_ereg_replace('{next_img_inactive}', $next_img_inactive, $pages);
		$pages = mb_ereg_replace('{first_img_inactive}', $first_img_inactive, $pages);
		$pages = mb_ereg_replace('{last_img_inactive}', $last_img_inactive, $pages);
		
		tpl_set_var('pages', $pages);
	}

	//make the template and send it out
	tpl_BuildTemplate();
?>
