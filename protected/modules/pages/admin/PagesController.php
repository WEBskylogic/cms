<?php
/*
 * вывод каталога компаний и их данных
 */
class PagesController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "pages";
		$this->name = "Содержимое";
		$this->tb_lang = $this->key_lang_admin.'_'.$this->tb;
		$this->registry = $registry;
        $this->pages = new Pages($this->sets);
	}

	public function indexAction()
	{
		$vars['message'] = '';
		$vars['name'] = $this->name;
        if(isset($this->params['subsystem']))return $this->Index($this->pages->subsystemAction());
		if(isset($this->registry['access']))$vars['message'] = $this->pages->registry['access'];
		if(isset($this->params['delete'])||isset($_POST['delete']))$vars['message'] = $this->pages->delete();
		elseif(isset($_POST['update']))$vars['message'] = $this->pages->save();
		elseif(isset($_POST['update_close']))$vars['message'] = $this->pages->save();
		elseif(isset($_POST['add_close']))$vars['message'] = $this->pages->add();

        $vars['list'] = $this->view->Render('view.phtml', array('list' =>
                        $this->pages->find( array('type'=>'rows', 'order'=>'tb.sort ASC, tb.id DESC'))));
        $data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name));
		$data['content'] = $this->view->Render('list.phtml', $vars);
		return $this->Index($data);
	}
	
	public function addAction()
	{
		$vars['message'] = '';
		if(isset($_POST['add']))$vars['message'] = $this->pages->add();
		
		//Check if isset comments module
		$row = $this->db->row("SELECT id FROM modules WHERE `controller`=?", array('comments'));
		if($row)
		{
			$vars['comments']=true;
		}
		
		$data['content'] = $this->view->Render('add.phtml', $vars);
		return $this->Index($data);
	}
	
	public function editAction()
	{
		$vars['message'] = '';
		if(isset($_POST['update']))$vars['message'] = $this->pages->save();

        $vars['edit'] = $this->pages->find((int)$this->params['edit']);
		
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

        if(isset($this->params['duplicate']))$vars['message'] = $this->pages->duplicate($vars['edit'], $this->tb);
		$data['content'] = $this->view->Render('edit.phtml', $vars);
		return $this->Index($data);
	}
}
?>