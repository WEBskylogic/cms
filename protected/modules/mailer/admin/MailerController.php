<?php
/*
* Рассылка сообщений
*/

class MailerController extends BaseController
{
	protected $params;
	protected $db;
	private $left_menu = array();

	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->registry = $registry; 	
		$this->tb = "mailer";
		$this->name = "Модуль рассылки";
		$this->queue = "mail_queue";
		$this->mailer = new Mailer($this->sets);
	}

	public function index2Action()
	{
		if(isset($_POST['sort_menu']))$_SESSION['sort_menu']=$_POST['sort_menu'];
        if(!isset($_SESSION['sort_menu']))$_SESSION['sort_menu']=0;
		$vars['message'] = '';
		
		if(isset($this->params['act']))
		{
			$act=$this->params['act'].'Action';
			return $this->Index($this->$act());
		}
		
		if(isset($this->registry['access']))$vars['message'] = $this->registry['access'];
		if(isset($this->params['subsystem']))return $this->Index($this->mailer->subsystemAction($this->left_menu));
		if(isset($this->params['delete'])||isset($_POST['delete']))$vars['message'] = $this->mailer->delete();
		elseif(isset($_POST['update']))$vars['message'] = $this->mailer->save();
		elseif(isset($_POST['update_close']))$vars['message'] = $this->mailer->save();
		elseif(isset($_POST['add_close']))$vars['message'] = $this->mailer->add($this->queue);

		$vars['list'] = $this->view->Render('view.phtml', $this->mailer->listView());
		$vars['pages'] = $this->mailer->find(array('select'=>'tb.id, tb_lang.name',
												   'order'=>'tb.sort ASC'));
		$data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name, 'menu2'=>$this->left_menu));
		$data['content'] = $this->view->Render('list.phtml', $vars);
		return $this->Index($data);
	}

	public function addAction()
	{
		$vars['message'] = '';
		$vars['users'] = $this->db->rows("SELECT `mailer`, `email`, `id` FROM `users`");
		if(isset($_POST['add']))$vars['message'] = $this->mailer->add($this->queue); 

		$data['content'] = $this->view->Render('add.phtml', $vars);
		return $this->Index($data);
	}

	public function editAction()
	{
		//if($vars['message']!='')return Router::act('error');
		$vars['message'] = '';
		if(isset($_POST['update']))$vars['message'] = $this->mailer->save();
		
		if(isset($_POST['deleteusers']))
		{
			$this->mailer->deleteusers();
		}
		
		$vars['edit'] = $this->mailer->find((int)$this->params['edit']);
		$vars['list'] = $this->mailer->listUsersView($this->params['edit']); 
		$vars['users'] =$this->db->rows("SELECT `mailer`, `email`, `id` FROM `users`");
		$data['content'] = $this->view->Render('edit.phtml', $vars);
		return $this->Index($data);
	}

	public function subscribersAction()
	{
		$vars['message'] = '';
		if(isset($_POST['update']))
		{
			$vars['message'] = $this->mailer->save_subscribers();
		}
		elseif(isset($this->params['addsubscribers']))
		{
			$vars['message'] = $this->mailer->add_subscribers();
		}
		elseif(isset($this->params['delete'])||isset($_POST['delete']))
		{
			$vars['message'] = $this->mailer->delete('subscribers');
		}
		
		$vars['name'] = 'Подписчики';
		$vars['action'] = $this->tb;
		$vars['path'] = '/act/subscribers';
		$vars['list'] = $this->mailer->find(array('table'=>'subscribers',
												  'order'=>'date_add ASC',
												  'type'=>'rows',
												  'paging'=>true));
		$data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name, 'sub'=>'subscribers', 'menu2'=>$this->left_menu));
		$data['content'] = $this->view->Render('subscribers.phtml', $vars);
		return $data;
	}
	
	public function indexAction()
	{
		$vars['message'] = '';
		if(isset($_POST['update']))
		{
			$vars['message'] = $this->mailer->save_subscribers();
		}
		elseif(isset($this->params['addsubscribers']))
		{
			$vars['message'] = $this->mailer->add_subscribers();
		}
		elseif(isset($this->params['delete'])||isset($_POST['delete']))
		{
			$vars['message'] = $this->mailer->delete('subscribers');
		}
		
		$vars['name'] = 'Подписчики';
		$vars['action'] = $this->tb;
		$vars['path'] = '/act/subscribers';
		$vars['list'] = $this->mailer->find(array('table'=>'subscribers',
												  'order'=>'date_add ASC',
												  'type'=>'rows',
												  'paging'=>true));
		$data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name,  'menu2'=>$this->left_menu));
		$data['content'] = $this->view->Render('subscribers.phtml', $vars);
		return $this->Index($data);
	}
}
?>