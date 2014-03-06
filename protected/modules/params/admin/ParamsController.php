<?php
/*
 * вывод каталога компаний и их данных
 */
class ParamsController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "params";
		$this->tb_lang = $this->key_lang_admin.'_'.$this->tb;
        $this->name = "Фильтры товаров";
		$this->registry = $registry;
        $this->filters = new Params($this->sets);
		$this->catalog = new Catalog($this->sets);
	}

	public function indexAction()
	{
        if(isset($this->params['subsystem']))return $this->Index($this->filters->subsystemAction());

		if(isset($_POST['sort_params']))$_SESSION['sort_params']=$_POST['sort_params'];
        if(!isset($_SESSION['sort_params']))$_SESSION['sort_params']=0;
		$vars['message'] = '';
        $vars['name'] = $this->name;
		if(isset($this->registry['access']))$vars['message'] = $this->registry['access'];
		if(isset($this->params['delete'])||isset($_POST['delete']))$vars['message'] = $this->filters->delete();
		elseif(isset($_POST['update']))$vars['message'] = $this->filters->save();
		elseif(isset($_POST['update_close']))$vars['message'] = $this->filters->save();
		elseif(isset($_POST['add_close']))$vars['message'] = $this->filters->add();

        //Вывод списка в <select>-e
        $vars['params'] = $this->filters->find(array('type'=>'rows','where'=>'tb.sub is NULL','order'=>'tb.sort ASC'));

        //Фильтрация согласно выбраного родителя в <select>-e
        $where="tb.sub is NULL";
        if($_SESSION['sort_params']!=0)$where="tb.sub='{$_SESSION['sort_params']}' ";

        //Вывод списка фильтров
        $vars['list'] = $this->view->Render('view.phtml', array('list' =>
                        $this->filters->find( array('type'=>'rows',
                                                    'where'=>$where,
                                                    'order'=>'tb.sort ASC, tb.id DESC'))));

        $data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name));
		$data['content'] = $this->view->Render('list.phtml', $vars);
		return $this->Index($data);
	}
	
	public function editAction()
	{
		$vars['message'] = '';
		if(isset($_POST['update']))$vars['message'] = $this->filters->save();
		$vars['edit'] = $this->filters->find((int)$this->params['edit']);
		$vars['type'] = $this->db->row("SELECT type FROM params WHERE id='{$vars['edit']['sub']}'");
		$vars['types'] = $this->db->rows("SELECT * FROM params_type WHERE active='1' ORDER BY sort ASC");
		
        //Список выбора раздела
        $where = "tb.sub is NULL and tb.id!=".(int)$this->params['edit'];
        $vars['params'] = $this->filters->find(array('where'=>$where,
                                                     'type'=>'rows',
                                                     'order'=>'tb.sort ASC'));
		
		if($vars['type']['type']=='color')
		{
			$data['scripts'] = array('colorpicker.js', 'eye.js', 'utils.js', 'layout.js');
			$data['styles'] = array('colorpicker.css');
		}
		
		$data['content'] = $this->view->Render('edit.phtml', $vars);
		return $this->Index($data);
	}
	
	public function addAction()
	{
		$vars['message'] = '';
		if(isset($_POST['add']))$vars['message'] = $this->filters->add();
		$vars['types'] = $this->db->rows("SELECT * FROM params_type WHERE active='1' ORDER BY sort ASC");
        $vars['params'] = $this->filters->find(array('type'=>'rows',
                                                	 'where'=>'tb.sub is NULL',
                                                	 'order'=>'tb.sort ASC'));
		
		$data['scripts'] = array('colorpicker.js', 'eye.js', 'utils.js', 'layout.js');
		$data['styles'] = array('colorpicker.css');
		$data['content'] = $this->view->Render('add.phtml', $vars);
		return $this->Index($data);
	}
}
?>