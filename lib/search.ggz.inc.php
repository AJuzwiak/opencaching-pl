<?php
    /***************************************************************************
            ./lib/search.zip.inc.php
    -------------------
                    begin                : January 28 2012
                    copyright            : (C) 2012 The OpenCaching Group
                    forum contact at     : http://forum.opencaching.pl

    ***************************************************************************/

    /***************************************************************************
    *
    *   This program is free software; you can redistribute it and/or modify
    *   it under the terms of the GNU General Public License as published by
    *   the Free Software Foundation; either version 2 of the License, or
    *   (at your option) any later version.
    *
    ***************************************************************************/

    /****************************************************************************

            Unicode Reminder ??

            Garmin GGZ search output (gpx + index)
            based on search.*.inc.php
            by Boguś z Polska (opencaching.pl)

    ****************************************************************************/

    function call_okapi($usr, $waypoints, $lang, $file_base_name, $zip_part)
    { 

        $okapi_response =  \okapi\Facade::service_call('services/caches/formatters/ggz',
    		$usr['userid'],
    		array(
				'cache_codes' => $waypoints,
				'langpref' => $lang,
				'ns_ground' => 'true',
				'ns_ox' => 'true',
				'images' => 'none',
				'attrs' => 'ox:tags',
				'trackables' => 'desc:count',
				'alt_wpts' => 'true',
				'recommendations' => 'desc:count',
				'latest_logs' => 'true',
				'lpc' => 'all',
				'my_notes' => isset($usr) ? "desc:text" : "none",
				'location_source'=> 'alt_wpt:user-coords',
				'location_change_prefix' => '(F)'));
        // Modifying OKAPI's default HTTP Response headers.
        $okapi_response->content_disposition = 'attachment; filename=' . $file_base_name . (($zip_part!=0)?'-'.$zip_part:'') . '.ggz';
        return $okapi_response;
    }

    function generate_link_content($queryid, $file_base_name, $zip_part)
    {
        $zipname = 'ocpl'.$queryid.'.ggz?startat=0&count=max&zippart='.$zip_part.(isset($_GET['okapidebug'])?'&okapidebug':'');
        $link_content = '<li><a class="links" href="'.$zipname.'" title="Garmin GGZ file (part '.$zip_part.')">'.$file_base_name.'-'.$zip_part.'.ggz</a></li>';
        return $link_content;
    }

    // reflect okapi limit of allowed geocache codes per invocation
    function get_max_caches_per_call()
    {
        return 500;
    }
    
    function get_pagination_template()
    {
        return 'garminggz';
    }
    
    function get_pagination_page_title()
    {
        return tr('GarminZip_01') . ': Garmin GGZ';
    }
    // all the logic is done here
    include 'search.okapi.inc.php';

?>
