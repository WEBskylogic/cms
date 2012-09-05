<?php
/*
 * вывод каталога компаний и их данных
 */
class ParamsController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		$this->tb = "params";
		$this->tb_lang = $this->key_lang.'_'.$this->tb;
        $this->name = "Фильтры товаров";
		$this->registry = $registry;
		//$this->db->row("SELECT FROM `moderators_permission` WHERE `id`=?", array($_SESSION['admin']['id']));
		parent::__construct($registry, $params);
	}

	public function indexAction()
	{
		if(isset($_POST['sort_params']))$_SESSION['sort_params']=$_POST['sort_params'];
        if(!isset($_SESSION['sort_params']))$_SESSION['sort_params']=0;
		$vars['message'] = '';
        $vars['name'] = $this->name;
		if(isset($this->registry['access']))$vars['message'] = $this->registry['access'];
		if(isset($this->params['delete'])||isset($_POST['delete']))$vars['message'] = $this->delete();
		elseif(isset($_POST['update']))$vars['message'] = $this->save();
		elseif(isset($_POST['update_close']))$vars['message'] = $this->save();
		elseif(isset($_POST['add_close']))$vars['message'] = $this->add();
		
		$view = new View($this->registry);
		
		$vars['list'] = $view->Render('view.phtml', $this->listView());
		$vars['params'] = $this->db->rows("SELECT tb.id, tb.sub, tb2.name
                                          FROM `".$this->tb."` tb
                                          LEFT JOIN `".$this->tb_lang."` tb2
                                          ON tb2.params_id=tb.id
										  WHERE sub is NULL
                                          ORDER BY tb.sort ASC");
		$data['content'] = $view->Render('list.phtml', $vars);
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
												tb.id=tb2.params_id
										WHERE
											tb.id=?",
										array($this->params['edit']));
		$vars['list'] = $this->listView();
		$view = new View($this->registry);

        $vars['params'] = $this->db->rows("SELECT tb.id, tb.sub, tb2.name
                                          FROM `".$this->tb."` tb
                                          LEFT JOIN `".$this->tb_lang."` tb2
                                          ON tb2.params_id=tb.id
                                          WHERE tb.id!=? AND tb.sub IS NULL
                                          ORDER BY tb.sort ASC",
        array($this->params['edit']));

		$data['content'] = $view->Render('edit.phtml', $vars);
		return $this->Render($data);
	}
	
	public function addAction()
	{
		$vars['message'] = '';
		if(isset($_POST['add']))$vars['message'] = $this->add();
		
		$vars['params'] = $this->db->rows("SELECT tb.id, tb.sub, tb2.name
                                          FROM `".$this->tb."` tb
                                          LEFT JOIN `".$this->tb_lang."` tb2
                                          ON tb2.params_id=tb.id
										  WHERE sub is NULL
                                          ORDER BY tb.sort ASC");
		$vars['list'] = $this->listView();
		$view = new View($this->registry);
		$data['content'] = $view->Render('add.phtml', $vars);
		return $this->Render($data);
	}

	private function add()
	{
		$message='';
		if(isset($_POST['name'], $_POST['sub'], $_POST['url']))
		{
			if($_POST['sub']==0)$_POST['sub']=NULL;
            $param = array($_POST['url'], $_POST['sub']);
            $insert_id = $this->db->insert_id("INSERT INTO `".$this->tb."` SET `url`=?, `sub`=?", $param);
			
			$param = array($_POST['name'], $insert_id);
			$this->db->query("INSERT INTO `".$this->tb_lang."` SET `name`=?, `params_id`=?", $param);
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
						$this->db->query("UPDATE `".$this->tb_lang."` SET `name`=? WHERE params_id=?", $param);
					}
					$message .= messageAdmin('Данные успешно сохранены');
				}
				else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
			}
			else{
				if(isset($_POST['active'], $_POST['url'], $_POST['id'], $_POST['name'], $_POST['body']))
				{
					if($_POST['url']=='')$url = translit($_POST['name']);
					else $url = translit($_POST['url']);

                    if($_POST['sub']==0)$sub = NULL;
                    else $sub = $_POST['sub'];

					$param = array($_POST['active'], $sub, $url, $_POST['id']);
					$this->db->query("UPDATE `".$this->tb."` SET `active`=?, sub=?, url=? WHERE id=?", $param);
					
					$param = array($_POST['name'], $_POST['body'], $_POST['id']);
					$this->db->query("UPDATE `".$this->tb_lang."` SET `name`=?, `info`=? WHERE `params_id`=?", $param);
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
	
	private function listView()
	{
		$where="WHERE tb.sub is NULL";
        if($_SESSION['sort_params']!=0)$where="WHERE tb.sub='{$_SESSION['sort_params']}' ";
		$vars['list'] = $this->db->rows("SELECT
											tb.*,
											tb2.name
										 FROM ".$this->tb." tb
											LEFT JOIN
												".$this->tb_lang." tb2
											ON
												tb.id=tb2.params_id
										 $where
										 GROUP BY tb.id
										 ORDER BY tb.`sort` ASC, id DESC");
		return $vars;
	}
}
?>