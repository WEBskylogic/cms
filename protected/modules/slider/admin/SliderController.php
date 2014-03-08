<?php
/*
 * вывод каталога компаний и их данных
 */
class SliderController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "slider";
        $this->name = "Слайдер";
		$this->registry = $registry;
        $this->slider = new Slider($this->sets);
	}

	public function indexAction()
	{
		$vars['message'] = '';
        $vars['name'] = $this->name;
        if(isset($this->params['subsystem']))return $this->Index($this->slider->subsystemAction());
		if(isset($this->registry['access']))$vars['message'] = $this->registry['access'];
		if(isset($this->params['delete'])||isset($_POST['delete']))$vars['message'] = $this->slider->delete();
		elseif(isset($_POST['update']))$vars['message'] = $this->slider->save();
		elseif(isset($_POST['update_close']))$vars['message'] = $this->slider->save();
		elseif(isset($_POST['add_close']))$vars['message'] = $this->slider->add();

        $vars['list'] = $this->view->Render('view.phtml', array('list' =>
            $this->slider->find( array('type'=>'rows', 'order'=>'tb.sort ASC, tb.id DESC')))
        );
        $data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name));
		$data['content'] = $this->view->Render('list.phtml', $vars);
		return $this->Index($data);
	}
	
	public function addAction()
	{
		$vars['message'] = '';
        $vars['width'] = $this->settings['width_slider'];
        $vars['height'] = $this->settings['height_slider'];

		if(isset($_POST['add']))$vars['message'] = $this->slider->add();
		$data['content'] = $this->view->Render('add.phtml', $vars);
		return $this->Index($data);
	}
	
	public function editAction()
	{
		$vars['message'] = '';
        $vars['width'] = $this->settings['width_slider'];
        $vars['height'] = $this->settings['height_slider'];

		if(isset($_POST['update']))$vars['message'] = $this->slider->save();
		$vars['edit'] = $this->slider->find((int)$this->params['edit']);
		$data['content'] = $this->view->Render('edit.phtml', $vars);
		return $this->Index($data);
	}
}
?>