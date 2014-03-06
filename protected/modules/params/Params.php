<?
class Params extends Model
{
    static $table='params'; //Главная талица
    static $name='Фильтры'; // primary key

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
        if(isset($_POST['name'], $_POST['sub'], $_POST['url']))
        {
            if($_POST['sub']==0)$_POST['sub']=NULL;
			if(!isset($_POST['type']))$_POST['type']=0;
			
            $param = array($_POST['url'], $_POST['sub'], $_POST['type'], $_POST['active']);
            $insert_id = $this->db->insert_id("INSERT INTO `".$this->table."` SET `url`=?, `sub`=?, type=?, active=?", $param);
           

            $languages = $this->db->rows("SELECT * FROM language");
            foreach($languages as $lang)
            {
                $tb=$lang['language']."_".$this->table;
                $this->db->query("INSERT INTO `".$tb."` SET `name`=?, `body`=?, `params_id`=?", array($_POST['name'], $_POST['body'], $insert_id));
            }
			
			if($_POST['url']=='')$url = String::translit($_POST['name'].'-'.$insert_id);
            else $url = String::translit($_POST['url']);
			$url=str_replace('-','',$url);
			$message = $this->checkUrl($this->table, $url, $insert_id);
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
					$count=count($_POST['save_id']) - 1;
                    for($i=0; $i<=$count; $i++)
                    {
						if($_POST['url'][$i]=='')$url = String::translit($_POST['name'][$i]);
                        else $url = $_POST['url'][$i];
						$url=str_replace('-','',$url);
						
						$message = $this->checkUrl($this->table, $url, $_POST['save_id'][$i]);
						
                        $param = array($_POST['name'][$i], $_POST['save_id'][$i]);
                        $this->db->query("UPDATE  `".$this->registry['key_lang_admin']."_".$this->table."` SET `name`=? WHERE params_id=?", $param);
                    }
                    $message .= messageAdmin('Данные успешно сохранены');
                }
                else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
            }
            else{
                if(isset($_POST['active'], $_POST['url'], $_POST['id'], $_POST['name'], $_POST['body']))
                {
                    if($_POST['url']=='')$url = String::translit($_POST['name'].'-'.$_POST['id']);
                    else $url = String::translit($_POST['url']);
					$url=str_replace('-','',$url);
					
                    if($_POST['sub']==0)$sub = NULL;
                    else $sub = $_POST['sub'];
					
					if(!isset($_POST['type']))$_POST['type']='';
					if(!isset($_POST['rgb']))$_POST['rgb']='';
					
                    $message = $this->checkUrl($this->table, $url, $_POST['id']);
                    $param = array($_POST['active'], $sub, $_POST['rgb'], $url, $_POST['type'], $_POST['id']);
                    $this->db->query("UPDATE `".$this->table."` SET `active`=?, `sub`=?, `rgb`=?, `url`=?, type=? WHERE `id`=?", $param);

                    $param = array($_POST['name'], $_POST['body'], $_POST['id']);
                    $this->db->query("UPDATE  `".$this->registry['key_lang_admin']."_".$this->table."` SET `name`=?, `body`=? WHERE `params_id`=?", $param);

                    $message .= messageAdmin('Данные успешно сохранены');
                }
                else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
            }
        }
        return $message;
    }
}
?>