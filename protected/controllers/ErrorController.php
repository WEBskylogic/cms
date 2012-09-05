<?php
class errorController extends BaseController{
	
	private $registry;
	protected $params;
	protected $key_lang="ru";
	
	function  __construct($registry, $params)
	{ 
		$this->registry = $registry;
		parent::__construct($registry, $params);
	}
	
	
	function indexAction()
	{
		header("HTTP/1.0 404 Not Found");
		//header('Refresh: 3; url=/');
        $view = new View($this->registry);
		//$vars['translate'] = $this->translation;
		$data['content'] = $view->Render('404.phtml');
		return $this->Render($data);
	}
}
?>