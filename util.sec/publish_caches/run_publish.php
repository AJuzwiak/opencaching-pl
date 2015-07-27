<?php
 /***************************************************************************
        ./util/publish_caches/run_publish.php
                                                            -------------------
        begin                : Sat September 2 2006
        copyright            : (C) 2005 The OpenCaching Group
        forum contact at     : http://www.opencaching.com/phpBB2

    ***************************************************************************/

 /***************************************************************************

        Unicode Reminder メモ

        Ggf. muss die Location des php-Binaries angepasst werden.

        Prueft auf wartende Caches, deren Veröffentlichungszeitpunkt
        gekommen ist und veröffentlicht sie.

    ***************************************************************************/
//ini_set ('display_errors', On);

    $rootpath = '../../';
    require_once($rootpath . 'lib/clicompatbase.inc.php');
    require_once('settings.inc.php');
    require_once($rootpath . 'lib/eventhandler.inc.php');

/* begin db connect */
    db_connect();
    if ($dblink === false)
    {
        echo 'Unable to connect to database';
        exit;
    }
/* end db connect */

    $rsPublish = sql("  SELECT `cache_id`, `user_id`
                FROM `caches`
                WHERE `status` = 5
                  AND `date_activate` <= NOW()");

    while($rPublish = sql_fetch_array($rsPublish))
    {
        $userid = $rPublish['user_id'];
        $cacheid = $rPublish['cache_id'];

        // update cache status to active
        sql("UPDATE `caches` SET `status`=1, `date_activate`=NULL, `last_modified`=NOW() WHERE `cache_id`='&1'", $cacheid);

        // send events
        touchCache($cacheid);
        event_new_cache($userid);
        event_notify_new_cache($cacheid);
    }
    mysql_free_result($rsPublish);

?>
