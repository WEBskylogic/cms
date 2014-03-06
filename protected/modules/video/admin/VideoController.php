<?php
/*
 * вывод каталога компаний и их данных
 */
class VideoController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "video";
		$this->name = "Видео";
		$this->registry = $registry;
        $this->video = new Video($this->sets);
	}

	public function indexAction()
	{
		$vars['message'] = '';
		$vars['name'] = $this->name;
        if(isset($this->params['subsystem']))return $this->Index($this->video->subsystemAction());
		if(isset($this->registry['access']))$vars['message'] = $this->registry['access'];
		if(isset($this->params['delete'])||isset($_POST['delete']))$vars['message'] = $this->video->delete();
		elseif(isset($_POST['update']))$vars['message'] = $this->video->save();
		elseif(isset($_POST['update_close']))$vars['message'] = $this->video->save();
		elseif(isset($_POST['add_close']))$vars['message'] = $this->video->add();

        $vars['list'] = $this->view->Render('view.phtml', array('list' =>
            $this->video->find( array('type'=>'rows', 'order'=>'tb.sort ASC, tb.id DESC')))
        );
        $data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name));
		$data['content'] = $this->view->Render('list.phtml', $vars);
		return $this->Index($data);
	}
	
	public function addAction()
	{
		$vars['message'] = '';
		if(isset($_POST['add']))$vars['message'] = $this->video->add();
		$data['content'] = $this->view->Render('add.phtml', $vars);
		return $this->Index($data);
	}
	
	public function editAction()
	{
		$vars['message'] = '';
		if(isset($_POST['update']))$vars['message'] = $this->video->save();
		$vars['edit'] = $this->video->find((int)$this->params['edit']);
		
		/////Load meta
		$row = $this->meta->load_meta($this->tb, $vars['edit']['url']);
		if($row)
		{
			$vars['edit']['title'] = $row['title'];	
			$vars['edit']['keywords'] = $row['keywords'];	
			$vars['edit']['description'] = $row['description'];	
		}
		
		if(isset($this->params['duplicate']))$vars['message'] = $this->video->duplicate($vars['edit']);
		$data['content'] = $this->view->Render('edit.phtml', $vars);
		return $this->Index($data);
	}
}
?>