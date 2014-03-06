<?php
class Arr{

	static function treeview($settings, $sub_id=0)
	{
		$return='';
		foreach($settings['arr'] as $row)
		{
			if($row['sub']==$sub_id)
			{
				$return.='<li><a href="'.$settings['link'].$row['url'].'">'.$row['name'].'</a>';
				$return.=Arr::treeview($settings, $row['id']);
				$return.='</li>';
				
				if(isset($settings['separator']))$return.='<li class="'.$settings['separator'].'"></li>';
			}
		}
		if($return!='')
		{
			if($sub_id==0)$id='id="'.$settings['id'].'"';
			else $id='';
			
			$return='<ul '.$id.'>'.$return.'</ul>';	
		}
		return $return;
	}
	
	
	static function Select_masiv($mas, $name_select, $KEY=0,$style, $option_text='Выберете')
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
	
	static function Select_masiv_multi($mas,$name_select,$ArKEY=0,$style, $option_text='Выберете')
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

	static function createTree_cat($array, $currentParent, $KEY, $currLevel = 0, $prevLevel = -1) 
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


				$text.=Arr::createTree_cat ($array, $categoryId, $KEY, $currLevel, $prevLevel);	
				$currLevel--;	 		 	
			}	
		}
		return $text;
	}

	
	static function arrayKeys($array, $key = 'id')
	{
		$array_new = array();
		foreach ($array as $val)
		{
			$array_new[$val[$key]] = $val;
		}	
		
		return $array_new;
	}
}
?>