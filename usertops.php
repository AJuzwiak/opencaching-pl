<?php
/***************************************************************************
		  ./mytop5.php
		-------------------
		begin                : November 4 2005
		copyright            : (C) 2005 The OpenCaching Group
		forum contact at     : http://www.opencaching.com/phpBB2

	***************************************************************************/

/****************************************************************************

   Unicode Reminder メモ

	 show the list of rated caches

 ****************************************************************************/

	require('./lib/common.inc.php');
	require($stylepath . '/usertops.inc.php');

	if ($error == false)
	{
		$userid = isset($_REQUEST['userid']) ? $_REQUEST['userid']+0 : 0;

		$tplname = 'usertops';

		$rs = sql("SELECT `username` FROM `user` WHERE `user_id`='&1'", $userid);
		if (mysql_num_rows($rs) == 1)
		{
			$r = sql_fetch_array($rs);
			$username = $r['username'];
			tpl_set_var('userid', $userid);
			tpl_set_var('username', $username);
		}
		else
		{
			tpl_set_var('userid', 0);
			tpl_set_var('username', 'Nicht gefunden');
			$userid = 0;
			$username = "Nicht gefunden";
			$notop5 = $user_notfound;
		}

		$i = 0;
		$content = '';
		$rs = sql("	SELECT `cache_rating`.`cache_id` AS `cache_id`, `caches`.`name` AS `cachename`, `user`.`username` AS `ownername`
				FROM `cache_rating`, `caches`, `user`
				WHERE `cache_rating`.`cache_id` = `caches`.`cache_id`
				  AND `caches`.`user_id`=`user`.`user_id`
				  AND `cache_rating`.`user_id`='&1' ORDER BY `caches`.`name` ASC", $userid);
		if (mysql_num_rows($rs) != 0)
		{
			while ($r = sql_fetch_array($rs))
			{
				$thisline = $viewtop5_line;

				$thisline = mb_ereg_replace('{cachename}', htmlspecialchars($r['cachename'], ENT_COMPAT, 'UTF-8'), $thisline);
				$thisline = mb_ereg_replace('{cacheid}', htmlspecialchars($r['cache_id'], ENT_COMPAT, 'UTF-8'), $thisline);
				$thisline = mb_ereg_replace('{ownername}', htmlspecialchars($r['ownername'], ENT_COMPAT, 'UTF-8'), $thisline);

				if (($i % 2) == 1)
					$thisline = mb_ereg_replace('{bgcolor}', $bgcolor2, $thisline);
				else
					$thisline = mb_ereg_replace('{bgcolor}', $bgcolor1, $thisline);

				$content .= $thisline;
				$i++;
			}
			mysql_free_result($rs);
		}
		else
		{
			$content = mb_ereg_replace('{username}', $username, $notop5);
		}

		tpl_set_var('top5', $content);
		tpl_BuildTemplate();
	}

?>
