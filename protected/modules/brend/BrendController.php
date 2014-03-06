<?php
/*
 * вывод каталога компаний и их данных
 */
class BrendController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "brend";
		$this->registry = $registry;
		$this->brend = new Brend($this->sets);
	}
	
	public function indexAction()
	{
		$vars['translate'] = $this->translation;
		if(!isset($this->params[$this->tb]))header("Location: ".LINK."/".$this->tb."/all");
	
		if(!isset($this->params[$this->tb])||$this->params[$this->tb]=='all')
		{
			$vars['list'] = $this->brend->find(array('where'=>'__tb.active:=1__', 
													 'paging'=>true, 
													 'order'=>'tb.sort ASC'));
			$data['breadcrumbs'] = array($this->translation['brend']);
		}
		else{
		   $vars['brend'] = $this->brend->find(array('where'=>'__tb.url:='.$this->params[$this->tb].'__ AND __tb.active:=1__'));
		   if(!$vars['brend'])return Router::act('error', $this->registry);
	
			$data['breadcrumbs'] = array('<a href="'.LINK.'/brend/all">'.$this->translation['brend'].'</a>', $vars['brend']['name']);
			$data['meta'] = $vars['brend'];
		}
	  
	   $data['content'] = $this->view->Render($this->tb.'.phtml', $vars);
	   return $this->Index($data);
	}
}
?>