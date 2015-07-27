<?php
/***************************************************************************
																./editlog.php
															-------------------
		begin                : July 5 2004
		copyright            : (C) 2004 The OpenCaching Group
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

	 edit a log listing

	 used template(s): editlog

	 GET/POST Parameter: logid

 ****************************************************************************/

  //prepare the templates and include all neccessary
	require_once('./lib/common.inc.php');
	require($stylepath.'/smilies.inc.php');

	//Preprocessing
	if ($error == false)
	{
		//logid
		$log_id = 0;
		if (isset($_REQUEST['logid']))
		{
			$log_id = $_REQUEST['logid'];
		}

		//user logged in?
		if ($usr == false)
		{
		    $target = urlencode(tpl_get_current_page());
		    tpl_redirect('login.php?target='.$target);
		}
		else
		{
			//does log with this logid exist?
			$log_rs = sql("SELECT `cache_logs`.`cache_id` AS `cache_id`, `cache_logs`.`node` AS `node`, `cache_logs`.`text` AS `text`, `cache_logs`.`date` AS `date`, `cache_logs`.`user_id` AS `user_id`, `cache_logs`.`type` AS `logtype`, `cache_logs`.`text_html` AS `text_html`, `cache_logs`.`text_htmledit` AS `text_htmledit`, `caches`.`name` AS `cachename`, `caches`.`status` AS `cachestatus`, `caches`.`type` AS `cachetype`, `caches`.`user_id` AS `cache_user_id`, `caches`.`logpw` as `logpw` FROM `cache_logs` INNER JOIN `caches` ON (`caches`.`cache_id`=`cache_logs`.`cache_id`) WHERE `id`='&1' AND `deleted` = &2", $log_id, 0);

			if (mysql_num_rows($log_rs) > 0)
			{
				$log_record = sql_fetch_array($log_rs);
				require($stylepath . '/editlog.inc.php');
				require_once($rootpath . 'lib/caches.inc.php');
				require($stylepath.'/rating.inc.php');

				if ($log_record['node'] != $oc_nodeid)
				{
					tpl_errorMsg('editlog', $error_wrong_node);
					exit;
				}

				//is this log from this user?
				if (($log_record['user_id'] == $usr['userid'] && $log_record['cachestatus'] != 3 && $log_record['cachestatus'] != 6) || $usr['admin'])
				{
					$tplname = 'editlog-test';

					//load settings
					$cache_name = $log_record['cachename'];
					$cache_type = $log_record['cachetype'];
					$cache_user_id = $log_record['cache_user_id'];
					$log_type = isset($_POST['logtype']) ? $_POST['logtype'] : $log_record['logtype'];
					$log_date_min = isset($_POST['logmin']) ? $_POST['logmin'] : date('i', strtotime($log_record['date']));
					$log_date_hour = isset($_POST['loghour']) ? $_POST['loghour'] : date('H', strtotime($log_record['date']));
					$log_date_day = isset($_POST['logday']) ? $_POST['logday'] : date('d', strtotime($log_record['date']));
					$log_date_month = isset($_POST['logmonth']) ? $_POST['logmonth'] : date('m', strtotime($log_record['date']));
					$log_date_year = isset($_POST['logyear']) ? $_POST['logyear'] : date('Y', strtotime($log_record['date']));
					$top_cache = isset($_POST['rating']) ? $_POST['rating']+0 : 0;

					$log_pw = '';
					$use_log_pw = (($log_record['logpw'] == NULL) || ($log_record['logpw'] == '')) ? false : true;
					if (($use_log_pw) && $log_record['logtype']==1)
						$use_log_pw = false;

					if ($use_log_pw)
						$log_pw = $log_record['logpw'];

					// check if user has exceeded his top5% limit
					$is_top = sqlValue("SELECT COUNT(`cache_id`) FROM `cache_rating` WHERE `user_id`='" . sql_escape($log_record['user_id']) . "' AND `cache_id`='" . sql_escape($log_record['cache_id']) . "'", 0);
					$user_founds = sqlValue("SELECT `founds_count` FROM `user` WHERE `user_id`='" .  sql_escape($log_record['user_id']) . "'", 0);
					$user_tops = sqlValue("SELECT COUNT(`user_id`) FROM `cache_rating` WHERE `user_id`='" .  sql_escape($log_record['user_id']) . "'", 0);

					if ($is_top == 0)
					{
						if (($user_founds * rating_percentage/100) < 1)
						{
							$top_cache = 0;
							$anzahl = (1 - ($user_founds * rating_percentage/100)) / (rating_percentage/100);
							if ($anzahl > 1)
							{
								$rating_msg = mb_ereg_replace('{anzahl}', "$anzahl", $rating_too_few_founds);
							}
							else
							{
								$rating_msg = mb_ereg_replace('{anzahl}', "$anzahl", $rating_too_few_founds);
							}
						}
						elseif ($user_tops < floor($user_founds * rating_percentage/100))
						{
							if ($cache_user_id != $usr['userid']) {
								$rating_msg = mb_ereg_replace('{chk_sel}', '', $rating_allowed.'<br />'.$rating_stat);
							} else {
								$rating_msg = mb_ereg_replace('{chk_dis}', ' disabled="disabled"', $rating_own.'<br />'.$rating_stat);
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
								$rating_msg = mb_ereg_replace('{anzahl}', "$anzahl", $rating_too_few_founds);
							}
							else
							{
								$rating_msg = mb_ereg_replace('{anzahl}', "$anzahl", $rating_too_few_founds);
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

					tpl_set_var('rating_message', mb_ereg_replace('{rating_msg}', $rating_msg, $rating_tpl));

					if (isset($_POST['descMode']))
					{
						$descMode = $_POST['descMode']+0;
						if (($descMode < 1) || ($descMode > 3)) $descMode = 3;
					}
					else
					{
						if ($log_record['text_html'] == 1)
							if ($log_record['text_htmledit'] == 1)
								$descMode = 3;
							else
								$descMode = 2;
						else
							$descMode = 1;
					}

					// fuer alte Versionen von OCProp
					if (isset($_POST['submit']) && !isset($_POST['version2']))
					{
						$descMode = 1;
						$_POST['submitform'] = $_POST['submit'];
					}

					if ($descMode != 1)
					{
						// Text from textarea
						$log_text = isset($_POST['logtext']) ? ($_POST['logtext']) : ($log_record['text']);

						// fuer alte Versionen von OCProp
						if (isset($_POST['submit']) && !isset($_POST['version2']))
						{
							$log_text = iconv("ISO-8859-1", "UTF-8", $log_text);
						}

						// check input
						require_once($rootpath . 'lib/class.inputfilter.php');
						$myFilter = new InputFilter($allowedtags, $allowedattr, 0, 0, 1);
						$log_text = $myFilter->process($log_text);
					}
					else
					{
						// escape text
						$log_text = isset($_POST['logtext']) ? htmlspecialchars($_POST['logtext'], ENT_COMPAT, 'UTF-8') : strip_tags($log_record['text']);

						// fuer alte Versionen von OCProp
						if (isset($_POST['submit']) && !isset($_POST['version2']))
						{
							$log_text = iconv("ISO-8859-1", "UTF-8", $log_text);
						}
					}

					//validate date
					$date_not_ok = true;
					if (is_numeric($log_date_day) && is_numeric($log_date_month) && is_numeric($log_date_year) && is_numeric($log_date_hour)&& is_numeric($log_date_min))
					{
						if (checkdate($log_date_month, $log_date_day, $log_date_year) == true || $log_date_hour > 0 || $log_date_hour < 23 || $log_date_min => 0 || $log_date_min < 60)
						{
							$date_not_ok = false;
						}
						if($date_not_ok == false)
						{
							if(isset($_POST['submitform']))
							{
								if(mktime(0, 0, 0, 0, 0, $log_date_min, $log_date_hour, $log_date_month, $log_date_day, $log_date_year)>=mktime())
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

					if ($cache_type == 6)
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

					// not a found log? then ignore the rating
					$sql = "SELECT count(*) as founds FROM `cache_logs` WHERE user_id='".sql_escape($log_record['user_id'])."' AND cache_id='".sql_escape($log_record['cache_id'])."' AND type='1' AND deleted=0";
					$res = mysql_fetch_array(mysql_query($sql));
					if( $res['founds'] == 0 )
					if ($log_type != 1 && $log_type != 7 /*&& $log_type != 3*/)
					{
						$top_cache = 0;
					}


					$pw_not_ok = false;
					if (($use_log_pw) && $log_type == 1)
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

					//store?
					if (isset($_POST['submitform']) && $date_not_ok == false && $logtype_not_ok == false && $pw_not_ok == false)
					{
						//store changed data
						sql("UPDATE `cache_logs` SET `type`='&1',
						                             `date`='&2',
						                             `text`='&3',
						                             `text_html`='&4',
						                             `text_htmledit`='&5',
						                             `last_modified`=NOW()
						                       WHERE `id`='&6'",
						                             $log_type,
						                             date('Y-m-d H:i', mktime(0, 0, 0, 0, 0, $log_date_min, $log_date_hour, $log_date_month, $log_date_day, $log_date_year)),
						                             tidy_html_description((($descMode != 1) ? $log_text : nl2br($log_text))),
						                             (($descMode != 1) ? 1 : 0),
						                             (($descMode == 3) ? 1 : 0),
						                             $log_id);

						//update user-stat if type changed
						if ($log_record['logtype'] != $log_type)
						{
							$user_rs = sql("SELECT `founds_count`, `notfounds_count`, `log_notes_count` FROM `user` WHERE `user_id`='&1'", $log_record['user_id']);
							$user_record = sql_fetch_array($user_rs);
							mysql_free_result($user_rs);

							if ($log_record['logtype'] == 1 || $log_record['logtype'] == 7)
							{
								$user_record['founds_count']--;
								
								// recalc scores for this cache
								sql("DELETE FROM `scores` WHERE `user_id` = '&1' AND `cache_id` = '&2'", $log_record['user_id'], $log_record['cache_id']);
								$sql = "SELECT count(*) FROM scores WHERE cache_id='".sql_escape($log_record['cache_id'])."'";
								$liczba = mysql_result(mysql_query($sql),0);
								$sql = "SELECT SUM(score) FROM scores WHERE cache_id='".sql_escape($log_record['cache_id'])."'";
								$suma = @mysql_result(@mysql_query($sql),0)+0;

								// obliczenie nowej sredniej
								if( $liczba != 0)
								{
									$srednia = $suma / $liczba;
								}
								else 
								{
									$srednia = 0;
								}
								
								$sql = "UPDATE caches SET votes='".sql_escape($liczba)."', score='".sql_escape($srednia)."' WHERE cache_id='".sql_escape($log_record['cache_id'])."'";
								mysql_query($sql);
							}
							elseif ($log_record['logtype'] == 2)
							{
								$user_record['notfounds_count']--;
							}
							elseif ($log_record['logtype'] == 3)
							{
								$user_record['log_notes_count']--;
							}

							// falls eines der felder NULL
							$user_record['founds_count'] = $user_record['founds_count']+0;
							$user_record['notfounds_count'] = $user_record['notfounds_count']+0;
							$user_record['log_notes_count'] = $user_record['log_notes_count']+0;

							if ($log_type == 1 || $log_type == 7)
							{
								$user_record['founds_count']++;
							}
							elseif ($log_type == 2)
							{
								$user_record['notfounds_count']++;
								if( $res['founds'] <= 1)
									$top_cache = 0;
							}
							elseif ($log_type == 3)
							{
								$user_record['log_notes_count']++;
								if( $res['founds'] <= 1)
									$top_cache = 0;
							}

							sql("UPDATE `user` SET `founds_count`='&1', `notfounds_count`='&2', `log_notes_count`='&3' WHERE `user_id`='&4'", $user_record['founds_count'], $user_record['notfounds_count'], $user_record['log_notes_count'], $log_record['user_id']);
							unset($user_record);

							//call eventhandler
							require_once($rootpath . 'lib/eventhandler.inc.php');
							event_change_log_type($log_record['cache_id'], $log_record['user_id']+0);
						}

						//update cache-stat if type or log_date changed
						$cache_rs = sql("SELECT `founds`, `notfounds`, `notes` FROM `caches` WHERE `cache_id`='&1'", $log_record['cache_id']);
						$cache_record = sql_fetch_array($cache_rs);
						mysql_free_result($cache_rs);

						if ($log_record['logtype'] != $log_type)
						{
							if ($log_record['logtype'] == 1 || $log_record['logtype'] == 7)
							{
								$cache_record['founds']--;
							}
							elseif ($log_record['logtype'] == 2 || $log_record['logtype'] == 8)
							{
								$cache_record['notfounds']--;
							}
							elseif ($log_record['logtype'] == 3)
							{
								$cache_record['notes']--;
							}

							// falls eines der felder NULL
							$cache_record['founds'] = $cache_record['founds']+0;
							$cache_record['notfounds'] = $cache_record['notfounds']+0;
							$cache_record['notes'] = $cache_record['notes']+0;

							if ($log_type == 1 || $log_type == 7)
							{
								$cache_record['founds']++;
							}
							elseif ($log_type == 2 || $log_type == 8)
							{
								$cache_record['notfounds']++;
							}
							elseif ($log_type == 3)
							{
								$cache_record['notes']++;
							}
						}

						// update top-list
						if ($top_cache == 1)
							sql("INSERT IGNORE INTO `cache_rating` (`user_id`, `cache_id`) VALUES('&1', '&2')", $log_record['user_id'], $log_record['cache_id']);
						else
							sql("DELETE FROM `cache_rating` WHERE `user_id`='&1' AND `cache_id`='&2'", $log_record['user_id'], $log_record['cache_id']);

						//Update last found
						$lastfound_rs = sql("SELECT MAX(`cache_logs`.`date`) AS `date` FROM `cache_logs` WHERE ((`cache_logs`.`type`=1) AND (`cache_logs`.`cache_id`='&1') AND deleted=&2)", $log_record['cache_id'], 0);
						$lastfound_record = sql_fetch_array($lastfound_rs);

						if ($lastfound_record['date'] === NULL)
						{
							$lastfound = 'NULL';
						}
						else
						{
							$lastfound = $lastfound_record['date'];
						}

						sql("UPDATE `caches` SET `last_found`='&1', `founds`='&2', `notfounds`='&3', `notes`='&4' WHERE `cache_id`='&5'", $lastfound, $cache_record['founds'], $cache_record['notfounds'], $cache_record['notes'], $log_record['cache_id']);
						unset($cache_record);

						//display cache page
						tpl_redirect('viewcache.php?cacheid=' . urlencode($log_record['cache_id']));
						exit;
					}

					// check if user has already found this cache and is not editing the found log (i.e. is able to change another comment's type to 'found')
					$already_found_in_other_comment = 0;
					$sql = "SELECT count(*) as founds FROM `cache_logs` WHERE user_id='".sql_escape($usr['userid'])."' AND cache_id='".sql_escape($log_record['cache_id'])."' AND type='1' AND deleted=0";
					$res = mysql_fetch_array(mysql_query($sql));
					
					if( $res['founds'] > 0 )
					{
						$sql2 = "SELECT count(*) as founds FROM `cache_logs` WHERE id='".sql_escape(intval($log_id))."' AND type='1' AND deleted=0";
						$res2 = mysql_fetch_array(mysql_query($sql2));
						if( $res2['founds'] == 0 )
							$already_found_in_other_comment = 1;
					}
					
					//build logtypeoptions
					$logtypeoptions = '';
					foreach ($log_types AS $type)
					{
						// skip if permission=O and not owner
						if($type['permission'] == 'O' && $log_record['user_id'] != $cache_user_id)
							continue;
						
						if( $log_record['logtype'] != $type['id'] && $log_record['cachestatus'] != 1 )
							continue;
							
						if($already_found_in_other_comment)
						{
							// skip found/notfound if the cache is an event or user has already found this cache or it is not ready to search
							if($type['id'] == 1 || $type['id'] == 2 || $type['id'] == 7 || $type['id'] == 8 )
							{
								continue;
							}
						}
						if($cache_type == 6)
						{
							
							// skip found/notfound if the cache is an event or user has already found this cache
							if($type['id'] == 1 || $type['id'] == 2)
							{
								continue;
							}
						}
						else
						{
							// skip will attend/attended if the cache no event
							if($type['id'] == 7 || $type['id'] == 8)
							{
								continue;
							}
						}
						
						
						if ($type['id'] == $log_type)
						{
							$logtypeoptions .= '<option value="' . $type['id'] . '" selected="selected">' . htmlspecialchars($type[$lang], ENT_COMPAT, 'UTF-8') . '</option>' . "\n";
						}
						else
						{
							$logtypeoptions .= '<option value="' . $type['id'] . '">' . htmlspecialchars($type[$lang], ENT_COMPAT, 'UTF-8') . '</option>' . "\n";
						}
					}

					//set template vars
					tpl_set_var('cachename', htmlspecialchars($cache_name, ENT_COMPAT, 'UTF-8'));
					tpl_set_var('logtypeoptions', $logtypeoptions);
					tpl_set_var('logmin', htmlspecialchars($log_date_min, ENT_COMPAT, 'UTF-8'));
					tpl_set_var('loghour', htmlspecialchars($log_date_hour, ENT_COMPAT, 'UTF-8'));
					tpl_set_var('logday', htmlspecialchars($log_date_day, ENT_COMPAT, 'UTF-8'));
					tpl_set_var('logmonth', htmlspecialchars($log_date_month, ENT_COMPAT, 'UTF-8'));
					tpl_set_var('logyear', htmlspecialchars($log_date_year, ENT_COMPAT, 'UTF-8'));
					tpl_set_var('cachename', htmlspecialchars($cache_name, ENT_COMPAT, 'UTF-8'));
					tpl_set_var('cacheid', $log_record['cache_id']);
					tpl_set_var('logid', $log_id);
					tpl_set_var('date_message', ($date_not_ok == true) ? $date_message : '');

					$log_text = tidy_html_description($log_text);

					if ($descMode != 1)
						tpl_set_var('logtext', htmlspecialchars($log_text, ENT_NOQUOTES, 'UTF-8'), true);
					else
						tpl_set_var('logtext', $log_text);


					// Text / normal HTML / HTML editor
					tpl_set_var('use_tinymce', (($desc_htmledit == 1) ? 1 : 0));

					if (($desc_html == 1) && ($desc_htmledit == 1))
					{
						tpl_set_var('descMode', 3);
					}
					else if ($desc_html == 1)
						tpl_set_var('descMode', 2);
					else
						tpl_set_var('descMode', 1);
					// TinyMCE
					$headers = tpl_get_var('htmlheaders') . "\n";
					$headers .= '<script language="javascript" type="text/javascript" src="lib/phpfuncs.js"></script>' . "\n";
					$headers .= '<script language="javascript" type="text/javascript" src="lib/tinymce/tiny_mce.js"></script>' . "\n";
					$headers .= '<script language="javascript" type="text/javascript" src="lib/tinymce/config/log.js.php?lang='.$lang.'&amp;logid=0"></script>' . "\n";
					tpl_set_var('htmlheaders', $headers);


					if ($use_log_pw == true && $log_pw != '')
					{
						if ($pw_not_ok == true && isset($_POST['submitform']))
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
								$smilies = $smilies.mb_ereg_replace('{smiley_text}', ' '.$smileytext[$i].' ', $tmp_smiley).'&nbsp;';
							}
						}
					}
					tpl_set_var('smilies', $smilies);
				}
				else
				{
					header('Location: viewcache.php?cacheid='.$log_record['cache_id']);
				}
			}
			else
			{
				// no such log or log marked as deleted
				header('HTTP/1.0 404 not found');
				include('./error_pages/404.html');
				die();
			}
		}
	}

	//make the template and send it out
	tpl_BuildTemplate();
?>
