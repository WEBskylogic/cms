<?
class Product_status extends Model
{
    static $table='product_status';
    static $name='Статусы товаров';

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
        if(isset($_POST['name'], $_POST['comment'])&&$_POST['name']!="")
        {
            if($_POST['url']=='')$url = String::translit($_POST['name']);
            else $url = String::translit($_POST['url']);

            $id = $this->db->insert_id("INSERT INTO `".$this->table."` SET url=?, comment=?", array($url,$_POST['comment']));

            $languages = $this->db->rows("SELECT * FROM language");
            foreach($languages as $lang)
            {
                $tb=$lang['language']."_".$this->table;
                $this->db->query("INSERT INTO `$tb` SET `name`=?, `".$this->table."_id`=?", array($_POST['name'], $id));
            }
            $message.= messageAdmin('Данные успешно добавлены');
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
            if(isset($_POST['save_id'])&&is_array($_POST['save_id']))
            {
                if(isset($_POST['save_id'], $_POST['name'], $_POST['url'], $_POST['comment']))
                {
                    for($i=0; $i<=count($_POST['save_id']) - 1; $i++)
                    {
                        if($_POST['url'][$i]=='')$url = String::translit($_POST['name'][$i]);
                        else $url = String::translit($_POST['url'][$i]);

                        $message = $this->checkUrl($this->table, $url, $_POST['save_id'][$i]);
                        $this->db->query("UPDATE `".$this->table."` SET `url`=?, `comment`=? WHERE id=?", array($_POST['url'][$i], $_POST['comment'][$i], $_POST['save_id'][$i]));
                        $this->db->query("UPDATE `".$this->registry['key_lang_admin']."_".$this->table."` SET `name`=? WHERE ".$this->table."_id=?", array($_POST['name'][$i], $_POST['save_id'][$i]));
                    }
                    $message .= messageAdmin('Данные успешно сохранены');
                }
                else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
            }
        }
        return $message;
    }
}
?>