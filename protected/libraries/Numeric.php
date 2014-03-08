<?php
class Numeric{

	static function  viewPrice($price, $discount=0)
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
				$return['old_price']='<div class="old_price">'.Numeric::formatPrice($price).'</div>';
				$price = Numeric::discount($discount, $price);
				$return['price'] = '<div class="new_price">'.Numeric::formatPrice($price).'</div>';
			}
			else $return['price'] = '<div>'.Numeric::formatPrice($price).'</div>';
			
			$return['cur_price'] = round($price, 2);
			$return['base_price'] = $price;
		}
		else
		{
			$return['base_price'] = $price;
			$price = $price * (1/$_SESSION['currency'][1]['rate']);
			if($discount!=0)
			{
				$return['old_price']='<div class="old_price">'.Numeric::formatPrice($price).'</div>';
				$price = Numeric::discount($discount, $price);	
				$return['base_price'] = Numeric::discount($discount, $return['base_price']);
				$return['price'] = '<div class="new_price">'.Numeric::formatPrice($price).'</div>';
			}
			else $return['price'] = Numeric::formatPrice($price);
			$return['cur_price'] = round($price, 2);
			
		}
		
		return $return;
	}
	
	static function  formatPrice($price)
	{
		if(!isset($_SESSION['rounding']))$_SESSION['rounding']=2;
		if($_SESSION['currency'][1]['position']==1)$price = '<span>'.number_format($price, $_SESSION['rounding'], ',', ' ').' </span> '.'<font>'.$_SESSION['currency'][1]['icon'].'</font>';
		else $price = '<font>'.$_SESSION['currency'][1]['icon'].'</font>'.number_format($price, $_SESSION['rounding'], ',', ' ');
		return $price;
	}
	
	static function  discount($discount, $sum)
	{
		//$return=array();
		$discount=$discount/100;
		return round($sum-$sum*$discount, 2);
	}
}
?>