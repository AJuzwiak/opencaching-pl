<?php
/***************************************************************************
												  ./lang/de/stdstyle/search.inc.php
															-------------------
		begin                : July 25 2004
		copyright            : (C) 2004 The OpenCaching Group
		forum contact at     : http://www.opencaching.com/phpBB2

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

	 set template specific language variables

 ****************************************************************************/

 	$outputformat_notexist = 'Output format does not exist!';
 	$error_query_not_found = 'The retrieval query could not be implemented, please enter the search data again.';
 	$safelink = '<a href="query.php?action=save&amp;queryid={queryid}">Save</a>';

 	$caches_newstring = '<b>NEW</b>&nbsp;';
 	$caches_olddays = 7;

	$next_img = '<img src="'.$stylepath.'/images/action/16x16-next.png" alt="&gt;" />';
	$prev_img = '<img src="'.$stylepath.'/images/action/16x16-prev.png" alt="&lt;" />';
	$last_img = '<img src="'.$stylepath.'/images/action/16x16-last.png" alt="&gt;&gt;" />';
	$first_img = '<img src="'.$stylepath.'/images/action/16x16-first.png" alt="&lt;&lt;;" />';
	$next_img_inactive = '<img src="'.$stylepath.'/images/action/16x16-next_inactive.png" alt="&gt;" />';
	$prev_img_inactive = '<img src="'.$stylepath.'/images/action/16x16-prev_inactive.png" alt="&lt;" />';
	$last_img_inactive = '<img src="'.$stylepath.'/images/action/16x16-last_inactive.png" alt="&gt;&gt;" />';
	$first_img_inactive = '<img src="'.$stylepath.'/images/action/16x16-first_inactive.png" alt="&lt;&lt;" />';
	
 	$bgcolor1 = 'bgcolor1';			// even lines
 	$bgcolor2 = 'bgcolor2';			// odd lines
	$bgcolor_found = "#66FFCC";		// if cache was found by user
	$bgcolor_owner = "#ffffc5";		// if user is owner
	$bgcolor_inactive = "#fafafa";	// if cache is inactive

 	$logdateformat = 'd.m.Y';
 	$logpics[1] = '<img alt="Found" border="0" src="images/ok.gif" />';
 	$logpics[2] = '<img alt="Not Found" border="0" src="images/redcross.gif" />';
 	$logpics[3] = '<img alt="Notes" border="0" src="images/info.gif" />';

 	$diffpics[2] = 'diff-10.gif';
 	$diffpics[3] = 'diff-15.gif';
 	$diffpics[4] = 'diff-20.gif';
 	$diffpics[5] = 'diff-25.gif';
 	$diffpics[6] = 'diff-30.gif';
 	$diffpics[7] = 'diff-35.gif';
 	$diffpics[8] = 'diff-40.gif';
 	$diffpics[9] = 'diff-45.gif';
 	$diffpics[10] = 'diff-50.gif';

 	$terrpics[2] = 'terr-10.gif';
 	$terrpics[3] = 'terr-15.gif';
 	$terrpics[4] = 'terr-20.gif';
 	$terrpics[5] = 'terr-25.gif';
 	$terrpics[6] = 'terr-30.gif';
 	$terrpics[7] = 'terr-35.gif';
 	$terrpics[8] = 'terr-40.gif';
 	$terrpics[9] = 'terr-45.gif';
 	$terrpics[10] = 'terr-50.gif';

 	$terrpics[1] = 'rat-10.gif';
 	$terrpics[2] = 'rat-20.gif';
 	$terrpics[3] = 'rat-30.gif';
 	$terrpics[4] = 'rat-40.gif';
 	$terrpics[5] = 'rat-50.gif';

	$difficulty_text_diff = "Difficulty: %01.1f to 5.0";
	$difficulty_text_terr = "Terrain: %01.1f to 5.0";
	$rating_text = "Rating: {rating}%";
	$not_rated = 'Not rated';

	$error_plz = '<tr><td><span class="errormsg">You must enter a name</span></td></tr>';
	$error_ort = '<tr><td><span class="errormsg">Place not found</span></td></tr>';
	$error_locidnocoords = '<tr><td><span class="errormsg">Coordinates for the selected location do not exist.</span></td></tr>';
	$error_noort = '<tr><td><span class="errormsg">Cannot find the coordinates for the designated village</span></td></tr>';
	$error_nofulltext = '<tr><td colspan="3"><span class="errormsg">Invalid entry. Change the name to search.</span></td></tr>';

	$gns_countries['DE'] = 'Niemcy';
	$gns_countries['AU'] = 'Austria';
	$gns_countries['SZ'] = 'Szwajcaria';
	$gns_countries['PL'] = 'Polska';
	$gns_countries['EZ'] = 'Czechy';
	$gns_countries['LO'] = 'Slowenia';
	$gns_countries['LH'] = 'Litwa';
	$gns_countries['UP'] = 'Ukraina';
	
	$default_lang = 'SV';
	$search_all_countries = '<option value="" selected="selected">All countries</option>';
	//$search_all_cachetypes = '<option value="" selected="selected">Wszystkie typy skrzynek</option>';

	$cache_attrib_jsarray_line = "new Array('{id}', {state}, '{text_long}', '{icon}', '{icon_no}', '{icon_undef}', '{category}')";
	$cache_attrib_img_line = '<img id="attrimg{id}" src="{icon}" title="{text_long}" alt="{text_long}" onmousedown="switchAttribute({id})" style="cursor: pointer;" />&nbsp;';

function dateDiff($interval, $dateTimeBegin, $dateTimeEnd)
{
  //Parse about any English textual datetime
  //$dateTimeBegin, $dateTimeEnd

  $dateTimeBegin = strtotime($dateTimeBegin);
  if ($dateTimeBegin === -1)
    return("..początkowa data niepoprawna");

  $dateTimeEnd = strtotime($dateTimeEnd);
  if ($dateTimeEnd === -1)
    return("..końcowa data niepoprawna");

  $dif = $dateTimeEnd - $dateTimeBegin;

  switch($interval)
  {
    case "s"://seconds
      return($dif);

    case "n"://minutes
      return(floor($dif/60)); //60s=1m

    case "h"://hours
      return(floor($dif/3600)); //3600s=1h

    case "d"://days
      return(floor($dif/86400)); //86400s=1d

    case "ww"://Week
      return(floor($dif/604800)); //604800s=1week=1semana

    case "m": //similar result "m" dateDiff Microsoft
      $monthBegin = (date("Y",$dateTimeBegin)*12) + date("n",$dateTimeBegin);
      $monthEnd = (date("Y",$dateTimeEnd)*12) + date("n",$dateTimeEnd);
      $monthDiff = $monthEnd - $monthBegin;
      return($monthDiff);

    case "yyyy": //similar result "yyyy" dateDiff Microsoft
      return(date("Y",$dateTimeEnd) - date("Y",$dateTimeBegin));

    default:
      return(floor($dif/86400)); //86400s=1d
  }
}
?>
