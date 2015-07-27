<?php
// error_reporting(0);

/***************************************************************************
	*
	*   This program is free software; you can redistribute it and/or modify
	*   it under the terms of the GNU General Public License as published by
	*   the Free Software Foundation; either version 2 of the License, or
	*   (at your option) any later version.
	*
	*    Unicode Reminder ąśłó
	***************************************************************************/

	/* todo:
	      create and set up 4 template selector with wybor_WE wybor_NS. 
		  
    */
	function isGeokretInCache($cacheid)
	{
	
		$sql = "SELECT wp_oc, wp_gc, wp_nc,wp_ge,wp_tc FROM caches WHERE cache_id = '".sql_escape(intval($cacheid))."'";
		$cache_record = mysql_fetch_array(mysql_query($sql));
		// get cache waypoint
		$cache_wp = '';
		if( $cache_record['wp_oc'] != '' ) 
			$cache_wp = $cache_record['wp_oc'];
		else if( $cache_record['wp_gc'] != '' ) 
			$cache_wp = $cache_record['wp_gc'];
		else if( $cache_record['wp_nc'] != '' ) 
			$cache_wp = $cache_record['wp_nc'];
		else if( $cache_record['wp_ge'] != '' ) 
			$cache_wp = $cache_record['wp_ge'];		
		else if( $cache_record['wp_tc'] != '' ) 
			$cache_wp = $cache_record['wp_tc'];		

		$geokret_sql = "SELECT id FROM gk_item WHERE id IN (SELECT id FROM gk_item_waypoint WHERE wp = '".sql_escape($cache_wp)."') AND stateid<>1 AND stateid<>4 AND stateid <>5 AND typeid<>2";
		$geokret_query = sql($geokret_sql);
		if (mysql_num_rows($geokret_query) == 0)
		{
			// no geokrets in this cache
			return 0;
		}
		else
			return 1;
		
	}
	

    // ini_set('display_errors', 1);
    // error_reporting(E_ALL);
	
	//prepare the templates and include all neccessary
	require_once('./lib/common.inc.php');
	require($stylepath.'/smilies.inc.php');


	$no_tpl_build = false;
	//Preprocessing
	if ($error == false)
	{
		//cacheid
		$cache_id = 0;
		if (isset($_REQUEST['cacheid']))
		{
			$cache_id = $_REQUEST['cacheid'];
		}

		//user logged in?
		if ($usr == false)
		{
		    $target = urlencode(tpl_get_current_page());
		    tpl_redirect('login.php?target='.$target);
		}
		else
		{
			//set here the template to process
			$tplname = 'log_cache';

			require($stylepath . '/log_cache.inc.php');
			require_once($rootpath . 'lib/caches.inc.php');
			require($stylepath.'/rating.inc.php');

			$cachename = '';
			if ($cache_id != 0)
			{
				//get cachename
				$rs = sql("SELECT `name`, `cache_id`, `user_id`, `logpw`, `wp_oc`,`wp_gc`, `wp_nc`,`wp_ge`,`wp_tc`, `type`, `status` FROM `caches` WHERE `cache_id`='&1'", $cache_id);

				if (mysql_num_rows($rs) == 0)
				{
					$cache_id = 0;
				}
				else
				{
					$record = sql_fetch_array($rs);
					// only OC Team member and the owner allowed to make logs to not published caches
					if ($record['user_id'] == $usr['userid'] || ($record['status'] != 5 && $record['status'] != 4 && $record['status'] != 6) || $usr['admin'])
					{
						$cachename = $record['name'];
						$cache_user_id = $record['user_id'];
						$use_log_pw = (($record['logpw'] == NULL) || ($record['logpw'] == '')) ? false : true;
						if ($use_log_pw) $log_pw = $record['logpw'];
						$wp_gc = $record['wp_gc'];
						$wp_nc = $record['wp_nc'];
						$cache_type = $record['type'];
					}
					else
					{
						$cache_id = 0;
					}
				}
			}

			if ($cache_id != 0)
			{
				$all_ok = false;
				
				$log_text  = isset($_POST['logtext']) ? ($_POST['logtext']) : '';
				// $log_type = isset($_POST['logtype']) ? ($_POST['logtype']+0) : $default_logtype_id;
				$log_type = isset($_POST['logtype']) ? ($_POST['logtype']+0) : -2;
				$log_date_min = isset($_POST['logmin']) ? ($_POST['logmin']+0) : date('i');
				$log_date_hour = isset($_POST['loghour']) ? ($_POST['loghour']+0) : date('H');
				$log_date_day = isset($_POST['logday']) ? ($_POST['logday']+0) : date('d');
				$log_date_month = isset($_POST['logmonth']) ? ($_POST['logmonth']+0) : date('m');
				$log_date_year = isset($_POST['logyear']) ? ($_POST['logyear']+0) : date('Y');
				$top_cache = isset($_POST['rating']) ? $_POST['rating']+0 : 0;
				
				// mobilne by Łza
				$wybor_NS   = isset($_POST['wybor_NS']) ? $_POST['wybor_NS'] : 0;
				$wsp_NS_st  = isset($_POST['wsp_NS_st']) ? $_POST['wsp_NS_st'] : null;
				$wsp_NS_min = isset($_POST['wsp_NS_min']) ? $_POST['wsp_NS_min'] : null;
				$wybor_WE   = isset($_POST['wybor_WE']) ? $_POST['wybor_WE'] : 0;
				$wsp_WE_st  = isset($_POST['wsp_WE_st']) ? $_POST['wsp_WE_st'] : null;
				$wsp_WE_min = isset($_POST['wsp_WE_min']) ? $_POST['wsp_WE_min'] : null;
								
				$is_top = sqlValue("SELECT COUNT(`cache_id`) FROM `cache_rating` WHERE `user_id`='" . sql_escape($usr['userid']) . "' AND `cache_id`='" . sql_escape($cache_id) . "'", 0);
				// check if user has exceeded his top5% limit
				$user_founds = sqlValue("SELECT `founds_count` FROM `user` WHERE `user_id`='" .  sql_escape($usr['userid']) . "'", 0);
				$user_tops = sqlValue("SELECT COUNT(`user_id`) FROM `cache_rating` WHERE `user_id`='" .  sql_escape($usr['userid']) . "'", 0);

				if ($is_top == 0)
				{
					if (($user_founds * rating_percentage/100) < 1)
					{
						$top_cache = 0;
						$anzahl = (1 - ($user_founds * rating_percentage/100)) / (rating_percentage/100);
						if ($anzahl > 1)
						{
							$rating_msg = mb_ereg_replace('{anzahl}', "$anzahl Founds", $rating_too_few_founds);
						}
						else
						{
							$rating_msg = mb_ereg_replace('{anzahl}', "$anzahl Found", $rating_too_few_founds);
						}
					}
					elseif ($user_tops < floor($user_founds * rating_percentage/100))
					{
						// this user can recommend this cache
						if ($cache_user_id != $usr['userid']) 
						{
							if( $top_cache )
								$rating_msg = mb_ereg_replace('{chk_sel}', ' checked', $rating_allowed.'<br />'.$rating_stat);
							else
								$rating_msg = mb_ereg_replace('{chk_sel}', '', $rating_allowed.'<br />'.$rating_stat);
						}
						else 
						{
							$rating_msg = mb_ereg_replace('{chk_dis}', ' disabled', $rating_own.'<br />'.$rating_stat);
						}
						$rating_msg = mb_ereg_replace('{max}', floor($user_founds * rating_percentage/100), $rating_msg);
						$rating_msg = mb_ereg_replace('{curr}', $user_tops, $rating_msg);
					}
					else
					{
						$top_cache = 0;
						$anzahl = ($user_tops + 1 - ($user_founds * rating_percentage/100)) / (rating_percentage/100);
						if ($anzahl > 1)
						{
							$rating_msg = mb_ereg_replace('{anzahl}', "$anzahl Founds", $rating_too_few_founds);
						}
						else
						{
							$rating_msg = mb_ereg_replace('{anzahl}', "$anzahl Found", $rating_too_few_founds);
						}
						$rating_msg .= '<br />'.$rating_maxreached;
					}
				}
				else
				{
					if ($cache_user_id != $usr['userid']) {
						$rating_msg = mb_ereg_replace('{chk_sel}', ' checked', $rating_allowed.'<br />'.$rating_stat);
					} else {
						$rating_msg = mb_ereg_replace('{chk_dis}', ' disabled', $rating_own.'<br />'.$rating_stat);
					}
					$rating_msg = mb_ereg_replace('{max}', floor($user_founds * rating_percentage/100), $rating_msg);
					$rating_msg = mb_ereg_replace('{curr}', $user_tops, $rating_msg);
				}

// sp2ong 28.I.2010 recommendation all caches except events
				if ( $cache_type != 6 ) { 
				tpl_set_var('rating_message', mb_ereg_replace('{rating_msg}', $rating_msg, $rating_tpl)); 
				} else {
				tpl_set_var('rating_message', ""); 			
				}
				// print mb_ereg_replace('{rating_msg}', $rating_msg, $rating_tpl); exit;	
				// enable backscoring
				$sql = "SELECT count(*) FROM scores WHERE user_id='".sql_escape($usr['userid'])."' AND cache_id='".sql_escape(intval($cache_id))."'";

				// disable backscoring
				// $sql = "SELECT count(*) FROM cache_logs WHERE `deleted`=0 AND type='1' AND user_id='".sql_escape($usr['userid'])."' AND cache_id='".sql_escape(intval($cache_id))."'";
				$is_scored_query = mysql_query($sql);
//				mysql_result($is_scored_query,0);
				if( mysql_result($is_scored_query,0) == 0 && $usr['userid'] != $record['user_id'])
				{
					//$color_table = array("#FF0000","#FF6600","#FF9900","#99FF00","#66FF00","#33FF00","#00FF00");
					$score = "<select name='r'>
						";
					if( isset($_POST['r']) && $_POST['r'] == -10)
						$checked = " selected";
					else
						$checked = "";
						
					$score .= "<option value='-10' $checked>".tr('do_not_rate')."</option>";
					for( $score_radio=$MIN_SCORE;$score_radio<=$MAX_SCORE;$score_radio++)
					{
						if( isset($_POST['r']) && $score_radio == $_POST['r'] )
							$checked = " selected";
						else
							$checked = "";
						$score .= "<option value='".$score_radio."' $checked>".$ratingDesc[$score_radio]."</option>";
					}
					$score .= "</select>";
					/*
					for( $score_radio=$MIN_SCORE;$score_radio<=$MAX_SCORE;$score_radio++)
						$score .= "<td width='14%' align='center'><label style='color:#ffffff;font-weight:bold;font-size:12px;' for='r$score_radio'>".$ratingDesc[$score_radio-1]."</label>";
					$score .= "</tr></table>";
					$score .= "<input style='border-style:none;background : transparent; color : black' type='radio' name='r' id='r-10' value='-10'><label for='r-10'>".tr('do_not_rate')."</label>";
					*/
					$score_header = tr('rate_cache');
					$display = "block";
				}
				else
				{
					$score = "";
					$score_header = "";
					$display = "none";
				}
				tpl_set_var('score', $score );
				tpl_set_var('score_header', $score_header);
				tpl_set_var('display', $display);
				
				// check if geokret is in this cache
				if( isGeokretInCache($cache_id) )
				{
					tpl_set_var('log_geokret', "<br /><img src=\"images/gk.png\" class=\"icon16\" alt=\"\" title=\"GeoKrety\" align=\"middle\" />&nbsp;<b>".tr('geokret_log')." <a href='http://geokrety.org/ruchy.php'>geokrety.org</a></b>");
				}
				else
					tpl_set_var('log_geokret', "");
				
				// descMode auslesen, falls nicht gesetzt aus dem Profil laden
				if (isset($_POST['descMode']))
					$descMode = $_POST['descMode']+0;
				else
				{
					if (sqlValue("SELECT `no_htmledit_flag` FROM `user` WHERE `user_id`='" .  sql_escape($usr['userid']) . "'", 1) == 1)
						$descMode = 1;
					else
						$descMode = 3;
				}
				if (($descMode < 1) || ($descMode > 3)) $descMode = 3;

				// fuer alte Versionen von OCProp
				if (isset($_POST['submit']) && !isset($_POST['version2']))
				{
					$descMode = 1;
					$_POST['submitform'] = $_POST['submit'];
					$log_text = iconv("ISO-8859-1", "UTF-8", $log_text);
				}

				if ($descMode != 1)
				{
					// check input
					require_once($rootpath . 'lib/class.inputfilter.php');
					$myFilter = new InputFilter($allowedtags, $allowedattr, 0, 0, 1);
					$log_text = $myFilter->process($log_text);
				}
				else
				{
					// escape text
					//if( $all_ok )
						$log_text = nl2br(htmlspecialchars($log_text, ENT_COMPAT, 'UTF-8'));
					//else
						//$log_text = strip_tags(htmlspecialchars($log_text, ENT_COMPAT, 'UTF-8'));
					
				}
                
				//setting tpl messages if they should be not visible.	
				tpl_set_var('lat_message', '');
                tpl_set_var('lon_message', '');
				
				//validate data
				if (is_numeric($log_date_month) && is_numeric($log_date_day) && is_numeric($log_date_year) && is_numeric($log_date_hour)&& is_numeric($log_date_min))
				{
					$date_not_ok = (checkdate($log_date_month, $log_date_day, $log_date_year) == false || $log_date_hour < 0 || $log_date_hour > 23 || $log_date_min < 0 || $log_date_min > 60);
					if($date_not_ok == false)
					{
						if (isset($_POST['submitform']))
						{
							if(mktime($log_date_hour, $log_date_min,0, $log_date_month, $log_date_day, $log_date_year)>=mktime())
							{
								$date_not_ok = true;
							}
							else
							{
								$date_not_ok = false;
							}
						}
					}
				}
				else
				{
					$date_not_ok = true;
				}

				if ($cache_type == 6) // (type 6 - Event cache)
				{
					switch($log_type)
					{
						case 1:
						case 2:
							$logtype_not_ok = true;
							break;
						default:
							$logtype_not_ok = false;
							break;
					}
				}
				else
				{
					switch($log_type)
					{
						case 7:
						case 8:
							$logtype_not_ok = true;
							break;
						default:
							$logtype_not_ok = false;
							break;
					}
				}

				if ($log_type < 0) $logtype_not_ok = true;
				
				if ($log_type == 4) 
				{
				 $coords_not_ok = validate_coords($wsp_NS_st, $wsp_NS_min, $wsp_WE_st, $wsp_WE_min, $wybor_WE, $wybor_NS, $error_coords_not_ok);
				}

				
				// not a found log? then ignore the rating
				$sql = "SELECT count(*) as founds FROM `cache_logs` WHERE `deleted`=0 AND user_id='".sql_escape($usr['userid'])."' AND cache_id='".sql_escape($cache_id)."' AND type='1'";
				$res = mysql_fetch_array(mysql_query($sql));
				if( $res['founds'] == 0 )
					if ($log_type != 1 && $log_type != 7 /* && $log_type != 3 */)
					{
						$top_cache = 0;
					}

				$pw_not_ok = false;
				if (isset($_POST['submitform']))
				{
					$all_ok = ($date_not_ok == false) && ($logtype_not_ok == false) && ($coords_not_ok == false);

					if (($all_ok) && ($use_log_pw) && $log_type == 1)
					{
						if (isset($_POST['log_pw']))
						{
							if (mb_strtolower($log_pw) != mb_strtolower($_POST['log_pw']))
							{
								$pw_not_ok = true;
								$all_ok = false;
							}
						}
						else
						{
							$pw_not_ok = true;
							$all_ok = false;
						}
					}
				}

				if( isset($_POST['submitform']) && ($log_type == 1 || $log_type == 7))
				{
					// fix
					if( $log_type == 7 && $usr['userid'] == $record['user_id'] )
					{
						$_POST['r'] = -10;
					}
					if( $_POST['r'] != -10 )
					{
						$_POST['r'] =  new2oldscore(intval($_POST['r'])); // convert to old score format
					}
					
					if( $_POST['r'] == -10 || ($_POST['r'] >= -3 && $_POST['r'] <= 3))
					{
						$score_not_ok = false;
					}
					else
					{
						$score_not_ok = true;
						$all_ok = false;
					}
				}
				else
				{
					$score_not_ok = false;
				}
				
				if( isset($_POST['submitform']) && ($all_ok == true) )
				{
					if( $_POST['r'] >= -3 && $_POST['r'] <= 3 )
					{
						// oceniono skrzynkę
						$sql = "SELECT count(*) FROM scores WHERE user_id='".sql_escape($usr['userid'])."' AND cache_id='".sql_escape(floatval($cache_id))."'";
						$is_scored_query = mysql_query($sql);
						if( mysql_result($is_scored_query,0) == 0 && $usr['userid'] != $record['user_id'])
						{					
							$sql = "UPDATE caches SET score=(score*votes+".sql_escape(floatval($_POST['r'])).")/(votes+1), votes=votes+1 WHERE cache_id=".sql_escape($cache_id);
							mysql_query($sql);
							$sql = "INSERT INTO scores (user_id, cache_id, score) VALUES('".sql_escape($usr['userid'])."', '".sql_escape(floatval($cache_id))."', '".sql_escape(floatval($_POST['r']))."')";
							mysql_query($sql);						
						}
					}
					else
					{
						// nie wybrano opcji oceny
						
					}
					$log_date = date('Y-m-d H:i:s', mktime($log_date_hour, $log_date_min,0, $log_date_month, $log_date_day, $log_date_year));

					$log_uuid = create_uuid();
					
					//add logentry to db
  				    
					if ($log_type < 0)
					{
					 // nie wybrano typu logu

					}
                    // if comment is empty, then do not insert data into db
					elseif (!($log_type == 3 && $log_text == "")) 
					{
						sql("INSERT INTO `cache_logs` (`id`, `cache_id`, `user_id`, `type`, `date`, `text`, `text_html`, `text_htmledit`, `date_created`, `last_modified`, `uuid`, `node`)
										 VALUES ('', '&1', '&2', '&3', '&4', '&5', '&6', '&7', NOW(), NOW(), '&8', '&9')",
										 $cache_id, $usr['userid'], $log_type, $log_date, $log_text, (($descMode != 1) ? 1 : 0), (($descMode == 3) ? 1 : 0), $log_uuid, $oc_nodeid);
                        
                        
						// mobline by Łza (mobile caches)
						
						// insert to database.
						// typ kesza mobilna 8, typ logu == 4
						if ($log_type == 4) // typ logu 4 - przeniesiona
						   {
						     ini_set('display_errors', 1);   
						     error_reporting(E_ALL);
							 
							 // id of last sql entery
							 $last_id_4_mobile_moved = mysql_insert_id();
						   
							 // converting from HH MM.MMM to DD.DDDDDD
						     $wspolrzedneNS = $wsp_NS_st + round($wsp_NS_min,3) / 60;
						     if ($wybor_NS == 'S') $wspolrzedneNS = -$wspolrzedneNS;						 
							 $wspolrzedneWE = $wsp_WE_st + round($wsp_WE_min,3) / 60;
						     if ($wybor_WE  == 'W') $wspolrzedneWE = -$wspolrzedneWE; 
							
							 // if it is first log "cache mooved" then move start coordinates from table caches
							 // to table cache_moved and create log type cache_moved, witch description 
							 // "depart point" or something like this. 
							 
							 //$count = mysql_result($result, 0);

							 $is_any_cache_movedlog = mysql_result(sql("SELECT COUNT(*) FROM `cache_moved` WHERE `cache_id` ='&1'", sql_escape($cache_id)),0);
							 
							 if ($is_any_cache_movedlog == 0)
							    {
								 
								 $tmp_move_query = sql("SELECT `user_id`, `longitude`, `latitude`, `date_hidden` FROM `caches` WHERE `cache_id` ='&1'", sql_escape($cache_id));
								 $tmp_move_data = mysql_fetch_array($tmp_move_query);
								 
								 // create initial log in cache_logs and copy coords to table caches
								 $init_log_desc = tr('log_mobile_init');
								 $init_log_latitude = $tmp_move_data['latitude'];
								 $init_log_longitude = $tmp_move_data['longitude'];
								 $init_log_userID = $tmp_move_data['user_id'];
								 $init_log_date = $tmp_move_data['date_hidden'];
								   $tmp_uuid = mysql_fetch_array(sql("SELECT `uuid` FROM `user` WHERE `user_id` = $init_log_userID"));
								   $init_log_uuid = $tmp_uuid['uuid'];
								   unset($tmp_uuid);
								 	
								 sql("INSERT INTO `cache_logs` (`id`, `cache_id`, `user_id`, `type`, `date`, `text`, `text_html`, `text_htmledit`, `date_created`, `last_modified`, `uuid`, `node`)
									  VALUES                   ('',   '&1',       '&2',      '&3',   '&4',   '&5',   '&6',        '&7',             NOW(),           NOW(),          '&8',   '&9')",
										 $cache_id, 
										 $init_log_userID, 
										 4, 
										 $init_log_date, 
										 $init_log_desc, 
										 0, 
										 0, 
										 $init_log_uuid, 
										 $oc_nodeid
										 );
									$last_id_4_init_log = mysql_insert_id();
									
									sql("INSERT INTO `cache_moved`(`id`, 
						                             `cache_id`, 
													 `user_id`, 
													 `log_id`, 
													 `date`, 
													 `longitude`, 
													 `latitude`,
													 `km`) 
										     VALUES ('', 
											         '&1', 
													 '&2', 
													 '&3', 
													 '&4', 
													 '&5', 
													 '&6',
													 '0'
											        )",	
											         sql_escape($cache_id),
													 $init_log_userID,
													 $last_id_4_init_log,
													 $init_log_date,
													 $init_log_longitude,
													 $init_log_latitude 
												    );
								 $dystans=sprintf("%.2f",calcDistance($init_log_latitude,$init_log_longitude,$wspolrzedneNS,$wspolrzedneWE)); 
							    }
							 else
							    {
								 // $log_date - data+czas logu
							     // calculate distance from piervous
								 $ostatnie_dane_mobilniaka = mysql_fetch_array(mysql_query("
								      SELECT 
								              `id`, 
											  `user_id`, 
											  `log_id`, 
											  `date`, 
											  `longitude`, 
											  `latitude`, 
											  `km` 
										FROM  `cache_moved` 
										WHERE `cache_id` = '$cache_id'
										
										ORDER BY id DESC
										LIMIT 1
										"										
										));
								 // jeśli beżący (właśnie wpisywany) log jest ostatnim, 
                                 // dystans zostanie wpisany do bazy. w przeciwnym wypadku
                                 // zmienna zostanie zastąpiona w if-ie 								 
								 $dystans=sprintf("%.2f",calcDistance($ostatnie_dane_mobilniaka['latitude'],$ostatnie_dane_mobilniaka['longitude'],$wspolrzedneNS,$wspolrzedneWE));	
										// check if log date is beetwen, or last
										if ($log_date <= $ostatnie_dane_mobilniaka['date'])
										    {
										     // find nearest log before 
										
										    $najblizszy_log_wczesniej_array = mysql_query("
								             SELECT 
								              `id`, 
											  `user_id`, 
											  `log_id`, 
											  `date`,
											  `longitude`, 
											  `latitude`, 
											  `km` 
										     FROM  `cache_moved` 
										     WHERE `cache_id` = '$cache_id'
										     AND   `date` < '$log_date'
										     ORDER BY `date` DESC
										     LIMIT 1
										     "										
										     ) or die(mysql_error());
										
										     $najblizszy_log_wczesniej = mysql_fetch_array($najblizszy_log_wczesniej_array);

										     // find nearest log after
										      $najblizszy_log_pozniej = mysql_fetch_array(mysql_query("
								             SELECT 
								              `id`, 
											  `date`,
											  `user_id`, 
											  `log_id`, 
											  `longitude`, 
											  `latitude`, 
											  `km` 
										     FROM  `cache_moved` 
										     WHERE `cache_id` = '$cache_id'
										     AND   `date` > '$log_date'
										     ORDER BY `date` ASC
										     LIMIT 1
										     "										
										     ));
											 	
										 print 'mieszanie z datami<br>';
										 print  'data logu: '.$log_date
										       .'<br><br>ostatnia (najpozniejsza) data logu ($ostatnie_dane_mobilniaka): '.$ostatnie_dane_mobilniaka['date']
											   . '<br>data wcześniejszego logu ($najblizszy_log_wczesniej): '. $najblizszy_log_wczesniej['date']
											   . '<br>data późniejszego logu ($najblizszy_log_pozniej):' .     $najblizszy_log_pozniej['date']
											   ;
										
										 // Report all PHP errors

										 
										 // wyliczenie zapisac w bazie dystans z obu wierszy modyfikowanych logow
										      $najblizszy_log_wczesniej['id'];
										      
											  // dla logu przed obecnym
											  $najblizszy_log_jeszcze_wczesniej = mysql_fetch_array($najblizszy_log_wczesniej_array);
											  $km_logu[$najblizszy_log_wczesniej['id']]=sprintf("%.2f",calcDistance($najblizszy_log_jeszcze_wczesniej['latitude'],$najblizszy_log_jeszcze_wczesniej['longitude'],$najblizszy_log_wczesniej['latitude'],$najblizszy_log_wczesniej['longitude']));
											  
											  // dla logu po obecnym
											  $km_logu[$najblizszy_log_pozniej['id']]=sprintf("%.2f",calcDistance($wspolrzedneNS,$wspolrzedneWE,$najblizszy_log_pozniej['latitude'],$najblizszy_log_pozniej['longitude']));
											  
											  sql ("UPDATE `cache_moved` SET `km`='&1' WHERE id = '&2'", $km_logu[$najblizszy_log_pozniej['id']], $najblizszy_log_pozniej['id']);
										      sql ("UPDATE `cache_moved` SET `km`='&1' WHERE id = '&2'", $km_logu[$najblizszy_log_wczesniej['id']], $najblizszy_log_wczesniej['id']);
											  
										     // wyliczenie dystansu dla obecnego logu.
										     $dystans=sprintf("%.2f",calcDistance($najblizszy_log_wczesniej['latitude'],$najblizszy_log_wczesniej['longitude'],$wspolrzedneNS,$wspolrzedneWE));
				                             //print $dystans; 
										}
									
								 
							     
							    }
							 //  
							 // insert into table cache_moved
						     sql("INSERT INTO `cache_moved`(`id`, 
						                             `cache_id`, 
													 `user_id`, 
													 `log_id`, 
													 `date`, 
													 `longitude`, 
													 `latitude`,
													 `km`) 
										     VALUES ('', 
											         '&1', 
													 '&2', 
													 '&3', 
													 '&4', 
													 '&5', 
													 '&6',
													 '&7'
											        )",	
											         sql_escape($cache_id),
													 $usr['userid'],
													 $last_id_4_mobile_moved,
													 $log_date,
													 $wspolrzedneWE,
													 $wspolrzedneNS,
													 $dystans
												    );			
						     // update main cache coordinates
						     sql("UPDATE `caches` SET `longitude` = '&2', `latitude` = '&3'  WHERE `cache_id`='&1'", sql_escape($cache_id), $wspolrzedneWE, $wspolrzedneNS);
						 
						   }
						// mobilne by Łza - koniec
						
						//inc cache stat and "last found"
						$rs = sql("SELECT `founds`, `notfounds`, `notes`, `last_found` FROM `caches` WHERE `cache_id`='&1'", sql_escape($cache_id));
						$record = sql_fetch_array($rs);

						$last_found = '';
						if ($log_type == 1 || $log_type == 7)
						{
							$tmpset_var = '`founds`=\'' . ($record['founds'] + 1) . '\'';

							$dlog_date = mktime($log_date_hour, $log_date_min,0, $log_date_month, $log_date_day, $log_date_year);
							if ($record['last_found'] == NULL)
							{
								$last_found = ', `last_found`=\'' . sql_escape(date('Y-m-d H:i:s', $dlog_date)) . '\'';
							}
							elseif (strtotime($record['last_found']) < $dlog_date)
							{
								$last_found = ', `last_found`=\'' . sql_escape(date('Y-m-d H:i:s', $dlog_date)) . '\'';
							}
						}
						elseif ($log_type == 2 || $log_type == 8) // fuer Events wird not found als will attend Zaehler missbraucht
						{
							$tmpset_var = '`notfounds`=\'' . sql_escape($record['notfounds'] + 1) . '\'';
						}
						elseif ($log_type == 3)
						{
							$tmpset_var = '`notes`=\'' . sql_escape($record['notes'] + 1) . '\'';
						}

						if ($log_type == 1 || $log_type == 2 || $log_type == 3 || $log_type == 7 || $log_type == 8)
						{
							sql("UPDATE `caches` SET " . $tmpset_var . $last_found . " WHERE `cache_id`='&1'", sql_escape($cache_id));
						}

						//inc user stat
						$rs = sql("SELECT `log_notes_count`, `founds_count`, `notfounds_count` FROM `user` WHERE `user_id`='&1'", $usr['userid']);
						$record = sql_fetch_array($rs);

						if ($log_type == 1 || $log_type == 7)
						{
							$tmpset_var = '`founds_count`=\'' . sql_escape($record['founds_count'] + 1) . '\'';
						}
						elseif ($log_type == 2)
						{
							$tmpset_var = '`notfounds_count`=\'' . sql_escape($record['notfounds_count'] + 1) . '\'';
						}
						elseif ($log_type == 3)
						{
							$tmpset_var = '`log_notes_count`=\'' . sql_escape($record['log_notes_count'] + 1) . '\'';
						}
						if ($log_type == 1 || $log_type == 2 || $log_type == 3 || $log_type == 7)
						{
							sql("UPDATE `user` SET " . $tmpset_var . " WHERE `user_id`='&1'", sql_escape($usr['userid']));
						}

						// update cache_status
						$rs = sql("SELECT `log_types`.`cache_status` FROM `log_types` WHERE `id`='&1'", sql_escape($log_type));
						if($record = sql_fetch_array($rs))
						{
							$cache_status = $record['cache_status'];
							if($cache_status != 0)
							{
								$rs = sql("UPDATE `caches` SET `status`='&1' WHERE `cache_id`='&2'", sql_escape($cache_status), sql_escape($cache_id));
							}
						}
						else
						{
							die("OPS!");
						}

						// update top-list
						if ($log_type == 1 || $log_type == 3) {
						if ($top_cache == 1)
							sql("INSERT IGNORE INTO `cache_rating` (`user_id`, `cache_id`) VALUES('&1', '&2')", $usr['userid'], $cache_id);
						else
							sql("DELETE FROM `cache_rating` WHERE `user_id`='&1' AND `cache_id`='&2'", $usr['userid'], $cache_id);
						}
						//call eventhandler
						require_once($rootpath . 'lib/eventhandler.inc.php');
						event_new_log($cache_id, $usr['userid']+0);
					}
					//redirect to viewcache
					$no_tpl_build = true;
					//include('viewcache.php');
					tpl_redirect('viewcache.php?cacheid=' . $cache_id);
					}
				else
				{
					$sql = "SELECT count(*) as founds FROM `cache_logs` WHERE `deleted`=0 AND user_id='".sql_escape($usr['userid'])."' AND cache_id='".sql_escape($cache_id)."' AND type='1'";
					$res = mysql_fetch_array(mysql_query($sql));
					$sql = "SELECT status, type FROM `caches` WHERE cache_id='".sql_escape($cache_id)."'";
					$res2 = mysql_fetch_array(mysql_query($sql));

					//build logtypeoptions
					
					$logtypeoptions = '';
					
					// setting selector neutral
					 if ($log_type < 0) {
					 if ($res2['status'] != 4) $logtypeoptions .= '<option value="-2" selected="selected">'. tr('wybrac_log') . '</option>' . '\n';
					 tpl_set_var('display', "none"); 
					 }
					
					foreach ($log_types AS $type)
					{
						// do not allow 'finding' or 'not finding' own or archived cache (events can be logged) $res2['status'] == 2 || $res2['status'] == 3 

						if( $res2['type'] != 6 && ($usr['userid'] == $cache_user_id || $res['founds'] > 0 || $res2['status'] == 4 || $res2['status'] == 6))
						{
							if ($usr['admin']==true && $res2['status'] == 4) $logtypeoptions .= '<option selected="selected" value="3">Komentarz</option>' . "\n";
							else $logtypeoptions .= '<option value="3">Komentarz</option>' . "\n";
							if  ($res2['type'] == 8){
							$logtypeoptions .= '<option value="4">Przeniesiona</option>' . "\n";}
							if ($usr['userid']!=$cache_user_id){$logtypeoptions .= '<option value="5">Potrzebny serwis</option>' . "\n";}
							if ($usr['admin']==true){$logtypeoptions .= '<option value="12">Komentarz COG</option>' . "\n";}
							
							// service log by Łza
					        // if curently logged user is a cache owner and cache status is "avilable"
					        // then add log type option "temp. unavailable";
							adad;
					        if ($usr['userid'] == $cache_user_id && $res2['status'] == 1)
					           {
							     $logtypeoptions .= '<option value="11">'.tr("log_type_temp_unavailable").'</option>'. "\n";
					           }
							// if curently logged user is a cache owner and cache status is "temp. unavailable" 
					        // then add log type option "avilable" 
							if (($usr['userid'] == $cache_user_id) && ($res2['status'] == 2	))
							   {
								 $logtypeoptions .= '<option value="10">'.tr("log_type_available").'</option>'. "\n";
					           }
							break;
							
						}
						

						
						// skip if permission=O and not owner
						if($type['permission'] == 'O' && $usr['userid'] != $cache_user_id && $type['permission'])
							continue;
						if($cache_type == 6)
						{
							if ($usr['admin']){
							if($type['id'] == 1 || $type['id'] == 2|| $type['id'] == 4|| $type['id'] == 5 || $type['id'] == 9 || $type['id'] == 10|| $type['id'] == 11)
							{continue;}} else{
							if($type['id'] == 1 || $type['id'] == 2|| $type['id'] == 4|| $type['id'] == 5 || $type['id'] == 9 || $type['id'] == 10|| $type['id'] == 11|| $type['id'] == 12)
							{continue;}}							
						}
						else
						{
							if($cache_type == 8)
							 {
							 if ($usr['admin']){
							// skip will attend/attended if the cache no event
							if($type['id'] == 7 || $type['id'] == 8|| $type['id'] == 9 || $type['id'] == 10|| $type['id'] == 11 )
							{continue;}} else {
							if($type['id'] == 7 || $type['id'] == 8|| $type['id'] == 9 || $type['id'] == 10|| $type['id'] == 11 || $type['id'] == 12)
							{continue;}}							
							}else{							
							// skip will attend/attended/Moved  if the cache no event and Mobile
							if ($usr['admin']) {
							if($type['id'] == 4 || $type['id'] == 7 || $type['id'] == 8|| $type['id'] == 9 || $type['id'] == 10|| $type['id'] == 11)
							{ continue;}} else {
							if($type['id'] == 4 || $type['id'] == 7 || $type['id'] == 8|| $type['id'] == 9 || $type['id'] == 10|| $type['id'] == 11 || $type['id'] == 12)
							{ continue;}}												
							}

						}
						

					   
						
							if(checkField('log_types',$lang) )
								$lang_db = $lang;
							else
								$lang_db = "en";

						
						
						// $logtypeoptions .= '<option value="' . $type['id'] . '" >' . htmlspecialchars($type[$lang_db], ENT_COMPAT, 'UTF-8') . '</option>' . "\n";
						
						
						if ($type['id'] == $log_type)
						{
							$logtypeoptions .= '<option value="' . $type['id'] . '" selected="selected">' . htmlspecialchars($type[$lang_db], ENT_COMPAT, 'UTF-8') . '</option>' . "\n";
						}
						else
						{
							$logtypeoptions .= '<option value="' . $type['id'] . '">' . htmlspecialchars($type[$lang_db], ENT_COMPAT, 'UTF-8') . '</option>' . "\n";
						}
					

					
					}


					//set tpl vars
					tpl_set_var('cachename', htmlspecialchars($cachename, ENT_COMPAT, 'UTF-8'));
					tpl_set_var('cacheid', htmlspecialchars($cache_id, ENT_COMPAT, 'UTF-8'));
					tpl_set_var('logmin', htmlspecialchars($log_date_min, ENT_COMPAT, 'UTF-8'));
					tpl_set_var('loghour', htmlspecialchars($log_date_hour, ENT_COMPAT, 'UTF-8'));
					tpl_set_var('logday', htmlspecialchars($log_date_day, ENT_COMPAT, 'UTF-8'));
					tpl_set_var('logmonth', htmlspecialchars($log_date_month, ENT_COMPAT, 'UTF-8'));
					tpl_set_var('logyear', htmlspecialchars($log_date_year, ENT_COMPAT, 'UTF-8'));
					tpl_set_var('logtypeoptions', $logtypeoptions);
					tpl_set_var('reset', $reset);
					tpl_set_var('submit', $submit);
					tpl_set_var('date_message', '');
					tpl_set_var('top_cache', $top_cache);
					tpl_set_var('bodyMod', ' onload="chkMoved()" onunload="GUnload()"');

					tpl_set_var('wsp_NS_st',  $wsp_NS_st);
				    tpl_set_var('wsp_NS_min', $wsp_NS_min);
				    tpl_set_var('wsp_WE_st',  $wsp_WE_st);
				    tpl_set_var('wsp_WE_min', $wsp_WE_min);
				    tpl_set_var('$wybor_WE',  $wybor_WE);
				    tpl_set_var('$wybor_NS',  $wybor_NS);

					// Text / normal HTML / HTML editor
					tpl_set_var('use_tinymce', (($descMode == 3) ? 1 : 0));

					if ($descMode == 1)
						tpl_set_var('descMode', 1);
					else if ($descMode == 2)
						tpl_set_var('descMode', 2);
					else
					{
					// TinyMCE
					$headers = tpl_get_var('htmlheaders') . "\n";
					$headers .= '<script language="javascript" type="text/javascript" src="lib/phpfuncs.js"></script>' . "\n";
					$headers .= '<script language="javascript" type="text/javascript" src="lib/tinymce/tiny_mce.js"></script>' . "\n";
					$headers .= '<script language="javascript" type="text/javascript" src="lib/tinymce/config/log.js.php?lang='.$lang.'&amp;logid=0"></script>' . "\n";
					tpl_set_var('htmlheaders', $headers);
						tpl_set_var('descMode', 3);
					}
					
					if ($descMode != 1)
						tpl_set_var('logtext', htmlspecialchars($log_text, ENT_COMPAT, 'UTF-8'), true);
					else
						tpl_set_var('logtext', strip_tags($log_text));
					
					$listed_on = array();
			if($cache_record['wp_ge'] != '')
				$listed_on[] = '<a href="http://geocaching.gpsgames.org/cgi-bin/ge.pl?wp='.$cache_record['wp_ge'].'" target="_blank">GPSgames.org</a>';

			if($cache_record['wp_tc'] != '')
				$listed_on[] = '<a href="http://www.terracaching.com/viewcache.cgi?C=/'.$cache_record['wp_tc'].'" target="_blank">TerraCaching.com</a>';

			if($cache_record['wp_nc'] != '')
			{
				$wpnc = hexdec(mb_substr($cache_record['wp_nc'], 1));
				$listed_on[] = '<a href="http://www.navicache.com/cgi-bin/db/displaycache2.pl?CacheID='.$wpnc.'" target="_blank">Navicache.com</a>';
			}
			if($cache_record['wp_gc'] != '')
				$listed_on[] = '<a href="http://www.geocaching.com/seek/cache_details.aspx?wp='.$cache_record['wp_gc'].'" target="_blank">Geocaching.com</a>';


					if(sizeof($listed_on))
					{
						tpl_set_var('listed_start', "");
						tpl_set_var('listed_end', "");
						tpl_set_var('listed_on', sizeof($listed_on) == 0 ? $listed_only_oc : implode(", ", $listed_on));
					}
					else
					{
					tpl_set_var('listed_start', "<!--");
					tpl_set_var('listed_end', "-->");
					}
					if ($use_log_pw == true)
					{
						if ($pw_not_ok == true)
						{
							tpl_set_var('log_pw_field', $log_pw_field_pw_not_ok);
						}
						else
						{
							tpl_set_var('log_pw_field', $log_pw_field);
						}
					}
					else
					{
						tpl_set_var('log_pw_field', '');
					}

					if ($date_not_ok == true)
					{
						tpl_set_var('date_message', $date_message);
					}
					
					if ($score_not_ok == true)
					{
						tpl_set_var('score_message', $score_message);
					}
					else
						tpl_set_var('score_message', '');

					if (($log_type < 0) && (isset($_POST['logtype']))) tpl_set_var('log_message', $log_not_ok_message);
					else tpl_set_var('log_message','');
					// build smilies
					$smilies = '';
					if ($descMode != 3)
					{
						for($i=0; $i<count($smileyshow); $i++)

						{
							if($smileyshow[$i] == '1')
							{
								$tmp_smiley = $smiley_link;
								$tmp_smiley = mb_ereg_replace('{smiley_image}', $smileyimage[$i], $tmp_smiley);
								$smilies = $smilies . mb_ereg_replace('{smiley_text}', ' '.$smileytext[$i].' ', $tmp_smiley) . '&nbsp;';
							}
						}
					}
					tpl_set_var('smilies', $smilies);
				}
			} // end if( cache_id != 0 )
			else
			{
				// cache_id = 0
				header('Location: viewcache.php?cacheid='.$_GET['cacheid']);
			}
		}
	}
	if ($no_tpl_build == false)
	{
		//make the template and send it out
		tpl_BuildTemplate(false);
	}

function validate_coords($lat_h, $lat_min, $lon_h, $lon_min, $lonEW, $latNS, $error_coords_not_ok)
{
	//check coordinates
	$error = false;
	if ($lat_h!='' || $lat_min!='')
	{
		if (!mb_ereg_match('^[0-9]{1,2}$', $lat_h))
		{
			tpl_set_var('lat_message', $error_coords_not_ok);
			$error = true;
			$lat_h_not_ok = true;
		}
		else
		{
			if (($lat_h >= 0) && ($lat_h < 90))
			{
				$lat_h_not_ok = false;
			}
			else
			{
				tpl_set_var('lat_message', $error_coords_not_ok);
				$error = true;
				$lat_h_not_ok = true;
			}
		}

		if (is_numeric($lat_min))
		{
			if (($lat_min >= 0) && ($lat_min < 60))
			{
				$lat_min_not_ok = false;
			}
			else
			{
				tpl_set_var('lat_message', $error_coords_not_ok);
				$error = true;
				$lat_min_not_ok = true;
			}
		}
		else
		{
			tpl_set_var('lat_message', $error_coords_not_ok);
			$error = true;
			$lat_min_not_ok = true;
		}

		$latitude = $lat_h + round($lat_min,3) / 60;
		if ($latNS == 'S') $latitude = -$latitude;

		if ($latitude == 0)
		{
			tpl_set_var('lon_message', $error_coords_not_ok);
			$error = true;
			$lat_min_not_ok = true;
		}
	}
	
	else
	{
		$latitude = NULL;
		$lat_h_not_ok = true;
		$lat_min_not_ok = true;
	}

	if ($lon_h!='' || $lon_min!='')
	{
		if (!mb_ereg_match('^[0-9]{1,3}$', $lon_h))
		{
			tpl_set_var('lon_message', $error_coords_not_ok);
			$error = true;
			$lon_h_not_ok = true;
		}
		else
		{
			if (($lon_h >= 0) && ($lon_h < 180))
			{
				$lon_h_not_ok = false;
			}
			else
			{
				tpl_set_var('lon_message', $error_coords_not_ok);
				$error = true;
				$lon_h_not_ok = true;
			}
		}

		if (is_numeric($lon_min))
		{
			if (($lon_min >= 0) && ($lon_min < 60))
			{
				$lon_min_not_ok = false;
			}
			else
			{
				tpl_set_var('lon_message', $error_coords_not_ok);
				$error = true;
				$lon_min_not_ok = true;
			}
		}
		else
		{
			tpl_set_var('lon_message', $error_coords_not_ok);
			$error = true;
			$lon_min_not_ok = true;
		}

		$longitude = $lon_h + round($lon_min,3) / 60;
		if ($lonEW == 'W') $longitude = -$longitude;

		if ($longitude == 0)
		{
			tpl_set_var('lon_message', $error_coords_not_ok);
			$error = true;
			$lon_min_not_ok = true;
		}
	}
	else
	{
		$longitude = NULL;
		$lon_h_not_ok = true;
		$lon_min_not_ok = true;
	}

	$lon_not_ok = $lon_min_not_ok || $lon_h_not_ok;
	$lat_not_ok = $lat_min_not_ok || $lat_h_not_ok;



/*	
if ($lon_not_ok == false) print "lon_ok<br>"; 
if ($lat_not_ok == false) print "lat_ok<br>"; 

exit;
*/

return ($error);
}
	
?>
