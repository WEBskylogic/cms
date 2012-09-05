<?php
/*
 * вывод каталога компаний и их данных
 */
class ModeratorstypeController extends BaseController{

    protected $params;
    protected $db;

    function  __construct($registry, $params)
    {
        $this->tb = "moderators_type";
        $this->name = "Группы модераторов";
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
        $data['styles']=array('timepicker.css');
        $data['scripts']=array('timepicker.js');
        $vars['modules'] = $this->db->rows("SELECT *
                                            FROM `module` m
                                            LEFT JOIN `moderators_permission` mp
                                            ON mp.module_id=m.id AND mp.moderators_type_id=?
                                            WHERE m.id!=?
                                            ORDER BY m.id ASC",
            array($this->params['edit'], 17));
        $data['content'] = $view->Render('edit.phtml', $vars);
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

    private function add()
    {
        $message='';
        if(isset($_POST['comment']))
        {
            $param = array($_POST['comment']);
            $this->db->query("INSERT INTO `".$this->tb."` SET comment=?", $param);
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
                if(isset($_POST['save_id'], $_POST['name'], $_POST['value'], $_POST['comment']))
                {
                    for($i=0; $i<=count($_POST['save_id']) - 1; $i++)
                    {
                        $param = array($_POST['comment'][$i], $_POST['save_id'][$i]);
                        $this->db->query("UPDATE `".$this->tb."` SET `comment`=? WHERE id=?", $param);
                    }
                    $message .= messageAdmin('Данные успешно сохранены');
                }
                else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
            }
            else{
                if(isset($_POST['name'], $_POST['module_id']))
                {

                    $param = array($_POST['name'], $_POST['id']);
                    $this->db->query("UPDATE `".$this->tb."` SET `comment`=? WHERE id=?", $param);

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
                            $param = array($_POST['id'], $id);
                            $row = $this->db->row("SELECT moderators_type_id FROM `moderators_permission` WHERE moderators_type_id=? AND module_id=?", $param);

                            $param = array($chmod, $_POST['id'], $id);
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
        $vars['list'] = $this->db->rows("SELECT
											tb.*
										 FROM ".$this->tb." tb
											ORDER BY tb.`id` DESC");
        return $vars;
    }
}
?>