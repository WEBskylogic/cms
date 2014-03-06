<?php
/*
 * вывод каталога компаний и их данных
 */
class ArticleController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "article";
		$this->registry = $registry;
		$this->article = new Article($this->sets);
	}
	
	public function indexAction()
	{
        if(!isset($this->params[$this->tb]))header("Location: ".LINK."/".$this->tb."/all");
		$vars['translate'] = $this->translation;

        if(!isset($this->params[$this->tb])||$this->params[$this->tb]=='all')
        {
            $vars['list'] = $this->article->find(array('where'=>'__tb.active:=1__',
                                                        'paging'=>true,
                                                        'order'=>'tb.date DESC'));
        }
		else{
            $vars['news'] = $this->article->find(array('where'=>'__tb.url:='.$this->params[$this->tb].'__ AND __tb.active:=1__'));
            $vars['other'] = $this->article->find(array('where'=>'__tb.id!:='.$vars['news']['id'].'__ AND __tb.active:=1__',
                                                        'type'=>'rows',
                                                        'order'=>'tb.date DESC',
                                                        'limit'=>10));
			
			if(isset($this->settings['comment_article'])&&$this->settings['comment_article']==1)	 
				$vars['comments'] = Comments::getObject($this->sets)->list_comments($vars['news']['id'], $this->tb);
				
			$data['breadcrumbs'] = array('<a href="'.LINK.'/article/all">'.$this->translation['article'].'</a>', $vars['news']['name']);
		}
		
		$data['content'] = $this->view->Render($this->tb.'.phtml', $vars);
		return $this->Index($data);
	}
}
?>