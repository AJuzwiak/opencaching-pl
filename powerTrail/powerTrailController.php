<?php
require_once __DIR__.'/../lib/db.php';
require_once __DIR__.'/powerTrailBase.php';

/**
 * 
 */
class powerTrailController {
	
	private $debug = true;	
	private $action;
	private $user;
	private $userPTs;
	private $ptAPI;
	private $allSeries;
	private $allCachesOfSelectedPt;
	private $powerTrailCachesUserLogsByCache;
	private $powerTrailDbRow;
	private $ptOwners;
	private $areOwnSeries = false;
	
	function __construct($user) 
	{
		if(isset($_REQUEST['ptAction'])) {
			$this->action = $_REQUEST['ptAction'];
		} else {
			$this->action = 'showAllSeries';
		}
		
		// self::debug($_POST, 'POST', __LINE__);
		// if(isset($_POST['createNewPowerTrail'])) $this->action = 'createNewPowerTrail';
		
		$this->ptAPI = new powerTrailBase;
		$this->user = $user;
	}
	
	public function run()
	{
		switch ($this->action) {
			case 'mySeries':
				$this->mySeries();
				break;
			case 'selectCaches':
				$this->getUserPTs();
				return $this->getUserCachesToChose();
				break;
			case 'createNewPowerTrail':
				$this->createNewPowerTrail();
				break;
			case 'showAllSeries':
				$this->getAllPowerTrails();
				break;
			case 'showSerie':
				$this->getPowerTrailCaches();
				break;		
			default:
				$this->getAllPowerTrails();
				break;
		}
	}

	private function mySeries(){
		// print $_SESSION['user_id'];
		$q = 'SELECT * FROM `PowerTrail` WHERE id IN (SELECT `PowerTrailId` FROM `PowerTrail_owners` WHERE `userId` = :1 ) ORDER BY cacheCount DESC';
		$db = new dataBase();
		$db->multiVariableQuery($q, $_SESSION['user_id']);
		$this->allSeries = $db->dbResultFetchAll();
		$this->action = 'showAllSeries';
		$this->areOwnSeries = true;
		
	}

	private function getAllPowerTrails()
	{
		$q = 'SELECT * FROM `PowerTrail` WHERE `status` = 1 and cacheCount > '.powerTrailBase::minimumCacheCount() .' ORDER BY cacheCount DESC';
		$db = new dataBase();
		$db->multiVariableQuery($q);
		$this->allSeries = $db->dbResultFetchAll();
	}
	
	private function getPowerTrailCaches()
	{
		$powerTrailId = isset($_REQUEST['ptrail'])?$_REQUEST['ptrail']:0;
		$db = new dataBase();
		$ptq = 'SELECT * FROM `PowerTrail` WHERE `id` = :1 LIMIT 1';
		$db->multiVariableQuery($ptq, $powerTrailId);
		$this->powerTrailDbRow = $db->dbResultFetch();
		
		$q = 'SELECT powerTrail_caches.isFinal, caches . * , user.username FROM  `caches` , user, powerTrail_caches WHERE cache_id IN ( SELECT  `cacheId` FROM  `powerTrail_caches` WHERE  `PowerTrailId` =:1) AND user.user_id = caches.user_id AND powerTrail_caches.cacheId = caches.cache_id ORDER BY caches.name';
		$db->multiVariableQuery($q, $powerTrailId);
		$this->allCachesOfSelectedPt = $db->dbResultFetchAll();
		
		$qr = 'SELECT `cache_id`, `date`, `text_html`, `text`  FROM `cache_logs` WHERE `cache_id` IN ( SELECT `cacheId` FROM `powerTrail_caches` WHERE `PowerTrailId` = :1) AND `user_id` = :2 AND `deleted` = 0 AND `type` = 1';
		isset($_SESSION['user_id']) ? $userId = $_SESSION['user_id'] : $userId = 0;
		$db->multiVariableQuery($qr, $powerTrailId, $userId);
		$powerTrailCacheLogsArr = $db->dbResultFetchAll();
		$powerTrailCachesUserLogsByCache = array();
		foreach ($powerTrailCacheLogsArr as $log) {
			$powerTrailCachesUserLogsByCache[$log['cache_id']] = array (
				'date' => $log['date'],
				'text_html' => $log['text_html'],
				'text' => $log['text'],
			);
		}
		// self::debug($powerTrailCacheLogsArr);
		// self::debug($powerTrailCachesUserLogsByCache);
		$this->powerTrailCachesUserLogsByCache = $powerTrailCachesUserLogsByCache;
		$this->findPtOwners($powerTrailId);
	}

	public function getPtOwners()
	{
		return $this->ptOwners;
	}

	public function getPowerTrailDbRow()
	{
		return $this->powerTrailDbRow;
	}

	public function getPowerTrailCachesUserLogsByCache()
	{
		return $this->powerTrailCachesUserLogsByCache;
	}
	
	public function getPowerTrailOwn() {
		return $this->areOwnSeries;
	}
	
	public function getAllCachesOfPt()
	{
		return $this->allCachesOfSelectedPt;
	}

	public function getUserPowerTrails()
	{
		return $this->userPTs;
	}
		
	public function getActionPerformed()
	{
		return $this->action;
	}
	
	public function getCountCachesAndUserFoundInPT()
	{
		$result['totalCachesCountInPowerTrail']	= count($this->allCachesOfSelectedPt);
		$result['cachesFoundByUser'] = count($this->powerTrailCachesUserLogsByCache);
		return $result;
	}
	
	public function getpowerTrails()
	{
		return $this->allSeries;	
	}
	
	private function createNewPowerTrail()
	{
		$this->action = 'createNewSerie';	
		// self::debug($_POST, 'POST', __LINE__);
		if(isset($_POST['powerTrailName']) && $_POST['powerTrailName'] != '' && $_POST['type'] != 0 && $_POST['status'] != 0)
		{
			print 'wchodzi<br/>';	
			$query = "INSERT INTO `PowerTrail`(`name`, `type`, `status`, `dateCreated`, `cacheCount`, `description`, `perccentRequired`) VALUES (:1,:2,:3,NOW(),0,:4,:5)";
			$db = new dataBase(false);
			$db->multiVariableQuery($query, strip_tags($_POST['powerTrailName']),(int) $_POST['type'], (int) $_POST['status'], htmlspecialchars($_POST['description']), (int) $_POST['dPercent']);
			$newProjectId = $db->lastInsertId();
			// exit;
			$query = "INSERT INTO `PowerTrail_owners`(`PowerTrailId`, `userId`, `privileages`) VALUES (:1,:2,:3)";
			$db->multiVariableQuery($query, $newProjectId, $this->user['userid'], 1);
			$logQuery = 'INSERT INTO `PowerTrail_actionsLog`(`PowerTrailId`, `userId`, `actionDateTime`, `actionType`, `description`) VALUES (:1,:2,NOW(),2,:3)';
			$db->multiVariableQuery($logQuery, $newProjectId,$this->user['userid'] ,$this->ptAPI->logActionTypes[1]['type']);
			header("location: powerTrail.php?ptAction=showSerie&ptrail=$newProjectId");
			// $this->mySeries();
			// $this->action = 'mySeries';
			return true;
		} 
		else 
		{
			return false;	
		}
		
	}
	
	private function getUserCachesToChose()
	{
		$query = "SELECT cache_id, wp_oc, PowerTrailId, name FROM `caches` LEFT JOIN powerTrail_caches ON powerTrail_caches.cacheId = caches.cache_id WHERE caches.status NOT IN (3,6) AND `user_id` = :1";
		$db = new dataBase;
		$db->multiVariableQuery($query, $this->user['userid']);
		$userCaches = $db->dbResultFetchAll();
		// self::debug($userCaches, 'user Caches', __LINE__);
		return $userCaches;
	}
	
	private function getUserPTs()
	{
		$query = "SELECT * FROM `PowerTrail`, PowerTrail_owners  WHERE  PowerTrail_owners.userId = :1 AND PowerTrail_owners.PowerTrailId = PowerTrail.id";
		$db = new dataBase();
		$db->multiVariableQuery($query, $this->user['userid']);
		$userPTs = $db->dbResultFetchAll();
		$this->userPTs = $userPTs;
		// self::debug($userPTs, 'user Power Trails', __LINE__);
		// self::debug($this->user['userid'], 'user Power Trails', __LINE__);
	}
	
	public function findPtOwners($powerTrailId){
		$query = 'SELECT `userId`, `privileages`, username FROM `PowerTrail_owners`, user WHERE `PowerTrailId` = :1 AND PowerTrail_owners.userId = user.user_id';
		$db = new dataBase();
		$db->multiVariableQuery($query, $powerTrailId);
		$owner = $db->dbResultFetchAll();
		foreach ($owner as $user) {
			$owners[$user['userId']] = array (
				'privileages' => $user['privileages'],
				'username' => $user['username'],
			);
		}
		$this->ptOwners = $owners;
	}
	
	public function debug($var, $name=null, $line=null)
	{
		//if($this->debug === false) return;	
		print '<font color=green><b>#'.$line."</b> $name, </font>(".__FILE__.") <pre>";
		print_r($var); 
		print '</pre>';
	}
}



// var_dump($_SESSION);
// var_dump($usr);