<?php
/*
 * вывод каталога компаний и их данных
 */
class CommentsController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "comments";
		$this->name = "Комментарии";
		$this->registry = $registry;
        $this->comments = new Comments($this->sets);
	}

	public function indexAction()
	{
		$vars['message'] = '';
		$vars['name'] = $this->name;
        if(isset($this->params['subsystem']))return $this->Index($this->comments->subsystemAction());
		if(isset($this->registry['access']))$vars['message'] = $this->registry['access'];
		if(isset($this->params['delete'])||isset($_POST['delete']))$vars['message'] = $this->comments->delete();
		elseif(isset($_POST['update']))$vars['message'] = $this->comments->save();
		elseif(isset($_POST['update_close']))$vars['message'] = $this->comments->save();

        $vars['list'] = $this->view->Render('view.phtml', array('list' =>
                        $this->comments->find( array('type'=>'rows', 'order'=>'tb.date DESC', 'paging'=>$this->settings['paging_comment_admin']))));
		$data['content'] = $this->view->Render('list.phtml', $vars);
        $data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name));
		return $this->Index($data);
	}

	public function editAction()
	{
		$vars['message'] = '';
		if(isset($_POST['update']))$vars['message'] = $this->comments->save();
        $vars['edit'] = $this->comments->find(array('where'=>"tb.id='{$this->params['edit']}'",
													'join'=>" LEFT JOIN product p ON tb.content_id=p.id AND tb.type='product' 
															  LEFT JOIN ru_product p2 ON p2.product_id=p.id",
													'select'=>'tb.*, p.url'));
													
		if($vars['edit']['type']!='')
		{
			$query = "SELECT m.url, t.url AS url2
				
					  FROM ".$vars['edit']['type']." t
					  
					  LEFT JOIN modules m
					  ON m.controller='".$vars['edit']['type']."'

					  WHERE t.id='{$vars['edit']['content_id']}'";
			$vars['content_url'] = $this->db->row($query);
		}
		
		$data['content'] = $this->view->Render('edit.phtml', $vars);
		return $this->Index($data);
	}
	
	public function delAction()
	{
		if(isset($_POST['id']))
		{
			$vars['message'] = '';
			$row = $this->comments->find($_POST['id']);
			
			if($row&&$row['type']!=''&&$row['content_id']!=0)
			{	
				$this->db->query("DELETE FROM comments WHERE id=?", array($_POST['id']));	
				$vars['content']=$this->comments->list_comments_admin($row['content_id'], $row['type']);
				return json_encode($vars);
			}
		}
	}
}
?>