<?php
class Dir{

	static function removeDir($dir)
	{
	   if($objs = glob($dir."/*"))
	   {
		   foreach($objs as $obj)
		   {
			   is_dir($obj) ? Dir::removeDir($obj) : unlink($obj);
		   }
	   }
	   if(is_dir($dir))rmdir($dir);
	}
	
	////Remove all dir with name $name_dir
	static function bfglob($path, $name_dir, $pattern = '*', $flags = GLOB_NOSORT, $depth = 0)
	{
		$matches = array();
		$folders = array(rtrim($path, DIRECTORY_SEPARATOR));
		
		$i=0;
		while($folder = array_shift($folders))
		{
			$matches = array_merge($matches, glob($folder.DIRECTORY_SEPARATOR.$pattern, $flags));
			if($depth != 0)
			{
				$moreFolders = glob($folder.DIRECTORY_SEPARATOR.'*', GLOB_ONLYDIR);
				$depth   = ($depth < -1) ? -1: $depth + count($moreFolders) - 2;
				$folders = array_merge($folders, $moreFolders);
				
				//echo $moreFolders[0].'<br />';
				$i++;
			}
		}
		
		//var_info($matches);
		for($i=0; $i<=count($matches) - 1; $i++)
		{
			if(strpos($matches[$i], $name_dir)!==false)
			{
				removeDir($matches[$i]);
				echo $matches[$i].'<br />';
			}
		}
		return $matches;
	}
	
	static function get_directory_list($path)
    {
		$list_dir=array();
		$dir = opendir($path);
		while($file_name = readdir($dir))
		{
			clearstatcache();
            // echo '',$file_name;
            if(is_dir($path.$file_name)&&$file_name!='.'&&$file_name!='..') {array_push($list_dir, $file_name); }
            // if (is_file($path.$file_name)) { echo '',$file_name; }
            //echo ''.dirname($file_name).' *** '.basename($file_name);
        }
        //var_info($list_dir);
        return $list_dir;
    }
	
	static function get_file_list($dir)
    {
		$list_file=array();
		if(is_dir($dir))
		{
		   if($dh = opendir($dir))
		   {
			   while (($file = readdir($dh)) !== false)
			   {
				   //print "Файл: $file : тип: " . filetype($dir . $file) . "\n";
				   if($file!='.'&&$file!='..'&&!is_dir($dir.$file))
				   {
					   $time = date("Y-m-d H:i:s", filemtime($dir . $file));
					   array_push($list_file, array('name'=>$file, 'time'=>$time, 'path'=>$dir . $file));
				   }
			   }
			   closedir($dh);
		   }
		}
		return $list_file;
	}
	
    static function createDir($id)
    {
		if($id!='')
		{
			$dir=array();
			$dir['0']="files/product/".substr($id,-1).'/'.$id.'/';
			$dir['1']="files/product/".substr($id,-1).'/'.$id.'/more/';
			if(!is_dir($dir['1']))mkdir($dir['1'], 0755, true);
			return $dir;
		}
    }
}
?>