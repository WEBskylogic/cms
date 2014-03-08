<?php
/*
 * вывод каталога компаний и их данных
 */
class NewsController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "news";
		$this->registry = $registry;
		$this->news = new News($this->sets);
	}
	
	public function indexAction()
	{
		$vars['translate'] = $this->translation;
		if(!isset($this->params[$this->tb])||$this->params[$this->tb]=='all')
		{
			$vars['list'] = $this->news->find(array('where'=>'__tb.active:=1__', 
													'paging'=>$this->settings['paging_news'], 
													'order'=>'tb.date DESC'));
		}
		else{
			$vars['news'] = $this->news->find(array('where'=>'__tb.url:='.$this->params[$this->tb].'__ AND __tb.active:=1__'));
			if(!isset($vars['news']['id']))return Router::act('error', $this->registry);
			$vars['other'] = $this->news->find(array('where'=>'__tb.id!:='.$vars['news']['id'].'__ AND __tb.active:=1__', 
													 'type'=>'rows', 
													 'order'=>'tb.date DESC', 
													 'limit'=>5));

			if(isset($this->settings['comment_news'])&&$this->settings['comment_news']==1)	 
				$vars['comments'] = Comments::getObject($this->sets)->list_comments($vars['news']['id'], $this->tb);
			//var_info($vars['other']);
		}
		
		$data['content'] = $this->view->Render($this->tb.'.phtml', $vars);
		return $this->Index($data);
	}
}
?>