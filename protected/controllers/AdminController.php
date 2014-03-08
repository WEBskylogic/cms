<?php
/**
 * class to auntificate user
 * @author mvc
 */

class adminController extends BaseController{
	
	function __construct ($registry, $params){
		$this->registry = $registry;
		parent::__construct($registry, $params);
	}

	function indexAction()
	{
		$vars['admin'] = 'admin';
		$vars['err'] = '';
		
		$this->view = new View($this->registry);
		$data['content'] = $this->view->Render('main.phtml', $vars);
		return $this->Index($data);
	}
}
?>