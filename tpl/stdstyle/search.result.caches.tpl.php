<?php
	/***************************************************************************
												./tpl/stdstyle/search.result.tpl.php
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

		(X)HTML search output template

	****************************************************************************/
	global $usr, $hide_coords;
?>
<div class="content2-pagetitle"><img src="tpl/stdstyle/images/blue/cache.png" class="icon32" alt="Wyszukiwanie" title="Suchergebnis" align="middle" />&nbsp;Wyniki poszukiwań: {results_count}</div>
<div class="content-title-noshade">
	<p align="left">
		<img src="tpl/stdstyle/images/blue/search3.png" class="icon32" alt="Search results" title="Search results" align="middle"/>&nbsp;<a href="search.php?queryid={queryid}&amp;showresult=0">Szukaj</a>&nbsp;&nbsp;
		<img src="tpl/stdstyle/images/blue/save.png" class="icon32" alt="Save results" title="Save results" align="middle"/>&nbsp;{safelink}<br/>
		{pages}<br/>
	</p>
</div>
<table class="content" style="font-size: 13px; line-height: 1.6em;" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td colspan="2" style="padding-left: 0px; padding-right: 0px;">
			<table border="0" cellspacing="0" cellpadding="0" class="null">
				<tr>
				<td width="18" height="13" bgcolor="#E6E6E6">#</td>
				<td width="15" height="13" bgcolor="#E6E6E6"><b>{distanceunit}</b></td>
				<td width="80" height="13" bgcolor="#E6E6E6"><b>WGS84</b></td>			
				<td width="16" height="13" bgcolor="#E6E6E6"><b>R</b></td>
				<td width="32" height="13" bgcolor="#E6E6E6"><b>Typ</b></td>
				<td width="46" height="13" bgcolor="#E6E6E6"><b>Z/T</b></td>
				<td width="448" height="13" bgcolor="#E6E6E6"><b>Nazwa</b></td>
				<td width="126" height="13" bgcolor="#E6E6E6"><b>Wpis do LOGu</b></td>
				<td width="20" height="13" bgcolor="#E6E6E6"></td>
				</tr>
				<!--a-->{results}<!--z-->
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="2" class="header-small">{pages}</td>
	</tr>
</table>
<?php
global $usr, $hide_coords;
$login =0;
$googlemaps = "";
if ($usr || !$hide_coords){ echo "
<table class=\"content\" style=\"font-size: 12px; line-height: 1.6em;\">
	<tr>
		<td width=\"230\"><b>".tr('download')."</b></td>
		<td align=\"right\" style=\"padding-right:20px;\">
			".tr('listing_from_this_page').":
			<a class=\"links\" href=\"ocpl";?>{queryid}<?php echo ".gpx?startat=";?>{startat}<?php echo "\" title=\"GPS Exchange Format .gpx |\">GPX</a> | 
			<a class=\"links\" href=\"ocplgc";?>{queryid}<?php echo ".gpx?startat=";?>{startat}<?php echo "\" title=\"GPS Exchange Format (Groundspeak) .gpx\">GPX GC</a> | 
			<a class=\"links\" href=\"ocpl";?>{queryid}<?php echo ".loc?startat=";?>{startat}<?php echo "\" title=\"Waypoint .loc\">LOC</a> | 
			<a class=\"links\" href=\"ocpl";?>{queryid}<?php echo ".kml?startat=";?>{startat}<?php echo "\" title=\"Google Earth .kml\">KML</a> | 
			<a class=\"links\" href='http://maps.google.pl/maps?f=q&amp;hl=pl&amp;geocode=&amp;q=http:%2F%2Fwww.opencaching.pl%2Fsearch.php%3Fqueryid%3D";?>{queryid}<?php echo "%26output%3Dkml%26startat%3D";?>{startat}<?php echo "' target='_blank' title='".tr('show_in_google_maps')."'>GoogleMaps</a> | ";
			echo "<a class=\"links\" href=\"search.ov2?queryid=";?>{queryid}<?php echo "&amp;output=ov2&amp;startat=";?>{startat}<?php echo "\" title=\"TomTom POI .ov2\">OV2</a> | 
			<a class=\"links\" href=\"ocpl";?>{queryid}<?php echo ".ovl?startat=";?>{startat}<?php echo "\" title=\"TOP50-Overlay .ovl\">OVL</a> | 
			<a class=\"links\" href=\"ocpl";?>{queryid}<?php echo ".txt?startat=";?>{startat}<?php echo "\" title=\"Text .txt\">TXT</a> | 
			<a class=\"links\" href=\"ocpl";?>{queryid}<?php echo ".wpt?startat=";?>{startat}<?php echo "\" title=\"Oziexplorer .wpt\">WPT</a> | 
			<a class=\"links\" href=\"ocpl";?>{queryid}<?php echo ".uam?startat=";?>{startat}<?php echo "\" title=\"AutoMapa .uam\">UAM</a>

			</td>
	</tr>
	<tr>
		<td width=\"230\" class=\"help\">
			".tr('download_for_various_apps')."
		</td>
		<td align=\"right\" style=\"padding-right:20px;\">";?> {{listing_from_to}}<?php echo " :
			<a class=\"links\" href=\"ocpl";?>{queryid}<?php echo ".gpx?startat=";?>{startat}<?php echo "&amp;count=max&amp;zip=1\" title=\"GPS Exchange Format .gpx\">GPX</a> | 
			<a class=\"links\" href=\"ocplgc";?>{queryid}<?php echo ".gpx?startat=";?>{startat}<?php echo "&amp;count=max&amp;zip=1\" title=\"GPS Exchange Format (Groundspeak) .gpx\">GPX GC</a> | 
			<a class=\"links\" href=\"ocpl";?>{queryid}<?php echo ".loc?startat=";?>{startat}<?php echo "&amp;count=max&amp;zip=1\" title=\"Waypoint .loc\">LOC</a> | 
			<a class=\"links\" href=\"ocpl";?>{queryid}<?php echo ".kml?startat=";?>{startat}<?php echo "&amp;count=max&amp;zip=1\" title=\"Google Earth .kml\">KML</a> | 
			<a class=\"links\" href='http://maps.google.pl/maps?f=q&amp;hl=pl&amp;geocode=&amp;q=http:%2F%2Fwww.opencaching.pl%2Fsearch.php%3Fqueryid%3D";?>{queryid}<?php echo "%26output%3Dkml%26startat%3D";?>{startat}<?php echo "%26count%3Dmax%26zip%3D1&amp;ie=UTF8&amp;z=7' target='_blank' title='".tr('show_in_google_maps')."'>GoogleMaps</a> | 
			<a class=\"links\" href=\"ocpl";?>{queryid}<?php echo ".ov2?startat=";?>{startat}<?php echo "&amp;count=max&amp;zip=1\" title=\"TomTom POI .ov2\">OV2</a> | 
			<a class=\"links\" href=\"ocpl";?>{queryid}<?php echo ".ovl?startat=";?>{startat}<?php echo "&amp;count=max&amp;zip=1\" title=\"TOP50-Overlay .ovl\">OVL</a> | 
			<a class=\"links\" href=\"ocpl";?>{queryid}<?php echo ".txt?startat=";?>{startat}<?php echo "&amp;count=max&amp;zip=1\" title=\"Text .txt\">TXT</a> | 
			<a class=\"links\" href=\"ocpl";?>{queryid}<?php echo ".wpt?startat=";?>{startat}<?php echo "&amp;count=max&amp;zip=1\" title=\"Oziexplorer .wpt\"> WPT</a> | 
			<a class=\"links\" href=\"ocpl";?>{queryid}<?php echo ".uam?startat=";?>{startat}<?php echo "&amp;count=max&amp;zip=1\" title=\"AutoMapa .uam\">UAM</a>

			</td>
	</tr>
	<tr>
		<td class=\"help\" colspan=\"2\" align=\"right\">" . '{{accept_terms_of_use}}' .
"	</td></tr>
</table>"; } ?>
