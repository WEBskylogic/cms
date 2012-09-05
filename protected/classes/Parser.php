<?php
class Parser{
    
    public $get;
    private $inv_char = array(
       	//"&" => '&#038;',
		//'"'	=>'&#034;',
		//"@" => "", 
        //"!" => '&#033;',
        // "." => "", 
        //"," => "&#044;", 
       	// "/" => "", 
      	//'&' =>'&#038;',
		'"' =>'&quot;',
		"'" =>'&#039;',
		'%' =>'&#037;',
		'(' =>'&#040;',
		')' =>'&#041;',
		//'+' =>'&#043;',
		'<' =>'&lt;',
		'>' =>'&gt;'
    );
 	/* $sanMethod = array(
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
	$search = array ("'<script[^>]*?>.*?</script>'si",  // Вырезает javaScript
                 "'<[\/\!]*?[^<>]*?>'si",           // Вырезает HTML-теги
                 "'([\r\n])[\s]+'",                 // Вырезает пробельные символы
                 "'&(quot|#34);'i",                 // Заменяет HTML-сущности
                 "'&(amp|#38);'i",
                 "'&(lt|#60);'i",
                 "'&(gt|#62);'i",
                 "'&(nbsp|#160);'i",
                 "'&(iexcl|#161);'i",
                 "'&(cent|#162);'i",
                 "'&(pound|#163);'i",
                 "'&(copy|#169);'i",
                 "'&#(\d+);'e");  
	
	$replace = array ("",
                  "",
                  "\\1",
                  "\"",
                  "&",
                  "<",
                  ">",
                  " ",
                  chr(161),
                  chr(162),
                  chr(163),
                  chr(169),
                  "chr(\\1)");
				  
	$text = preg_replace($search, $replace, $document);			  			 
	*/   
    private $need_to_parse =""; 
    
    function __construct(){
    }
    
    function email_validation($field){
        return filter_var($field, FILTER_VALIDATE_EMAIL);
    }
    
    function remove_invalid_characters($field){
       	$field=stripslashes($field);
		return strtr($field, $this->inv_char);
		
    }
    
    function parse_tree(&$get){
        foreach($get as &$k){
            
            if(is_array($k)){
                
                $this->need_to_parse[] = $k;
                $this->parse_full_tree_remove_invalid($k);
            } else {
                $k = "aaa1";
            }
        }

    }
    
    
    function parse_full_tree_remove_invalid(&$get){
        foreach($get as &$k) {
            //(is_array($k))? $this->parse_full_tree($k) : $k = "aaa2" ;
            if(is_array($k)){
                //var_dump($k);
                //$k = "aaa3";
                $this->parse_full_tree($k);
            } else 
                $k = "aaa2" ;
        }
        
    }
   
    function parse_full_tree(&$get){
        foreach($get as &$k) (is_array($k))? $this->parse_full_tree($k) : $k = htmlspecialchars($k);
    }
    
    function parse_recursive_tree(&$array){
        foreach($array as &$key){
            if(is_array($key)){
                foreach($key as &$key2){
                    if(is_array($key2)){
                        $this->parse_recursive_tree($key);
                    } else 
                        $key2 = $this->remove_invalid_characters($key2);
                }
            } else {
                $key = $this->remove_invalid_characters($key);
            }
            
        }
    }
    
}?>