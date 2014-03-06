<?php
/*
 * вывод каталога компаний и их данных
 */
class PaymentController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "payment";
        $this->name = "Способ оплаты";
		$this->registry = $registry;
        $this->payment = new Payment($this->sets);
	}

	public function indexAction()
	{
		$vars['message'] = '';
        $vars['name'] = $this->name;
        if(isset($this->params['subsystem']))return $this->Index($this->payment->subsystemAction());
		if(isset($this->registry['access']))$vars['message'] = $this->registry['access'];
		if(isset($this->params['delete'])||isset($_POST['delete']))$vars['message'] = $this->payment->delete();
		elseif(isset($_POST['update']))$vars['message'] = $this->payment->save();
		elseif(isset($_POST['update_close']))$vars['message'] = $this->payment->save();
		elseif(isset($_POST['add_close']))$vars['message'] = $this->payment->add();

        $vars['list'] = $this->view->Render('view.phtml', array('list' =>
            $this->payment->find( array('type'=>'rows', 'order'=>'tb.sort ASC')))
        );
        $data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name));
		$data['content'] = $this->view->Render('list.phtml', $vars);
		return $this->Index($data);
	}
	
	public function addAction()
	{
		$vars['message'] = '';
		if(isset($_POST['add']))$vars['message'] = $this->payment->add();
		$data['content'] = $this->view->Render('add.phtml', $vars);
		return $this->Index($data);
	}
}
?>