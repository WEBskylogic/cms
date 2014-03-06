<?php
/**
 * class to Ajax admin action
 * @author mvc
 */

class AjaxAdminController extends BaseController{
	
	function __construct ($registry, $params)
	{
		$this->registry = $registry;
		parent::__construct($registry, $params);
	}

	function indexAction()
	{
		
	}
	
	///On/off
	function activeAction()
	{
		if(isset($_POST['id'], $_POST['tb']))
		{
			echo json_encode($this->model->active($_POST['id'], $_POST['tb'], $_POST['tb2']));
		}
	}
	
	/////Sort items
	function sortAction()
	{
		if(isset($_POST['arr'], $_POST['tb']))
		{
			echo json_encode($this->model->sortTable($_POST['arr'], $_POST['tb'], $_POST['tb2']));
		}
	}
	
	
	/////Product extra photo view
    function loadextraphotoAction()
    {
		if(isset($_REQUEST['id'], $_REQUEST['tb'], $_REQUEST['fk']))
		{
			$tb=$_REQUEST['tb'];
			$fk=$_REQUEST['fk'].'_id';
			$path=$_REQUEST['path'];
			$res = $this->db->rows("SELECT * FROM `$tb` tb
									LEFT JOIN `".$this->key_lang."_{$tb}` tb2
									ON tb.id=tb2.{$tb}_id
									WHERE {$fk}=?
									ORDER BY sort ASC, id DESC",
			array($_REQUEST['id']));
			
			$this->registry->set('admin', 'product');
			echo $this->view->Render('extra_photo_one.phtml', array('photo'=>$res, 'action'=>$_REQUEST['fk'], 'path'=>$path, 'sub_id'=>$_REQUEST['id']));
		}
    }
	
	function uploadifyAction()
    {
		//$this->db->query("UPDATE ru_news SET title=? WHERE news_id=?", array(var_info($_REQUEST), 1));
		if(isset($_FILES['Filedata'], $_REQUEST['tb'], $_REQUEST['path'], $_REQUEST['fk'], $_REQUEST['id'], $_REQUEST['width'], $_REQUEST['height']))
		{		
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$name = str_replace(strchr($_FILES['Filedata']['name'], "."), "", $_FILES['Filedata']['name']);
			$this->model->loadExtraPhoto($tempFile, $name, $_REQUEST['tb'], $_REQUEST['fk'], $_REQUEST['id'], $_REQUEST['path'], $_REQUEST['width'], $_REQUEST['height']);
			switch ($_FILES['Filedata']['error'])
			{     
				case 0:$msg = "";break;
				case 1:$msg = "The file is bigger than this PHP installation allows";break;
				case 2:$msg = "The file is bigger than this form allows";break;
				case 3:$msg = "Only part of the file was uploaded";break;
				case 4:$msg = "No file was uploaded";break;
				case 6:$msg = "Missing a temporary folder";break;
				case 7:$msg = "Failed to write file to disk";break;
				case 8:$msg = "File upload stopped by extension";break;
				default:$msg = "unknown error ".$_FILES['Filedata']['error'];break;
			}
			
			if($msg)
			{
				$stringData = "Error: ".$_FILES['Filedata']['error']." Error Info: ".$msg;
			}
			else{//This is required for onComplete to fire on Mac OSX
				$stringData = "1";
			}
			
			$targetFile =  str_replace('//','/',$targetPath) . $_FILES['Filedata']['name'];
			echo str_replace($_SERVER['DOCUMENT_ROOT'],'',$targetFile);
		}
    }
	
	/**Crop upload image*/
	
	////Create small Photo
    function createphotoAction()
    {
		if(isset($_POST['path'], $_POST['width'], $_POST['height'], $_POST['src']))
		{
			$file = $_POST['path'].$_POST['image_id']."_b.jpg";
			$file_new = $_POST['path'].$_POST['image_id']."_s.jpg";
			$params = getimagesize($file);
			//var_info($file);
			$img_r = Images::get_image_mime($file, $params['mime']);
			
			$dst_r = imagecreatetruecolor($_POST['width'], $_POST['height']);
			imagecopyresampled($dst_r, $img_r, 0, 0, $_POST['x'], $_POST['y'], $_POST['width'], $_POST['height'], $_POST['w'], $_POST['h']);
			header('Content-type: image/jpeg');
			imagejpeg($dst_r, $file_new, 100);
			
			if(isset($_POST['width2'],$_POST['height2'])&&is_numeric($_POST['width2'])&&is_numeric($_POST['height2']))
			{
				$dst_r = imagecreatetruecolor($_POST['width2'], $_POST['height2']);
				imagecopyresampled($dst_r, $img_r, 0, 0, $_POST['x'], $_POST['y'], $_POST['width2'], $_POST['height2'], $_POST['w'], $_POST['h']);
				header('Content-type: image/jpeg');
				imagejpeg($dst_r, $_POST['path'].$_POST['image_id']."_m.jpg", 100);
			}
			imagedestroy($dst_r);
			unlink($file);  

			$this->set_path_image($_POST['action'], $_POST['image_id'], $file_new);
			return json_encode(array('path'=>$file_new, 'file'=>$_POST['image_id']."_s.jpg"));
		}
    }
	
	///Create big tmp photo from existing photo
	function getcropAction()
	{
		$return=array();
		$_POST['id'] = explode('-', $_POST['id']);
		$return['path'] = $_POST['id'][0];
		$return['fileName'] = str_replace($return['path'], '', current(explode('?',$_POST['src'])));
		$return['fileName']=str_replace('/', '', $return['fileName']);
		$return['image_id'] = $_POST['id'][1];
		$_POST['path'] = str_replace('_s', '', $return['path']);

		Images::substrate(max_width_image, max_height_image, $return['path'].str_replace('_s', '', $return['fileName']), $_POST['id'][0].$_POST['id'][1], false);
		//var_info($return);
		echo json_encode($return);
	}
	
	////Upload photos from other servers
	function curluploadAction()
	{
		$ext = end(explode('.', $_POST['url']));
		if($img = file_get_contents($_POST['url']))
		{
			$file = $_POST['path']."_b.".$ext;
			file_put_contents($file, $img);
			return $this->type_upload($_POST['type_upload'], $file, $_POST['path'], $_POST['width'], $_POST['height'], $_POST['width2'], $_POST['height2']);
		}
		else{
			return'File not founded';
		}
	}
	
	///Create big tmp image
	function includephotoAction()
	{
		if(!is_dir($_POST['path']))mkdir($_POST['path'], 0755, true);
		$return=array();
		$img = str_replace(' ', '+', $_POST['image']);
		$data = explode(';', $img);
		//
		switch($data[0])
		{
			case 'data:image/gif'		: $ext = 'gif'; break;
			case 'data:image/png'		: $ext = 'png'; break;
			case 'data:image/x-ms-bmp'	: $ext = 'bmp'; break;
			default						: $ext = 'jpg'; break;
		}
		$img = str_replace('base64,', '', $data[1]);
		$img = base64_decode($img);
		$file = $_POST['path'].$_POST['image_id']."_b.".$ext;
		file_put_contents($file, $img);
		//echo $file;
		
		//echo memory_get_usage().'===';
		$arr = $this->type_upload($_POST['type_upload'], $file, $_POST['path'].$_POST['image_id'], $_POST['width'], $_POST['height'], $_POST['width2'], $_POST['height2']);
		
		if($_POST['type_upload']!=1)
		{
			$arr=json_decode($arr);
			$arr->err.=$this->set_path_image($_POST['action'], $_POST['image_id'], $arr->path);	
			$arr=json_encode($arr);
		}
		return $arr;
	}
	
	function set_path_image($tb, $id, $path)
	{
		$err='';
		if(!$this->db->query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$tb}' AND COLUMN_NAME = 'photo'"))$err=$tb.' таблица не существует';
		else{
			$this->db->query("UPDATE {$tb} SET photo=? WHERE id=?", array($path, $id));
			Images::set_watermark($this->settings['watermark'], $path, $tb);
		}
		return $err;
	}
	
	///Delete tmp big image
	function delextraAction()
	{
		$path = $_POST['path'].$_POST['image_id'].'_b.jpg';
		if(file_exists($path))
		{
			unlink($path);
		}
	}
	
	function type_upload($type_upload, $file, $path, $width, $height, $width2, $height2)
	{
		$_SESSION['type_upload']=$_POST['type_upload'];
		$return=array();
		$return['err'] = '';
		$checkImage=Images::checkImage($file);
		if($checkImage!='')
		{
			unlink($file);
			$return['err'] = $checkImage;
			return json_encode($return);
		}
		
		$ext='jpg';
		$return['path']=$path."_s.jpg";
		if($type_upload==1)
		{
			$return['err'] = Images::substrate(max_width_image, max_height_image, $file, $path);
		}
		elseif($type_upload==2)
		{
			Images::resizeImage($file, $path.".jpg", $path."_s.jpg", $width, $height);
			//Images::scaleImage($file, $width, $height, $path."_s.png");
			if(isset($width2,$height2)&&is_numeric($width2)&&is_numeric($height2))
			{
				//Images::resizeImage($file, "", $path."_m.jpg", $width2, $height2);	
			}
			unlink($file);
		}
		elseif($type_upload==3)
		{
			$ext = pathinfo($file, PATHINFO_EXTENSION);
			copy($file, $path.'_s.'.$ext);	
			copy($file, $path.'.'.$ext);
			if(isset($width2,$height2)&&is_numeric($width2)&&is_numeric($height2))
			{
				copy($file, $path.'_m.'.$ext);
			}
			$return['path'] = $path.'_s.'.$ext;
			unlink($file);
		}
		$return['ext'] = $ext;
		return json_encode($return);
	}
	
	function delimageAction()
	{
		$data=array();
		$data['message'] ='';
		$tb = $_POST['action'];
		$path = $_POST['path'];
		if(!$this->model->checkAccess('edit', $tb))$data['access'] = messageAdmin('Отказано в доступе', 'error');
		else{
			if(substr($path, 0, 1)=='/')$path = substr($path, 1, strlen($path));
			if(file_exists($path))unlink($path);

			$path=str_replace('_s', '_b', $path);
			if(file_exists($path))unlink($path);
			
			$path=str_replace('_b', '_m', $path);
			if(file_exists($path))unlink($path);
			
			$path=str_replace('_m', '', $path);
			if(file_exists($path))unlink($path);
		}
		return json_encode($data);	
	}
	/*Crop upload image**/
}
?>