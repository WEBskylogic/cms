<?php
/*
 * вывод каталога компаний и их данных
 */
class ProductController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		$this->tb = "product";
		$this->name = "Товары";
		$this->tb_lang = $this->key_lang.'_'.$this->tb;
        $this->tb_cat=$this->key_lang.'_catalog';
		$this->tb_photo=$this->key_lang.'_product_photo';
        $this->width=100;
        $this->height=100;
		$this->registry = $registry;
		//$this->db->row("SELECT FROM `moderators_permission` WHERE `id`=?", array($_SESSION['admin']['id']));
		parent::__construct($registry, $params);
	}

	public function indexAction()
	{
        if(!isset($_SESSION['search'])||isset($_POST['clear']))
        {
			$_SESSION['search']=array();
            $_SESSION['search']['cat_id']=0;
            $_SESSION['search']['price_from']="";
            $_SESSION['search']['price_to']="";
            $_SESSION['search']['word']="";
        }
        elseif(isset($_POST['word']))
        {
            $_SESSION['search']['cat_id']=$_POST['cat_id'];
            $_SESSION['search']['price_from']=$_POST['price_from'];
            $_SESSION['search']['price_to']=$_POST['price_to'];
            $_SESSION['search']['word']=$_POST['word'];
        }
		$vars['message'] = '';
		$vars['name'] = $this->name;
		if(isset($this->registry['access']))$vars['message'] = $this->registry['access'];
		if(isset($this->params['delete'])||isset($_POST['delete']))$vars['message'] = $this->delete();
		elseif(isset($_POST['update']))$vars['message'] = $this->save();
		elseif(isset($_POST['update_close']))$vars['message'] = $this->save();
		elseif(isset($_POST['add_close']))$vars['message'] = $this->add();
		
		$view = new View($this->registry);
		$vars['list'] = $view->Render('view.phtml', $this->listView());

        $vars['catalog'] = $this->db->rows("SELECT tb.*, tb3.*, tb2.product_id
											  FROM `".$this->tb_cat."` tb
											  
											  LEFT JOIN `catalog` tb3
											  ON tb.cat_id=tb3.id
	
											  LEFT JOIN `product_catalog` tb2
											  ON tb.cat_id=tb2.catalog_id
											  
											  GROUP by tb3.id
											  ORDER BY tb3.sort ASC");
										  
		$data['content'] = $view->Render('list.phtml', $vars);
		return $this->Render($data);
	}
	
	public function addAction()
	{
		$vars['message'] = '';
		if(isset($_POST['add']))$vars['message'] = $this->add();
		
		$vars['list'] = $this->listView();
		$view = new View($this->registry);
		$vars['catalog'] = $this->db->rows("SELECT tb.*, tb3.*
											  FROM `".$this->tb_cat."` tb
											  
											  LEFT JOIN `catalog` tb3
											  ON tb.cat_id=tb3.id

											  GROUP BY tb.cat_id
											  ORDER BY tb3.sort ASC");
		$vars['status'] = $this->db->rows("SELECT * FROM `product_status`");	
		$vars['currency'] = $this->db->row("SELECT icon FROM currency WHERE `base`='1'");									  
		$data['content'] = $view->Render('add.phtml', $vars);
		return $this->Render($data);
	}
	
	public function editAction()
	{
		//if($vars['message']!='')return Router::act('error');
		$vars['message'] = '';
        $dir=createDir($this->params['edit']);
        $view = new View($this->registry);
		if(isset($_POST['update']))$vars['message'] = $this->save();
        if(isset($_POST['dell']))
        {
            for($i=0; $i<=count($_POST['photo_id']) - 1; $i++)
            {
                $this->db->query("DELETE FROM `product_photo` WHERE `id`=?", array($_POST['photo_id'][$i]));
                if(file_exists("/{$dir['0']}/{$_POST['photo_id'][$i]}.jpg"))unlink("/{$dir['0']}/{$_POST['photo_id'][$i]}.jpg");
                if(file_exists("/{$dir['0']}/{$_POST['photo_id'][$i]}_s.jpg"))unlink("/{$dir['0']}/{$_POST['photo_id'][$i]}_s.jpg");

            }
            $message = messageAdmin('Запись успешно удалена');
        }
        elseif(isset($this->params['delete'])&& $this->params['delete']!='')
        {
            $id = $this->params['delete'];
            if($this->db->query("DELETE FROM `product_photo` WHERE `id`=?", array($id)))$message = messageAdmin('Запись успешно удалена');
            if(file_exists("/{$dir['0']}/{$id}.jpg"))unlink("files/photos/{$dir['0']}/{$id}.jpg");
            if(file_exists("/{$dir['0']}/{$id}_s.jpg"))unlink("files/photos/{$dir['0']}/{$id}_s.jpg");
        }
		

		$vars['edit'] = $this->db->row("SELECT
											*
										 FROM ".$this->tb." tb

                                         LEFT JOIN ".$this->tb_lang." tb2
                                         ON tb.id=tb2.product_id
										 WHERE
											tb.id=?",
										array($this->params['edit']));
										
		//////////Params set
		$row = $this->db->row("SELECT id FROM module WHERE `controller`=?", array('params'));
		if($row)
		{
			$vars['params'] = $this->db->rows("SELECT tb.id, tb.sub, tb2.name
												  FROM `params` tb
												  LEFT JOIN `".$this->key_lang."_params` tb2
												  ON tb2.params_id=tb.id
												  ORDER BY tb.sort ASC");
			$vars['params_set'] = $this->db->rows("SELECT * FROM `params_product` WHERE product_id=?", array($vars['edit']['id']));	
			$vars['params'] = $view->Render('params.phtml', $vars);								  
		}								

		////Extra photo
        $vars['photo'] = $this->db->rows("SELECT * FROM `product_photo` tb
                                          LEFT JOIN `".$this->key_lang."_product_photo` tb2
                                          ON tb.id=tb2.photo_id
                                          WHERE product_id=?
                                          ORDER BY tb.sort ASC",
            array($vars['edit']['id']));
        $vars['photo'] = $view->Render('photo.phtml', $vars);
		
		/////Catalog
        $vars['catalog'] = $this->db->rows("SELECT tb.*, tb3.*, tb2.product_id
											  FROM `".$this->tb_cat."` tb
											  
											  LEFT JOIN `catalog` tb3
											  ON tb.cat_id=tb3.id
		
											  LEFT JOIN `product_catalog` tb2
											  ON tb.cat_id=tb2.catalog_id AND tb2.product_id='{$this->params['edit']}'
											  
											  GROUP BY tb.cat_id
											  ORDER BY tb3.sort ASC");
		
		/////Catalog set							  
		$vars['status'] = $this->db->rows("SELECT * FROM `product_status` tb
                                          LEFT JOIN `product_status_set` tb2
                                          ON tb.id=tb2.status_id AND product_id=?",
            array($vars['edit']['id']));
		
		$vars['currency'] = $this->db->row("SELECT icon FROM currency WHERE `base`='1'");	
		$vars['height']=$this->height;	
		$vars['width']=$this->width;							  
		$vars['list'] = $this->listView();
        $data['styles']=array('default.css', 'uploadify.css');
        $data['scripts']=array('swfobject.js', 'jquery.uploadify.v2.1.4.min.js');

		$data['content'] = $view->Render('edit.phtml', $vars);
		return $this->Render($data);
	}
	
	
	private function add()
	{
		$message='';
		if(isset($_POST['active'], $_POST['url'], $_POST['name'], $_POST['title'], $_POST['keywords'], $_POST['description'], $_POST['body'])&&$_POST['name']!="")
		{
			if($_POST['url']=='')$url = translit($_POST['name']);
			else $url = translit($_POST['url']);

			$insert_id = $this->db->insert_id("INSERT INTO `".$this->tb."` 
											   SET 
											   		`brend_id`=?, 
													`active`=?, 
													`price`=?, 
													`discount`=?,
													`code`=?, 
													`date_add`=?", 
											   array(0, 
											   		 $_POST['active'],
													 $_POST['price'],
													 $_POST['discount'],
													 $_POST['code'],
													 date("Y-m-d H:i:s")));
													 
			//Language
			foreach($this->language as $lang)
			{
				$tb=$lang['language']."_".$this->tb;
				$param = array($_POST['name'], $_POST['title'], $_POST['keywords'], $_POST['description'], $_POST['body'], $_POST['body_m'], $insert_id);
				$this->db->query("INSERT INTO `$tb` SET `name`=?, `title`=?, `keywords`=?, `description`=?, `body`=?, `body_m`=?, `product_id`=?", $param);
			}										 
			$message = $this->checkUrl($this->tb, $url, $insert_id);
			
			////////Set product categories
			if(isset($_POST['cat_id'])&&count($_POST['cat_id'])!=0)
			{
				if(isset($_POST['cat_id']))
				{
					for($i=0; $i<=count($_POST['cat_id']) - 1; $i++)
					{
						$this->db->query("insert into product_catalog set product_id=?, catalog_id=?", array($insert_id, $_POST['cat_id'][$i]));
					}
				}
			}
			
			////////Set product status
			if(isset($_POST['status_id'])&&count($_POST['status_id'])!=0)
			{
				if(isset($_POST['status_id']))
				{
					for($i=0; $i<=count($_POST['status_id']) - 1; $i++)
					{
						$this->db->query("INSERT INTO product_status_set SET product_id=?, status_id=?", array($insert_id, $_POST['status_id'][$i]));
					}
				}
			}
			
			////Photo
			if(isset($_FILES['photo']['tmp_name'])&&$_FILES['photo']['tmp_name']!="")
			{
				$dir=createDir($insert_id);
				resizeImage($_FILES['photo']['tmp_name'], $dir['0'].$insert_id.".jpg", $dir['0'].$insert_id."_s.jpg", $this->width, $this->height);
			}
			
			$message.= messageAdmin('Данные успешно добавлены');
		}
		else $message.= messageAdmin('При добавление произошли ошибки', 'error');	
		return $message;
	}
	
	private function save()
	{
		$message='';
		if(isset($this->registry['access']))$message = $this->registry['access'];
		else
		{
			if(isset($_POST['save_id'])&&is_array($_POST['save_id']))
			{
				if(isset($_POST['save_id'], $_POST['code'], $_POST['price']))
				{
					for($i=0; $i<=count($_POST['save_id']) - 1; $i++)
					{
						//echo $_POST['name'][$i].'<br>';
						$param = array($_POST['code'][$i], $_POST['price'][$i], $_POST['save_id'][$i]);
						$this->db->query("UPDATE `".$this->tb_lang."` tb, `".$this->tb."` tb2 
										  SET tb2.`code`=?, tb2.`price`=? 
										  WHERE tb.product_id=? AND tb2.id=tb.product_id", $param);
					}
					$message .= messageAdmin('Данные успешно сохранены');
				}
				else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
			}
			else{
				if(isset($_POST['active'], $_POST['url'], $_POST['id'], $_POST['name'], $_POST['title'], $_POST['keywords'], $_POST['description'], $_POST['body']))
				{
					if($_POST['url']=='')$url = translit($_POST['name']);
					else $url = translit($_POST['url']);

                    ////////Set params
					$row = $this->db->row("SELECT id FROM module WHERE `controller`=?", array('params'));
					if($row)
					{
						$this->db->query("DELETE FROM params_product WHERE product_id=?", array($_POST['id']));
						if(isset($_POST['params'])&&count($_POST['params'])!=0)
						{
							if(isset($_POST['params']))
							{
								for($i=0; $i<=count($_POST['params']) - 1; $i++)
								{
									$this->db->query("INSERT INTO params_product SET product_id=?, params_id=?", array($_POST['id'], $_POST['params'][$i]));
								}
							}
						}
					}
					
					////////Set product categories
                    $this->db->query("DELETE FROM product_catalog WHERE product_id=?", array($_POST['id']));
                    if(isset($_POST['cat_id'])&&count($_POST['cat_id'])!=0)
                    {
                        if(isset($_POST['cat_id']))
                        {
                            for($i=0; $i<=count($_POST['cat_id']) - 1; $i++)
                            {
                                $this->db->query("INSERT INTO product_catalog SET product_id=?, catalog_id=?", array($_POST['id'], $_POST['cat_id'][$i]));
                            }
                        }
                    }
					
					////////Set product status
                    $this->db->query("DELETE FROM product_status_set WHERE product_id=?", array($_POST['id']));
                    if(isset($_POST['status_id'])&&count($_POST['status_id'])!=0)
                    {
                        if(isset($_POST['status_id']))
                        {
                            for($i=0; $i<=count($_POST['status_id']) - 1; $i++)
                            {
                                $this->db->query("INSERT INTO product_status_set SET product_id=?, status_id=?", array($_POST['id'], $_POST['status_id'][$i]));
                            }
                        }
                    }

					$message = $this->checkUrl($this->tb, $url, $_POST['id']);
					
					$param = array($_POST['code'], $_POST['price'], $_POST['name'], $_POST['title'], $_POST['keywords'], $_POST['description'], $_POST['body'], $_POST['body_m'], $_POST['active'], $_POST['discount'], $_POST['id']);
					$this->db->query("UPDATE `".$this->tb_lang."` tb1, ".$this->tb." tb2
					                  SET
					                        tb2.`code`=?,
											tb2.`price`=?,
					                        tb1.`name`=?,
					                        tb1.`title`=?,
					                        tb1.`keywords`=?,
					                        tb1.`description`=?,
					                        tb1.`body`=?,
											tb1.`body_m`=?,
											tb2.active=?,
											tb2.discount=?
											
					                  WHERE
									  		tb1.product_id=tb2.id AND
                                            tb2.`id`=?
                                            ", $param);

                    ////Photo
                    if(isset($_FILES['photo']['tmp_name'])&&$_FILES['photo']['tmp_name']!="")
                    {
                        $dir=createDir($_POST['id']);
                        resizeImage($_FILES['photo']['tmp_name'], $dir['0'].$_POST['id'].".jpg", $dir['0'].$_POST['id']."_s.jpg", $this->width, $this->height);
                    }
					
					///Dop photo
					if(isset($_POST['save_photo_id']))
					{
						for($i=0; $i<=count($_POST['save_photo_id']) - 1; $i++)
						{
							$this->db->query("UPDATE ".$this->tb_photo." SET name=? WHERE photo_id=?", array($_POST['photo_name'][$i], $_POST['save_photo_id'][$i]));
						}
					}
					$message .= messageAdmin('Данные успешно сохранены');
				}
				else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
			}
		}
		return $message;
	}
	
	private function delete()
	{
		$message='';
		if(isset($this->registry['access']))$message = $this->registry['access'];
		else
		{
			if(isset($_POST['id'])&&is_array($_POST['id']))
			{
				for($i=0; $i<=count($_POST['id']) - 1; $i++)
				{
					$dir = createDir($_POST['id'][$i]);
					removeDir($dir[0]);
					$this->db->query("DELETE FROM `".$this->tb."` WHERE `id`=?", array($_POST['id'][$i]));
				}
				$message = messageAdmin('Запись успешно удалена');
			}
			elseif(isset($this->params['delete'])&& $this->params['delete']!='')
			{
				$id = $this->params['delete'];
				$dir = createDir($id);
				removeDir($dir[0]);
				if($this->db->query("DELETE FROM `".$this->tb."` WHERE `id`=?", array($id)))$message = messageAdmin('Запись успешно удалена');
			}
			
		}
		return $message;
	}
	
	private function listView()
	{
        $where="";
        if($_SESSION['search']['word']!="")$where="AND (tb2.name LIKE '%{$_SESSION['search']['word']}%' or tb.code LIKE '%{$_SESSION['search']['word']}%' or tb2.body LIKE '%{$_SESSION['search']['word']}%' or tb2.body_m LIKE '%{$_SESSION['search']['word']}%' or tb4.name LIKE '%{$_SESSION['search']['word']}%')";
        if($_SESSION['search']['cat_id']!=0)$where.="AND (tb5.id ='{$_SESSION['search']['cat_id']}' OR tb5.sub ='{$_SESSION['search']['cat_id']}')";
        if($_SESSION['search']['price_from']!=""&&$_SESSION['search']['price_to']=="")$where.="AND (tb.price >='{$_SESSION['search']['price_from']}')";
        elseif($_SESSION['search']['price_from']==""&&$_SESSION['search']['price_to']!="")$where.="AND (tb.price <='{$_SESSION['search']['price_to']}')";
        elseif($_SESSION['search']['price_from']!=""&&$_SESSION['search']['price_to']!="")$where.="AND (tb.price <='{$_SESSION['search']['price_to']}' AND tb.price >='{$_SESSION['search']['price_from']}')";
        $size_page =10;
        $start_page = 0;
        $cur_page = 0;
        $vars['paging'] = '';

        if(isset($this->params['page']))
        {
            $cur_page = $this->params['page'];
            $start_page = ($cur_page-1) * $size_page;//номер начального элемента
        }
        $q="SELECT
                tb.*,
                tb2.name,
				tb4.name as catalog
             FROM ".$this->tb." tb
                LEFT JOIN
                    ".$this->tb_lang." tb2
                ON tb.id=tb2.product_id

                LEFT JOIN product_catalog tb3
                ON tb3.product_id=tb.id
				
				LEFT JOIN ".$this->tb_cat." tb4
                ON tb4.cat_id=tb3.catalog_id
				
				LEFT JOIN catalog tb5
                ON tb4.cat_id=tb5.id

             WHERE tb.id!='0' $where
             GROUP BY tb.id
             ORDER BY tb.`sort` ASC, id DESC";
        $sql = $q." LIMIT ".$start_page.", ".$size_page."";
        //echo $sql;
        $count = $this->db->query($q);//кол страниц
        if($count > $size_page)
        {
            $vars['paging'] = Paging::MakePaging($cur_page, $count, $size_page, $dir="admin_");//вызов шаблона для постраничной навигации
        }
		$vars['currency'] = $this->db->row("SELECT icon FROM currency WHERE `base`='1'");	
        $vars['list'] = $this->db->rows($sql);
		return $vars;
	}
}
?>