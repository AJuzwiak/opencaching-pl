<?php
/***************************************************************************
	*                                         				                                
	*   This program is free software; you can redistribute it and/or modify  	
	*   it under the terms of the GNU General Public License as published by  
	*   the Free Software Foundation; either version 2 of the License, or	    	
	*   (at your option) any later version.
	*   
	*  UTF-8 ąść
	***************************************************************************/
?>
<script type="text/javascript">
<!--
function _chkType () 
{var nextstage = document.forms['waypoints_form'].nextstage.value;
  if (document.waypoints_form.type.value == "4" || document.waypoints_form.type.value == "5" ) 
	{
			document.waypoints_form.stage.value = "0";
			document.waypoints_form.stage.disabled = true;
	}		
		else {
			document.waypoints_form.stage.value = nextstage;
			document.waypoints_form.stage.disabled = false; }
  return false;
}
//-->
</script>
<div class="content2-pagetitle"><img src="tpl/stdstyle/images/blue/compas.png" class="icon32" alt="" />&nbsp;{{add_new_waypoint}} {{for_cache}}: <font color="black">{cache_name}</color></div>
	{general_message}
<form action="newwp.php" method="post" enctype="application/x-www-form-urlencoded" name="waypoints_form" dir="ltr">
<input type="hidden" name="cacheid" value="{cacheid}"/>
<input type="hidden" name="cachetype" value="{cachetype}"/>
<input type="hidden" name="nextstage" value="{nextstage}"/>
<div class="searchdiv">
<table width="90%" class="table" border="0">
	<tr><td class="buffer" colspan="2"></td></tr>
	<tr>
		<td class="content-title-noshade">{{type_wp2}}:</td>
		<td>
			<select name="type" class="input200" onChange="return _chkType()">
				{typeoptions}
			</select>{type_message}
		</td>
	</tr>
	<tr><td>&nbsp;</td>
		<td><div class="notice" style="width:500px;min-height:24px;height:auto;"><a class="links" href="http://wiki.opencaching.pl/index.php/Dodatkowe_waypointy_w_skrzynce" target="_blank">Zobacz opis i rodzaje dodatkowych waypointów</a></div></td>
	</tr>
{start_stage}
		<tr>
		<td class="content-title-noshade">{{number_stage_wp}}:</td>
		<td>
		<input type="text" name="stage" maxlength="2" value="{stage}" class="input30" />{stage_message}
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><div class="notice" style="width:350px;height:44px;">Jeśli ten waypoint nie jest kolejnym etapem wymaganym do odnalezienia skrzynki typu multicache lub quiz wstaw wartość 0.</div>
		</td>
	</tr>
{end_stage}	
	<tr>
		<td valign="top" class="content-title-noshade">{{coordinates}}:</td>
		<td class="content-title-noshade">
		<fieldset style="border: 1px solid black; width: 250px; height: 32%; background-color: #FAFBDF;">
			<legend>&nbsp; <strong>WGS-84</strong> &nbsp;</legend>&nbsp;&nbsp;&nbsp;
			<select name="latNS" class="input40">
				<option value="N"{latNsel}>N</option>
				<option value="S"{latSsel}>S</option>
			</select>
			&nbsp;<input type="text" name="lat_h" maxlength="2" value="{lat_h}" class="input30" />
			&deg;&nbsp;<input type="text" name="lat_min" maxlength="6" value="{lat_min}" class="input50" />&nbsp;'&nbsp;
			{lat_message}<br />
			&nbsp;&nbsp;&nbsp;
			<select name="lonEW" class="input40">
				<option value="E"{lonEsel}>E</option>
				<option value="W"{lonWsel}>W</option>
			</select>
			&nbsp;<input type="text" name="lon_h" maxlength="3" value="{lon_h}" class="input30" />
			&deg;&nbsp;<input type="text" name="lon_min" maxlength="6" value="{lon_min}" class="input50" />&nbsp;'&nbsp;
			{lon_message}
			</fieldset>
		</td>
	</tr>
	<tr><td colspan="2"><div class="buffer"></div></td></tr>
	<tr>
		<td valign="top" class="content-title-noshade">{{describe_wp}}:</td>
		<td class="content-title-noshade">
		<textarea name="desc" rows="10" cols="60">{desc}</textarea>{desc_message}</td>
	</td>
	</tr>
	<tr>
		<td valign="top" class="content-title-noshade">{{status_wp}}:</td>
	</tr>	
	<tr>
		<td valign="top" align="left" colspan="2">
		<table border="0" style="width:600px;font-size: 12px; line-height: 1.6em;">
		<tr><td><input type="radio" name="status" value="1" {checked1} /><label for="status" style="font-size: 12px; line-height: 1.6em;">{{wp_status1}}</label>
		</td></tr>
		<tr><td>
		<input type="radio" name="status" value="2" {checked2} /><label for="status" style="font-size: 12px; line-height: 1.6em;">{{wp_status2}}</label>
		</td></tr>
		<tr><td>
		<input type="radio" name="status" value="3" {checked3} /><label for="status" style="font-size: 12px; line-height: 1.6em;">{{wp_status3}}</label>
		</td></tr></td>
		</table>
<tr><td class="buffer" colspan="2"></td></tr>
	<tr>
		<td valign="top" align="left" colspan="2">
			<button type="submit" name="back" value="back" style="font-size:12px;width:160px"><b>{{cancel}}</b></button>&nbsp;&nbsp;
			<button type="submit" name="submitform" value="submit" style="font-size:12px;width:160px"><b>{{add_new_waypoint}}</b></button>
		<br /><br /></td>
	</tr>

</table>
</form>
</div>