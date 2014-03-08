<?php
/*
 * вывод каталога компаний и их данных
 */
class FeedbackController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "feedback";
		$this->name = "Обратная связь";
		$this->registry = $registry;
		$this->feedback = new Feedback($this->sets);
	}

	public function indexAction()
	{
		$vars['message'] = '';
		$vars['name'] = $this->name;
		if(isset($this->params['subsystem']))return $this->Index($this->feedback->subsystemAction());
		if(isset($this->registry['access']))$vars['message'] = $this->registry['access'];
		if(isset($this->params['delete'])||isset($_POST['delete']))$vars['message'] = $this->feedback->delete();
		elseif(isset($_POST['update']))$vars['message'] = $this->feedback->save();
		elseif(isset($_POST['update_close']))$vars['message'] = $this->feedback->save();
		elseif(isset($_POST['add_close']))$vars['message'] = $this->feedback->add();

		$vars['list'] = $this->view->Render('view.phtml', $this->feedback->find(array('paging'=>true, 'order'=>'tb.date DESC')));
		$data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name));
		$data['content'] = $this->view->Render('list.phtml', $vars);
		return $this->Index($data);
	}

	public function editAction()
	{
		$vars['message'] = '';
		$vars['edit'] = $this->feedback->find((int)$this->params['edit']);
		$data['content'] = $this->view->Render('edit.phtml', $vars);
		return $this->Index($data);
	}
}
?>