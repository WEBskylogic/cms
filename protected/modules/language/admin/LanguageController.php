<?php
/*
 * вывод каталога компаний и их данных
 */

class LanguageController extends BaseController{

	protected $params;
	protected $db;

	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "language";
        $this->name = "Языки";
		$this->registry = $registry;
        $this->lang = new Language($this->sets);
	}

	public function indexAction()
	{
		$vars['message'] = '';
        $vars['name'] = $this->name;
        if(isset($this->params['subsystem']))return $this->Index($this->lang->subsystemAction());
		if(isset($this->registry['access']))$vars['message'] = $this->registry['access'];
		
		if(isset($this->params['delete'])||isset($_POST['delete']))$vars['message'] = $this->lang->delete();
		elseif(isset($_POST['update']))$vars['message'] = $this->lang->save();
		elseif(isset($_POST['update_close']))$vars['message'] = $this->lang->save();
		elseif(isset($_POST['add_close']))$vars['message'] = $this->lang->add();

        $vars['list'] = $this->view->Render('view.phtml', array('list' =>
                $this->lang->find( array('type'=>'rows', 'order'=>'tb.id DESC')))
        );
        $data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name));
		$data['content'] = $this->view->Render('list.phtml', $vars);
		return $this->Index($data);
	}
	

	public function addAction()
	{
		$vars['message'] = '';
		if(isset($_POST['add']))$vars['message'] = $this->lang->add();

		$vars['Language']=array();
		$dir=getcwd().'/tpl/admin/images/flags/';
		$Language =scandir($dir);
		sort($Language);

		$default = $this->db->rows_key("Select `language`,`language`  FROM `".$this->tb."`    " ) ;
		foreach($Language as $ky=>$val)
		{
			$fileParts  = pathinfo($dir.$val );
			if($val<>'..' and $val<>'.'  and $fileParts["extension"]=='png' ) 
			{
				$index=substr($val,0,-4); 
				if( !in_array($index, array_keys($default))) 
					$vars['Language'][$index]=$index;
			}
		} 
		$data['content'] = $this->view->Render('add.phtml', $vars);
		return $this->Index($data);
	}

}
?>