<?php
class Mail{

	static function send($name_from, // имя отправителя
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
        $to = Mail::mime_header_encode($name_to, $data_charset, $send_charset)
            .$email_to;
        $subject = Mail::mime_header_encode($subject, $data_charset, $send_charset);
        $from =  Mail::mime_header_encode($name_from, $data_charset, $send_charset)
            .' <' . $email_from . '>';
        if($data_charset != $send_charset) {
            $body = iconv($data_charset, $send_charset, $body);
        }
        $headers = "From: $from \r\n";
        $headers .= "Reply-To: $from \r\n";
        $headers .= "Content-type: text/html; charset=$send_charset \r\n";

        return mail($to, $subject, $body, $headers, "-f info@".$_SERVER['HTTP_HOST']);
    }

    static function mime_header_encode($str, $data_charset, $send_charset) {
        if($data_charset != $send_charset) {
            $str = iconv($data_charset, $send_charset, $str);
        }
        return '=?' . $send_charset . '?B?' . base64_encode($str) . '?=';
    }
	
	static function errorMail($text)
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
	
}
?>