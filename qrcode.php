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

   Unicode Reminder ăĄă˘


 ****************************************************************************/

  //prepare the templates and include all neccessary
	require_once('./lib/common.inc.php');
	
	//Preprocessing
	if ($error == false)
	{

			$tplname = 'qrcode';
    
    //set it to writable location, a place for temp generated PNG files
    $PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR;
    
    //html PNG location prefix
    $PNG_WEB_DIR = './tmp/';

    include "./lib/phpqrcode/qrlib.php";    
    
    //ofcourse we need rights to create temp dir
    if (!file_exists($PNG_TEMP_DIR))
        mkdir($PNG_TEMP_DIR);
    
    
    $filename = $PNG_TEMP_DIR.'test.png';
    
    //processing form input
    //remember to sanitize user input in real-life solution !!!
    $errorCorrectionLevel = 'L';
    if (isset($_REQUEST['level']) && in_array($_REQUEST['level'], array('L','M','Q','H')))
        $errorCorrectionLevel = $_REQUEST['level'];    

    $matrixPointSize = 4;
    if (isset($_REQUEST['size']))
        $matrixPointSize = min(max((int)$_REQUEST['size'], 1), 10);


    if (isset($_REQUEST['data'])) { 
        //it's very important!
        if (trim($_REQUEST['data']) == '')
            die('data cannot be empty! <a href="?">back</a>');

            
        // user data
        tpl_set_var('qrcode', $_REQUEST['data']);
		$uuq=md5($_REQUEST['data']);
        $filename = $PNG_TEMP_DIR.'test.png';
        QRcode::png($_REQUEST['data'], $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
        
    } else {    
    
        //default data
        tpl_set_var('qrcode', "OpenCaching PL QR Code");
        QRcode::png('OpenCaching PL QR Code', $filename, $errorCorrectionLevel, $matrixPointSize, 2);    
        
    }    
        
	    // Create image instances
	    $dest =  imagecreatefromjpeg('/var/www/ocpl/lib/phpqrcode/qrcode.jpg');
	    $src =  imagecreatefrompng('/var/www/ocpl/tmp/test.png');
	    
	    // Copy and merge
	    imagecopymerge($dest, $src, 20, 90, 0, 0, 130, 130, 100);
	    // Output and free from memory
	    imagejpeg($dest,'/var/www/ocpl/tmp/qrcode.jpg', 85);
	ImageDestroy($dest);
	// generate number for refresh image
		$rand=rand();

     		tpl_set_var('imgqrcode', '<img src="/tmp/qrcode.jpg?rand='.$rand.'" border="0" alt="" width="171" height="284" />');


	}

	//make the template and send it out
	tpl_BuildTemplate();
?>
