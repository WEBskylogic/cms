<?php
class errorController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{ 
		$this->registry = $registry;
		parent::__construct($registry, $params);
	}
	
	
	function indexAction()
	{
		header("HTTP/1.0 404 Not Found");
		header('Refresh: 3; url=/');
		//$vars['translate'] = $this->translation;
		$data['meta'] = array('title'=>'Page not found', 'keywords'=>'Page not found', 'description'=>'Page not found');
		$data['content'] = $this->view->Render('404.phtml');
		return $this->Index($data);
	}
}
?>