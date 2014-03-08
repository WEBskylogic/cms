<?php
class Images{

	static function resizeImage($file, $src_big, $src_small, $width, $height, $maxWidth=max_width_image, $copyright=0)
	{
		$checkImage=Images::checkImage($file);
		if($checkImage!='')return messageAdmin($checkImage, 'error');
		
		//Increase the allowed memory size for the bigger images
		try{
			$image = new imageResizer($file);
			$gab =  getimagesize($file); // Берем размеры исх. картинки.
			if ($gab[0]>=$maxWidth)
			{
				$w = $maxWidth; //Получаем будущее значение ширины уменьшенной копии.
				$h = ($gab[1]/($gab[0]/$w)); //Аналогично с высотой.
			}
			else{
				$w=$gab[0];
				$h=$gab[1];
			}
			
			if($src_big!="")
			{
				// Make a smaller version of the original
				$image->resize($w, $h,$gab[0],$gab[1]);
				$image->save($src_big, JPG);
			}
			// Make a thumbnail of the original
			$image->resize($width, $height,$gab[0],$gab[1]);
			$image->save($src_small, JPG);
		 
			// Retrieve the thumbnail as a string as BMP and show it in the browser as JPG
			//$string = $image->getString(BMP);
		}
		catch(Exception $e){
			// Catch and display any exceptional behaviour
			//print $e->getMessage();
			exit();
		}
		// Destroy object (executes the destructor) and more importantly, frees up memory
		$image = null;
		if($copyright==1)copyright($src_big);
	}
	
	static function set_watermark($watermark, $path, $action)
	{
		$watermark = json_decode($watermark, true);
		foreach($watermark['modules'] as $row)
		{
			if($row==$action)
			{
				$done=true;
				break;	
			}
		}
		if(isset($done))
		{
			if($watermark['active']==1&&($watermark['type_image']==1||$watermark['type_image']==2))
			{
				$path2=str_replace('_s', '', $path);
				if(file_exists($path2))Images::copyright(str_replace('_s', '', $path), $watermark);
				
				$path2=str_replace('_s', '_m', $path);
				if(file_exists($path2))Images::copyright(str_replace('_s', '_m', $path), $watermark);
			}
			if($watermark['active']==1&&($watermark['type_image']==0||$watermark['type_image']==2))
			{
				if(file_exists($path))Images::copyright($path, $watermark);
			}
		}
	}
	
	static function Copyright($file, $data)
	{		
		$image_size=getimagesize($file);
		$res = Images::get_image_mime($file, $image_size['mime']);
		$logo=imagecreatefrompng($data['image']);
		$logo_size=getimagesize($data['image']);

		$srcX=0;
		$srcY=0;
		$dstW=imagesx($logo);
		$dstH=imagesy($logo);
		
		$srcW=imagesx($logo);
		$srcH=imagesy($logo);
		
		if($dstW>$image_size[0])
		{
			$dstH = ($dstH/($dstW/$image_size[0]));
			$dstW = $image_size[0];
			$logo_size[0]=$image_size[0];
			$logo_size[1]=$dstH;
		}
		elseif($dstH>$image_size[1])
		{
			$dstW = ($dstW/($dstH/$image_size[1]));
			$dstH = $image_size[1];
			$logo_size[0]=$dstW;
			$logo_size[1]=$image_size[1];
		}
		
		switch($data['position'])
		{
			case 'top_center': 
				$dstX=imagesx($res)-($logo_size[0]/2)-($image_size[0]/2);
				$dstY=0;
			break;
			case 'top_right': 
				$dstX=imagesx($res)-$logo_size[0];
				$dstY=0;
			break;
			case 'center_left': 
				$dstX=0;
				$dstY=imagesy($res)-($logo_size[1]/2)-($image_size[1]/2);
			break;
			case 'center_center': 
				$dstX=imagesx($res)-($logo_size[0]/2)-($image_size[0]/2);
				$dstY=imagesy($res)-($logo_size[1]/2)-($image_size[1]/2);
			break;
			case 'center_right': 
				$dstX=imagesx($res)-$logo_size[0];
				$dstY=imagesy($res)-($logo_size[1]/2)-($image_size[1]/2);
			break;
			
			case 'bot_left': 
				$dstX=0;
				$dstY=imagesy($res)-$logo_size[1];
			break;
			case 'bot_center': 
				$dstX=imagesx($res)-($logo_size[0]/2)-($image_size[0]/2);
				$dstY=imagesy($res)-$logo_size[1];
			break;
			case 'bot_right': 
				$dstX=imagesx($res)-$logo_size[0];
				$dstY=imagesy($res)-$logo_size[1];
			break;
			default:
				$dstX=0;
				$dstY=0;
			break;
		}
		imagecopyresized($res, $logo, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);
		imagejpeg($res, $file, 100);       
	}
	
	static function substrate($max_width, $max_height, $File, $File_new, $del_tmp_file=true)
	{	
		if(file_exists($File))
		{
			$border_size=250;
			$background_color='0xffffff';
			$checkImage=Images::checkImage($File);
			if($checkImage!='')
			{
				unlink($File);
				return $checkImage;
			}
			$File_big = $File_new.'.jpg';
			$File_new.='_b.jpg';
			
			$gab =  getimagesize($File); //Берем размеры исх. картинки.
			
			/*Save big image*/
			if($gab[0]>$gab[1]&&$gab[0]>max_width_image_substrate)
			{
				$w = max_width_image_substrate;
				$h = ($gab[1]/($gab[0]/$w));
				$image = new imageResizer($File);
				$image->resize($w, $h, $gab[0],$gab[1]);
				$image->save($File_big, JPG);
			}
			elseif($gab[0]<$gab[1]&&$gab[1]>max_height_image_substrate)
			{
				$h = max_height_image_substrate;
				$w = ($gab[0]/($gab[1]/$h));
				$image = new imageResizer($File);
				$image->resize($w, $h, $gab[0],$gab[1]);
				$image->save($File_big, JPG);
			}
			else{
				$h = max_height_image_substrate;
				$w = max_width_image_substrate;
				$image = new imageResizer($File);
				$image->resize($gab[0], $gab[1], $gab[0], $gab[1]);
				$image->save($File_big, JPG);
			}
			
			$gab =  getimagesize($File_big);
			$max_width = $gab[0]+$border_size;
			$max_height = $gab[1]+$border_size;
			////////////////
			
			$gab = getimagesize($File);
			$w = $max_width-$border_size;
			$h = $max_height-$border_size;
			if(max_size_image>4)ini_set('memory_limit', '256M');
			
			$resimage = Images::get_image_mime($File, $gab['mime']);
			if($del_tmp_file)unlink($File);//////Delete tmp image _b
			
			/*Save small image*/
			if ($gab[0]>=$w&&$gab[0]>$gab[1])
			{
				$h = ($gab[1]/($gab[0]/$w));
				$smallimage = imagecreatetruecolor($max_width, $max_height);
				imagefill($smallimage, 0, 0, $background_color);
				imagecopyresampled($smallimage, $resimage, ($max_width-$w)/2, ($max_height-$h)/2, 0, 0, $w, $h, $gab[0], $gab[1]);
				imagejpeg($smallimage,$File_new, 100);
			}
			elseif($gab[1]>=$w&&$gab[0]<$gab[1])
			{
				$w = ($gab[0]/($gab[1]/$h));
				$smallimage =  imagecreatetruecolor($max_width, $max_height);
				imagefill($smallimage, 0, 0, $background_color);
				imagecopyresampled($smallimage, $resimage, ($max_width-$w)/2, ($max_height-$h)/2, 0, 0, $w, $h, $gab[0], $gab[1]);
				imagejpeg($smallimage,$File_new, 100);
			}
			elseif($gab[1]>=$w)
			{
				$h = ($gab[1]/($gab[0]/$w));
				$smallimage =  imagecreatetruecolor($max_width, $max_height);
				imagefill($smallimage, 0, 0, $background_color);
				imagecopyresampled($smallimage, $resimage, ($max_width-$w)/2, ($max_height-$h)/2, 0, 0, $w, $h, $gab[0], $gab[1]);
				imagejpeg($smallimage, $File_new, 100);
			}
			else{
				$smallimage = imagecreatetruecolor($max_width, $max_height);
				imagefill($smallimage, 0, 0, $background_color);
				imagecopyresampled($smallimage, $resimage, ($max_width-$w)/2, ($max_height-$h)/2, 0, 0, $gab[0], $gab[1], $gab[0], $gab[1]);
				imagejpeg($smallimage, $File_new, 100);
			}
			imagedestroy($smallimage);
		}
		return '';
		//$this->Copyright($path);//echo $File;
	}
	
	static function checkImage($file)
	{
		$fileTypes = explode(',', ext_image); //File extensions
		
		if(is_array($file))
		{
			$fileParts = pathinfo($file['name']);
			$file_tmp = $file['tmp_name'];
			$file_size = $file['size'];
		}
		else{
			$fileParts = pathinfo($file);
			$file_tmp = $file;
			$file_size=filesize($file_tmp);
		}
		if(!isset($fileParts['extension']))$fileParts['extension']='jpg';
		$extension=$fileParts['extension'];
		$name=$file['name'];
		
		$size=round($file_size / 1048576, 3);
		$message='';
		
		if(!in_array($extension, $fileTypes))
		{
			$message='Invalid image type';
		}
		elseif($size>max_size_image)
		{
			$message='Max size '.max_size_image.' MB';
		}
		else{
			if(!getimagesize($file_tmp))$message='Invalid image';
		}
		return $message;
	}
	
	static function scaleImage($source_image_path, $maxWidth, $maxHeight, $thumbnail_image_path)
	{
		$info   = getimagesize('./'.$source_image_path);
		$width = $info[0];
		$height = $info[1];
	
		// If one dimension is right then nothing to do
		/*if($width == $maxWidth || $height == $maxHeight)
			return($source_image_path);
		*/
		// Calculate new size
		if(($maxWidth - $width) > ($maxHeight - $height)) // use height
		{
			$s = $maxHeight / $height;
			$nw = round($width * $s);
			$nh = round($height * $s);
		}
		else{ // Use width
			$s = $maxWidth / $width;
			$nw = round($width * $s);
			$nh = round($height * $s);
		}
		$maxWidth=$nw;
		$maxHeight=$nh;
		
		$source = imagecreatefrompng('./'.$source_image_path);
		$image_resized = imagecreatetruecolor( $maxWidth, $maxHeight);
		imagealphablending($image_resized, false);
        $color  = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
        imagefill($image_resized, 0, 0, $color);
        imagesavealpha($image_resized, true);
		imagecopyresampled($image_resized, $source, 0, 0, 0, 0, $maxWidth, $maxHeight, $info[0], $info[1]);
		imagepng($image_resized, './'.$thumbnail_image_path);
	}
	
	function get_image_mime($file, $mime)
	{
		switch($mime)
		{
			case 'image/pjpeg'		: $res = imagecreatefromjpeg($file); break;
			case 'image/jpeg'		: $res = imagecreatefromjpeg($file); break;
			case 'image/gif'		: $res = imagecreatefromgif($file); break;
			case 'image/png'		: $res = imagecreatefrompng($file); break;
			case 'image/x-ms-bmp'	: $res = imagecreatefromwbmp($file); break;
			default					: $res = imagecreatefromjpeg($file); break;
		}	
		return $res;
	}
}
?>