<?
class Pages extends Model
{
    static $table='pages'; //Главная талица
    static $name='Содержимое'; // primary key

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
			
			//////Save meta data
			$meta = new Meta($this->sets);
			$meta->save_meta($this->table, $url, $_POST['title'], $_POST['keywords'], $_POST['description']);
			
            $param = array($_POST['active'], $_POST['form']);
            $insert_id = $this->db->insert_id("INSERT INTO `".$this->table."` SET `active`=?, `form`=?", $param);
            $message = $this->checkUrl($this->table, $url, $insert_id);
            $languages = $this->db->rows("SELECT * FROM language");
            foreach($languages as $lang)
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
					
					//////Save meta data
					$meta = new Meta($this->sets);
					$meta->save_meta($this->table, $url, $_POST['title'], $_POST['keywords'], $_POST['description']);
			
                    $message = $this->checkUrl($this->table, $url, $_POST['id']);
                    $param = array($_POST['active'], $_POST['form'], $_POST['id']);
                    $this->db->query("UPDATE `".$this->table."` SET `active`=?, `form`=? WHERE id=?", $param);

                    $param = array($_POST['name'], $_POST['body'], $_POST['id']);
                    $this->db->query("UPDATE `".$this->registry['key_lang_admin']."_".$this->table."` SET `name`=?, `body`=? WHERE `".$this->table."_id`=?", $param);
                    $message .= messageAdmin('Данные успешно сохранены');
                }
                else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
            }
        }
        return $message;
    }
	
	function feedback($name, $email, $phone, $text, $captcha)
	{
		$error="";
		if(!Captcha3D::check($captcha))$error.="<div class='err'>".$this->translation['wrong_code']."</div>";
		$error.=Validate::check($email, $this->translation, 'email');
		$error.=Validate::check(array($name, $text, $captcha), $this->translation);
		if($error=="")
		{
			$text = "
			ФИО: {$name}<br />
			E-mail: {$email}<br />
			Телефон: {$phone}<br /><br />
			
			Сообщение: {$text}";
			$this->insert_post_form($text);
			//$this->sendMail($email, $name, $city, $tel, $text);
			Mail::send($name, // имя отправителя
						"info@".$_SERVER['HTTP_HOST'], // email отправителя
						$this->settings['sitename'], // имя получателя
						$this->settings['email'], // email получателя
						"utf-8", // кодировка переданных данных
						"utf-8", // кодировка письма
						"Сообщения от посетителя сайта ".$this->settings['sitename'], // тема письма
						$text // текст письма
						);
			$vars['message']='<div class="done">'.$this->translation['message_sent'].'</div>';		
			$vars['send']=1;
		}
		else $vars['message'] = $error;	
		return $vars;
	}
}
?>