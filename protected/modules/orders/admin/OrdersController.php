<?php
/*
 * вывод каталога компаний и их данных
 */
class OrdersController extends BaseController{
	
	protected $params;
	protected $db;
	private $left_menu = array(array('title'=>'Незавершенные заказы', 
									 'url'=>'/admin/orders/act/incomplete', 
									 'name'=>'incomplete'));
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "orders";
		$this->name = "Заказы";
		$this->registry = $registry;
		$this->orders = new Orders($this->sets);
	}

	public function indexAction()
	{
		$vars['message'] = '';
		$vars['name'] = $this->name;
		if(isset($this->params['act']))
		{
			$act=$this->params['act'].'Action';
			return $this->Index($this->$act());
		}
		
		if(isset($this->params['subsystem']))return $this->Index($this->orders->subsystemAction());
		if(isset($this->registry['access']))$vars['message'] = $this->orders->registry['access'];
		if(isset($this->params['delete'])||isset($_POST['delete']))$vars['message'] = $this->orders->delete();
		elseif(isset($_POST['update']))$vars['message'] = $this->orders->save();
		elseif(isset($_POST['update_close']))$vars['message'] = $this->orders->save();
		elseif(isset($_POST['add_close']))$vars['message'] = $this->orders->add();
		
		$vars['currency'] = $this->db->row("SELECT icon FROM currency WHERE `base`='1'");
		$vars['list'] = $this->orders->find(array('paging'=>true, 
												  'order'=>'tb.date_add DESC', 
												  'select'=>'tb.*, tb2.name as status',
												  'join'=>'LEFT JOIN orders_status tb2 ON tb.status_id=tb2.id'));
												  
		$vars['list'] = $this->view->Render('view.phtml', $vars);
		$data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name, 'menu2'=>$this->left_menu));
		$data['content'] = $this->view->Render('list.phtml', $vars);
		return $this->Index($data);
	}
	
	public function addAction()
	{
		$vars['message'] = '';
		if(isset($_POST['add']))$vars['message'] = $this->orders->add();
		
		////Delivery
		$row = $this->db->row("SELECT id FROM modules WHERE `controller`=?", array('delivery'));
		if($row)$vars['delivery'] = Delivery::getObject($this->sets)->find(array('type'=>'rows', 'where'=>'__tb.active:=1__', 'order'=>'tb.sort ASC'));
		
		////Payment
		$row = $this->db->row("SELECT id FROM modules WHERE `controller`=?", array('payment'));
		if($row)$vars['payment'] = Payment::getObject($this->sets)->find(array('type'=>'rows', 'where'=>'__tb.active:=1__', 'order'=>'tb.sort ASC'));
		$data['content'] = $this->view->Render('add.phtml', $vars);
		return $this->Index($data);
	}
	
	public function editAction()
	{
		$vars['message'] = '';		
		if(isset($this->params['del']))
		{
			$vars['message'] = $this->orders->del_product($this->params['del']);
		}
		
		if(isset($_POST['update']))$vars['message'] = $this->orders->save();
		$vars['status'] = $this->db->rows("SELECT * FROM orders_status");
		$vars['product'] = $this->db->rows("SELECT * FROM orders_product WHERE orders_id=?", array($this->params['edit']));//var_info($vars['product']);
		$vars['catalog'] = Catalog::getObject($this->sets)->find(array('type'=>'rows', 'where'=>'__tb.active:=1__', 'order'=>'tb.sort ASC'));
		$vars['edit'] = $this->orders->find((int)$this->params['edit']);
		
		////Delivery
		$row = $this->db->row("SELECT id FROM modules WHERE `controller`=?", array('delivery'));
		if($row)$vars['delivery'] = Delivery::getObject($this->sets)->find(array('type'=>'rows', 'where'=>'__tb.active:=1__', 'order'=>'tb.sort ASC'));
		
		////Payment
		$row = $this->db->row("SELECT id FROM modules WHERE `controller`=?", array('payment'));
		if($row)$vars['payment'] = Payment::getObject($this->sets)->find(array('type'=>'rows', 'where'=>'__tb.active:=1__', 'order'=>'tb.sort ASC'));
		
		$vars['currency'] = $this->db->row("SELECT icon FROM currency WHERE `base`='1'");											
		$data['content'] = $this->view->Render('edit.phtml', $vars);
		return $this->Index($data);
	}
	
	public function incompleteAction()
	{
		$vars = $this->orders->incomplete();
											
		$data['styles'] = array('jquery.simple-dtpicker.css');
		$data['scripts'] = array('jquery.simple-dtpicker.js');									
		$data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name, 'sub'=>'incomplete', 'menu2'=>$this->left_menu));
		$data['content'] = $this->view->Render('incomplete.phtml', $vars);
		return $data;
	}
	
	
	/////Load list product in select
	function orderProductAction()
    {
		return Orders::getObject($this->sets)->orderProduct();
    }
	
	////Load table product view
	function orderProductViewAction()
    {
		$this->registry->set('admin', 'orders');
		$data = Orders::getObject($this->sets)->orderProductView();
		$data['content']=$this->view->Render('orderproduct.phtml', array('product'=>$data['res'], 'total'=>$data['total'], 'currency'=>$data['currency']));
		return json_encode($data);
    }
}
?>