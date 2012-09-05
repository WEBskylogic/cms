<?php
if(!empty($_FILES))
{	
	header("Content-Type: text/html; charset=utf-8");
	include_once '../../../../config.php';
	include_once '../../../../library/registry.php';
	include_once '../../../../library/library.php';
	include_once '../../../../library/initializer.php';
	include_once '../../../../library/log.php';
	include_once '../../../../library/pdo.php';
	include_once '../../../../library/imageresizer.php';
	$settings = Registry::getParam('db_settings'); 	
	$user =$settings['user']; $pass =$settings['password'];    //var_info($this->language);
	unset($settings);

	$db = new PDOchild(NULL,$user,$pass);

	$tempFile = $_FILES['Filedata']['tmp_name'];
	
	//$name = iconv( "utf-8","cp1251", $name);
	//$filename=uniqid();
	$name =str_replace(strchr($_FILES['Filedata']['name'], "."), "", $_FILES['Filedata']['name']);
	$query = "insert into product_photo (name, product_id) values('', '{$_REQUEST['id']}')";
	$id=$db->insert_id($query);
	
	$dir="../../../../files/product/{$_REQUEST['id']}/";
	if(!is_dir($dir))
	{
		mkdir($dir, 0755) ;
	}
	resizeImage($tempFile, "../../../../files/product/{$_REQUEST['id']}/{$id}.jpg", "../../../../files/product/{$_REQUEST['id']}/{$id}_s.jpg", 217, 140);
	$tempFile = $_FILES['Filedata']['tmp_name'];
	$targetPath = $_SERVER['DOCUMENT_ROOT'] . $_REQUEST['folder'] . '/';
	$targetFile =  str_replace('//','/',$targetPath) . $_FILES['Filedata']['name'];
	echo str_replace($_SERVER['DOCUMENT_ROOT'],'',$targetFile);
	//break;
}
?>