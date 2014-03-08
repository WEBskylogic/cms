<?php
class String{

	static function translit($string, $flag=false)	
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
			"	"=>"-"," "=>"-", "„"=>"-", "”"=>"-"
		);
		$string=iconv("UTF-8","UTF-8//IGNORE",strtr($string,$replace));
		if(!$flag)$string = preg_replace("/[^a-zA-ZА-Яа-я0-9\s]/", "-", $string); 
		else $string = preg_replace("/[^a-zA-ZА-Яа-я0-9\s\/\:]/", "-", $string); 
		return mb_strtolower($string);
	}
	
	static function sanitize($var, $reverse = false)
	{
		$sanMethod = array(
			array('&','&#038;'),
			array('"','&#034;'),
			array('"','&quot;'),
			array("'",'&#039;'),
			array('%','&#037;'),
			array('(','&#040;'),
			array(')','&#041;'),
			array('+','&#043;'),
			array('<','&lt;'),
			array('>','&gt;'),
			array('\'','&apos;'),
			array('&','&amp;'),
			array(' ','&nbsp;')
		);

		if(!is_array($var))
		{
			$charsCount = count($sanMethod);
			if($reverse) for($j=$charsCount;$j>0;$j--)
			{
				if(isset($sanMethod[$j][1]))
				$var = str_replace($sanMethod[$j][1], $sanMethod[$j][0], $var);
			}
			else{
				for($j=0;$j<$charsCount;$j++)
				{
					if(isset($sanMethod[$j][0]))
						$var = str_replace($sanMethod[$j][0], $sanMethod[$j][1], $var);
				}
			}
			return $var;
		}
		$varCount = count($var);
		$keys = array_keys($var);
		$i=0; while($i<$varCount)
		{
			if (is_array($var[$keys[$i]])) return String::sanitize($var[$keys[$i]]);
			else{
				$charsCount = count($sanMethod);
				if($reverse) for($j=$charsCount;$j>0;$j--) $var = str_replace($sanMethod[$j][1], $sanMethod[$j][0], $var);
				else for($j=0;$j<$charsCount;$j++) $var[$keys[$i]] = str_replace($sanMethod[$j][0], $sanMethod[$j][1], $var[$keys[$i]]);
			}
			$i++;
		}
		return $var;
	}
	
	static function post_write($err,$header = false)
	{
		$_POST['err'] = $err;
		$_SESSION['_POST'] = $_POST;
		$_POST = array();
		if($header) {
			header("location:$header");
			exit();
		}
	}
	
	static function post_read()
	{
		if(empty($_SESSION['_POST'])) return array();
		$post = $_SESSION['_POST'];
		unset($_SESSION['_POST']);
		return $post;
	}
	
	
	static function get_page_link($page, $cur_page, $var, $text='')
	{
		if (!$text)$text = $page;
		if ($page!=$cur_page)
		{
			$path=$_SERVER['REQUEST_URI'];
			//$reg = '/((\/|^)'.$var.'\/)[^&#]*/';
			//$url = ( preg_match( $reg, $path ) ? preg_replace($reg, '${1}', $path) : ( $path ? $path.'/' : '' ).$var.'/');
			$reg = '/((\/|^)'.$var.'\/)[^\/#]*/';
			if($page!=1)$url = ( preg_match( $reg, $path ) ? preg_replace($reg, '${1}'.$page, $path) : ($path? $path.'/' : '' ).$var.'/'.$page);
			else $url = ( preg_match( $reg, $path ) ? preg_replace($reg, '', $path) : ($path? $path.'/' : '' ).$var.'/'.$page);
			//echo "<br />{$url2}<br /><br />";
			$url=str_replace("//", "/", $url);
			return '<a href="'.$url.'">'.$text.'</a>';
		}
		return '<span>'.$text.'</span>';
	}
	
	static function getUrl2($var)
	{
		$reg = '/'.$var.'\/[a-z0-9]+/';
		$url = preg_replace($reg, '', $_SERVER['REQUEST_URI']);
		$url=str_replace("//", "/", $url);
		return $url;
	}
	
	static function getUrl($var, $page='')
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
	
	static function search_links($text)
	{
		$str='';
		preg_match_all('/(?:href|src|url)=(\"?)((http\:\/\/)[^\s\">]+?)(\"?)([^>]*>)/ismU', $text, $links);
		foreach($links[2] as $link)
		{
			if($str!='')$str.=', ';	
			$str.=$link;	
		}
		return $str;	
	}
	
	static function clear_links($text)
	{
		//~<a\b[^>]*(http:\/\/|www)+>|</a\b[^>]*+>~
		/*$text = preg_replace('/<img(?:\\s[^<>]*)?>/i', '', $text);*/
		$text = preg_replace('~<a\b[^>]*+>|</a\b[^>]*+>~', '', $text);
		return $text;	
	}
}
?>