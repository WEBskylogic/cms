<?php
/*
 * вывод каталога компаний и их данных
 */
class HelpController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "help";
        $this->name = "Помощь";
		$this->registry = $registry;
		//$this->db->row("SELECT FROM `moderators_permission` WHERE `id`=?", array($_SESSION['admin']['id']));
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
		
		$this->view = new View($this->registry);
		$vars['list'] = $this->view->Render('view.phtml', $this->listView());
		$data['content'] = $this->view->Render('list.phtml', $vars);
		return $this->Index($data);
	}
	
	public function editAction()
	{
		$this->view = new View($this->registry);
		$vars['modules'] = $this->params['edit'];
		$data['content'] = $this->view->Render('edit.phtml', $vars);
		return $this->Index($data);
	}

	public function add($modules_id='')
	{
		$message='';
		if(isset($_POST['name'], $_POST['value'], $_POST['comment']))
		{
            $param = array($_POST['name'], $_POST['value'], $_POST['comment']);
            $this->db->query("INSERT INTO `".$this->tb."` SET `name`=?, `value`=?, comment=?", $param);
			$message.= messageAdmin('Данные успешно добавлены');
		}
		elseif(isset($this->params['addsubsystem']))
		{
            $this->db->query("INSERT INTO `".$this->tb."` SET modules_id='$modules_id'");
			$message.= messageAdmin('Данные успешно добавлены');
		}
		//else $message.= messageAdmin('При добавление произошли ошибки', 'error');	
		return $message;
	}
	
	
	public function save()
	{
		$message='';
		if(isset($this->registry['access']))$message = $this->registry['access'];
		else
		{
			if(isset($_POST['save_id'])&&is_array($_POST['save_id']))
			{
				if(isset($_POST['save_id'], $_POST['name'], $_POST['value'], $_POST['comment']))
				{
					for($i=0; $i<=count($_POST['save_id']) - 1; $i++)
					{
                        $param = array($_POST['name'][$i], $_POST['value'][$i], $_POST['comment'][$i], $_POST['save_id'][$i]);
                        $this->db->query("UPDATE `".$this->tb."` SET `name`=?, `value`=?, `comment`=? WHERE id=?", $param);
					}
					$message .= messageAdmin('Данные успешно сохранены');
				}
				else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
			}
		}
		return $message;
	}
	
	public function delete()
	{
		$message='';
		if(isset($this->registry['access']))$message = $this->registry['access'];
		else
		{
			if(isset($_POST['id'])&&is_array($_POST['id']))
			{
				for($i=0; $i<=count($_POST['id']) - 1; $i++)
				{
					$this->db->query("DELETE FROM `".$this->tb."` WHERE `id`=?", array($_POST['id'][$i]));
				}
				$message = messageAdmin('Запись успешно удалена');
			}
			elseif(isset($this->params['delete'])&& $this->params['delete']!='')
			{
				$id = $this->params['delete'];
				if($this->db->query("DELETE FROM `".$this->tb."` WHERE `id`=?", array($id)))$message = messageAdmin('Запись успешно удалена');
			}
			elseif(isset($this->params['delsubsystem'])&& $this->params['delsubsystem']!='')
			{
				$id = $this->params['delsubsystem'];
				if($this->db->query("DELETE FROM `".$this->tb."` WHERE `id`=?", array($id)))$message = messageAdmin('Запись успешно удалена');
			}
		}
		return $message;
	}
	
	public function listView()
	{
		$vars['list'] = $this->db->rows("SELECT 
											tb.*
										 FROM modules tb
											ORDER BY tb.`id` DESC");
		return $vars;
	}
	
	public function subcontent($vars=array())
	{
		$this->view = new View($this->registry);
		return $this->view->Render($this->tb.'.phtml', $vars);	
	}
}
?>