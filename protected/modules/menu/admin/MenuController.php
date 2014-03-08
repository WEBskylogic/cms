<?php
/*
* редактирвоание главного меню
*/
class MenuController extends BaseController
{
	protected $params;
	protected $registry;
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->key_lang_admin = $this->registry['key_lang_admin'];//Current language
		
		$this->name = "Меню";
		$this->tb = "menu";
		$this->menu = new Menu($this->sets);
	}

	public function indexAction()
	{
		$vars['name'] = $this->name;
		if(isset($this->params['subsystem']))return $this->Index($this->menu->subsystemAction());
		if(isset($_POST['sort_menu']))$_SESSION['sort_menu'] = $_POST['sort_menu'];
        if(!isset($_SESSION['sort_menu']))$_SESSION['sort_menu'] = 0;
		$vars['message'] = '';
		
		if(isset($this->registry['access']))$vars['message'] = $this->registry['access'];
		if(isset($this->params['delete'])||isset($_POST['delete']))$vars['message'] = Menu::getObject($this->sets)->delete();
		elseif(isset($_POST['update']))$vars['message'] = Menu::getObject($this->sets)->save();
		elseif(isset($_POST['update_close']))$vars['message'] = Menu::getObject($this->sets)->save();
		elseif(isset($_POST['add_close']))$vars['message'] = Menu::getObject($this->sets)->add();
		
		$where="tb.sub is NULL";
        if($_SESSION['sort_menu']!=0)$where="__tb.sub:={$_SESSION['sort_menu']}__";
		
		$vars['select_tree'] =$this->model->select_tree($this->tb, $_SESSION['sort_menu']); 
		$vars['menu'] = Menu::getObject($this->sets)->find(array('type'=>'rows', 'order'=>'tb.sort ASC, tb.id ASC'));
		$vars['list'] = $this->view->Render('view.phtml', Menu::getObject($this->sets)->find(array('paging'=>true, 
																		   'where'=>$where,
																		   'order'=>'tb.sort ASC, tb.id ASC')));
		$data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name));
		$data['content'] = $this->view->Render('list.phtml', $vars);
		return $this->Index($data);
	}

	public function addAction()
	{
		$vars['message'] = '';
		if(isset($_POST['add']))$vars['message'] = Menu::getObject($this->sets)->add(); 
		$vars['select_tree'] =$this->model->select_tree($this->tb, $_SESSION['sort_menu']); 
		$data['content'] = $this->view->Render('add.phtml', $vars);
		return $this->Index($data);
	}

	public function editAction()
	{
		$vars['message'] = '';
		if(isset($_POST['update']))$vars['message'] = Menu::getObject($this->sets)->save();
		$vars['edit'] = Menu::getObject($this->sets)->find((int)$this->params['edit']);
		
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
		
		$vars['select_tree'] = $this->model->select_tree($this->tb, $vars['edit']['sub']); 
		$data['content'] = $this->view->Render('edit.phtml', $vars);
		return $this->Index($data);
	}
}
?>