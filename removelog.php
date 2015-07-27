<?php
/***************************************************************************
															./removelogs.php
															-------------------
		begin                : July 7 2004
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

	 remove a cache log

	 GET/POST-Parameter: logid

 ****************************************************************************/
require_once('./lib/common.inc.php');

function removelog($log_id, $language, $lang)
{
	global $tplname, $usr, $lang, $stylepath, $oc_nodeid, $error_wrong_node, $removed_message_titel, $removed_message_end, $emailheaders, $rootpath, $cacheid, $log_record, $cache_types, $cache_size, $cache_status, $dblink;
	$log_rs = sql("SELECT	`cache_logs`.`node` AS `node`, `cache_logs`.`uuid` AS `uuid`, `cache_logs`.`cache_id` AS `cache_id`, `caches`.`user_id` AS `cache_owner_id`,
						`caches`.`name` AS `cache_name`, `cache_logs`.`text` AS `log_text`, `cache_logs`.`type` AS `log_type`,
						`cache_logs`.`user_id` AS `log_user_id`, `cache_logs`.`date` AS `log_date`,
						`log_types`.`icon_small` AS `icon_small`,
						`log_types_text`.`text_listing` AS `text_listing`,
						`user`.`username` as `log_username`
					 FROM `log_types`, `log_types_text`, `cache_logs`, `caches`, `user`
					WHERE `cache_logs`.`id`='&1'
					  AND `cache_logs`.`user_id`=`user`.`user_id`
					  AND `caches`.`cache_id`=`cache_logs`.`cache_id`
					  AND `log_types_text`.`log_types_id`=`log_types`.`id` AND `log_types_text`.`lang`='&2'
						AND `cache_logs`.`deleted` = &3 
					  AND `log_types`.`id`=`cache_logs`.`type`", $log_id, $lang, 0);

			//log exists?
			if (mysql_num_rows($log_rs) == 1)
			{
				$log_record = sql_fetch_array($log_rs);
				mysql_free_result($log_rs);

				include($stylepath . '/removelog.inc.php');

				if ($log_record['node'] != $oc_nodeid)
				{
					tpl_errorMsg('removelog', $error_wrong_node);
					exit;
				}

				//cache-owner or log-owner
				if (($log_record['log_user_id'] == $usr['userid']) || ($log_record['cache_owner_id'] == $usr['userid']) || $usr['admin'])
				{
					//Daten lesen
					if( $usr['admin'] && isset($_POST['userid']))
						$commit = 1;
					else
						$commit = isset($_REQUEST['commit']) ? $_REQUEST['commit'] : 0;

					//we are the logger
					if ($log_record['log_user_id'] == $usr['userid'])
					{
						$tplname = 'removelog_logowner';
					}
					else
					{
						$tplname = 'removelog_cacheowner';

						if ($commit == 1)
						{
							//send email to logowner schicken
							$email_content = read_file($stylepath . '/email/removed_log.email');

							$message = isset($_POST['logowner_message']) ? $_POST['logowner_message'] : '';
							if ($message != '')
							{
								//message to logowner
								$message = $removed_message_titel . "\n" . $message . "\n" . $removed_message_end;
							}

							//get cache owner name
							$cache_owner_rs = sql("SELECT `username` FROM `user` WHERE `user_id`='&1'", $log_record['cache_owner_id']);
							$cache_owner_record = sql_fetch_array($cache_owner_rs);

							//get email address of logowner
							$log_user_rs = sql("SELECT `email`, `username` FROM `user` WHERE `user_id`='&1'", $log_record['log_user_id']);
							$log_user_record = sql_fetch_array($log_user_rs);

							$email_content = mb_ereg_replace('%log_owner%', $log_user_record['username'], $email_content);
							$email_content = mb_ereg_replace('%cache_owner%', $cache_owner_record['username'], $email_content);
							$email_content = mb_ereg_replace('%cache_name%', $log_record['cache_name'], $email_content);
							$email_content = mb_ereg_replace('%log_entry%', $log_record['log_text'], $email_content);
							$email_content = mb_ereg_replace('%comment%', $message, $email_content);

							//send email (only on single removement)
							if( !($usr['userid']==2619 && isset($_POST['userid'])))
								mb_send_mail($log_user_record['email'], $removed_log_title, $email_content, $emailheaders);
						}
					}

					if ($commit == 1)
					{
						//log in removed_objects
						//sql("INSERT INTO `removed_objects` (`id`, `localID`, `uuid`, `type`, `removed_date`, `node`) VALUES ('', '&1', '&2', '1', NOW(), '&3')", $log_id, $log_record['uuid'], $oc_nodeid);

						//log entfernen
						//sql("DELETE FROM `cache_logs` WHERE `cache_logs`.`id`='&1' LIMIT 1", $log_id);
						// do not acually delete logs - just mark them as deleted.
						sql("UPDATE `cache_logs` SET deleted = 1 WHERE `cache_logs`.`id`='&1' LIMIT 1", $log_id);
						// remove from cache_moved for log "MOVED"
						if ($log_record['log_type'] == 4)
						{
						$check_cm = sqlValue("SELECT `id` FROM `cache_moved` WHERE `log_id`='" .  sql_escape($log_id) . "'", 0);
						if ($check_cm!=0) {
						sql("DELETE FROM `cache_moved` WHERE `log_id`='&1' LIMIT 1", $log_id);
							}
						}
						//user stats aktualisieren
						// moegliche racecondition, wenn jemand gleichzeitig loggt koennen die Zaehler auseinanderlaufen! -orotl-
						$user_rs = sql("SELECT `founds_count`, `notfounds_count`, `log_notes_count` FROM `user` WHERE `user_id`='&1'", $log_record['log_user_id']);
						$user_record = sql_fetch_array($user_rs);
						mysql_free_result($user_rs);
						
						
						if ($log_record['log_type'] == 1 || $log_record['log_type'] == 7)
						{ 
							// remove cache from users top caches, because the found log was deleted for some reason
							sql("DELETE FROM `cache_rating` WHERE `user_id` = '&1' AND `cache_id` = '&2'", $log_record['log_user_id'], $log_record['cache_id']);
							$user_record['founds_count']--;

							// recalc scores for this cache
							sql("DELETE FROM `scores` WHERE `user_id` = '&1' AND `cache_id` = '&2'", $log_record['log_user_id'], $log_record['cache_id']);
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
						elseif ($log_record['log_type'] == 2)
						{
							$user_record['notfounds_count']--;
						}
						elseif ($log_record['log_type'] == 3)
						{
							$user_record['log_notes_count']--;
						}

						$user_record['founds_count'] = $user_record['founds_count'] + 0;
						$user_record['notfounds_count'] = $user_record['notfounds_count'] + 0;
						$user_record['log_notes_count'] = $user_record['log_notes_count'] + 0;

						sql("UPDATE `user` SET `founds_count`='&1', `notfounds_count`='&2', `log_notes_count`='&3' WHERE `user_id`='&4'", $user_record['founds_count'], $user_record['notfounds_count'], $user_record['log_notes_count'], $log_record['log_user_id']);
						unset($user_record);

						//call eventhandler
						require_once($rootpath . 'lib/eventhandler.inc.php');
						event_remove_log($cacheid, $usr['userid']+0);

						//update cache-stat if type or log_date changed
						$cache_rs = sql("SELECT `founds`, `notfounds`, `notes` FROM `caches` WHERE `cache_id`='&1'", $log_record['cache_id']);
						$cache_record = sql_fetch_array($cache_rs);
						mysql_free_result($cache_rs);

						if ($log_record['log_type'] == 1 || $log_record['log_type'] == 7)
						{
							$cache_record['founds']--;
						}
						elseif ($log_record['log_type'] == 2 || $log_record['log_type'] == 8)
						{
							$cache_record['notfounds']--;
						}
						elseif ($log_record['log_type'] == 3)
						{
							$cache_record['notes']--;
						}

						//Update last found
						$last_tmp = $log_record['cache_id']; 
						$lastfound_rs = sql("SELECT MAX(`cache_logs`.`date`) AS `date` FROM `cache_logs` WHERE ((cache_logs.`type`=1) AND (cache_logs.`cache_id`='$last_tmp'))");
						$lastfound_record = sql_fetch_array($lastfound_rs);

						if ($lastfound_record['date'] === NULL)
						{
							$lastfound = 'NULL';
						}
						else
						{
							$lastfound = $lastfound_record['date'] ;
						}

						sql("UPDATE `caches` SET `last_found`='&1', `founds`='&2', `notfounds`='&3', `notes`='&4' WHERE `cache_id`='&5'", $lastfound, $cache_record['founds'], $cache_record['notfounds'], $cache_record['notes'], $log_record['cache_id']);
						unset($cache_record);

						if( !($usr['userid']==2619 && isset($_POST['userid'])))
						{
							//cache anzeigen
							$_GET['cacheid'] = $log_record['cache_id'];
							$_REQUEST['cacheid'] = $log_record['cache_id'];
							require('viewcache.php');
						}
					}
					else
					{
						tpl_set_var('cachename', htmlspecialchars($log_record['cache_name'], ENT_COMPAT, 'UTF-8'));
						tpl_set_var('cacheid', htmlspecialchars($log_record['cache_id'], ENT_COMPAT, 'UTF-8'));
						tpl_set_var('logid_urlencode', htmlspecialchars(urlencode($log_id), ENT_COMPAT, 'UTF-8'));
						tpl_set_var('logid', htmlspecialchars($log_id, ENT_COMPAT, 'UTF-8'));
						
						$log = read_file($stylepath . '/viewcache_log.tpl.php');
						
						
						$log = mb_ereg_replace('{date}', htmlspecialchars(strftime("%d %B %Y", strtotime($log_record['log_date'])), ENT_COMPAT, 'UTF-8'), $log);

						if ($log_record['recommended'] == 1)
							$log = mb_ereg_replace('{ratingimage}', $rating_picture, $log);
						else
							$log = mb_ereg_replace('{ratingimage}', '', $log);


						$log = mb_ereg_replace('{username}', htmlspecialchars($log_record['log_username'], ENT_COMPAT, 'UTF-8'), $log);
						$log = mb_ereg_replace('{userid}', htmlspecialchars($log_record['log_user_id'] + 0, ENT_COMPAT, 'UTF-8'), $log);
						tpl_set_var('log_user_name', htmlspecialchars($log_record['log_username'], ENT_COMPAT, 'UTF-8'));

						$log = mb_ereg_replace('{type}', htmlspecialchars($log_record['text_listing'], ENT_COMPAT, 'UTF-8'), $log);

						$log = mb_ereg_replace('{logimage}', icon_log_type($log_record['icon_small'], ""), $log);
						$log = mb_ereg_replace('{logfunctions}', '', $log);
						$log = mb_ereg_replace('{logpictures}', '', $log);
						$log = mb_ereg_replace('{logtext}', $log_record['log_text'], $log);

						tpl_set_var('log', $log);
						//make the template and send it out
						tpl_BuildTemplate();
					}
				}
				else
				{
					//TODO: hm ... no permission to remove the log
				}
			}
			else
			{
				//TODO: log doesn't exist
			}
}
 
   //prepare the templates and include all neccessary
	
	require_once($stylepath . '/lib/icons.inc.php');

	//Preprocessing
	if ($error == false)
	{
		//cacheid
		$log_id = 0;
		if (isset($_REQUEST['logid']))
		{
			$log_id = intval($_REQUEST['logid']);
		}

		//user logged in?
		if ($usr == false)
		{
		    $target = urlencode(tpl_get_current_page());
		    tpl_redirect('login.php?target='.$target);
		}
		else
		{
			if( $usr['userid']==2619 && isset($_REQUEST['userid']) )
			{
				$sql = "SELECT username FROM user WHERE user_id = '".sql_escape(intval($_REQUEST['userid']))."'" ;
				$username = mysql_result(mysql_query($sql),0);
				
				if( !isset($_POST['submit']))
				echo '
					<font color="red"><b><h1>UWAGA!!!</h1></b>Po wciśnięciu "Potwierdzam" nastąpi nieodwracalne usunięcie WSZYSTKICH wpisów użytkownika "'.$username.'".<br/><br/>
					<form action="removelog.php" method="POST">
					<input type="submit" name="submit" value="Potwierdzam"/>
					<input type="hidden" name="userid" value="'.intval($_REQUEST['userid']).'"/>
					</form>
				';
				else
				{
					$logs_rs = sql( "SELECT id FROM cache_logs WHERE user_id = '&1'", intval($_REQUEST['userid']));
					
					while( $log_to_remove = sql_fetch_array($logs_rs) )
					{
						removelog($log_to_remove['id'],$language, $lang);
					}
					mysql_free_result($logs_rs);
					echo 'Wszystkie logi użytkownika "'.$username.'" zostały usunięte...';
				}
			}
			else
				removelog($log_id, $language, $lang);
		}
	}
?>
