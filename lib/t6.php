<?php
	global $lang, $rootpath;

	if (!isset($rootpath)) $rootpath = './';

	//include template handling
	require_once($rootpath . 'lib/common.inc.php');

  setlocale(LC_TIME, 'pl_PL.UTF-8');

  $rsU = sql('SELECT COUNT(*) `count` FROM (SELECT COUNT(caches.user_id) FROM `caches` WHERE `status`=1 GROUP BY `user_id`) `users_with_founds`');
  $fC = sql('SELECT COUNT(*) `count` FROM `caches` WHERE `status`=1');
    $rsUs = mysql_fetch_array($rsU);
    $fCt = mysql_fetch_array($fC);
 
 $rsfCR = sql("SELECT COUNT(*) `count`, `cache_location`.`adm3` region, `cache_location`.`code3` code_region FROM `cache_location` INNER JOIN caches ON cache_location.cache_id=caches.cache_id WHERE `cache_location`.`code1`='PL' AND `caches`.`status`=1 AND `caches`.`type`<>6 GROUP BY `cache_location`.`code3` ORDER BY count DESC");

	echo '<table width="97%"><tr><td align="center"><center><b> '.tr('number_of_caches_by_region').'</b> <br />'.tr('only_active_caches').'<br /> '.tr('users_who_created_caches').': ';
	echo $rsUs[count]; 
	echo ' .::. '.tr('number_of_active_caches').': ';
	echo $fCt[count]; 
	echo '<br /><br />(Kliknij na nazwe województwa aby zobaczyć statytykę użytkowników w danym województwie)</center></td></tr></table><br><table border="1" bgcolor="white" width="97%">' . "\n";

 
echo '
<tr class="bgcolor2">
	<td width="20%" align="right">
		&nbsp;&nbsp;<b>'.tr('number_of_caches').'</b>&nbsp;&nbsp;
	</td>
	<td align="right">
		&nbsp;&nbsp;<b>'.tr('region').'</b>&nbsp;&nbsp;
	</td>
</tr><tr><td height="2"></td></tr>';

while ($line=mysql_fetch_array($rsfCR))
{
    echo '<tr class="bgcolor2">
			<td align="right">
				&nbsp;&nbsp;<b>'.$line[count].'</b>&nbsp;&nbsp;
			</td>
			<td align="right">
				&nbsp;&nbsp;<b><a class=links href=articles.php?page=s9&region='.$line[code_region].'>'.$line[region].'</a></b>&nbsp;&nbsp;
			</td>';

}

	echo '</table>' . "\n";

mysql_free_result($rsfCR);

?>
