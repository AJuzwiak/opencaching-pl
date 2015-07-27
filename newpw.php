<?php
/***************************************************************************
																./newpw.php
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

   Unicode Reminder メモ

	 send the user an email and change the password

	 used template(s): newpw

 ****************************************************************************/

	//prepare the templates and include all neccessary
	require_once('./lib/common.inc.php');

	//Preprocessing
	if ($error == false)
	{
		//set here the template to process
		$tplname = 'newpw';

		//load language specific variables
		require_once($stylepath . '/' . $tplname . '.inc.php');

		if ($usr == false)
		{
			tpl_set_var('email', '');
		}
		else
		{
			tpl_set_var('email', htmlspecialchars($usr['email'], ENT_COMPAT, 'UTF-8'));
		}
		tpl_set_var('message', '');
		tpl_set_var('email_message', '');
		tpl_set_var('code_message', '');
		tpl_set_var('pw_message', '');
		tpl_set_var('code', '');
		tpl_set_var('changepw', $changepw);
		tpl_set_var('getcode', $getcode);
		tpl_set_var('reset', $reset);
		tpl_set_var('step1', tr('step1'));
		tpl_set_var('step2', tr('step2'));	
		tpl_set_var('new_password_msg1', tr('new_password_msg1'));
		tpl_set_var('new_password_msg2', tr('new_password_msg2'));
		tpl_set_var('security_code', tr('security_code'));
		tpl_set_var('change_password_msg1', tr('change_password_msg1'));
		tpl_set_var('new_password', tr('new_password'));		
		tpl_set_var('password_confirm', tr('password_confirm'));
		tpl_set_var('email_address', tr('email_address'));
		
		if (isset($_POST['submit_getcode']) || isset($_POST['submit_changepw']))
		{
			if (isset($_POST['submit_getcode']))
			{
				// try to send the code via email
				$email = $_POST['email'];
				tpl_set_var('email', htmlspecialchars($email, ENT_COMPAT, 'UTF-8'));

				$rs = sql("SELECT `is_active_flag`, `username` FROM `user` WHERE `email`='&1'", $email);
				if (mysql_num_rows($rs) > 0)
				{
					//ok, a user with this email does exist
					$record = sql_fetch_array($rs);

					if ($record['is_active_flag'] != 1)
					{
						//no active user with this email exists
						tpl_set_var('email_message', $emailnotexist);
					}
					else
					{
						$secure_code = uniqid('');

						//set code in DB
						sql("UPDATE `user` SET `new_pw_date`='&1', `new_pw_code`='&2' WHERE `email`='&3'", time(), $secure_code, $email);

						$email_content = read_file($stylepath . '/email/newpw.email');

						$email_content = mb_ereg_replace('%user%', $record['username'], $email_content);
						$email_content = mb_ereg_replace('%date%', strftime($datetimeformat), $email_content);
						$email_content = mb_ereg_replace('%code%', $secure_code, $email_content);

						//send email
						mb_send_mail($email, $newpw_subject, $email_content, $emailheaders);

						//output message
						tpl_set_var('message', $emailsend);
					}
				}
				else
				{
					//no user with this email exists
					tpl_set_var('email_message', $emailnotexist);
				}
			}
			else if (isset($_POST['submit_changepw']))
			{
				// try to change the pw
				$email = $_POST['email'];
				$code = $_POST['code'];
				$password = $_POST['password'];
				$rp_pass = $_POST['rp_pass'];

				tpl_set_var('email', htmlspecialchars($email, ENT_COMPAT, 'UTF-8'));

				$rs = sql("SELECT `is_active_flag`, `new_pw_code`, `new_pw_date` FROM `user` WHERE `email`='&1'", $email);
				if (mysql_num_rows($rs) > 0)
				{
					//ok, a user with this email does exist
					$record = sql_fetch_array($rs);

					if ($record['is_active_flag'] != 1)
					{
						//no active user with this email exists
						tpl_set_var('email_message', $emailnotexist);
					}
					else
					{
						if ($record['new_pw_code'] == $code)
						{
							if (time() - $record['new_pw_date'] < 259200)
							{
								if (!mb_ereg_match(regex_password, $password))
								{
									//no valid password
									tpl_set_var('code', $code);
									tpl_set_var('pw_message', $pw_not_ok);
								}
								else if ($password !== $rp_pass)
								{
									//both pw's dont match
									tpl_set_var('code', $code);
									tpl_set_var('pw_message', $pw_no_match);
								}
								else
								{
									//set new pw
									sql("UPDATE `user` SET `new_pw_date`=0, `new_pw_code`=NULL, `password`='&1', `last_modified`=NOW() WHERE `email`='&2'", hash('sha512', md5($password)), $email);
									tpl_set_var('message', $pw_changed);
								}
							}
							else
							{
								//code timed out
								tpl_set_var('message', $code_timed_out);
							}
						}
						else
						{
							//wrong code
							tpl_set_var('code_message', $code_not_ok);
						}
					}
				}
				else
				{
					//no user with this email exists
					tpl_set_var('email_message', $emailnotexist);
				}
			}
		}
	}

	//make the template and send it out
	tpl_BuildTemplate();
?>
