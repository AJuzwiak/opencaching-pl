<?php

/***************************************************************************
	*
	*   This program is free software; you can redistribute it and/or modify
	*   it under the terms of the GNU General Public License as published by
	*   the Free Software Foundation; either version 2 of the License, or
	*   (at your option) any later version.
	*
	***************************************************************************/
?>
 
<!-- Text container -->
{body_scripts}
		<div class="content2-container line-box">

			<div class="">

				<div class="nav4">
<?

					if ($usr == false) 
					{
						echo '<span class="notlogged-cacheview">'.tr('cache_logged_required').'</span>';
					}
					// cachelisting
					$clidx = mnu_MainMenuIndexFromPageId($menu, "cachelisting");
					if( $menu[$clidx]['title'] != '' )
					{
						echo '<ul id="cachemenu">';
						$menu[$clidx]['visible'] = false;
						echo '<li class="title" ';
						echo '>'.$menu[$clidx]["title"].'</li>';
						mnu_EchoSubMenu($menu[$clidx]['submenu'], $tplname, 1, false);
						echo '</ul>';
					}
					//end cachelisting
?>
				</div>
				<div class="content2-container-2col-left" style="width:60px; clear: left;">

					<div><img src="{icon_cache}" class="icon32" id="viewcache-cacheicon" alt="{cachetype}" title="{cachetype}"/></div>
					<div>{difficulty_icon_diff}</div><div>{difficulty_icon_terr}</div>
					<div>{cache_stats}</div>

				
				</div>
					<div class="content2-container-2col-left" id="cache_name_block">
					<span class="content-title-noshade-size5">{cachename} - {oc_waypoint}</span><br />
					<p class="content-title-noshade-size1">&nbsp;{short_desc}</p>
					<p>{{owner}}&nbsp; <a class="links" href="viewprofile.php?userid={userid_urlencode}">{owner_name}</a></p>
					{event_attendance_list}
					</div>


			</div>
		</div>


<!-- End Text Container -->
<!-- Text container -->
			<div class="content2-container">
				<div class="content2-container-2col-left" id="viewcache-baseinfo">
					<p class="content-title-noshade-size3">
						<img src="tpl/stdstyle/images/blue/kompas.png" class="icon32" alt="" title="" />
						<b>{coords}</b> <span class="content-title-noshade-size0">(WGS84)</span><br />
					</p>
					<p style="line-height: 1.6em;">
						<img src="tpl/stdstyle/images/free_icons/map.png" class="icon16" alt="" title="" align="middle" />&nbsp;{coords_other} <img src="tpl/stdstyle/images/misc/linkicon.png" alt="link"><br />
						<img src="tpl/stdstyle/images/free_icons/world.png" class="icon16" alt="" title="" align="middle" />&nbsp;{{location}}:<b><span style="color: rgb(88,144,168)"> {kraj} {dziubek1} {woj} {dziubek2} {miasto}</span></b><br /> 
						{distance_cache}
						<img src="tpl/stdstyle/images/free_icons/box.png" class="icon16" alt="" title="" align="middle" />&nbsp;{{cache_type}}: <b>{cachetype}</b><br />
						<img src="tpl/stdstyle/images/free_icons/package_green.png" class="icon16" alt="" title="" align="middle" />&nbsp;{{size}}: <b>{cachesize}</b><br />
						<img src="tpl/stdstyle/images/free_icons/page.png" class="icon16" alt="" title="" align="middle" />&nbsp;{{status_label}}: {status}<br />
						{hidetime_start}<img src="tpl/stdstyle/images/free_icons/time.png" class="icon16" alt="" title="" align="middle" />&nbsp;{{time}}: {search_time}&nbsp;&nbsp;<img src="tpl/stdstyle/images/free_icons/arrow_switch.png" class="icon16" alt="" title="" align="middle" />&nbsp;{{length}}: {way_length}<br />{hidetime_end}		
						<img src="tpl/stdstyle/images/free_icons/date.png" class="icon16" alt="" title="" align="middle" />&nbsp;{{date_hidden_label}}: {hidden_date}<br />
						<img src="tpl/stdstyle/images/free_icons/date.png" class="icon16" alt="" title="" align="middle" />&nbsp;{{date_created_label}}: {date_created}<br />
						<img src="tpl/stdstyle/images/free_icons/date.png" class="icon16" alt="" title="" align="middle" />&nbsp;{{last_modified_label}}: {last_modified}<br />
						<img src="tpl/stdstyle/images/free_icons/arrow_in.png" class="icon16" alt="" title="" align="middle" />&nbsp;Waypoint: <b>{oc_waypoint}</b><br />
						{hidelistingsites_start}<img src="tpl/stdstyle/images/free_icons/link.png" class="icon16" alt="" title="" align="middle" />&nbsp;{{listed_also_on}}: {listed_on}<br />{hidelistingsites_end}
					</p>
					<?php
global $usr, $lang, $hide_coords;			

// uśpiony mechanizm ukrywania niektórych danych dla niezalogowanych
if ($usr == false && $hide_coords)
{
	echo "";
}
else
{

						echo "<a class=\"send-to-gps\" href=\"#\" onclick=\"javascript:window.open('garmin.php?lat="; ?>{latitude}<?php echo "&amp;long="; ?>{longitude}<?php echo "&amp;wp="; ?>{oc_waypoint}<?php echo "&amp;name="; ?>{cachename}<?php echo "&amp;popup=y','Send_To_GPS','width=450,height=160,resizable=no,scrollbars=0')\"><input type=\"button\" name=\"SendToGPS\" value=\""; ?>{{send_to_gps}}<?php echo "\" id=\"SendToGPS\"/></a><p>&nbsp;</p>";
} ?>

				</div>
				<div class="content2-container-2col-right" id="viewcache-maptypes">
					<?php
					if ($usr == false && $hide_coords)
							{
					?>
					{map_msg}
					<?php 
							}
							else
							{
					?>
						<div class="content2-container-2col-left" id="viewcache-numstats">
						<p style="line-height: 1.4em;"><br />
							{found_icon} {founds} {found_text}<br />
							{hidemobile_start}{moved_icon} {moved} x {{moved_text}}<br/>{hidemobile_end}	
							{notfound_icon} {notfounds} {notfound_text}<br />
							{note_icon} {notes} {{comments}}<br />
							{watch_icon} {watcher} {{watchers}}<br />
							{visit_icon} {visits} {{visitors}}<br />
							{vote_icon} {votes_count} x {{scored}}<br />
							{score_icon} {{score_label}}: <b><font color="{scorecolor}">{score}</font></b><br />
							{list_of_rating_begin}{rating_stat}{list_of_rating_end}
							{gk_icon} <a class="links" href="http://geokrety.org/szukaj.php?lang=pl_PL.UTF-8&wpt={oc_waypoint}" target="_blank">{{history_gk}}  <img src="tpl/stdstyle/images/misc/linkicon.png" alt="link"></a><br />

							</p>
						</div>
						<div id="viewcache-map" class="content2-container-2col-right"><div class="img-shadow">
							<img src="http://maps.google.com/staticmap?center={latitude},{longitude}&amp;zoom=8&amp;size=170x170&amp;maptype=terrain&amp;key={googlemap_key}&amp;sensor=false&amp;markers={latitude},{longitude},blue{typeLetter}&amp;format=png" longdesc="ifr::cachemap-mini.php?inputZoom=14&amp;lat={latitude}&amp;lon={longitude}&amp;cacheid={cacheid}::480::385" onclick="enlarge(this);" alt="{{map}}" />
						</div></div>
					<?php
							}
					if ($usr == false && $hide_coords)
							{
					echo "";
							}
							else
							{

					echo "<b>{{available_maps}}:</b>
											<a target=\"_blank\" href='cachemap3.php?lat=";?>{latitude}<?php echo "&amp;lon=";?>{longitude}<?php echo "&amp;cacheid=";?>{cacheid}<?php echo "&amp;inputZoom=14'>Opencaching.pl</a>,
											<a target=\"_blank\" href='http://osmapa.pl?zoom=16&amp;lat=";?>{latitude}<?php echo "&amp;lon=";?>{longitude}<?php echo "&amp;o=TFFT&amp;map=1'>OSMapa</a>,
											<a target=\"_blank\" href='http://mapa.ump.waw.pl/ump-www/?zoom=14&amp;lat=";?>{latitude}<?php echo "&amp;lon=";?>{longitude}<?php echo "&amp;layers=B00000T&amp;mlat=";?>{latitude}<?php echo "&amp;mlon=";?>{longitude}<?php echo "'>UMP</a>, <a target=\"_blank\" href='http://www.zumi.pl/namapie.html?&amp;lat=";?>{latitude}<?php echo "&amp;long=";?>{longitude}<?php echo "&amp;type=1&amp;scale=4'>Zumi</a>,<br/>											
											<a href=\"http://maps.google.com/maps?hl=UTF-8&q=";?>{latitude}<?php echo "+";?>{longitude}<?php echo '+(' . urlencode($vars['cachename'])  . ")\" target=\"_blank\">Google&nbsp;Maps</a>, 
											<a href=\"http://mapa.szukacz.pl/?n=";?>{latitude}<?php echo "&amp;e=";?>{longitude}<?php echo "&amp;t=Skrzynka%20Geocache\" target=\"_blank\">AutoMapa</a>";
								
					} 
					?>				
				</div>
			</div>
<!-- End Text Container -->
	
<!-- Text container -->
					{cache_attributes_start}
			<div class="content2-container bg-blue02">
				<p class="content-title-noshade-size1">
					<img src="tpl/stdstyle/images/blue/attributes.png" class="icon32" alt="" />
					{{cache_attributes_label}}
				</p>
			</div>
			<div class="content2-container">
				<p>
					{cache_attributes}{password_req}
				</p>
			</div>
<div class="notice" id="viewcache-attributesend">{{attributes_desc_hint}}  <img src="tpl/stdstyle/images/misc/linkicon.png" alt="link"></div>
					{cache_attributes_end}
<!-- End Text Container -->
<!-- Text container -->
			{start_rr_comment}
			<div class="content2-container bg-blue02">
				<p class="content-title-noshade-size1">
					
					<img src="tpl/stdstyle/images/blue/crypt.png" class="icon32" alt="" />
					{{rr_comment_label}}
				</p>
				</div>
				<div class="content2-container">
				<p><br/>
				{rr_comment}
				</p>
			</div>
			{end_rr_comment}
<!-- End Text Container -->
<!-- Text container -->
			<div class="content2-container bg-blue02">
				<p class="content-title-noshade-size1">
					<img src="tpl/stdstyle/images/blue/describe.png" class="icon32" alt="" />
					{{descriptions}}&nbsp;&nbsp;
					{desc_langs}&nbsp;{add_rr_comment}&nbsp;{remove_rr_comment}
				</p></div>
				<div class="content2-container">
				<div id='branding'>{branding}</div>
				<div id="description">
					<div id="viewcache-description">
						{desc}
					</div>
				</div>
			</div>
<!-- End Text Container -->
<!-- Text container -->

<!-- sekcja opensprawdzacza -->
{opensprawdzacz_start}

<div class="content2-container bg-blue02">
<p class="content-title-noshade-size1">
<img src="tpl/stdstyle/images/blue/opensprawdzacz32x32.png" class="icon32" alt="" />
OpenSprawdzacz 
</p></div>
<p>
{{opensprawdzacz_main}}<br/><br/> 
<a href="opensprawdzacz.php?op_keszynki={oc_waypoint}">{{os_sprawdz}}</a><br/><br/>
</p>
<p>{{statistics}}: 
{{os_pr}}: {proby} razy, {{os_sukc}}: {sukcesy} razy. 
{opensprawdzacz_end}
<!-- koniec sekcji opensprawdzacza -->

{waypoints_start}
			<div class="content2-container bg-blue02">
				<p class="content-title-noshade-size1">
					<img src="tpl/stdstyle/images/blue/compas.png" class="icon32" alt="" />
					{{additional_waypoints}}
				</p></div>
				<p>
					{waypoints_content}
				</p><br />
			<div class="notice" id="viewcache-attributesend"><a class="links" href="http://wiki.opencaching.pl/index.php/Dodatkowe_waypointy_w_skrzynce" target="_blank">Zobacz opis i rodzaje dodatkowych waypointów <img src="tpl/stdstyle/images/misc/linkicon.png" alt="link"></a></div>
{waypoints_end}
<!-- End Text Container -->
<!-- Text container -->
{hidehint_start}
			<div class="content2-container bg-blue02">
				<p class="content-title-noshade-size1">
					<img src="tpl/stdstyle/images/blue/crypt.png" class="icon32" alt="" />
					<b>{{additional_hints}}</b>&nbsp;&nbsp;
					<span id="decrypt-info">
					{decrypt_link_start}
					<img src="tpl/stdstyle/images/blue/decrypt.png" class="icon32" alt="" />
					{decrypt_link}
					{decrypt_link_end}
					</span>
					<br/>

				</p>
			</div>
					<div class="content2-container">
					<p id="decrypt-hints">   
							{hints}
					</p>  

					<div style="width:200px;align:right;float:right">
						{decrypt_table_start}
						<font face="Courier" size="2" style="font-family : 'Courier New', FreeMono, Monospace;">A|B|C|D|E|F|G|H|I|J|K|L|M</font>
						<font face="Courier" size="2" style="font-family : 'Courier New', FreeMono, Monospace;">N|O|P|Q|R|S|T|U|V|W|X|Y|Z</font>
						{decrypt_table_end}
					</div>
				</div>

{hidehint_end}
<!-- End Text Container -->


{EditCacheNoteS}
	<div class="content2-container bg-blue02">
		<p class="content-title-noshade-size2">
			<img src="tpl/stdstyle/images/blue/logs.png" style="align: left; margin-right: 10px;" alt="{{Personal cache note}}" /> 
			{{personal_cache_note}}
		</p>
	</div>

	<div class="content2-container">
<form action="viewcache.php" method="post" name="cache_note">
<input type="hidden" name="cacheid" value="{cacheid}" />

  <table id="cache_note1" class="table">
    <tr valign="top">
    <td></td>
      <td>
        <textarea name="note_content" rows="4" cols="85" style="font-size:13px;">{note_content}</textarea>
      </td>
    </tr>
    <tr>
      <td></td>
      <td colspan="2">
        <button type="submit" name="save" value="save" style="width:100px">{{save}}</button>&nbsp;&nbsp;
        <img src="tpl/stdstyle/images/misc/16x16-info.png" class="icon16" alt="Info" />
        <small>
          {{cache_note_visible}}</td>
        </small>
      </td>
    </tr>
  </table>
		</form>
	</div>
{EditCacheNoteE}
{CacheNoteS}
	<div class="content2-container bg-blue02">
		<p class="content-title-noshade-size2">
			<img src="tpl/stdstyle/images/blue/logs.png" style="align: left; margin-right: 10px;" alt="{{personal_cache_note}}" /> 
			{{personal_cache_note}}
		</p>
	</div>

	<div class="content2-container">
<form action="viewcache.php?cacheid={cacheid}#cache_note1" method="post" name="cache_note">
<input type="hidden" name="cacheid" value="{cacheid}" />

  <table id="cache_note2" class="table">
    <tr valign="top">
    <td></td>
      <td>
      <div class="searchdiv" style="width: 710px;">
        <span style="font-size:13px;">{notes_content}</span>
	</div>
      </td>
    </tr>
    <tr>
      <td></td>
      <td colspan="2">&nbsp;
        <button type="submit" name="edit" value="edit" style="width:100px">{{Edit}}</button>&nbsp;&nbsp;
	<button type="submit" name="remove" value="remove" style="width:100px">{{delete}}</button>&nbsp;&nbsp;
        <img src="tpl/stdstyle/images/misc/16x16-info.png" class="icon16" alt="Info" />
        <small>
          {{cache_note_visible}}</td>
        </small>
      </td>
    </tr>
  </table>
		</form>
	</div>
{CacheNoteE} 

<!-- Text container -->
{hidenpa_start}
			<div class="content2-container bg-blue02">
				<p class="content-title-noshade-size1">
					
					<img src="tpl/stdstyle/images/blue/npav1.png" class="icon32" alt="" />
					Obszary ochrony przyrody
				</p>
				</div>
				<div class="content2-container"><center>
{npa_content}
</center>
			</div>
{hidenpa_end}
<!-- End Text Container -->
<!-- Text container -->
{geokrety_begin}
			<div class="content2-container bg-blue02">
				<p class="content-title-noshade-size1">
					<img src="tpl/stdstyle/images/blue/travelbug.png" class="icon32" alt="" />
					Geokrety
				</p></div>
				<div class="content2-container">
				<p>
					{geokrety_content}
				</p>
			</div>
{geokrety_end}
<!-- End Text Container -->
<!-- Text container -->
{hidemp3_start}
			<div class="content2-container bg-blue02">
				<p class="content-title-noshade-size1">
					<img src="tpl/stdstyle/images/blue/podcache-mp3.png" class="icon32" alt="" />
					{{mp3_files_info}}
				</p></div>
				<div class="content2-container">
				<div id="viewcache-mp3s">
					{mp3_files}
				</div>
			</div>
{hidemp3_end}
<!-- End Text Container -->

<!-- Text container -->
{hidepictures_start}
			<div class="content2-container bg-blue02">
				<p class="content-title-noshade-size1">
					<img src="tpl/stdstyle/images/blue/picture.png" class="icon32" alt="" />
					{{images}}
				</p></div>
				<div class="content2-container">
				<div id="viewcache-pictures">
					{pictures}
				</div>
			</div>
{hidepictures_end}
<!-- End Text Container -->
<!-- Text container -->
			<div class="content2-container bg-blue02">
				<p class="content-title-noshade-size1">
					<!-- End Text Container -->
					<img src="tpl/stdstyle/images/blue/tools.png" class="icon32" alt="" />&nbsp;{{utilities}}
				</p></div>
				<div class="content2-container">
			<div id="viewcache-utility">
			<div>{search_icon} {{search_geocaches_nearby}}<?php echo ":
			<a href=\"search.php?searchto=searchbydistance&amp;showresult=1&amp;expert=0&amp;output=HTML&amp;sort=bydistance&amp;f_userowner=0&amp;f_userfound=0&amp;f_inactive=1&amp;latNS=";?>{latNS}<?php echo "&amp;lat_h="; ?>{lat_h}<?php echo "&amp;lat_min="; ?>{lat_min}<?php echo "&amp;lonEW="; ?>{lonEW}<?php echo "&amp;lon_h="; ?>{lon_h}<?php echo "&amp;lon_min="; ?>{lon_min}<?php echo "&amp;distance=100&amp;unit=km\">";?>{{all_geocaches}}<?php echo "</a>&nbsp;
			<a href=\"search.php?searchto=searchbydistance&amp;showresult=1&amp;expert=0&amp;output=HTML&amp;sort=bydistance&amp;f_userowner=1&amp;f_userfound=1&amp;f_inactive=1&amp;latNS="; ?>{latNS}<?php echo "&amp;lat_h="; ?>{lat_h}<?php echo "&amp;lat_min="; ?>{lat_min}<?php echo "&amp;lonEW="; ?>{lonEW}<?php echo "&amp;lon_h=";?>{lon_h}<?php echo "&amp;lon_min=";?>{lon_min}<?php echo "&amp;distance=100&amp;unit=km\">";?>{{searchable}}<?php echo "</a>&nbsp;&nbsp;&nbsp;<br/>"; ?>
{search_icon} {{find_geocaches_on}}<?php echo ":&nbsp;<b>
			<a target=\"_blank\" href=\"http://www.geocaching.com/seek/nearest.aspx?origin_lat=";?>{latitude}<?php echo "&amp;origin_long=";?>{longitude}<?php echo "&amp;dist=100&amp;submit8=Submit\">Geocaching.com</a>&nbsp;&nbsp;&nbsp;
                        <a target=\"_blank\" href=\"http://www.terraCaching.com/gmap.cgi#center_lat=";?>{latitude}<?php echo "&amp;center_lon="; ?>{longitude}<?php echo "&amp;&center_zoom=7&cselect=all&ctselect=all\">TerraCaching.com</a>&nbsp;&nbsp;
                        <a target=\"_blank\" href=\"http://www.navicache.com/cgi-bin/db/distancedp.pl?latNS=";?>{latNS}<?php echo "&amp;latHours=";?>{latitude}<?php echo "&amp;longWE="; ?>{lonEW}<?php echo "&amp;longHours=";?>{longitudeNC}<?php echo "&amp;Distance=100&amp;Units=M\">Navicache.com</a>&nbsp;&nbsp;&nbsp;
        		<a target=\"_blank\" href=\"http://geocaching.gpsgames.org/cgi-bin/ge.pl?basic=yes&amp;download=Google+Maps&amp;zoom=8&amp;lat_1=";?>{latitude}<?php echo "&amp;lon_1=";?>{longitude}<?php echo "\">GPSgames.org</a>&nbsp;
        		<a href=\"http://www.opencaching.cz/search.php?searchto=searchbydistance&amp;showresult=1&amp;expert=0&amp;output=HTML&amp;sort=bydistance&amp;f_userowner=0&amp;f_userfound=0&amp;f_inactive=1&amp;country=&amp;cachetype=&amp;cache_attribs=&amp;cache_attribs_not=7&amp;latNS=";?>{latNS}<?php echo "&amp;lat_h=";?>{lat_h}<?php echo "&amp;lat_min=";?>{lat_min}<?php echo "&amp;lonEW=";?>{lonEW}<?php echo "&amp;lon_h=";?>{lon_h}<?php echo "&amp;lon_min=";?>{lon_min}<?php echo "&amp;distance=100&amp;unit=km\">OC CZ</a>&nbsp;&nbsp;&nbsp;
        		<a href=\"http://www.opencaching.de/search.php?searchto=searchbydistance&amp;showresult=1&amp;expert=0&amp;output=HTML&amp;sort=bydistance&amp;f_userowner=0&amp;f_userfound=0&amp;f_inactive=1&amp;country=&amp;cachetype=&amp;cache_attribs=&amp;cache_attribs_not=7&amp;latNS=";?>{latNS}<?php echo "&amp;lat_h=";?>{lat_h}<?php echo "&amp;lat_min=";?>{lat_min}<?php echo "&amp;lonEW=";?>{lonEW}<?php echo "&amp;lon_h=";?>{lon_h}<?php echo "&amp;lon_min=";?>{lon_min}<?php echo "&amp;distance=100&amp;unit=km\">OC DE</a></b>&nbsp;&nbsp;
						
			"; ?></div><div> {save_icon} <b>{{download_as_file}}</b><br/><?php echo "

   <table class=\"content\" style=\"font-size: 12px; line-height: 1.6em;\">             
       <tr>  
		<td  width=\"350\" align=\"left\" style=\"padding-left:5px;\">
		<div class=\"searchdiv\">
                    <span class=\"content-title-noshade txt-blue08\">GPX format</span>:<br/>
			<a class=\"links\" href=\"search.php?searchto=searchbycacheid&amp;showresult=1&amp;f_inactive=0&amp;f_ignored=0&amp;f_userfound=0&amp;f_userowner=0&amp;f_watched=0&amp;startat=0&amp;cacheid=";?>{cacheid_urlencode}<?php echo "&amp;output=gpx\" title=\"GPS Exchange Format .gpx\">OpenCaching | </a>
			<a class=\"links\" href=\"search.php?searchto=searchbycacheid&amp;showresult=1&amp;f_inactive=0&amp;f_ignored=0&amp;f_userfound=0&amp;f_userowner=0&amp;f_watched=0&amp;startat=0&amp;cacheid=";?>{cacheid_urlencode}<?php echo "&amp;output=gpxgc\" title=\"GPS Exchange Format (Groundspeak) .gpx\">Geocaching.com | </a>
			<a class=\"links\"  href=\"search.php?searchto=searchbycacheid&amp;showresult=1&amp;f_inactive=0&amp;f_ignored=0&amp;f_userfound=0&amp;f_userowner=0&amp;f_watched=0&amp;startat=0&amp;cacheid=";?>{cacheid_urlencode}<?php echo "&amp;output=zip\" title=\"Garmin ZIP file (GPX + zdjęcia)  .zip\">GARMIN</a>
		    </div>
            	    </td>
                <td width=\"350\" align=\"left\" style=\"padding-left:5px;\">
		<div class=\"searchdiv\">
		<span class=\"content-title-noshade txt-blue08\">Inne formaty</span>:<br/>
			<a  class=\"links\" href=\"search.php?searchto=searchbycacheid&amp;showresult=1&amp;f_inactive=0&amp;f_ignored=0&amp;f_userfound=0&amp;f_userowner=0&amp;f_watched=0&amp;startat=0&amp;cacheid=";?>{cacheid_urlencode}<?php echo "&amp;output=loc\" title=\"Waypoint .loc\">LOC | </a>
			<a  class=\"links\" href=\"search.php?searchto=searchbycacheid&amp;showresult=1&amp;f_inactive=0&amp;f_ignored=0&amp;f_userfound=0&amp;f_userowner=0&amp;f_watched=0&amp;startat=0&amp;cacheid=";?>{cacheid_urlencode}<?php echo "&amp;output=kml\" title=\"Google Earth .kml\">KML | </a>
			<a  class=\"links\" href=\"search.php?searchto=searchbycacheid&amp;showresult=1&amp;f_inactive=0&amp;f_ignored=0&amp;f_userfound=0&amp;f_userowner=0&amp;f_watched=0&amp;startat=0&amp;cacheid=";?>{cacheid_urlencode}<?php echo "&amp;output=ov2\" title=\"TomTom POI .ov2\">OV2 | </a>
			<a  class=\"links\" href=\"search.php?searchto=searchbycacheid&amp;showresult=1&amp;f_inactive=0&amp;f_ignored=0&amp;f_userfound=0&amp;f_userowner=0&amp;f_watched=0&amp;startat=0&amp;cacheid=";?>{cacheid_urlencode}<?php echo "&amp;output=ovl\" title=\"TOP50-Overlay .ovl\">OVL | </a>
			<a  class=\"links\" href=\"search.php?searchto=searchbycacheid&amp;showresult=1&amp;f_inactive=0&amp;f_ignored=0&amp;f_userfound=0&amp;f_userowner=0&amp;f_watched=0&amp;startat=0&amp;cacheid=";?>{cacheid_urlencode}<?php echo "&amp;output=txt\" title=\"Tekst .txt\">TXT | </a>
			<a  class=\"links\" href=\"search.php?searchto=searchbycacheid&amp;showresult=1&amp;f_inactive=0&amp;f_ignored=0&amp;f_userfound=0&amp;f_userowner=0&amp;f_watched=0&amp;startat=0&amp;cacheid=";?>{cacheid_urlencode}<?php echo "&amp;output=wpt\" title=\"Oziexplorer .wpt\">WPT | </a>
			<a  class=\"links\" href=\"search.php?searchto=searchbycacheid&amp;showresult=1&amp;f_inactive=0&amp;f_ignored=0&amp;f_userfound=0&amp;f_userowner=0&amp;f_watched=0&amp;startat=0&amp;cacheid=";?>{cacheid_urlencode}<?php echo "&amp;output=uam\" title=\"AutoMapa .uam\">UAM | </a>
			<a  class=\"links\" href=\"search.php?searchto=searchbycacheid&amp;showresult=1&amp;f_inactive=0&amp;f_ignored=0&amp;f_userfound=0&amp;f_userowner=0&amp;f_watched=0&amp;startat=0&amp;cacheid=";?>{cacheid_urlencode}<?php echo "&amp;output=xml\" title=\"XML\">XML</a>
		    </div>
                    </td>
    		</tr>
	</table>
			<div class=\"notice buffer\" id=\"viewcache-termsofuse\">"; ?> {{accept_terms_of_use}}<?php echo "</div></div>
";
	 ?>
				</div>
			</div>
<!-- Text container -->
			<div class="content2-container bg-blue02">
				<p class="content-title-noshade-size1">
					<img src="tpl/stdstyle/images/blue/logs.png" class="icon32" alt=""/>
					{{log_entries}}
					&nbsp;&nbsp;
					{found_icon} {founds}x
					{notfound_icon} {notfounds}x
					{note_icon} {notes}x
					{gallery}
					&nbsp;&nbsp;
					{viewlogs}
					&nbsp;
					<img src="images/actions/new-entry-18.png" alt=""/>
					<a href="log.php?cacheid={cacheid_urlencode}">{{new_log_entry}}</a>
				</p>
			</div>
			<div class="content2-container" id="viewcache-logs">
					{logs}
			</div>
<!-- End Text Container -->
