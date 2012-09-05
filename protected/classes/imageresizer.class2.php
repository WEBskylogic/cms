<?php
	class ImageResizer{
		public $file_name;
		public $tmp_name;
		public $dir_path;
		
		//Set variables
		public function __construct($file_name,$tmp_name,$dir_path){
			$this->file_name = $file_name;
			$this->tmp_name = $tmp_name;
			$this->dir_path = $dir_path;
			$this->getImageInfo();
			$this->moveImage();
		}
		
		//Move the uploaded image to the new directory and rename
		public function moveImage(){
			if(!is_dir($this->dir_path)){
				mkdir($this->dir_path,0777,true);
			}
			if(move_uploaded_file($this->tmp_name, $this->dir_path.mktime().'_'.$this->file_name)){
				$this->setFileName($this->dir_path.mktime().'_'.$this->file_name);
			}
			
		}
		
		//Define the new filename
		public function setFileName($file_name){
			$this->file_name = $file_name;
			return $this->file_name;
		}
		
		//Resize the image function with new max height and width
		public function resizeImage($max_height,$max_width, $path){
		$File=$this->file_name;
		$File_new=$path;
		
		$gab =  getimagesize($File);
		$w=300;
		switch($gab['mime'])
		{
				case 'image/pjpeg'		: $resimage = imagecreatefromjpeg($File); break;
				case 'image/jpeg'		: $resimage = imagecreatefromjpeg($File); break;
				case 'image/gif'		: $resimage = imagecreatefromgif($File); break;
				case 'image/png'		: $resimage = imagecreatefrompng($File); break;
				case 'image/x-ms-bmp'	: $resimage = imagecreatefromwbmp($File); break;
				default					: $resimage = imagecreatefromjpeg($File); break;
		}
		if ($gab[0]>=$w)
		{
			$h = ($gab[1]/($gab[0]/$w));
			$smallimage =  imagecreatetruecolor(500, 500);
			imagefill($smallimage, 0, 0, 0xffffff);
			imagecopyresampled($smallimage, $resimage, 50, 50, 0, 0, $w, $h, $gab[0], $gab[1]);
			imagejpeg($smallimage,$File, 100);
		}
		else{
			$smallimage =  imagecreatetruecolor(500, 500);
			imagefill($smallimage, 0, 0, 0xffffff);
			imagecopyresampled($smallimage, $resimage, 50, 50, 0, 0, $gab[0], $gab[1], $gab[0], $gab[1]);
			imagejpeg($smallimage, $File, 100);
		}
		}
		
		public function getImageInfo(){
			list($width, $height, $type) = getimagesize($this->tmp_name);
			$this->width = $width;
			$this->height = $height;
			$this->file_type = $type;
		}
		
		public function showResizedImage(){
			echo "<img src='".$this->file_name." />";
		}
		
		
		public function onSuccess(){
			header("location: index.php");
		}
	}
?>