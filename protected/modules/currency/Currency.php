<?
class Currency extends Model
{
    static $table='currency'; //Главная талица
    static $name='Курс валюты'; // primary key

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
        if(isset($_POST['name']))
        {
            $param = array($_POST['name'], $_POST['icon'], $_POST['rate'], $_POST['position']);
            $this->db->query("INSERT INTO `".$this->table."` SET `name`=?, icon=?, rate=?, position=?", $param);
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

                if(isset($_POST['save_id'], $_POST['name'], $_POST['icon'], $_POST['position'], $_POST['rate']))
                {
                    for($i=0; $i<=count($_POST['save_id']) - 1; $i++)
                    {
                        $param = array($_POST['name'][$i], $_POST['icon'][$i], $_POST['rate'][$i], $_POST['position'][$i], 0, $_POST['save_id'][$i]);
                        $this->db->query("UPDATE `".$this->table."` SET `name`=?, icon=?, rate=?, position=?, base=? WHERE id=?", $param);
                    }
                    $message .= messageAdmin('Данные успешно сохранены');
                    if(isset($_POST['base']))$this->db->query("UPDATE `".$this->table."` SET base=? WHERE id=?", array(1, $_POST['base']));
                }
                else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
            }
        }
        return $message;
    }

}
?>