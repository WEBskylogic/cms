<?php
define("JPG", 0);
define("GIF", 1);
define("PNG", 2);
define("BMP", 3);

define("JPG_QUALITY", 100);
define("PNG_QUALITY", 0);

class imageResizer
{
	private $filename;
	private $image;
	private $data;
	private $copy;
	
	function imageResizer($filename) {
		if(!is_file($filename))
			throw new Exception("File does not exist");
			
		$this->filename = $filename;
		$this->data = getimagesize($this->filename);
		
		switch($this->data['mime']) {
			case 'image/pjpeg'		:
			case 'image/jpeg'		: $this->image = imagecreatefromjpeg($this->filename); break;
			case 'image/gif'		: $this->image = imagecreatefromgif($this->filename); break;
			case 'image/png'		: $this->image = imagecreatefrompng($this->filename); break;
			case 'image/x-ms-bmp'	: $this->image = imagecreatefromwbmp($this->filename); break;
			default					: throw new Exception("File format is not supported"); break;
		}
	}
	
	// Makes a plain copy of the original
	public function duplicate() {
		if(!isset($this->image))
			throw new Exception("No image loaded");
		$this->copy = $this->image;
	}
	
	// Makes a resized copy of the original
	public function resize($wx, $hx, $gab1, $gab2) {
		$wm = 0; $hm = 0;
		if(!isset($this->image))
			throw new Exception("No image loaded");

		if($wx != $wm && $hx != $hm && $wm != 0 && $hm != 0)
			throw new Exception("Bad dimensions specified");
				
		$r = $this->data[0] / $this->data[1];
		$rx = $wx / $hx;
		
		if($wm == 0 || $hm == 0)
			$rm = $rx;
		else
			$rm = $wm / $hm;

		$dx=0; $dy=0; $sx=0; $sy=0; $dw=0; $dh=0; $sw=0; $sh=0; $w=0; $h=0;

		if($r > $rx && $r > $rm) {
			$w = $wx;
			$h = $hx;
			$sw = $this->data[1] * $rx;
			$sh = $this->data[1];
			$sx = ($this->data[0] - $sw) / 2;
			$dw = $wx;
			$dh = $hx;
		} elseif($r < $rm && $r < $rx) {
			$w = $wx;
			$h = $hx;
			$sh = $this->data[0] / $rx;
			$sy = ($this->data[1] - $sh) / 2;
			$sw = $this->data[0];
			$dw = $wx;
			$dh = $hx;
		} elseif($r >= $rx && $r <= $rm) {
			$w = $wx;
			$h = $wx / $r;
			$dw = $wx;
			$dh = $wx / $r;
			$sw = $this->data[0];
			$sh = $this->data[1];
		} elseif($r <= $rx && $r >= $rm) {
			$w = $hx * $r;
			$h = $hx;
			$dw = $hx * $r;
			$dh = $hx;
			$sw = $this->data[0];
			$sh = $this->data[1];
		} else {
			throw new Exception("Can't resize the image");
		}
		if($gab1<$gab2)
		{
			$sx=0;
			$sy=0;
		}
		$this->copy = imagecreatetruecolor($w, $h);
		imagecopyresampled($this->copy, $this->image, $dx, $dy, $sx, $sy, $dw, $dh, $sw, $sh);
		
		return true;
	}
	
	// Save copy to file. If no file name omitted it will overwrite the original
	public function save($filename = false, $type = JPG) {
		if(!isset($this->copy))
			throw new Exception("No copy to save");
			
		if(!$filename)
			$filename = $this->filename;
			
		switch($type) {
			case GIF	: imagegif($this->copy, $filename); return true; break;
			case PNG	: imagepng($this->copy, $filename, PNG_QUALITY); return true; break;
			case BMP	: imagewbmp($this->copy, $filename); return true; break;
			case JPG	:
			default		: imagejpeg($this->copy, $filename, JPG_QUALITY); return true; break;
		}
		throw new Exception("Save failed");
	}
	
	// Save copy to string and return it
	public function getString($type = JPG) {
		if(!isset($this->copy))
			throw new Exception("No copy to return");
		
		$contents = ob_get_contents();
		if ($contents !== false) ob_clean();
		else ob_start();
		
		$this->show($type);
		
		$data = ob_get_contents();
		if ($contents !== false) {
			ob_clean();
			echo $contents;
		}
		else ob_end_clean();
		return $data;
	}
	
	// Output copy to browser
	public function show($type = JPG) {
		if(!isset($this->copy))
			throw new Exception("No copy to show");
		
		switch($type) {
			case GIF	: imagegif($this->copy, null); return true; break;
			case PNG	: imagepng($this->copy, null, PNG_QUALITY); return true; break;
			case BMP	: imagewbmp($this->copy, null); return true; break;
			case JPG	:
			default		: imagejpeg($this->copy, null, JPG_QUALITY); return true; break;
		}
		throw new Exception("Show failed");
	}
	
	public function __destruct()
	{
		imagedestroy($this->image);
		imagedestroy($this->copy);
		$this->filename = null;
		$this->data = null;
	}
}
?>