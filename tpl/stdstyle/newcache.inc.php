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

	 set template specific language variables

 ****************************************************************************/

 $submit = tr('new_cache');
 $default_country = 'PL';
  $default_region = '';
 $default_lang = 'PL';
 $show_all = tr('show_all');
 $default_NS = 'N';
 $default_EW = 'E';
 $date_time_format_message = '&nbsp;Format:&nbsp;DD-MM-RRRR';

 $error_general = '<div class="warning">'.tr('error_new_cache').'</div>';
 $error_coords_not_ok = '<br/><img src="tpl/stdstyle/images/misc/32x32-impressum.png" class="icon32" alt=""  />&nbsp;<span class="errormsg">'.tr('bad_coordinates').'</span>';
 $time_not_ok_message = '<br/><img src="tpl/stdstyle/images/misc/32x32-impressum.png" class="icon32" alt=""  />&nbsp;<span class="errormsg">'.tr('time_incorrect').'</span>';
 $way_length_not_ok_message = '<br/><img src="tpl/stdstyle/images/misc/32x32-impressum.png" class="icon32" alt=""  />&nbsp;<span class="errormsg">'.tr('distance_incorrect').'</span>';
 $date_not_ok_message = '<br/><img src="tpl/stdstyle/images/misc/32x32-impressum.png" class="icon32" alt=""  />&nbsp;<span class="errormsg">'.tr('date_incorrect').'</span>';
 $name_not_ok_message = '<br/><img src="tpl/stdstyle/images/misc/32x32-impressum.png" class="icon32" alt=""  />&nbsp;<span class="errormsg">'.tr('no_cache_name').'</span>';
 $tos_not_ok_message = '<br/><img src="tpl/stdstyle/images/misc/32x32-impressum.png" class="icon32" alt=""  />&nbsp;<span class="errormsg">'.tr('new_cache_no_terms').'</span>';
 $desc_not_ok_message = '<br/><img src="tpl/stdstyle/images/misc/32x32-impressum.png" class="icon32" alt=""  />&nbsp;<span class="errormsg">'.tr('html_incorrect').'</span>';
 $descwp_not_ok_message = '<br/><img src="tpl/stdstyle/images/misc/32x32-impressum.png" class="icon32" alt=""  />&nbsp;<span class="errormsg">Brak opisu.</span>';
 $type_not_ok_message = '<br/><img src="tpl/stdstyle/images/misc/32x32-impressum.png" class="icon32" alt=""  />&nbsp;&nbsp;<span class="errormsg">'.tr('type_incorrect').'</span>';
 $typewp_not_ok_message = '<br/><img src="tpl/stdstyle/images/misc/32x32-impressum.png" class="icon32" alt=""  />&nbsp;&nbsp;<span class="errormsg">Wybierz typ waypointa.</span>';
 $stage_not_ok_message = '<br/><img src="tpl/stdstyle/images/misc/32x32-impressum.png" class="icon32" alt=""  />&nbsp;&nbsp;<span class="errormsg">Nieprawidłowy numer etapu.</span>';
 $size_not_ok_message = '<br/><img src="tpl/stdstyle/images/misc/32x32-impressum.png" class="icon32" alt=""  />&nbsp;&nbsp;<span class="errormsg">'.tr('size_incorrect').'</span>';
 $diff_not_ok_message = '<br/><img src="tpl/stdstyle/images/misc/32x32-impressum.png" class="icon32" alt=""  />&nbsp;&nbsp;<span class="errormsg">'.tr('diff_incorrect').'</span>';
 $sizemismatch_message = '<br/><img src="tpl/stdstyle/images/misc/32x32-impressum.png" class="icon32" alt=""  />&nbsp;&nbsp;<span class="errormsg">'.tr('virtual_cache_size').'</span>';

 $html_desc_errbox = '<br /><br /><p style="margin-top:0px;margin-left:0px;width:550px;background-color:#e5e5e5;border:1px solid black;text-align:left;padding:3px 8px 3px 8px;"><span class="errormsg">'.tr('html_incorrect').'</span><br />%text%</p><br />';

 $cache_submitted = tr('cache_submitted');

 $sel_message = 'Wybierz';
 $cache_size[] = array('id' => '-1', 'pl' => $language['pl']['select_one'], 'en' => $language['en']['select_one']);
 $cache_types[] = array('id' => '-1', 'short' => 'n/a', 'pl' => $language['pl']['select_one'], 'en' => $language['en']['select_one']);
 $wp_types[] = array('id' => '-1', 'short' => 'n/a', 'pl' => $language['pl']['select_one'], 'en' => $language['en']['select_one']);
 
 $cache_attrib_js = "new Array({id}, {selected}, '{img_undef}', '{img_large}')";
 $cache_attrib_pic = '<img id="attr{attrib_id}" src="{attrib_pic}" border="0" alt="{attrib_text}" title="{attrib_text}" onmousedown="toggleAttr({attrib_id})" />&nbsp;';

 $default_lang = 'PL';
 ?>
