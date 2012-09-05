<?php
class UploadFileXhr{
	function save($path, $name)
	{
		$input = fopen("php://input", "r");
		$fp = fopen($path, "w");
		$pref = "";
		$uploaddir = $_SESSION['path'];

		while ($data = fread($input, 1024)){			
			fwrite($fp,$data);
		}
		
		fclose($fp);
		fclose($input);
		$input = fopen("php://input", "r");
		$fp2 = fopen($name, "w");
		$max_height = 500;
		$max_width = 500;
		$pref = "";
		$uploaddir = $_SESSION['path'];

		while ($data = fread($input, 1024)){			
			fwrite($fp2,$data);
		}
		fclose($fp2);
		fclose($input);
		require_once('imageresizer.class.php');
		$resizer = new ImageResizer($path, $path, $pref.$uploaddir, $name);
		$resizer->resizeImage($max_width, $max_height, $path, $name);
		$resizer->showResizedImage();		
	}
	
	function getName(){
		return $_GET['qqfile'];
	}
	
	 
}
?>