<?php
  function normTo100($value, $sum)
  {
  	return $value * 100 / $sum;
  }
  setlocale(LC_TIME, 'pl_PL.UTF-8');
 
  require('../lib/web.inc.php');
  sql('USE `ocpl`');

  // Start date of Opencaching
  $startDate = mktime(0, 0, 0, 1, 1, 2006);
	
	// Get data 
  $rsTypes = sql('SELECT COUNT(`caches`.`type`) `count`, `cache_type`.`pl` `type`, `cache_type`.`color` FROM `caches` INNER JOIN `cache_type` ON (`caches`.`type`=`cache_type`.`id`) WHERE `status`=1 GROUP BY `caches`.`type` ORDER BY `count` DESC');
	
  $yData = array();
  $xData = array();
  $colors = array();
	$url = "http://chart.apis.google.com/chart?chs=550x200&chd=t:";
	$sum = 0;
  while ($rTypes = mysql_fetch_array($rsTypes))
  {
    $yData[] = ' (' . $rTypes['count'] . ') ' . $rTypes['type'];
		$xData[] = $rTypes['count'];
    $colors[] = substr($rTypes['color'], 1);
		$sum += $rTypes['count'];
  }
  mysql_free_result($rsTypes);
	foreach( $xData as $count )
	{
		$url .= normTo100($count, $sum).",";
	}
	
	$url = substr($url, 0, -1);
	$url .= "&cht=p3&chl=";
	
	foreach( $yData as $label )
	{
		$url .= urlencode($label)."|";
	}
	$url = substr($url, 0, -1);
	
	$url .= "&chco=";
	foreach( $colors as $color )
	{
		$url .= urlencode($color).",";
	}
	$url = substr($url, 0, -1);
	header("Content-type: image/png");
	include( $url );
?>
