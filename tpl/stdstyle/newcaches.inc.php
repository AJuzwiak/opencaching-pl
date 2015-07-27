<?php
/***************************************************************************
												  ./tpl/stdstyle/newcaches.inc.php
															-------------------
		begin                : Mon November 5 2005
		copyright            : (C) 2005 The OpenCaching Group
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
 
 $next_img = '<img src="'.$stylepath.'/images/action/16x16-next.png" alt="&gt;"/>';
 $prev_img = '<img src="'.$stylepath.'/images/action/16x16-prev.png" alt="&lt;"/>';
 $last_img = '<img src="'.$stylepath.'/images/action/16x16-last.png" alt="&gt;&gt;"/>';
 $first_img = '<img src="'.$stylepath.'/images/action/16x16-first.png" alt="&lt;&lt;"/>';
 $next_img_inactive = '<img src="'.$stylepath.'/images/action/16x16-next_inactive.png" alt="&gt;"/>';
 $prev_img_inactive = '<img src="'.$stylepath.'/images/action/16x16-prev_inactive.png" alt="&lt;"/>';
 $last_img_inactive = '<img src="'.$stylepath.'/images/action/16x16-last_inactive.png" alt="&gt;&gt;"/>';
 $first_img_inactive = '<img src="'.$stylepath.'/images/action/16x16-first_inactive.png" alt="&lt;&lt;"/>';
 
 $tpl_line = '{date} ({country}): <img src="{imglink}" width="16" height="16" border="0" alt="Cache" title="Cache" style="margin-top:4px;" /> <a href="viewcache.php?cacheid={cacheid}">{cachename}</a> {{created_by}} <a href="viewprofile.php?userid={userid}">{username}</a><br />';
?>
