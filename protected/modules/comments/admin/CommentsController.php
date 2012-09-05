<?php
/*
 * вывод каталога компаний и их данных
 */
class CommentsController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		$this->tb = "comments";
		$this->name = "Комментарии";
		$this->registry = $registry;
		parent::__construct($registry, $params);
	}

	public function indexAction()
	{
		$vars['message'] = '';
		$vars['name'] = $this->name;
		if(isset($this->registry['access']))$vars['message'] = $this->registry['access'];
		if(isset($this->params['delete'])||isset($_POST['delete']))$vars['message'] = $this->delete();
		elseif(isset($_POST['update']))$vars['message'] = $this->save();
		elseif(isset($_POST['update_close']))$vars['message'] = $this->save();
		elseif(isset($_POST['add_close']))$vars['message'] = $this->add();
		
		$view = new View($this->registry);
		$vars['list'] = $view->Render('view.phtml', $this->listView());
		$data['content'] = $view->Render('list.phtml', $vars);
		return $this->Render($data);
	}

	public function editAction()
	{
		$vars['message'] = '';
		if(isset($this->params['delanswer']))
		{
			if($this->db->query("DELETE FROM `".$this->tb."` WHERE `id`=?", array($this->params['delanswer'])))$vars['message'] = messageAdmin('Запись успешно удалена');
		}
		//if($vars['message']!='')return Router::act('error');
		
		if(isset($_POST['update']))$vars['message'] = $this->save();
		
		$vars['edit'] = $this->db->row("SELECT 
											tb.*
										FROM ".$this->tb." tb
										WHERE
											tb.id=?",
										array($this->params['edit']));
		$vars['comments'] = $this->db->rows("SELECT * FROM comments WHERE sub=? ORDER BY date DESC", array($this->params['edit']));																		
		$vars['list'] = $this->listView();
		$view = new View($this->registry);
		$data['content'] = $view->Render('edit.phtml', $vars);
		return $this->Render($data);
	}
	
	private function save()
	{
		$message='';
		if(isset($this->registry['access']))$message = $this->registry['access'];
		else
		{
            if(isset($_POST['active'], $_POST['name'], $_POST['text']))
            {
                $param = array($_POST['name'], $_POST['text'], $_POST['active'], $_POST['id']);
                $this->db->query("UPDATE `".$this->tb."` SET `author`=?, `text`=?, `active`=? WHERE `id`=?", $param);
                $message .= messageAdmin('Данные успешно сохранены');
                $message .= messageAdmin('Данные успешно сохранены');
            }
            else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
		}
		return $message;
	}
	
	private function delete()
	{
		$message='';
		if(isset($this->registry['access']))$message = $this->registry['access'];
		else
		{
			if(isset($_POST['id'])&&is_array($_POST['id']))
			{
				for($i=0; $i<=count($_POST['id']) - 1; $i++)
				{
					$this->db->query("DELETE FROM `".$this->tb."` WHERE `id`=? OR `sub`=?", array($_POST['id'][$i], $_POST['id'][$i]));
				}
				$message = messageAdmin('Запись успешно удалена');
			}
			elseif(isset($this->params['delete'])&& $this->params['delete']!='')
			{
				$id = $this->params['delete'];
				if($this->db->query("DELETE FROM `".$this->tb."` WHERE `id`=? OR `sub`=?", array($id, $id)))$message = messageAdmin('Запись успешно удалена');
			}
		}
		return $message;
	}
	
	private function listView()
	{
		$vars['list'] = $this->db->rows("SELECT tb.*
										 FROM ".$this->tb." tb
										 WHERE tb.sub=0	
										 ORDER BY tb.`date` DESC");
		return $vars;
	}
}
?>