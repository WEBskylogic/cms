<?php
/**
 * class to auntificate user
 * @author mvc
 */

class LoginController extends BaseController{
	
	function __construct ($registry, $params){
		$this->registry = $registry;
		parent::__construct($registry, $params);
	}

	function indexAction()
	{
		$vars['admin'] = 'admin';
		$vars['err'] = '';
		
		
		
		if(isset($_POST['login'],$_POST['password']))
		{
			$err='';
			if(!Captcha3D::check($_POST['captcha'])) $err = messageAdmin('Неправильно введены контрольные символы', 'error');
			$sql = "SELECT id, type_moderator, login FROM `moderators` WHERE `login`=? AND `password`=? AND `active`=?";
            $param = array($_POST['login'], md5($_POST['password']), 1);
			$res = $this->db->row($sql, $param);
			if(!$res)$err = messageAdmin('Неправильный логин или пароль', 'error');
			if($err=='')
			{
				$admin_info['agent'] = $_SERVER['HTTP_USER_AGENT'];
				$admin_info['referer'] = $_SERVER['HTTP_REFERER'];
				$admin_info['ip'] = $_SERVER['REMOTE_ADDR'];
				$admin_info['id'] = $res['id'];
                $admin_info['type'] = $res['type_moderator'];
				$admin_info['login'] = $res['login'];
				$_SESSION['admin'] = $admin_info;
				
				//setcookie('login', $_POST['login'],  time()+3600*24, '/', ".".$_SERVER['HTTP_HOST']);
				//setcookie('password', md5($_POST['password']), time()+3600*24, '/', ".".$_SERVER['HTTP_HOST']);

				if($_SERVER['REQUEST_URI']=='/admin/logout'||(isset($this->params['code'])&&$this->params['code']!=''))header('location: /admin');
				else header('location:'.$_SERVER['REQUEST_URI']);
			}
			else $vars['err'] = $err;
		}
		elseif(isset($_POST['email_forgot']))
		{
			$err='';
			if(!Captcha3D::check($_POST['captcha'])) $err = messageAdmin('Неправильно введены контрольные символы', 'error');
			$sql = "SELECT id, type_moderator, login FROM `moderators` WHERE `email`=? AND `active`=?";
            $param = array($_POST['email_forgot'], 1);
			$res = $this->db->row($sql, $param);
			if(!$res)$err = messageAdmin('E-mail не найден в базе', 'error');
			if($err=='')
			{
				$code=md5(mktime());
				$this->db->query("UPDATE moderators SET active_code='$code' WHERE id='{$res['id']}'");
				$text="
                Смена пароля на сайте {$_SERVER['HTTP_HOST']}.<br /><br />
		
				Чтобы поменять пароль, перейдите по ссылке<br />
				<a href=\"http://{$_SERVER['HTTP_HOST']}/admin/changepass/code/$code\" target=\"_blank\">http://{$_SERVER['HTTP_HOST']}/admin/changepass/code/$code</a><br /><br />
				";
				Mail::send($_SERVER['HTTP_HOST'], // имя отправителя
							"info@".$_SERVER['HTTP_HOST'], // email отправителя
							$res['login'], // имя получателя
							$_POST['email_forgot'], // email получателя
							"utf-8", // кодировка переданных данных
							"windows-1251", // кодировка письма
							"Смена пароля {$_SERVER['HTTP_HOST']}", // тема письма
							$text // текст письма
							);
				$vars['err'] = messageAdmin('На ваш E-mail была выслана ссылка для смены пароля');
			}
			else $vars['err'] = $err;
		}
		elseif(isset($this->params['code'])&&$this->params['code']!='')
		{
			$row = $this->db->row("SELECT id, type_moderator, login, email FROM `moderators` WHERE `active_code`=? AND `active`=? AND email!=''", array($this->params['code'], 1));
			if($row)
			{
				$pass=genPassword();
				$this->db->query("UPDATE moderators SET password='".md5($pass)."' WHERE id='{$row['id']}'");
				$text="
                Смена пароля на сайте {$_SERVER['HTTP_HOST']}.<br /><br />
		
				Ваш новый пароль: $pass";
				
				Mail::send($_SERVER['HTTP_HOST'], // имя отправителя
							"info@".$_SERVER['HTTP_HOST'], // email отправителя
							$row['login'], // имя получателя
							$row['email'], // email получателя
							"utf-8", // кодировка переданных данных
							"windows-1251", // кодировка письма
							"Смена пароля {$_SERVER['HTTP_HOST']}", // тема письма
							$text // текст письма
							);
				$vars['err'] = messageAdmin('На ваш E-mail была выслан новый пароль');	
			}
		}
		$data['content'] = $this->view->Render('log-in.phtml', $vars);
		return $this->Index($data);
	}
}
?>