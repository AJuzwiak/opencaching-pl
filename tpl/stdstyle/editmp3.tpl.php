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
function checkForm()
	{
		if(document.editmp3_form.title.value == "")
		{
			alert("Proszę nadać nazwę plikowi!");
			return false;
		}

		if(document.editmp3_form.file.value == "")
		{
			/*alert("Proszę podać źródło obrazka!");
			return false;*/
		}

		return true;
	}//-->
</script>

<form action="editmp3.php" method="post" enctype="multipart/form-data" name="editmp3_form" dir="ltr" onsubmit="return checkForm();">
<input type="hidden" name="uuid" value="{uuid}" />
<table class="content">
	<colgroup>
		<col width="100">
		<col>
	</colgroup>
	<tr><td class="content2-pagetitle" colspan="2"><img src="tpl/stdstyle/images/blue/podcache-mp3.png" class="icon32" alt="" title="edit mp3" align="middle" /> <b>{mp3typedesc} </b><font size="2"><a href="/viewcache.php?cacheid={cacheid}">{cachename}</a></font></td></tr>
	
	<tr><td class="spacer" colspan="2"><br /><br /></td></tr>
	<tr>
		<td valign="top">Tytuł:</td>
		<td><input class="input200" name="title" type="text" value="{title}" size="43" /> {errnotitledesc}</td>
	</tr>

	<tr>
		<td valign="top">Nazwa pliku:</td>
		<td><input class="input200" name="file" type="file" maxlength="{maxmp3size}" /></td>
	</tr>
	<tr><td class="spacer" colspan="2"></td></tr>
	{begin_cacheonly}
	<tr>
		<td align="right"><input class="checkbox" type="checkbox" name="notdisplay" value="1" {notdisplaychecked}></td>
		<td>Tego pliku nie pokazuj</td>
	</tr>
	{end_cacheonly}

	<tr><td class="spacer" colspan="2"></td></tr>

	<tr>
		<td class="header-small" colspan="2">
			<input type="reset" name="reset" value="{{reset}}" style="width:120px"/>&nbsp;&nbsp;
			<input type="submit" name="submit" value="{{submit}}" style="width:120px"/>
		</td>
	</tr>
  </table>
</form>
