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
				mkdir($this->dir_path,0755,true);
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
		
		public function Copyright($file_str)
		{		
			$path=$_SERVER['DOCUMENT_ROOT']."/tpl/";
			$file=$file_str;
			$res=@imagecreatefromjpeg($file);
			$image_size=getimagesize($file);
			if($image_size[0]<425||$image_size[1]<378)
			{
				$logo=@imagecreatefrompng($path."images/water_logo_s.png");
				$logo_size=getimagesize($path."images/water_logo_s.png");
			}
			else{
				$logo=@imagecreatefrompng($path."images/water_logo.png");
				$logo_size=getimagesize($path."images/water_logo.png");
			}
			
			$dstX=@imagesx($res)-($logo_size[0]/2)-($image_size[0]/2);
			$dstY=@imagesy($res)-($image_size[1]/2)-($logo_size[1]/2);	
			$srcX=0;
			$srcY=0;
			$dstW=@imagesx($logo); $dstH=@imagesy($logo);
			$srcW=@imagesx($logo); $srcH=@imagesy($logo);
			@imagecopyresized($res,$logo,$dstX,$dstY,$srcX,$srcY,$dstW,$srcH,$srcW,$srcH);
			@imageJpeg($res,$file,100);       
		}

		//Resize the image function with new max height and width
		public function resizeImage($max_width, $max_height, $path, $name='')
		{
			$File=$this->file_name;
			$File_new=$path;
			
			$gab =  getimagesize($File);
			$w=450;
			$h=450;
			switch($gab['mime'])
			{
				case 'image/pjpeg'		: $resimage = imagecreatefromjpeg($File); break;
				case 'image/jpeg'		: $resimage = imagecreatefromjpeg($File); break;
				case 'image/gif'		: $resimage = imagecreatefromgif($File); break;
				case 'image/png'		: $resimage = imagecreatefrompng($File); break;
				case 'image/x-ms-bmp'	: $resimage = imagecreatefromwbmp($File); break;
				default					: $resimage = imagecreatefromjpeg($File); break;
			}
			
			if ($gab[0]>=$w&&$gab[0]>$gab[1])
			{
				$h = ($gab[1]/($gab[0]/$w));
				$smallimage =  imagecreatetruecolor($max_width, $max_height);
				imagefill($smallimage, 0, 0, 0xffffff);
				imagecopyresampled($smallimage, $resimage, ($max_width-$w)/2, ($max_height-$h)/2, 0, 0, $w, $h, $gab[0], $gab[1]);
				imagejpeg($smallimage,$File_new, 100);
			}
			elseif($gab[1]>=$w&&$gab[0]<$gab[1])
			{
				$w = ($gab[0]/($gab[1]/$h));
				$smallimage =  imagecreatetruecolor($max_width, $max_height);
				imagefill($smallimage, 0, 0, 0xffffff);
				imagecopyresampled($smallimage, $resimage, ($max_width-$w)/2, ($max_height-$h)/2, 0, 0, $w, $h, $gab[0], $gab[1]);
				imagejpeg($smallimage,$File_new, 100);
			}
			elseif($gab[1]>=$w)
			{
				$h = ($gab[1]/($gab[0]/$w));
				$smallimage =  imagecreatetruecolor($max_width, $max_height);
				imagefill($smallimage, 0, 0, 0xffffff);
				imagecopyresampled($smallimage, $resimage, ($max_width-$w)/2, ($max_height-$h)/2, 0, 0, $w, $h, $gab[0], $gab[1]);
				imagejpeg($smallimage, $File_new, 100);
			}
			else{
				$smallimage =  imagecreatetruecolor($max_width, $max_height);
				imagefill($smallimage, 0, 0, 0xffffff);
				imagecopyresampled($smallimage, $resimage, ($max_width-$w)/2, ($max_height-$h)/2, 0, 0, $gab[0], $gab[1], $gab[0], $gab[1]);
				imagejpeg($smallimage, $File_new, 100);
			}
			//$this->Copyright($path);//echo $File;
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