<?
class Slider extends Model
{
    static $table='slider';
    static $name='Слайдер';

    public function __construct($registry)
    {
        parent::getInstance($registry);
    }

    public static function getObject($registry)
    {
        return new self::$table($registry);
    }


    public function add()
    {
        $message='';
        if(isset($_POST['name'], $_POST['url']))
        {
            $id=$this->db->insert_id("INSERT INTO `".$this->table."` SET url=?, active=?", array($_POST['url'], $_POST['active']));
            $this->db->query("INSERT INTO `".$this->registry['key_lang_admin']."_".$this->table."` SET `name`=?, slider_id=?", array($_POST['name'], $id));

            if(isset($_POST['tmp_image'])&&file_exists("files/tmp/{$_POST['tmp_image']}.jpg"))
            {
                $dir="files/slider/";
                copy("files/tmp/{$_POST['tmp_image']}.jpg", $dir.$id.".jpg");
                copy("files/tmp/{$_POST['tmp_image']}_s.jpg", $dir.$id."_s.jpg");
                unlink("files/tmp/{$_POST['tmp_image']}.jpg");
                unlink("files/tmp/{$_POST['tmp_image']}_s.jpg");
				$this->db->query("UPDATE `".$this->table."` SET photo=? WHERE id=?", array($dir.$id."_s.jpg", $id));
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
                    for($i=0; $i<=count($_POST['save_id']) - 1; $i++)
                    {
                        $this->db->query("UPDATE `".$this->registry['key_lang_admin']."_".$this->table."` SET `name`=? WHERE slider_id=?", array($_POST['name'][$i], $_POST['save_id'][$i]));
                    }
                    $message .= messageAdmin('Данные успешно сохранены');
                }
                else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
            }
            else{
                if(isset($_POST['active'], $_POST['url'], $_POST['id'], $_POST['name']))
                {
                    if($_POST['url']=='')$url = String::translit($_POST['name'], true);
                    else $url = String::translit($_POST['url'], true);

                    $message = $this->checkUrl($this->table, $url, $_POST['id']);
                    $param = array($_POST['active'], $_POST['id']);
                    $this->db->query("UPDATE `".$this->table."` SET `active`=? WHERE id=?", $param);

                    $param = array($_POST['name'], $_POST['id']);
                    $this->db->query("UPDATE `".$this->registry['key_lang_admin']."_".$this->table."` SET `name`=? WHERE `".$this->table."_id`=?", $param);
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
                    $this->db->query("DELETE FROM `".$this->table."` WHERE `id`=?", array($_POST['id'][$i]));
                    if(file_exists("files/slider/{$_POST['id'][$i]}.jpg"))unlink("files/slider/{$_POST['id'][$i]}.jpg");
                    if(file_exists("files/slider/{$_POST['id'][$i]}_s.jpg"))unlink("files/slider/{$_POST['id'][$i]}_s.jpg");
                }
                $message = messageAdmin('Запись успешно удалена');
            }
            elseif(isset($this->params['delete'])&& $this->params['delete']!='')
            {
                $id = $this->params['delete'];
                if($this->db->query("DELETE FROM `".$this->table."` WHERE `id`=?", array($id)))$message = messageAdmin('Запись успешно удалена');
                if(file_exists("files/slider/{$id}.jpg"))unlink("files/slider/{$id}.jpg");
                if(file_exists("files/slider/{$id}_s.jpg"))unlink("files/slider/{$id}_s.jpg");
            }
        }
        return $message;
    }


}
?>