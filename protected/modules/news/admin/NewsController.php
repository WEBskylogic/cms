<?php
/*
 * вывод каталога компаний и их данных
 */
class NewsController extends BaseController{
	
	protected $params;
	protected $registry;

	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "news";
		$this->name = "Новости";
		$this->registry = $registry;
		$this->news = new News($this->sets);
	}

	public function indexAction()
	{
		if(isset($this->params['subsystem']))return $this->Index($this->news->subsystemAction());
		$vars['message'] = '';
		$vars['name'] = $this->name;
		if(isset($this->registry['access']))$vars['message'] = $this->registry['access'];
		if(isset($this->params['delete'])||isset($_POST['delete']))$vars['message'] = $this->news->delete();
		elseif(isset($_POST['update']))$vars['message'] = $this->news->save();
		elseif(isset($_POST['update_close']))$vars['message'] = $this->news->save();
		elseif(isset($_POST['add_close']))$vars['message'] = $this->news->add();
							   
		$vars['list'] = $this->view->Render('view.phtml', $this->news->find(array('paging'=>$this->settings['paging_news_admin'], 'order'=>'tb.date DESC')));
		
		$data['styles'] = array('jquery.simple-dtpicker.css');
		$data['scripts'] = array('jquery.simple-dtpicker.js');
		
		$data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name));
		$data['content'] = $this->view->Render('list.phtml', $vars);
		return $this->Index($data);
	}
	
	public function addAction()
	{
		$vars['message'] = '';
		if(isset($_POST['add']))$vars['message'] = $this->news->add();
		$vars['width'] = $this->settings['width_news'];
        $vars['height'] = $this->settings['height_news'];
		$data['styles'] = array('jquery.simple-dtpicker.css');
		$data['scripts'] = array('jquery.simple-dtpicker.js');
		$data['content'] = $this->view->Render('add.phtml', $vars);
		return $this->Index($data);
	}
	
	public function editAction()
	{
		$vars['message'] = '';
		if(isset($_POST['update']))$vars['message'] = $this->news->save();

		$vars['edit'] = $this->news->find((int)$this->params['edit']);
		
		/////Load meta
		$row = $this->meta->load_meta($this->tb, $vars['edit']['url']);
		if($row)
		{
			$vars['edit']['title'] = $row['title'];	
			$vars['edit']['keywords'] = $row['keywords'];	
			$vars['edit']['description'] = $row['description'];	
		}
		
		////Show tab comment
		$this->comments = new Comments($this->sets);
		$vars['comments']=$this->comments->list_comments_admin($vars['edit']['id'], $this->tb);
		
		////Загрузка фоток для текушего альбома
		$vars['width'] = $this->settings['width_news'];
        $vars['height'] = $this->settings['height_news'];
		$vars['action']=$this->tb;
		$vars['path']="files/news/";	
		//////////////
		
		$data['styles'] = array('jquery.simple-dtpicker.css');
		$data['scripts'] = array('jquery.simple-dtpicker.js');
		$data['content'] = $this->view->Render('edit.phtml', $vars);
		return $this->Index($data);
	}
}
?>