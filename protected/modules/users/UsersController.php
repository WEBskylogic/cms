<?php
/*
 * вывод каталога компаний и их данных
 */
class UsersController extends BaseController{

    protected $params;
    protected $db;

    function  __construct($registry, $params)
    {
        $this->tb = "users";
        $this->registry = $registry;
        parent::__construct($registry, $params);
    }

    public function indexAction()
    {
        $vars['translate'] = $this->translation;
        $view = new View($this->registry);
		if(!isset($_SESSION['user_id'])&&isset($this->params['users'])&&$this->params['users']!="sign-up"&&$this->params['users']!="active")
		{
			header("Location: /users/sign-up");	
			exit();
		}

        if(isset($this->params['users'])&&$this->params['users']=="sign-up")////Registration or authorization
        {
            $data=$this->signUpAction();
        }
		elseif(isset($this->params['users'])&&$this->params['users']=="orders")///Users orders
        {
            $data=$this->ordersAction();
        }
		elseif(isset($this->params['users'])&&$this->params['users']=="logout")///Users orders
        {
            $data=$this->logoutAction();
        }
		elseif(isset($this->params['users'])&&$this->params['users']=="active")///Users orders
        {
            $data=$this->activeAction();
        }
        else{/////Edit user info
			if(isset($_POST['save_data']))
			{
				if($_POST['old_pass']!=""&&$_POST['new_pass']!="")
				{
					$row = $this->db->row("select id from users where password=? and id=?", array(md5($_POST['old_pass']), $_SESSION['user_id']));	
					if($row)
					{
						$row=$this->db->query("update users set password=? where id=?", array(md5($_POST['new_pass']), $_SESSION['user_id']));	
					}
				}
				$row=$this->db->query("update users set name=?, address=?, phone=?, city=?, `post_index`=? where id=?",
				array($_POST['name_save'], $_POST['address'], $_POST['phone_save'], $_POST['city'], $_POST['post_index'], $_SESSION['user_id']));
				$vars['user'] = $this->db->row("select * from users where id=?", array($_SESSION['user_id']));		
				$vars['message']="<div class='done'>Информация обновлена</div>";	
			}
            $vars['user_info']=$this->db->row("SELECT * FROM `".$this->tb."` WHERE id=?", array($_SESSION['user_id']));
            $data['content'] = $view->Render($this->tb.'.phtml', $vars);
        }
		
		$data['styles']=array('user.css', 'validationEngine.jquery.css');
		$data['scripts']=array('jquery.validationEngine.js', 'jquery.validationEngine-ru.js');
        return $this->Render($data);
    }

    public function signUpAction()
    {
		if(isset($_SESSION['user_id']))header("Location: /users/cabinet");
		elseif(isset($_POST['email_auth']))
        {
			$vars['message'] = $this->authAction();	
		}
        elseif(isset($_POST['email_sign']))
        {
            $error="";
            if(!Captcha3D::check($_POST['captcha']))$error.="<div class='err'>".$this->translation['wrong_code']."</div>";
            $error.=$this->validate($_POST['email_sign'], 'email');
            $error.=$this->validate(array($_POST['name_sign'], $_POST['phone_sign'], $_POST['captcha'], $_POST['pass_sign']));
			
            $row=$this->db->row("SELECT id FROM users WHERE email='{$_POST['email_sign']}'");
            if($row)$error.="<div class='err'>".$this->translation['email_exists']."</div>";
            if($error=="")
            {
                $date=date("Y-m-d H:i:s");
                $pass=$_POST['pass_sign'];
				$code=md5(mktime());
                $query="INSERT INTO `".$this->tb."` SET
						email=?,
						name=?,
						phone=?,
						status_id=?,
						pass=?,
						start_date=?,
						active_email=?";
                $this->db->query($query, array($_POST['email_sign'], $_POST['name_sign'], $_POST['phone_sign'], 1, md5($pass), $date, $code));

                $settings = Registry::get('user_settings');
                $text="
                Новый пользователь на сайте www.{$_SERVER['HTTP_HOST']}<br />
                Дата: ".date("d/m/Y, H:i")."<br />
                ФИО: {$_POST['name_sign']}<br />
                E-mail: {$_POST['email_sign']}<br />
                Телефон: {$_POST['phone_sign']}<br />";
				
                send_mime_mail($settings['sitename'], // имя отправителя
                    "info@".$_SERVER['HTTP_HOST'], // email отправителя
                    $_SERVER['HTTP_HOST'], // имя получателя
                    $settings['email'], // email получателя
                    "utf-8", // кодировка переданных данных
                    "windows-1251", // кодировка письма
                    "Новый пользователь на сайте ".$settings['sitename'], // тема письма
                    $text // текст письма
                );
				
				
				/////////
				send_mime_mail($settings['sitename'], // имя отправителя
								"info@".$_SERVER['HTTP_HOST'], // email отправителя
								$_POST['name_sign'], // имя получателя
								$_POST['email_sign'], // email получателя
								"utf-8", // кодировка переданных данных
								"windows-1251", // кодировка письма
								"Вы зарегистрированы на сайте ".$_SERVER['HTTP_HOST'], // тема письма
								"
								Вы зарегистрировались на сайте {$_SERVER['HTTP_HOST']}.<br /><br />

								Для завершения регистрации перейдите по адресу<br />
								<a href='http://{$_SERVER['HTTP_HOST']}/users/active/code/$code' target='_blank'>http://{$_SERVER['HTTP_HOST']}/users/active/code/$code</a><br /><br />

									Ваш логин: {$_POST['email_sign']}<br />
									Ваш пароль: $pass<br /><br />
									
									P.S. Если вы получили это письмо, но не проходили процесс регистрации (возможно кто-то использовал ваш e-mail), просто проигнорируйте это письмо." 
									// текст письма
								);//echo $text;	
                $vars['message'] = "<div class='done'>".$this->translation['sign_up_yes']."</div>";
            }
            else $vars['message'] = $error;
        }
        $vars['body'] = $this->db->row("SELECT tb.body FROM `".$this->key_lang."_pages` tb LEFT JOIN pages tb2 ON tb.pages_id=tb2.id WHERE tb2.url=?", array('sign-up'));
        $vars['translate'] = $this->translation;
        $view = new View($this->registry);
        $data['content'] = $view->Render('sign_up.phtml', $vars);
        return $data;
    }
	
	public function ordersAction()
    {
        if(isset($this->params['id'])&&$this->params['id']!="")
		{
			$vars['order'] = $this->db->row("SELECT tb.*,
													tb2.name,
													tb3.name as delivery
											 FROM orders tb 
											 
											 LEFT JOIN orders_status tb2
											 ON tb.status_id=tb2.id
											 
											 LEFT JOIN delivery tb3
											 ON tb.delivery_id=tb3.id
											 
											 WHERE tb.id=? AND tb.user_id=?", array($this->params['id'], $_SESSION['user_id']));
			if(isset($vars['order']['id']))
			{
				$vars['product'] = $this->db->rows("SELECT * FROM orders_product WHERE orders_id=?", array($vars['order']['id']));
			}
			
		}
		else{
			$vars['orders'] = $this->db->rows("SELECT
													tb.id,
													tb.sum,
													tb.date_add,
													tb2.name
												 FROM `orders` tb
													LEFT JOIN
														orders_status tb2
													ON tb.status_id=tb2.id
												 
												 WHERE tb.user_id=?
												 ORDER BY tb.`date_add` DESC", array($_SESSION['user_id']));
		}
        
		//$vars['body'] = $this->db->row("SELECT tb.body FROM `".$this->key_lang."_pages` tb LEFT JOIN pages tb2 ON tb.pages_id=tb2.id WHERE tb2.url=?", array('sign-up'));
        $vars['translate'] = $this->translation;
        $view = new View($this->registry);
        $data['content'] = $view->Render('orders.phtml', $vars);
        return $data;
    }
	
	public function authAction()
    {
		$error="";
		$error.=$this->validate($_POST['email_auth'], 'email');
		$error.=$this->validate($_POST['pass_auth']);

		$row=$this->db->row("SELECT id FROM users WHERE email=? AND pass=?", array($_POST['email_auth'], md5($_POST['pass_auth'])));
		if(!$row)$error.="<div class='err'>".$this->translation['email_no_exists']."</div>";
		else{
			$row=$this->db->row("SELECT id FROM users WHERE id=? AND active=?", array($row['id'], 1));	
			if(!$row)$error.="<div class='err'>".$this->translation['no_active']."</div>";
		}
		if($error=="")
		{
			$admin_info=array();
			$admin_info['agent'] = $_SERVER['HTTP_USER_AGENT'];
			$admin_info['referer'] = $_SERVER['HTTP_REFERER'];
			$admin_info['ip'] = $_SERVER['REMOTE_ADDR'];
			$admin_info['id'] = $row['id'];
			$_SESSION['user_info'] = $admin_info;
			$_SESSION['user_id']=$row['id'];
			$vars['message'] = "<div class='done'>".$this->translation['auth_yes']."</div>";
		}
		else $vars['message'] = $error;	
		
		return $vars['message'];	
	}
	
	public function logoutAction()
    {
		unset($_SESSION['user_id']);	
		header("Location: /users/sign-up");
	}
	
	public function activeAction()
    {
		$row=$this->db->row("SELECT id FROM users WHERE active_email=?", array($this->params['code']));
		if($row)
		{
			$code=md5(mktime());
			$this->db->query("UPDATE users SET active=?, active_email=? WHERE id=?", array(1, $code, $row['id']));
			$vars['message'] = "<div class='done'>".$this->translation['you_active']."</div>";	
		}
		else $vars['message'] = "<div class='err'>".$this->translation['wrong_active']."</div>";	
		$view = new View($this->registry);
        $data['content'] = $view->Render('active.phtml', $vars);
		return $data;
	}
}
?>