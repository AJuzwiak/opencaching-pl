<?php
	$rootpath = "../";
	require_once('./common.inc.php');
	require_once($rootpath . 'lib/caches.inc.php');	

	function getUsername($user_id)
	{
		$sql = "SELECT username FROM user WHERE user_id=".intval($user_id);
		return @mysql_result(@mysql_query($sql),0);
	}
	
	

	function distance4zoom($zoom)
	{
		switch($zoom)
		{
			case 6:
				return 0.0008000;
			case 7:
				return 0.0004500;
			case 8:
				return 0.0002800;
			case 9:
				return 0.0001400;
			case 10:
				return 0.0000700;
			case 11:
				return 0.0000440;
			case 12:
				return 0.0000240;
			case 13:
				return 0.0000120;
			case 14:
				return 0.0000096;
			case 15:
				return 0.0000048;
			case 16:
				return 0.0000024;
			case 17:
				return 0.0000012;
			case 18:
				return 0.0000007;
			case 19:
				return 0.0000004;
			default:
				return 0.0;
		}

	}
	function mod4zoom($zoom)
	{
		switch($zoom)
		{
			case 6:
				return -0.06000;
			case 7:
				return -0.04800;
			case 8:
				return -0.02400;
			case 9:
				return -0.01200;
			case 10:
				return -0.00600;
			case 11:
				return -0.00380;
			case 12:
				return -0.00250;
			case 13:
				return -0.00140;
			case 14:
				return -0.00080;
			case 15:
				return -0.00040;
			case 16:
				return -0.00020;
			case 17:
				return -0.00010;
			case 18:
				return -0.00005;
			case 19:
				return -0.00003;
			default:
				return 0.0;
		}

	}

	if(isset($_GET['searchdata']) && preg_match('/^[a-f0-9]+/', $_GET['searchdata'])) {
		$searchdata = $_GET['searchdata'];
	}

	$zoom = $_GET['zoom'];
	$lat = ($_GET['lat'])+0+mod4zoom($zoom);
	$lon = ($_GET['lon'])+0;

	$latmin = $lat + mod4zoom($zoom);
	$latmax = $lat - mod4zoom($zoom);
	$lonmin = $lon + mod4zoom($zoom);
	$lonmax = $lon - mod4zoom($zoom);

	$user_id = intval($_GET['userid']);
	$username = getUsername($user_id);
	
	$writer = new XMLWriter();
	
	$writer->openURI('php://output');
	$writer->startDocument('1.0');
	$writer->setIndent(4);
	$writer->startElement('caches');

	if(!isset($searchdata)) {

	if( $_GET['be_ftf'] == "true" )
	{
		$own_not_attempt = "caches.founds>0";
		$_GET['h_temp_unavail'] = "true";
		$_GET['h_arch'] = "true";
	}
	else
		$own_not_attempt = "caches.cache_id IN (SELECT cache_id FROM cache_logs WHERE deleted=0 AND user_id='".sql_escape($user_id)."' AND (type=1 OR type=8))";
	
	$hide_by_type = "";
	if( $_GET['h_u'] == "true" )
		$hide_by_type .= " AND caches.type<>1 ";
	if( $_GET['h_t'] == "true" )
		$hide_by_type .= " AND caches.type<>2 ";
	if( $_GET['h_m'] == "true" )
		$hide_by_type .= " AND caches.type<>3 ";
	if( $_GET['h_v'] == "true" )
		$hide_by_type .= " AND caches.type<>4 ";
	if( $_GET['h_w'] == "true" )
		$hide_by_type .= " AND caches.type<>5 ";
	if( $_GET['h_e'] == "true" )
		$hide_by_type .= " AND caches.type<>6 ";
	if( $_GET['h_q'] == "true" )
		$hide_by_type .= " AND caches.type<>7 ";
	if( $_GET['h_o'] == "true" )
		$hide_by_type .= " AND caches.type<>8 ";
	if( $_GET['h_owncache'] == "true" )
		$hide_by_type .= " AND caches.type<>10 ";
	if( $_GET['h_own'] == "true" )
		$hide_by_type .= " AND caches.user_id<>".$user_id." ";
	if( $_GET['h_found'] == "true" )
		$hide_by_type .= " AND IF($own_not_attempt, 1, 0)<>1 ";
	if( $_GET['be_ftf'] == "true" )
		$hide_by_type .= " AND (IF($own_not_attempt, 1, 0)<>1 AND caches.status=1 AND caches.user_id<>".$user_id.") ";
	if( $_GET['h_avail'] == "true" )
		$hide_by_type .= " AND caches.status<>1 ";
	if( $_GET['h_temp_unavail'] == "true" )
		$hide_by_type .= " AND caches.status<>2 ";
	if( $_GET['h_arch'] == "true" )
		$hide_by_type .= " AND caches.status<>3 ";
	if( $_GET['h_noattempt'] == "true" )
		$hide_by_type .= " AND IF($own_not_attempt, 1, 0)=1 ";
	if( $_GET['h_ignored'] == "true" )
		$hide_by_type .= " AND cache_ignore.id IS NULL ";
	if( isset($_GET['min_score']) && isset($_GET['max_score']))
	{
		$score_filter = " AND ((caches.score BETWEEN ".floatval($_GET['min_score'])." AND ".floatval($_GET['max_score'])." AND caches.votes>=3 ";
		if( $_GET['h_noscore'] == "true" )
		{
			$score_filter .= ") OR (caches.votes<3";
		}
		$score_filter .= ")) ";
	}
	
	// enable searching for ignored caches
	if( $_GET['h_ignored'] == "true" )
	{
		$h_sel_ignored = "cache_ignore.id as ignored,";
		$h_ignored = " LEFT JOIN cache_ignore ON (cache_ignore.user_id=".$user_id." AND cache_ignore.cache_id=caches.cache_id) ";
	}
	else
	{
		$h_sel_ignored = "0 as ignored,";
		$h_ignored = "";
	}

	if( $_GET['h_nogeokret'] == "true" )
		$filter_by_type_string .= " AND caches.cache_id IN (SELECT cache_id FROM caches WHERE wp_oc IN (SELECT wp FROM gk_item_waypoint WHERE id IN (SELECT id FROM gk_item WHERE stateid<>1 AND stateid<>4 AND stateid<>5 AND typeid<>2)) OR (wp_gc IN (SELECT wp FROM gk_item_waypoint WHERE id IN (SELECT id FROM gk_item WHERE stateid<>1 AND stateid<> 4 AND typeid<>2)) AND wp_gc <> '') OR (wp_nc IN (SELECT wp FROM gk_item_waypoint WHERE id IN (SELECT id FROM gk_item WHERE stateid<>1 AND stateid<>4 AND typeid<>2)) AND wp_nc <> '')) ";
	else
		$filter_by_type_string = "";

	$sql ="SELECT $h_sel_ignored caches.cache_id, IF($own_not_attempt, 1, 0) as found, caches.name, caches.node, user.username, caches.wp_oc as wp, caches.votes, caches.score, caches.topratings, caches.latitude, caches.longitude, caches.type, caches.size, caches.status as status, datediff(now(), caches.date_hidden) as old, caches.user_id, caches.founds, caches.notfounds FROM user, caches 
	$h_ignored
	WHERE caches.user_id = user.user_id AND caches.status < 4 AND
	(caches.latitude BETWEEN $latmin AND $latmax) AND (caches.longitude BETWEEN $lonmin AND $lonmax)
	".$hide_by_type.$filter_by_type_string.$score_filter." LIMIT 1";
	
	
	// for foreign caches -------------------------------------------------------------------------------------
	
	
	$hide_by_type = "";
	if( $_GET['h_u'] == "true" )
		$hide_by_type .= " AND foreign_caches.type<>1 ";
	if( $_GET['h_t'] == "true" )
		$hide_by_type .= " AND foreign_caches.type<>2 ";
	if( $_GET['h_m'] == "true" )
		$hide_by_type .= " AND foreign_caches.type<>3 ";
	if( $_GET['h_v'] == "true" )
		$hide_by_type .= " AND foreign_caches.type<>4 ";
	if( $_GET['h_w'] == "true" )
		$hide_by_type .= " AND foreign_caches.type<>5 ";
	if( $_GET['h_e'] == "true" )
		$hide_by_type .= " AND foreign_caches.type<>6 ";
	if( $_GET['h_q'] == "true" )
		$hide_by_type .= " AND foreign_caches.type<>7 ";
	if( $_GET['h_o'] == "true" )
		$hide_by_type .= " AND foreign_caches.type<>8 ";
	if( $_GET['h_owncache'] == "true" )
		$hide_by_type .= " AND foreign_caches.type<>10 ";
	//if( $_GET['h_own'] == "true" )
	//	$hide_by_type .= " AND foreign_caches.username<>'".$username."'";
	//if( $_GET['h_found'] == "true" )
	//	$hide_by_type .= " AND IF($own_not_attempt, 1, 0)<>1 ";
	//if( $_GET['be_ftf'] == "true" )
	//	$hide_by_type .= " AND (IF($own_not_attempt, 1, 0)<>1 AND foreign_caches.status=1 AND foreign_caches.user_id<>".$user_id.") ";
	if( $_GET['h_avail'] == "true" )
		$hide_by_type .= " AND foreign_caches.status<>1 ";
	if( $_GET['h_temp_unavail'] == "true" )
		$hide_by_type .= " AND foreign_caches.status<>2 ";
	if( $_GET['h_arch'] == "true" )
		$hide_by_type .= " AND foreign_caches.status<>3 ";
	//if( $_GET['h_noattempt'] == "true" )
	//	$hide_by_type .= " AND IF($own_not_attempt, 1, 0)=1 ";
	
	// enable searching for ignored caches
/*	if( $_GET['h_ignored'] == "true" )
	{
		$h_sel_ignored = "cache_ignore.id as ignored,";
		$h_ignored = " LEFT JOIN cache_ignore ON (cache_ignore.user_id='$user_id' AND cache_ignore.cache_id=foreign_caches.cache_id) ";
	}
	else
*/
	{
		$h_sel_ignored = "";
		$h_ignored = "";
	}
	
	if( $_GET['h_nogeokret'] == "true" )
		$filter_by_type_string = " AND foreign_caches.cache_id IN (SELECT cache_id FROM foreign_caches WHERE wp_oc IN (SELECT wp FROM gk_item_waypoint WHERE id IN (SELECT id FROM gk_item WHERE stateid<>1 AND stateid<>4 AND typeid<>2)) OR (wp_gc IN (SELECT wp FROM gk_item_waypoint WHERE id IN (SELECT id FROM gk_item WHERE stateid<>1 AND stateid<> 4 AND typeid<>2)) AND wp_gc <> '') OR (wp_nc IN (SELECT wp FROM gk_item_waypoint WHERE id IN (SELECT id FROM gk_item WHERE stateid<>1 AND stateid<>4 AND typeid<>2)) AND wp_nc <> '')) ";
	else
		$filter_by_type_string = "";

	
	$sql_foreign ="SELECT foreign_caches.cache_id, foreign_caches.name, foreign_caches.username, foreign_caches.node, foreign_caches.wp_oc as wp, foreign_caches.topratings, foreign_caches.latitude, foreign_caches.longitude, foreign_caches.type, foreign_caches.size, foreign_caches.status as status, datediff(now(), foreign_caches.date_hidden) as old, foreign_caches.founds, foreign_caches.notfounds FROM foreign_caches 
	WHERE foreign_caches.status < 4 AND
	(foreign_caches.latitude BETWEEN $latmin AND $latmax) AND (foreign_caches.longitude BETWEEN $lonmin AND $lonmax)
	".$hide_by_type.$filter_by_type_string." LIMIT 1";

	if(!($_GET['h_pl']=="false"))
	{
		$query = mysql_query($sql);
		$cache = mysql_fetch_array($query);
	}
	else
		$cache = 0;

	if(!($_GET['h_de']=="false"))
	{
		$query_foreign = mysql_query($sql_foreign);
		$cache_foreign = mysql_fetch_array($query_foreign);
	}
	else
		$cache_foreign = 0;

	if( ($cache == 0) || ($cache['cache_id'] == "") )
		$cache = $cache_foreign;

	}
	else { // searchdata
		mysql_query("CREATE TEMPORARY TABLE cache_ids (id INTEGER PRIMARY KEY);");
		mysql_query("LOAD DATA LOCAL INFILE '".$dynbasepath."/searchdata/".$searchdata."' INTO TABLE cache_ids FIELDS TERMINATED BY ' '  LINES TERMINATED BY '\\n' (id);");


		$own_not_attempt = "caches.cache_id IN (SELECT cache_id FROM cache_logs WHERE deleted=0 AND user_id='".sql_escape($user_id)."' AND (type=1 OR type=8))";

		$sql ="SELECT caches.cache_id, IF($own_not_attempt, 1, 0) as found, caches.name, caches.node, user.username, caches.wp_oc as wp, caches.votes, caches.score, caches.topratings, caches.latitude, caches.longitude, caches.type, caches.size, caches.status as status, datediff(now(), caches.date_hidden) as old, caches.user_id, caches.founds, caches.notfounds FROM user, caches 
		WHERE caches.cache_id IN (SELECT * FROM cache_ids) AND caches.user_id = user.user_id AND caches.status < 4 AND
		(caches.latitude BETWEEN $latmin AND $latmax) AND (caches.longitude BETWEEN $lonmin AND $lonmax) LIMIT 1";

//		print $sql;
		$query = mysql_query($sql);
//		print mysql_error() . "\n";
		$cache = mysql_fetch_array($query);
		mysql_query("DROP TABLE cache_ids");

	}
	
	//while( $cache = mysql_fetch_array($query) )
	{
		$writer->startElement("cache");
		
		$writer->writeAttribute('cache_id', $cache['cache_id']);
		@$writer->writeAttribute('name', addslashes($cache['name']));
		@$writer->writeAttribute('username', addslashes($cache['username']));
		$writer->writeAttribute('wp', $cache['wp']);
		$writer->writeAttribute('votes', $cache['votes']);
		$writer->writeAttribute('score', score2rating($cache['score']));
		$writer->writeAttribute('topratings', $cache['topratings']);
		$writer->writeAttribute('lat', $cache['latitude']);
		$writer->writeAttribute('lon', $cache['longitude']);
		$writer->writeAttribute('type', $cache['type']);
 		$writer->writeAttribute('size', htmlspecialchars( cache_size_from_id($cache['size'], $lang), ENT_COMPAT, 'UTF-8'));	
		$writer->writeAttribute('status', $cache['status']);
		$writer->writeAttribute('user_id', $cache['user_id']);
		$writer->writeAttribute('founds', $cache['founds']);
		$writer->writeAttribute('notfounds', $cache['notfounds']);
		$writer->writeAttribute('node', $cache['node']);
		
		// End cache
		$writer->endElement();
	}

	if(isset($query)) {
		mysql_free_result($query);
	}
	if(isset($query_foreign)) {
		mysql_free_result($query_foreign);
	}

	// End caches
	$writer->endElement();
	$writer->endDocument();
	$writer->flush();

?>
