<?
class Info extends Model
{
    static $table='info_blocks';
    static $name='Информационные блоки';
	
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
            $insert_id = $this->db->insert_id("INSERT INTO `".$this->table."` SET `sort`=?, url=?", array(1, $_POST['url']));

            $languages = $this->db->rows("SELECT * FROM language");
            foreach($languages as $lang)
            {
                $tb=$lang['language']."_".$this->table;
                $param = array($_POST['name'], $_POST['body'], $insert_id);
                $this->db->query("INSERT INTO `$tb` SET `name`=?, `body`=?, `info_blocks_id`=?", $param);
            }
            $message.= messageAdmin('Данные успешно добавлены');
        }
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
						$this->db->query("UPDATE `".$this->table."` SET `url`=? WHERE `id`=?", array($_POST['url'][$i], $_POST['save_id'][$i]));
                        $param = array($_POST['name'][$i], $_POST['save_id'][$i]);
                        $this->db->query("UPDATE `".$this->registry['key_lang_admin']."_".$this->table."` SET `name`=? WHERE info_blocks_id=?", $param);
                    }
                    $message .= messageAdmin('Данные успешно сохранены');
                }
                else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
            }
            else{
                if(isset($_POST['id'], $_POST['name'], $_POST['body']))
                {
					$this->db->query("UPDATE `".$this->table."` SET `url`=? WHERE `id`=?", array($_POST['url'], $_POST['id']));
                    $param = array($_POST['name'], $_POST['body'], $_POST['id']);
                    $this->db->query("UPDATE `".$this->registry['key_lang_admin']."_".$this->table."` SET `name`=?, `body`=? WHERE `info_blocks_id`=?", $param);
                    $message .= messageAdmin('Данные успешно сохранены');
                }
                else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
            }
        }
        return $message;
    }
}
?>