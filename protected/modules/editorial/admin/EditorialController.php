<?php
/*
 * вывод каталога компаний и их данных
 */
class EditorialController extends BaseController{
	
	protected $params;
	protected $db;
	private $left_menu = array(array('title'=>'.htaccess', 
									 'url'=>'/admin/editorial/act/htaccess', 
									 'name'=>'htaccess'),
							   array('title'=>'Темы', 
									 'url'=>'/admin/editorial/act/themes', 
									 'name'=>'themes'),
							   array('title'=>'Водяной знак', 
									 'url'=>'/admin/editorial/act/watermark', 
									 'name'=>'watermark')	  
							  );
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "editorial";
        $this->name = "Редактирование";
		$this->registry = $registry;
		$this->editorial = new Editorial($this->sets);
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
		
        if(isset($this->params['subsystem']))return $this->Index($this->editorial->subsystemAction($this->left_menu));
		if(isset($this->registry['access']))$vars['message'] = $this->registry['access'];
		elseif(isset($_POST['update']))$vars['message'] = $this->editorial->save();
		elseif(isset($_POST['update_close']))$vars['message'] = $this->editorial->save();

        $vars['list'] = $this->view->Render('view.phtml', array('list' => $this->editorial->listView()));

        $data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name, 'menu2'=>$this->left_menu));
		$data['content'] = $this->view->Render('list.phtml', $vars);
		return $this->Index($data);
	}
	
	public function editAction()
	{
		$vars['message'] = '';
		if(isset($_POST['update']))$vars['message'] = $this->editorial->save();

		$vars['edit'] = $this->editorial->find($this->params['edit']);
										
        $data['styles'] = array('codemirror.css');
        $data['scripts'] = array('codemirror.js', 'css.js', 'matchbrackets.js', 'active-line.js', 'htmlmixed.js', 'closetag.js', 'xml.js', 'php.js', 'clike.js');

		$data['content'] = $this->view->Render('edit.phtml', $vars);
		return $this->Index($data);
	}
	
	public function htaccessAction()
	{
		$vars['message'] = '';
		if(isset($_POST['update']))$vars['message'] = $this->editorial->save();

		$vars['edit'] = $this->editorial->find('.htaccess');
										
        $data['styles'] = array('codemirror.css');
        $data['scripts'] = array('codemirror.js', 'css.js', 'active-line.js');
		
		$data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name, 'sub'=>'htaccess', 'menu2'=>$this->left_menu));
		$data['content'] = $this->view->Render('htaccess.phtml', $vars);
		return $data;
	}
	
	public function themesAction()
	{
		$vars['message'] = '';
		if(isset($_POST['update']))$vars['message'] = $this->editorial->save_theme();
		
		$vars['edit'] = Dir::get_directory_list("tpl/");
		unset($vars['edit'][0]);
		sort($vars['edit']);
		//var_info($vars['edit']);
		
		$vars['theme'] = $this->db->row("SELECT * FROM `config` WHERE `name`='theme'");
		$data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name, 'sub'=>'themes', 'menu2'=>$this->left_menu));
		$data['content'] = $this->view->Render('themes.phtml', $vars);
		return $data;
	}
	
	public function watermarkAction()
	{
		$vars['message'] = '';
		if(isset($_POST['update']))$vars['message'] = $this->editorial->save_watermark();
		//var_info($vars['edit']);
		$row = $this->db->row("SELECT * FROM `config` WHERE `name`='watermark'");
		$vars['edit'] = json_decode($row['value'], true);var_info($row['value']);
		$vars['modules'] = $this->db->rows("SELECT id, name, controller FROM `modules` WHERE `photo`='1' ORDER BY name ASC");
		$data['scripts'] = array('colorpicker.js', 'eye.js', 'utils.js', 'layout.js');
		$data['styles'] = array('colorpicker.css');
		$data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name, 'sub'=>'watermark', 'menu2'=>$this->left_menu));
		$data['content'] = $this->view->Render('watermark.phtml', $vars);
		return $data;
	}
}
?>