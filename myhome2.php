<?php
/***************************************************************************
																./myhome.php
															-------------------
		begin                : Mon June 14 2004
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

	 the users home

	 used template(s): myhome
	 parameter(s):     none

 ****************************************************************************/

	//prepare the templates and include all neccessary
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
			if( $usr['admin'] )
				tpl_set_var('reports',"<b>".tr('manage_ocpl')."</b><br />[<a href='viewreports.php'>".tr("browse_problem_reports")."</a>]");
			else
				tpl_set_var('reports','');
			require($stylepath . '/myhome2.inc.php');
			require($stylepath . '/lib/icons.inc.php');

			$tplname = 'myhome2';
			tpl_set_var('username', htmlspecialchars($usr['username'], ENT_COMPAT, 'UTF-8'));
			tpl_set_var('userid', htmlspecialchars($usr['userid'], ENT_COMPAT, 'UTF-8'));

			//get user record
			$userid = $usr['userid'];
			$sql = "SELECT COUNT(*) FROM caches WHERE user_id='$userid'";
			if( $odp = mysql_query($sql) )
				$hidden_count = mysql_result($odp,0);
			else 
				$hidden_count = 0;
			
			$sql = "SELECT COUNT(*) founds_count 
							FROM cache_logs 
							WHERE user_id=$userid AND type=1 AND deleted=0";
			
			if( $odp = mysql_query($sql) )
				$founds_count = mysql_result($odp,0);
			else 
				$founds_count = 0;
			
			$sql = "SELECT COUNT(*) events_count 
							FROM cache_logs 
							WHERE user_id=$userid AND type=7 AND deleted=0";
			
			if( $odp = mysql_query($sql) )
				$events_count = mysql_result($odp,0);
			else 
				$events_count = 0;
			
			$sql = "SELECT COUNT(*) notfounds_count 
							FROM cache_logs 
							WHERE user_id=$userid AND type=2 AND deleted=0";
			
			if( $odp = mysql_query($sql) )
				$notfounds_count = mysql_result($odp,0);
			else 
				$notfounds_count = 0;
			
			$sql = "SELECT COUNT(*) log_notes_count 
							FROM cache_logs 
							WHERE user_id=$userid AND type=3 AND deleted=0";
			
			if( $odp = mysql_query($sql) )
				$log_notes_count = mysql_result($odp,0);
			else 
				$log_notes_count = 0;
			
			if( $events_count > 0 )
				$events = "Uczestniczyłeś w ".$events_count." spotkaniach.";
			else $events = "";
			
			tpl_set_var('founds', $founds_count);
			tpl_set_var('hidden', $hidden_count);
			tpl_set_var('events', $events);
			
			//get last logs
			$rs_logs = sql("
					SELECT `cache_logs`.`cache_id` `cache_id`, `cache_logs`.`type` `type`, `cache_logs`.`date` `date`, `caches`.`name` `name`,
						`log_types`.`icon_small`, `log_types_text`.`text_combo`
					FROM `cache_logs`, `caches`, `log_types`, `log_types_text`
					WHERE `cache_logs`.`user_id`='&1' 
					AND `cache_logs`.`deleted`=0 
					AND `cache_logs`.`cache_id`=`caches`.`cache_id`
					AND `log_types`.`id`=`cache_logs`.`type`
					AND `log_types_text`.`log_types_id`=`log_types`.`id` AND `log_types_text`.`lang`='&2'
					ORDER BY `cache_logs`.`date` DESC, `cache_logs`.`date_created` DESC
					", $usr['userid'], $lang);

			if (mysql_num_rows($rs_logs) == 0)
			{
				tpl_set_var('lastlogs', $no_logs);
			}
			else
			{
				$logs = '';
				for ($i = 0; $i < mysql_num_rows($rs_logs); $i++)
				{
					$record_logs = sql_fetch_array($rs_logs);

					$tmp_log = $log_line;
					$tmp_log = mb_ereg_replace('{logimage}', icon_log_type($record_logs['icon_small'], $record_logs['text_combo']), $tmp_log);
					$tmp_log = mb_ereg_replace('{logtype}', $record_logs['text_combo'], $tmp_log);
					$tmp_log = mb_ereg_replace('{date}', fixPlMonth(strftime($dateformat , strtotime($record_logs['date']))), $tmp_log);
					$tmp_log = mb_ereg_replace('{cachename}', htmlspecialchars($record_logs['name'], ENT_COMPAT, 'UTF-8'), $tmp_log);
					$tmp_log = mb_ereg_replace('{cacheid}', htmlspecialchars(urlencode($record_logs['cache_id']), ENT_COMPAT, 'UTF-8'), $tmp_log);

					$logs .= "\n" . $tmp_log;
				}
				tpl_set_var('lastlogs', $logs);
			}

			//get last hidden caches
			$rs_caches = sql("	SELECT	`cache_id`, `name`, `date_hidden`, `status`,
							`cache_status`.`id` AS `cache_status_id`, `cache_status`.`&1` AS `cache_status_text`
						FROM `caches`, `cache_status`
						WHERE `user_id`='&2'
						  AND `cache_status`.`id`=`caches`.`status`
						  AND `caches`.`status` != 5
						ORDER BY `date_hidden` DESC, `caches`.`date_created` DESC
						LIMIT 20", $lang, $usr['userid']);
			if (mysql_num_rows($rs_caches) == 0)
			{
				tpl_set_var('lastcaches', $no_hiddens);
			}
			else
			{
				$caches = '';
				for ($i = 0; $i < mysql_num_rows($rs_caches); $i++)
				{
					$record_logs = sql_fetch_array($rs_caches);

					$tmp_cache = $cache_line;

					$tmp_cache = mb_ereg_replace('{cacheimage}', icon_cache_status($record_logs['status'], $record_logs['cache_status_text']), $tmp_cache);
					$tmp_cache = mb_ereg_replace('{cachestatus}', htmlspecialchars($record_logs['cache_status_text'], ENT_COMPAT, 'UTF-8'), $tmp_cache);
					$tmp_cache = mb_ereg_replace('{cacheid}', htmlspecialchars(urlencode($record_logs['cache_id']), ENT_COMPAT, 'UTF-8'), $tmp_cache);
					$tmp_cache = mb_ereg_replace('{date}', strftime($dateformat , strtotime($record_logs['date_hidden'])), $tmp_cache);
					$tmp_cache = mb_ereg_replace('{cachename}', htmlspecialchars($record_logs['name'], ENT_COMPAT, 'UTF-8'), $tmp_cache);

					$caches .= "\n" . $tmp_cache;
				}
				tpl_set_var('lastcaches', $caches);
			}

			//get not published caches
			$rs_caches = sql("	SELECT  `caches`.`cache_id`, `caches`.`name`, `caches`.`date_hidden`, `caches`.`date_activate`, `caches`.`status`, `cache_status`.`&1` AS `cache_status_text`
						FROM `caches`, `cache_status`
						WHERE `user_id`='&2'
						AND `cache_status`.`id`=`caches`.`status`
						AND `caches`.`status` = 5
						ORDER BY `date_activate` DESC, `caches`.`date_created` DESC",$lang, $usr['userid']);
			if (mysql_num_rows($rs_caches) == 0)
			{
				tpl_set_var('notpublishedcaches', $no_notpublished);
			}
			else
			{
				$caches = '';
				for ($i = 0; $i < mysql_num_rows($rs_caches); $i++)
				{
					$record_caches = sql_fetch_array($rs_caches);

					$tmp_cache = $cache_notpublished_line;

					$tmp_cache = mb_ereg_replace('{cacheimage}', icon_cache_status($record_caches['status'], $record_caches['cache_status_text']), $tmp_cache);
					$tmp_cache = mb_ereg_replace('{cachestatus}', htmlspecialchars($record_caches['cache_status_text'], ENT_COMPAT, 'UTF-8'), $tmp_cache);
					$tmp_cache = mb_ereg_replace('{cacheid}', htmlspecialchars(urlencode($record_caches['cache_id']), ENT_COMPAT, 'UTF-8'), $tmp_cache);
					if(is_null($record_caches['date_activate']))
					{
						$tmp_cache = mb_ereg_replace('{date}', $no_time_set, $tmp_cache);
					}
					else
					{
						$tmp_cache = mb_ereg_replace('{date}', strftime($datetimeformat , strtotime($record_caches['date_activate'])), $tmp_cache);
					}
					$tmp_cache = mb_ereg_replace('{cachename}', htmlspecialchars($record_caches['name'], ENT_COMPAT, 'UTF-8'), $tmp_cache);

					$caches .= "\n" . $tmp_cache;
				}
				tpl_set_var('notpublishedcaches', $caches);
			}

			// get number of sent emails
			$emails_sent = '0';
			$resp = sql("SELECT COUNT(*) AS `emails_sent` FROM `email_user` WHERE `from_user_id`='&1'", $usr['userid']);
			if($row = sql_fetch_array($resp))
				$emails_sent = $row['emails_sent'];

			tpl_set_var('emails_sent', $emails_sent);
		}
	}

	//make the template and send it out
	tpl_BuildTemplate();
?>
