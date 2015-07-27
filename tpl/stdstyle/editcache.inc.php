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

   Unicode Reminder ??

	 language vars

 ****************************************************************************/

	$submit = 'Zapisz';
	$remove = 'Usuń';
	$edit = 'Edytuj';
$error_general = '<div class="warning">'.tr('error_new_cache').'</div>';
 $error_coords_not_ok = '<br/><img src="tpl/stdstyle/images/misc/32x32-impressum.png" class="icon32" alt=""  />&nbsp;<span class="errormsg">'.tr('bad_coordinates').'</span>';
 $time_not_ok_message = '<br/><img src="tpl/stdstyle/images/misc/32x32-impressum.png" class="icon32" alt=""  />&nbsp;<span class="errormsg">'.tr('time_incorrect').'</span>';
 $way_length_not_ok_message = '<br/><img src="tpl/stdstyle/images/misc/32x32-impressum.png" class="icon32" alt=""  />&nbsp;<span class="errormsg">'.tr('distance_incorrect').'</span>';
 $date_not_ok_message = '<br/><img src="tpl/stdstyle/images/misc/32x32-impressum.png" class="icon32" alt=""  />&nbsp;<span class="errormsg">'.tr('date_incorrect').'</span>';
 $name_not_ok_message = '<br/><img src="tpl/stdstyle/images/misc/32x32-impressum.png" class="icon32" alt=""  />&nbsp;<span class="errormsg">'.tr('no_cache_name').'</span>';
 $tos_not_ok_message = '<br/><img src="tpl/stdstyle/images/misc/32x32-impressum.png" class="icon32" alt=""  />&nbsp;<span class="errormsg">'.tr('new_cache_no_terms').'</span>';
 $desc_not_ok_message = '<br/><img src="tpl/stdstyle/images/misc/32x32-impressum.png" class="icon32" alt=""  />&nbsp;<span class="errormsg">'.tr('html_incorrect').'</span>';
 $type_not_ok_message = '<br/><img src="tpl/stdstyle/images/misc/32x32-impressum.png" class="icon32" alt=""  />&nbsp;&nbsp;<span class="errormsg">'.tr('type_incorrect').'</span>';
 $size_not_ok_message = '<br/><img src="tpl/stdstyle/images/misc/32x32-impressum.png" class="icon32" alt=""  />&nbsp;&nbsp;<span class="errormsg">'.tr('size_incorrect').'</span>';
 $diff_not_ok_message = '<br/><img src="tpl/stdstyle/images/misc/32x32-impressum.png" class="icon32" alt=""  />&nbsp;&nbsp;<span class="errormsg">'.tr('diff_incorrect').'</span>';
 $sizemismatch_message = '<br/><img src="tpl/stdstyle/images/misc/32x32-impressum.png" class="icon32" alt=""  />&nbsp;&nbsp;<span class="errormsg">'.tr('virtual_cache_size').'</span>';
	$all_countries_submit = '<input type="submit" name="show_all_countries_submit" value="Wszystkie kraje"/>';

	$status_message = '&nbsp;<span class="errormsg">Status skrzynki nie pasuje do opcji publikacji.</span>';
	$nopictures = '<tr><td colspan="2"><div class="notice">Nie ma załączonych obrazków.</div></td></tr>';
	$pictureline = '<tr><td colspan="2"><img src="tpl/stdstyle/images/free_icons/picture.png" class="icon32" alt=""  />&nbsp;<a href="{link}">{title}</a>&nbsp;&nbsp;<img src="images/actions/edit-16.png" align="middle" alt="" title="" /> [<a href="editpic.php?uuid={uuid}">'.$edit.'</a>] <img src="tpl/stdstyle/images/log/16x16-trash.png" border="0" align="middle" class="icon16" alt="" title="" />[<a href="removepic.php?uuid={uuid}">'.$remove.'</a>]</td></tr>';
	$picturelines = '{lines}<tr><td colspan="2">&nbsp;</td></tr>';
	
	$nomp3 = '<tr><td colspan="2"><div class="notice">Nie ma załączonych plików MP3</div></td></tr>';
	$mp3line = '<tr><td colspan="2"><img src="tpl/stdstyle/images/free_icons/sound.png" class="icon32" alt=""  />&nbsp;<a href="{link}">{title}</a>&nbsp;&nbsp;<img src="images/actions/edit-16.png"  align="middle"  alt="" title="" /> [<a href="editmp3.php?uuid={uuid}">'.$edit.'</a>] <img src="tpl/stdstyle/images/log/16x16-trash.png" border="0" align="middle" class="icon16" alt="" title="" />[<a href="removemp3.php?uuid={uuid}">'.$remove.'</a>]</td></tr>';
	$mp3lines = '{lines}<tr><td colspan="2">&nbsp;</td></tr>';

	$nowp = '<div class="notice">Nie ma dodatkowych waypointów dla skrzynki</div>';
	$wpline='<tr>{stagehide_start}<td align="center" valign="middle"><center>{number}</center></td>{stagehide_end}<td align="center" valign="middle"><center><img src="{wp_icon}" alt="" title="{type}" /></center></td><td align="center" valign="middle">{type}</td><td align="center" valign="middle"><b><span style="color: rgb(88,144,168)">{lat}<br />{lon}</span></b></td><td align="center" valign="middle">{desc}</td><td align="center" valign="middle"><center><img src="{status}" alt="" title="Status" /></center></td><td align="center" valign="middle"><center><a class="links" href="editwp.php?wpid={wpid}"><img src="images/actions/edit-16.png" alt="" title="Edit WP" /></a></center></td><td align="center" valign="middle"><center><a class="links" href="editwp.php?wpid={wpid}&delete" onclick="return confirm(\'Czy usunąć ten waypoint?\');"><img src="tpl/stdstyle/images/log/16x16-trash.png" align="middle" class="icon16" alt="" title="Usuń WP" /></a></center></td> </tr>';


	$cache_attrib_js = "new Array({id}, {selected}, '{img_undef}', '{img_large}')";
	$cache_attrib_pic = '<img id="attr{attrib_id}" src="{attrib_pic}" border="0" alt="{attrib_text}" title="{attrib_text}" onmousedown="toggleAttr({attrib_id})" /> ';

	$default_lang = $lang;

	 $activation_form = '
		<tr><td colspan="2">		
		<fieldset style="border: 1px solid black; width: 80%; height: 32%; background-color: #FFFFFF;">
			<legend>&nbsp; <strong>'.tr("submit_new_cache").'</strong> &nbsp;</legend>
				<input type="radio" class="radio" name="publish" id="publish_now" value="now" {publish_now_checked}>&nbsp;<label for="publish_now">Publikuj teraz</label><br />
				<input type="radio" class="radio" name="publish" id="publish_later" value="later" {publish_later_checked}>&nbsp;<label for="publish_later">Opublikuj dnia:</label>
				<input class="input40" type="text" name="activate_year" maxlength="4" value="{activate_year}"/> - 
				<input class="input20" type="text" name="activate_month" maxlength="2" value="{activate_month}"/> - 
				<input class="input20" type="text" name="activate_day" maxlength="2" value="{activate_day}"/>&nbsp;
				<select name="activate_hour" class="input40">
					{activation_hours}
				</select>&nbsp;godzina&nbsp;{activate_on_message}<br />
				<input type="radio" class="radio" name="publish" id="publish_notnow" value="notnow" {publish_notnow_checked}>&nbsp;<label for="publish_notnow">Jeszcze nie publikuj</label>
				</fieldset>
				</td>
		</tr>
		';
?>
