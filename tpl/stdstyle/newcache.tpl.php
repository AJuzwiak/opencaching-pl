<?php

/***************************************************************************
	*
	*   This program is free software; you can redistribute it and/or modify
	*   it under the terms of the GNU General Public License as published by
	*   the Free Software Foundation; either version 2 of the License, or
	*   (at your option) any later version.
	*
	***************************************************************************/

/****************************************************************************

   Unicode Reminder ?? ąść


 ****************************************************************************/
?>
<input type="hidden" value="arrrgh" id="qwertyuiop">

<script type="text/javascript" src="tpl/stdstyle/js/jquery_1.9.2_ocTheme/ajaxfileupload.js"></script>
<script type="text/javascript" src="tpl/stdstyle/js/jquery_1.9.2_ocTheme/jquery-1.8.3.js"></script>
<script type="text/javascript" src="tpl/stdstyle/js/jquery_1.9.2_ocTheme/newcache_wptGpxHandler.js"></script>
<script type="text/javascript" src="tpl/stdstyle/js/jquery_1.9.2_ocTheme/jquery.js"></script>
<script src="tpl/stdstyle/js/jquery-2.0.3.min.js"></script>


<script type="text/javascript">
<!--

$(function() {
	chkcountry2();
}); 

var maAttributes = new Array({jsattributes_array});

function chkregion() {
	if ($('#region').val() == "0") {
		alert("Proszę wybrać region");
		return false;
	}
	return true;
}


function chkcountry2(){
	$('#region1').hide();
	$('#regionAjaxLoader').show();
	request = $.ajax({
    	url: "ajaxGetRegionsByCountryCode.php",
    	type: "post",
    	data:{countryCode: $('#country').val(), selectedRegion: '{sel_region}' },
	});

    // callback handler that will be called on success
    request.done(function (response, textStatus, jqXHR){
    	$('#region1').html(response);
    	console.log(response);
    });
    
    request.always(function () {
		$('#regionAjaxLoader').hide();
		$('#region1').fadeIn(1000);
    });
}

function _chkVirtual () 
{
chkiconcache();
	// disable password for traditional cache
	if($('#cacheType').val() == "2")
	{
		$('#log_pw').attr('disabled', true);
	}
	else
	{
		$('#log_pw').removeAttr('disabled');

	}
	
  if ($('#cacheType').val() == "4" || $('#cacheType').val() == "5" || $('#cacheType').val() == "6" ) 
	{
		   
		// if( document.newcacheform.size.options[ $('#size option').length - 1].value != "7" && document.newcacheform.size.options[document.newcacheform.size.options.length - 2].value != "7")
		if (!($("#size option[value='7']").length > 0))
		{
			var o = new Option("{{size_07}}", "7");
			$(o).html("{{size_07}}");
			$("#size").append(o);
		}
		$('#size').val(7);
		$('#size').attr('disabled', true);
  }
  else
  {
	$('#size').attr('disabled', false);
	$("#size option[value='7']").remove();
  }
  return false;
}

function rebuildCacheAttr()
{
	var i = 0;
	var sAttr = '';
	
	for (i = 0; i < maAttributes.length; i++)
	{
		if (maAttributes[i][1] == 1)
		{
			if (sAttr != '') sAttr += ';';
			sAttr = sAttr + maAttributes[i][0];

			document.getElementById('attr' + maAttributes[i][0]).src = maAttributes[i][3];
		}
		else
			document.getElementById('attr' + maAttributes[i][0]).src = maAttributes[i][2];

		document.getElementById('cache_attribs').value = sAttr;
		
	}
}

function chkcountry()
{

if (document.newcacheform.country.value !='PL')
{
document.forms['newcacheform'].country.value = document.newcacheform.country.value;

// var as = $('#qwertyuiop').val();
// alert(as);
$('#region0').hide();
$('#region1').hide();
$('#region2').hide();
$('#region3').hide();
$('#region1').val(0);

document.forms['newcacheform'].region.value = '0';
document.newcacheform.region.disable = true;
 } else {
$('#region0').show();
$('#region1').show();
$('#region2').show();
$('#region3').show(); 	
document.forms['newcacheform'].country.value = 'PL';
//document.newcacheform.region.options[document.newcacheform.region.options.length] = new Option('--- Select name of region ---', '0')
document.newcacheform.region.disable = false;
document.forms['newcacheform'].region.value = document.newcacheform.region.value;}
}

function chkiconcache()
    {
	var mode = $('#cacheType').val(); // document.newcacheform.type.value;
	var iconarray = new Array();
		iconarray['-1'] = 'arrow_left.png';
		iconarray['1'] = 'unknown.png';
		iconarray['2'] = 'traditional.png';
		iconarray['3'] = 'multi.png';
		iconarray['4'] = 'virtual.png';
		iconarray['5'] = 'webcam.png';
		iconarray['6'] = 'event.png';
		iconarray['7'] = 'quiz.png';
		iconarray['8'] = 'moving.png';
		iconarray['10'] = 'owncache.png';
	var image_cache = "/tpl/stdstyle/images/cache/" + iconarray[mode];
	$('#actionicons').attr('src', image_cache);
}
function toggleAttr(id)
{
	var i = 0;
	
	for (i = 0; i < maAttributes.length; i++)
	{
		if (maAttributes[i][0] == id)
		{
			if (maAttributes[i][1] == 0)
				maAttributes[i][1] = 1;
			else
				maAttributes[i][1] = 0;

			rebuildCacheAttr();
			break;
		}
	}
}
//-->
</script>
<script type="text/javascript"><!--
function nearbycache()
{
		var latNS = document.forms['newcacheform'].latNS.value;
		var lat_h = document.forms['newcacheform'].lat_h.value;
		var lat_min = document.forms['newcacheform'].lat_min.value;
		var lonEW = document.forms['newcacheform'].lonEW.value;
		var lon_h = document.forms['newcacheform'].lon_h.value;
		var lon_min = document.forms['newcacheform'].lon_min.value;
				if (document.newcacheform.lat_h.value == "0" && document.newcacheform.lon_h.value == "0" ) {
		alert("{{input_coord}}"); 
			} else {
	window.open('http://www.opencaching.pl/search.php?searchto=searchbydistance&showresult=1&expert=0&output=HTML&sort=bydistance&f_userowner=0&f_userfound=0&f_inactive=0&distance=0.3&unit=km&latNS=' + latNS + '&lat_h=' + lat_h + '&lat_min=' + lat_min + '&lonEW=' + lonEW + '&lon_h=' + lon_h + '&lon_min=' + lon_min);
	}
	return false;
}
function extractregion()
{
		var latNS = document.forms['newcacheform'].latNS.value;
		var lat_h = document.forms['newcacheform'].lat_h.value;
		var lat_min = document.forms['newcacheform'].lat_min.value;
		var lat ;
		lat=(lat_h*1)+(lat_min/60);
		if (latNS=="S") lat=-lat;
		var lonEW = document.forms['newcacheform'].lonEW.value;
		var lon_h = document.forms['newcacheform'].lon_h.value;
		var lon_min = document.forms['newcacheform'].lon_min.value;
		var lon ;
		lon=(lon_h*1)+(lon_min/60);
	        if (lonEW=="W") lon=-lon;
		if (document.newcacheform.lat_h.value == "0" && document.newcacheform.lon_h.value == "0" ) {
		alert("{{input_coord}}"); 
			} else {
	window.open('http://www.opencaching.pl/region.php?lat=' + lat + '&lon=' + lon+ '&popup=y','Region','width=300,height=250');
	}
	return false;
}

//--></script>
<script type="text/javascript"><!--
function nearbycachemapOC()
{
		var lat_h = document.forms['newcacheform'].lat_h.value;
		var latNS = document.forms['newcacheform'].latNS.value;
		var lat_min = document.forms['newcacheform'].lat_min.value;
		var lat ;
		lat=(lat_h*1)+(lat_min/60);
		if (latNS=="S") lat=-lat;
		var lon_h = document.forms['newcacheform'].lon_h.value;
		var lonEW = document.forms['newcacheform'].lonEW.value;
		var lon_min = document.forms['newcacheform'].lon_min.value;
		var lon ;
		lon=(lon_h*1)+(lon_min/60);
	        if (lonEW=="W") lon=-lon;
				if (document.newcacheform.lat_h.value == "0" && document.newcacheform.lon_h.value == "0" ) {
		alert("{{input_coord}}"); 
			} else {
		window.open('http://www.opencaching.pl/cachemap3.php?circle=1&inputZoom=17&lat=' + lat + '&lon=' + lon);}
	return false;
}//--></script>


<style>
	#oldIE {
	color: red;
	border: solid 1px;
	border-color: red;
	padding: 10px;
	width:90%;
	}
</style>
<!--[if IE 6 ]> <div id="oldIE">{{pt129}}</div><br/><br/> <![endif]--> 
<!--[if IE 7 ]> <div id="oldIE">{{pt129}}</div><br/><br/> <![endif]--> 
<!--[if IE 8 ]> <div id="oldIE">{{pt129}}</div><br/><br/> <![endif]--> 


<div class="content2-pagetitle"><img src="tpl/stdstyle/images/blue/cache.png" class="icon32" alt="" title="{{new_cache}}" align="middle"/>&nbsp;{{new_cache}}</div>
	{general_message}
	<div class="buffer"></div>
	<div class="content2-container bg-blue02" >
			<p class="content-title-noshade-size1"><img src="tpl/stdstyle/images/blue/basic2.png" class="icon32" alt=""/>&nbsp;{{basic_information}}</p>
	</div>

	<div class="buffer"></div>
	<div class="notice">
		{{first_cache}}.
	</div>
	{approvement_note}
	<div class="buffer"></div>
	<table class="table" border="0">
	<colgroup>
		<col width="180"/>
		<col/>
	</colgroup>
	<!-- coord from gpx. Swithed off because of jquery version conflicts (TODO)
	<tr>
		<td valign="top"><p class="content-title-noshade" title="{{newcache_import_wpt_help}}">{{newcache_import_wpt}}</p></td>
		<td valign="top">
			<img id="loading" src="tpl/stdstyle/js/jquery_1.9.2_ocTheme/ajax-loader.gif" style="display:none;">
			<form name="form" action="" method="POST" enctype="multipart/form-data">
			 <input id="fileToUpload" type="file" size="20" name="fileToUpload" value="sdsd">
			 <button class="button" id="buttonUpload" onclick="return ajaxFileUpload();">{{newcache_upload}}</button>
			</form>  
		<br/><br/>
		
		
					<form action="powerTrail/ajaxImage.php" method="post" enctype="multipart/form-data" target="upload_target" onsubmit="startUpload();" >
         				<p id="f1_upload_form" align="center"><br/>
             			File: <input name="myfile" type="file" size="30" />
             			
						<a href="javascript:void(0)" onclick="$(this).closest('form').submit()" class="editPtDataButton">{{pt044}}</a> 
         				</p>
         				<iframe id="upload_target" name="upload_target" src="#" style="width:0;height:0;border:0px solid #fff;"></iframe>
					</form>
		
		
		</td>
	</tr>
	<tr><td>&nbsp;</td>
		<td><div class="notice" style="width:500px;height:60px;">{{newcache_import_wpt_help}}</div>
		</td>
	</tr>
	-->
	
	
	
<form action="newcache.php" method="post" enctype="application/x-www-form-urlencoded" name="newcacheform" dir="ltr" onsubmit="javascript: return chkregion()">
<input type="hidden" name="show_all_countries" value="{show_all_countries}"/>
<input type="hidden" name="show_all_langs" value="{show_all_langs}"/>
<input type="hidden" name="version2" value="1"/>
<input type="hidden" name="beginner" value="0"/>
<input type="hidden" name="newcache_info" value="0"/>
<input type="hidden" id="cache_attribs" name="cache_attribs" value="{cache_attribs}" />
<input id="descMode" type="hidden" name="descMode" value="1" />

	<tr>
		<td><p class="content-title-noshade">{{name_label}}:</p></td>
		<td><input type="text" name="name" id="name" value="{name}" maxlength="60" class="input400"/>{name_message}</td>
	</tr>
	<tr><td class="buffer" colspan="2"></td></tr>
	<tr>
		<td><p class="content-title-noshade">{{cache_type}}:</p></td>
		<td>
			<select name="type" id="cacheType" class="input200" onchange="return _chkVirtual()">
				{typeoptions}
			</select>&nbsp;&nbsp;<img id="actionicons" name="actionicon" src="" align="top" alt="">{type_message}
		</td>
	</tr>
		<tr><td>&nbsp;</td>
		<td><div class="notice" style="width:500px;height:44px;">{{read_info_about_cache_types}}</div>
		</td></tr>
	<tr>
		<td><p class="content-title-noshade">{{cache_size}}:</p></td>
		<td>
			<select name="size" id="size" class="input200" onchange="return _chkVirtual()" {is_disabled_size}>
				{sizeoptions}
			</select>{size_message}
		</td>
	</tr>
	<tr><td colspan="2"><div class="buffer"></div></td></tr>
	<tr>
		<td valign="top"><p class="content-title-noshade">{{coordinates}}:</p></td>
		<td class="content-title-noshade">
		<fieldset style="border: 1px solid black; width: 80%; height: 32%; background-color: #FAFBDF;">
			<legend>&nbsp; <strong>WGS-84</strong> &nbsp;</legend>&nbsp;&nbsp;&nbsp;
			<select name="latNS" id="latNS" class="input40">
				<option value="N"{latNsel}>N</option>
				<option value="S"{latSsel}>S</option>
			</select>
			&nbsp;<input type="text" id="lat_h"  name="lat_h" maxlength="2" value="{lat_h}" class="input30" />
			&deg;&nbsp;<input type="text" id="lat_min" name="lat_min" maxlength="6" value="{lat_min}" class="input50" />&nbsp;'&nbsp;
			<button onclick="return nearbycachemapOC()">{{check_nearby_caches_map}}</button>
			{lat_message}<br />
			&nbsp;&nbsp;&nbsp;
			<select name="lonEW" id="lonEW" class="input40">
			    <option value="W"{lonWsel}>W</option>
				<option value="E"{lonEsel}>E</option>
			</select>
			&nbsp;<input type="text" id="lon_h" name="lon_h" maxlength="3" value="{lon_h}" class="input30" />
			&deg;&nbsp;<input type="text" id="lon_min" name="lon_min" maxlength="6" value="{lon_min}" class="input50" />&nbsp;'&nbsp;
			<button onclick="return nearbycache()">{{check_nearby_caches}}</button><br />
			 {lon_message}</fieldset>
		</td>
	</tr>
	<tr><td>&nbsp;</td>
		<td><div class="notice" style="width:500px;height:44px;">{{check_nearby_caches_info}}</div>
		</td></tr>
	<tr>
		<td><p class="content-title-noshade">{{country_label}}:</p></td>
		<td>
			<select name="country"  id="country" class="input200" onchange="javascript:chkcountry2()">
				{countryoptions}
			</select>
			{show_all_countries_submit}
		</td>
	</tr>
	<tr><td colspan="2"><div class="buffer"></div></td></tr>
	<tr>
		<td><p id="region0" class="content-title-noshade">{{regiononly}}:</p></td>
		<td>
			<!-- <select name="region" id="region1" class="input200" onchange="javascript:chkcountry()" > --></select>
			<select name="region" id="region1" class="input200" >
				
			</select>&nbsp;&nbsp;<img src="tpl/stdstyle/images/free_icons/help.png" class="icon16" id="region2" alt=""/>&nbsp;<button id="region3" onclick="return extractregion()">{{region_from_coord}}</button>
			<img id="regionAjaxLoader" style="display: none" src="tpl/stdstyle/js/jquery_1.9.2_ocTheme/ptPreloader.gif" />
			{region_message}
		</td>
	</tr>
	<tr><td colspan="2"><div class="buffer"></div></td></tr>
	<tr><td><p class="content-title-noshade">{{difficulty_level}}:</p></td>
		<td>
			{{task_difficulty}}:
			<select name="difficulty" class="input60">
				{difficulty_options}
			</select>&nbsp;&nbsp;
			{{terrain_difficulty}}:
			<select name="terrain" class="input60">
				{terrain_options}
			</select>{diff_message}
		</td>
	</tr>
		<tr>
		<td>&nbsp;</td>
		<td><div class="notice" style="width:500px;height:44px;">{{difficulty_problem}} <a href="rating-c.php" target="_BLANK">{{rating_system}}</a>.</div>
		</td>
	</tr>
	<tr><td><p class="content-title-noshade">{{additional_information}} ({{optional}}):</p></td>
	    <td>
				{{time}}:
				<input type="text" name="search_time" maxlength="10" value="{search_time}" class="input30" /> h
				&nbsp;&nbsp;
				{{length}}:
				<input type="text" name="way_length" maxlength="10" value="{way_length}" class="input30" /> km &nbsp; {effort_message}
			</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><div class="notice" style="width:500px;height:44px">{{time_distance_hint}}</div><div class="buffer"></div></td>
	</tr>
	<tr>
		<td><p class="content-title-noshade">{{waypoint}} ({{optional}}):</p></td>
		<td>
			Geocaching.com: &nbsp;&nbsp;<input type="text" name="wp_gc" value="{wp_gc}" maxlength="7" class="input50"/>
			Navicache.com: <input type="text" name="wp_nc" value="{wp_nc}" maxlength="6" class="input50"/><br/>
			OpenCaching.com: <input type="text" name="wp_tc" value="{wp_tc}" maxlength="7" class="input50"/>
			GPSGames.org: <input type="text" name="wp_ge" value="{wp_ge}" maxlength="6" class="input50"/>

		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><div class="notice" style="width:500px;height:44px;">{{waypoint_other_portal_info}}</div><div class="buffer"></div></td>
	</tr>
	<tr>
		<td colspan="2"><div class="content2-container bg-blue02">
			<p class="content-title-noshade-size1"><img src="tpl/stdstyle/images/blue/attributes.png" class="icon32" alt=""/>&nbsp;{{cache_attributes}}</p>
		</div>
		</td>
	</tr>
	<tr><td class="buffer" colspan="2"></td></tr>
	<tr>
		<td colspan="2">{cache_attrib_list}</td>
	</tr>
	<tr><td colspan="2"><div class="buffer"></div></td></tr>
	<tr>
		<td colspan="2"><div class="notice" style="width:500px;min-height:24px;height:auto;">{{attributes_edit_hint}} {{attributes_desc_hint}}</div></td></tr>
	<tr>
		<td colspan="2"><div class="content2-container bg-blue02"> 
			<p class="content-title-noshade-size1"><img src="tpl/stdstyle/images/blue/describe.png" class="icon32" alt=""/>&nbsp;{{descriptions}}</p>
			</div>
			</td>
	</tr>
	<tr><td colspan="2"><div class="buffer"></div></td></tr>
	<tr>
		<td><p class="content-title-noshade">{{language}}:</p></td>
		<td>
			<select name="desc_lang" class="input200">
				{langoptions}
			</select>
			{show_all_langs_submit}
		</td>
	</tr>
	<tr><td colspan="2"><div class="notice" style="width:500px;height:44px;">{{other_languages_desc}}</div></td></tr>
	<tr>
		<td><p class="content-title-noshade">{{short_desc_label}}:</p></td>
		<td><input type="text" name="short_desc" maxlength="120" value="{short_desc}" class="input400"/></td>
	</tr>
	<tr>
		<td colspan="2">
			<br />
				<p class="content-title-noshade">{{full_description}}:</p>
			<br/>
			{desc_message}
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<div class="menuBar" style="height:20px;">
				<span id="descText" class="buttonNormal" onclick="btnSelect(1)" onmouseover="btnMouseOver(1)" onmouseout="btnMouseOut(1)">Text</span>
				<span class="buttonSplitter">|</span>
				<span id="descHtml" class="buttonNormal" onclick="btnSelect(2)" onmouseover="btnMouseOver(2)" onmouseout="btnMouseOut(2)">&lt;html&gt;</span>
				<span class="buttonSplitter">|</span>
				<span id="descHtmlEdit" class="buttonNormal" onclick="btnSelect(3)" onmouseover="btnMouseOver(3)" onmouseout="btnMouseOut(3)">Editor</span>
			</div>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<span id="scriptwarning" class="errormsg">{{no_javascript}}</span>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<textarea id="desc" name="desc" rows="20" cols="20" class="cachedesc">{desc}</textarea>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<div class="notice" style="width:500px;min-height:24px;height:auto;"><b><i>{{mandatory_field}}.</i></b> {{full_desc_long_text}} {{description_hint}} {{html_usage}} <a href="articles.php?page=htmltags" target="_BLANK">{{available_html}}</a></div> 
		</td>
	</tr>
	<tr><td colspan="2"><div class="buffer"></div></td></tr>
	<tr>
		<td colspan="2"><div class="notice" style="width:500px;min-height:24px;height:auto;">{{additional_enc_info}}</div></td>
	</tr>
	<tr>
		<td colspan="2">
			<textarea name="hints" rows="5" cols="20" class="hint mceNoEditor">{hints}</textarea><br /><br />
		</td>
	</tr>
	<tr>
		<td colspan="2"><div class="content2-container bg-blue02">
		
			<p class="content-title-noshade-size1"><img src="tpl/stdstyle/images/blue/crypt.png" class="icon32" alt=""/>
				{{other}}
			</p>
			</div>
		</td>
	</tr>
	<tr><td colspan="2"><div class="buffer"></div></td></tr>
	<tr><td colspan="2"><div class="notice" style="width:500px;height:24px;">{{add_photo_newcache}}</div></td></tr>
	<tr>
		<td colspan="2">	
		<fieldset style="border: 1px solid black; width: 80%; height: 32%; background-color: #FFFFFF;">
			<legend>&nbsp; <strong>{{date_hidden_label}}</strong> &nbsp;</legend>
			<input class="input40" type="text" name="hidden_year" maxlength="4" value="{hidden_year}"/>-
			<input class="input20" type="text" name="hidden_month" maxlength="2" value="{hidden_month}"/>-
			<input class="input20" type="text" name="hidden_day" maxlength="2" value="{hidden_day}"/>
			{hidden_since_message}
		</fieldset>		
		</td>
	</tr>
	<tr><td colspan="2"><div class="notice buffer" style="width:500px;height:24px;">{{event_hidden_hint}}</div></td></tr>
	{hide_publish_start}
	<tr>
		<td colspan="2">		
		<fieldset style="border: 1px solid black; width: 80%; height: 32%; background-color: #FFFFFF;">
			<legend>&nbsp; <strong>{{submit_new_cache}}</strong> &nbsp;</legend>
			<input type="radio" class="radio" name="publish" id="publish_now" value="now" {publish_now_checked}/>&nbsp;<label for="publish_now">{{publish_now}}</label><br />
			<input type="radio" class="radio" name="publish" id="publish_later" value="later" {publish_later_checked}/>&nbsp;<label for="publish_later">{{publish_date}}:</label>
			<input class="input40" type="text" name="activate_year" maxlength="4" value="{activate_year}"/>-
			<input class="input20" type="text" name="activate_month" maxlength="2" value="{activate_month}"/>-
			<input class="input20" type="text" name="activate_day" maxlength="2" value="{activate_day}"/>&nbsp;
			<select name="activate_hour" class="input40">{activation_hours}
			</select>&nbsp;{{hour}}&nbsp;{activate_on_message}<br />
			<input type="radio" class="radio" name="publish" id="publish_notnow" value="notnow" {publish_notnow_checked}/>&nbsp;<label for="publish_notnow">{{dont_publish_yet}}</label>
		</fieldset>
		</td>
	</tr>
	{hide_publish_end}
	<tr>

		<td colspan="2"><br />	
		<fieldset style="border: 1px solid black; width: 80%; height: 32%; background-color: #FFFFFF;">
		<legend>&nbsp; <strong>{{log_password}}</strong> &nbsp;</legend>
		<input class="input100" type="text" name="log_pw" id="log_pw" value="{log_pw}" maxlength="20"/> ({{no_password_label}})
		</fieldset>
		</td>
	</tr>
	<tr><td colspan="2"><div class="notice buffer" style="width:500px;height:24px;">{{please_read}}</div></td></tr>
	<tr><td colspan="2"><div class="errormsg"><br />{{creating_cache}}<br /><br /></div></td></tr>
	<tr>
		<td colspan="2">
		<button type="submit" name="submitform" value="{submit}" style="font-size:14px;width:160px"><b>{submit}</b></button>

		<br /><br /></td>
	</tr>
</table>
</form>
<script language="javascript" type="text/javascript">
<!--
	/*
		1 = Text
		2 = HTML
		3 = HTML-Editor
	*/
	var use_tinymce = 0;
	var descMode = {descMode};
	document.getElementById("scriptwarning").firstChild.nodeValue = "";
	
	// descMode auf 1 oder 2 stellen ... wenn Editor erfolgreich geladen wieder auf 3 Stellen
	if (descMode == 3)
	{
		toggleEditor("desc");
		use_tinymce = 1;
/*		if (document.getElementById("desc").value == '')
			descMode = 1;
		else
			descMode = 2;*/
	}

	document.getElementById("descMode").value = descMode;
	mnuSetElementsNormal();
	
	function postInit()
	{
		descMode = 3;
		use_tinymce = 1;
		document.getElementById("descMode").value = descMode;
		mnuSetElementsNormal();
	}




	function toggleEditor(id) {
		if (!tinyMCE.getInstanceById(id))
			tinyMCE.execCommand('mceAddControl', false, id);
		else
			tinyMCE.execCommand('mceRemoveControl', false, id);
	}


	function SwitchToTextDesc(oldMode)
	{
		document.getElementById("descMode").value = 1;

		if(use_tinymce)
			toggleEditor("desc");
		use_tinymce = 0;
		// convert HTML to text
		var desc = document.getElementById("desc").value;

		desc = html_entity_decode(desc, ['ENT_NOQUOTES']);

		document.getElementById("desc").value = desc;
	}

	function SwitchToHtmlDesc(oldMode)
	{
		document.getElementById("descMode").value = 2;

		if(use_tinymce)
			toggleEditor("desc");
		use_tinymce = 0;

		// convert text to HTML
		var desc = document.getElementById("desc").value;

		if(oldMode != 3)
			desc = htmlspecialchars(desc, ['ENT_NOQUOTES']);

		document.getElementById("desc").value = desc;
	}

	function SwitchToHtmlEditDesc(oldMode)
	{
		document.getElementById("descMode").value = 3;
		use_tinymce = 1;

		if(oldMode == 2) {
			var desc = document.getElementById("desc").value;
			desc = html_entity_decode(desc, ['ENT_NOQUOTES']);
			document.getElementById("desc").value = desc;
		}

		toggleEditor("desc");
	}

	function mnuSelectElement(e)
	{
		e.backgroundColor = '#D4D5D8';
		e.borderColor = '#6779AA';
		e.borderWidth = '1px';
		e.borderStyle = 'solid';
	}

	function mnuNormalElement(e)
	{
		e.backgroundColor = '#F0F0EE';
		e.borderColor = '#F0F0EE';
		e.borderWidth = '1px';
		e.borderStyle = 'solid';
	}

	function mnuHoverElement(e)
	{
		e.backgroundColor = '#B6BDD2';
		e.borderColor = '#0A246A';
		e.borderWidth = '1px';
		e.borderStyle = 'solid';
	}

	function mnuUnhoverElement(e)
	{
		mnuSetElementsNormal();
	}

	function mnuSetElementsNormal()
	{
		var descText = document.getElementById("descText").style;
		var descHtml = document.getElementById("descHtml").style;
		var descHtmlEdit = document.getElementById("descHtmlEdit").style;

		switch (descMode)
		{
			case 1:
				mnuSelectElement(descText);
				mnuNormalElement(descHtml);
				mnuNormalElement(descHtmlEdit);

				break;
			case 2:
				mnuNormalElement(descText);
				mnuSelectElement(descHtml);
				mnuNormalElement(descHtmlEdit);

				break;
			case 3:
				mnuNormalElement(descText);
				mnuNormalElement(descHtml);
				mnuSelectElement(descHtmlEdit);

				break;
		}
	}

	function btnSelect(mode)
	{
		var descText = document.getElementById("descText").style;
		var descHtml = document.getElementById("descHtml").style;
		var descHtmlEdit = document.getElementById("descHtmlEdit").style;

		var oldMode = descMode;
		descMode = mode;
		mnuSetElementsNormal();

		if(oldMode == descMode)
	{
			// convert text to HTML
			var desc = document.getElementById("desc").value;

			if ((desc.indexOf('&amp;') == -1) &&
			    (desc.indexOf('&quot;') == -1) &&
			    (desc.indexOf('&lt;') == -1) &&
			    (desc.indexOf('&gt;') == -1) &&
			    (desc.indexOf('<p>') == -1) &&
			    (desc.indexOf('<i>') == -1) &&
			    (desc.indexOf('<strong>') == -1) &&
			    (desc.indexOf('<br />') == -1))
			{
				desc = desc.replace(/&/g, "&amp;");
				desc = desc.replace(/"/g, "&quot;");
				desc = desc.replace(/</g, "&lt;");
				desc = desc.replace(/>/g, "&gt;");
				desc = desc.replace(/\r\n/g, "\<br />");
				desc = desc.replace(/\n/g, "<br />");
				desc = desc.replace(/<br \/>/g, "<br />\n");
			}

			document.getElementById("desc").value = desc;
		}


		switch (mode)
		{
			case 1:
				SwitchToTextDesc(oldMode);
				break;
			case 2:
				SwitchToHtmlDesc(oldMode);
				break;
			case 3:

				SwitchToHtmlEditDesc(oldMode);
				break;
		}
	}
	
	function btnMouseOver(id)
	{
		var descText = document.getElementById("descText").style;
		var descHtml = document.getElementById("descHtml").style;
		var descHtmlEdit = document.getElementById("descHtmlEdit").style;

		switch (id)
		{
			case 1:
				mnuHoverElement(descText);
				break;
			case 2:
				mnuHoverElement(descHtml);
				break;
			case 3:
				mnuHoverElement(descHtmlEdit);
				break;
		}
	}
	
	function btnMouseOut(id)
	{
		var descText = document.getElementById("descText").style;
		var descHtml = document.getElementById("descHtml").style;
		var descHtmlEdit = document.getElementById("descHtmlEdit").style;

		switch (id)
		{
			case 1:
				mnuUnhoverElement(descText);
				break;
			case 2:
				mnuUnhoverElement(descHtml);
				break;
			case 3:
				mnuUnhoverElement(descHtmlEdit);
				break;
		}
	}
//-->
</script>



