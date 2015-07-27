<?php
function getMapType($value)
{
	switch( $value ) 
	{
		case 0:
			return "G_NORMAL_MAP";
		case 1:
			return "G_SATELLITE_MAP";
		case 2:
			return "G_HYBRID_MAP";
		case 3:
			return "G_PHYSICAL_MAP";
		default:
			return "G_NORMAL_MAP";
	}
}



require_once('./lib/common.inc.php');
	//Preprocessing
	if ($error == false)
	{
		//user logged in?
		if ($usr == false)
		{
		    $target = urlencode(tpl_get_current_page());
		    tpl_redirect('login.php?target='.$target);
		}
		else
		{

$tplname = 'logmap';
tpl_set_var('bodyMod', ' onload="initialize()" onunload="GUnload()"');;
global $usr;
global $get_userid;

	        function cleanup_text($str)
        {

          $str = strip_tags($str, "<li>");
	  $from[] = '<p>&nbsp;</p>'; $to[] = '';
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
           $from[] = '('; $to[] = '[';
 $from[] = ')'; $to[] = ']';
          $from[] = '&'; $to[] = '';
          $from[] = '\''; $to[] = '';
          $from[] = '"'; $to[] = '';
          $from[] = '<'; $to[] = '';
          $from[] = '>'; $to[] = '';
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

//user logged in?
	session_start();


//	$uLat = sqlValue("SELECT `user`.`latitude`  FROM `user` WHERE `user_id`='".sql_escape($usr[userid]) ."'", 0);
//	$uLon = sqlValue("SELECT `user`.`longitude`  FROM `user` WHERE `user_id`='". sql_escape($usr[userid]) ."'",0);

//	if (($uLat==NULL || $uLat==0) && ($uLon==NULL || $uLon==0)) {
	
	tpl_set_var('mapzoom', 6);
	tpl_set_var('mapcenterLat', 52.057);
	tpl_set_var('mapcenterLon', 19.07);	

//	} else {
//	tpl_set_var('mapzoom', 11);
//	tpl_set_var('mapcenterLat', $uLat);
//	tpl_set_var('mapcenterLon', $uLon);
//}
	
	$rs = sql("SELECT `cache_logs`.`id`
			FROM `cache_logs`, `caches`
			WHERE `cache_logs`.`cache_id`=`caches`.`cache_id`
				AND `cache_logs`.`deleted`=0 
				AND `caches`.`status` IN (1, 2, 3) AND `cache_logs`.`type` IN (1,2,3,4,5)
			ORDER BY  `cache_logs`.`date_created` DESC
			LIMIT 100");
	$log_ids = '';

	if (mysql_num_rows($rs)==0) $log_ids = '0';

	for ($i = 0; $i < mysql_num_rows($rs); $i++)
	{
		$record = sql_fetch_array($rs);
		if ($i > 0)
		{
			$log_ids .= ', ' . $record['id'];
		}
		else
		{
			$log_ids = $record['id'];
		}
	}
	mysql_free_result($rs);



$rscp = sql("SELECT cache_logs.id, cache_logs.cache_id AS cache_id,
	                          cache_logs.type AS log_type,
	                          cache_logs.date AS log_date,
							cache_logs.user_id AS luser_id,
	                          caches.name AS cache_name,
							caches.wp_oc AS wp,
	                         user.username AS username,
							`caches`.`latitude` `latitude`,
							`caches`.`longitude` `longitude`,
							  caches.type AS cache_type,
							  cache_type.icon_small AS cache_icon_small,
							  log_types.icon_small AS icon_small
							FROM (cache_logs INNER JOIN caches ON (caches.cache_id = cache_logs.cache_id)) INNER JOIN user ON (cache_logs.user_id = user.user_id) INNER JOIN log_types ON (cache_logs.type = log_types.id) INNER JOIN cache_type ON (caches.type = cache_type.id)
							WHERE cache_logs.deleted=0 AND cache_logs.id IN (" . $log_ids . ") 
							AND cache_logs.cache_id=caches.cache_id
							AND caches.status<> 4 AND caches.status<> 5 AND caches.status<> 6 
							GROUP BY cache_logs.id
							ORDER BY cache_logs.date_created DESC");




			$point="";
			for ($i = 0; $i < mysql_num_rows($rscp); $i++)
			{
				$record = sql_fetch_array($rscp);
				$username=$record['username'];
				$y=$record['longitude'];
				$x=$record['latitude'];
				$log_date=htmlspecialchars(date("Y-m-d", strtotime($record['log_date'])), ENT_COMPAT, 'UTF-8');
				$cache_name=cleanup_text($record['cache_name']);

			$point .=" var point = new GLatLng(" . $x . "," . $y . ");\n";
			$number=$i+1;

			$point .="var marker".$number." = new GMarker(point,icon".$record['log_type']."); map0.addOverlay(marker".$number.");\n\n";

			$point .="GEvent.addListener(marker".$number.", \"click\", function() {marker".$number.".openInfoWindowHtml('<table><tr><td><img src=\"tpl/stdstyle/images/".$record['cache_icon_small']."\" border=\"0\" alt=\"\" title=\"geocache\"/><b>&nbsp;<a class=\"links\" href=\"viewcache.php?wp=".$record['wp']."\">".$record['wp'].": ".$cache_name."</a></td></tr><tr><td><a class=\"links\" href=\"viewlogs.php?logid=".$record['id']."\"><img src=\"tpl/stdstyle/images/" . $record['icon_small'] ."\" border=\"0\" alt=\"\" /></a> <b>przez</b> <a class=\"links\" href=\"viewprofile.php?userid=".$record['luser_id']."\">".$username."</a> <b>dnia: ". $log_date . "</b></td></tr></table>');});\n";
			}



		tpl_set_var('points', $point);	

	
	tpl_set_var("map_type", "G_NORMAL_MAP");
	;

		mysql_free_result($rscp);

	/*SET YOUR MAP CODE HERE*/
	tpl_set_var('cachemap_header', '<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key='.$googlemap_key.'" type="text/javascript"></script>');

}
}
	tpl_BuildTemplate(); 

?>
