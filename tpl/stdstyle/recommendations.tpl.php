<?php
/***************************************************************************
		./tpl/stdstyle/recommendations.tpl.php
		-------------------
		begin                : November 4 2005
		copyright            : (C) 2005 The OpenCaching Group
		forum contact at     : http://www.opencaching.com/phpBB2

   Unicode Reminder メモ

	***************************************************************************/
?>
<table class="content">
	<colgroup>
		<col width="100">
		<col>
	</colgroup>
	<tr><td class="content2-pagetitle" colspan="2"><img src="tpl/stdstyle/images/blue/recommendation.png" border="0" width="32" height="32" alt="Cache-Rekomendacja" title="Cache-Rekomendacja" align="middle"> <b>Rekomendowane skrzynki</b></td></tr>
	<tr><td class="spacer" colspan="2"></td></tr>
	<tr><td class="header-small">Użytkownik rekomenduje skrzynke &quot;<a href="viewcache.php?cacheid={cacheid}">{cachename}</a>&quot;, oraz natępujące skrzynki rekomendował:</td></tr>
	<tr><td class="spacer" colspan="2"></td></tr>
	<tr>
		<td colspan="2">
			<table class="null" border="0" cellspacing="0">
				<tr>
					<td class="header-small" width="50px">Liczba rekomendacji</td>
					<td class="header-small" width="10px">&nbsp;</td>
					<td class="header-small">Nazwa</td>
				</tr>
				{recommendations}
			</table>
		</td>
	</tr>

</table>