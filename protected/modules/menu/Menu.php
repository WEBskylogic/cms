<?
class Menu extends Model
{
    static $table='menu'; //Главная талица
    static $name="Меню"; //доп таблица
	
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
		if(isset($_POST['sub'], $_POST['active'], $_POST['url'], $_POST['name'], $_POST['title'], $_POST['keywords'], $_POST['description'], $_POST['body'])&&$_POST['name']!="")
		{
			if($_POST['url']=='')$url = String::translit($_POST['name'], true);
			else $url = String::translit($_POST['url'], true);
			
			//////Save meta data
			$meta = new Meta($this->sets);
			$meta->save_meta($this->table, $url, $_POST['title'], $_POST['keywords'], $_POST['description']);
			
			if($_POST['sub']==0)$sub = NULL;
			else $sub = $_POST['sub'];
			$param = array($sub, $_POST['active']);
			$insert_id = $this->db->insert_id("INSERT INTO `".$this->table."` SET `sub`=?, `active`=?", $param);
			$message = $this->checkUrl($this->table, $url, $insert_id);
			foreach($this->language as $lang)
			{
				$tb=$lang['language']."_".$this->table;
				$param = array($_POST['name'], $_POST['body'], $insert_id);
				$this->db->query("INSERT INTO `$tb` SET `name`=?, `body`=?, `".$this->table."_id`=?", $param);
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
						if($_POST['url'][$i]=='')$url = String::translit($_POST['name'][$i], true);
						else $url = String::translit($_POST['url'][$i], true);
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
				if(isset($_POST['sub'], $_POST['active'], $_POST['url'], $_POST['id'], $_POST['name'], $_POST['title'], $_POST['keywords'], $_POST['description'], $_POST['body']))
				{
					if($_POST['url']=='')$url = String::translit($_POST['name'], true);
					else $url = $_POST['url'];
					
					//////Save meta data
					$meta = new Meta($this->sets);
					$meta->save_meta($this->table, $url, $_POST['title'], $_POST['keywords'], $_POST['description']);
					
					$message = $this->checkUrl($this->table, $url, $_POST['id']);
					if($_POST['sub']==0)$sub = NULL;
					else $sub = $_POST['sub'];
					$param = array($sub, $_POST['active'], $_POST['form'], $_POST['id']);
					$this->db->query("UPDATE `".$this->table."` SET `sub`=?, `active`=?, `form`=? WHERE id=?", $param);
					
					$param = array($_POST['name'], $_POST['body'], $_POST['id']);
					$this->db->query("UPDATE `".$this->registry['key_lang_admin']."_".$this->table."` SET `name`=?, `body`=? WHERE `".$this->table."_id`=?", $param);
					$message .= messageAdmin('Данные успешно сохранены');
				}
				else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
			}
		}
		return $message;
	}
}
?>