<?php
/*
 * Moderators edit
 */
class ModeratorsController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "moderators";
		$this->name = "Модераторы";
		$this->registry = $registry;
		$this->moderators = new Moderators($this->sets);
	}

    public function indexAction()
    {
        $vars['message'] = '';
        $vars['name'] = $this->name;
		if(isset($this->params['subsystem']))return $this->Index($this->moderators->subsystemAction());
        if(isset($this->registry['access']))$vars['message'] = $this->registry['access'];
        if(isset($this->params['delete'])||isset($_POST['delete']))$vars['message'] = $this->moderators->delete();
        elseif(isset($_POST['update']))$vars['message'] = $this->moderators->save();
        elseif(isset($_POST['update_close']))$vars['message'] = $this->moderators->save();
        elseif(isset($_POST['add_open']))$vars['message'] = $this->moderators->add(true);

        $vars['list'] = $this->view->Render('view.phtml', $this->moderators->find(array('select'=>'tb.*, tb2.comment as status', 
																					    'join'=>'LEFT JOIN moderators_type tb2 ON tb.type_moderator=tb2.id',
																					    'where'=>"tb2.id!='1'",
																						'order'=>'tb.`id` DESC',
																						'type'=>'rows',
																						'paging'=>true
																				)));
		$data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name));
        $data['content'] = $this->view->Render('list.phtml', $vars);
        return $this->Index($data);
    }

    public function addAction()
    {
        $vars['message'] = '';
        if(isset($_POST['add']))$vars['message'] = $this->moderators->add();

        $vars['types'] = $this->db->rows("SELECT * FROM `moderators_type` WHERE id!='1' ORDER BY id ASC");
        $data['content'] = $this->view->Render('add.phtml', $vars);
        return $this->Index($data);
    }

    public function editAction()
    {
        $vars['message'] = '';
        if(isset($_POST['update']))$vars['message'] = $this->moderators->save();
		$vars['edit'] = $this->moderators->find(array('select'=>'tb.*, tb2.comment as status', 
									  				  'join'=>'LEFT JOIN moderators_type tb2 ON tb.type_moderator=tb2.id',
									  				  'where'=>'__tb.id:='.$this->params['edit'].'__'
									  			));	
        $vars['types'] = $this->db->rows("SELECT * FROM `moderators_type` WHERE id!='1' ORDER BY id ASC");
        $data['content'] = $this->view->Render('edit.phtml', $vars);
        return $this->Index($data);
    }
}
?>