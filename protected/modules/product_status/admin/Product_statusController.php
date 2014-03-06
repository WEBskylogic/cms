<?php
/*
 * вывод каталога компаний и их данных
 */
class Product_statusController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "product_status";
        $this->name = "Статусы товаров";
		$this->registry = $registry;
		$this->status = new Product_status($this->sets);
	}

	public function indexAction()
	{
		$vars['message'] = '';
        $vars['name'] = $this->name;
        if(isset($this->params['subsystem']))return $this->Index($this->status->subsystemAction());
		if(isset($this->registry['access']))$vars['message'] = $this->registry['access'];
		if(isset($this->params['delete'])||isset($_POST['delete']))$vars['message'] = $this->status->delete();
		elseif(isset($_POST['update']))$vars['message'] = $this->status->save();
		elseif(isset($_POST['update_close']))$vars['message'] = $this->status->save();
		elseif(isset($_POST['add_close']))$vars['message'] = $this->status->add();

        $vars['list'] = $this->view->Render('view.phtml', array('list' =>
            $this->status->find( array('type'=>'rows', 'order'=>'tb.id DESC')))
        );
        $data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name));
        $data['content'] = $this->view->Render('list.phtml', $vars);
		return $this->Index($data);
	}
	
	public function addAction()
	{
		$vars['message'] = '';
		if(isset($_POST['add']))$vars['message'] = $this->status->add();
		$data['content'] = $this->view->Render('add.phtml', $vars);
		return $this->Index($data);
	}
}
?>