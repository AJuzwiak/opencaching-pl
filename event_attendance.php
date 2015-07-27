<?php
/***************************************************************************
		 ./event_attendance.php
		 -------------------
		begin                : June 24 2004
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

   Unicode Reminder メモ

	 show who attends an events

	 used template(s): event_attendance

	 GET Parameter: id

 ****************************************************************************/

	//prepare the templates and include all neccessary

	$tplname = 'event_attendance';
	require_once('./lib/common.inc.php');

	require($stylepath . '/event_attendance.inc.php');

	tpl_set_var('nocacheid_start', '<!--');
	tpl_set_var('nocacheid_end', '-->');
	tpl_set_var('owner', '');
	tpl_set_var('cachename', '');
	tpl_set_var('event_date', '');

	// id gesetzt?

	$cache_id = isset($_REQUEST['id']) ? $_REQUEST['id']+0 : 0;
	if ($cache_id != 0)
	{
		$rs = sql("SELECT `caches`.`name`, `user`.`username`, `caches`.`date_hidden`
			   FROM `caches`
			   INNER JOIN `user` ON (`user`.`user_id`=`caches`.`user_id`)
			   WHERE `caches`.`cache_id`='&1'", $cache_id);

		if ($r = sql_fetch_array($rs))
		{
			tpl_set_var('nocacheid_start', '');
			tpl_set_var('nocacheid_end', '');

			tpl_set_var('owner', htmlspecialchars($r['username'], ENT_COMPAT, 'UTF-8'));
			tpl_set_var('cachename', htmlspecialchars($r['name'], ENT_COMPAT, 'UTF-8'));
			tpl_set_var('event_date', htmlspecialchars(strftime($dateformat, strtotime($r['date_hidden'])), ENT_COMPAT, 'UTF-8'));
		}


		$rs = sql("SELECT DISTINCT `user`.`username`
			   FROM `cache_logs`
			   INNER JOIN `user` ON (`user`.`user_id`=`cache_logs`.`user_id`)
			   WHERE `cache_logs`.`type`=8
					AND `cache_logs`.`deleted`=0 
			    AND `cache_logs`.`cache_id`='&1'
			   ORDER BY `user`.`username`", $cache_id);

		$attendants = '';
		$count = 0;
		while($r = sql_fetch_array($rs))
		{
			$attendants .= $r['username'].'<br />';
			$count++;
		}

		tpl_set_var('attendants', $attendants);
		tpl_set_var('att_count', $count);
	}

	//make the template and send it out
	tpl_BuildTemplate();

?>
