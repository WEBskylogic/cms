<?php
/*
 * вывод каталога компаний и их данных
 */
class UsersController extends BaseController{

    protected $params;
    protected $db;

    function  __construct($registry, $params)
    {
		parent::__construct($registry, $params);
		$this->registry = $registry;
        $this->tb = "users";       
		$this->users = new Users($this->sets);
    }

    public function indexAction()
    {
        $vars['translate'] = $this->translation;
		if(!isset($_SESSION['user_id'])&&(isset($this->params['users'])&&$this->params['users']!="sign-up"&&$this->params['users']!="active"))
		{
			/*header("Location: /users/sign-up");	
			exit();*/
		}

		if(isset($this->params['users'])&&$this->params['users']=="orders")///Users orders
        {
            $data=$this->ordersAction();
        }
		elseif(isset($this->params['users'])&&$this->params['users']=="logout")///Users orders
        {
            $data=$this->users->logout();;
        }
		elseif(isset($this->params['users'])&&$this->params['users']=="active")///Users orders
        {
            $data['content']=$this->users->active();
        }
		elseif(isset($this->params['users'])&&$this->params['users']=="forgotpass")///Users orders
        {
            $data=$this->forgotAction();
        }
        elseif(isset($_SESSION['user_id']))/////Edit user info
		{
			if(isset($_POST['save_data']))
			{
				if($_POST['old_pass']!=""&&$_POST['new_pass']!="")
				{
					$row = $this->db->row("SELECT id FROM users WHERE pass=? AND id=?", array(md5($_POST['old_pass']), $_SESSION['user_id']));	
					if($row)
					{
						$row=$this->db->query("UPDATE users SET pass=? WHERE id=?", array(md5($_POST['new_pass']), $_SESSION['user_id']));	
					}
				}
				$row = $this->db->query("UPDATE users SET name=?, address=?, phone=?, city=?, `post_index`=? WHERE id=?",
				array($_POST['name_save'], $_POST['address'], $_POST['phone_save'], $_POST['city'], $_POST['post_index'], $_SESSION['user_id']));
				$vars['message']="<div class='done'>Информация обновлена</div>";
			}
            $vars['user_info']=$this->users->find((int)$_SESSION['user_id']);
            $data['content'] = $this->view->Render($this->tb.'.phtml', $vars);
        }
		else////Registration or authorization
        {
            $data=$this->signUpAction();
        }
		
		$data['styles']=array('user.css', 'validationEngine.jquery.css');
		$data['scripts']=array('jquery.validationEngine.js', 'jquery.validationEngine-ru.js');
        return $this->Index($data);
    }

    public function signUpAction()
    {
		if(isset($_SESSION['user_id']))header("Location: /users/cabinet");
		elseif(isset($_POST['email_auth']))
        {
			$vars['message'] = $this->users->auth($_POST['email_auth'], $_POST['pass_auth']);
		}
        else
        {
            $vars['message'] = $this->users->signUp();
        }
        $vars['body'] = $this->model->getPage('sign-up');
        $vars['translate'] = $this->translation;
        $data['content'] = $this->view->Render('sign_up.phtml', $vars);
        return $data;
    }
	
	public function ordersAction()
    {
		if(isset($this->params['id'])&&$this->params['id']!="")
		{
			$vars['order'] = Orders::getObject($this->sets)->find(array('select'=>'tb.*, tb2.name, tb3.name as delivery, tb4.name as payment',
																		 'join'=>'LEFT JOIN orders_status tb2 ON tb.status_id=tb2.id 
																		 		  LEFT JOIN '.$this->registry['key_lang'].'_payment tb4 ON tb.payment_id=tb4.payment_id
																				  LEFT JOIN '.$this->registry['key_lang'].'_delivery tb3 ON tb.delivery_id=tb3.delivery_id',
																		 'where'=>'__tb.id:='.$this->params['id'].'__ AND __tb.user_id:='.$_SESSION['user_id'].'__ '));
			if(isset($vars['order']['id']))
			{
				$vars['product'] = $this->db->rows("SELECT * FROM orders_product WHERE orders_id=?", array($vars['order']['id']));
			}
		}
		else{												 
			$vars['orders'] = Orders::getObject($this->sets)->find(array('select'=>'tb.id, tb.sum, tb.date_add, tb2.name',
																		 'join'=>'LEFT JOIN orders_status tb2 ON tb.status_id=tb2.id',
																		 'where'=>'__tb.user_id:='.$_SESSION['user_id'].'__', 
																		 'order'=>'tb.date_add DESC',
																		 'type'=>'rows'));
		}
        
		//$vars['body'] = $this->db->row("SELECT tb.body FROM `".$this->key_lang."_pages` tb LEFT JOIN pages tb2 ON tb.pages_id=tb2.id WHERE tb2.url=?", array('sign-up'));
        $vars['translate'] = $this->translation;
        $data['content'] = $this->view->Render('orders.phtml', $vars);
        return $data;
    }
	
    function checkauthAction()
    {
        $vars['message'] = $this->users->auth($_POST['email'], $_POST['pass']);
		if(isset($_SESSION['user_id']))$vars['auth']=1;
        return json_encode($vars);
    }

    function registerAction()
    {
        $message = "";
        if(isset($_POST['name'],$_POST['email'],$_POST['phone']))
        {
            $message = Validate::check($_POST['name'], $this->translation);
            $message .= Validate::check($_POST['email'], $this->translation, 'email');
            $message .= Validate::check($_POST['phone'], $this->translation);
        }
        $row=$this->db->row("SELECT `id` FROM users WHERE email=?",array($_POST['email']));
        if($row) $message = "Такой E-Mail уже зарегистрирован";
        if(!isset($_POST['pass'], $_POST['pass2']) || ($_POST['pass'] != $_POST['pass2'])) $message .= "  Пароли не совпадают.";

        if($message=="")
        {
            $date=date("Y-m-d H:i:s");
            $pass=md5($_POST['pass']);
            $code=md5(mktime());

            $this->db->query("INSERT INTO `users` SET
								name=?,
								city=?,
								email=?,
								phone=?,
								status_id=?,
								pass=?,
								start_date=?,
								active_email=?", array($_POST['name'], $_POST['city'], $_POST['email'], $_POST['phone'], 1, $pass, $date, $code));

            $settings = Registry::get('user_settings');

            $text="
                Новый пользователь на сайте www.{$_SERVER['HTTP_HOST']}<br />
                Дата: ".date("d/m/Y, H:i")."<br />
                ФИО: {$_POST['name']}<br />
				E-mail: {$_POST['email']}<br />
                Город: {$_POST['city']}<br />
                Телефон: {$_POST['phone']}<br />";


            Mail::send($settings['sitename'], // имя отправителя
                "info@".$_SERVER['HTTP_HOST'], // email отправителя
                $_SERVER['HTTP_HOST'], // имя получателя
                $settings['email'], // email получателя
                "utf-8", // кодировка переданных данных
                "windows-1251", // кодировка письма
                "Новый ".$opt_topic." на сайте ".$settings['sitename'], // тема письма
                $text // текст письма
            );

            Mail::send($settings['sitename'], // имя отправителя
                "info@".$_SERVER['HTTP_HOST'], // email отправителя
                $_POST['name'], // имя получателя
                $_POST['email'], // email получателя
                "utf-8", // кодировка переданных данных
                "windows-1251", // кодировка письма
                "Вы зарегистрированы на сайте ".$_SERVER['HTTP_HOST'], // тема письма
                "
								Вы зарегистрировались на сайте {$_SERVER['HTTP_HOST']}.<br /><br />

								Для завершения регистрации перейдите по адресу<br />
								<a href='http://{$_SERVER['HTTP_HOST']}/users/active/code/$code' target='_blank'>http://{$_SERVER['HTTP_HOST']}/users/active/code/$code</a><br /><br />

									Ваш логин: {$_POST['email']}<br />
									Ваш пароль: {$_POST['pass']}<br /><br />

									P.S. Если вы получили это письмо, но не проходили процесс регистрации (возможно кто-то использовал ваш e-mail), просто проигнорируйте это письмо."
            );
            echo "Регистрация завершена. На Ваш E-Mail отправлено письмо с ссылкой для активации учётной записи.";
        }
        else echo $message;
    }
	
	public function forgotAction()
    {
		$vars['translate'] = $this->translation;
		$settings = Registry::get('user_settings');
		$vars['message'] = '';
		if(isset($_POST['email']))
		{
			$error="";
			if(!preg_match('|([a-z0-9_\.\-]{1,20})@([a-z0-9\.\-]{1,20})\.([a-z]{2,4})|is', $_POST['email']))
				$error.="<div class='error'>Поле Email: Неправильный email</div>";
				
			$row = $this->db->row("SELECT id FROM users WHERE email=?", array($_POST['email']));
			if(!$row)$error.="<div class='error'>".$this->translation['email_exists2']."</div>";
			if($error=="")
			{
				$pass=md5(uniqid());
				$this->db->query("UPDATE users SET active_email=? WHERE `id`=?", array($pass, $row['id']));
				$text="Для смены пароля пройдите пожалуйста по ссылке <a href='http://{$_SERVER['HTTP_HOST']}/users/forgotpass/changepass/$pass' target='_blank'>http://{$_SERVER['HTTP_HOST']}/users/forgotpass/changepass/$pass</a>";
				Mail::send($settings['sitename'], // имя отправителя
									"info@".$_SERVER['HTTP_HOST'], // email отправителя
									"Пользователь на сайте ".$settings['sitename'], // имя получателя
									$_POST['email'], // email получателя
									"utf-8", // кодировка переданных данных
									"windows-1251", // кодировка письма
									"Запрос о смене пароля на сайте ".$settings['sitename'], // тема письма
									$text // текст письма
									);
				$vars['message'] = "<div class='done'>".$this->translation['change_pass']."</div>";					
			}
			else $vars['message'] = $error;
		}
		
		
		if(isset($this->params['changepass']))
		{
			$row=$this->db->row("SELECT id, email, name FROM users WHERE active_email=?", array($this->params['changepass']));
			if(!$row)$vars['message'] = "<div class='err'>Ошибка!</div>";
			else{
				$pass2=genPassword();
				$pass=md5($pass2);
				$code=md5(mktime());
				$this->db->query("UPDATE users SET pass=?, active_email=? WHERE `id`=?", array($pass, $code, $row['id']));
				$text="Ваш новый пароль: $pass2";
				Mail::send($settings['sitename'], // имя отправителя
							   "info@".$_SERVER['HTTP_HOST'], // email отправителя
							   $row['name'], // имя получателя
							   $row['email'], // email получателя
							   "utf-8", // кодировка переданных данных
							   "windows-1251", // кодировка письма
							   "Ваш пароль изменен на сайте ".$settings['sitename'], //тема письма
							   $text // текст письма
								);
				$vars['message'] = "<div class='done'>".$this->translation['change_new_pass']."</div>";	
			}
		}
		$data['content'] = $this->view->Render('forgotpass.phtml', $vars);
		return $data;
	}
}
?>