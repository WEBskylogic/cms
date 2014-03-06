<?php
	class View
	{
		private $registry;
		public $tplDir = null;
		public $resources = null;
		public $vars = null;

		function __construct ($registry, $vars = array())
		{
			$this->vars = $vars;
			$this->registry = $registry;
			$tplSettings = $this->registry['tpl_settings'];//echo var_dump($tplSettings)."<br />";
			$this->resources = array();
			$this->resources['image'] = $tplSettings['images'];
			$this->resources['flash'] = $tplSettings['flash'];
			$this->resources['styles'] = $tplSettings['styles'];
			$this->resources['scripts'] = $tplSettings['jscripts'];
			$this->tplDir = $tplSettings['source'];
		}
		
		public function Render($includeFile, $vars = array())
		{
			//$vars['module']="";
			//echo $vars['subsystem'].' '.$includeFile;
			if(isset($vars['subsystem'])&&$vars['subsystem']!="")
			{
				$pathTpl = SUBSYSTEM.$vars['subsystem']."/".$this->tplDir.$includeFile;//echo $pathTpl."<br />";
				if (!file_exists($pathTpl))
				{
					Log::echoLog('Could not found template \'' . $pathTpl . '\' !!!');
					return;
				}
			}
			elseif(isset($vars['module'])&&$vars['module']!="")
			{
				$pathTpl = MODULES.$vars['module']."/".$this->tplDir.$includeFile;//echo $pathTpl."<br />";
				if (!file_exists($pathTpl))
				{
					Log::echoLog('Could not found template \'' . $pathTpl . '\' !!!');
					return;
				}
			}
			elseif(isset($this->registry['admin']))
			{
				$pathTpl = $this->tplDir."admin/".$includeFile;//echo $pathTpl."<br />";
				if(!file_exists($pathTpl))
				{
					$vars['action'] = $this->registry['admin'];
					$pathTpl = MODULES.$this->registry['admin']."/admin"."/".$this->tplDir.$includeFile;//echo $pathTpl."<br />";
					if(!file_exists($pathTpl))
					{
						$pathTpl = SUBSYSTEM.$this->registry['admin']."/".$this->tplDir.$includeFile;//echo $pathTpl."<br />";
						if(!file_exists($pathTpl))
						{
							Log::echoLog('Could not found template \'' . $pathTpl . '\' !!!');
							return;
						}
					}
				}
			}
			else{
				$theme = $this->registry['theme'];
				$pathTpl = $this->tplDir.$theme."/".$includeFile;//echo $pathTpl."<br />";
				if(!file_exists($pathTpl))
				{
					Log::echoLog('Could not found template \'' . $pathTpl . '\' !!!');
					return;
				}
			}
            ob_start();
			
			//echo $pathTpl;
       		if(isset($theme))require SITE_PATH.'/'.$pathTpl;
			else require $pathTpl;

			$contents = ob_get_contents();
        	ob_end_clean();
	       	return $contents;
		}
		
		public function LoadProdImage($id)
		{
  			$pathOrig = $this->resources['prodimage'] . $id . '/main.jpg';
  			$cacheID = md5($pathOrig);
			$cache = new Cache ();
   			if (!$path = $cache->LoadImage($cacheID)){
                 $path = $cache->SaveImage($pathOrig, $cacheID);
   			}
			return $path;
		}

		public function LoadProdImageFut($id, $type, $color, $size) {
            $pathOrig = $this->resources['prodimage'] . $id . '/'.$type.'/'.$color.'_'.$size.'.jpg';
  			$cacheID = md5($pathOrig);
			$cache = new Cache ();
   			if (!$path = $cache->LoadImage($cacheID)){
                 $path = $cache->SaveImage ($pathOrig,$cacheID);
   			}
			return $path;
		}

        public function LoadImgage($fileName)
		{
			$pathToResource = $this->resources['image'] . $fileName;
			if (!file_exists($pathToResource)) {
				Log::echoLog ('Could not found resource \'' . $pathToResource . '\' (resource type \'' . $id_resource . '\')');
				return;
			}
			return '/' . $pathToResource;
        }

		public function LoadResource($id_resource, $fileName, $admin='')
		{
			if($admin=='')
			{
				$path1 = $this->tplDir.$this->registry['theme']."/".$this->resources[$id_resource].$fileName;
				$path2 = $this->resources[$id_resource].$fileName;
				if(file_exists($path1))
				{
					return $this->typeResource($id_resource, $path1);
				}
				elseif(file_exists($path2))
				{
					return $this->typeResource($id_resource, $path2);
				}
				else{
					Log::echoLog ('Could not found resource \'' . $path1 . '\' (resource type \'' . $id_resource . '\')');
					return false;
				}
			}
			else{
				$path1 = $this->tplDir."admin/".$this->resources[$id_resource].$fileName;
				$path2 = $this->resources[$id_resource].$fileName;
				if(file_exists($path1))
				{
					return $this->typeResource($id_resource, $path1);
				}
				elseif(file_exists($path2))
				{
					return $this->typeResource($id_resource, $path2);
				}
				else{
					Log::echoLog ('Could not found resource \'' . $path1 . '\' (resource type \'' . $id_resource . '\')');
					return false;
				}
			}
		}
		
		public function typeResource($type, $path)
		{
			if($type=="styles")return '<link rel="stylesheet" type="text/css" href="/'.$path.'" />';
			elseif($type=="scripts")return '<script type="text/javascript" src="/'.$path.'"></script>';
			elseif($type=="image")return '<link rel="shortcut icon"  href="/'.$path.'" />';
			else return'/'.$path;
		}
		
		public function Load($array, $type, $admin='')
		{
			$data='';
			if(count($array)>0)
			{
				$data = "\n";
				if($type=="styles")
				{
					for ($i=0;$i<count($array);$i++)
					{
						$data.= $this->LoadResource('styles', $array[$i], $admin)."\n";
					} 
				}
				else{
					for ($i=0;$i<count($array);$i++)
					{
						$data.= $this->LoadResource('scripts', $array[$i], $admin)."\n";		
					}
				}
			}
			return $data;
		}
	}
?>