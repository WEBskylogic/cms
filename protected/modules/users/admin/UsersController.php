<?php
/*
 * вывод каталога компаний и их данных
 */
class UsersController extends BaseController{
	
	protected $params;
	protected $db;
	private $left_menu = array(array('title'=>'Статусы пользователей', 
									 'url'=>'/admin/users/act/usertype', 
									 'name'=>'usertype')
							   );
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "users";
		$this->name = "Покупатели";
		$this->registry = $registry;
		$this->users = new Users($this->sets);
		$this->orders = new Orders($this->sets);
	}
	
	public function indexAction()
	{
		$vars['message'] = '';
		$vars['name'] = $this->name;
		
		if(isset($this->params['act']))
		{
			$act = $this->params['act'].'Action';
			return $this->Index($this->$act());
		}
		
		if(isset($this->params['subsystem']))return $this->Index($this->users->subsystemAction($this->left_menu));
		if(isset($this->registry['access']))$vars['message'] = $this->registry['access'];
		if(isset($this->params['delete'])||isset($_POST['delete']))$vars['message'] = $this->users->delete();
		elseif(isset($_POST['update']))$vars['message'] = $this->users->save();
		elseif(isset($_POST['update_close']))$vars['message'] = $this->users->save();
		elseif(isset($_POST['add_open']))$vars['message'] = $this->users->add(true);

		$vars['status'] = $this->db->rows("SELECT * FROM user_status ORDER BY name ASC");
		$vars['list'] = $this->view->Render('view.phtml', $this->users->listView());
		$data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name, 'menu2'=>$this->left_menu));
		$data['content'] = $this->view->Render('list.phtml', $vars);
		return $this->Index($data);
	}
	
	public function addAction()
	{
		$vars['message'] = '';
		if(isset($_POST['add']))$vars['message'] = $this->users->add();
		
		$vars['price_type'] = $this->db->row("SELECT id FROM user_status WHERE `default`='1'");
		$vars['status'] = $this->db->rows("SELECT * FROM user_status ORDER BY id ASC");
		$data['content'] = $this->view->Render('add.phtml', $vars);
		return $this->Index($data);
	}
	
	public function editAction()
	{
		/////////////
		if(isset($this->params['del'])||isset($_POST['del']))
		{
			$table='bascket';
			if(isset($_POST['del'])&&is_array($_POST['bascket_id']))
			{
				$count=count($_POST['bascket_id']) - 1;
				for($i=0; $i<=$count; $i++)
				{
					$this->db->query("DELETE FROM `".$table."` WHERE `id`=?", array($_POST['bascket_id'][$i]));
				}
				$message = messageAdmin('Запись успешно удалена');
			}
			elseif(isset($this->params['del'])&& $this->params['del']!='')
			{
				$id = $this->params['del'];
				if($this->db->query("DELETE FROM `".$table."` WHERE `id`=?", array($id)))$message = messageAdmin('Запись успешно удалена');
			}
		}
		
		if(isset($this->params['delorder'])||isset($_POST['del_orders']))
		{
			$table='orders';
			if(isset($_POST['del_orders'])&&is_array($_POST['order_id']))
			{
				$count=count($_POST['order_id']) - 1;
				for($i=0; $i<=$count; $i++)
				{
					$this->db->query("DELETE FROM `".$table."` WHERE `id`=?", array($_POST['order_id'][$i]));
				}
				$message = messageAdmin('Запись успешно удалена');
			}
			elseif(isset($this->params['delorder'])&& $this->params['delorder']!='')
			{
				$id = $this->params['delorder'];
				if($this->db->query("DELETE FROM `".$table."` WHERE `id`=?", array($id)))$message = messageAdmin('Запись успешно удалена');
			}
		}
		
		$vars = $this->orders->incomplete($this->params['edit']);
							
		$data['styles'] = array('jquery.simple-dtpicker.css');
		$data['scripts'] = array('jquery.simple-dtpicker.js');									
		$vars['incomplete'] = $this->view->Render('incomplete.phtml', $vars);
		/////////////
		
		
		//if($vars['message']!='')return Router::act('error');
		$vars['message'] = '';
		if(isset($_POST['update']))$vars['message'] = $this->users->save();

		$vars['currency'] = $this->db->row("SELECT icon FROM currency WHERE `base`='1'");
		$vars['list']['list'] = $this->orders->find(array('type'=>'rows', 
												  'where'=>"tb.user_id='".$this->params['edit']."'", 
												  'order'=>'tb.date_add DESC', 
												  'select'=>'tb.*, tb2.name as status',
												  'join'=>'LEFT JOIN orders_status tb2 ON tb.status_id=tb2.id'));
		$vars['orders'] = $this->view->Render('orders.phtml', $vars);										  
		/////////
		
		
		$vars['edit'] = $this->users->find(array('where'=>'__tb.id:='.$this->params['edit'].'__', 
												  'select'=>'tb.*, tb2.name as status',
												  'join'=>' LEFT JOIN user_status tb2 ON tb.status_id=tb2.id'));							  					  
		$vars['status'] = $this->db->rows("SELECT * FROM user_status ORDER BY id ASC");
		$vars['sum'] = $this->db->row("SELECT SUM(`sum`) as total, COUNT(*) as cnt FROM `orders` WHERE user_id=?", array($vars['edit']['id']));
		$data['content'] = $this->view->Render('edit.phtml', $vars);
		return $this->Index($data);
	}
	
	public function usertypeAction()
	{
		$vars['message'] = '';
		if(isset($_POST['update']))
		{
			$vars['message'] = $this->users->save_usertype();
		}
		elseif(isset($this->params['addusertype']))
		{
			$vars['message'] = $this->users->addusertype();
		}
		elseif(isset($this->params['delete'])||isset($_POST['delete']))
		{
			$vars['message'] = $this->users->delete('user_status');
		}
		
		$vars['name'] = 'Статусы пользователей';
		$vars['action'] = $this->tb;
		$vars['path'] = '/act/usertype';
		$vars['price_type'] = $this->db->rows("SELECT * FROM `price_type` ORDER BY id ASC");
		$vars['list'] = $this->db->rows("SELECT * FROM `user_status` ORDER BY id ASC");
		
		$data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name, 'sub'=>'usertype', 'menu2'=>$this->left_menu));
		$data['content'] = $this->view->Render('user_type.phtml', $vars);
		return $data;
	}
}
?>