<?php
	function __autoload($class_name)
	{
		$filename = $class_name.'.php'; //echo $filename.'<br>';
		if(!include($filename))
		{
			return false;
		}
	}
	
	function Select_masiv($mas, $name_select, $KEY=0,$style, $option_text='Выберете')
	{
		$text="<select $style class=form_option name=\"$name_select\">";
		
		if($option_text<>'')$text.="<option value='0'>$option_text</option>";
		
		foreach ($mas as $key_t=>$value_t) 
		{
			if($KEY==$key_t)$sel='selected="selected"';
			else $sel='';  				
			$text=$text.'<option value="'.$key_t.'" '.$sel.'>'.$value_t.'</option>';
		}
		$text=$text.'</select>';
		
		return $text;						   
	}
	
	function Select_masiv_multi($mas,$name_select,$ArKEY=0,$style, $option_text='Выберете')
	{ 
	
		$text='Чтобы выбрать несколько позиций зажмите клавишу CTRL и кликайте машкой ан разделы<br />
			<select name="'.$name_select.'"  '.$style.' size="10" multiple="multiple">';
		if($option_text<>'')$text.="<option value=0 >$option_text</option>";
		foreach ($mas as $key_t=>$value_t)
		{
			$sel='';
			if(isset($ArKEY) and is_array($ArKEY)===true and count($ArKEY)>0)
			{
				 if(in_array($key_t,array_keys($ArKEY)))
				 {
					 $sel='selected="selected"';
				 }	
			} 
			else if($key_t == $ArKEY)
			{
				$sel='selected="selected"';	
			}	 
						
			$text=$text.'<option value="'.$key_t.'" '.$sel.' >'.$value_t.'</option>';
		}
		$text=$text.'</select>';
		
		return $text;						   
	}

	function createTree_cat($array, $currentParent, $KEY, $currLevel = 0, $prevLevel = -1) 
	{
		
		$text='';
		foreach ($array as $categoryId => $category) 
		{
			if ($currentParent == $category['sub']) 
			{	
				$sub = $category['sub'];
				$level = $currLevel;

				if	($level == 0) 		$bull = '&nbsp;'; 
				elseif ($level == 1) 	$bull = '&nbsp;&bull;&nbsp;'; 
				elseif ($level == 2) 	$bull = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&ordm;&nbsp;';
				elseif ($level == 3) 	$bull = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-';
				elseif ($level > 3) 	$bull = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; 
				
				if ($sub == 0) $class = " class = form_ac "; 
				else $class = "";

				$select = "";	 

				if ($categoryId == $KEY)
				{
					$select = 'selected = "selected"';
				}	

				$text .= "<option value=\"$categoryId\" $select $class > $bull". htmlspecialchars(stripslashes($category['name'])) ."</option>";
 
				if ($currLevel > $prevLevel) 
				{ 
					$prevLevel = $currLevel; 
				}

				$currLevel++; 


				$text.=createTree_cat ($array, $categoryId, $KEY, $currLevel, $prevLevel);	
				$currLevel--;	 		 	
			}	
		}
		return $text;
	}

	
	function arrayKeys($array, $key = 'id')
	{
		$array_new = array();
		foreach ($array as $val)
		{
			$array_new[$val[$key]] = $val;
		}	
		
		return $array_new;
	}
	
	function viewPrice($price, $discount=0)
	{
		$return=array();
		$return['old_price']='';////Старая цена(str)
		$return['price']=''; ////Цена с форматированием(str)
		$return['cur_price']=0; ////Цена без форматирования(float)
		$return['base_price']=0; ////Цена в базовой валюте(float)
		
		if($_SESSION['currency'][1]['base']==1)
		{
			if($discount!=0)
			{
				$return['old_price']=formatPrice($price);
				$price = discount($discount, $price);
			}
			$return['price'] = formatPrice($price);
			$return['cur_price'] = round($price, 2);
			$return['base_price'] = $price;
		}
		else
		{
			$return['base_price'] = $price;
			$price = $price * (1/$_SESSION['currency'][1]['rate']);
			if($discount!=0)
			{
				$return['old_price']=formatPrice($price);
				$price = discount($discount, $price);	
				$return['base_price'] = discount($discount, $return['base_price']);
			}
			
			$return['cur_price'] = round($price, 2);
			$return['price'] = formatPrice($price);
		}
		
		return $return;
	}
	
	function formatPrice($price)
	{
		if ($_SESSION['lang'] === "ru")
		{
			if($_SESSION['currency'][1]['position']==1)$price = number_format($price, 0, ',', ' ').' '.' <font>'.$_SESSION['currency'][1]['icon'].'</font>';
			else $price = '<font>'.$_SESSION['currency'][1]['icon'].'</font> '.number_format($price, 2, ',', ' ');
			return $price;
		}
		else
		{
			if($_SESSION['currency'][1]['position']==1)$price = number_format($price, 0, ',', ' ').' '.' <font>'.$_SESSION['currency'][1]['icon2'].'</font>';
			else $price = '<font>'.$_SESSION['currency'][1]['icon2'].'</font> '.number_format($price, 2, ',', ' ');
			return $price;
		}
	}
	
	function discount($discount, $sum)
	{
		//$return=array();
		//$discount=$discount/100;
		//return round($sum-$sum*$discount, 2);
		return 0;
	}
		
	function getUri($languages)
	{
		$key_translation = array();
		$url=sanitize($_SERVER['REQUEST_URI']);
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
		$url=sanitize($_SERVER['REQUEST_URI']);
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
	
	function messageAdmin($text, $type='')
	{
		if($type=='error')
		return '<div id="notification_befe1cc681ba0975b28fff2d630e6dcd" class="notification-content">
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
	
	function errorMail($text)
	{
		$contact_mail=email_error;
		$url = $_SERVER['REQUEST_URI'];
		$refer = '';
		if(isset($_SERVER['HTTP_REFERER'])) $refer   = $_SERVER['HTTP_REFERER'];
		$ip_user = $_SERVER['REMOTE_ADDR'];
		$br_user = $_SERVER['HTTP_USER_AGENT'];
	
		$header = "From: $contact_mail" . "\r\n" .
				  "Reply-To: $contact_mail" . "\r\n" .
				  "Return-Path: $contact_mail" . "\r\n" .
				  "Content-type: text/plain; charset=UTF-8";
		
		$subject = 'Отладка ошибок в системе SkyCms:'.$_SERVER['SERVER_NAME'];
		$body = "SERVER_NAME:".$_SERVER['SERVER_NAME']." 
				 страница: $url \n
				 REFER страница: $refer \n
				 IP пользователя: $ip_user \n
				 браузер пользователя: $br_user \n
				 -----------------------------------------
				 $text";
		mail($contact_mail, $subject, $body, $header);
	}
	
	function translit($string, $flag=false)	
	{
		
		$replace=array(
			"'"=>"",
			"`"=>"",
			"а"=>"a","А"=>"a",
			"б"=>"b","Б"=>"b",
			"в"=>"v","В"=>"v",
			"г"=>"g","Г"=>"g",
			"д"=>"d","Д"=>"d",
			"е"=>"e","Е"=>"e",
			"ж"=>"zh","Ж"=>"zh",
			"з"=>"z","З"=>"z",
			"и"=>"i","И"=>"i",
			"й"=>"y","Й"=>"y",
			"к"=>"k","К"=>"k",
			"л"=>"l","Л"=>"l",
			"м"=>"m","М"=>"m",
			"н"=>"n","Н"=>"n",
			"о"=>"o","О"=>"o",
			"п"=>"p","П"=>"p",
			"р"=>"r","Р"=>"r",
			"с"=>"s","С"=>"s",
			"т"=>"t","Т"=>"t",
			"у"=>"u","У"=>"u",
			"ф"=>"f","Ф"=>"f",
			"х"=>"h","Х"=>"h",
			"ц"=>"c","Ц"=>"c",
			"ч"=>"ch","Ч"=>"ch",
			"ш"=>"sh","Ш"=>"sh",
			"щ"=>"sch","Щ"=>"sch",
			"ъ"=>"","Ъ"=>"",
			"ы"=>"y","Ы"=>"y",
			"ь"=>"","Ь"=>"",
			"э"=>"e","Э"=>"e",
			"ю"=>"yu","Ю"=>"yu",
			"я"=>"ya","Я"=>"ya",
			"і"=>"i","І"=>"i",
			"ї"=>"yi","Ї"=>"yi",
			"є"=>"e","Є"=>"e",
			"	"=>"-"," "=>"-"
		);
		$string=iconv("UTF-8","UTF-8//IGNORE",strtr($string,$replace));
		if(!$flag)$string = preg_replace("/[^a-zA-ZА-Яа-я0-9\s]/", "-", $string); 
		else $string = preg_replace("/[^a-zA-ZА-Яа-я0-9\s\/\:]/", "-", $string); 
		return mb_strtolower($string);
	}
		
	function var_info($vars,$d=false)
	{
		echo "<pre>\n";
		var_dump($vars);
		echo "</pre>\n";
		if($d)exit();
	}
	
	function sanitize($var,$reverse = false)
	{
		$sanMethod = array(
			array('&','&#038;'),
			array('"','&#034;'),
			array("'",'&#039;'),
			array('%','&#037;'),
			array('(','&#040;'),
			array(')','&#041;'),
			array('+','&#043;'),
			array('<','&lt;'),
			array('>','&gt;')
		);
		if (!is_array($var))
		{
			$charsCount = count($sanMethod);
			if($reverse) for($j=$charsCount;$j>0;$j--) $var = str_replace($sanMethod[$j][1], $sanMethod[$j][0], $var);
			else for($j=0;$j<$charsCount;$j++) $var = str_replace($sanMethod[$j][0], $sanMethod[$j][1], $var);
			return $var;
		}
		$varCount = count($var);
		$keys = array_keys($var);
		$i=0; while($i<$varCount)
		{
			if (is_array($var[$keys[$i]])) return sanitize($var[$keys[$i]]);
			else{
				$charsCount = count($sanMethod);
				if($reverse) for($j=$charsCount;$j>0;$j--) $var = str_replace($sanMethod[$j][1], $sanMethod[$j][0], $var);
				else for($j=0;$j<$charsCount;$j++) $var[$keys[$i]] = str_replace($sanMethod[$j][0], $sanMethod[$j][1], $var[$keys[$i]]);
			}
			$i++;
		}
		return $var;
	}
	
	function post_write($err,$header = false)
	{
		$_POST['err'] = $err;
		$_SESSION['_POST'] = $_POST;
		$_POST = array();
		if($header) {
			header("location:$header");
			exit();
		}
	}
	
	function post_read()
	{
		
		if(empty($_SESSION['_POST'])) return array();
		$post = $_SESSION['_POST'];
		unset($_SESSION['_POST']);
		return $post;
	}
	
	function resizeImage($file, $src_big, $src_small, $width, $height, $maxWidth=800, $copyright=0)
	{
		// Increase the allowed memory size for the bigger images
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
		catch(Exception $e) {
			// Catch and display any exceptional behaviour
			//print $e->getMessage();
			exit();
		}
		// Destroy object (executes the destructor) and more importantly, frees up memory
		$image = null;
		if($copyright==1)copyright($src_big);
	}
	
	function Copyright($file_str)
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

    function checkPass($pass, $pass2)
    {
        $text='';
        if($pass!=$pass2)$text.='Пароли не совпадают<br />';
        if(strlen($pass)<6)$text.='Пароль не менее 6 символов!<br />';
        return $text;
    }

    function get_directory_list($path)
    {
        $list_dir=array();
        $dir = opendir($path);
        while ($file_name = readdir($dir))
        {
            clearstatcache();
            // echo '',$file_name;
            if (is_dir($path.$file_name)&&$file_name!='.'&&$file_name!='..') {array_push($list_dir, $file_name); }
            // if (is_file($path.$file_name)) { echo '',$file_name; }
            //echo ''.dirname($file_name).' *** '.basename($file_name);
        }
        //var_info($list_dir);
        return $list_dir;
    }

    function createDir($id)
    {
        $dir=array();
        $dir['0']="files/product/".substr($id,-1).'/'.$id.'/';
        $dir['1']="files/product/".substr($id,-1).'/'.$id.'/more/';
        if(!is_dir($dir['1']))mkdir($dir['1'], 0755, true);
        return $dir;
    }
	  function generate_password($number)
	  {
		$arr = array('a','b','c','d','e','f',
					 'g','h','i','j','k','l',
					 'm','n','o','p','r','s',
					 't','u','v','x','y','z',
					 'A','B','C','D','E','F',
					 'G','H','I','J','K','L',
					 'M','N','O','P','R','S',
					 'T','U','V','X','Y','Z',
					 '1','2','3','4','5','6',
					 '7','8','9','0','.',',',
					 '(',')','[',']','!','?',
					 '&','^','%','@','*','$',
					 '<','>','/','|','+','-',
					 '{','}','`','~');
		// Генерируем пароль
		$pass = "";
		for($i = 0; $i < $number; $i++)
		{
		  // Вычисляем случайный индекс массива
		  $index = rand(0, count($arr) - 1);
		  $pass .= $arr[$index];
		}
		return $pass;
	  }
	  
    function send_mime_mail($name_from, // имя отправителя
                            $email_from, // email отправителя
                            $name_to, // имя получателя
                            $email_to, // email получателя
                            $data_charset, // кодировка переданных данных
                            $send_charset, // кодировка письма
                            $subject, // тема письма
                            $body // текст письма
    )
    {
        $email_to=str_replace("&#044;", ",", $email_to);
        $email_cnt=explode(",", $email_to);
        $email_to="";
        for($i=0; $i<=count($email_cnt) - 1; $i++)
        {
            if($i!=0)$email_to.=", ";
            $email_to.="< {$email_cnt[$i]} >";//echo $email_cnt[$i]."<br />";
        }
        $to = mime_header_encode($name_to, $data_charset, $send_charset)
            .$email_to;
        $subject = mime_header_encode($subject, $data_charset, $send_charset);
        $from =  mime_header_encode($name_from, $data_charset, $send_charset)
            .' <' . $email_from . '>';
        if($data_charset != $send_charset) {
            $body = iconv($data_charset, $send_charset, $body);
        }
        $headers = "From: $from \r\n";
        $headers .= "Reply-To: $from \r\n";
        $headers .= "Content-type: text/html; charset=$send_charset \r\n";

        return mail($to, $subject, $body, $headers, "-f info@".$_SERVER['HTTP_HOST']);
    }

    function mime_header_encode($str, $data_charset, $send_charset) {
        if($data_charset != $send_charset) {
            $str = iconv($data_charset, $send_charset, $str);
        }
        return '=?' . $send_charset . '?B?' . base64_encode($str) . '?=';
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
	
	function removeDir($dir)
	{
	   if($objs = glob($dir."/*"))
	   {
		   foreach($objs as $obj)
		   {
			   is_dir($obj) ? removeDir($obj) : unlink($obj);
		   }
	   }
	   if(is_dir($dir))rmdir($dir);
	}
	
	////Remove all dir with name $name_dir
	function bfglob($path, $name_dir, $pattern = '*', $flags = GLOB_NOSORT, $depth = 0)
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
	function checkAuthAgentik($db)
	{
		if(isset($_COOKIE['login'], $_COOKIE['password']))
		{
			$sql = "SELECT id, type_moderator, login FROM `moderators` WHERE `login`=? AND `password`=? AND `active`=?";
			$param = array($_COOKIE['login'], $_COOKIE['password'], 1);
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
				
				setcookie('login', $_COOKIE['login'],  time()+3600*24);
				setcookie('password', $_COOKIE['password'],  time()+3600*24);
			}
			else $error = 'error';
		}
		if(isset($error))unset($_SESSION['admin']);
		if(!isset($_SESSION['admin']))return false;
		return true;
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
	
	function images($File, $big_photo, $small_photo, $width, $height) 
	{
		$File_new=$small_photo;
		$gab =  getimagesize($File);
		switch($gab['mime'])
		{
			case 'image/pjpeg'		: $resimage = imagecreatefromjpeg($File); break;
			case 'image/jpeg'		: $resimage = imagecreatefromjpeg($File); break;
			case 'image/gif'		: $resimage = imagecreatefromgif($File); break;
			case 'image/png'		: $resimage = imagecreatefrompng($File); break;
			case 'image/x-ms-bmp'	: $resimage = imagecreatefromwbmp($File); break;
			default					: $resimage = imagecreatefromjpeg($File); break;
		}
		$w=$width;
		$h=$height;
		if ($gab[1]>=$w&&$gab[0]<=$gab[1])
		{
			$h=$height;
			$w = ($gab[0]/($gab[1]/$h));
			$smallimage =  imagecreatetruecolor($width, $height);
			imagefill($smallimage, 0, 0, 0xffffff);
			imagecopyresampled($smallimage, $resimage, ($width-$w)/2, ($height-$h)/2, 0, 0, $w, $h, $gab[0], $gab[1]);
			imagejpeg($smallimage,$File_new, 100);
		}
		else{
			$h=$height;
			$w = ($gab[0]/($gab[1]/$h));
			$smallimage =  imagecreatetruecolor($width, $height);
			imagefill($smallimage, 0, 0, 0xffffff);
			imagecopyresampled($smallimage, $resimage, ($width-$w)/2, ($height-$h)/2, 0, 0, $w, $h, $gab[0], $gab[1]);
			imagejpeg($smallimage,$File_new, 100);
		}
		
		////////////big photo
		if($big_photo!="")
		{
			copy($File, $big_photo);	
		}
		
		/*$gab =  getimagesize($File);
		if($gab[1]>=$gab[0]&&$gab[1]>=390)
		{
			$h = 390;
			$w = ($gab[0]/($gab[1]/390));
			$smallimage =  imagecreatetruecolor($w, $h);
			imagecopyresampled($smallimage, $resimage, 0, 0, 0, 0, $w, $h, $gab[0], $gab[1]);
			imageJpeg($smallimage,$File_new, 100);
		}
		elseif($gab[0]>=650)
		{
			$w = 650;
			$h = ($gab[1]/($gab[0]/650));
			$smallimage =  imagecreatetruecolor($w, $h);
			imagecopyresampled($smallimage, $resimage, 0, 0, 0, 0, $w, $h, $gab[0], $gab[1]);
			imagejpeg($smallimage,$File_new, 100);
		}
		else{
			copy($File, $File_new);	
		}*/
		
	}
?>