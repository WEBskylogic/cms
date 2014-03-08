<?php
/*
 * вывод каталога компаний и их данных
 */
class MetaController extends BaseController{
	
	protected $params;
	protected $db;
	private $left_menu = array(array('title'=>'Сео-настройки', 
									 'url'=>'/admin/meta/act/seoconfig', 
									 'name'=>'seoconfig'),
							   array('title'=>'Перенаправления', 
									 'url'=>'/admin/meta/act/redirects', 
									 'name'=>'redirects'),
							   array('title'=>'Поиск ссылок', 
									 'url'=>'/admin/meta/act/searchlink', 
									 'name'=>'searchlink'),
							   array('title'=>'robots.txt', 
									 'url'=>'/admin/meta/act/robots', 
									 'name'=>'robots')
							   );
							   
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "meta";
		$this->name = "Мета-данные";
		$this->registry = $registry;
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
		if(isset($this->params['subsystem']))return $this->Index($this->meta->subsystemAction($this->left_menu));
		if(isset($this->registry['access']))$vars['message'] = $this->registry['access'];
		if(isset($this->params['delete'])||isset($_POST['delete']))$vars['message'] = $this->meta->delete();
		elseif(isset($_POST['update']))$vars['message'] = $this->meta->save();
		elseif(isset($_POST['update_close']))$vars['message'] = $this->meta->save();
		elseif(isset($_POST['add_close']))$vars['message'] = $this->meta->add();

		$vars['list'] = $this->view->Render('view.phtml', $this->meta->find(array('select'=>'tb.*', 
																				  'order'=>'tb.`id` DESC',
																				  'type'=>'rows',
																				  'paging'=>true
																				)));
		$data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name, 'menu2'=>$this->left_menu));
		$data['content'] = $this->view->Render('list.phtml', $vars);
		return $this->Index($data);
	}
	
	public function addAction()
	{
		$vars['message'] = '';
		if(isset($_POST['add']))$vars['message'] = $this->meta->add();

		$data['content'] = $this->view->Render('add.phtml', $vars);
		return $this->Index($data);
	}
	
	public function editAction()
	{
		$vars['message'] = '';
		if(isset($_POST['update']))$vars['message'] = $this->meta->save();
		
		$vars['edit'] = $this->meta->find((int)$this->params['edit']);
		if(isset($this->params['duplicate']))$vars['message'] = $this->meta->duplicate($vars['edit'], $this->tb);								
		$data['content'] = $this->view->Render('edit.phtml', $vars);
		return $this->Index($data);
	}
	
	public function seoconfigAction()
	{
		$vars['message'] = '';
		if(isset($_POST['update']))
		{
			$vars['message'] = $this->meta->save_seoconfig();
		}
		
		if(isset($_POST['generate']))
		{
			$vars['message'] = $this->meta->generate_static_sitemap();
		}

		$vars['edit'] = $this->db->rows_key("SELECT `name`, `value` FROM `config` WHERE modules_id='113'");
		$data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name, 'sub'=>'seoconfig', 'menu2'=>$this->left_menu));
		$data['content'] = $this->view->Render('seoconfig.phtml', $vars);
		return $data;
	}
	
	public function redirectsAction()
	{
		$vars['message'] = '';
		if(isset($_POST['update']))
		{
			$vars['message'] = $this->meta->save_redirects();
		}
		elseif(isset($this->params['addredirect']))
		{
			$vars['message'] = $this->meta->addredirect();
		}
		elseif(isset($this->params['delete'])||isset($_POST['delete']))
		{
			$vars['message'] = $this->meta->delete('redirects');
		}
		
		$vars['name'] = 'Перенаправления';
		$vars['action'] = $this->tb;
		$vars['path'] = '/act/redirects';
		$vars['list'] = $this->db->rows("SELECT * FROM `redirects` ORDER BY id DESC");
		$data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name, 'sub'=>'redirects', 'menu2'=>$this->left_menu));
		$data['content'] = $this->view->Render('redirects.phtml', $vars);
		return $data;
	}
	
	public function searchlinkAction()
	{
		$vars['message'] = '';
		
		if(isset($this->params['clear'])||isset($_POST['clear']))
		{
			$vars['message'] = $this->meta->clear_from_links();
		}
		
		$vars['name'] = 'Поиск ссылок';
		$vars['action'] = $this->tb;
		$vars['path'] = '/act/searchlink';
		$vars['list'] = $this->meta->search_link();
		$data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name, 'sub'=>'searchlink', 'menu2'=>$this->left_menu));
		$data['content'] = $this->view->Render('searchlink.phtml', $vars);
		return $data;
	}
	
	public function robotsAction()
	{
		$vars['message'] = '';
		if(isset($_POST['update']))$vars['message'] = Editorial::getObject($this->sets)->save();

		$vars['edit'] = Editorial::getObject($this->sets)->find('robots.txt');
										
        $data['styles'] = array('codemirror.css');
        $data['scripts'] = array('codemirror.js', 'css.js', 'active-line.js');
		
		$data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name, 'sub'=>'robots', 'menu2'=>$this->left_menu));
		$data['content'] = $this->view->Render('robots.phtml', $vars);
		return $data;
	}
}
?>