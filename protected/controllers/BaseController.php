<?php
class BaseController{
	
	private $registry;
	protected $params;
	protected $key_lang="ru";

	function  __construct($registry, $params)
	{ 
		$this->params = $params;
		$this->registry = $registry;//
		$this->db = new PDOchild($registry);//Connect to database
		
		/////////////Language
		$this->key_lang = $this->registry['key_lang'];//Current language
		$this->language = $this->db->rows("SELECT * FROM language ORDER BY `id` ASC");//Languages

        ////////////Translations
		$this->translation=$this->db->rows_key("SELECT
                                                    tb.key,
                                                    tb2.value
                                                 FROM translate tb
                                                    LEFT JOIN
                                                        ".$this->key_lang."_translate tb2
                                                    ON tb.id=tb2.translate_id");

        ////////////Config
        $const = $this->db->rows_key("SELECT name, value FROM const");
        Registry::set('user_settings', $const);
		
		/////////Currency
		if(!isset($_SESSION['currency']))
		{
			$_SESSION['currency']=array();
			$_SESSION['currency'][1]=$this->db->row("select * from `currency` WHERE base=?", array(1));
			$_SESSION['currency'][0]=$_SESSION['currency'][1]['id'];
			//$_SESSION['currency'][3]=$this->db->rows("select * from `currency` ORDER BY id ASC");
		}
		elseif(isset($_POST['currency']))
		{
			$row = $this->db->row("select * from `currency` where id='{$_POST['currency']}'");
			if($row)
			{
				$_SESSION['currency']=array();
				$_SESSION['currency'][1]=$row;
				$_SESSION['currency'][0]=$_SESSION['currency'][1]['id'];
			}
		}
		else{
			$_SESSION['currency'][1]=$this->db->row("select * from `currency` WHERE id=?", array($_SESSION['currency'][0]));	
		}
	}
	
	///////////////////
	public function Render($param = array())
	{
		if(!isset($this->params['topic'])) $param['topic'] = '';
		else $param['topic'] = $this->params['topic'];

		$data = $param;

		$settings = Registry::get('user_settings');
        if($settings['google']!="")$data['google']=$settings['google'];

		$data['lang'] = $this->key_lang;
		$view = new View($this->registry);
        if(!isset($param['styles']))$param['styles']=array();
        if(!isset($param['scripts']))$param['scripts']=array();

		if($param['topic'] == 'admin')////Админка
		{
			$data['menu_inc']='';
			$data['admin'] = "admin";
			$styles=array_merge(array('style.css', 'jquery.notification.css'), $param['styles']);
			$scripts=array_merge(array('jquery-1.7.1.min.js', 'jquery.router-0.5.3.js', 'base.js', 'jquery.tablednd_0_5.js', 'ajax.js'), $param['scripts']);
			$data['styles'] = $view->Load($styles, 'styles', 'admin');
			$data['scripts'] = $view->Load($scripts, 'scripts', 'admin');
			if(isset($_SESSION['admin']))
			{
				if(!isset($param['breadcrumb']))$data['breadcrumb']=$this->breadcrumbAdmin();
				$data['menu'] = $this->db->rows("SELECT tb.*
				                                 FROM `module` tb
				                                 RIGHT JOIN moderators_permission tb2
				                                  ON tb.id=tb2.module_id AND tb2.moderators_type_id=? AND tb2.permission!=?

				                                 ORDER BY tb.`sort` ASC",
                array($_SESSION['admin']['type'], '000'));
				$data['language'] =  $this->language;  
				$data['languages'] = $this->db->rows("SELECT * FROM `language` ORDER  BY id ASC"); 
				$data['key'] = $this->key_lang;
				$data['menu_inc'] = $view->Render('menu.inc.phtml',	$data);  
			}
            else $data['login']=1;
			return ($view->Render('index.phtml', $data));//начальный шаблон	
		}
		else//////Сайт
        {	
            $styles=array_merge(array('style.css', 'fancy.css', 'sticker.css', 'menu.css'), $param['styles']);
            $scripts=array_merge(array('jquery-1.7.1.min.js', 
									   'base.js', 
									   'ajax.js', 
									   'jquery.stickr.min.js',
									   'jquery.fancybox-1.3.4.pack.js'), 
									   $param['scripts']);
            $data['styles'] = $view->Load($styles, 'styles');
            $data['scripts'] = $view->Load($scripts, 'scripts');
			$data2=array();
            $data['translate'] = $this->translation;///Переводы интерфейса
            if(!isset($data['meta']))$data['meta']=array();
            $data['meta']=$this->meta($data['meta']);//Вызов функции мета-данных
			
			#Start Main menu
            $menu = $this->db->rows("SELECT tb1.*, tb2.name 
									 FROM `menu` tb1
									 LEFT JOIN ".$this->key_lang."_menu tb2
									 ON tb1.id=tb2.menu_id
									 WHERE tb1.active=?
									 ORDER BY tb1.`sort` ASC", array(1));
            $data['menu'] = $view->Render('menu.phtml',	array_merge(array('menu'=>$menu, 'translate'=>$data['translate'])));
			#!End Main menu 
			
			#Start Left menu
			$menu = $this->db->rows("SELECT tb1.*, tb2.name 
									 FROM `catalog` tb1
									 LEFT JOIN ".$this->key_lang."_catalog tb2
									 ON tb1.id=tb2.cat_id
									 WHERE tb1.active=?
									 ORDER BY tb1.`sort` ASC", array(1));
			$open_link='';
			if(isset($data['open_link']))
			{
				$open_link = $this->getOpenLink($data['open_link']);
			}
			elseif(isset($this->params['catalog']))
			{
				$open_link = $this->getOpenLink($this->params['catalog']);
			}
			
            $data['top_menu'] = $view->Render('top_menu.phtml', array_merge($param, array('menu'=>$menu, 'open_link'=>$open_link, 'translate'=>$data['translate'])));
			$data['left_menu'] = $view->Render('left_menu.phtml', array_merge($param, array('menu'=>$menu, 'open_link'=>$open_link, 'translate'=>$data['translate'])));
			#!End Left menu 

			#Start Info blocks
            $data['phone'] = $this->getBlock(1);
			$data['recomand'] = $this->getBlock(3);
			$data['bonus'] = $this->getBlock(4);
			$data['brands'] = $this->getBlock(5);
			#!End Info blocks
			
			#Start News blocks
			$news = $this->db->rows("SELECT tb1.*, tb2.name 
									 FROM `news` tb1
									 LEFT JOIN ".$this->key_lang."_news tb2
									 ON tb1.id=tb2.news_id
									 WHERE tb1.active=?
									 ORDER BY tb1.`date` DESC LIMIT ".$settings['limit_news_block'], array(1));
			$data['news'] = $view->Render('news_block.phtml', array_merge($param, array('news'=>$news, 'translate'=>$data['translate'])));
			#!End News blocks
			
			//echo $this->validate('+3(123)7291231231', 'phone');
            if(!isset($data['slider']))
			{
                $data['discount'] = $this->getBlock(4);
            }
			
			//Bread crumbs
			if(isset($data['breadcrumbs']))$data['breadcrumbs'] = $this->breadcrumbs($data['breadcrumbs']);
			
            return ($view->Render("index.phtml", $data));//начальный шаблон
		}
	}
	
	function getOpenLink($id)
	{
		$return=array();
		if(is_numeric($id))$where='tb1.id=?';
		else $where='tb1.url=?';
		
		$row = $this->db->row("SELECT id, sub FROM `catalog` tb1 WHERE ".$where." AND tb1.active=?", array($id, 1));
		if($row['sub']!=NULL)
		{
			$row2 = $this->db->row("SELECT id, sub FROM `catalog` tb1 WHERE tb1.id=? AND tb1.active=?", array($row['sub'], 1));	
			if($row2['sub']!=NULL)
			{
				$row3 = $this->db->row("SELECT id, sub FROM `catalog` tb1 WHERE tb1.id=? AND tb1.active=?", array($row2['sub'], 1));	
				
				$return['level_1']=$row3['id'];
				$return['level_2']=$row2['id'];	
				$return['level_3']=$row['id'];	
			}
			else{
				$return['level_1']=$row2['id'];
				$return['level_2']=$row['id'];	
			}
		}
		else{
			$return['level_1']=$row['id'];
		}
		//var_info($return);
		return $return;
	}
	
	public function getPage($id, $index='*')/////Get text page from tables menu or pages
	{
		//echo $id;
		if(is_numeric($id))$where='tb1.id=?';
		else $where='tb1.url=?';
		
		$page = $this->db->row("SELECT ".$index." FROM `menu` tb1
								 LEFT JOIN ".$this->key_lang."_menu tb2
								 ON tb1.id=tb2.menu_id
								 WHERE ".$where." AND tb1.active=?",
        array($id, 1));
		
		
		if(!$page)
		{
			$page = $this->db->row("SELECT ".$index." FROM `pages` tb1
										  LEFT JOIN ".$this->key_lang."_pages tb2
										  ON tb1.id=tb2.pages_id
										  WHERE ".$where." AND tb1.active=?",
        	array($id, 1));
			$page['type']='pages';
		}
		else $page['type']='menu';
		return $page;
	}
	
	public function getBlock($id, $index='*')/////Get text page from tables menu or pages
	{
		return $this->db->row("SELECT ".$index." FROM ".$this->key_lang."_info_blocks tb2 WHERE tb2.info_id=?", array($id));
	}
	
	public function breadcrumbAdmin()/////Хлебные крошки админки
	{
		if(isset($this->params['action'])&&($this->params['action']=='edit'||$this->params['action']=='add'))
			 return'<a href="/admin/'.strtolower($this->params['controller']).'" class="back-link">« Назад в:&nbsp;'.$this->params['module'].'</a>';
		else return'';
	}

	public function breadcrumbs($links)/////Хлебные крошки на сайте
	{
		$separator='<span>>></span>';
		$return='';
		$cnt=count($links)-1;
		$i=0;
		foreach($links as $row)
		{
			$return.=$row;	
			if($cnt!=$i)$return.=' '.$separator.' ';	
			$i++;
		}
		if($return!='')$return='<div id="breadcrumbs"><a href="/">'.$this->translation['main'].'</a> '.$separator.' '.$return.'</div>';
		return $return;
	}
	
	public function getBreadCat($catrow, $product_name='')
	{
		$return=array();
		if(is_numeric($catrow))
		{
			$catrow = $this->db->row("SELECT *
										  FROM catalog tb 
											LEFT JOIN ".$this->key_lang."_catalog tb2
											ON tb.id=tb2.cat_id
											
										  WHERE tb.id=?", array($catrow));
		}
		
		if($product_name!='')$last='<a href="/catalog/'.$catrow['url'].'">'.$catrow['name'].'</a>'.' ';
		if($catrow['sub']==0)
		{
			if($product_name!='')$return = array('<a href="/catalog/all">'.$this->translation['catalog'].'</a>', 
												 '<a href="/catalog/'.$catrow['url'].'">'.$catrow['name'].'</a>', 
												  $product_name);	
			else $return = array('<a href="/catalog/all">'.$this->translation['catalog'].'</a>', 
								 $catrow['name']);	
		}
		else{
			$catrow2 = $this->db->row("SELECT *
										  FROM catalog tb 
											LEFT JOIN ".$this->key_lang."_catalog tb2
											ON tb.id=tb2.cat_id
											
										  WHERE tb.id=?", array($catrow['sub']));
			
			if($catrow2['sub']==0)
			{
				if($product_name!='')$return = array('<a href="/catalog/all">'.$this->translation['catalog'].'</a>', 
													 '<a href="/catalog/'.$catrow2['url'].'">'.$catrow2['name'].'</a>', 
													 '<a href="/catalog/'.$catrow['url'].'">'.$catrow['name'].'</a>', 
													 $product_name);	
				else $return = array('<a href="/catalog/all">'.$this->translation['catalog'].'</a>', 
									 '<a href="/catalog/'.$catrow2['url'].'">'.$catrow2['name'].'</a>', 
									 $catrow['name']);	
			}
			else{
				$catrow3 = $this->db->row("SELECT *
											 FROM catalog tb 
												LEFT JOIN ".$this->key_lang."_catalog tb2
												ON tb.id=tb2.cat_id
												
											  WHERE tb.id=?",array($catrow2['sub']));
											  
				if($product_name!='')$return = array('<a href="/catalog/all">'.$this->translation['catalog'].'</a>', 
													 '<a href="/catalog/'.$catrow3['url'].'">'.$catrow3['name'].'</a>',  
													 '<a href="/catalog/'.$catrow2['url'].'">'.$catrow2['name'].'</a>', 
													 '<a href="/catalog/'.$catrow['url'].'">'.$catrow['name'].'</a>', 
													 $product_name);	
				else $return = array('<a href="/catalog/all">'.$this->translation['catalog'].'</a>', 
									 '<a href="/catalog/'.$catrow3['url'].'">'.$catrow3['name'].'</a>',  
									 '<a href="/catalog/'.$catrow2['url'].'">'.$catrow2['name'].'</a>', 
									 $catrow['name']);										 
			}
		}
					
		return $return;
	}
	
	public function meta($data)///Мета-данные страницы
	{
		$settings = Registry::get('user_settings');
        $url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $row=$this->db->row("SELECT * FROM `meta_data` WHERE url=? AND active=?", array($url, 1));

        if($row)
        {
            $data['title']=$row['title'];
            $data['keywords']=$row['keywords'];
            $data['description']=$row['description'];
            $data['text']=$row['body'];
        }
        elseif(count($data)!=0)
        {
            if(isset($data['title'])&&$data['title']!="")$data['title']=$data['title'];
            else $data['title']=$settings['sitename'];

            if(isset($data['keywords'])&&$data['keywords']!="")$data['keywords']=$data['keywords'];
            else $data['keywords']=$settings['sitename'];

            if(isset($data['description'])&&$data['description']!="")$data['description']=$data['description'];
            else $data['description']=$settings['sitename'];
        }
        else{
            $data['title']=$settings['sitename'];
            $data['keywords']=$settings['sitename'];
            $data['description']=$settings['sitename'];
        }

		return $data;
	}
	
	public function insert_ban($position)
	{
		$ban = "";
		$sql = "SELECT * FROM `banners` where `position`='$position' and `cheked`='1' order by `sort` desc";
		$res = $this->db->rows($sql);
		$i=0;
		foreach ($res as $row)
		{	
			if($row['position'] == "bot")$padding="style='padding:10px;'";
			else $padding = "";
			$name = $row['name'];
			if($i!=0&&$row['position'] == "right")$ban.="<div class='red_line2'></div>";	
			if($row['type'] == "image")
			{
				if($row['link'] == "#")
					$ban.="<a href='/tpl/images/ban/{$name}_b.jpg' class='ban' $padding rel='lightbox[portfolio]'><img src='/tpl/images/ban/$name.jpg' class='ban' /></a>";
				else
					$ban.="<a href='{$row['link']}' class='ban' $padding><img src='/tpl/images/ban/$name.jpg' class='ban' /></a>";	
			}
			elseif($row['type']=="code")
			{
				$ban.=html_entity_decode($row['link']);	
			}
			else $ban.="<div class='ban'><div id='$name'></div>
						<script type='text/javascript'>	
							var arr = new Array('/tpl/images/ban/$name.swf');
							var banner = arr[ Math.floor( Math.random() * arr.length) ];
							var params = {	majorversion:'8',build:'0',bgcolor:'#EDE1CC',allowfullscreen:'false',flashvars:'', wmode:'transparent' };
							swfobject.embedSWF(banner, '$name', '{$row['width']}', '{$row['height']}', '8.0.0','expressInstall.swf', false, params, false);
						</script></div>";
			$i++;
		}
		return $ban;		
	}
	
	public function checkAccess($action, $module)//////Проверка доступа модулей в админке
	{
		$row = $this->db->row("SELECT m.`permission` 
							   FROM `moderators_permission` m
							   
							   LEFT JOIN moderators mm
							   ON m.moderators_type_id=mm.type_moderator
							   
							   LEFT JOIN module mmm
							   ON mmm.id=m.module_id
							   
							   WHERE mmm.controller=? AND mm.id=?", array($module, $_SESSION['admin']['id']));
		if(($action=='delete'||$action=='edit')&&($row['permission']!=500&&$row['permission']!=700))return messageAdmin('Отказано в доступе', 'error');
		elseif($action=='add'&&($row['permission']!=600&&$row['permission']!=700))return messageAdmin('Отказано в доступе', 'error');
		elseif($action=='read'&&$row['permission']==000){return Loader::act('error');}
		return false;
	}
	
	public function checkUrl($tb, $url, $id)///Проверка уникальности URL
	{
		if($this->db->row("SELECT id from `".$tb."` where url=? and id!=?", array($url, $id)))return messageAdmin('Данный адрес уже занят', 'error');
		else{
			$this->db->query("UPDATE `".$tb."` set url=? where id=?", array($url, $id));
		}
	}
	
	public function currency()///Валюта
	{
		return $this->db->row("select * from `currency` where id='{$_SESSION['currency']}'");	
	}
	
	public function validate($str, $type='text')///Validate form
	{
		$message='';//echo $str."<br />";
		if(is_array($str))
		{
			foreach($str as $row)
			{
				if($type=='email')
					if(!preg_match('|([a-z0-9_\.\-]{1,20})@([a-z0-9\.\-]{1,20})\.([a-z]{2,4})|is', $row))
						$message="<div class='err'>".$this->translation['wrong_email']."</div>";
				elseif($type=='text')
					if(strlen($row)<3)
						$message="<div class='err'>".$this->translation['required']."</div>";
				elseif($type=='password')
					if(strlen($row)<3)
						$message="<div class='err'>".$this->translation['required']."</div>";		
			}
		}
		else{
			if($type=='email'&&!preg_match('|([a-z0-9_\.\-]{1,20})@([a-z0-9\.\-]{1,20})\.([a-z]{2,4})|is', $str))$message="<div class='err'>".$this->translation['wrong_email']."</div>";
			if($type=='phone'&&!preg_match('/^\+?[0-9]{1,3}\s?\s?\(?\s?[0-9]{1,5}\s?\)?\s?[0-9\s-]{3,10}$/', $str))$message="<div class='err'>".$this->translation['wrong_phone']."</div>";
			elseif($type=='text'&&strlen($str)<3)$message="<div class='err'>".$this->translation['required']."</div>";
	
		}
		return $message;	
	}
}
?>