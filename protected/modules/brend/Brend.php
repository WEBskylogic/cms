<?
class Brend extends Model
{
    static $table='brend'; //Главная талица
    static $name='Брэнды'; // primary key
	
	public function __construct($registry)
    {
        parent::getInstance($registry);
    }

    //для доступа к классу через статичекий метод
	public static function getObject($registry)
	{
		return new self::$table($registry);
	}

	public function add()
	{
		$message='';
		if(isset($_POST['active'], $_POST['url'], $_POST['name'], $_POST['title'], $_POST['keywords'], $_POST['description'], $_POST['body'])&&$_POST['name']!="")
		{
			if($_POST['url']=='')$url = String::translit($_POST['name']);
			else $url = String::translit($_POST['url']);

			$param = array($_POST['active']);
			$insert_id = $this->db->insert_id("INSERT INTO `".$this->table."` SET `active`=?", $param);
			$message = $this->checkUrl($this->table, $url, $insert_id);
			$res = $this->db->rows("SELECT * FROM language");
			foreach($res as $lang)
			{
				$tb=$lang['language']."_".$this->table;
				$param = array($_POST['name'], $_POST['title'], $_POST['keywords'], $_POST['description'], $_POST['body'], $insert_id);
				$this->db->query("INSERT INTO `$tb` SET `name`=?, `title`=?, `keywords`=?, `description`=?, `body`=?, `".$this->table."_id`=?", $param);
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
				if(isset($_POST['save_id'], $_POST['name'], $_POST['url']))
				{
					for($i=0; $i<=count($_POST['save_id']) - 1; $i++)
					{
						if($_POST['url'][$i]=='')$url = String::translit($_POST['name'][$i]);
						else $url = $_POST['url'][$i];
						//echo $_POST['name'][$i].'<br>';
						$message = $this->checkUrl($this->table, $url, $_POST['save_id'][$i]);
						$param = array($_POST['name'][$i], $_POST['save_id'][$i]);
						$this->db->query("UPDATE `".$this->registry['key_lang_admin']."_".$this->table."` SET `name`=? WHERE ".$this->table."_id=?", $param);
					}
					$message .= messageAdmin('Данные успешно сохранены');
				}
				else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
			}
			else{
				if(isset($_POST['active'], $_POST['url'], $_POST['id'], $_POST['name'], $_POST['title'], $_POST['keywords'], $_POST['description'], $_POST['body']))
				{
					if($_POST['url']=='')$url = String::translit($_POST['name']);
					else $url = String::translit($_POST['url']);
					
					$message = $this->checkUrl($this->table, $url, $_POST['id']);
					$param = array($_POST['active'], $_POST['id']);
					$this->db->query("UPDATE `".$this->table."` SET `active`=? WHERE id=?", $param);
					
					$param = array($_POST['name'], $_POST['title'], $_POST['keywords'], $_POST['description'], $_POST['body'], $_POST['id']);
					$this->db->query("UPDATE `".$this->registry['key_lang_admin']."_".$this->table."` SET `name`=?, `title`=?, `keywords`=?, `description`=?, `body`=? WHERE `".$this->table."_id`=?", $param);
					$message .= messageAdmin('Данные успешно сохранены');
				}
				else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
			}
		}
		return $message;
	}
}
?>