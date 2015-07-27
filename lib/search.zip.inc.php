<?php
        /***************************************************************************
                ./lib/search.zip.inc.php
        -------------------
                        begin                : January 28 2012
                        copyright            : (C) 2012 The OpenCaching Group
                        forum contact at     : http://forum.opencaching.pl

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

                Unicode Reminder ??

                Garmin zip search output (gpx + images for garmin devices)
                based on search.*.inc.php
                by Limak (opencaching.pl)

        ****************************************************************************/
setlocale(LC_TIME, 'pl_PL.UTF-8');

        global $content, $bUseZip, $sqldebug, $usr, $hide_coords;
	set_time_limit(1800);
    

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

		// cleanup (old zipcontent lingers if zip-download is cancelled by user)		
		sql('DROP TEMPORARY TABLE IF EXISTS `zipcontent`');
					// temporäre tabelle erstellen
					sql('CREATE TEMPORARY TABLE `zipcontent` ' . $sql . $sqlLimit);
					$rsCount = sql('SELECT COUNT(*) `count` FROM `zipcontent`');
					$rCount = sql_fetch_array($rsCount);
					mysql_free_result($rsCount);
					
					$caches_count = $rCount['count'];
					
					if ($rCount['count'] == 1)
					{
									$rsName = sql('SELECT `caches`.`wp_oc` `wp_oc` FROM `zipcontent`, `caches` WHERE `zipcontent`.`cache_id`=`caches`.`cache_id` LIMIT 1');
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

		//$bUseZip = ($rCount['count'] > 50);
		//$bUseZip = $bUseZip || (isset($_REQUEST['zip']) && ($_REQUEST['zip'] == '1'));
		$bUseZip = false;
					
		// ok, ausgabe ...
		
		
					// =======================================
					// I don't know what code above doing (it's horrible and I don't have enough time to analyze this code), 
					// so I just modify existing piece of code from other output search.*.inc.php file.
					// == Limak (28.01.2012) ==
					
					// change this, only if OKAPI changes this value (in okapi/caches/formatters/garmin.php file)!
					if(isset($_REQUEST['okapidebug'])) $okapi_max_caches = 500; else $okapi_max_caches = 50;
					
					//zippart param in request is used for split ZIP files
					if(!isset($_REQUEST['zippart'])) $_REQUEST['zippart'] = 0;
					$zippart = abs(intval($_REQUEST['zippart'])) + 0;
					$startat = ($zippart-1)*$okapi_max_caches;
					
					// too much caches for one zip file - generate webpage instead
					if(($caches_count > $okapi_max_caches) && ($zippart==0 || $startat>=$caches_count)) 
					{
						$tplname = 'garminzip';
						
						tpl_set_var('zip_total_cache_count', $caches_count);
						tpl_set_var('zip_max_count', $okapi_max_caches);
						
						$links_content = '';
						$forlimit=intval($caches_count/$okapi_max_caches)+1;
						for($i=1;$i<=$forlimit;$i++)
						{
						$zipname='ocpl'.$options['queryid'].'.zip?startat=0&count=max&zip=1&zippart='.$i.(isset($_REQUEST['okapidebug'])?'&okapidebug':'');
						$links_content .= '<li><a class="links" href="'.$zipname.'" title="Garmin ZIP file (part '.$i.')">'.$sFilebasename.'-'.$i.'.zip</a></li>';
						}
						tpl_set_var('zip_links', $links_content);
						tpl_BuildTemplate();
					} 
					else // caches are less or equals then okapi_max_caches in one ZIP file limit - okey, return ZIP file
					{
						// use 'LIMIT' only if it's needed
						if($caches_count > $okapi_max_caches) $ziplimit = ' LIMIT '.$startat.','.$okapi_max_caches;
						// OKAPI need only waypoints
						$rs = sql('SELECT `caches`.`wp_oc` `wp_oc` FROM `zipcontent`, `caches` WHERE `zipcontent`.`cache_id`=`caches`.`cache_id`'.$ziplimit);

						$waypoints_tab = array();
						while($r = sql_fetch_array($rs))
						{
							$waypoints_tab[] = $r['wp_oc'];	
						}
						$waypoints = implode("|",$waypoints_tab);
					
						mysql_free_result($rs);
					
						// I don't know what this line doing, but other 'search.*.inc.php' files include this.
						if ($sqldebug == true) sqldbg_end();
					
						// OKAPI including
						require_once($rootpath.'okapi/core.php');
						require_once($rootpath.'okapi/service_runner.php');
					
						
						try {
							
							//$request->consumer, $request->token
							$OkapiCall =  \okapi\OkapiServiceRunner::call('services/caches/formatters/garmin', 
									new \okapi\OkapiInternalRequest(new \okapi\OkapiInternalConsumer(), new \okapi\OkapiInternalAccessToken($usr['userid']), 
										array('cache_codes' => $waypoints,'langpref' => 'pl')));				
									
							// own header parametres
							$OkapiCall->content_type = 'application/zip';
							$OkapiCall->content_disposition = 'Content-Disposition: attachment; filename=' . $sFilebasename . (($zippart!=0)?'-'.$zippart:'') . '.zip';
						
							// ->display() send header() and prints ZIP file
							$OkapiCall->display();
							
						}
						catch (Exception $e) {
							header('content-type: plain/text');
							//$tplname = 'error';
							//tpl_set_var('tplname', 'search.php');
							//tpl_set_var('error_msg', $e);
							//tpl_BuildTemplate();
							echo $e;
							exit;
						}
						exit;
						
					}
				}
					
					// =======================================
					
					function convert_string($str)
					{
									$newstr = iconv("UTF-8", "ASCII//TRANSLIT", $str);
									if ($newstr == false)
													return "--- charset error ---";
									else
													return $newstr;
					}

					function append_output($str)
					{
									global $content, $bUseZip, $sqldebug;
									if ($sqldebug == true) return;

									if ($bUseZip == true)
													$content .= $str;
									else
													echo $str;
					}
				

        /*
Funkcja do konwersji polskich znakow miedzy roznymi systemami kodowania.
Zwraca skonwertowany tekst.

Argumenty:
$source - string - źródłowe kodowanie
$dest - string - źródłowe kodowanie
$tekst - string - tekst do konwersji

Obsługiwane formaty kodowania to:
POLSKAWY (powoduje zamianę polskich liter na ich łacińskie odpowiedniki)
ISO-8859-2
WINDOWS-1250
UTF-8
ENTITIES (zamiana polskich znaków na encje html)

Przyklad:
echo(PlConvert('UTF-8','ISO-8859-2','Zażółć gęślą jaźń.'));
*/
function PlConvert($source,$dest,$tekst)
{
    $source=strtoupper($source);
    $dest=strtoupper($dest);
    if($source==$dest) return $tekst;

    $chars['POLSKAWY']    =array('a','c','e','l','n','o','s','z','z','A','C','E','L','N','O','S','Z','Z');
    $chars['ISO-8859-2']  =array("\xB1","\xE6","\xEA","\xB3","\xF1","\xF3","\xB6","\xBC","\xBF","\xA1","\xC6","\xCA","\xA3","\xD1","\xD3","\xA6","\xAC","\xAF");
    $chars['WINDOWS-1250']=array("\xB9","\xE6","\xEA","\xB3","\xF1","\xF3","\x9C","\x9F","\xBF","\xA5","\xC6","\xCA","\xA3","\xD1","\xD3","\x8C","\x8F","\xAF");
    $chars['UTF-8']       =array('ą','ć','ę','ł','ń','ó','ś','ź','ż','Ą','Ć','Ę','Ł','Ń','Ó','Ś','Ź','Ż');
    $chars['ENTITIES']    =array('ą','ć','ę','ł','ń','ó','ś','ź','ż','Ą','Ć','Ę','Ł','Ń','Ó','Ś','Ź','Ż');

    if(!isset($chars[$source])) return false;
    if(!isset($chars[$dest])) return false;

    return str_replace($chars[$source],$chars[$dest],$tekst);
}

?>
