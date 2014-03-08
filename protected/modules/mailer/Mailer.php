<?
class Mailer extends Model
{
    static $table='mailer';
    static $name='Модуль рассылки';
	
	public function __construct($registry)
    {
        parent::getInstance($registry);
    }

	public static function getObject($registry)
	{
		return new self::$table($registry);
	}
    
	
	public function add($queue)
	{
		$message='';
		if(isset($_POST['name'], $_POST['body'])&&$_POST['name']!="")
		{
			$reset = isset($_POST['reset'])?1:0;
			
			$param = array($_POST['active'], $reset);
			$insert_id = $this->db->insert_id("INSERT INTO `".$this->table."` SET `active`=?, `reset_pass`=?", $param);

			foreach($this->language as $lang)
			{
				$tb=$lang['language']."_".$this->table;
				$param = array($_POST['name'], $_POST['body'], $insert_id);
				$this->db->query("INSERT INTO `$tb` SET `name`=?, `text`=?, `mailer_id`=?", $param);
			}
			$message.= messageAdmin('Данные успешно добавлены');
			
			$vars['users'] = $this->db->rows("SELECT * FROM `users`");
			foreach($vars['users'] as $row)
			{
				// Если нужно отправлять не всем, а по признаку.
				//if($row['mailer']=='1') {
					if($row['email']!='')
					$this->db->query("INSERT INTO `$queue` SET mailbody_id=?, user_id=?", array($insert_id, $row['id']));
				//}
			}
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
						$param = array($_POST['name'][$i], $_POST['save_id'][$i]);
						$this->db->query("UPDATE `".$this->registry['key_lang_admin']."_".$this->table."` SET `name`=? WHERE mailer_id=?", $param);
					}
					$message .= messageAdmin('Данные успешно сохранены');
				}
				else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
			}
			else{
				if(isset($_POST['active'], $_POST['id'], $_POST['name'], $_POST['body']))
				{
					$reset = isset($_POST['reset'])?1:0;
					$param = array($_POST['active'], $reset, $_POST['id']);
					$this->db->query("UPDATE `".$this->table."` SET `active`=?, `reset_pass`=? WHERE id=?", $param);
					
					$param = array($_POST['name'], $_POST['body'], $_POST['id']);
					$this->db->query("UPDATE `".$this->registry['key_lang_admin']."_".$this->table."` SET `name`=?, `text`=? WHERE `mailer_id`=?", $param);
					$vars['users'] = $this->db->rows("SELECT `mailer`, `email`, `id` FROM `users`");

					
					$message .= messageAdmin('Данные успешно сохранены');
				}
				else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
			}
		}
		return $message;
	}
	
	public function listView()
	{
		$vars['list'] = $this->db->rows("SELECT
		
										tb.*,
										tb2.name,
										(SELECT COUNT(*) FROM `mail_queue` WHERE mailbody_id = tb.id) as total,
										(SELECT COUNT(*) FROM `mail_queue` WHERE mailbody_id = tb.id AND `delivered`='1') as sent
										
										FROM ".$this->table." tb
									
										LEFT JOIN ".$this->registry['key_lang_admin']."_".$this->table." tb2
										ON tb.id=tb2.mailer_id
										
										LEFT JOIN `mail_queue` tb3
										ON tb.id = tb3.mailbody_id
										GROUP BY tb.id
										ORDER BY tb.`sort` ASC");
		return $vars;
	}
	
	public function listUsersView($id)
	{
		$vars['list'] = $this->db->rows("SELECT tb.*, tb2.email, tb2.name FROM `mail_queue` tb 
										LEFT JOIN `users` tb2
										ON tb.`user_id` = tb2.`id`
										WHERE tb.`mailbody_id`='$id'");
		return $vars['list'];
	}
	
	
	public function deleteusers()
	{	
		if(isset($_POST['u_id'])&&is_array($_POST['u_id']))
		{
			for($i=0; $i<=count($_POST['u_id']) - 1; $i++)
			{
				$dir = Dir::createDir($_POST['u_id'][$i]);
				removeDir($dir[0]);
				$this->db->query("DELETE FROM `mail_queue` WHERE `id`=? AND `mailbody_id`=?", array($_POST['u_id'][$i], $_POST['id']));
			}
			$message = messageAdmin('Записи успешно удалены');
		}
		
		header("Location: /admin/mailer/edit/".$_POST['id']." ");
	}
	
	public function newmail()
	{
		$message_id = $_POST['post_id'];
		$user = $_POST['add_mail'];
		
		$this->db->query("INSERT INTO `mail_queue` WHERE `mailbody_id`=? SET `email`=?", array($message_id, $user));
		$message = messageAdmin('Данные успешно сохранены');
		return $message;
		
		//header("Location: /admin/mailer/edit/".$message_id." ");
	}
	
	public function subscriber($name, $email, $mail=true)
	{
		$data=array();
		$err='';
		$err .= Validate::check($email, $this->translation, 'email');
		$err .= Validate::check($name, $this->translation);
		$row=$this->db->row("SELECT `id`, active FROM `subscribers` WHERE `email`=?", array($email));
		if(isset($row['active'])&&$row['active']==1)$err .= $this->translation['email_exists'];
		if($err=="")
		{
			$code=md5(mktime());
			if(isset($row['active']))$this->db->query("UPDATE `subscribers` SET active='1', code='$code' WHERE email=?", array($email));
			else $this->db->query("INSERT INTO `subscribers` SET name=?, email=?, date_add=?, active='1', code='$code'", array($name, $email, date('Y-m-d H:i:s')));
			
			if($mail)
			{
				$text = "Вы успешно подписались на рассылку на сайта ".$_SERVER['HTTP_HOST']."
						 <div style='margin:35px 0;'><a href='http://".$_SERVER['HTTP_HOST']."/mailer/unsubscribe/code/$code' style='font-size:11px;'>Отписаться</a></div>";
	
				Mail::send($this->settings['sitename'], // имя отправителя
							"info@".$_SERVER['HTTP_HOST'], // email отправителя
							$name, // имя получателя
							$_POST['email'], // email получателя
							"utf-8", // кодировка переданных данных
							"windows-1251", // кодировка письма
							"Подписка на рассылку на сайте ".$this->settings['sitename'], // тема письма
							$text // текст письма
							);
			}
			$data['message']=$this->translation['email_added'];
		}
		else $data['err']=$err;	
		return $data;
	}
	
	public function unsubscribe($code)
	{
		$err='';
		$row=$this->db->row("SELECT `id` FROM `subscribers` WHERE `code`=?", array($code));
		if(!$row)$err .= $this->translation['email_exists2'];
		if($err=="")
		{
			$this->db->query("UPDATE `subscribers` SET active='0' WHERE code=?", array($code));
			$message='<div class="done">'.$this->translation['email_deleted'].'</div>';
		}
		else $message='<div class="err">'.$err.'</div>';	
		return $message;	
	}
	
	public function add_subscribers()
	{
		$this->db->query("INSERT INTO `subscribers` SET active='0'");
		return messageAdmin('Данные успешно сохранены');
	}
	
	public function save_subscribers()
	{	
		if(isset($_POST['save_id'], $_POST['email'], $_POST['name'])&&is_array($_POST['save_id']))
		{
			$count=count($_POST['save_id']) - 1;
			for($i=0; $i<=$count; $i++)
			{
				$this->db->query("UPDATE `subscribers` SET name=?, email=? WHERE `id`=?", array($_POST['name'][$i], $_POST['email'][$i], $_POST['save_id'][$i]));
			}
			return messageAdmin('Записи успешно удалены');
		}
	}
}
?>