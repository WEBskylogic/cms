<?php
/*
 * вывод каталога компаний и их данных
 */
class ManagersController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		$this->tb = "users";
		$this->name = "Менеджеры";
		$this->tb_lang = $this->key_lang.'_'.$this->tb;
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
											tb2.comment as status
										FROM ".$this->tb." tb
											LEFT JOIN
												user_status tb2
											ON
												tb.status_id=tb2.id
										WHERE
											tb.id=? AND tb.status_id=?",
            array($this->params['edit'], 2));
        $vars['list'] = $this->listView();
        $view = new View($this->registry);
        $data['content'] = $view->Render('edit.phtml', $vars);
        return $this->Render($data);
    }


    private function add()
    {
        $message='';
        if(isset($_POST['active'], $_POST['email'], $_POST['password'], $_POST['password2'])&&$_POST['email']!="")
        {
            $err=checkPass($_POST['password'], $_POST['password2']);
            $row=$this->db->row("SELECT id FROM `".$this->tb."` WHERE email=?", array($_POST['email']));
            if($row)$err="Данный E-mail уже зарегистрирован!";
            if($err=="")
            {
                $pass=md5($_POST['password']);
                $start_date=date("Y-m-d H:i:s");
                $id=$this->db->insert_id("INSERT INTO `".$this->tb."` SET
                                                                         `name`=?,
                                                                         `surname`=?,
                                                                         `patronymic`=?,
                                                                         `email`=?,
                                                                         `pass`=?,
                                                                         `phone`=?,
                                                                         `skype`=?,
                                                                         `city`=?,
                                                                         `street`=?,
                                                                         `building`=?,
                                                                         `start_date`=?,
                                                                         `info`=?,
                                                                         `status_id`=?,
                                                                         `active`=?", array(
                        $_POST['name'],
                        $_POST['surname'],
                        $_POST['patronymic'],
                        $_POST['email'],
                        $pass,
                        $_POST['phone'],
                        $_POST['skype'],
                        $_POST['city'],
                        $_POST['street'],
                        $_POST['building'],
                        $start_date,
                        $_POST['info'],
                        2,
                        1)
                );
                $dir="files/users/";
                if(isset($_FILES['photo']['tmp_name'])&&$_FILES['photo']['tmp_name']!="")resizeImage($_FILES['photo']['tmp_name'], $dir.$id.".jpg", $dir.$id."_s.jpg", 214, 159);
                $message.= messageAdmin('Данные успешно добавлены');
            }
            else $message.= messageAdmin($err, 'error');
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
            if(isset($_POST['active'], $_POST['email'], $_POST['password'], $_POST['password2'])&&$_POST['email']!="")
            {
                $err='';
                $pass='';
                if($_POST['password']!=""&&$_POST['password2']!="")
                {
                    $err=checkPass($_POST['password'], $_POST['password2']);
                    $pass="pass='".md5($_POST['password'])."',";
                }
                if($err=="")
                {

                    $this->db->insert_id("UPDATE `".$this->tb."` SET
                                                                         `name`=?,
                                                                         `surname`=?,
                                                                         `patronymic`=?,
                                                                         `email`=?,
                                                                         $pass
                                                                         `phone`=?,
                                                                         `skype`=?,
                                                                         `city`=?,
                                                                         `street`=?,
                                                                         `building`=?,
                                                                         `info`=?,
                                                                         `active`=?
                                          WHERE id=?", array(
                            $_POST['name'],
                            $_POST['surname'],
                            $_POST['patronymic'],
                            $_POST['email'],
                            $_POST['phone'],
                            $_POST['skype'],
                            $_POST['city'],
                            $_POST['street'],
                            $_POST['building'],
                            $_POST['info'],
                            $_POST['active'],
                            $_POST['id'])
                    );
                    $dir="files/users/";
                    if(isset($_FILES['photo']['tmp_name'])&&$_FILES['photo']['tmp_name']!="")resizeImage($_FILES['photo']['tmp_name'], $dir.$_POST['id'].".jpg", $dir.$_POST['id']."_s.jpg", 214, 159);
                    $message.= messageAdmin('Данные успешно сохранены');
                }
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
											tb.*,
											tb2.comment as status
										 FROM ".$this->tb." tb
											LEFT JOIN
												user_status tb2
											ON
												tb.status_id=tb2.id
										 WHERE tb.status_id=?
										 ORDER BY tb.`start_date` DESC",
            array(2));
        return $vars;
    }
}
?>