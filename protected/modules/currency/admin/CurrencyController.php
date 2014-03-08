<?php
/*
 * вывод каталога компаний и их данных
 */
class CurrencyController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "currency";
        $this->name = "Курс валюты";
		$this->registry = $registry;
		$this->currency = new Currency($this->sets);
	}

	public function indexAction()
	{
		$vars['message'] = '';
        $vars['name'] = $this->name;
        if(isset($this->params['subsystem']))return $this->Index($this->currency->subsystemAction());
		if(isset($this->registry['access']))$vars['message'] = $this->registry['access'];
		if(isset($this->params['delete'])||isset($_POST['delete']))$vars['message'] = $this->currency->delete();
		elseif(isset($_POST['update']))$vars['message'] = $this->currency->save();
		elseif(isset($_POST['update_close']))$vars['message'] = $this->currency->save();
		elseif(isset($_POST['add_close']))$vars['message'] = $this->currency->add();

        $vars['list'] = $this->view->Render('view.phtml', array(
                        'list' => $this->currency->find(array('type'=>'rows', 'order'=>'tb.id ASC'))));

        $data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name));
		$data['content'] = $this->view->Render('list.phtml', $vars);
		return $this->Index($data);
	}
	
	public function addAction()
	{
		$vars['message'] = '';
		if(isset($_POST['add']))$vars['message'] = $this->currency->add();
		$data['content'] = $this->view->Render('add.phtml', $vars);
		return $this->Index($data);
	}
}
?>