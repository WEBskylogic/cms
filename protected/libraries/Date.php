<?php
class Date{

	static function date_view($str, $format="dd-mm-yy")
	{
		$dd = substr($str, 8, 2);

		$mm = substr($str, 5, 2);
		$MM = Date::getMonth($mm);
		$YY = substr($str, 0, 4);
		$yy = substr($str, 2, 2);
		$hh = substr($str, 11, 2);
		$ii = substr($str, 14, 2);
		$ss = substr($str, 17, 2);
		$DD = Date::getDay(mktime(0, 0, 0, $mm, $dd, $YY));
		$replace = array('YY'=>$YY, 'yy'=>$yy, 'mm'=>$mm, 'dd'=>$dd, 'DD'=>$DD, 'hh'=>$hh, 'ii'=>$ii, 'ss'=>$ss, 'MM'=>$MM);
		$str = strtr($format, $replace);
		return $str;
	}
	
	public static function getMonth($month)
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
			case "12":$month='Декабрь';break;
		}
		return $month;	
	}

	public static function getDay($day)
	{
		$day=getdate($day);
		switch($day['wday'])
		{
			case "1":$day='Понедельник';break;
			case "2":$day='Вторник'; break;
			case "3":$day='Среда';break;
			case "4":$day='Четверг';break;
			case "5":$day='Пятница'; break;
			case "6":$day='Суббота';break;
			case "0":$day='Воскресенье';break;
		}
		return $day;
	}
}
?>