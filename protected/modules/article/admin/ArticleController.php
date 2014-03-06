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
		$this->name = "Статьи";
		$this->width=202;
		$this->height=130;
		$this->registry = $registry;
		$this->article = new Article($this->sets);
	}

	public function indexAction()
	{
		$vars['message'] = '';
		$vars['name'] = $this->name;
        if(isset($this->params['subsystem']))return $this->Index($this->article->subsystemAction());
		if(isset($this->registry['access']))$vars['message'] = $this->registry['access'];
		if(isset($this->params['delete'])||isset($_POST['delete']))$vars['message'] = $this->article->delete();
		elseif(isset($_POST['update']))$vars['message'] = $this->article->save();
		elseif(isset($_POST['update_close']))$vars['message'] = $this->article->save();
		elseif(isset($_POST['add_close']))$vars['message'] = $this->article->add();

        $vars['list'] = $this->view->Render('view.phtml', $this->article->find(array('paging'=>true, 'order'=>'tb.date DESC')));
		$data['styles'] = array('timepicker.css');
		$data['scripts'] = array('timepicker.js');
        $data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name));
		$data['content'] = $this->view->Render('list.phtml', $vars);
		return $this->Index($data);
	}
	
	public function addAction()
	{
		$vars['message'] = '';
		if(isset($_POST['add']))$vars['message'] = $this->article->add();

		$vars['width'] = $this->width;
		$vars['height'] = $this->height;
		$data['styles']=array('timepicker.css');
		$data['scripts']=array('timepicker.js');
		$data['content'] = $this->view->Render('add.phtml', $vars);
		return $this->Index($data);
	}
	
	public function editAction()
	{
		$vars['message'] = '';
		if(isset($_POST['update']))$vars['message'] = $this->article->save();
		$vars['edit'] = $this->article->find((int)$this->params['edit']);
		
		/////Load meta
		$row = $this->meta->load_meta($this->tb, $vars['edit']['url']);
		if($row)
		{
			$vars['edit']['title'] = $row['title'];	
			$vars['edit']['keywords'] = $row['keywords'];	
			$vars['edit']['description'] = $row['description'];	
		}
		
		$vars['width'] = $this->width;
		$vars['height'] = $this->height;
		$data['styles']=array('timepicker.css');
		$data['scripts']=array('timepicker.js');
		$data['content'] = $this->view->Render('edit.phtml', $vars);
		return $this->Index($data);
	}
}
?>