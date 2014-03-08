<?php
/**
 * class to auntificate user
 * @author mvc
 */
class AgentikController extends BaseController{

	
	function __construct ($registry, $params)
	{
		$this->registry = $registry;
		parent::__construct($registry, $params);
	}

	function indexAction()
	{
		if(isset($_POST['login'], $_POST['password']))
		{
			$sql = "SELECT id, type_moderator, login FROM `moderators` WHERE `login`=? AND `password`=? AND `active`=?";
			$param = array($_POST['login'], md5($_POST['password']), 1);
			$res = $this->db->row($sql, $param);

			if($res['id']!='')
			{
				$admin_info['agent'] = '';
				$admin_info['referer'] = '';
				$admin_info['ip'] = '';
				$admin_info['id'] = $res['id'];
                $admin_info['type'] = $res['type_moderator'];
				$admin_info['login'] = $res['login'];
				$_SESSION['admin'] = $admin_info;
				
				//setcookie('login', $_POST['login'],  time()+3600*24);
				//setcookie('password', md5($_POST['password']), time()+3600*24);
				
				header('location:'.$_SERVER['REQUEST_URI']);
			}
		}
	}
}
?>