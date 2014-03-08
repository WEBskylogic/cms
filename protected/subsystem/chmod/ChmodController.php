<?php
/*
 * вывод каталога компаний и их данных
 */
class ChmodController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "moderators_type";
        $this->name = "Права доступа";
		$this->tb_lang = $this->key_lang_admin.'_'.$this->tb;
		$this->registry = $registry;
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
        //if($vars['message']!='')return Router::act('error');
        $vars['message'] = '';
        if(isset($_POST['update']))$vars['message'] = $this->save();
        $vars['edit'] = $this->db->row("SELECT
											tb.*
										FROM ".$this->tb." tb

										WHERE
											tb.id=?",
            array($this->params['edit']));
        $vars['list'] = $this->listView();
        $this->view = new View($this->registry);
        $data['styles'] = array('timepicker.css');
        $data['scripts'] = array('timepicker.js');
        $vars['modules'] = $this->db->rows("SELECT *
                                            FROM `modules` m
                                            LEFT JOIN `moderators_permission` mp
                                            ON mp.module_id=m.id AND mp.moderators_type_id=? AND subsystem_id='0'
                                            ORDER BY m.id ASC",
            array($this->params['edit']));
			
		$vars['subsystem2'] = $this->db->rows("SELECT *
												FROM `subsystem` m
												GROUP BY m.id
												ORDER BY m.id ASC");
			
		$vars['permission'] = $this->db->rows("SELECT *
												FROM `moderators_permission` mp
												WHERE mp.moderators_type_id=? AND subsystem_id!='0'
												",
            array($this->params['edit']));	
			
		//var_info($vars['subsystem2']);
        $data['content'] = $this->view->Render('edit.phtml', $vars);
        return $this->Index($data);
    }
	
	public function addAction()
	{
		$vars['message'] = '';
		if(isset($_POST['add']))$vars['message'] = $this->add();
		
		$vars['list'] = $this->listView();
		$this->view = new View($this->registry);
		$data['content'] = $this->view->Render('add.phtml', $vars);
		return $this->Index($data);
	}

	public function add($modules_id='')
	{
		$message='';
		if(isset($_POST['key'], $_POST['value'], $_POST['comment']))
		{
			$where="";
			if($modules_id!='')$where=", modules_id='$modules_id'";
            $param = array($_POST['key'], $_POST['comment']);
            $insert_id = $this->db->insert_id("INSERT INTO `".$this->tb."` SET `key`=?, comment=? $where", $param);
			foreach($this->language as $lang)
			{
				$tb=$lang['language']."_".$this->tb;
				$param = array($_POST['value'], $insert_id);
				$this->db->query("INSERT INTO `$tb` SET `value`=?, `".$this->tb."_id`=?", $param);
			}
			$message.= messageAdmin('Данные успешно добавлены');
		}
		elseif(isset($this->params['addsubsystem']))
		{
            $insert_id = $this->db->insert_id("INSERT INTO `".$this->tb."` SET modules_id='$modules_id'");
			foreach($this->language as $lang)
			{
				$tb=$lang['language']."_".$this->tb;
				$param = array('', $insert_id);
				$this->db->query("INSERT INTO `$tb` SET `value`=?, `".$this->tb."_id`=?", $param);
			}
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
                if(isset($_POST['save_id'], $_POST['name']))
                {
					$count=count($_POST['save_id']) - 1;
                    for($i=0; $i<=$scount; $i++)
                    {
                        $param = array($_POST['name'][$i], $_POST['save_id'][$i]);
                        $this->db->query("UPDATE `".$this->tb."` SET `name`=? WHERE id=?", $param);
                    }
                    $message .= messageAdmin('Данные успешно сохранены');
                }
                else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
            }
            else{
                if(isset($_POST['update']))
                {
					//$this->db->query("DELETE FROM `moderators_permission` WHERE subsystem_id!='0'");
					if(isset($_POST['name'], $_POST['module_id'], $_POST['sub'])&&$_POST['sub']!="")
					{
						$param = array($_POST['name'], $_POST['comment'], $_POST['tables'], $_POST['sub'], $_POST['id']);
						$this->db->query("UPDATE `".$this->tb."` SET `name`=?, `comment`=?, `tables`=?, `sub`=? WHERE id=?", $param);
					}

                    ///
					/*
					'000'-off;
					'100'-read;
					'200'-read/edit;
					'300'-read/del;
					'400'-read/add;
					'500'-read/edit/del;
					'600'-read/edit/add;
					'700'-read/del/add;
					'800'-read/edit/del/add;
					*/
                    if(isset($_POST['module_id'])&&count($_POST['module_id'])!=0)
                    {
						$count=count($_POST['module_id']) - 1;
                        for($i=0; $i<=$count; $i++)
                        {
                            $id = $_POST['module_id'][$i];
                            if(isset($_POST['read'.$id])&&isset($_POST['edit'.$id])&&isset($_POST['del'.$id])&&isset($_POST['add'.$id]))$chmod=800;
                            elseif(isset($_POST['read'.$id])&&!isset($_POST['edit'.$id])&&!isset($_POST['del'.$id])&&!isset($_POST['add'.$id]))$chmod=100;
                            elseif(isset($_POST['read'.$id])&&isset($_POST['edit'.$id])&&!isset($_POST['del'.$id])&&!isset($_POST['add'.$id]))$chmod=200;
                            elseif(isset($_POST['read'.$id])&&!isset($_POST['edit'.$id])&&isset($_POST['del'.$id])&&!isset($_POST['add'.$id]))$chmod=300;
							elseif(isset($_POST['read'.$id])&&!isset($_POST['edit'.$id])&&!isset($_POST['del'.$id])&&isset($_POST['add'.$id]))$chmod=400;
                            elseif(isset($_POST['read'.$id])&&isset($_POST['edit'.$id])&&isset($_POST['del'.$id])&&!isset($_POST['add'.$id]))$chmod=500;
                            elseif(isset($_POST['read'.$id])&&isset($_POST['edit'.$id])&&!isset($_POST['del'.$id])&&isset($_POST['add'.$id]))$chmod=600;
							elseif(isset($_POST['read'.$id])&&!isset($_POST['edit'.$id])&&isset($_POST['del'.$id])&&isset($_POST['add'.$id]))$chmod=700;
                            else $chmod="000";
                            //echo $chmod.'<br />';
                            if(isset($_POST['id']))$param = array($_POST['id'], $id);
							else $param = array($id, $_POST['id2']);
                            $row = $this->db->row("SELECT moderators_type_id FROM `moderators_permission` WHERE moderators_type_id=? AND module_id=? AND subsystem_id='0'", $param);

                            if(isset($_POST['id']))$param = array($chmod, $_POST['id'], $id);
							else $param = array($chmod, $id, $_POST['id2']);
							
                            if($row)$this->db->query("UPDATE `moderators_permission` SET `permission`=? WHERE moderators_type_id=? AND module_id=? AND subsystem_id='0'", $param);
                            else $this->db->query("INSERT INTO `moderators_permission` SET `permission`=?, moderators_type_id=?, module_id=?, subsystem_id='0'", $param);
							
							$row = $this->db->row("SELECT moderators_type_id FROM `moderators_permission` WHERE moderators_type_id=? AND module_id=? AND subsystem_id='0'", array(1, $id));
							$param = array(800, 1, $id);
							if(!$row)$this->db->query("INSERT INTO `moderators_permission` SET `permission`=?, moderators_type_id=?, module_id=?, subsystem_id='0'", $param);
                        }
                    }
					
					if(isset($_POST['subsystem_id'])&&count($_POST['subsystem_id'])!=0)
                    {
						$chmod=800;
						$this->db->query("UPDATE `moderators_permission` SET `permission`='000' WHERE subsystem_id!='0'");
						$count=count($_POST['subsystem_id']) - 1;
                        for($i=0; $i<=$count; $i++)
                        {
                            $id = explode('-', $_POST['subsystem_id'][$i]);
                            $param = array($_POST['id'], $id[0], $id[1]);//echo $id[1].'=';
                            $row = $this->db->row("SELECT moderators_type_id FROM `moderators_permission` WHERE moderators_type_id=? AND module_id=? AND subsystem_id=?", $param);

                            $param = array($chmod, $_POST['id'], $id[0], $id[1]);
                            if($row)$this->db->query("UPDATE `moderators_permission` SET `permission`=? WHERE moderators_type_id=? AND module_id=? AND subsystem_id=?", $param);
                            else $this->db->query("INSERT INTO `moderators_permission` SET `permission`=?, moderators_type_id=?, module_id=?, subsystem_id=?", $param);
							/*
							$param = array(800, 1, $id[0], $id[1]);
							$row = $this->db->row("SELECT moderators_type_id FROM `moderators_permission` WHERE moderators_type_id=? AND module_id=? AND subsystem_id=?", array(1, $id[0], $id[1]));
							if(!$row)$this->db->query("INSERT INTO `moderators_permission` SET `permission`=?, moderators_type_id=?, module_id=?, subsystem_id=?", $param);*/
                        }
						
						
						 
                    }
					$this->db->query("DELETE FROM `moderators_permission` WHERE moderators_type_id='1'");
					$res = $this->db->rows("SELECT * FROM `modules`");
					foreach($res as $row)
					{
						$param = array(1, $row['id']);
						$this->db->query("INSERT INTO `moderators_permission` SET `permission`='800', moderators_type_id=?, module_id=?, subsystem_id='0'", $param);
						
						$res2 = $this->db->rows("SELECT * FROM `subsystem`");
						foreach($res2 as $row2)
						{
							$param = array(1, $row['id'], $row2['id']);
							$this->db->query("INSERT INTO `moderators_permission` SET `permission`='800', moderators_type_id=?, module_id=?, subsystem_id=?", $param);
						}
					}
					$this->db->query("UPDATE `moderators_permission` SET `permission`=? WHERE moderators_type_id=?", array(800, 1));
                    $message.=messageAdmin('Данные успешно сохранены');
                }
                else $message.=messageAdmin('При сохранение произошли ошибки', 'error');
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
				$count=count($_POST['id']) - 1;
				for($i=0; $i<=$count; $i++)
				{
					if($_POST['id'][$i]!=1)
					$this->db->query("DELETE FROM `".$this->tb."` WHERE `id`=?", array($_POST['id'][$i]));
				}
				$message = messageAdmin('Запись успешно удалена');
			}
			elseif(isset($this->params['delete'])&& $this->params['delete']!='')
			{
				$id = $this->params['delete'];
				if($id!=1)
				if($this->db->query("DELETE FROM `".$this->tb."` WHERE `id`=?", array($id)))$message = messageAdmin('Запись успешно удалена');
			}
			elseif(isset($this->params['delsubsystem'])&& $this->params['delsubsystem']!='')
			{
				$id = $this->params['delsubsystem'];
				if($id!=1)
				if($this->db->query("DELETE FROM `".$this->tb."` WHERE `id`=?", array($id)))$message = messageAdmin('Запись успешно удалена');
			}
		}
		return $message;
	}
	
	function listView($where='')
	{
		$vars['list'] = $this->db->rows("SELECT tb.*
										 FROM ".$this->tb." tb
												
										 WHERE id!='1'	
										 ORDER BY tb.`id` DESC");
		return $vars;
	}
	
	public function subcontent($vars=array())
	{
		$vars['modules'] = $this->db->rows("SELECT *, m.comment as name, m.id as type_id
                                            FROM `moderators_type` m
                                          
										    LEFT JOIN `moderators_permission` mp
                                            ON mp.moderators_type_id=m.id AND mp.module_id=?
											
											WHERE m.id!=?
											GROUP BY m.id
                                            ORDER BY m.id ASC",
            array($vars['modules_id'], 1));
		$this->view = new View($this->registry);
		return $this->view->Render('chmod.phtml', $vars);
	}
}
?>