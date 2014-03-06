<?php
/*
 * вывод каталога компаний и их данных
 */
class ConfigController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "config";
        $this->name = "Параметры";
		$this->registry = $registry;
		$row = $this->db->row("SELECT id FROM `subsystem` WHERE `name`=?", array($this->tb));
		$this->subsystem_id = $row['id'];
		
		$row = $this->db->row("SELECT id FROM `modules` WHERE `controller`=?", array($this->tb));
		$this->modules_id = $row['id'];
	}

	public function indexAction()
	{
		//$this->set_in_file_config();
		$vars['message'] = '';
        $vars['name'] = $this->name;
		if(isset($this->registry['access']))$vars['message'] = $this->registry['access'];
		if(isset($this->params['delete'])||isset($_POST['delete']))$vars['message'] = $this->delete();
		elseif(isset($_POST['update']))$vars['message'] = $this->save();
		elseif(isset($_POST['update_close']))$vars['message'] = $this->save();
		elseif(isset($_POST['add_close']))$vars['message'] = $this->add();
		
		$where="";
		$vars['path']='';
		if(isset($this->params['modules']))
		{
			$vars['sub']=$this->params['modules'];
			$where="WHERE modules_id='{$this->params['modules']}'";
			$vars['path']='/modules/'.$this->params['modules'];
		}
		
		$vars['menu'] = $this->db->rows("SELECT m.*, mp.*, COUNT(config.id) as cnt  
										 FROM modules m
										 
										 LEFT JOIN `moderators_permission` mp
                                         ON mp.module_id=m.id
										 
										 LEFT JOIN config
										 ON config.modules_id=m.id
										 
										 WHERE mp.moderators_type_id=? AND subsystem_id='".$this->subsystem_id."' AND permission!='000'
										 GROUP BY m.id
										 ORDER BY cnt DESC
										 ", array($_SESSION['admin']['type']));
		$data['left_menu'] = $this->view->Render('left_menu2.phtml', $vars);	
		$vars['list'] = $this->view->Render('view.phtml', $this->listView($where));
		$data['content'] = $this->view->Render('list.phtml', $vars);
		return $this->Index($data);
	}
	
	public function addAction()
	{
		$vars['message'] = '';
		if(isset($_POST['add']))$vars['message'] = $this->add();
		
		$vars['path']='';
		if(isset($this->params['modules']))
		{
			$vars['path']='/modules/'.$this->params['modules'];
		}
		$data['breadcrumb'] = '<a class="back-link" href="/admin/'.$this->tb.$vars['path'].'">« Назад в: '.$this->name.'</a>';
		$data['content'] = $this->view->Render('add.phtml', $vars);
		return $this->Index($data);
	}

	public function add($modules_id='')
	{
		$message='';
		if(isset($_POST['name'], $_POST['value'], $_POST['comment']))
		{
			$row = $this->db->row("SELECT id FROM `".$this->tb."` WHERE name=?", array($_POST['name']));
			if(!$row)
			{
				$where='';
				if(isset($this->params['modules']))$modules_id=$this->params['modules'];
				if($modules_id!='')$where=", modules_id='$modules_id'";
				$param = array($_POST['name'], $_POST['value'], $_POST['comment']);
				$this->db->query("INSERT INTO `".$this->tb."` SET `name`=?, `value`=?, comment=? $where", $param);
				$message.= messageAdmin('Данные успешно добавлены');
			}
			else $message.= messageAdmin('Данный ключ занят!', 'error');
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
				if(isset($_POST['save_id'], $_POST['name'], $_POST['comment']))
				{
					for($i=0; $i<=count($_POST['save_id']) - 1; $i++)
					{
						$id=$_POST['save_id'][$i];
						if($this->model->check_for_update($id, $this->tb, $_SESSION['admin']['type']))
						{
							if(isset($_POST['value'.$id]))$value=$_POST['value'.$id];
							else $value='';
							//echo $value.'<br />';
							$param = array($_POST['name'][$i], $value, $_POST['comment'][$i], $_POST['modules_id'][$i], $_POST['type_id'][$i], $_POST['save_id'][$i]);
							$this->db->query("UPDATE `".$this->tb."` tb
	
											  SET `name`=?, `value`=?, `comment`=? , `modules_id`=? , `type`=? 
											  
											  WHERE `id`=?", $param);
						}
						else $message .=messageAdmin('Ошибка в правах доступа!'.$this->tb.$id, 'error');	
					}
					if($message=='')$message = messageAdmin('Данные успешно сохранены');
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
					$message = $this->model->check_for_delete($_POST['id'][$i], $this->tb, $_SESSION['admin']['type']);
				}
				if($message=='')$message = messageAdmin('Запись успешно удалена');
			}
			elseif(isset($this->params['delete'])&& $this->params['delete']!='')
			{
				$subsystem_id = $this->params['delete'];
				$message = $this->model->check_for_delete($subsystem_id, $this->tb, $_SESSION['admin']['type']);							 
			}
			elseif(isset($this->params['delsubsystem'])&& $this->params['delsubsystem']!='')
			{
				$subsystem_id = $this->params['delsubsystem'];
				$message = $this->model->check_for_delete($subsystem_id, $this->tb, $_SESSION['admin']['type']);		 
				
			}
		}
		return $message;
	}
	
	public function listView($where='')
	{
		$vars['path']='';
		if(isset($this->params['modules']))
		{
			$vars['path']='/modules/'.$this->params['modules'];
		}
		//if($where=='')$where="AND tb.modules_id='0'";
		$where = str_replace('WHERE', 'AND', $where);
		$vars['modules'] = $this->db->rows("SELECT id, name FROM modules ORDER BY sub ASC, name ASC");
		$vars['list'] = $this->db->rows("SELECT tb.*
										 FROM ".$this->tb." tb
										 
										 LEFT JOIN `moderators_permission` mp
                                         ON mp.module_id=tb.modules_id
										 
										 WHERE ((mp.moderators_type_id=? AND subsystem_id='".$this->subsystem_id."' AND permission!='000') OR tb.modules_id='0') AND tb.active='1'

										 $where	
										 GROUP BY tb.id
										 ORDER BY tb.`id` DESC, tb.modules_id ASC", array($_SESSION['admin']['type']));
		return $vars;
	}
	
	public function subcontent($vars=array())
	{
		$vars['modules'] = $this->db->rows("SELECT id, name FROM modules ORDER BY sub ASC, name ASC");
		$arr=$this->listView($vars['where']);
		$vars['list'] = $arr['list'];
		return $this->view->Render($this->tb.'.phtml', $vars);
	}
	
	
	function set_in_file_config()
	{
		$res = $this->db->rows("SELECT tb.name, tb.`value`, tb.comment, tb.modules_id 
								 FROM ".$this->tb." tb
								 
								 LEFT JOIN `modules` m
								 ON tb.modules_id=m.id

								 GROUP BY tb.id
								 ORDER BY m.id ASC", array($_SESSION['admin']['type']));
								 var_info($res);
	}
}
?>