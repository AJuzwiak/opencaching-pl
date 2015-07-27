<?php
/***************************************************************************
																./register.php
															-------------------
		begin                : Mon June 14 2004
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

	 register a new user

	 used template(s): register

 ****************************************************************************/

  //prepare the templates and include all neccessary
	require_once('./lib/common.inc.php');

	//Preprocessing
	if ($error == false)
	{
		//set here the template to process
		$tplname = 'register';

		//load language specific variables
		require_once($stylepath . '/' . $tplname . '.inc.php');

		//set to defaults
	  tpl_set_var('register', $register);
	  tpl_set_var('reset', $reset);
	  tpl_set_var('tos_message', '');
	  tpl_set_var('all_countries_submit', '');
	  tpl_set_var('countries_list', '');
	  tpl_set_var('email_message', '');
	  tpl_set_var('email', '');
	  tpl_set_var('username', '');
	  tpl_set_var('username_message', '');
	  tpl_set_var('password_message', '');
	  tpl_set_var('show_all_countries', 0);

// Languages

			tpl_set_var('email_address', tr('email_address'));
			tpl_set_var('user', tr('user'));
			tpl_set_var('password', tr('password'));
			tpl_set_var('country_label', tr('country_label'));
			tpl_set_var('registration', tr('registration'));

			tpl_set_var('register_new', tr('register_new'));
			tpl_set_var('password_confirm', tr('password_confirm'));
			tpl_set_var('register_msg1', tr('register_msg1'));
			tpl_set_var('register_msg2', tr('register_msg2'));
			tpl_set_var('register_msg3', tr('register_msg3'));
			tpl_set_var('register_msg4', tr('register_msg4'));
			tpl_set_var('register_msg5', tr('register_msg5'));
			tpl_set_var('register_msg6', tr('register_msg6'));

//			tpl_set_var('no_answer', tr('no_answer'));


		if (isset($_POST['submit']) || isset($_POST['show_all_countries_submit']))
		{
		  //form load setting
			$display_all_countries = $_POST['allcountries'];
			$username = $_POST['username'];
			$password = $_POST['password1'];
			$password2 = $_POST['password2'];
			$email = $_POST['email'];
			$country = $_POST['country'];
			$tos = isset($_POST['TOS']) ? ($_POST['TOS'] == 'ON') : false;
		

			if (isset($_POST['submit']))
			{
				//try to register

				//validate the entered data
				$email_not_ok = !is_valid_email_address($email);
				$username_not_ok = mb_ereg_match(regex_username, $username) ? false : true;
				if ($username_not_ok == false)
				{
					// username should not be formatted like an email-address
					$username_not_ok = is_valid_email_address($username);
				}
				$password_not_ok = mb_ereg_match(regex_password, $password) ? false : true;
				$password_diffs = ($password != $password2);

				//check if email is in the database
				$rs = sql("SELECT `username` FROM `user` WHERE `email`='&1'", $email);
				if (mysql_num_rows($rs) > 0)
				{
					$email_exists = true;
				}
				else
				{
					$email_exists = false;
				}

				//check if username is in the database
				$rs = sql("SELECT `username` FROM `user` WHERE `username`='&1'", $username);
				if (mysql_num_rows($rs) > 0)
				{
					$username_exists = true;
				}
				else
				{
					$username_exists = false;
				}

				$all_ok = false;
				if ((!$email_not_ok) &&
						(!$username_not_ok) &&
						(!$password_not_ok) &&
						(!$password_diffs) &&
					  (!$email_exists))
				{
					if ($username_exists == false)
					{
						if ($tos == true)
						{
							$all_ok = true;
						}
					}
				}

				if ($all_ok)
				{
					//send email

					//generate random password
					$activationcode = mb_strtoupper(mb_substr(md5(uniqid('')), 0, 13));

					//process email
					$email_content = read_file($stylepath . '/email/register.email');

					$email_content = mb_ereg_replace('%user%', $username, $email_content);
					$email_content = mb_ereg_replace('%email%', $email, $email_content);
					$country_name = db_CountryFromShort($country);
					$email_content = mb_ereg_replace('%country%', $country_name, $email_content);
					$email_content = mb_ereg_replace('%code%', $activationcode, $email_content);

					$uuid = create_uuid();
					if(strtotime("2008-11-01 00:00:00") <= strtotime(date("Y-m-d h:i:s")))
						$rules_conf_req = 1;
					else $rules_conf_req = 0;
					//insert the user
					sql("INSERT INTO `user` ( `user_id`, `username`, `password`, `email`, `latitude`,
					                          `longitude`, `last_modified`, `login_faults`, `login_id`, `is_active_flag`,
					                          `was_loggedin`, `country`, `date_created`,
					                          `uuid`, `activation_code`, `node`, `rules_confirmed`
					                        ) VALUES ('', '&1', '&2', '&3', NULL, NULL, NOW(), '0', '0', '0', '0', '&4', NOW(), '&5', '&6', '&7', &8)",
					                        $username,
					                        hash('sha512', md5($password)),
					                        $email,
					                        $country,
					                        $uuid,
					                        $activationcode,
					                        $oc_nodeid,
																	$rules_conf_req);

					mb_send_mail($email, $register_email_subject, $email_content, $emailheaders);

					//display confirmationpage
					$tplname = 'register_confirm';
				  tpl_set_var('country', htmlspecialchars($country_name, ENT_COMPAT, 'UTF-8'));
				}
				else
				{
					//set error strings
					if ($email_not_ok)	tpl_set_var('email_message', $error_email_not_ok);
					if ($username_not_ok)	tpl_set_var('username_message', $error_username_not_ok);
					if ($email_exists)	tpl_set_var('email_message', $error_email_exists);
					if ($username_exists)	tpl_set_var('username_message', $error_username_exists);

					if ($password_not_ok)
						tpl_set_var('password_message', $error_password_not_ok);
					else
						if ($password_diffs)
							tpl_set_var('password_message', $error_password_diffs);

					if ($tos == false) tpl_set_var('tos_message', $error_tos);
					
				}
			}
			else if (isset($_POST['show_all_countries_submit']))
			{
				//display all countries
				$display_all_countries = 1;
			}
		}
		else
		{
			//set to defaults
			$display_all_countries = 0;
			$username = '';
			$email = '';
			$country = $default_country;
			$tos = false;
		}

	  tpl_set_var('email', htmlspecialchars($email, ENT_COMPAT, 'UTF-8'));
	  tpl_set_var('username', htmlspecialchars($username, ENT_COMPAT, 'UTF-8'));

	  //make countries list
		if ($country == 'XX')
		{
			$stmp = '<option value="XX" selected="selected">' . $no_answer . '</option>';
		}
		else
		{
			$stmp = '<option value="XX">' . $no_answer . '</option>';
		}

		if ($display_all_countries == 0)
		{
		  $rs = sql('SELECT `&1`, `short` FROM `countries` WHERE `list_default_' . $lang . '`=1 ORDER BY `sort_' . $lang . '` ASC', $lang);
		  tpl_set_var('all_countries_submit', '<input type="submit" name="show_all_countries_submit" value="' . $allcountries . '" />');
		}
		else
		{
		  $rs = sql('SELECT `&1`, `short` FROM `countries` ORDER BY `sort_' . $lang . '` ASC', $lang);
		}

  	for ($i = 0; $i < mysql_num_rows($rs); $i++)
		{
			$record = sql_fetch_array($rs);

			if ($country == $record['short'])
			{
				$stmp .= '<option value="' . $record['short'] . '" selected="selected">' . htmlspecialchars($record[$lang], ENT_COMPAT, 'UTF-8') . "</option>\n";
			}
			else
			{
				$stmp .= '<option value="' . $record['short'] . '">' . htmlspecialchars($record[$lang], ENT_COMPAT, 'UTF-8') . "</option>\n";
			}
		}

	  tpl_set_var('countries_list', $stmp);
	  unset($stmp);

	  tpl_set_var('show_all_countries', $display_all_countries);
	}

	//make the template and send it out
	tpl_BuildTemplate();
?>

