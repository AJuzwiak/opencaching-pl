<?php
/***************************************************************************
 *  You can find the license in the docs directory
 *
 *  Unicode Reminder メモ
 *
 *  Cleanup the table sys_temptables from entries of dead threads
 *
 *                         run it once a day
 *
 
 
 ***************************************************************************/

	$rootpath = './';
	require_once($rootpath.'lib2/logic/gis.class.php');
	require_once($rootpath.'lib/clicompatbase.inc.php');
	require_once($rootpath.'lib/common.inc.php');

// checkJob(new cache_npa_areas());

class cache_npa_areas
{
//	var $name = 'cache_npa_areas';
//	var $interval = 600;

	function run()
	{
	
			/* begin db connect */
		db_connect();
		if ($dblink === false)
		{
			echo 'Unable to connect to database';
			exit;
		}
	/* end db connect */
//		$rsCache = sql("SELECT `cache_id`, `latitude`, `longitude` FROM `caches` WHERE `need_npa_recalc`=1");
		$rsCache = sql("SELECT `cache_id`, `latitude`, `longitude` FROM `caches`");
		while ($rCache = mysql_fetch_assoc($rsCache))
		{
//					$sql=sql("DELETE FROM `cache_npa_areas` WHERE `cache_id`='&1' AND `calculated`=1", $rCache['cache_id']);
//					mysql_query($sql);


			$rsLayers = sql("SELECT `id`, AsText(`shape`) AS `geometry` FROM `npa_areas` WHERE WITHIN(GeomFromText('&1'), `shape`)", 'POINT(' . $rCache['longitude'] . ' ' . $rCache['latitude'] . ')');

						
			while ($rLayers = mysql_fetch_assoc($rsLayers))
			{
			
//				echo "progress";
				if (gis::ptInLineRing($rLayers['geometry'], 'POINT(' . $rCache['longitude'] . ' ' . $rCache['latitude'] . ')'))
				{

						$sql=sql("INSERT INTO `cache_npa_areas` (`cache_id`, `npa_id`, `calculated`) VALUES ('&1', '&2', 1) ON DUPLICATE KEY UPDATE `calculated`=1", $rCache['cache_id'], $rLayers['id']);
					mysql_query($sql);
						
				}
			}
			mysql_free_result($rsLayers);
			
//			$sql=sql("UPDATE `caches` SET `need_npa_recalc`=0 WHERE `cache_id`='&1'", $rCache['cache_id']);
//					mysql_query($sql);
		}
		mysql_free_result($rsCache);
				db_disconnect();
	}
}
$cache_npa = new cache_npa_areas();
$cache_npa->run();
?>
