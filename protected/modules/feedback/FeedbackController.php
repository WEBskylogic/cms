<?php
/*
 * вывод каталога компаний и их данных
 */
class FeedbackController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "Feedback";
		$this->registry = $registry;
		$this->feedback = new Feedback($this->sets);
	}
	
	
	///Send message
    function feedbackAction()
    {
		if(isset($_POST['name'], $_POST['message'], $_POST['email']))
		{
			// echo strlen($_POST['message']);
			$send=0;
			if(strlen($_POST['message'])<5||strlen($_POST['name'])<3)$message="".$this->translation['required']."";
			elseif(!preg_match('|([a-z0-9_\.\-]{1,20})@([a-z0-9\.\-]{1,20})\.([a-z]{2,4})|is', $_POST['email']))$message="".$this->translation['wrong_email']."";
			else{
				$settings = Registry::get('user_settings');
				Mail::send($settings['sitename'], // имя отправителя
					"info@".$_SERVER['HTTP_HOST'], // email отправителя
					$settings['sitename'], // имя получателя
					$settings['email'], // email получателя
					"utf-8", // кодировка переданных данных
					"windows-1251", // кодировка письма
					"Обратная связь: ".$_SERVER['HTTP_HOST'], // тема письма
					"
					Имя:{$_POST['name']}<br />
					E-mail:{$_POST['email']}<br />
					Телефон:{$_POST['phone']}<br />
					<br />
					Сообщение:{$_POST['message']}
						" // текст письма
				);
				$send=1;
				$message="<font style='color:green;'>".$this->translation['message_sent']."</font>";
			}
			$message = array($send, $message);
			echo json_encode($message);
		}
    }
}
?>