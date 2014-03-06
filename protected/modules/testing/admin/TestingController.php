<?php

/*

 * вывод каталога компаний и их данных

 */

class TestingController extends BaseController{

	

	protected $params;

	protected $db;

	private $left_menu = array();


	function  __construct($registry, $params)

	{

		parent::__construct($registry, $params);

		$this->tb = "testing";

		$this->name = "Тикеты";

		$this->registry = $registry;

		$this->testing = new Testing($this->sets);

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

		

		if(isset($this->params['subsystem']))return $this->Index($this->testing->subsystemAction());

		if(isset($this->registry['access']))$vars['message'] = $this->testing->registry['access'];

		if(isset($this->params['delete'])||isset($_POST['delete']))$vars['message'] = $this->delete();

		$vars['list'] = $this->testing->find(array('paging'=>true, 

												  'order'=>'tb.date_add DESC', 

												  'select'=>'tb.*'));
			  

		$vars['list'] = $this->view->Render('view.phtml', $vars);

		$data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name, 'menu2'=>$this->left_menu));

		$data['content'] = $this->view->Render('list.phtml', $vars);

		return $this->Index($data);

	}

	public function delete()

	{

		if(isset($_POST['id'])&&is_array($_POST['id']))

		{

			$count=count($_POST['id']) - 1;

			for($i=0; $i<=$count; $i++)

			{

				$this->db->query("DELETE FROM `".$this->tb ."` WHERE `id`=?", array($_POST['id'][$i]));

				unlink('files/testing/'.$_POST['id'][$i].'.jpg');

			}

			$message = messageAdmin('Запись успешно удалена');

		}

		elseif(isset($this->params['delete'])&& $this->params['delete']!='')

		{

			$id = $this->params['delete'];


			if($this->db->query("DELETE FROM `".$this->tb ."` WHERE `id`=?", array($id))) {

				unlink('files/testing/'.$id.'.jpg');

				$message = messageAdmin('Запись успешно удалена');

			} 

	
		}
	}


}

?>