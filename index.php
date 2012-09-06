<?php
/*
*PHP  3
//ok ok ok
*/

    //echo phpversion();
	error_reporting(0);
	if(version_compare(phpversion(), '5.1.0', '<') == true){die('PHP 5.1 Only');}

	define('SITE_PATH', dirname(__FILE__)."/");
	define('CLASSES', dirname(__FILE__)."/protected/classes/");
	define('CONROLLERS', dirname(__FILE__)."/protected/controllers/");
	define('MODULES', dirname(__FILE__)."/protected/modules/");
	require_once(CLASSES."initializer.php");
?>