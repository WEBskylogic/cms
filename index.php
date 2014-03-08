<?php
    //echo phpversion();
	
	if(version_compare(phpversion(), '5.3.0', '<') == true){die('PHP 5.3 Only');}

	define('SITE_PATH', dirname(__FILE__)."/");
	define('CLASSES', dirname(__FILE__)."/protected/classes/");
	define('CONROLLERS', dirname(__FILE__)."/protected/controllers/");
	define('MODULES', dirname(__FILE__)."/protected/modules/");
	define('SUBSYSTEM', dirname(__FILE__)."/protected/subsystem/");
	define('LIBRARY', dirname(__FILE__)."/protected/libraries/");
	require_once(SITE_PATH."protected/config.php");
	require_once(CLASSES."initializer.php");
?>