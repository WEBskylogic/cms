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
		
		public function Render($includeFile, $vars = '')
		{
			//$vars['module']="";
			if(isset($vars['module'])&&$vars['module']!="")
			{
				$pathTpl = MODULES.$vars['module']."/".$this->tplDir.$includeFile;//echo $pathTpl."<br />";
				if (!file_exists($pathTpl))
				{
					LOG::echoLog('Could not found template \'' . $pathTpl . '\' !!!');
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
						LOG::echoLog('Could not found template \'' . $pathTpl . '\' !!!');
						return;
					}
				}
			}
			else{
				$theme = $this->registry['theme'];
				$pathTpl = $this->tplDir.$theme."/".$includeFile;//echo $pathTpl."<br />";
				if(!file_exists($pathTpl))
				{
					LOG::echoLog('Could not found template \'' . $pathTpl . '\' !!!');
					return;
				}
			}
            ob_start();
			//echo $pathTpl;
			 $pos = strpos($pathTpl, "home");
       		 if($pos === false)require SITE_PATH.'/'.$pathTpl;
			 else require $pathTpl;

			$contents = ob_get_contents();
        	ob_end_clean();
	       	return $contents;
		}
		
		public function LoadProdImage($id) {
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
				LOG::echoLog ('Could not found resource \'' . $pathToResource . '\' (resource type \'' . $id_resource . '\')');
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
					LOG::echoLog ('Could not found resource \'' . $path1 . '\' (resource type \'' . $id_resource . '\')');
					return false;
				}
			}
			else{
				$path1 = $this->tplDir."admin/".$this->resources[$id_resource].$fileName;
				if(file_exists($path1))
				{
					return $this->typeResource($id_resource, $path1);
				}
				else{
					LOG::echoLog ('Could not found resource \'' . $path1 . '\' (resource type \'' . $id_resource . '\')');
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

		function date_view($str, $format="dd-mm-yy")
		{
			$dd = substr($str, 8, 2);

			$mm = substr($str, 5, 2);
			$MM = $this->getMonth($mm);
			$YY = substr($str, 0, 4);
			$yy = substr($str, 2, 2);
			$hh = substr($str, 11, 2);
			$ii = substr($str, 14, 2);
			$ss = substr($str, 17, 2);
            $DD = $this->getDay(mktime(0, 0, 0, $mm, $dd, $YY));
			$replace = array('YY'=>$YY, 'yy'=>$yy, 'mm'=>$mm, 'dd'=>$dd, 'DD'=>$DD, 'hh'=>$hh, 'ii'=>$ii, 'ss'=>$ss, 'MM'=>$MM);
			$str = strtr($format, $replace);
			return $str;
		}
		
		public function getMonth($month)
		{
			switch($month)
			{
				case "01":$month='Январь';break;
				case "02":$month='Февраль'; break;
				case "03":$month='Март';break;
				case "04":$month='Апрель';break;
				case "05":$month='Май'; break;
				case "06":$month='Июнь';break;
				case "07":$month='Июль';break;
				case "08":$month='Август';break;
				case "09":$month='Сентябрь';break;
				case "10";$month='Октябрь'; break;
				case "11":$month='Ноябрь';break;
				case "12": $month='Декабрь';break;
			}
			return $month;	
		}

        public function getDay($day)
        {
            $day=getdate($day);
            switch($day['wday'])
            {
                case "1":$day='Понидельник';break;
                case "2":$day='Вторник'; break;
                case "3":$day='Среда';break;
                case "4":$day='Четверг';break;
                case "5":$day='Пятница'; break;
                case "6":$day='Суббота';break;
                case "0":$day='Воскресенье';break;
            }
            return $day;
        }

        public function get_page_link($page, $cur_page, $var, $text='')
        {
            if (!$text)$text = $page;
            if ($page!=$cur_page)
            {
                $path=$_SERVER['REQUEST_URI'];
                //$reg = '/((\/|^)'.$var.'\/)[^&#]*/';
                //$url = ( preg_match( $reg, $path ) ? preg_replace($reg, '${1}', $path) : ( $path ? $path.'/' : '' ).$var.'/');
                $reg = '/((\/|^)'.$var.'\/)[^\/#]*/';
                $url = ( preg_match( $reg, $path ) ? preg_replace($reg, '${1}'.$page, $path) : ($path? $path.'/' : '' ).$var.'/'.$page);
                //echo "<br />{$url2}<br /><br />";
                $url=str_replace("//", "/", $url);
                return '<a href="'.$url.'">'.$text.'</a>';
            }
            return '<span>'.$text.'</span>';
        }
        public function getUrl2($var)
        {
            $reg = '/'.$var.'\/[a-z0-9]+/';
            $url = preg_replace($reg, '', $_SERVER['REQUEST_URI']);
            $url=str_replace("//", "/", $url);
            return $url;
        }
        public function getUrl($var, $page='')
        {
            $path=$_SERVER['REQUEST_URI'];
            //$reg = '/((\/|^)'.$var.'\/)[^&#]*/';
            //$url = ( preg_match( $reg, $path ) ? preg_replace($reg, '${1}', $path) : ( $path ? $path.'/' : '' ).$var.'/');
            $reg = '/((\/|^)'.$var.'\/)[^\/#]*/';
            $url = ( preg_match( $reg, $path ) ? preg_replace($reg, '${1}'.$page, $path) : ($path? $path.'/' : '' ).$var.'/'.$page);
            //echo "<br />{$url2}<br /><br />";
            $url=str_replace("//", "/", $url);
            return $url;
        }
		
		public function showFilemanager($elm=1)
		{
			return'<link rel="stylesheet" href="/js/editors/elfinder/css/smoothness/jquery-ui-1.8.13.custom.css" type="text/css" media="screen" charset="utf-8" /> 
					<link rel="stylesheet" href="/js/editors/elfinder/css/elfinder.css" type="text/css" media="screen" charset="utf-8" /> 
			
					<script src="/js/editors/elfinder/js/jquery-1.6.1.min.js" type="text/javascript" charset="utf-8"></script>
					<script src="/js/editors/style/jquery.backgroundPosition.js" type="text/javascript" charset="utf-8"></script>
			
					<script src="/js/editors/elfinder/js/jquery-ui-1.8.13.custom.min.js" type="text/javascript" charset="utf-8"></script>
					<script src="/js/editors/elfinder/js/elfinder.min.js" type="text/javascript" charset="utf-8"></script> 
					<script src="/js/editors/elfinder/js/i18n/elfinder.ru.js" type="text/javascript" charset="utf-8"></script>
					<script type="text/javascript" charset="utf-8">
						$().ready(function() {
		
							$(\'#elRTE a\').hover(
								function () {
									$(\'#elRTE a\').animate({
										\'background-position\' : \'0 -45px\'
									}, 300);
								},
								function () {
									$(\'#elRTE a\').delay(400).animate({
										\'background-position\' : \'0 0\'
									}, 300);
								}
							);
							
							$(\'#elRTE a\').delay(800).animate({\'background-position\' : \'0 0\'}, 300);
		
							var f = $(\'#finder\').elfinder({
								url : \'http://'.$_SERVER['HTTP_HOST'].'/js/editors/elfinder/connectors/php/connector.php\',
								lang : \'ru\',
								docked : true,
								height: 490
							})
						})
					</script> 
					<div id="finder">finder</div>';	
		}
		public function showEditor($name, $body, $elm=1)
		{
			if($this->registry['editor']=='tinyMCE')
			return'<script type="text/javascript" src="/js/editors/tinymce/tiny_mce.js"></script>
                    <link rel="stylesheet" type="text/css" media="screen" href="/js/editors/elfinder/css/elfinder.css">
                            <script type="text/javascript" src="/js/editors/elfinder/js/elfinder.min.js"></script>
                            <script type="text/javascript" src="/js/editors/elfinder/js/i18n/elfinder.ru.js"></script>
                            <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"></script>
                            <link rel="stylesheet" type="text/css" media="screen" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/themes/smoothness/jquery-ui.css" />
					<script type="text/javascript">
						tinyMCE.init({
							// General options
							force_br_newlines : true,
							language : "ru",
							mode : "exact",
							convert_urls : false,
							elements : "elm1, elm2, elm3, elm4, elm5, elm6, elm7, elm8, elm9, elm10, elm11, elm12",
							
							skin : "o2k7",
							theme : "advanced",
							
							plugins : "safari,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
					
							// Theme options
							theme_advanced_buttons1 : "save,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect,hr,|,sub,sup,|,charmap,emotions,fullscreen",
							theme_advanced_buttons2 : "pastetext,pasteword,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,code,|,forecolor,backcolor,|,tablecontrols,image",
							theme_advanced_buttons3 : "",
							theme_advanced_buttons4 : "",
							theme_advanced_toolbar_location : "top",
							theme_advanced_toolbar_align : "left",
							theme_advanced_statusbar_location : "bottom",
							theme_advanced_resizing : true,

                            file_browser_callback: function(field_name, url, type, win) {
                                 aFieldName = field_name, aWin = win;

                                 if($("#elfinder").length == 0) {
                                     $("body").append($("<div/>").attr("id", "elfinder"));
                                     $("#elfinder").elfinder({
                                         url : "/js/editors/elfinder/connectors/php/connector.php",
                                         lang: "ru",
                                         dialog : { width: 900, modal: true, title: "Файл менеджер", zIndex: 400001 }, // open in dialog window
                                         editorCallback: function(url) {
                                             aWin.document.forms[0].elements[aFieldName].value = url;
                                         },
                                         closeOnEditorCallback: true
                                     });
                                 } else {
                                         $("#elfinder").elfinder("open");
                                     }
                            },

							content_css : "/tpl/'.$this->registry['theme'].'/css/style_editors.css",
							// Replace values for the template plugin
							template_replace_values : {
								username : "Some User",
								staffid : "991234"
							}
						});
					</script>
				<textarea name="'.$name.'" id="elm'.$elm.'" rows="20" cols="94">'.$body.'</textarea>

				';

			elseif($this->registry['editor']=='elfinder')
			return'
					<link rel="stylesheet" href="/js/editors/elrte/css/smoothness/jquery-ui-1.8.7.custom.css" type="text/css" media="screen" charset="utf-8">
					<link rel="stylesheet" href="/js/editors/elrte/css/elrte.min.css"                         type="text/css" media="screen" charset="utf-8">

					<script src="/js/editors/elrte/js/jquery-ui-1.8.7.custom.min.js" type="text/javascript" charset="utf-8"></script>
					<script src="/js/editors/elrte/js/elrte.min.js"                  type="text/javascript" charset="utf-8"></script>
					<script src="/js/editors/elrte/js/i18n/elrte.ru.js"              type="text/javascript" charset="utf-8"></script>
					<script type="text/javascript" charset="utf-8">
				      $().ready(function() {
				          var opts = {
				              lang         : \'ru\',   // set your language
				              styleWithCSS : false,
				              height       : 400,
				              toolbar      : \'maxi\'
				          };
				          // create editor
				         $(\'#our-element\').elrte(opts);
				 
				         // or this way
				         // var editor = new elRTE(document.getElementById(\'our-element\'), opts);
				     });
				 </script>
				<textarea name="'.$name.'" id="our-element" rows="20" cols="94">'.$body.'</textarea>';
		}
	}
?>