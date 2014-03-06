<?php
class Model extends DataBase
{
	static $table='news'; //Главная талица
    static $name="_news"; //доп таблица
	
	function test()
	{
		echo'asdasd';	
	}
	
	public function __construct($registry)
    {
        parent::getInstance($registry);
    }
	
	
	public function insert($sql=array(), $debug=false)
	{
		$where = $this->checkWhere($sql['query']);
		$insert_id = $db->insert_id("INSERT INTO `".$this->table."` SET ".$where['query'], $where['params']);
		
		if(!$insert_id)return false;
		if(isset($sql['query_lang']))
		{
			$where = $this->checkWhere($sql['query_lang']);
			if($where['query']!='')$where['query']=', '.$where['query'];
			foreach($this->language as $lang)
			{
				$tb=$lang['language']."_".$this->table;
				if(!$db->query("INSERT INTO `$tb` SET `".$this->table."_id`='{$insert_id}'".$where['query'], $where['params']))return false;
			}
		}
		
		return $insert_id;
	}
	
	public function delete($table='')
	{
		$meta = new Meta($this->sets);
		if($table=='')$table=$this->table;
		$message='';
		if(isset($this->registry['access']))$message = $this->registry['access'];
		else
		{
			if(isset($_POST['id'])&&is_array($_POST['id']))
			{
				$count=count($_POST['id']) - 1;
				for($i=0; $i<=$count; $i++)
				{
					////Delete meta data
					$meta->delete_meta($this->table, $_POST['id'][$i]);
					
					$this->db->query("DELETE FROM `".$table."` WHERE `id`=?", array($_POST['id'][$i]));
				}
				$message = messageAdmin('Запись успешно удалена');
			}
			elseif(isset($this->params['delete'])&& $this->params['delete']!='')
			{
				$id = $this->params['delete'];
				
				////Delete meta data
				$meta->delete_meta($this->table, $id);
				
				if($this->db->query("DELETE FROM `".$table."` WHERE `id`=?", array($id)))$message = messageAdmin('Запись успешно удалена');
				
				
			}
		}
		return $message;
	}
	
	public function get_columns($row, $table, $fk='')
	{
		$query="";
		$fields = $this->db->rows("SHOW COLUMNS FROM $table");
		foreach($fields as $row2)
		{
			if($row2['Field']!='id'&&$row2['Field']!=$fk)
			{
				if($row2['Field']=='url')$row[$row2['Field']].="-".time();
				elseif($row2['Field']=='name')$row[$row2['Field']].="-[copy]";
				$query.="{$row2['Field']}='".$row[$row2['Field']]."', ";
			}
		}
		return $query = substr($query, 0, strlen($query)-2);	
	}
	
	public function duplicate($row, $table)
	{
		$message='';
		if(isset($this->registry['access']))$message = $this->registry['access'];
		else
		{
			$message='';
			$fk = $table."_id";
			$query = $this->get_columns($row, $table);
			if($query!='')
			{
				$insert_id = $this->db->insert_id("INSERT INTO `$table` SET ".$query);
				if($this->db->row("SHOW TABLES LIKE '".$this->registry['key_lang_admin']."_".$table."'"))
				{
					$res = $this->db->rows("SELECT * FROM language");
					foreach($res as $lang)
					{
						$tb=$lang['language']."_".$table;
						
						$query = $this->get_columns($row, $tb, $fk);
						if($query!='')$this->db->query("INSERT INTO `$tb` SET $fk = '$insert_id', ".$query);
					}	
				}
				header("Location: /admin/$table/edit/".$insert_id);
			}
		}
		return $message;
	}
	
	public function find($param)
    {
		if(isset($param['paging'])&&is_array($param))
		{
			if(is_numeric($param['paging']))$size_page = $param['paging'];
			elseif(isset($this->settings['paging_admin_'.$this->table], $this->registry['admin']))$size_page = $this->settings['paging_admin_'.$this->table];
			elseif(isset($this->settings['paging_'.$this->table]))$size_page = $this->settings['paging_'.$this->table];
			else $size_page = default_paging;
			
			$start_page = 0;
			$cur_page = 0;
			$paging = '';
			$param2 = $param;
			$param['select'] = 'tb.id';
			$count = count($this->select($param));//кол страниц
			
			//var_info($this->params).'s';
			if(isset($this->params['page']))
			{
				$cur_page = $this->params['page'];
				if($cur_page<2)
				{
					header('Location: '.String::getUrl2('page'));
					exit();
				}
				if($cur_page>round($count/$size_page))
				{
					return false;
				}
				$start_page = ($cur_page-1) * $size_page;//номер начального элемента
			}

			if($count > $size_page)
			{
				$class = new Paging($this->registry, $this->params);
				$paging = $class->MakePaging($cur_page, $count, $size_page);//вызов шаблона для постраничной навигации
			}
			
			$param2['limit'] = $start_page.', '.$size_page;
			return array('list'=>$this->select($param2), 'paging'=>$paging);//echo $data['sql'];
		}
		elseif(is_numeric($param))
		{
			return $this->select(array('where'=>'__tb.id:='.$param.'__'));
		}
		elseif(is_string($param))
		{
			return $this->select(array('where'=>'__tb.url:='.$param.'__'));
		}
		else{
			return $this->select($param);
		}
	}
	
	//-----------------функция для вывода каталога SELECT------------------------------------------------------
	function select_tree( $tab='menu', $cur_id = '')
	{
		$text='';
		$query="SELECT * FROM `$tab` JOIN `{$this->registry['key_lang']}_$tab`  on `{$this->registry['key_lang']}_$tab`.`menu_id`=`$tab`.`id` ORDER BY `sub` asc, `sort` asc";
	
		$arrayCategories = array();
		foreach( $this->db->rows($query) as $row){ 	
			$arrayCategories[$row['id']] = array("sub" => $row['sub'], "name" => $row['name'] );	 
		} 
	 
		$text=Arr::createTree_cat($arrayCategories, 0, $cur_id);
		return 	$text;	
	}
	
	public function getPage($id, $index='*')/////Get text page from tables menu or pages
	{
		//echo $id;
		if(is_numeric($id))$WHERE='tb1.id=?';
		else $WHERE='tb1.url=?';
		
		$page = $this->db->row("SELECT ".$index." FROM `menu` tb1
								 LEFT JOIN ".$this->registry['key_lang']."_menu tb2
								 ON tb1.id=tb2.menu_id
								 WHERE ".$WHERE." AND tb1.active=?",
		array($id, 1));
		
		if(!$page)
		{
			$page = $this->db->row("SELECT ".$index." FROM `pages` tb1
										  LEFT JOIN ".$this->registry['key_lang']."_pages tb2
										  ON tb1.id=tb2.pages_id
										  WHERE ".$WHERE." AND tb1.active=?",
        	array($id, 1));
			$page['type']='pages';
		}
		else $page['type']='menu';
		return $page;
	}
	
	public function getBlock($id)
	{
		if(is_array($id))
		{
			$where='';
			foreach($id as $row)
			{
				$where.="OR id='$row'";
			}
			if($where!='')$where="AND (".substr($where, 3, strlen($where)).")";
			return $this->db->rows_key("SELECT id, body 
										   FROM info_blocks tb 
										   
										   LEFT JOIN ".$this->registry['key_lang']."_info_blocks tb2 
										   ON tb.id=tb2.info_blocks_id
										   
										   WHERE tb.active='1' $where");
		}
		else{
			if(is_numeric($id))
			{
				$where="AND tb.id='$id'";
			}
			else{
				$where="AND tb.url='$id'";
				
			}	
			return $this->db->row("SELECT * 
								   FROM info_blocks tb 
								   
								   LEFT JOIN ".$this->registry['key_lang']."_info_blocks tb2 
								   ON tb.id=tb2.info_blocks_id
								   
								   WHERE tb.active='1' $where");
		}
	}
	
	public function breadcrumbAdmin()/////Хлебные крошки админки
	{
		if(isset($this->params['action'])&&($this->params['action']=='edit'||$this->params['action']=='add'))
			 return'<a href="/admin/'.strtolower($this->params['controller']).'" class="back-link">« Назад в:&nbsp;'.$this->params['module'].'</a>';
		else return'';
	}

	public function breadcrumbs($links)/////Хлебные крошки на сайте
	{
		$separator='<span>></span>';
		$return='';
		$cnt=count($links)-0;
		if($cnt>1)
		{
			$i=1;
			foreach($links as $row)
			{
				$return.=$row;
				if($cnt!=$i)$return.=' '.$separator.' ';	
				$i++;
			}
			if($return!='')$return='<div id="breadcrumbs">'.$return.'</div>';
			return $return;
		}
	}
	
	public function getBreadCat($catrow, $product_name='')
	{
		$return=array();
		if(is_numeric($catrow))
		{
			$catrow = $this->db->row("SELECT tb.id, tb.sub, tb.url, tb2.name 
									  FROM catalog tb 
									  
									  LEFT JOIN ".$this->registry['key_lang']."_catalog tb2
									  ON tb.id=tb2.catalog_id
											
									  WHERE tb.id=?", array($catrow));
				  
		}
		
		if($product_name!='')$last='<a href="'.LINK.'/catalog/'.$catrow['url'].'" title="'.$catrow['name'].'">'.$catrow['name'].'</a>'.' ';
		if($catrow['sub']==0)
		{
			if($product_name!='')$return = array('<a href="'.LINK.'/catalog/'.$catrow['url'].'" title="'.$catrow['name'].'">'.$catrow['name'].'</a>', 
												  $product_name);	
			else $return = array();	
		}
		else{
			$catrow2 = $this->db->row("SELECT * FROM catalog tb 
											LEFT JOIN ".$this->registry['key_lang']."_catalog tb2
											ON tb.id=tb2.catalog_id
											
										  WHERE tb.id=?", array($catrow['sub']));
			
			if($catrow2['sub']==0)
			{
				if($product_name!='')$return = array('<a href="'.LINK.'/catalog/'.$catrow2['url'].'" title="'.$catrow2['name'].'">'.$catrow2['name'].'</a>', 
													 '<a href="'.LINK.'/catalog/'.$catrow['url'].'" title="'.$catrow['name'].'">'.$catrow['name'].'</a>', 
													 $product_name);	
				else $return = array('<a href="'.LINK.'/catalog/'.$catrow2['url'].'" title="'.$catrow2['name'].'">'.$catrow2['name'].'</a>', 
									 $catrow['name']);	
			}
			else{
				$catrow3 = $this->db->row("SELECT * FROM catalog tb 
												LEFT JOIN ".$this->registry['key_lang']."_catalog tb2
												ON tb.id=tb2.catalog_id
												
											  WHERE tb.id=?",array($catrow2['sub']));
											  
				if($product_name!='')$return = array('<a href="'.LINK.'/catalog/'.$catrow3['url'].'" title="'.$catrow3['name'].'">'.$catrow3['name'].'</a>',  
													 '<a href="'.LINK.'/catalog/'.$catrow2['url'].'" title="'.$catrow2['name'].'">'.$catrow2['name'].'</a>', 
													 '<a href="'.LINK.'/catalog/'.$catrow['url'].'" title="'.$catrow['name'].'">'.$catrow['name'].'</a>', 
													 $product_name);	
				else $return = array('<a href="'.LINK.'/catalog/'.$catrow3['url'].'" title="'.$catrow3['name'].'">'.$catrow3['name'].'</a>',  
									 '<a href="'.LINK.'/catalog/'.$catrow2['url'].'" title="'.$catrow2['name'].'">'.$catrow2['name'].'</a>', 
									 $catrow['name']);										 
			}
		}
					
		return $return;
	}

	public function checkAccess($action, $module)//////Проверка доступа модулей в админке
	{
		/*
		'000'-off;
		'100'-read;
		'200'-read/edit;
		'300'-read/del;
		'400'-read/add;
		'500'-read/edit/del;
		'600'-read/edit/add;
		'700'-read/del/add;
		'800'-read/edit/del/add;
		*/
		$row = $this->db->row("SELECT m.`permission` 
							   FROM `moderators_permission` m
							   
							   LEFT JOIN moderators mm
							   ON m.moderators_type_id=mm.type_moderator
							   
							   LEFT JOIN modules mmm
							   ON mmm.id=m.module_id
							   
							   WHERE mmm.controller=? AND mm.id=?", array($module, $_SESSION['admin']['id']));
							   
		if($row['permission']==000)return false;
		elseif($action=='delete'&&($row['permission']!=500&&$row['permission']!=300&&$row['permission']!=700&&$row['permission']!=800))
		{
			return false;
		}
		elseif(($action=='edit'||$action=='update')&&($row['permission']!=200&&$row['permission']!=500&&$row['permission']!=600&&$row['permission']!=800))
		{
			return false;
		}
		elseif(($action=='add'||$action=='duplicate')&&($row['permission']!=400&&$row['permission']!=600&&$row['permission']!=700&&$row['permission']!=800))
		{
			return false;
		}
		return true;
	}
	
	public function checkUrl($tb, $url, $id)///Проверка уникальности URL
	{
		if($this->db->row("SELECT id from `".$tb."` WHERE url=? and id!=?", array($url, $id)))return messageAdmin('Данный адрес уже занят', 'error');
		else{
			$this->db->query("UPDATE `".$tb."` set url=? WHERE id=?", array($url, $id));
		}
	}
	
	public function currency()///Валюта
	{
		return $this->db->row("SELECT * FROM `currency` WHERE id='{$_SESSION['currency']}'");	
	}
	
	
	function left_menu_admin($vars)
	{
		$this->view = new View($this->registry);
		$vars['menu'] = $this->db->rows("SELECT tb.* FROM subsystem tb
										 
										 LEFT JOIN subsystem_modules tb3
										 ON tb.id=tb3.subsystem_id 
										 
										 LEFT JOIN moderators_permission tb2
				                         ON tb.id=tb2.subsystem_id 
										 
										 LEFT JOIN modules m
										 ON m.id=tb2.module_id
										 
										 WHERE tb2.moderators_type_id=? AND tb2.permission!=? AND m.controller=?
										 GROUP BY tb.id
										 ORDER BY tb.sort ASC, tb.`id` DESC", array($_SESSION['admin']['type'], '000', $vars['action']));

		$i=0;
		foreach($vars['menu'] as $row)
		{
			$vars['menu'][$i]['url']='/admin/'.$vars['action'].'/subsystem/'.$row['name'];
			$i++;
		}
		
		if(isset($vars['menu2']))
		{
			$vars['menu']=array_merge($vars['menu'], $vars['menu2']);//var_info($vars['menu']);	
		}
		return $this->view->Render('left_menu.phtml', $vars);	
	}
	
	public function subsystemAction($left_menu=array())
	{
		$class_name = ucfirst($this->params['subsystem']).'Controller';
		$class = new $class_name($this->registry, $this->params);
		
		$vars['message'] = '';
		$vars['subsystem'] = $this->params['subsystem'];
		$vars['action'] = $this->table;
		
		$row = $this->db->row("SELECT id, name FROM modules WHERE controller='".$this->table."'");
		$modules_id = $row['id'];
		
		if(isset($this->params['delsubsystem'])||isset($_POST['delete']))$vars['message'] = $class->delete();
		elseif(isset($_POST['update']))$vars['message'] = $class->save();
		elseif(isset($_POST['update_close']))$vars['message'] = $class->save();
		elseif(isset($this->params['addsubsystem']))$vars['message'] = $class->add($modules_id);

		$vars['where'] = "WHERE `modules_id`='".$modules_id."'";
		$vars['modules_id'] = $modules_id;
		$vars['modules_name'] = $row['name'];
		$vars['path'] = "/subsystem/".$this->params['subsystem'];
	
		if(count($left_menu)==0)$left_menu = array('action'=>$this->table, 'name'=>$this->name, 'sub'=>$this->params['subsystem']);
		else $left_menu = array('action'=>$this->table, 'name'=>$this->name, 'sub'=>$this->params['subsystem'], 'menu2'=>$left_menu);
		$data['left_menu'] = $this->left_menu_admin($left_menu);
		$data['content'] = $class->subcontent($vars);
		return $data;
	}
	
	public function photo_del($dir, $id)
	{
		if(file_exists("{$dir}{$id}.jpg"))unlink("{$dir}{$id}.jpg");
		if(file_exists("{$dir}{$id}_s.jpg"))unlink("{$dir}{$id}_s.jpg");	
	}
	
	public function insert_post_form($text)
	{
		$this->db->query("INSERT INTO `feedback` SET `text`=?", array($text));
	}
	
	public function active($id, $tb, $tb2)
	{
		$data=array();
		$data['message'] ='';
		if(!$this->checkAccess('edit', $tb))$data['message'] = messageAdmin('Отказано в доступе', 'error');
		
		if($tb=='info')$tb='info_blocks';
		if($tb2!='undefined')$tb=$tb2;
		if($data['message']=='')
		{
			$id=str_replace("active", "", $id);
			//$tb=$_POST['tb'];
			$row=$this->db->row("SELECT `active` FROM `$tb` WHERE `id`=?", array($id));
			if($row['active']==1)
			{
				$this->db->query("UPDATE `$tb` SET `active`=? WHERE `id`=?", array(0, $id));
				$data['active']='<div class="selected-status status-d"><a> Выкл. </a></div>';
			}
			else{
				$this->db->query("UPDATE `$tb` SET `active`=? WHERE `id`=?", array(1, $id));
				$data['active']='<div class="selected-status status-a"><a> Вкл. </a></div>';
			}
			$data['message']=messageAdmin('Данные успешно сохранены');
		}
		return $data;
	}
	
	public function sortTable($arr, $tb, $tb2)
	{
		if($tb=='module')$tb='modules';
		elseif($tb=='info_blocks')$tb='info';
		$data=array();
		$data['message'] ='';			
		if(!$this->checkAccess('edit', $tb))$data['message'] = messageAdmin('Отказано в доступе', 'error');
		if($tb2!='undefined')$tb=$tb2;
		if($data['message']=='')
		{
			$arr=str_replace("sort", "", $arr);
			preg_match_all("/=(\d+)/",$arr,$a);//echo var_dump($a);
			foreach($a[1] as $pos=>$id)
			{
				$pos2=$pos+1;
				//echo"update {$_POST['tb']} set sort='$pos2' WHERE id='".$id."'";
				$this->db->query("update `$tb` set `sort`=? WHERE `id`=?", array($pos2, $id));
			}
			$data['message']=messageAdmin('Данные успешно сохранены');
		}
		return $data;
	}
	
	function check_for_delete($subsystem_id, $tb, $group_id)
	{
		if($this->db->query("DELETE tb.* FROM `".$tb."` tb
									 
							 LEFT JOIN `moderators_permission` mp
							 ON mp.module_id=tb.modules_id
							 
							 WHERE mp.moderators_type_id=? AND `id`=? AND (permission='300' OR permission='500' OR permission='700' OR permission='800') AND mp.subsystem_id='0'", 
							 array($group_id, $subsystem_id)))return messageAdmin('Запись успешно удалена');
		else return messageAdmin('Ошибка в правах доступа!', 'error');	
	}
	
	function check_for_update($subsystem_id, $tb, $group_id)
	{
		$row = $this->db->row("SELECT tb.* FROM `".$tb."` tb
									 
								 LEFT JOIN `moderators_permission` mp
								 ON mp.module_id=tb.modules_id
								 
								 WHERE (mp.moderators_type_id=? AND `id`=? AND (permission='200' OR permission='500' OR permission='600' OR permission='800') AND mp.subsystem_id='0')
								 		OR (tb.modules_id='' AND `id`=?)", 
								 array($group_id, $subsystem_id, $subsystem_id));
		//var_info($row);	
		return $row;					 
	}
	
	function loadExtraPhoto($tempFile, $name, $tb, $fk, $id, $path, $width, $height)
	{
		$fk2=$fk.'_id';
		if(!is_dir($path))mkdir($path, 0755, true);
		$insert_id = $this->db->insert_id("INSERT INTO $tb SET {$fk2}=?, active=?", array($id, 1));
		foreach($this->language as $lang)
		{
			$tb_l=$lang['language'].'_'.$tb;
			$param = array($name, $insert_id);
			$this->db->query("INSERT INTO `$tb_l` SET `name`=?, `{$tb}_id`=?", $param);
		}
		
		if(!is_dir($path))mkdir($path, 0755, true);
		Images::resizeImage($tempFile, $path.$insert_id.".jpg", $path.$insert_id."_s.jpg", $width, $height);
		Images::set_watermark($this->settings['watermark'], $path.$insert_id."_s.jpg", $fk);
		
		$this->db->query("UPDATE {$tb} SET photo=? WHERE id=?", array($path.$insert_id."_s.jpg", $insert_id));
	}
}
?>