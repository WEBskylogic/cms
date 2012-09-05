<?php
/*
 * вывод каталога компаний и их данных
 */
class ModulesController extends BaseController{

    protected $params;
    protected $db;

    function  __construct($registry, $params)
    {
        $this->tb = "module";
        $this->name = "Управление модулями";
        $this->registry = $registry;
        //$this->db->row("SELECT FROM `moderators_permission` WHERE `id`=?", array($_SESSION['admin']['id']));
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
        $view = new View($this->registry);

        $vars['modules'] = $this->db->rows("SELECT *, m.comment as name
                                            FROM `moderators_type` m
                                            LEFT JOIN `moderators_permission` mp
                                            ON mp.moderators_type_id=m.id AND mp.module_id=?
                                            ORDER BY m.id ASC",
            array($this->params['edit']));
        $vars['menu'] = $this->db->rows("SELECT * FROM `menu_admin` ORDER BY id ASC");
        $data['content'] = $view->Render('edit.phtml', $vars);
        return $this->Render($data);
    }

    public function addAction()
    {
        $vars['message'] = '';
        if(isset($_POST['add']))$vars['message'] = $this->add();
        $vars['dir']=get_directory_list(MODULES);
        $vars['modules'] = $this->db->rows("SELECT *, m.comment as name
                                            FROM `moderators_type` m

                                            ORDER BY m.id ASC");
        $vars['list'] = $this->listView();
        $vars['menu'] = $this->db->rows("SELECT * FROM `menu_admin` ORDER BY id ASC");
        $view = new View($this->registry);
        $data['content'] = $view->Render('add.phtml', $vars);
        return $this->Render($data);
    }

    private function add()
    {

        $message='';
        if(isset($_POST['comment'], $_POST['sub'])&&$_POST['module']!=""&&$_POST['sub']!="")
        {
            ////Добавляем таблицы, если есть db.sql
            $dir=MODULES.$_POST['module']."/admin/data/db.sql";//echo $dir;
            if(file_exists($dir))
            {
                $handle = fopen($dir, "r");
                $contents = fread($handle, filesize($dir));
                fclose($handle);//echo($contents);
                $this->db->query($contents);
            }

            ////Добавляем таблицы связку полей, если есть db_key.sql
            $dir=MODULES.$_POST['module']."/admin/data/db_key.sql";//echo $dir;
            if(file_exists($dir))
            {
                $handle = fopen($dir, "r");
                $contents = fread($handle, filesize($dir));
                fclose($handle);
               if($contents!='')
			   {
				   $contents = explode('#@@#', $contents);
				   foreach($contents as $row)
				   {
						$this->db->query($row);
				   }
			   }
            }

			$param = array($_POST['name'], $_POST['module'], $_POST['comment'], $_POST['tables'], $_POST['sub']);
            $insert_id = $this->db->insert_id("INSERT INTO `".$this->tb."` SET name=?, controller=?, comment=?, tables=?, sub=?", $param);
            
			///Права доступа
            if(count($_POST['module_id'])!=0)
            {
                for($i=0; $i<=count($_POST['module_id']) - 1; $i++)
                {
                    $id = $_POST['module_id'][$i];
                    if(isset($_POST['read'.$id])&&isset($_POST['del'.$id])&&isset($_POST['add'.$id]))$chmod=700;
                    elseif(isset($_POST['read'.$id])&&!isset($_POST['del'.$id])&&isset($_POST['add'.$id]))$chmod=600;
                    elseif(isset($_POST['read'.$id])&&isset($_POST['del'.$id])&&!isset($_POST['add'.$id]))$chmod=500;
                    elseif(isset($_POST['read'.$id])&&!isset($_POST['del'.$id])&&!isset($_POST['add'.$id]))$chmod=400;
                    else $chmod="000";
                    //echo $chmod.'<br />';
                    $param = array($id, $insert_id);
                    $row = $this->db->row("SELECT moderators_type_id FROM `moderators_permission` WHERE moderators_type_id=? AND module_id=?", $param);

                    $param = array($chmod, $id, $insert_id);
                    if($row)$this->db->query("UPDATE `moderators_permission` SET `permission`=? WHERE moderators_type_id=? AND module_id=?", $param);
                    else $this->db->query("INSERT INTO `moderators_permission` SET `permission`=?, moderators_type_id=?, module_id=?", $param);
                }
            }
			
			if(isset($_POST['create_dir']))
			{
				$dir="files/{$_POST['module']}/";
				if(!is_dir($dir))
				{
					mkdir($dir, 0755) ;
				}	
			}
            $message.= messageAdmin('Данные успешно добавлены');
        }
        else $message.= messageAdmin('При добавление произошли ошибки', 'error');
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
                        $this->db->query("UPDATE `".$this->tb."` SET `name`=? WHERE id=?", $param);
                    }
                    $message .= messageAdmin('Данные успешно сохранены');
                }
                else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
            }
            else{
                if(isset($_POST['name'], $_POST['module_id'], $_POST['sub'])&&$_POST['sub']!="")
                {

                    $param = array($_POST['name'], $_POST['comment'], $_POST['tables'], $_POST['sub'], $_POST['id']);
                    $this->db->query("UPDATE `".$this->tb."` SET `name`=?, `comment`=?, `tables`=?, `sub`=? WHERE id=?", $param);

                    ///
                    if(count($_POST['module_id'])!=0)
                    {
                        for($i=0; $i<=count($_POST['module_id']) - 1; $i++)
                        {
                            $id = $_POST['module_id'][$i];
                            if(isset($_POST['read'.$id])&&isset($_POST['del'.$id])&&isset($_POST['add'.$id]))$chmod=700;
                            elseif(isset($_POST['read'.$id])&&!isset($_POST['del'.$id])&&isset($_POST['add'.$id]))$chmod=600;
                            elseif(isset($_POST['read'.$id])&&isset($_POST['del'.$id])&&!isset($_POST['add'.$id]))$chmod=500;
                            elseif(isset($_POST['read'.$id])&&!isset($_POST['del'.$id])&&!isset($_POST['add'.$id]))$chmod=400;
                            else $chmod="000";
                            //echo $chmod.'<br />';
                            $param = array($id, $_POST['id']);
                            $row = $this->db->row("SELECT moderators_type_id FROM `moderators_permission` WHERE moderators_type_id=? AND module_id=?", $param);

                            $param = array($chmod, $id, $_POST['id']);
                            if($row)$this->db->query("UPDATE `moderators_permission` SET `permission`=? WHERE moderators_type_id=? AND module_id=?", $param);
                            else $this->db->query("INSERT INTO `moderators_permission` SET `permission`=?, moderators_type_id=?, module_id=?", $param);
                        }
                    }

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
                    $id = $_POST['id'][$i];
                    $row= $this->db->row("SELECT tables, controller FROM `".$this->tb."` WHERE id=?", array($id));

                    if($row&&$row['tables']!="")
                    {
                        foreach(explode(",", $row['tables']) as $row2)
                        {
                            //echo $row2.'<br />';
                            $this->db->query("DROP TABLE IF EXISTS `".$row2."`");
                        }
                    }
					$dir="files/{$row['controller']}/";
					if($row['controller']!=''&&is_dir($dir))removeDir($dir);
                    $this->db->query("DELETE FROM `moderators_permission` WHERE `module_id`=?", array($id));
                    if($this->db->query("DELETE FROM `".$this->tb."` WHERE `id`=?", array($id)))$message = messageAdmin('Запись успешно удалена');
                }
            }
            elseif(isset($this->params['delete'])&& $this->params['delete']!='')
            {
                $id = $this->params['delete'];
                $row= $this->db->row("SELECT tables, controller FROM `".$this->tb."` WHERE id=?", array($id));

                if($row&&$row['tables']!="")
                {
                    foreach(explode(",", $row['tables']) as $row2)
                    {
                        //echo $row2.'<br />';
                        $this->db->query("DROP TABLE IF EXISTS `".$row2."`");
                    }
                }
				$dir="files/{$row['controller']}/";
				if(is_dir($dir))removeDir($dir);
                $this->db->query("DELETE FROM `moderators_permission` WHERE `module_id`=?", array($id));
                if($this->db->query("DELETE FROM `".$this->tb."` WHERE `id`=?", array($id)))$message = messageAdmin('Запись успешно удалена');
            }
        }
        return $message;
    }

    private function listView()
    {
        $vars['list'] = $this->db->rows("SELECT
											tb.*, tb2.name as cat
										 FROM ".$this->tb." tb
										 LEFT JOIN menu_admin tb2
										  ON tb.sub=tb2.id
											ORDER BY tb.sub asc, tb.`sort` ASC");
        return $vars;
    }
}
?>