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
		$this->name = "Бренды";
		$this->width=160;
        $this->height=60;
		$this->registry = $registry;
		$this->brend = new Brend($this->sets);
	}

	public function indexAction()
	{
		$vars['message'] = '';
		$vars['name'] = $this->name;
		if(isset($this->params['subsystem']))return $this->Index($this->brend->subsystemAction());
		if(isset($this->registry['access']))$vars['message'] = $this->registry['access'];
		if(isset($this->params['delete'])||isset($_POST['delete']))$vars['message'] = $this->brend->delete();
		elseif(isset($_POST['update']))$vars['message'] = $this->brend->save();
		elseif(isset($_POST['update_close']))$vars['message'] = $this->brend->save();
		elseif(isset($_POST['add_close']))$vars['message'] = $this->brend->add();

		$vars['list'] = $this->view->Render('view.phtml', $this->brend->find(array('paging'=>true, 'order'=>'tb.sort ASC')));
		$data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name));
		$data['content'] = $this->view->Render('list.phtml', $vars);
		return $this->Index($data);
	}
	
	public function addAction()
	{
		$vars['message'] = '';
		if(isset($_POST['add']))$vars['message'] = $this->brend->add();
		$data['content'] = $this->view->Render('add.phtml', $vars);
		return $this->Index($data);
	}
	
	public function editAction()
	{
		//if($vars['message']!='')return Router::act('error');
		$vars['message'] = '';
		if(isset($_POST['update']))$vars['message'] = $this->brend->save();
		
		$vars['height']=$this->height;	
		$vars['width']=$this->width;
		
		$vars['edit'] = $this->brend->find((int)$this->params['edit']);
		if(isset($this->params['duplicate']))$vars['message'] = $this->brend->duplicate($vars['edit'], $this->tb);								
		$data['content'] = $this->view->Render('edit.phtml', $vars);
		return $this->Index($data);
	}
}
?>