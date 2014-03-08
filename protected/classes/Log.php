<style>
	.debug_message{
		border:solid 1px #DEDEDE; 
		background:#EFEFEF;
		color:#222222;
		padding:4px;
	}
</style>
<?php
class Log
{
	public static function echoLog ($logText)
	{
		if(DEBUG)echo '<div class="debug_message"><pre><font color="red">' . $logText . '</font></pre></div>';
		else echo"Mysql Error!";
		Mail::errorMail($logText);
	}
	
	public static function writeLog ($logText,$logType) {
		$body=$logType.' \n '.$logText;
		
		if(DEBUG)
		{
			echo '<font color="red">' . $logType . '<br>';
			echo $logText . '</font>';
		}
		else{
			Mail::errorMail($body);
			echo"Mysql Error!";	
		}
	}
}
?>