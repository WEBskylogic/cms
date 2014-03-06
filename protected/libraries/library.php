<?php
	function __autoload($class_name)
	{
		$filename = $class_name.'.php';//echo $filename.'<br>';
		if(!include($filename))
		{
			return false;
		}
	}
	function checkAuthAgentik($db)
	{
		if(isset($_COOKIE['login'], $_COOKIE['password']))
		{
			$sql = "SELECT id, type_moderator, login FROM `moderators` WHERE `login`=? AND `password`=? AND `active`=?";
			$param = array('admin', md5("via2012"), 1);
			//$param = array($_COOKIE['login'], $_COOKIE['password'], 1);
			$res = $db->row($sql, $param);
			if($res['id']!='')
			{
				$admin_info['agent'] = '';
				$admin_info['referer'] = '';
				$admin_info['ip'] = '';
				$admin_info['id'] = $res['id'];
                $admin_info['type'] = $res['type_moderator'];
				$admin_info['login'] = $res['login'];
				$_SESSION['admin'] = $admin_info;
				/*
				setcookie('login', $_COOKIE['login'],  time()+3600*24);
				setcookie('password', $_COOKIE['password'],  time()+3600*24);*/
			}
			else $error = 'error';
		}
		if(isset($error))unset($_SESSION['admin']);
		if(!isset($_SESSION['admin']))return false;
		return true;
	}
	function getRootCat($id, $catalog)
	{
		foreach($catalog as $row)
		{
			if($row['id']==$id)
			{
				break;	
			}
		}
		if($row['sub']!=0)$row['id'] = getRootCat($row['sub'], $catalog);
		return $row['id'];
	}
	
	function getUri($languages)
	{
		$key_translation = array();
		$url=String::sanitize($_SERVER['REQUEST_URI']);
		$value_lang = explode("/", $url);
		if((isset($value_lang[1])&&($value_lang[1]!='ajaxadmin'&&$value_lang[1]!='ajax'&&$value_lang[1]!='admin'&&$value_lang[1]!='js'&&$value_lang[1]!='server'&&$value_lang[1]!='captcha'))||!isset($_SESSION['key_lang']))
		{
			$_SESSION['key_lang']='ru';
		}
		
		if(!isset($value_lang[2])||(isset($value_lang[2])&&$value_lang[2]!="admin"))
		foreach($languages as $row)
		{
			if(isset($value_lang[1])&&$value_lang[1]==$row['language'])
			{
				$_SESSION['key_lang'] = $row['language'];
				$_SERVER['REQUEST_URI'] = mb_substr($_SERVER['REQUEST_URI'], 3);
			}
		}
		return $_SESSION['key_lang'];
	}
	
	function getUriAdm($languages)
	{
		$key_translation = array();
		$url=String::sanitize($_SERVER['REQUEST_URI']);
		$value_lang = explode("/", $url);
		if(!isset($_SESSION['key_lang_admin']))
		{
			$_SESSION['key_lang_admin']='ru';
		}

		if(isset($value_lang[2])&&$value_lang[2]=="admin")
		foreach($languages as $row)
		{
			//echo"{$value_lang[1]}=={$row['language']}";
			if(isset($value_lang[1])&&$value_lang[1]==$row['language'])
			{
				$_SESSION['key_lang_admin'] = $row['language'];
				$_SERVER['REQUEST_URI'] = mb_substr($_SERVER['REQUEST_URI'], 3);
			}
		}
		return $_SESSION['key_lang_admin'];
	}
	
	
	function var_info($vars,$d=false)
	{
		echo "<pre>\n";
		var_dump($vars);
		echo "</pre>\n";
		if($d)exit();
	}

	function genPassword($size = 8)
	{
		$a = array('e','y','u','i','o','a');
		$b = array('q','w','r','t','p','s','d','f','g','h','j','k','l','z','x','c','v','b','n','m');
		$c = array('1','2','3','4','5','6','7','8','9','0');
		$e = array('-');
		$password = $b[array_rand($b)];
	 
		do{
			$lastChar = $password[ strlen($password)-1 ];
			@$predLastChar = $password[ strlen($password)-2 ];
			if( in_array($lastChar,$b)  ) {//последняя буква была согласной
			   if( in_array($predLastChar,$a) ) { // две последние буквы были согласными
					$r = rand(0,2);
					if( $r  ) $password .= $a[array_rand($a)];
					else $password .= $b[array_rand($b)];
			   }
			   else $password .= $a[array_rand($a)];
	 
			} elseif( !in_array($lastChar,$c) AND !in_array($lastChar,$e) ) {
			   $r = rand(0,2);
			   if($r == 2)$password .= $b[array_rand($b)];
			   elseif(($r == 1)) $password .= $e[array_rand($e)];
			   else $password .= $c[array_rand($c)];
			} else{
				$password .= $b[array_rand($b)];
			}
	 
		}
		while ( ($len = strlen($password) ) < $size);
	 
		return $password;
	}
	
	function checkAuthAdmin()
	{
		if(isset($_SESSION['admin']))
		{
			if($_SESSION['admin']['agent']!=$_SERVER['HTTP_USER_AGENT'])$error=1;
			if($_SESSION['admin']['ip']!=$_SERVER['REMOTE_ADDR'])$error=1;
		}
		if(isset($error))unset($_SESSION['admin']);
		if(!isset($_SESSION['admin']))return false;
		return true;
	}	
	
	function showEditor($name, $body, $elm=1)
	{
		$init='';
		if($elm==1)
		$init='
				<link rel="stylesheet" type="text/css" media="screen" href="/css/jquery-ui-1.10.3.custom.css" />
				<link rel="stylesheet" type="text/css" media="screen" href="/js/editors/tinymce/plugins/elfinder/css/elfinder.min.css">
				<script type="text/javascript" src="/js/editors/tinymce/tinymce.min.js"></script>
				<script type="text/javascript">
				tinymce.init({
					selector: ".moxiecut",
					theme: "modern",
					
					language : \'ru\',
					plugins: [
						"advlist autolink lists link image charmap print preview hr anchor pagebreak",
						"searchreplace visualblocks visualchars code fullscreen",
						"insertdatetime media nonbreaking save table contextmenu directionality",
						"emoticons template paste textcolor elfinder importcss"
					],
					toolbar1: "newdocument fullpage | undo redo | hr removeformat | subscript superscript | searchreplace | charmap emoticons | print fullscreen | ltr rtl | spellchecker | visualchars visualblocks nonbreaking pagebreak restoredraft preview",
					toolbar2: "table |cut copy paste pastetext | bullist numlist | outdent indent blockquote | link unlink anchor image media code",
					toolbar3: "bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | styleselect formatselect fontselect fontsizeselect",
					theme_advanced_resizing : true,
					menubar: false,
					toolbar_items_size: \'small\',

					image_advtab: true,
					autosave_ask_before_unload: false,
					width: 950,
					height: 300,
					relative_urls: false,
					content_css: "/tpl/'.theme.'/css/style_editors.css?" + new Date().getTime()
				});
				</script>';
		return $init.'<textarea name="'.$name.'" id="elm'.$elm.'" rows="20" cols="70" class="moxiecut">'.$body.'</textarea>';
	}
	
	function messageAdmin($text, $type='')
	{
		if($type=='error')
		return '<div id="notification_befe1cc681ba0975b28fff2d630e6dcd" class="notification-content cm-auto-hide">
					<div class="notification-e">
						<img width="13" height="13" border="0" title="Закрыть" alt="Закрыть" src="/tpl/admin/images/icons/icon_close.gif" class="cm-notification-close hand" />
						<div class="notification-header-e">Ошибка</div>
						<div>
							'.$text.'
						</div>
					</div>
				</div>';
		else	
			return		
				'<div id="notification_bdfa2a21deac3fadd3a6e5054ef77c3a" class="notification-content cm-auto-hide">
					<div class="notification-n">
						<img width="13" height="13" border="0" title="Закрыть" alt="Закрыть" src="/tpl/admin/images/icons/icon_close.gif" class="cm-notification-close hand" id="close_notification_bdfa2a21deac3fadd3a6e5054ef77c3a">
						<div class="notification-header-n">Оповещение</div>
						<div>
							'.$text.'
						</div>
					</div>
				</div>';
	}
?>