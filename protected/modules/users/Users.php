<?php
class Users extends Model
{
    static $table='users'; //Главная талица
    static $name='Пользователи'; // primary key

    public function __construct($registry)
    {
        parent::getInstance($registry);
    }

    //для доступа к классу через статичекий метод
    public static function getObject($registry)
    {
        return new self::$table($registry);
    }
	
	public function add($open=false)
	{
		$message='';
		if(isset($_POST['active'], $_POST['email'], $_POST['password'], $_POST['password2']))
		{
            $err=Validate::checkPass($_POST['password'], $_POST['password2'], $this->translation);
            $row=$this->db->row("SELECT id FROM `".$this->table."` WHERE email=?", array($_POST['email']));
            if($row)$err="Данный E-mail уже зарегистрирован!";
			if($err=="")
            {
				//var_info($_POST);exit();
                $pass=md5($_POST['password']);
                $start_date=date("Y-m-d H:i:s");
                $id=$this->db->insert_id("INSERT INTO `".$this->table."` SET
                                                                         `name`=?,
                                                                         `surname`=?,
                                                                         `patronymic`=?,
                                                                         `email`=?,
                                                                         `pass`=?,
                                                                         `discount`=?,
																		 `phone`=?,
                                                                         `skype`=?,
                                                                         `city`=?,
                                                                         `address`=?,
                                                                         `post_index`=?,
                                                                         `start_date`=?,
                                                                         `info`=?,
                                                                         `status_id`=?,
                                                                         `active`=?", array(
                                                                         $_POST['name'],
                                                                         $_POST['surname'],
                                                                        $_POST['patronymic'],
                                                                        $_POST['email'],
                                                                        $pass,
                                                                        $_POST['discount'],
																		$_POST['phone'],
                                                                        $_POST['skype'],
                                                                        $_POST['city'],
                                                                        $_POST['address'],
                                                                        $_POST['post_index'],
                                                                        $start_date,
                                                                        $_POST['info'],
                                                                        $_POST['status_id'],
                                                                        1)
                );
                $dir="files/users/";
                if(isset($_FILES['photo']['tmp_name'])&&$_FILES['photo']['tmp_name']!="")Images::resizeImage($_FILES['photo']['tmp_name'], $dir.$id.".jpg", $dir.$id."_s.jpg", 214, 159);
                $message.= messageAdmin('Данные успешно добавлены');
				
				if($open)
				{
					header('Location: /admin/'.$this->table.'/edit/'.$id);
					exit();	
				}
            }
            else $message.= messageAdmin($err, 'error');
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
            if(isset($_POST['active'], $_POST['email'], $_POST['password'], $_POST['password2']))
            {
                $err='';
                $pass='';
                if($_POST['password']!=""&&$_POST['password2']!="")
                {
                    $err=Validate::checkPass($_POST['password'], $_POST['password2'], $this->translation);
                    $pass="pass='".md5($_POST['password'])."',";
                }
                
				if($err=="")
                {
                    $this->db->insert_id("UPDATE `".$this->table."` SET
																 `name`=?,
																 `surname`=?,
																 `patronymic`=?,
																 `email`=?,
																  $pass
																 `phone`=?,
																 `skype`=?,
																 `city`=?,
																 `address`=?,
																 `post_index`=?,
																 `info`=?,
																 `discount`=?,
																 `active`=?,
																 `status_id`=?
                                          WHERE id=?", array(
                            $_POST['name'],
                            $_POST['surname'],
                            $_POST['patronymic'],
                            $_POST['email'],
                            $_POST['phone'],
                            $_POST['skype'],
                            $_POST['city'],
                            $_POST['address'],
                            $_POST['post_index'],
                            $_POST['info'],
							$_POST['discount'],
                            $_POST['active'],
							$_POST['status_id'],
                            $_POST['id'])
                    );
                    $dir="files/users/";
                    if(isset($_FILES['photo']['tmp_name'])&&$_FILES['photo']['tmp_name']!="")Images::resizeImage($_FILES['photo']['tmp_name'], $dir.$_POST['id'].".jpg", $dir.$_POST['id']."_s.jpg", 214, 159);
                    $message.= messageAdmin('Данные успешно сохранены');
                }
            }
            else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
		}
		return $message;
	}
	
	
	public function listView()
	{
		$size_page =30;
        $start_page = 0;
        $cur_page = 0;
        $vars['paging'] = '';

        if(isset($this->params['page']))
        {
            $cur_page = $this->params['page'];
            $start_page = ($cur_page-1) * $size_page;//номер начального элемента
        }
		
		if(isset($_POST['word']))
        {
			$_SESSION['search_user']['status']=$_POST['status'];
            $_SESSION['search_user']['sort']=$_POST['sort'];
            $_SESSION['search_user']['word']=$_POST['word'];
        }
		
		$where="";
		if(!isset($_SESSION['search_user'])||isset($_POST['clear']))
		{
			$_SESSION['search_user']['status']='';
			$_SESSION['search_user']['cat_id']='';
			$_SESSION['search_user']['word']='';
			$_SESSION['search_user']['sort']='ORDER BY tb.`start_date` DESC';
		}
		
		if($_SESSION['search_user']['status']!='')
		{
			$where.=" AND tb.status_id='{$_SESSION['search_user']['status']}'";	
		}
		/*
		if($_SESSION['search_user']['rating']!='')
		{
			$where.=" AND tb.rating='{$_SESSION['search_user']['rating']}'";	
		}*/
		
		if($_SESSION['search_user']['word']!="")
		 	$where.="AND (tb.surname LIKE '%{$_SESSION['search_user']['word']}%' OR 
						  tb.name LIKE '%{$_SESSION['search_user']['word']}%' OR 
						  tb.email LIKE '%{$_SESSION['search_user']['word']}%' OR 
						  tb.phone LIKE '%{$_SESSION['search_user']['word']}%'
						  )";
		
		$sort='ORDER BY tb.`start_date` DESC';
		if(isset($_SESSION['search_user']['sort'])&&$_SESSION['search_user']['sort']!='')
		{
			if($_SESSION['search_user']['sort']=='name asc')$sort="ORDER BY tb.name ASC, tb.id DESC"; 
			elseif($_SESSION['search_user']['sort']=='name desc')$sort="ORDER BY tb.name DESC, tb.id DESC";
			elseif($_SESSION['search_user']['sort']=='email asc')$sort="ORDER BY tb.email ASC, tb.id DESC";
			elseif($_SESSION['search_user']['sort']=='email desc')$sort="ORDER BY tb.email DESC, tb.id DESC";
			elseif($_SESSION['search_user']['sort']=='date asc')$sort="ORDER BY tb.start_date ASC, tb.id DESC";
			
			elseif($_SESSION['search_user']['sort']=='price asc')$sort="ORDER BY total ASC, tb.id DESC";
			elseif($_SESSION['search_user']['sort']=='price desc')$sort="ORDER BY total DESC, tb.id DESC";
		}
		
		$join="";
		if($_SESSION['search_user']['cat_id']!='')
		{
			$where.=" AND cat.catalog_id='{$_SESSION['search_user']['cat_id']}'";	
			$join="LEFT JOIN orders_product orders_p
					ON orders_p.orders_id=orders.id
					
					LEFT JOIN product_catalog cat
					ON cat.product_id=orders_p.product_id";
		}
		
		$q="SELECT
				tb.*,
				tb2.name as status, 
				`orders`.`sum` as total
			 FROM ".$this->table." tb
				LEFT JOIN user_status tb2
				ON tb.status_id=tb2.id
				
				LEFT JOIN `orders` 
				ON orders.user_id=tb.id
				
				$join
			 WHERE tb.id!='0' $where
			 
			 GROUP BY tb.id
			 ".$sort;
        $sql = $q." LIMIT ".$start_page.", ".$size_page."";
        //echo $sql;
        $count = $this->db->query($q);//кол страниц
        if($count > $size_page)
        {
            $vars['paging'] = Paging::MakePaging($cur_page, $count, $size_page, $dir="admin_");//вызов шаблона для постраничной навигации
        }
		$vars['count']=$count;
        $vars['list'] = $this->db->rows($sql);
		return $vars;
	}
	
	
	public function addusertype()
	{
		$message='';
		if(isset($this->registry['access']))$message = $this->registry['access'];
		else
		{
			$this->db->query("INSERT INTO `user_status` SET `name`='New user status', price_type_id='".Product::getObject($this->sets)->default_price_type()."'");
			$message .= messageAdmin('Данные успешно сохранены');	
		}
		return $message;
	}
	
	public function save_usertype()
	{
		$message='';
		if(isset($this->registry['access']))$message = $this->registry['access'];
		else
		{
			if(isset($_POST['name'], $_POST['id']))
			{
				$count=count($_POST['id']) - 1;
				if(!isset($_POST['default']))$_POST['default']=1;
				for($i=0; $i<=$count; $i++)
				{
					$default=0;
					if($_POST['default']==$_POST['id'][$i])$default=1;
					
					$param = array($_POST['name'][$i], $_POST['price_type'][$i], $default, $_POST['id'][$i]);
					$this->db->query("UPDATE `user_status` SET `name`=?, `price_type_id`=?, `default`=? WHERE id=?", $param);
				}
				$message .= messageAdmin('Данные успешно сохранены');
			}
			else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
		}
		return $message;
	}
	
	public function signUp()
    {
		$message='';
		if(isset($_POST['email_sign']))
        {
            $error="";
			if(isset($_POST['captcha']))if(!Captcha3D::check($_POST['captcha']))$error.="<div class='err'>".$this->translation['wrong_code']."</div>";
			
            $error.=Validate::check($_POST['email_sign'], $this->translation, 'email');
            //$error.=Validate::check(array($_POST['name_sign'], $_POST['phone_sign'], $_POST['captcha'], $_POST['pass_sign']));

            $row=$this->db->row("SELECT id FROM users WHERE email='{$_POST['email_sign']}'");
            if($row)$error.="<div class='err'>".$this->translation['email_exists']."</div>";
            if($error=="")
            {
				$settings = Registry::get('user_settings');
                $date=date("Y-m-d H:i:s");
                if(isset($_POST['pass_sign']))$pass = $_POST['pass_sign'];
				else $pass = genPassword();
				
				if(!isset($_POST['status_id']))
				{
					$row_s = $this->db->row("SELECT id FROM user_status WHERE `default`='1'");
					$_POST['status_id']=$row_s['id'];	
				}
				$code=md5(mktime());
                $query="INSERT INTO `".$this->table."` SET
						__email:=".$_POST['email_sign']."__,
						__name:=".$_POST['name_sign']."__,
						__status_id:=".$_POST['status_id']."__,
						__pass:=".md5($pass)."__,
						__start_date:=".$date."__,
						__active_email:=".$code."__";
                $text="
                Вы зарегистрировались на сайте {$_SERVER['HTTP_HOST']}.<br /><br />
		
				Для завершения регистрации перейдите по адресу<br />
				<a href=\"http://{$_SERVER['HTTP_HOST']}/users/active/code/$code\" target=\"_blank\">http://{$_SERVER['HTTP_HOST']}/users/active/code/$code</a><br /><br />
				
                Дата: ".date("d/m/Y, H:i")."<br />
                ФИО: {$_POST['name_sign']}<br />
                E-mail: {$_POST['email_sign']}<br />
				";
				Users::getObject($this->sets)->insert_post_form($text);
				if(isset($_POST['phone_sign'])&&$_POST['phone_sign']!='')
				{
					$query.=",__phone:=".$_POST['phone_sign']."__";	
					$text.="Телефон: {$_POST['phone_sign']}<br />";	
				}
				if(isset($_POST['post_index_sign'])&&$_POST['post_index_sign']!='')
				{
					$query.=",__post_index:=".$_POST['post_index_sign']."__";	
					$text.="Почтовый индекс: {$_POST['post_index_sign']}<br />";	
				}
				if(isset($_POST['city_sign'])&&$_POST['city_sign']!='')
				{
					$query.=",__city:=".$_POST['city_sign']."__";	
					$text.="Город: {$_POST['city_sign']}<br />";	
				}
				if(isset($_POST['address_sign'])&&$_POST['address_sign']!='')
				{
					$query.=",__address:=".$_POST['address_sign']."__";	
					$text.="Адрес: {$_POST['address_sign']}<br />";	
				}
				
				$this->query($query);
                Mail::send($settings['sitename'], // имя отправителя
                    "info@".$_SERVER['HTTP_HOST'], // email отправителя
                    $_SERVER['HTTP_HOST'], // имя получателя
                    $settings['email'], // email получателя
                    "utf-8", // кодировка переданных данных
                    "windows-1251", // кодировка письма
                    "Новый пользователь на сайте ".$settings['sitename'], // тема письма
                    $text // текст письма
                );
				
				/////////
				Mail::send($settings['sitename'], // имя отправителя
								"info@".$_SERVER['HTTP_HOST'], // email отправителя
								$_POST['name_sign'], // имя получателя
								$_POST['email_sign'], // email получателя
								"utf-8", // кодировка переданных данных
								"windows-1251", // кодировка письма
								"Вы зарегистрированы на сайте ".$_SERVER['HTTP_HOST'], // тема письма
								"
								Вы зарегистрировались на сайте {$_SERVER['HTTP_HOST']}.<br /><br />

								Для завершения регистрации перейдите по адресу<br />
								<a href='http://{$_SERVER['HTTP_HOST']}/users/active/code/$code' target='_blank'>http://{$_SERVER['HTTP_HOST']}/users/active/code/$code</a><br /><br />

									Ваш логин: {$_POST['email_sign']}<br />
									Ваш пароль: $pass<br /><br />
									
									P.S. Если вы получили это письмо, но не проходили процесс регистрации (возможно кто-то использовал ваш e-mail), просто проигнорируйте это письмо." 
									// текст письма
								);//echo $text;	
								
								
				///Add to subcriber
				$this->mailer = new Mailer($this->sets);
				$this->mailer->subscriber($_POST['name_sign'], $_POST['email_sign'], false);
	
                $message = "<div class='done'>".$this->translation['sign_up_yes']."</div>";
            }
            else $message = $error;
        }
		return $message;
    }
	
	public function auth($email, $pass)
    {
		$error="";
		$error.=Validate::check($email, $this->translation, 'email');
		$error.=Validate::check($pass, $this->translation);

		$row=$this->db->row("SELECT id, status_id, email, name FROM users WHERE email=? AND pass=?", array($email, md5($pass)));
		if(!$row)$error.="<div class='err'>".$this->translation['wrong_pass']."</div>";
		else{
			$row=$this->db->row("SELECT id, status_id, email, name FROM users WHERE id=? AND active=?", array($row['id'], 1));
			if(!$row)$error.="<div class='err'>".$this->translation['no_active']."</div>";
		}
		
		if($error=="")
		{
			$admin_info=array();
			$admin_info['agent'] = $_SERVER['HTTP_USER_AGENT'];
			$admin_info['referer'] = $_SERVER['HTTP_REFERER'];
			$admin_info['ip'] = $_SERVER['REMOTE_ADDR'];
			$admin_info['id'] = $row['id'];
			$admin_info['status'] = $row['status_id'];
			$admin_info['email'] = $row['email'];
			$admin_info['name'] = $row['name'];
			$_SESSION['user_info'] = $admin_info;
			$_SESSION['user_id'] = $row['id'];
			$message = "<div class='done'>".$this->translation['auth_yes']."</div>";
		}
		else $message = $error;	
		
		return $message;
	}
	
	public function logout()
    {
		unset($_SESSION['user_id']);	
		header("Location: /users/sign-up");
	}
	
	public function active()
    {
		$row=$this->db->row("SELECT id FROM users WHERE active_email=?", array($this->params['code']));
		if($row)
		{
			$code=md5(mktime());
			$this->db->query("UPDATE users SET active=?, active_email=? WHERE id=?", array(1, $code, $row['id']));
			$message = "<div class='done'>".$this->translation['you_active']."</div>";	
		}
		else $message = "<div class='err'>".$this->translation['wrong_active']."</div>";
		return $message;
	}
}
?>