<?
class Video extends Model
{
    static $table='video';
    static $name='Видео';

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
        if(isset($_POST['name'], $_POST['body'])&&$_POST['name']!="")
        {
            $insert_id = $this->db->insert_id("INSERT INTO `".$this->table."` SET `sort`=?, `body`=?", array(1, $_POST['body']));
            $languages = $this->db->rows("SELECT * FROM language");
            foreach($languages as $lang)
            {
                $tb=$lang['language']."_".$this->table;
                $param = array($_POST['name'], $insert_id);
                $this->db->query("INSERT INTO `$tb` SET `name`=?, `".$this->table."_id`=?", $param);
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
						if($_POST['url'][$i]=='')$url = String::translit($_POST['name'][$i]);
                        else $url = $_POST['url'][$i];
						$message = $this->checkUrl($this->table, $url, $_POST['save_id'][$i]);
						
                        $param = array($_POST['name'][$i], $_POST['save_id'][$i]);
                        $this->db->query("UPDATE `".$this->registry['key_lang_admin']."_".$this->table."` SET `name`=? WHERE ".$this->table."_id=?", $param);
                    }
                    $message .= messageAdmin('Данные успешно сохранены');
                }
                else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
            }
            else{
                if(isset($_POST['id'], $_POST['name'], $_POST['body']))
                {
                    $param = array($_POST['name'], $_POST['id']);
                    $this->db->query("UPDATE `".$this->registry['key_lang_admin']."_".$this->table."` SET `name`=? WHERE `".$this->table."_id`=?", $param);

                    $param = array($_POST['body'], $_POST['id']);
                    $this->db->query("UPDATE `".$this->table."` SET `body`=? WHERE `id`=?", $param);
                    $message .= messageAdmin('Данные успешно сохранены');
                }
                else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
            }
        }
        return $message;
    }
}
?>