<?
class Photos extends Model
{
    static $table='photos';
    static $name= 'Фотогалерея';
	
	public function __construct($registry)
    {
        parent::getInstance($registry);
    }

	public static function getObject($registry)
	{
		return new self::$table($registry);
	}

    // Удалить одно фото из альбома
    public function delPhoto($id)
    {
        if($this->db->query("DELETE FROM `photo` WHERE `id`=?", array($id)))$message = messageAdmin('Запись успешно удалена');
        if(file_exists("files/photos/{$this->params['edit']}/{$id}.jpg"))unlink("files/photos/{$this->params['edit']}/{$id}.jpg");
        if(file_exists("files/photos/{$this->params['edit']}/{$id}_s.jpg"))unlink("files/photos/{$this->params['edit']}/{$id}_s.jpg");
    }

    // Удалить несколько фото из альбома
    public function deletePhotos()
    {
        for($i=0; $i<=count($_POST['photo_id']) - 1; $i++)
        {
            $this->db->query("DELETE FROM `photo` WHERE `id`=?", array($_POST['photo_id'][$i]));
            if(file_exists("files/photos/{$this->params['edit']}/{$_POST['photo_id'][$i]}.jpg"))unlink("files/photos/{$this->params['edit']}/{$_POST['photo_id'][$i]}.jpg");
            if(file_exists("files/photos/{$this->params['edit']}/{$_POST['photo_id'][$i]}_s.jpg"))unlink("files/photos/{$this->params['edit']}/{$_POST['photo_id'][$i]}_s.jpg");
        }
        $message = messageAdmin('Запись успешно удалена');
    }

    public function loadPhotos($albumId, $active='')
    {
        if($active!='')$active = " AND tb.active='1'";
        $photos =  $this->db->rows("SELECT *
									FROM `photo` tb
									
									LEFT JOIN `".$this->registry['key_lang_admin']."_photo` tb2
									ON tb.id=tb2.photo_id
			
									WHERE photos_id=? $active
									ORDER BY sort ASC, id DESC", array($albumId));

        return $photos;
    }

    public function add($open=false)
    {
        $message='';
        if(isset($_POST['active'], $_POST['url'], $_POST['name'], $_POST['title'], $_POST['keywords'], $_POST['description'], $_POST['body'])&&$_POST['name']!="")
        {
            if($_POST['url']=='')$url = String::translit($_POST['name']);
            else $url = String::translit($_POST['url']);
			
			//////Save meta data
			$meta = new Meta($this->sets);
			$meta->save_meta($this->table, $url, $_POST['title'], $_POST['keywords'], $_POST['description']);
			
			
            $param = array($_POST['active']);
            $insert_id = $this->db->insert_id("INSERT INTO `".$this->table."` SET `active`=?", $param);
            $message = $this->checkUrl($this->table, $url, $insert_id);

            $languages = $this->db->rows("SELECT * FROM language");
            foreach($languages as $lang)
            {
                $tb=$lang['language']."_".$this->table;
                $param = array($_POST['name'], $_POST['body'], $insert_id);
                $this->db->query("INSERT INTO `$tb` SET `name`=?, `body`=?, `photos_id`=?", $param);
            }
			
			////Photo
            if(isset($_POST['current_photo'])&&file_exists($_POST['current_photo']))
            {
				$ext = pathinfo($_POST['current_photo'], PATHINFO_EXTENSION);
                $dir="files/photos/";
                copy(str_replace('_s', '', $_POST['current_photo']), $dir.$insert_id.".".$ext);
                copy($_POST['current_photo'], $dir.$insert_id."_s.".$ext);
				
				$photo_m=str_replace('_s', '_m', $_POST['current_photo']);
				if(file_exists($photo_m))copy($photo_m, $dir.$insert_id."_m.".$ext);
				
				$this->photo_del("files/tmp/", $_POST['tmp_image']);
				$this->db->query("UPDATE `".$this->table."` SET `photo`=? WHERE `id`=?", array($dir.$insert_id."_s.".$ext, $insert_id));
            }
			
			if($open)
			{
				header('Location: /admin/'.$this->table.'/edit/'.$insert_id);
				exit();	
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
                if(isset($_POST['save_id'], $_POST['name'], $_POST['url']))
                {
                    for($i=0; $i<=count($_POST['save_id']) - 1; $i++)
                    {
                        if($_POST['url'][$i]=='')$url = String::translit($_POST['name'][$i]);
                        else $url = $_POST['url'][$i];

                        //echo $_POST['name'][$i].'<br>';
                        $message = $this->checkUrl($this->table, $url, $_POST['save_id'][$i]);
                        $param = array($_POST['name'][$i], $_POST['save_id'][$i]);
                        $this->db->query("UPDATE `".$this->registry['key_lang_admin']."_".$this->table."` SET `name`=? WHERE photos_id=?", $param);
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
                    $param = array($_POST['active'], $_POST['id']);
                    $this->db->query("UPDATE `".$this->table."` SET `active`=? WHERE id=?", $param);

                    $param = array($_POST['name'], $_POST['body'], $_POST['id']);
                    $this->db->query("UPDATE `".$this->registry['key_lang_admin']."_".$this->table."` SET `name`=?, `body`=? WHERE `photos_id`=?", $param);

                    if(isset($_POST['save_photo_id'], $_POST['photo_name']))
                    {
                        for($i=0; $i<=count($_POST['save_photo_id']) - 1; $i++)
                        {
                            $param = array($_POST['photo_name'][$i], $_POST['save_photo_id'][$i]);
                            $this->db->query("UPDATE `".$this->registry['key_lang_admin']."_photo` SET `name`=? WHERE photo_id=?", $param);
                        }
                    }

                    if(isset($_FILES['extra_files']))
                    {
						$dir="files/photos/{$_POST['id']}/";
						for($i=0; $i<=count($_FILES['extra_files']) - 1; $i++)
                        {
							if(isset($_FILES['extra_files']['tmp_name'][$i])&&$_FILES['extra_files']['tmp_name'][$i]!="")
							{
								$this->loadExtraPhoto($_FILES['extra_files']['tmp_name'][$i], $_FILES['extra_files']['name'][$i], 'photo', 'photos', $_POST['id'], $dir, $this->settings['width_photos_extra'], $this->settings['height_photos_extra']);
								
							}
						}
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