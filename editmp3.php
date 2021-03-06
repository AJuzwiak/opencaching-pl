<?php

use Utils\Database\XDb;
//prepare the templates and include all neccessary
require_once('./lib/common.inc.php');

$message = false;

//Preprocessing
if ($error == false) {
    //user logged in?
    if ($usr == false) {
        $target = urlencode(tpl_get_current_page());
        tpl_redirect('login.php?target=' . $target);
    } else {
        $tplname = 'editmp3';
        require_once($stylepath . '/editmp3.inc.php');

        $uuid = isset($_REQUEST['uuid']) ? $_REQUEST['uuid'] : 0;
        if (!$uuid)
            $message = $message_mp3_not_found;

        if (!$message) {
            // read from databese and check owner

            $stmt = XDb::xSql(
                "SELECT `mp3`.`display`, `mp3`.`title`, `mp3`.`object_id`, `mp3`.`object_type`,
                        `caches`.`name`, `caches`.`cache_id` FROM `mp3`, `caches`
                WHERE `caches`.`cache_id`=`mp3`.`object_id` AND `mp3`.`uuid`= ? AND `mp3`.`user_id`=? LIMIT 1",
                $uuid, $usr['userid']);

            if (!$stmt) {
                //query returns error
                $message = $message_title_internal;
            } else {
                if (!$row = XDb::xFetchArray($stmt)) {
                    //no records
                    $message = $message_mp3_not_found;
                }
            }
        }

        if (!$message) {

            if (isset($_POST['submit'])) {

                if ($_FILES['file']['name'] != '') {
                    // check of the file was uploaded successfully
                    if ($_FILES['file']['error'] != 0) {
                        // oops ... no idea what I should do now
                        $tplname = 'message';
                        tpl_set_var('messagetitle', $message_title_internal);
                        tpl_set_var('message_start', '');
                        tpl_set_var('message_end', '');
                        tpl_set_var('message', $message_internal);
                        tpl_BuildTemplate();
                        exit;
                    } else {
                        // file extension ok?
                        $fna = mb_split('\\.', $_FILES['file']['name']);
                        $extension = mb_strtolower($fna[count($fna) - 1]);

                        if (mb_strpos($mp3extensions, ';' . $extension . ';') === false) {
                            $tplname = 'message';
                            tpl_set_var('messagetitle', $message_title_wrongext);
                            tpl_set_var('message_start', '');
                            tpl_set_var('message_end', '');
                            tpl_set_var('message', $message_wrongext);
                            tpl_BuildTemplate();
                            exit;
                        }

                        // file too big?
                        if ($_FILES['file']['size'] > $maxmp3size) {
                            $tplname = 'message';
                            tpl_set_var('messagetitle', $message_title_toobig);
                            tpl_set_var('message_start', '');
                            tpl_set_var('message_end', '');
                            tpl_set_var('message', $message_toobig);
                            tpl_BuildTemplate();
                            exit;
                        }

                        // move file
                        //echo $_FILES['file']['tmp_name'], $mp3dir . '/' . $uuid . '.' . $extension;
                        move_uploaded_file($_FILES['file']['tmp_name'], $mp3dir . '/' . $uuid . '.' . $extension);
                    }
                }


                // store

                $row['display'] = isset($_REQUEST['notdisplay']) ? $_REQUEST['notdisplay'] : 0;
                if ($row['display'] == 0)
                    $row['display'] = 1;
                else
                    $row['display'] = 0; // reverse

                $row['title'] = isset($_REQUEST['title']) ? stripslashes($_REQUEST['title']) : '';

                if ($row['title']) {
                    XDb::xSql(
                        "UPDATE `mp3` SET `title`= ?, `display`= ?, `last_modified`=NOW()
                         WHERE `uuid`= ? ",
                        $row['title'], (($row['display'] == 1) ? '1' : '0'), $uuid);

                    switch ($row['object_type']) {
                        // log - currently not used, because log mp3 cannot be edited
                        case 1:
                            XDb::xSql(
                                "UPDATE `cache_logs` SET `last_modified`=NOW() WHERE `id`= ?",
                                $row['object_id']);
                            break;

                        // cache
                        case 2:
                            XDb::xSql(
                                "UPDATE `caches` SET `last_modified`=NOW() WHERE `cache_id`= ?",
                                $row['object_id']);
                            break;
                    }

                    tpl_redirect('editcache.php?cacheid=' . urlencode($row['object_id']));
                }
            }
        }

        if (!$message) {
            // display
            $tplname = 'editmp3';
            $tpl_subtitle = htmlspecialchars($row['name'], ENT_COMPAT, 'UTF-8') . ' - ';
            tpl_set_var('cacheid', htmlspecialchars($row['cache_id'], ENT_COMPAT, 'UTF-8'));
            tpl_set_var('cachename', htmlspecialchars($row['name'], ENT_COMPAT, 'UTF-8'));
            tpl_set_var('title', htmlspecialchars($row['title'], ENT_COMPAT, 'UTF-8'));
            if ($row['title'] <= "")
                tpl_set_var('errnotitledesc', $errnotitledesc);
            else
                tpl_set_var('errnotitledesc', "");
            tpl_set_var('uuid', htmlspecialchars($uuid, ENT_COMPAT, 'UTF-8'));
            tpl_set_var('notdisplaychecked', $row['display'] == '0' ? 'checked' : '');

            if ($row['object_type'] == "2") {
                tpl_set_var('mp3typedesc', $mp3typedesc_cache);
                tpl_set_var('begin_cacheonly', "");
                tpl_set_var('end_cacheonly', "");
            } else if ($row['object_type'] == "1") {
                tpl_set_var('mp3typedesc', $mp3typedesc_log);
                tpl_set_var('begin_cacheonly', "<!--");
                tpl_set_var('end_cacheonly', "-->");
            }
        } else {
            $tplname = 'message';
            tpl_set_var('messagetitle', $message);
            tpl_set_var('message_start', '');
            tpl_set_var('message_end', '');
            tpl_set_var('message', $message);
        }
    }
}

//make the template and send it out
tpl_BuildTemplate();
