<?
class Moderators extends Model
{
    static $table='moderators'; //Главная талица
    static $name='Модераторы'; // primary key
	
	public function __construct($registry)
    {
        parent::getInstance($registry);
    }

    //для доступа к классу через статичекий метод
	public static function getObject($registry)
	{
		return new self::$table($registry);
	}

	public function add($open=false)
    {
        $message='';
        if(isset($_POST['active'], $_POST['login'], $_POST['password'], $_POST['password2'],$_POST['types'])&&$_POST['login']!=""&&$_POST['types']!="")
        {
            $err=Validate::checkPass($_POST['password'], $_POST['password2']);
            $row=$this->db->row("SELECT id FROM `".$this->table."` WHERE login=?", array($_POST['login']));
            if($row)$err="Данный Логин уже занят!";
            if($err=="")
            {
                $pass=md5($_POST['password']);
                $insert_id= $this->db->insert_id("INSERT INTO `".$this->table."` SET
                                                                         `login`=?,
                                                                         `name`=?,
                                                                         `surname`=?,
                                                                         `email`=?,
                                                                         `password`=?,
                                                                         `phone`=?,
                                                                         `skype`=?,
                                                                         `city`=?,
                                                                         `text`=?,
                                                                         `type_moderator`=?,
                                                                         `active`=?", array(
                        $_POST['login'],
                        $_POST['name'],
                        $_POST['surname'],
                        $_POST['email'],
                        $pass,
                        $_POST['phone'],
                        $_POST['skype'],
                        $_POST['city'],
                        $_POST['info'],
                        $_POST['types'],
                        $_POST['active'])
						
                );
				
				$dir="files/moderators/";
                    if(isset($_FILES['photo']['tmp_name'])&&$_FILES['photo']['tmp_name']!="")
						Images::resizeImage($_FILES['photo']['tmp_name'], $dir.$insert_id.".jpg", $dir.$insert_id."_s.jpg", 50, 50);
						
                $message.= messageAdmin('Данные успешно добавлены');
				
				if($open)
				{
					header('Location: /admin/'.$this->table.'/edit/'.$insert_id);
					exit();	
				}
            }
            else $message.= messageAdmin($err, 'error');
        }
        else $message.= messageAdmin('При добавление произошли ошибки', 'error');
        return $message;
    }


    public function save()
    {
        $message='';
        if(isset($this->registry['access']))$message = $this->registry['access'];
        else
        {
            if(isset($_POST['active'], $_POST['email'], $_POST['password'], $_POST['password2'])&&$_POST['login']!="")
            {
                $err='';
                $pass='';
                if($_POST['password']!=""&&$_POST['password2']!="")
                {
                    $err=Validate::checkPass($_POST['password'], $_POST['password2']);
                    $pass="password='".md5($_POST['password'])."',";
                }
                $row=$this->db->row("SELECT id FROM `".$this->table."` WHERE login=? AND id!=?", array($_POST['login'], $_POST['id']));
                if($row)$err="Данный Логин уже занят!";
                if($err=="")
                {

                    $this->db->insert_id("UPDATE `".$this->table."` SET
                                                                         `login`=?,
                                                                         `name`=?,
                                                                         `surname`=?,

                                                                         `email`=?,
                                                                         $pass
                                                                         `phone`=?,
                                                                         `skype`=?,
                                                                         `city`=?,

                                                                         `text`=?,
                                                                         `active`=?
                                          WHERE id=?", array(
                            $_POST['login'],
                            $_POST['name'],
                            $_POST['surname'],
                            $_POST['email'],
                            $_POST['phone'],
                            $_POST['skype'],
                            $_POST['city'],
                            $_POST['info'],
                            $_POST['active'],
                            $_POST['id'])
                    );
                    $dir="files/moderators/";
                    if(isset($_FILES['photo']['tmp_name'])&&$_FILES['photo']['tmp_name']!="")
						Images::resizeImage($_FILES['photo']['tmp_name'], $dir.$_POST['id'].".jpg", $dir.$_POST['id']."_s.jpg", 50, 50);
                    $message.= messageAdmin('Данные успешно сохранены');
                }
                else $message.= messageAdmin($err, 'error');
            }
            else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
        }
        return $message;
    }
}
?>