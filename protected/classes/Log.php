<?php
class Log
{
	public static function echoLog ($logText)
	{
		//echo '<pre><font color="red">' . $logText . '</font></pre>';
		echo"Mysql Error!";
		errorMail($logText);
	}
	
	public static function writeLog ($logText,$logType) {
		$body=$logType.' \n '.$logText;
		errorMail($body);
		echo"Mysql Error!";
		//echo '<font color="red">' . $logType . '<br>';		echo $logText . '</font>';
	}
}
?>