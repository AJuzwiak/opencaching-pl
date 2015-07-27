<?php
session_start();
// ini_set ('display_errors', on);
require "../lib/settings.inc.php";
header('Content-type: text/xml');

//$PER_PAGE = 500;	//number of caches at once on map
$ENCODING = "UTF-8";
function onTheList($theArray, $item)
	{
		for( $i=0;$i<count($theArray);$i++)
		{
			if( $theArray[$i] == $item )
				return $i;
			
		}
		return -1;
	}
	
function db_connect()
{
	global $dblink, $dbpconnect, $dbusername, $dbname, $dbserver, $dbpasswd, $dbpconnect;

	//connect to the database by the given method - no php error reporting!
	if ($dbpconnect == true)
	{
		$dblink = @mysql_pconnect($dbserver, $dbusername, $dbpasswd);
	}
	else
	{
		$dblink = @mysql_connect($dbserver, $dbusername, $dbpasswd);
	}

	if ($dblink != false)
	{
		//database connection established ... set the used database
		if (@mysql_select_db($dbname, $dblink) == false)
		{
			//error while setting the database ... disconnect
			db_disconnect();
			$dblink = false;
		}
	}
}

//disconnect the databse
function db_disconnect()
{
	global $dbpconnect, $dblink;

	//is connected and no persistent connect used?
	if (($dbpconnect == false) && ($dblink !== false))
	{
		mysql_close($dblink);
		$dblink = false;
	}
}

function typeLetter($intType) {
	switch($intType) {
	case 1: return "U"; break;		//unknown
	case 2: return "T"; break;		//traditional
	case 3: return "M"; break;		//multi
	case 4: return "V"; break;		//virtual
	case 5: return "W"; break;		//webcam
	case 6: return "E"; break;		//event
	case 7: return "Q"; break;		//quiz
	case 8: return "C"; break;		//math
	case 9: return "O"; break;		//mOving
	case 10: return "D"; break;	//Drive-in
	default: return "";
	}
}

function typeToInt($type) {
	switch($type) {
	case 'u': return 1; break;		//unknown
	case 't': return 2; break;		//traditional
	case 'm': return 3; break;		//multi
	case 'v': return 4; break;		//virtual
	case 'w': return 5; break;		//webcam
	case 'e': return 6; break;		//event
	case 'q': return 7; break;		//quiz
	case 'c': return 8; break;		//math
	case 'o': return 9; break;		//mOving
	case 'd': return 10; break;	//Drive-in
	default: return "";
	}
}

db_connect();

$latNE = mysql_escape_string($_GET['latNE']);
$lonNE = mysql_escape_string($_GET['lonNE']);
$latSW = mysql_escape_string($_GET['latSW']);
$lonSW = mysql_escape_string($_GET['lonSW']);

//$page = ((int)($_GET['page'])) * $PER_PAGE;

if(!isset($_GET['caches'])) {
	$PER_PAGE = 250; 
} else {
	$PER_PAGE = (int) ($_GET['caches']);
	if($PER_PAGE<10) $PER_PAGE = 10;
	if($PER_PAGE>500) $PER_PAGE = 500;
}
$page = ((int)($_GET['page'])) * $PER_PAGE;

$user_id = (int) $_GET['klaun'];

switch($_GET['order']) {
	case "1":
		$ORDERBY = "cache_id DESC";
		break;
	case "2":
		$ORDERBY = "name";
		break;
	case "3":
		$ORDERBY = "old";
		break;
	default:
		$ORDERBY = "cache_id DESC";
}

$filter_by_type_string = "";
$typy = array('u','t','m','v','w','e','q','c','o','d','I','W','Z','A','N','C','T','Y');

$filter = $_GET['filter'];

$only_active = " AND (foreign_caches.status = 1 OR foreign_caches.status = 2)";
for( $i=0;$i<strlen($filter);$i++)
{
	if( $i < 10)
	{
		if( $filter[$i] == 0 )
			$filter_by_type_string .= " AND foreign_caches.type != ".($i+1);
	}
	else
	{
		if( $i == 10 && $filter[$i] == 0 ) // I
			$filter_by_type_string .= " AND cache_id NOT IN (SELECT cache_id FROM cache_ignore WHERE user_id='".$user_id."')";
		
		if( $i == 11 && $filter[$i] == 0 ) // W
			$filter_by_type_string .= " AND cache_id NOT IN (SELECT cache_id FROM foreign_caches WHERE user_id='".$user_id."')";
		
		if( $i == 12 && $filter[$i] == 0 ) // Z
			$filter_by_type_string .= " AND cache_id NOT IN (SELECT cache_id FROM cache_logs WHERE deleted=0 AND user_id='".$user_id."' AND (type='1' OR type='8'))";
		
		
		if( $i == 13 && $filter[$i] == 0 ) // A
			$filter_by_type_string .= " AND (cache_id IN (SELECT cache_logs.cache_id FROM cache_logs WHERE deleted=0 AND cache_logs.user_id='".$user_id."' AND (cache_logs.type='1' OR cache_logs.type='8')) OR foreign_caches.user_id='".$user_id."')";
		
		if( $i == 14 && $filter[$i] == 0 ) // N
			$filter_by_type_string .= " AND foreign_caches.cache_id IN (SELECT cache_id FROM foreign_caches WHERE wp_oc IN (SELECT wp FROM gk_item_waypoint WHERE id IN (SELECT id FROM gk_item WHERE stateid<>1 AND stateid<>4 AND typeid<>2)) OR (wp_gc IN (SELECT wp FROM gk_item_waypoint WHERE id IN (SELECT id FROM gk_item WHERE stateid<>1 AND stateid<> 4 AND typeid<>2)) AND wp_gc <> '') OR (wp_nc IN (SELECT wp FROM gk_item_waypoint WHERE id IN (SELECT id FROM gk_item WHERE stateid<>1 AND stateid<>4 AND typeid<>2)) AND wp_nc <> '')) ";
			
		// 15 - RESERVED - DO NOT USE !!!	
		if( $i == 16 && $filter[$i] == 0 ) // T
			$only_active = " AND foreign_caches.status = 1";
		if( $i == 17 && $filter[$i] == 0 ) // Y
			$only_active = " AND foreign_caches.status = 2";
	}
}
//$only_active = " AND foreign_caches.status = 1";

$sql = "SELECT foreign_caches.cache_id, foreign_caches.name, foreign_caches.username, foreign_caches.wp_oc as wp, /*foreign_caches.votes, foreign_caches.score,*/ foreign_caches.topratings, foreign_caches.latitude, foreign_caches.longitude, foreign_caches.type, foreign_caches.status as status, datediff(now(), foreign_caches.date_hidden) as old, foreign_caches.user_id, IF(cache_id IN (SELECT cache_id FROM cache_logs WHERE deleted=0 AND user_id='".$user_id."' AND (type=1 OR type=8)), 1, 0) as found FROM foreign_caches WHERE ((foreign_caches.latitude>'".$latSW."' AND foreign_caches.latitude<'".$latNE."') AND (foreign_caches.longitude>'".$lonSW."' AND foreign_caches.longitude<'".$lonNE."')) ".$only_active." ".$filter_by_type_string." ORDER BY ".$ORDERBY." LIMIT ".$page.", " . $PER_PAGE;
$result = mysql_query($sql);

$sysres = mysql_query("SELECT count(cache_id) as num FROM foreign_caches WHERE (foreign_caches.status = 1) AND ((foreign_caches.latitude>'".$latSW."' AND foreign_caches.latitude<'".$latNE."') AND (foreign_caches.longitude>'".$lonSW."' AND foreign_caches.longitude<'".$lonNE."'));");

echo "<?xml version=\"1.0\" encoding=\"".$ENCODING."\"?>\n";
echo "<markers>\n";
while($res = mysql_fetch_array($result)) {

	if( onTheList($_SESSION['print_list'], $res['cache_id']) == -1 )
		$druk = "druk=\"y\"";
	else $druk = "druk=\"n\"";
	$founds_query = mysql_query("SELECT count(*) FROM cache_logs WHERE deleted=0 AND cache_id = ".$res['cache_id']." AND (type=1 OR type=8)");
	$founds = mysql_result($founds_query,0);
	$notfounds_query = mysql_query("SELECT count(*) FROM cache_logs WHERE deleted=0 AND cache_id = ".$res['cache_id']." AND type=2");
	$notfounds = mysql_result($notfounds_query,0);

	if( $res['votes'] > 2 ) $score = $res['score']; else $score="";
	echo "<marker id=\"".htmlspecialchars($res['cache_id'])."\" name=\"".htmlspecialchars($res['name'])."\" lat=\"".htmlspecialchars($res['latitude'])."\" lng=\"".htmlspecialchars($res['longitude'])."\" owner=\"".htmlspecialchars($res['username'])."\" owner_id=\"".htmlspecialchars($res['user_id'])."\" type=\"".htmlspecialchars(typeLetter($res['type']))."\" found=\"".$res['found']."\" old=\"".htmlspecialchars($res['old'])."\" score=\"".htmlspecialchars($score)."\" topratings=\"".htmlspecialchars($res['topratings'])."\"  notfounds=\"".htmlspecialchars($notfounds)."\" votes=\"".htmlspecialchars($res['votes'])."\" founds=\"".htmlspecialchars($founds)."\" status=\"".htmlspecialchars($res['status'])."\" wp=\"".htmlspecialchars($res['wp'])."\" ".$druk."/>\n";
}
if($res2 = mysql_fetch_array($sysres)) {
	for($i=0; $i<$res2['num']/$PER_PAGE; $i++) $pages .= "<a href=\"javascript:load_data(".$i.");\">".($i+1)."</a> ";
	$pages = htmlspecialchars($pages);
	echo "<data count=\"".$res2['num']."\" pager=\"".$pages."\" />\n";
}
echo "</markers>";
?>