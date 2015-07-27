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
	         
   Unicode Reminder メモ
                                				                                
	 include the newcaches HTML file
	
 ****************************************************************************/
	

	//prepare the templates and include all neccessary
	require_once('./lib/common.inc.php');
	require_once('./lib/cache_icon.inc.php');

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

		// get the news
		$tplname = 'cacheratings';

		$startat = isset($_REQUEST['startat']) ? $_REQUEST['startat'] : 0;
		$startat = $startat + 0;

		$perpage = 50;
		$startat -= $startat % $perpage;



	//start_ratings.include
	$rs = sql('	SELECT	`user`.`user_id` `user_id`,
				`user`.`username` `username`,
				`caches`.`cache_id` `cache_id`,
				`caches`.`name` `name`,
				`cache_type`.`icon_large` `icon_large`,
				count(`cache_rating`.`cache_id`) as `anzahl`
			FROM `caches`, `user`, `cache_type`, `cache_rating`
			WHERE `caches`.`user_id`=`user`.`user_id`
			  AND `cache_rating`.`cache_id`=`caches`.`cache_id`
			  AND `status`=1  AND `type` <> 6
			  AND `caches`.`type`=`cache_type`.`id`
			GROUP BY `user`.`user_id`, `user`.`username`, `caches`.`cache_id`, `caches`.`name`, `cache_type`.`icon_large`
			ORDER BY `anzahl` DESC, `caches`.`name` ASC
			LIMIT ' . ($startat+0) . ', ' . ($perpage+0));
			
	$cacheline = '<tr><td>&nbsp;</td><td><span class="content-title-noshade txt-blue08" >{rating_absolute}</span></td><td><img src="{cacheicon}" class="icon16" alt="Cache" title="Cache" /></td><td><strong><a class="links" href="viewcache.php?cacheid={cacheid}">{cachename}</a></strong></td><td><strong><a class="links" href="viewprofile.php?userid={userid}">{username}</a></strong></td></tr>';

if (mysql_num_rows($rs) == 0)
	{
		$file_content = '<tr><td colspan="5"><strong>Nie ma nowych skrzynek z rekomendacjami</strong></td></tr>';
	}
	else
	{

	tpl_set_var('num_ratings', mysql_num_rows($rs));
	
	for ($i = 0; $i < mysql_num_rows($rs); $i++)
	{
		$record = sql_fetch_array($rs);
		$cacheicon = 'tpl/stdstyle/images/'.getSmallCacheIcon($record['icon_large']);

		$thisline = $cacheline;
		$thisline = mb_ereg_replace('{cacheid}', urlencode($record['cache_id']), $thisline);
		$thisline = mb_ereg_replace('{cachename}', htmlspecialchars($record['name'], ENT_COMPAT, 'UTF-8'), $thisline);
		$thisline = mb_ereg_replace('{userid}', urlencode($record['user_id']), $thisline);
		$thisline = mb_ereg_replace('{username}', htmlspecialchars($record['username'], ENT_COMPAT, 'UTF-8'), $thisline);
		$thisline = mb_ereg_replace('{rating_absolute}', $record['anzahl'], $thisline);
		$thisline = mb_ereg_replace('{cacheicon}', $cacheicon, $thisline);

		$file_content .= $thisline . "\n";
	}

}

		}

tpl_set_var('content', $file_content);

	$rs = sql('SELECT COUNT(*) `count`
			FROM `caches`
			WHERE caches.`status`=1  AND caches.type <> 6
			AND caches.`topratings`!=0');
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
			$pages .= '<a href="cacheratings.php?startat=0">{first_img}</a> <a href="cacheratings.php?startat=' . ($startat - 100) . '">{prev_img}</a> ';
		else
			$pages .= '{first_img_inactive} {prev_img_inactive} ';

		for ($i = $frompage; $i <= $topage; $i++)
		{
			if ($i == $thissite)
				$pages .= $i . ' ';
			else
				$pages .= '<a href="cacheratings.php?startat=' . ($i - 1) * $perpage . '">' . $i . '</a> ';
		}
		if ($thissite < $topage)
			$pages .= '<a href="cacheratings.php?startat=' . ($startat + $perpage) . '">{next_img}</a> <a href="cacheratings.php?startat=' . (ceil($count / 100) * 100 - 100) . '">{last_img}</a>';
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
