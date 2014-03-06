<?php
/*
 * вывод каталога компаний и их данных
 */
class InfoController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "info_blocks";
		$this->name = "Информационные блоки";
		$this->registry = $registry;
	    $this->info = new Info($this->sets);
	}

	public function indexAction()
	{
		$vars['message'] = '';
		$vars['name'] = $this->name;
        if(isset($this->params['subsystem']))return $this->Index($this->info->subsystemAction());
		if(isset($this->registry['access']))$vars['message'] = $this->registry['access'];
		if(isset($this->params['delete'])||isset($_POST['delete']))$vars['message'] = $this->info->delete();
		elseif(isset($_POST['update']))$vars['message'] = $this->info->save();
		elseif(isset($_POST['update_close']))$vars['message'] = $this->info->save();
		elseif(isset($_POST['add_close']))$vars['message'] = $this->info->add();
        $vars['list'] = $this->view->Render('view.phtml', array('list' => $this->info->find(array('type'=>'rows','order'=>'tb.sort ASC'))));
        $data['left_menu'] = $this->model->left_menu_admin(array('action'=>'info', 'name'=>$this->name));
		$data['content'] = $this->view->Render('list.phtml', $vars);
		return $this->Index($data);
	}
	
	public function addAction()
	{
		$vars['message'] = '';
		if(isset($_POST['add']))$vars['message'] = $this->info->add();
		$data['content'] = $this->view->Render('add.phtml', $vars);
		return $this->Index($data);
	}
	
	public function editAction()
	{
		//if($vars['message']!='')return Router::act('error');
		$vars['message'] = '';
		if(isset($_POST['update']))$vars['message'] = $this->info->save();
		
		$vars['edit'] = $this->info->find((int)$this->params['edit']);
		if(isset($this->params['duplicate']))$vars['message'] = $this->info->duplicate((int)$vars['edit']);
		$data['content'] = $this->view->Render('edit.phtml', $vars);
		return $this->Index($data);
	}
}
?>