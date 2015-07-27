<?php
/***************************************************************************
																./newcache.php
															-------------------
		begin                : June 24 2004
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

   Unicode Reminder ăĄă˘

	 submitt a new cache

	 used template(s): newcache, viewcache, login

 ****************************************************************************/

  //prepare the templates and include all neccessary
	require_once('./lib/common.inc.php');

	$no_tpl_build = false;

	//Preprocessing
	if ($error == false)
	{
		//user logged in?
		if ($usr == false)
		{
		    $target = urlencode(tpl_get_current_page());
		    tpl_redirect('login.php?target='.$target);
		}
		else
		{			

			if (isset($_REQUEST['beginner']))
				{$beginner=$_GET['beginner'];
			} else { $beginner=1;}

			$rsnc = sql("SELECT COUNT(`caches`.`cache_id`) as num_caches FROM `caches` WHERE `user_id` = ".sql_escape($usr['userid'])." 
										AND status <> 4 AND status <> 5 AND status <> 6");
			$record = sql_fetch_array($rsnc);
			$num_caches = $record['num_caches'];

			$rs = sql("SELECT `hide_flag` as hide_flag FROM `user` WHERE `user_id` =  ".sql_escape($usr['userid']));
			$record = sql_fetch_array($rs);
			$hide_flag = $record['hide_flag'];
			
			if($hide_flag == 10) 
			{
				// user is banned for creating new caches for some reason
				$tplname = 'newcache_forbidden';
				require_once($rootpath . '/lib/caches.inc.php');
				//require_once($stylepath . '/' . $tplname . '.inc.php');				
			} 

			elseif ( $num_caches < $NEED_APPROVE_LIMIT &&  $beginner=='1' )
			{

				// user is banned for creating new caches for some reason
				$tplname = 'newcache_beginner';
				require_once($rootpath . '/lib/caches.inc.php');
				//require_once($stylepath . '/' . $tplname . '.inc.php');
			}
			else 
			{
				$errors = false; // set if there was any errors

				$rsnc = sql("SELECT COUNT(`caches`.`cache_id`) as num_caches FROM `caches` WHERE `user_id` = ".sql_escape($usr['userid'])." 
										AND status = 1");
				$record = sql_fetch_array($rsnc);
				$num_caches = $record['num_caches'];

				if( $num_caches < $NEED_APPROVE_LIMIT )
				{
					// user needs approvement for first 3 caches to be published
					$needs_approvement = true;
					tpl_set_var('hide_publish_start', '<!--');
					tpl_set_var('hide_publish_end', '-->');
					tpl_set_var('approvement_note', '<div class="notice"><font color="red"><b>'.tr('first_cache_approvement').'</b></font></div>');
				}
				else
				{
					$needs_approvement = false;
					tpl_set_var('hide_publish_start', '');
					tpl_set_var('hide_publish_end', '');
					tpl_set_var('approvement_note', '');
				}

				//set here the template to process
				$tplname = 'newcache';
				require_once($rootpath . '/lib/caches.inc.php');
				require_once($stylepath . '/' . $tplname . '.inc.php');
				

				//set template replacements
				tpl_set_var('reset', $reset);
				tpl_set_var('submit', $submit);
				tpl_set_var('general_message', '');
				tpl_set_var('hidden_since_message', $date_time_format_message);
				tpl_set_var('activate_on_message', $date_time_format_message);
				tpl_set_var('lon_message', '');
				tpl_set_var('lat_message', '');
				tpl_set_var('tos_message', '');
				tpl_set_var('name_message', '');
				tpl_set_var('desc_message', '');
				tpl_set_var('effort_message', '');
				tpl_set_var('size_message', '');
				tpl_set_var('type_message', '');
				tpl_set_var('diff_message', '');
				
				
				
				$sel_type = isset($_POST['type']) ? $_POST['type'] : -1;
				if (!isset($_POST['size']))
				{
					if( $sel_type == 6 )
						$sel_size = 7;
					else if ($sel_type == 4 || $sel_type == 5 )
					{
						$sel_type = 1;
						$sel_size = 1;
					}
					else
					{
						$sel_size = -1;
					}
				}
				else
				{
					$sel_size = isset($_POST['size']) ? $_POST['size'] : -1;
					if ($cache_type == 4 || $cache_type == 5 || $cache_type == 6)
					{
						$sel_size = 7;
					}
				}
				$sel_lang = isset($_POST['desc_lang']) ? $_POST['desc_lang'] : $default_lang;
				$sel_country = isset($_POST['country']) ? $_POST['country'] : $default_country;
				$show_all_countries = isset($_POST['show_all_countries']) ? $_POST['show_all_countries'] : 0;
				$show_all_langs = isset($_POST['show_all_langs']) ? $_POST['show_all_langs'] : 0;

				//coords
				$lonEW = isset($_POST['lonEW']) ? $_POST['lonEW'] : $default_EW;
				if ($lonEW == 'E')
				{
					tpl_set_var('lonEsel', ' selected="selected"');
					tpl_set_var('lonWsel', '');
				}
				else
				{
					tpl_set_var('lonEsel', '');
					tpl_set_var('lonWsel', ' selected="selected"');
				}
				$lon_h = isset($_POST['lon_h']) ? $_POST['lon_h'] : '0';
				tpl_set_var('lon_h', htmlspecialchars($lon_h, ENT_COMPAT, 'UTF-8'));

				$lon_min = isset($_POST['lon_min']) ? $_POST['lon_min'] : '00.000';
				tpl_set_var('lon_min', htmlspecialchars($lon_min, ENT_COMPAT, 'UTF-8'));

				$latNS = isset($_POST['latNS']) ? $_POST['latNS'] : $default_NS;
				if ($latNS == 'N')
				{
					tpl_set_var('latNsel', ' selected="selected"');
					tpl_set_var('latSsel', '');
				}
				else
				{
					tpl_set_var('latNsel', '');
					tpl_set_var('latSsel', ' selected="selected"');
				}
				$lat_h = isset($_POST['lat_h']) ? $_POST['lat_h'] : '0';
				tpl_set_var('lat_h', htmlspecialchars($lat_h, ENT_COMPAT, 'UTF-8'));

				$lat_min = isset($_POST['lat_min']) ? $_POST['lat_min'] : '00.000';
				tpl_set_var('lat_min', htmlspecialchars($lat_min, ENT_COMPAT, 'UTF-8'));

				//name
				$name = isset($_POST['name']) ? $_POST['name'] : '';
				tpl_set_var('name', htmlspecialchars($name, ENT_COMPAT, 'UTF-8'));

				//shortdesc
				$short_desc = isset($_POST['short_desc']) ? $_POST['short_desc'] : '';
				tpl_set_var('short_desc', htmlspecialchars($short_desc, ENT_COMPAT, 'UTF-8'));

				//desc
				$desc = isset($_POST['desc']) ? $_POST['desc'] : '';
				tpl_set_var('desc', htmlspecialchars($desc, ENT_COMPAT, 'UTF-8'));

				// descMode auslesen, falls nicht gesetzt aus dem Profil laden
				if (isset($_POST['descMode']))
					$descMode = $_POST['descMode']+0;
				else
				{
					if (sqlValue("SELECT `no_htmledit_flag` FROM `user` WHERE `user_id`='" .  sql_escape($usr['userid']) . "'", 1) == 1)
						$descMode = 1;
					else
						$descMode = 3;
				}
				if (($descMode < 1) || ($descMode > 3)) $descMode = 3;

				// fuer alte Versionen von OCProp
				if (isset($_POST['submit']) && !isset($_POST['version2']))
				{
						$descMode = (isset($_POST['desc_html']) && ($_POST['desc_html']==1)) ? 2 : 1;
						$_POST['submitform'] = $_POST['submit'];

						$short_desc = iconv("utf-8", "UTF-8", $short_desc);
						$desc = iconv("utf-8", "UTF-8", $desc);
						$name = iconv("utf-8", "UTF-8", $name);
				}

						// Text / normal HTML / HTML editor
					tpl_set_var('use_tinymce', (($descMode == 3) ? 1 : 0));

					if ($descMode == 1)
						tpl_set_var('descMode', 1);
					else if ($descMode == 2)
							tpl_set_var('descMode', 2);
						else
					{
						$headers = tpl_get_var('htmlheaders') . "\n";
						$headers .= '<script language="javascript" type="text/javascript" src="lib/phpfuncs.js"></script>' . "\n";
						$headers .= '<script language="javascript" type="text/javascript" src="lib/tinymce/tiny_mce.js"></script>' . "\n";

						$headers .= '<script language="javascript" type="text/javascript" src="lib/tinymce/config/desc.js.php?lang='.$lang.'&amp;cacheid=' . ($desc_record['cache_id']+0) . '"></script>' . "\n";
						tpl_set_var('htmlheaders', $headers);

				tpl_set_var('descMode', 3);
			}

				//effort
				$search_time = isset($_POST['search_time']) ? $_POST['search_time'] : '0';
				$way_length = isset($_POST['way_length']) ? $_POST['way_length'] : '0';

				$search_time = mb_ereg_replace(',', '.', $search_time);
				$way_length = mb_ereg_replace(',', '.', $way_length);

				if (mb_strpos($search_time, ':') == mb_strlen($search_time) - 3)
				{
					$st_hours = mb_substr($search_time, 0, mb_strpos($search_time, ':'));
					$st_minutes = mb_substr($search_time, mb_strlen($st_hours) + 1);

					if (is_numeric($st_hours) && is_numeric($st_minutes))
					{
						if (($st_minutes >= 0) && ($st_minutes < 60))
						{
							$search_time = $st_hours + $st_minutes / 60;
						}
					}
				}

				$st_hours = floor($search_time);
				$st_minutes = sprintf('%02d', ($search_time - $st_hours) * 60);

				tpl_set_var('search_time', $st_hours . ':' . $st_minutes);
				tpl_set_var('way_length', $way_length);


				//hints
				$hints = isset($_POST['hints']) ? $_POST['hints'] : '';
				tpl_set_var('hints', htmlspecialchars($hints, ENT_COMPAT, 'UTF-8'));

				// fuer alte Versionen von OCProp
				if (isset($_POST['submit']) && !isset($_POST['version2']))
				{
						$hints = iconv("utf-8", "UTF-8", $hints);
				}

				//tos
	//			$tos = isset($_POST['TOS']) ? 1 : 0;
	//			if ($tos == 1)
	//				tpl_set_var('toschecked', ' checked="checked"');
	//			else
	//				tpl_set_var('toschecked', '');

				//hidden_since
				$hidden_day = isset($_POST['hidden_day']) ? $_POST['hidden_day'] : date('d');
				$hidden_month = isset($_POST['hidden_month']) ? $_POST['hidden_month'] : date('m');
				$hidden_year = isset($_POST['hidden_year']) ? $_POST['hidden_year'] : date('Y');
				tpl_set_var('hidden_day', htmlspecialchars($hidden_day, ENT_COMPAT, 'UTF-8'));
				tpl_set_var('hidden_month', htmlspecialchars($hidden_month, ENT_COMPAT, 'UTF-8'));
				tpl_set_var('hidden_year', htmlspecialchars($hidden_year, ENT_COMPAT, 'UTF-8'));

				//activation date
				$activate_day = isset($_POST['activate_day']) ? $_POST['activate_day'] : date('d');
				$activate_month = isset($_POST['activate_month']) ? $_POST['activate_month'] : date('m');
				$activate_year = isset($_POST['activate_year']) ? $_POST['activate_year'] : date('Y');
				tpl_set_var('activate_day', htmlspecialchars($activate_day, ENT_COMPAT, 'UTF-8'));
				tpl_set_var('activate_month', htmlspecialchars($activate_month, ENT_COMPAT, 'UTF-8'));
				tpl_set_var('activate_year', htmlspecialchars($activate_year, ENT_COMPAT, 'UTF-8'));

				if(isset($_POST['publish']))
				{
					$publish = $_POST['publish'];
					if($publish == 'now')
					{
						tpl_set_var('publish_now_checked', 'checked="checked"');
					}
					else
					{
						tpl_set_var('publish_now_checked', '');
					}

					if($publish == 'later')
					{
						tpl_set_var('publish_later_checked', 'checked="checked"');
					}
					else
					{
						tpl_set_var('publish_later_checked', '');
					}

					if($publish == 'notnow')
					{
						tpl_set_var('publish_notnow_checked', 'checked="checked"');
					}
					else
					{
						tpl_set_var('publish_notnow_checked', '');
					}
				}
				else
				{
					// Standard
					tpl_set_var('publish_now_checked', '');
					tpl_set_var('publish_later_checked', '');
					tpl_set_var('publish_notnow_checked', 'checked="checked"');
				}

				// fill activate hours
				$activate_hour = isset($_POST['activate_hour']) ? $_POST['activate_hour'] + 0 : date('H') + 0;
				$activation_hours = '';
				for ($i = 0; $i <= 23; $i++)
				{
					if ($activate_hour == $i)
					{
						$activation_hours .= '<option value="' . $i . '" selected="selected">' . $i . '</option>';
					}
					else
					{
						$activation_hours .= '<option value="' . $i . '">' . $i . '</option>';
					}
					$activation_hours .= "\n";
				}
				tpl_set_var('activation_hours', $activation_hours);

				//log-password (no password for traditional caches)
				$log_pw = (isset($_POST['log_pw']) && $sel_type != 2) ? mb_substr($_POST['log_pw'], 0, 20) : '';
				tpl_set_var('log_pw', htmlspecialchars($log_pw, ENT_COMPAT, 'UTF-8'));

				// gc- and nc-waypoints
				$wp_gc = isset($_POST['wp_gc']) ? $_POST['wp_gc'] : '';
				tpl_set_var('wp_gc', htmlspecialchars($wp_gc, ENT_COMPAT, 'UTF-8'));

				$wp_nc = isset($_POST['wp_nc']) ? $_POST['wp_nc'] : '';
				tpl_set_var('wp_nc', htmlspecialchars($wp_nc, ENT_COMPAT, 'UTF-8'));

				//difficulty
				$difficulty = isset($_POST['difficulty']) ? $_POST['difficulty'] : 1;
				$difficulty_options = '<option value="1">'.$sel_message.'</option>';
				for ($i = 2; $i <= 10; $i++)
				{
					if ($difficulty == $i)
					{
						$difficulty_options .= '<option value="' . $i . '" selected="selected">' . $i / 2 . '</option>';
					}
					else
					{
						$difficulty_options .= '<option value="' . $i . '">' . $i / 2 . '</option>';
					}
					$difficulty_options .= "\n";
				}
				tpl_set_var('difficulty_options', $difficulty_options);

				//terrain
				$terrain = isset($_POST['terrain']) ? $_POST['terrain'] : 1;
				$terrain_options = '<option value="1">'.$sel_message.'</option>';;
				for ($i = 2; $i <= 10; $i++)
				{
					if ($terrain == $i)
					{
						$terrain_options .= '<option value="' . $i . '" selected="selected">' . $i / 2 . '</option>';
					}
					else
					{
						$terrain_options .= '<option value="' . $i . '">' . $i / 2 . '</option>';
					}
					$terrain_options .= "\n";
				}
				tpl_set_var('terrain_options', $terrain_options);

				//sizeoptions
				if(checkField('cache_size',$lang) )
					$lang_db = $lang;
				else
					$lang_db = "en";

				$sizes = '';
				foreach ($cache_size as $size)
				{
					if( $sel_type == 6 )
					{
						if ($size['id'] == 7 )
						{
							$sizes .= '<option value="' . $size['id'] . '" selected="selected">' . htmlspecialchars($size[$lang_db], ENT_COMPAT, 'UTF-8') . '</option>';
							tpl_set_var('is_disabled_size', '');
						}
						else
						{
							$sizes .= '<option value="' . $size['id'] . '">' . htmlspecialchars($size[$lang_db], ENT_COMPAT, 'UTF-8') . '</option>';
							tpl_set_var('is_disabled_size', 'disabled');
						}
					}
					else
					if( $size['id'] != 7 )
					{
						if ($size['id'] == $sel_size )
						{
							$sizes .= '<option value="' . $size['id'] . '" selected="selected">' . htmlspecialchars($size[$lang_db], ENT_COMPAT, 'UTF-8') . '</option>';
						}
						else
						{
							$sizes .= '<option value="' . $size['id'] . '">' . htmlspecialchars($size[$lang_db], ENT_COMPAT, 'UTF-8') . '</option>';
						}
					}
				}
				tpl_set_var('sizeoptions', $sizes);

				//typeoptions			
				if(checkField('cache_type',$lang) )
					$lang_db = $lang;
				else
					$lang_db = "en";

				$types = '';
				foreach ($cache_types as $type)
				{
					if( $type['id'] == 4 || $type['id'] == 5 )
						continue;
					if ($type['id'] == $sel_type)
					{
						$types .= '<option value="' . $type['id'] . '" selected="selected">' . htmlspecialchars($type[$lang_db], ENT_COMPAT, 'UTF-8') . '</option>';
					}
					else
					{
						$types .= '<option value="' . $type['id'] . '">' . htmlspecialchars($type[$lang_db], ENT_COMPAT, 'UTF-8') . '</option>';
					}
				}
				tpl_set_var('typeoptions', $types);

				if (isset($_POST['show_all_countries_submit']))
				{
					$show_all_countries = 1;
				}
				elseif (isset($_POST['show_all_langs_submit']))
				{
					$show_all_langs = 1;
				}


				//langoptions
				$langsoptions = '';

				//check if selected country is in list_default
				
				if(checkField('countries','list_default_'.$lang) )
					$lang_db = $lang;
				else
					$lang_db = "en";

				if ($show_all_langs == 0)
				{
					$rs = sql("SELECT `short` FROM `languages` WHERE (`list_default_" . sql_escape($lang_db) . "`=1) AND (`short`='&1')", $sel_lang);
					if (mysql_num_rows($rs) == 0)
					{
						$show_all_langs = 1;
					}
				}

				if ($show_all_langs == 0)
				{
					tpl_set_var('show_all_langs', '0');
					tpl_set_var('show_all_langs_submit', '<input type="submit" name="show_all_langs_submit" value="' . $show_all . '"/>');

					$rs = sql("SELECT `&1`, `short` FROM `languages` WHERE `list_default_" . sql_escape($lang_db) . "`=1 ORDER BY `&1` ASC", $lang_db);
				}
				else
				{
					tpl_set_var('show_all_langs', '1');
					tpl_set_var('show_all_langs_submit', '');

					$rs = sql("SELECT `&1`, `short` FROM `languages` ORDER BY `&1` ASC", $lang_db);
				}

				for ($i = 0; $i < mysql_num_rows($rs); $i++)
				{
					$record = sql_fetch_array($rs);

					if ($record['short'] == $sel_lang)
					{
						$langsoptions .= '<option value="' . htmlspecialchars($record['short'], ENT_COMPAT, 'UTF-8') . '" selected="selected">' . htmlspecialchars($record[$lang_db], ENT_COMPAT, 'UTF-8') . '</option>';
					}
					else
					{
						$langsoptions .= '<option value="' . htmlspecialchars($record['short'], ENT_COMPAT, 'UTF-8') . '">' . htmlspecialchars($record[$lang_db], ENT_COMPAT, 'UTF-8') . '</option>';
					}

					$langsoptions .= "\n";
				}

				tpl_set_var('langoptions', $langsoptions);

				//countryoptions
				$countriesoptions = '';

				//check if selected country is in list_default
				if ($show_all_countries == 0)
				{
					$rs = sql("SELECT `short` FROM `countries` WHERE (`list_default_" . sql_escape($lang_db) . "`=1) AND (`short`='&1')", $sel_country);
					if (mysql_num_rows($rs) == 0)
					{
						$show_all_countries = 1;
					}
				}

				if ($show_all_countries == 0)
				{
					tpl_set_var('show_all_countries', '0');
					tpl_set_var('show_all_countries_submit', '<input type="submit" name="show_all_countries_submit" value="' . $show_all . '"/>');

					$rs = sql("SELECT `&1`, `short` FROM `countries` WHERE `list_default_" . sql_escape($lang_db) . "`=1 ORDER BY `sort_" . sql_escape($lang_db) . "` ASC", $lang_db);
				}
				else
				{
					tpl_set_var('show_all_countries', '1');
					tpl_set_var('show_all_countries_submit', '');

					$rs = sql("SELECT `&1`, `short` FROM `countries` ORDER BY `sort_" . sql_escape($lang_db) . "` ASC", $lang_db);
				}

				for ($i = 0; $i < mysql_num_rows($rs); $i++)
				{
					$record = sql_fetch_array($rs);

					if ($record['short'] == $sel_country)
					{
						$countriesoptions .= '<option value="' . htmlspecialchars($record['short'], ENT_COMPAT, 'UTF-8') . '" selected="selected">' . htmlspecialchars($record[$lang_db], ENT_COMPAT, 'UTF-8') . '</option>';
					}
					else
					{
						$countriesoptions .= '<option value="' . htmlspecialchars($record['short'], ENT_COMPAT, 'UTF-8') . '">' . htmlspecialchars($record[$lang_db], ENT_COMPAT, 'UTF-8') . '</option>';
					}

					$countriesoptions .= "\n";
				}

				tpl_set_var('countryoptions', $countriesoptions);

				// cache-attributes
				$cache_attribs = isset($_POST['cache_attribs']) ? mb_split(';', $_POST['cache_attribs']) : array();

				// cache-attributes
				$cache_attrib_list = '';
				$cache_attrib_array = '';
				$cache_attribs_string = '';

				$rs = sql("SELECT `id`, `text_long`, `icon_undef`, `icon_large` FROM `cache_attrib` WHERE `language`='&1' ORDER BY `category`, `id`", $default_lang);
				if(mysql_num_rows($rs) > 0)
				{
					while($record = sql_fetch_array($rs))
					{
						$line = $cache_attrib_pic;

						$line = mb_ereg_replace('{attrib_id}', $record['id'], $line);
						$line = mb_ereg_replace('{attrib_text}', $record['text_long'], $line);
						if (in_array($record['id'], $cache_attribs))
							$line = mb_ereg_replace('{attrib_pic}', $record['icon_large'], $line);
						else
							$line = mb_ereg_replace('{attrib_pic}', $record['icon_undef'], $line);
						$cache_attrib_list .= $line;

						$line = $cache_attrib_js;
						$line = mb_ereg_replace('{id}', $record['id'], $line);
						if (in_array($record['id'], $cache_attribs))
							$line = mb_ereg_replace('{selected}', 1, $line);
						else
							$line = mb_ereg_replace('{selected}', 0, $line);
						$line = mb_ereg_replace('{img_undef}', $record['icon_undef'], $line);
						$line = mb_ereg_replace('{img_large}', $record['icon_large'], $line);
						if ($cache_attrib_array != '') $cache_attrib_array .= ',';
						$cache_attrib_array .= $line;

						if (in_array($record['id'], $cache_attribs))
						{
							if ($cache_attribs_string != '') $cache_attribs_string .= ';';
							$cache_attribs_string .= $record['id'];
						}
					}
				}
				tpl_set_var('cache_attrib_list', $cache_attrib_list);
				tpl_set_var('jsattributes_array', $cache_attrib_array);
				tpl_set_var('cache_attribs', $cache_attribs_string);

				if (isset($_POST['submitform']))
				{
					//check the entered data

					//check coordinates
					if ($lat_h!='' || $lat_min!='')
					{
						if (!mb_ereg_match('^[0-9]{1,2}$', $lat_h))
						{
							tpl_set_var('lat_message', $error_coords_not_ok);
							$error = true;
							$lat_h_not_ok = true;
						}
						else
						{
							if (($lat_h >= 0) && ($lat_h < 90))
							{
								$lat_h_not_ok = false;
							}
							else
							{
								tpl_set_var('lat_message', $error_coords_not_ok);
								$error = true;
								$lat_h_not_ok = true;
							}
						}

						if (is_numeric($lat_min))
						{
							if (($lat_min >= 0) && ($lat_min < 60))
							{
								$lat_min_not_ok = false;
							}
							else
							{
								tpl_set_var('lat_message', $error_coords_not_ok);
								$error = true;
								$lat_min_not_ok = true;
							}
						}
						else
						{
							tpl_set_var('lat_message', $error_coords_not_ok);
							$error = true;
							$lat_min_not_ok = true;
						}

						$latitude = $lat_h + $lat_min / 60;
						if ($latNS == 'S') $latitude = -$latitude;

						if ($latitude == 0)
						{
							tpl_set_var('lon_message', $error_coords_not_ok);
							$error = true;
							$lat_min_not_ok = true;
						}
					}
					else
					{
						$latitude = NULL;
						$lat_h_not_ok = false;
						$lat_min_not_ok = false;
					}

					if ($lon_h!='' || $lon_min!='')
					{
						if (!mb_ereg_match('^[0-9]{1,3}$', $lon_h))
						{
							tpl_set_var('lon_message', $error_coords_not_ok);
							$error = true;
							$lon_h_not_ok = true;
						}
						else
						{
							if (($lon_h >= 0) && ($lon_h < 180))
							{
								$lon_h_not_ok = false;
							}
							else
							{
								tpl_set_var('lon_message', $error_coords_not_ok);
								$error = true;
								$lon_h_not_ok = true;
							}
						}

						if (is_numeric($lon_min))
						{
							if (($lon_min >= 0) && ($lon_min < 60))
							{
								$lon_min_not_ok = false;
							}
							else
							{
								tpl_set_var('lon_message', $error_coords_not_ok);
								$error = true;
								$lon_min_not_ok = true;
							}
						}
						else
						{
							tpl_set_var('lon_message', $error_coords_not_ok);
							$error = true;
							$lon_min_not_ok = true;
						}

						$longitude = $lon_h + $lon_min / 60;
						if ($lonEW == 'W') $longitude = -$longitude;

						if ($longitude == 0)
						{
							tpl_set_var('lon_message', $error_coords_not_ok);
							$error = true;
							$lon_min_not_ok = true;
						}
					}
					else
					{
						$longitude = NULL;
						$lon_h_not_ok = false;
						$lon_min_not_ok = false;
					}



					$lon_not_ok = $lon_min_not_ok || $lon_h_not_ok;
					$lat_not_ok = $lat_min_not_ok || $lat_h_not_ok;

					//check effort
					$time_not_ok = true;
					if (is_numeric($search_time) || ($search_time == ''))
						{
							$time_not_ok = false;
						}
					if ($time_not_ok)
						{
							tpl_set_var('effort_message', $time_not_ok_message);
							$error = true;
						}
					$way_length_not_ok =true;
					if  (is_numeric($way_length) || ($search_time == ''))
						{
							$way_length_not_ok = false;
						}
					if ($way_length_not_ok)
						{
							tpl_set_var('effort_message', $way_length_not_ok_message);
							$error = true;
						}

					//check hidden_since
					$hidden_date_not_ok = true;
					if (is_numeric($hidden_day) && is_numeric($hidden_month) && is_numeric($hidden_year))
					{
						$hidden_date_not_ok = (checkdate($hidden_month, $hidden_day, $hidden_year) == false);
					}
					if ($hidden_date_not_ok)
					{
						tpl_set_var('hidden_since_message', $date_not_ok_message);
						$error = true;
					}

					if( $needs_approvement )
					{
						$activation_date_not_ok = false;
					}
					else
					{
						//check date_activate if approvement is not required
						$activation_date_not_ok = true;
						
						if (is_numeric($activate_day) && is_numeric($activate_month) && is_numeric($activate_year) && is_numeric($activate_hour))
						{
							$activation_date_not_ok = ((checkdate($activate_month, $activate_day, $activate_year) == false) || $activate_hour < 0 || $activate_hour > 23);
						}
						if ($activation_date_not_ok == false)
						{
							if(!($publish == 'now' || $publish == 'later' || $publish == 'notnow'))
							{
								$activation_date_not_ok = true;
							}
						}
						if ($activation_date_not_ok)
						{
							tpl_set_var('activate_on_message', $date_not_ok_message);
							$error = true;
						}
					}

					//name
					if ($name == '')
					{
						tpl_set_var('name_message', $name_not_ok_message);
						$error = true;
						$name_not_ok = true;
					}
					else
					{
						$name_not_ok = false;
					}

					//tos
	//				if ($tos != 1)
	//				{
	//					tpl_set_var('tos_message', $tos_not_ok_message);
	//					$error = true;
	//					$tos_not_ok = true;
	//				}
	//				else
	//				{
	//					$tos_not_ok = false;
	//				}
	//
					//html-desc?
					$desc_html_not_ok = false;
					if ($descMode != 1)
					{
						require_once($rootpath . 'lib/class.inputfilter.php');

						$myFilter = new InputFilter($allowedtags, $allowedattr, 0, 0, 1);
						$desc = $myFilter->process($desc);
						tpl_set_var('desc', htmlspecialchars($desc, ENT_COMPAT, 'UTF-8'));

						$desc_html_not_ok = false;

						if ($desc_html_not_ok == true)
						{
							tpl_set_var('desc_message', mb_ereg_replace('%text%', $errmsg, $html_desc_errbox));
							$error = true;
						}
					}

					//cache-size
					$size_not_ok = false;
					if ($sel_size == -1)
					{
						tpl_set_var('size_message', $size_not_ok_message);
						$error = true;
						$size_not_ok = true;
					}

					//cache-type
					$type_not_ok = false;
					if ($sel_type == -1 || $sel_type == 4 || $sel_type == 5)
					{
						tpl_set_var('type_message', $type_not_ok_message);
						$error = true;
						$type_not_ok = true;
					}
	/*
					if ($sel_size != 7 && ($sel_type == 4 || $sel_type == 5))
					{
						if (!$size_not_ok) tpl_set_var('size_message', $sizemismatch_message);
						$error = true;
						$size_not_ok = true;
					}
	*/
					if ($sel_size != 7 && ($sel_type == 4 || $sel_type == 5 || $sel_type == 6))
					{
						if (!$size_not_ok) tpl_set_var('size_message', $sizemismatch_message);
						$error = true;
						$size_not_ok = true;
					}
					//difficulty / terrain
					$diff_not_ok = false;
					if ($difficulty < 2 || $difficulty > 10 || $terrain < 2 || $terrain > 10)
					{
						tpl_set_var('diff_message', $diff_not_ok_message);
						$error = true;
						$diff_not_ok = true;
					}

					//no errors?
					if (!($name_not_ok || $hidden_date_not_ok || $activation_date_not_ok || $lon_not_ok || $lat_not_ok || $desc_html_not_ok || $time_not_ok || $way_length_not_ok || $size_not_ok || $type_not_ok || $diff_not_ok))
					{
						//sel_status
						$now = getdate();
						$today = mktime(0, 0, 0, $now['mon'], $now['mday'], $now['year']);
						$hidden_date = mktime(0, 0, 0, $hidden_month, $hidden_day, $hidden_year);
						
						if( $needs_approvement )
						{
							$sel_status = 4;
							$activation_date = 'NULL';
						}
						else
						{
							if (($hidden_date > $today) && ($sel_type != 6))
							{
								$sel_status = 2; //currently not available
							}
							else
							{
								$sel_status = 1; //available
							}
						
							if($publish == 'now')
							{
								$activation_date = 'NULL';
								$activation_column = ' ';
							}
							elseif($publish == 'later')
							{
								$sel_status = 5;
								$activation_date = "'".date('Y-m-d H:i:s', mktime($activate_hour, 0, 0, $activate_month, $activate_day, $activate_year))."'";
							}
							elseif($publish == 'notnow')
							{
								$sel_status = 5;
								$activation_date = 'NULL';
							}
							else
							{
								// should never happen
								$activation_date = 'NULL';
							}
						}
						
						$cache_uuid = create_uuid();
						//add record to caches table
						sql("INSERT INTO `caches` (
													`cache_id`,
													`user_id`,
													`name`,
													`longitude`,
													`latitude`,
													`last_modified`,
													`date_created`,
													`type` ,
													`status` ,
													`country` ,
													`date_hidden` ,
													`date_activate` ,
													`founds` ,
													`notfounds` ,
													`notes` ,
													`last_found` ,
													`size` ,
													`difficulty` ,
													`terrain`,
													`uuid`,
													`logpw`,
													`search_time`,
													`way_length`,
													`wp_gc`,
													`wp_nc`,
													`node`
												) VALUES (
													'', '&1', '&2', '&3', '&4', NOW(), NOW(), '&5', '&6', '&7', '&8', $activation_date, '0', '0', '0', NULL ,
													'&9', '&10', '&11', '&12', '&13', '&14', '&15', '&16', '&17', '&18')",
												$usr['userid'],
												$name,
												$longitude,
												$latitude,
												$sel_type,
												$sel_status,
												$sel_country,
												date('Y-m-d', $hidden_date),
												$sel_size,
												$difficulty,
												$terrain,
												$cache_uuid,
												$log_pw,
												$search_time,
												$way_length,
												$wp_gc,
												$wp_nc,
												$oc_nodeid);
						$cache_id = mysql_insert_id($dblink);

						// waypoint erstellen
						setCacheWaypoint($cache_id);

						$desc_uuid = create_uuid();
						//add record to cache_desc table
						if ($descMode != 1)
						{
							sql("INSERT INTO `cache_desc` (
														`id`,
														`cache_id`,
														`language`,
														`desc`,
														`desc_html`,
														`hint`,
														`short_desc`,
														`last_modified`,
														`uuid`,
														`desc_htmledit`,
														`node`
													) VALUES ('', '&1', '&2', '&3', '1', '&4', '&5', NOW(), '&6', '&7', '&8')",
													$cache_id,
													$sel_lang,
													$desc,
													nl2br(htmlspecialchars($hints, ENT_COMPAT, 'UTF-8')),
													$short_desc,
													$desc_uuid,
													(($descMode == 3) ? 1 : 0),
													$oc_nodeid);
						}
						else
						{
							sql("INSERT INTO `cache_desc` (
														`id`,
														`cache_id`,
														`language`,
														`desc`,
														`desc_html`,
														`hint`,
														`short_desc`,
														`last_modified`,
														`uuid`,
														`desc_htmledit`,
														`node`
													) VALUES ('', '&1', '&2', '&3', '0', '&4', '&5', NOW(), '&6', 0, '&7')",
													$cache_id,
													$sel_lang,
													nl2br(htmlspecialchars($desc, ENT_COMPAT, 'UTF-8')),
													nl2br(htmlspecialchars($hints, ENT_COMPAT, 'UTF-8')),
													$short_desc,
													$desc_uuid,
													$oc_nodeid);
						}

						setCacheDefaultDescLang($cache_id);

						// insert cache-attributes
						for($i=0; $i<count($cache_attribs); $i++)
						{
							if(($cache_attribs[$i]+0) > 0)
							{
								sql("INSERT INTO `caches_attributes` (`cache_id`, `attrib_id`) VALUES ('&1', '&2')", $cache_id, $cache_attribs[$i]+0);
							}
						}

						// only if no approval is needed and cache is published NOW or activate_date is in the past
						if(!$needs_approvement && ($publish == 'now' || ($publish == 'later' && mktime($activate_hour, 0, 0, $activate_month, $activate_day, $activate_year) <= $today)))
						{
							//do event handling
							include_once($rootpath . '/lib/eventhandler.inc.php');

							event_notify_new_cache($cache_id + 0);
							event_new_cache($usr['userid']+0);
						}
						
						if( $needs_approvement )
						{
							// notify RR that new cache has to be verified
							$email_content = read_file($stylepath . '/email/rr_activate_cache.email');
							$email_content = mb_ereg_replace('%username%', $usr['username'], $email_content);
							$email_content = mb_ereg_replace('%cachename%', $name, $email_content);
							$email_content = mb_ereg_replace('%cacheid%', $cache_id, $email_content);	
							$email_headers = "Content-Type: text/plain; charset=utf-8\r\n";
							$email_headers .= "From: Opencaching.pl <notify@opencaching.pl>\r\n";
							$email_headers .= "Reply-To: cog@opencaching.pl\r\n";
							$rr_email = "cog@opencaching.pl";

							//send email to rr
							mb_send_mail($rr_email, "[OC PL] Akceptacja skrzynki: ".$name, $email_content, $email_headers);
							
							sql("UPDATE sysconfig SET value = value + 1 WHERE name = 'hidden_for_approval'");
						}

						// redirection
						tpl_redirect('viewcache.php?cacheid=' . urlencode($cache_id));
					}
					else
					{
						tpl_set_var('general_message', $error_general);
					}
				}
			}
		}
	}
	tpl_set_var('is_disabled_size', '');
	if ($no_tpl_build == false)
	{
		//make the template and send it out
		tpl_BuildTemplate();
	}
?>
