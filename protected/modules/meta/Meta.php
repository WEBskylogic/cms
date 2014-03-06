<?
class Meta extends Model
{
    static $table='meta'; //Главная талица
    static $name='Meta-данные'; // primary key
	
	public function __construct($registry)
    {
        parent::getInstance($registry);
    }

    //для доступа к классу через статичекий метод
	public static function getObject($registry)
	{
		return new self::$table($registry);
	}

	public function save_meta($tb, $url, $title, $keywords, $description)
	{
		$url = $this->get_url($tb, $url);
		if($url!='')
		{
			$row = $this->db->row("SELECT id, body
								   FROM `meta` tb
								   
								   LEFT JOIN ".$this->registry['key_lang_admin']."_meta tb2
								   ON tb.id=tb2.meta_id
								   
								   WHERE tb.url=?", array($url));
			if($row)
			{
				if($title==''&&$keywords==''&&$description==''&&$row['body']=='')
				{
					$this->db->query("DELETE FROM ".$this->table." WHERE id=?", array($row['id']));
				}
				else{
					$this->db->query("UPDATE `".$this->registry['key_lang_admin']."_meta` SET `title`=?, `keywords`=?, `description`=? WHERE meta_id=?", array($title, $keywords, $description, $row['id']));
				}
			}
			else{
				if($title==''&&$keywords==''&&$description=='')
				{
					return false;
				}
				else{
					$param = array($url, 1);
					$id = $this->db->insert_id("INSERT INTO `".$this->table."` SET `url`=?, `active`=?", $param);
					
					$param = array($title, $keywords, $description, $id);
					foreach($this->language as $lang)
					{
						$tb=$lang['language']."_".$this->table;
						$this->db->query("INSERT INTO `$tb` SET `title`=?, `keywords`=?, `description`=?, `meta_id`=?", $param);
					}	
				}
			}
		}
	}
	
	public function load_meta($tb, $url)
	{
		$url = $this->get_url($tb, $url);
		$row = $this->db->row("SELECT title, keywords, description
							   FROM `meta` tb
							   
							   LEFT JOIN ".$this->registry['key_lang_admin']."_meta tb2
							   ON tb.id=tb2.meta_id
							   
							   WHERE tb.url=?", array($url));
		if($row)
		{
			return $row;
		}
		else{
			return array('title'=>'', 'keywords'=>'', 'description'=>'');
		}
	}
	
	function get_url($tb, $url)
	{
		$row = $this->db->row("SELECT url FROM `modules` WHERE `controller`=?", array($tb));
		if($row)
		{
			if($row['url']!='')$row['url'].='/';
			$url = 'http://'.$_SERVER['HTTP_HOST'].'/'.$row['url'].$url;
			return $url;
		}
		else return false;
	}
	
	function delete_meta($tb, $id)
	{
		$row = $this->db->row("SELECT * FROM `".$tb."` WHERE `id`=?", array($id));
		if(isset($row['url']))
		{
			$url = $this->get_url($tb, $row['url']);
			if($url!='')
				$this->db->query("DELETE FROM ".$this->table." WHERE url=?", array($url));
		}
		
	}
	
	
	public function add()
	{
		$message='';
		if(isset($_POST['active'], $_POST['url'], $_POST['title'], $_POST['keywords'], $_POST['description'], $_POST['body'], $_POST['excerpt'])&&$_POST['url']!="")
		{
			$param = array($_POST['url'], $_POST['active']);
			$id = $this->db->insert_id("INSERT INTO `".$this->table."` SET `url`=?, `active`=?", $param);
			
			$param = array($_POST['title'], $_POST['keywords'], $_POST['description'], $_POST['body'], $_POST['excerpt'], $id);
			foreach($this->language as $lang)
			{
				$tb=$lang['language']."_".$this->table;
				$this->db->query("INSERT INTO `$tb` SET `title`=?, `keywords`=?, `description`=?, `body`=?, `excerpt`=?, `meta_id`=?", $param);
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
					$count=count($_POST['save_id']) - 1;
					for($i=0; $i<=$count; $i++)
					{
						$url = $_POST['url'][$i];
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
				if(isset($_POST['active'], $_POST['url'], $_POST['id'], $_POST['title'], $_POST['keywords'], $_POST['description'], $_POST['body']))
				{
					$param = array($_POST['title'], $_POST['keywords'], $_POST['description'], $_POST['body'], $_POST['excerpt'], $_POST['id']);
					$this->db->query("UPDATE `".$this->table."` SET `url`=?, `active`=?, `type`=? WHERE id=?", array($_POST['url'], $_POST['active'], $_POST['type'], $_POST['id']));
					$this->db->query("UPDATE `".$this->registry['key_lang_admin']."_".$this->table."` SET `title`=?, `keywords`=?, `description`=?, `body`=?, `excerpt`=? WHERE meta_id=?", $param);

					$message .= messageAdmin('Данные успешно сохранены');
				}
				else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
			}
		}
		return $message;
	}
	
	public function save_seoconfig()
	{
		$message='';
		if(isset($this->registry['access']))$message = $this->registry['access'];
		else
		{
			if(isset($_POST['on_sitemap']))$value=1;
			else $value=0;
			$this->db->query("UPDATE `config` SET `value`='$value' WHERE name='sitemap_generation'");
			
			if(isset($_POST['on_yandex_market']))$value=1;
			else $value=0;
			$this->db->query("UPDATE `config` SET `value`='$value' WHERE name='yandex_market'");
			
			if(!isset($_POST['main_domain']))$value=0;
			else $value=$_POST['main_domain'];
			$this->db->query("UPDATE `config` SET `value`='$value' WHERE name='main_domain'");
	
			$message .= messageAdmin('Данные успешно сохранены');
		}
		return $message;
	}
	
	
	public function addredirect()
	{
		$message='';
		if(isset($this->registry['access']))$message = $this->registry['access'];
		else
		{
			$this->db->query("INSERT INTO `redirects` SET `active`='0'");
			$message .= messageAdmin('Данные успешно сохранены');
			
		}
		return $message;
	}
	
	public function save_redirects()
	{
		$message='';
		if(isset($this->registry['access']))$message = $this->registry['access'];
		else
		{
			if(isset($_POST['id'], $_POST['from'], $_POST['to']))
			{
				$count=count($_POST['id']) - 1;
				for($i=0; $i<=$count; $i++)
				{
					$from = str_replace('http://','',$_POST['from'][$i]);
					$to = str_replace('http://','',$_POST['to'][$i]);
					$param = array($from, $to, $_POST['type'][$i], $_POST['id'][$i]);
					$this->db->query("UPDATE `redirects` SET `from`=?, `to`=?, `type`=? WHERE id=?", $param);
				}
				$message .= messageAdmin('Данные успешно сохранены');
			}
			else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
		}
		return $message;
	}
	
	function check_redirects()
	{
		$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$row = $this->db->row("SELECT `to`, `type` FROM `redirects` WHERE `from`=? AND `from`!='' AND `to`!='' AND active='1'", array($url));
		if($row)
		{
			if($row['type']=='302')header("HTTP/1.1 302 Found");
			else header("HTTP/1.1 301 Moved Permanently");
			header("Location: http://".$row['to']);
			exit();
		}
		
		$domain = current(explode('.', $_SERVER['HTTP_HOST']));
		if(isset($this->settings['main_domain'])&&$this->settings['main_domain']==1&&$domain!='www')
		{
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: http://www.".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
			exit();
		}
		elseif(isset($this->settings['main_domain'])&&$this->settings['main_domain']==0&&$domain=='www')
		{
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: http://".str_replace('www.','',$_SERVER['HTTP_HOST']).$_SERVER['REQUEST_URI']);
			exit();
		}
	}
	
	
	function check_targets($db, $url)
	{
		//////////////
		$row = $db->row("SELECT * FROM config WHERE name='targets_for_google'");
		if($row&&$row!='')
		{
			$targets = explode(',', str_replace(' ', '', $row['value']));
			//var_info($_POST);
			$countPost=count($_POST);
			$i=0;
			foreach($url as $row)
			{
				//echo $row.'<br />';	
				foreach($targets as $row2)
				{
					if($row==$row2)
					{
						$_SERVER['REQUEST_URI']=str_replace('/'.$row2, '', $_SERVER['REQUEST_URI']);
						if($countPost==0)
						{
							if($_SERVER['REQUEST_URI']=='')$_SERVER['REQUEST_URI']='/';
							//echo $link.'asdsaaaaaaaaa'.$url[$i];
							header("HTTP/1.1 301 Moved Permanently");
							header('Location: '.$_SERVER['REQUEST_URI']);
							exit();
						}
						else{
							unset($url[$i]);	
						}
					}
				}
				$i++;
			}
			array_values($url);
		}
		//var_info($url);
		return $url;
	}
	
	
	public function search_link()
	{
		$query='';
		$res = $this->db->rows("SELECT controller 
								FROM modules 
								WHERE controller='pages' OR 
									  controller='menu' OR 
									  controller='news' OR 
									  controller='article' OR 
									  controller='product' OR 
									  controller='catalog' OR 
									  controller='params' OR 
									  controller='brend' OR 
									  controller='photos' OR 
									  controller='meta'");
		foreach($res as $row)
		{
			$where="";
			if($this->db->query("SHOW COLUMNS FROM `".$row['controller']."` LIKE 'body_m'"))$where=" OR body_m LIKE '%http://%'";
				
			if($query!='')$query.=" UNION ";
			$query.="(SELECT tb.id, tb.url, m.url as url2, m.controller, '".$row['controller']."' as action, tb2.body 
					  FROM ".$row['controller']." tb
					
					  LEFT JOIN ru_".$row['controller']." tb2
					  ON tb.id=tb2.".$row['controller']."_id
					
					  LEFT JOIN modules m
					  ON m.controller='".$row['controller']."'
					
					  WHERE body LIKE '%http://%' $where
					  GROUP BY tb.id
					  )";	
		}
		/////Menu
		$result = $this->db->rows($query);

		return $result;
	}
	
	public function clear_from_links()
	{
		$message='';
		if(isset($this->registry['access']))$message = $this->registry['access'];
		else
		{
			if(isset($_POST['id'])&&is_array($_POST['id']))
			{
				$parser = new Parser();
				$count=count($_POST['id']) - 1;
				for($i=0; $i<=$count; $i++)
				{
					$id = explode('-', $_POST['id'][$i]);
					$where="";
					if($this->db->query("SHOW COLUMNS FROM `".$id[0]."` LIKE 'body_m'"))$where=" OR body_m LIKE '%http://%'";
					$row = $this->db->row("SELECT * FROM `".$this->registry['key_lang_admin']."_".$id[0]."` WHERE ".$id[0]."_id=? AND (body LIKE '%http://%'$where)", 
					array($id[1]));
					if($row)
					{
						$where="";
						$body=String::clear_links(htmlspecialchars_decode($row['body']));
						if(isset($row['body_m']))
						{
							$where=", body_m=?";
							$body_m=String::clear_links(htmlspecialchars_decode($row['body_m']));
							$param = array($body, $body_m, $id[1]);
						}
						else $param = array($body, $id[1]);
						$parser->parse_recursive_tree($param);
						//var_info($param);
						$this->db->query("UPDATE `".$this->registry['key_lang_admin']."_".$id[0]."` SET `body`=? $where WHERE ".$id[0]."_id=?", $param);
					}
				}
				$message .= messageAdmin('Данные успешно сохранены');
			}
			elseif(isset($this->params['clear']))
			{
				$parser = new Parser();
				$id = explode('-', $this->params['clear']);
				$where="";
				if($this->db->query("SHOW COLUMNS FROM `".$id[0]."` LIKE 'body_m'"))$where=" OR body_m LIKE '%http://%'";
				$row = $this->db->row("SELECT * FROM `".$this->registry['key_lang_admin']."_".$id[0]."` WHERE ".$id[0]."_id=? AND (body LIKE '%http://%' $where)", array($id[1]));
				if($row)
				{
					$where="";
					$body=String::clear_links(htmlspecialchars_decode($row['body']));
					if(isset($row['body_m']))
					{
						$where=", body_m=?";
						$body_m=String::clear_links(htmlspecialchars_decode($row['body_m']));
						$param = array($body, $body_m, $id[1]);
					}
					else $param = array($body, $id[1]);//var_info($param);
					$parser->parse_recursive_tree($param);
					
					//$this->db->query("UPDATE `".$this->registry['key_lang_admin']."_".$id[0]."` SET `body`=? $where WHERE ".$id[0]."_id=?", $param);
					$message .= messageAdmin('Данные успешно сохранены');
				}
			}
		}
		return $message;
	}
	
	public function yandex_market()
	{
		$vars=array();
		$vars['product'] = Product::getObject($this->sets)->find(array('select'=>"tb.id, tb.price, tb.url, tb.discount, tb_lang.name, tb_lang.body, tb3.catalog_id",
																	   'join'=>"LEFT JOIN product_catalog tb3
																				ON tb3.product_id=tb.id
																				
																				LEFT JOIN ".$_SESSION['key_lang']."_catalog cat
																				ON cat.catalog_id=tb3.catalog_id",
																	   'where'=>"active='1'",
																	   'type'=>'rows',
																	   'group'=>'tb.id',
																	   'order'=>'sort ASC'));
		///////////////////////
		
		$vars['catalog'] = Catalog::getObject($this->sets)->find(array('where'=>"sub IS NULL AND active='1'",
																	   'type'=>'rows',
																	   'order'=>'sort ASC'));

		return $vars;
	}
	
	
	function generate_static_sitemap()
	{
		file_put_contents('sitemap.xml', Meta::sitemap_generate());
		return messageAdmin('Данные успешно сохранены');
	}
	
	function sitemap_generate()
	{
		$sitemap='<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

		////////Put menu links
		$res = $this->db->rows("SELECT url FROM menu ORDER BY `sort` ASC");	
		if(count($res)!=0) 
		{
			foreach($res as $row) 
			{ 
$sitemap.='
	<url>
		<loc>http://'.$_SERVER['HTTP_HOST'].$row['url'].'</loc>
	</url>'; 
			}
		}
		
		////////Put pages links
		$res = $this->db->rows("SELECT url FROM pages ORDER BY `sort` ASC");	
		if(count($res)!=0) 
		{
			foreach($res as $row) 
			{ 
$sitemap.='
	<url>
		<loc>http://'.$_SERVER['HTTP_HOST'].$row['url'].'</loc>
	</url>'; 
			}
		}
		
		////////Put menu links
		$res = $this->db->rows("SELECT url FROM catalog ORDER BY `sort` ASC");	
		if(count($res)!=0) 
		{
			foreach($res as $row) 
			{ 
$sitemap.='
	<url>
		<loc>http://'.$_SERVER['HTTP_HOST'].'/catalog/'.$row['url'].'</loc>
	</url>'; 
			}
		}
		
		////////Put menu links
		$res = $this->db->rows("SELECT url FROM product ORDER BY `sort` ASC");	
		if(count($res)!=0) 
		{
			foreach($res as $row) 
			{ 
$sitemap.='
	<url>
		<loc>http://'.$_SERVER['HTTP_HOST'].'/product/'.$row['url'].'</loc>
	</url>'; 
			}
		}

		
$sitemap.='
</urlset>';	
		return $sitemap;
	}
	
	
	public function set_meta_data($data, $topic)///Мета-данные страницы
	{
		//echo $topic;
        $url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		if(substr($url, strlen($url)-1, 1)=='/'&&$_SERVER['REQUEST_URI']!='/')
		{
			$lang='';
			if($this->registry['key_lang']!='ru')$lang='/'.$this->registry['key_lang'];
			$url2 = "http://".$_SERVER['HTTP_HOST'].$lang.$_SERVER['REQUEST_URI'];
			header("HTTP/1.1 301 Moved Permanently");
			header('Location: '.substr($url2, 0, strlen($url2)-1));
			exit();	
		}
		
		//echo $url;
		$row = $this->find(array('where'=>"__url:={$url}__ AND active='1' AND title!=''"));
        if($row)
        {
            $data['title'] = $row['title'];
            $data['keywords'] = $row['keywords'];
            $data['description'] = $row['description'];
            $data['text'] = $row['body'];
			$data['excerpt'] = $row['excerpt'];
        }
        elseif(count($data)!=0)
        {
			if(!isset($data['name']))$data['name']='';
			
			if($topic=='catalog'&&isset($data['sub'])&&$data['sub']!=0)
			{
				$name_sub = $this->get_sub_cat($data['sub']);
				if($name_sub)
				{
					if(isset($data['keywords'])&&$data['keywords']=="")$data['keywords']=str_replace('@-@',', ',$name_sub).', '.$data['name'];	
					$data['name']=str_replace('@-@',' - ',$name_sub).' - '.$data['name'];	
					
				}
			}
			
			
            if(isset($data['title'])&&$data['title']!="")$data['title']=$data['title'];
            else $data['title']=$data['name'];

            if(isset($data['keywords'])&&$data['keywords']!="")$data['keywords']=$data['keywords'];
            else $data['keywords']=$data['name'];

            if(isset($data['description'])&&$data['description']!="")$data['description']=$data['description'];
            else $data['description']=$data['name'];
        }
        else{
			$row = $this->find(array('where'=>"`type`='1' AND active='1' AND title!=''"));
            $data['title'] = $row['title'];
            $data['keywords'] = $row['keywords'];
            $data['description'] = $row['description'];
        }
		$prefix = $this->find(array('where'=>"`type`='2' AND active='1' AND title!=''"));
		$suffix = $this->find(array('where'=>"`type`='3' AND active='1' AND title!=''"));
		
		if($prefix)
		{
			$data['title'] = $prefix['title'].$data['title'];	
			$data['keywords'] = $prefix['keywords'].$data['keywords'];
			$data['description'] = $prefix['description'].$data['description'];
		}
		if($suffix)
		{
			$data['title'] = $data['title'].$suffix['title'];	
			$data['keywords'] = $data['keywords'].$suffix['keywords'];
			$data['description'] = $data['description'].$suffix['description'];
		}		
		if(isset($this->params['page'])&&$this->params['page']!='')
		{
			$data['title'] .= " - {$this->translation['page']} {$this->params['page']}";	
			$data['keywords'] .= " - {$this->translation['page']} {$this->params['page']}";
			$data['description'] .= " - {$this->translation['page']} {$this->params['page']}";
		}
		return $data;
	}
	
	function get_sub_cat($sub)
	{
		$q="SELECT tb2.name, tb.sub
			FROM catalog tb
			
			LEFT JOIN ".$this->registry['key_lang']."_catalog tb2
			ON tb.id=tb2.catalog_id
			
			WHERE id='{$sub}'
			";
		$row = $this->db->row($q);	
		if($row)
		{
			$namecat=$this->get_sub_cat($row['sub']);
			if($namecat!='')$row['name']=$namecat.'@-@'.$row['name'];
			return $row['name'];
		}
		else return false;
	}
}
?>