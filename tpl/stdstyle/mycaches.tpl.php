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
<script type="text/javascript" src="lib/js/wz_tooltip.js"></script>
<div class="content2-pagetitle"><img src="tpl/stdstyle/images/blue/cache.png" class="icon32" alt="" title="" align="middle"/>&nbsp;{{my_caches_status}}: <font color="black">{cache_stat}</font></div>
<table border="1"  bgcolor="#DBE6F1" style="border-collapse: collapse;font-weight:bold; margin-left: 10px; line-height: 1.4em; font-size: 12px;" width="95%">
<tr>
<td width="15%" align="center" onmouseover="this.style.backgroundColor='#9CBAD6'" onmouseout="this.style.background='#DBE6F1'"><a class="links" href="mycaches.php?status=1">{{active}} ({activeN})</a></td>
<td width="15%" align="center" onmouseover="this.style.backgroundColor='#9CBAD6'" onmouseout="this.style.background='#DBE6F1'"><a class="links" href="mycaches.php?status=2">{{temp_unavailable}} ({unavailableN})</a></td>
<td width="15%" align="center" onmouseover="this.style.backgroundColor='#9CBAD6'" onmouseout="this.style.background='#DBE6F1'"><a class="links" href="mycaches.php?status=3">{{archived}} ({archivedN})</a></td>
<td width="15%" align="center" onmouseover="this.style.backgroundColor='#9CBAD6'" onmouseout="this.style.background='#DBE6F1'"><a class="links" href="mycaches.php?status=5">{{not_published}} ({notpublishedN})</a></td>
<td width="15%" align="center" onmouseover="this.style.backgroundColor='#9CBAD6'" onmouseout="this.style.background='#DBE6F1'"><a class="links" href="mycaches.php?status=4">{{for_approval}} ({approvalN})</a></td>
<td width="15%" align="center" onmouseover="this.style.backgroundColor='#9CBAD6'" onmouseout="this.style.background='#DBE6F1'"><a class="links" href="mycaches.php?status=6">{{blocked}} ({blockedN})</a></td>
</tr>
</table>
<p>&nbsp;</p>
<div class="searchdiv">
<table border="0" cellspacing="2" cellpadding="1" style="margin-left: 10px; line-height: 1.4em; font-size: 13px;" width="95%">
<tr>
<td colspan="2"><a class="links" href="mycaches.php?col=1{my_cache_sort}">{{date_hidden_label}}</a></td>
<td></td>
<td><a class="links" href="mycaches.php?col=2{my_cache_sort}">Geocache</a></td>
<td><a class="links" href="mycaches.php?col=3{my_cache_sort}"><img src="tpl/stdstyle/images/log/16x16-found.png"></a></td>
<td><a class="links" href="mycaches.php?col=4{my_cache_sort}"><img src="images/rating-star.png"></a></td>
<td><a class="links" href="mycaches.php?col=5{my_cache_sort}">{{last_found}}</a></td>
<td><strong>{{latest_logs}}</strong></td>
</tr>
<tr><td colspan="8"><hr></hr></td></tr>
{file_content}
<tr><td colspan="8"><hr></hr></td></tr>
</table>
</div>
	<p>
		{pages}
	</p>

