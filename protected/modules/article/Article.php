<?php

class Article extends Model
{
    static $table='article'; //Главная талица
    static $name='Статьи'; // primary key

    public function __construct($registry)
    {
        parent::getInstance($registry);
    }

    //для доступа к классу через статичекий метод
    public static function getObject($registry)
    {
        return new self::$table($registry);
    }
	
	function addQuery($q, $param)
	{
		$query='';
		preg_match('^__(.*)\__^', $q, $match);
		$i=0;
		foreach($match as $row)
		{
			echo $match[$i].'<br />';
			$query.='';
			$i++;
		}
	}

	function add()
	{
		$message='';
		if(isset($_POST['active'], $_POST['date'], $_POST['url'], $_POST['name'], $_POST['title'], $_POST['keywords'], $_POST['description'], $_POST['body'], $_POST['body_m'])&&$_POST['name']!="")
		{
			if($_POST['url']=='')$url = String::translit($_POST['name']);
			else $url = String::translit($_POST['url']);
			
			//////Save meta data
			$meta = new Meta($this->sets);
			$meta->save_meta($this->table, $url, $_POST['title'], $_POST['keywords'], $_POST['description']);
			
			if($_POST['date']=='0000-00-00 00:00:00')$date = date("Y-m-d H:i:s");
			else $date = $_POST['date'];
			
			$param = array($_POST['active'], $date);
			$insert_id = $this->db->insert_id("INSERT INTO `".$this->table."` SET `active`=?, date=?", $param);
			$message = $this->checkUrl($this->table, $url, $insert_id);
            $language = $this->db->rows("SELECT * FROM `language`");
			foreach($language as $lang)
			{
				$tb=$lang['language']."_".$this->table;
				$param = array($_POST['name'], $_POST['title'], $_POST['keywords'], $_POST['description'], $_POST['body_m'], $_POST['body'], $insert_id);
				$this->db->query("INSERT INTO `$tb` SET `name`=?, `title`=?, `keywords`=?, `description`=?, `body_m`=?, `body`=?, `".$this->table."_id`=?", $param);
			}
			
			////Photo
			if(isset($_POST['tmp_image'])&&file_exists("files/tmp/{$_POST['tmp_image']}.jpg"))
			{
				$dir="files/news/";
				copy("files/tmp/{$_POST['tmp_image']}.jpg", $dir.$insert_id.".jpg");
				copy("files/tmp/{$_POST['tmp_image']}_s.jpg", $dir.$insert_id."_s.jpg");
				unlink("files/tmp/{$_POST['tmp_image']}.jpg");
				unlink("files/tmp/{$_POST['tmp_image']}_s.jpg");
			}
			$message.= messageAdmin('Данные успешно добавлены');
		}
		else $message.= messageAdmin('При добавление произошли ошибки', 'error');	
		return $message;
	}
	
	function save()
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
						
						if($_POST['date'][$i]=='0000-00-00 00:00:00')$date = date("Y-m-d H:i:s");
						else $date = $_POST['date'][$i];
			
						//echo $_POST['name'][$i].'<br>';
						$message = $this->checkUrl($this->table, $url, $_POST['save_id'][$i]);
						$param = array($_POST['name'][$i], $_POST['save_id'][$i]);
						$this->db->query("UPDATE `".$this->table."` SET `date`=? WHERE id=?", array($_POST['date'][$i], $_POST['save_id'][$i]));
						$this->db->query("UPDATE  `".$this->registry['key_lang_admin']."_".$this->table."` SET `name`=? WHERE ".$this->table."_id=?", $param);
					}
					$message .= messageAdmin('Данные успешно сохранены');
				}
				else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
			}
			else{
				if(isset($_POST['active'], $_POST['url'], $_POST['id'], $_POST['name'], $_POST['title'], $_POST['keywords'], $_POST['description'], $_POST['body_m'], $_POST['body']))
				{
					if($_POST['url']=='')$url = String::translit($_POST['name']);
					else $url = String::translit($_POST['url']);
					
					//////Save meta data
					$meta = new Meta($this->sets);
					$meta->save_meta($this->table, $url, $_POST['title'], $_POST['keywords'], $_POST['description']);
			
					if($_POST['date']=='0000-00-00 00:00:00')$date = date("Y-m-d H:i:s");
					else $date = $_POST['date'];
			
					$message = $this->checkUrl($this->table, $url, $_POST['id']);
					$param = array($_POST['active'], $date, $_POST['id']);
					$this->db->query("UPDATE `".$this->table."` SET `active`=?, `date`=? WHERE id=?", $param);
					
					$param = array($_POST['name'], $_POST['title'], $_POST['keywords'], $_POST['description'], $_POST['body_m'], $_POST['body'], $_POST['id']);
					$this->db->query("UPDATE  `".$this->registry['key_lang_admin']."_".$this->table."` SET `name`=?, `title`=?, `keywords`=?, `description`=?, `body_m`=?, `body`=? WHERE `".$this->table."_id`=?", $param);
					$message .= messageAdmin('Данные успешно сохранены');
				}
				else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
			}
		}
		return $message;
	}
	
	function getList()
	{
		$q="SELECT ".$data['select']."
			 FROM `".$this->tb."` tb1
			 LEFT JOIN ".$this->key_lang."_".$this->tb." tb2
			 ON tb1.id=tb2.".$this->tb."_id
			 WHERE  tb1.active=? ".$data['where']."
			 ORDER BY ".$data['order']." ".$data['limit'];
			 
		if($data['order']=='')
		{
			
		}
		else{
			$size_page =10;
			$start_page = 0;
			$cur_page = 0;
			$vars['paging'] = '';
		
			if(isset($this->params['page']))
			{
				$cur_page = $this->params['page'];
				$start_page = ($cur_page-1) * $size_page;//номер начального элемента
			}
			
			$sql = $q." LIMIT ".$start_page.", ".$size_page."";
			//echo $sql;
			$count = $this->db->query($q);//кол страниц
			if($count > $size_page)
			{
				$vars['paging'] = Paging::MakePaging($cur_page, $count, $size_page);//вызов шаблона для постраничной навигации
			}
			$vars['list'] = $this->db->rows($sql);
		}
	}
}
?>