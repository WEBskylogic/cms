<?php
	function __autoload($class_name)
	{
		$filename = $class_name.'.php';//echo $filename.'<br>';
		if(!include($filename))
		{
			return false;
		}
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
		if($_SESSION['currency'][1]['position']==1)$price = number_format($price, 2, ',', ' ').' '.$_SESSION['currency'][1]['icon'];
		else $price = $_SESSION['currency'][1]['icon'].number_format($price, 2, ',', ' ');
		return $price;
	}
	function discount($discount, $sum)
	{
		//$return=array();
		$discount=$discount/100;
		return round($sum-$sum*$discount,2);
	}
		
	function getUri($languages)
	{
		$key_translation = array();
		$url=sanitize($_SERVER['REQUEST_URI']);
		foreach($languages as $row)
		{
			$value_lang = explode("/", $url);
			if(isset($value_lang[1])&&$value_lang[1]==$row['language'])
			{
				$_SESSION['key_lang'] = $row['language'];
				$_SERVER['REQUEST_URI'] = mb_substr($_SERVER['REQUEST_URI'], 3);
			}
		}
		//$_SESSION['key_lang']='ru';
		if(!isset($_SESSION['key_lang']))$_SESSION['key_lang']='ru';
		return $_SESSION['key_lang'];
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
	
	function translit($str)
	{
	  
		//	echo" $str";
		$transtable = array();
		$transtable = array('А' => 'A',		'Б' => 'B',		'В' => 'V',		'Г' => 'G',		'Д' => 'D',		'Е' => 'E',
							'Ё' => 'Yo',	'Ж' => 'Zh',	'З' => 'Z',		'И' => 'I',		'Й' => 'Y',		'К' => 'K',
							'Л' => 'L',		'М' => 'M',		'Н' => 'N',		'О' => 'O',		'П' => 'P',		'Р' => 'R',
							'С' => 'S',		'Т' => 'T',		'У' => 'U',		'Ф' => 'F',		'Х' => 'H',		'Ц' => 'Ts',
							'Ч' => 'Ch',	'Ш' => 'Sh',	'Щ' => 'Shch',	'Ъ' => '',		'Ы' => 'I',		'Ь' => '',
							'Э' => 'E',		'Ю' => 'Yu',	'Я' => 'Ya',	'а' => 'a',		'б' => 'b',		'в' => 'v',
							'г' => 'g',		'д' => 'd',		'е' => 'e',		'ё' => 'yo',	'ж' => 'zh',	'з' => 'z',
							'и' => 'i',		'й' => 'y',		'к' => 'k',		'л' => 'l',		'м' => 'm',		'н' => 'n',
							'о' => 'o',		'п' => 'p',		'р' => 'r',		'с' => 's',		'т' => 't',		'у' => 'u',
							'ф' => 'f',		'х' => 'h',		'ц' => 'ts',	'ч' => 'ch',	'ш' => 'sh',	'щ' => 'shch',
							'ъ' => '',		'ы' => 'i',		'ь' => '',		'э' => 'e',		'ю' => 'yu',	'я' => 'ya',
							
							' ' => '-',		'\\' => '',		'/' => '',		'*' => '',		'+' => '',		'&' => '',
							'>' => '',		'<' => '',		'@' => '',		'№' => '',		';' => '',		'%' => '',		
							':' => '',		'?' => '',		'(' => '',		')' => '',		'=' => '',		'!' => '',		
							'"' => '',		'\'' => '',		'$' => '',		'.' => '-',		',' => '',		'~' => '',		
							'^' => '',		'«' => '',		'»' => '');
		  //  заменяем "односимвольные", многосимвольные, символы    
		$str = strtr($str, $transtable);
		$str=mb_strtolower($str); 
		return $str;
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
	
	function post_write($err,$header = false){
		$_POST['err'] = $err;
		$_SESSION['_POST'] = $_POST;
		$_POST = array();
		if($header) {
			header("location:$header");
			exit();
		}
	}
	
	function post_read(){
		
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
	
	function genPassword($size = 8){
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
?>