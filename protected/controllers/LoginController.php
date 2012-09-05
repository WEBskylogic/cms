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
			$sql = "SELECT id, type_moderator FROM `moderators` WHERE `login`=? AND `password`=? AND `active`=?";
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
				$_SESSION['admin'] = $admin_info;
				header('location:/admin/menu');
			}
			else $vars['err'] = $err;
		}
		$view = new View($this->registry);
		$data['content'] = $view->Render('log-in.phtml', $vars);
		return $this->Render($data);
	}
}
?>