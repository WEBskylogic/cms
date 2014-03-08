<?php
/*
 * вывод каталога компаний и их данных
 */
class DeliveryController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "delivery";
        $this->name = "Способ доставки";
		$this->registry = $registry;
        $this->delivery = new Delivery($this->sets);
	}

	public function indexAction()
	{
        if(isset($this->params['subsystem']))return $this->Index($this->delivery->subsystemAction());
		$vars['message'] = '';
        $vars['name'] = $this->name;
		if(isset($this->registry['access']))$vars['message'] = $this->delivery->registry['access'];
		if(isset($this->params['delete'])||isset($_POST['delete']))$vars['message'] = $this->delivery->delete();
		elseif(isset($_POST['update']))$vars['message'] = $this->delivery->save();
		elseif(isset($_POST['update_close']))$vars['message'] = $this->delivery->save();
		elseif(isset($_POST['add_close']))$vars['message'] = $this->delivery->add();

       $vars['list'] = $this->view->Render('view.phtml', array(
                'list' => $this->delivery->find(array('type'=>'rows', 'order'=>'tb.sort ASC')))
        );
		$data['content'] = $this->view->Render('list.phtml', $vars);
        $data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name));
		return $this->Index($data);
	}

	public function addAction()
	{
		$vars['message'] = '';
		if(isset($_POST['add']))$vars['message'] = $this->delivery->add();
		$data['content'] = $this->view->Render('add.phtml', $vars);
		return $this->Index($data);
	}
}
?>