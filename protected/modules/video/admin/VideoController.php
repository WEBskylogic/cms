<?php
/*
 * вывод каталога компаний и их данных
 */
class VideoController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		$this->tb = "video";
		$this->name = "Видео";
		$this->tb_lang = $this->key_lang.'_'.$this->tb;
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
	
	public function addAction()
	{
		$vars['message'] = '';
		if(isset($_POST['add']))$vars['message'] = $this->add();
		
		$vars['list'] = $this->listView();
		$view = new View($this->registry);
		$data['content'] = $view->Render('add.phtml', $vars);
		return $this->Render($data);
	}
	
	public function editAction()
	{
		//if($vars['message']!='')return Router::act('error');
		$vars['message'] = '';
		if(isset($_POST['update']))$vars['message'] = $this->save();
		
		$vars['edit'] = $this->db->row("SELECT 
											tb.*,
											tb2.* 
										FROM ".$this->tb." tb
											LEFT JOIN
												".$this->tb_lang." tb2
											ON
												tb.id=tb2.".$this->tb."_id
										WHERE
											tb.id=?",
										array($this->params['edit']));
		if(isset($this->params['duplicate']))$vars['message'] = $this->duplicate($vars['edit']);
		$vars['list'] = $this->listView();
		$view = new View($this->registry);
		$data['content'] = $view->Render('edit.phtml', $vars);
		return $this->Render($data);
	}


	private function duplicate($row)
	{
		$message='';
		if(isset($this->registry['access']))$message = $this->registry['access'];
		else
		{
			$message='';
			if(isset($row['name'])&&$row['name']!="")
			{
                $name=$row['name'].' [copy]';
                $insert_id = $this->db->insert_id("INSERT INTO `".$this->tb."` SET `sort`=?, `body`=?", array(1, $row['body']));
				foreach($this->language as $lang)
				{
					$tb=$lang['language']."_".$this->tb;
					$param = array($name, $insert_id);
					$this->db->query("INSERT INTO `$tb` SET `name`=?, `".$this->tb."_id`=?", $param);
				}
				header("Location: /admin/info/edit/".$insert_id);
				//$message.= messageAdmin('Данные успешно клонированы');
			}
			//else $message.= messageAdmin('При добавление произошли ошибки', 'error');
		}
		return $message;
	}

	private function add()
	{
		$message='';
		if(isset($_POST['name'], $_POST['body'])&&$_POST['name']!="")
		{
            $insert_id = $this->db->insert_id("INSERT INTO `".$this->tb."` SET `sort`=?, `body`=?", array(1, $_POST['body']));
			foreach($this->language as $lang)
			{
				$tb=$lang['language']."_".$this->tb;
				$param = array($_POST['name'], $insert_id);
				$this->db->query("INSERT INTO `$tb` SET `name`=?, `".$this->tb."_id`=?", $param);
			}
			$message.= messageAdmin('Данные успешно добавлены');
		}
		//else $message.= messageAdmin('При добавление произошли ошибки', 'error');
		return $message;
	}


	private function save()
	{
		$message='';
		if(isset($this->registry['access']))$message = $this->registry['access'];
		else
		{
			if(isset($_POST['save_id'])&&is_array($_POST['save_id']))
			{
				if(isset($_POST['save_id'], $_POST['name']))
				{
					for($i=0; $i<=count($_POST['save_id']) - 1; $i++)
					{
						$param = array($_POST['name'][$i], $_POST['save_id'][$i]);
						$this->db->query("UPDATE `".$this->tb_lang."` SET `name`=? WHERE ".$this->tb."_id=?", $param);
					}
					$message .= messageAdmin('Данные успешно сохранены');
				}
				else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
			}
			else{
				if(isset($_POST['id'], $_POST['name'], $_POST['body']))
				{
					$param = array($_POST['name'], $_POST['id']);
					$this->db->query("UPDATE `".$this->tb_lang."` SET `name`=? WHERE `".$this->tb."_id`=?", $param);

                    $param = array($_POST['body'], $_POST['id']);
                    $this->db->query("UPDATE `".$this->tb."` SET `body`=? WHERE `id`=?", $param);
					$message .= messageAdmin('Данные успешно сохранены');
				}
				else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
			}
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
					$this->db->query("DELETE FROM `".$this->tb."` WHERE `id`=?", array($_POST['id'][$i]));
				}
				$message = messageAdmin('Запись успешно удалена');
			}
			elseif(isset($this->params['delete'])&& $this->params['delete']!='')
			{
				$id = $this->params['delete'];
				if($this->db->query("DELETE FROM `".$this->tb."` WHERE `id`=?", array($id)))$message = messageAdmin('Запись успешно удалена');
			}
		}
		return $message;
	}

	private function listView($WHERE='')
	{
		$vars['list'] = $this->db->rows("SELECT
											tb.*,
											tb2.name
										 FROM ".$this->tb." tb
											LEFT JOIN
												".$this->tb_lang." tb2
											ON
												tb.id=tb2.".$this->tb."_id
										 $WHERE		
										 ORDER BY tb.`sort` ASC");
		return $vars;
	}
}
?>