<?
class Product extends Model
{
    static $table='product'; //Главная талица
    static $name='Товары'; // primary key

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
        if(isset($_POST['active'], $_POST['url'], $_POST['name'], $_POST['title'], $_POST['keywords'], $_POST['description'], $_POST['body'])&&$_POST['name']!="")
        {
            if($_POST['url']=='')$url = String::translit($_POST['name']);
            else $url = String::translit($_POST['url']);

            $brend="";
            if(isset($_POST['brend_id']))$brend=",brend_id='{$_POST['brend_id']}'";
            $insert_id = $this->db->insert_id("INSERT INTO `".$this->table."`
											   SET
													`unit`=?,
													`stock`=?,
													`code`=?,
													`price`=?,
													`discount`=?,
													`active`=?,
													`date_add`=?
													$brend
													",
                array(
                    $_POST['unit'],
					$_POST['stock'],
					$_POST['code'],
					$_POST['price'],
					$_POST['discount'],
					$_POST['active'],
                    date("Y-m-d H:i:s")));

            //Language
            $languages = $this->db->rows("SELECT * FROM language");
            foreach($languages as $lang)
            {
                $tb=$lang['language']."_".$this->table;
                $param = array($_POST['name'], $_POST['body'], $_POST['body_m'], $insert_id);
                $this->db->query("INSERT INTO `$tb` SET `name`=?, `body`=?, `body_m`=?, `product_id`=?", $param);
            }
            $message = $this->checkUrl($this->table, $url, $insert_id);
			
			//////Save meta data
			$meta = new Meta($this->sets);
			$meta->save_meta($this->table, $url, $_POST['title'], $_POST['keywords'], $_POST['description']);
			
            ////////Set product categories
            if(isset($_POST['cat_id'])&&count($_POST['cat_id'])!=0)
            {
                if(isset($_POST['cat_id']))
                {
					$count=count($_POST['cat_id']) - 1;
                    for($i=0; $i<=$count; $i++)
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
					$count=count($_POST['status_id']) - 1;
                    for($i=0; $i<=$count; $i++)
                    {
                        $this->db->query("INSERT INTO product_status_set SET product_id=?, status_id=?", array($insert_id, $_POST['status_id'][$i]));
                    }
                }
            }
			
			///Price save
			if(isset($_POST['save_price_id']))
			{
				$count=count($_POST['save_price_id']) - 1;
				for($i=0; $i<=$count; $i++)
				{
					$this->db->query("INSERT INTO `price` 
										SET code=?,
											name=?,
											price=?,
											unit=?,
											stock=?,
											discount=?,
											price_type_id=?,
											product_id=?", 
										array($_POST['price_code'][$i], 
												$_POST['price_name'][$i], 
												$_POST['price_price'][$i], 
												$_POST['price_unit'][$i], 
												$_POST['price_stock'][$i], 
												$_POST['price_discount'][$i],
												$_POST['price_type'][$i],
												$insert_id));
				}
			}
			
			////////Set params
			$row = $this->db->row("SELECT id FROM modules WHERE `controller`=?", array('params'));
			if($row)
			{
				if(isset($_POST['params'])&&count($_POST['params'])!=0)
				{
					if(isset($_POST['params']))
					{
						$count=count($_POST['params']) - 1;
						for($i=0; $i<=$count; $i++)
						{
							$this->db->query("INSERT INTO params_product SET product_id=?, params_id=?", array($insert_id, $_POST['params'][$i]));
						}
					}
				}
			}
					
            ////Photo
            if(isset($_POST['current_photo'])&&file_exists($_POST['current_photo']))
            {
				$ext = pathinfo($_POST['current_photo'], PATHINFO_EXTENSION);
                $dir=Dir::createDir($insert_id);
                copy(str_replace('_s', '', $_POST['current_photo']), $dir['0'].$insert_id.".".$ext);
                copy($_POST['current_photo'], $dir['0'].$insert_id."_s.".$ext);
				
				$photo_m=str_replace('_s', '_m', $_POST['current_photo']);
				if(file_exists($photo_m))copy($photo_m, $dir['0'].$insert_id."_m.".$ext);
				
				$this->photo_del("files/tmp/", $_POST['tmp_image']);
				$this->db->query("UPDATE `".$this->table."` SET `photo`=? WHERE `id`=?", array($dir['0'].$insert_id."_s.".$ext, $insert_id));
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
                if(isset($_POST['save_id'], $_POST['code'], $_POST['price']))
                {
					$count=count($_POST['save_id']) - 1;
                    for($i=0; $i<=$count; $i++)
                    {
                        //echo $_POST['name'][$i].'<br>';
                        $param = array($_POST['code'][$i], $_POST['price'][$i], $_POST['sort'][$i], $_POST['save_id'][$i]);
                        $this->db->query("UPDATE `".$this->table."` tb
										  SET tb.`code`=?, tb.`price`=?, tb.`sort`=?
										  WHERE tb.id=?", $param);
                    }
                    $message .= messageAdmin('Данные успешно сохранены');
                }
                else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
            }
            else{
                if(isset($_POST['active'], $_POST['url'], $_POST['id'], $_POST['name'], $_POST['title'], $_POST['keywords'], $_POST['description'], $_POST['body']))
                {
                    if($_POST['url']=='') $url = String::translit($_POST['name']);
                    else $url = String::translit($_POST['url']);
					
					$meta = new Meta($this->sets);
					$meta->save_meta($this->table, $url, $_POST['title'], $_POST['keywords'], $_POST['description']);
					
                    ////////Set params
                    $row = $this->db->row("SELECT id FROM modules WHERE `controller`=?", array('params'));
                    if($row)
                    {
                        //$this->db->query("DELETE FROM params_product WHERE product_id=?", array($_POST['id']));
                        if(isset($_POST['params'])&&count($_POST['params'])!=0)
                        {
                            if(isset($_POST['params']))
                            {
								$count=count($_POST['params']) - 1;
                                for($i=0; $i<=$count; $i++)
                                {
									$row = $this->db->row("SELECT id FROM params_product WHERE params_id='{$_POST['params'][$i]}' AND product_id='{$_POST['id']}'");
									if(!$row)
									{
										$this->db->query("INSERT INTO params_product SET product_id=?, params_id=?", array($_POST['id'], $_POST['params'][$i]));
									}
                                }
                            }
                        }
                    }

					
					if(isset($_POST['price_param']))
					{
						$count=count($_POST['config_price_id']) - 1;
						for($i=0; $i<=$count; $i++)
						{
							$this->db->query("DELETE FROM product_property_sets WHERE price_id='{$_POST['config_price_id'][$i]}'");
						}
						
						$count=count($_POST['price_param']) - 1;
						for($i=0; $i<=$count; $i++)
						{
							$arr=explode('-', $_POST['price_param'][$i]);							
							if(isset($arr[1]))
							{
								$price_id=$arr[0];
								$params_id=$arr[1];
								$row = $this->db->row("SELECT id FROM params_product WHERE params_id='{$params_id}' AND product_id='{$_POST['id']}'");
								if(!$row)
								{
									$insert_id=$this->db->insert_id("INSERT INTO params_product (params_id, product_id) VALUES('{$params_id}', '{$_POST['id']}')");
								}
								else $insert_id=$row['id'];
								
								
								/*		*/
								$res = $this->get_param($params_id, $_POST['id']);
								$sets='';
								foreach($res as $row_s)
								{
									if($sets!='')$sets.=',';
									$sets.=$row_s['id'];
								}
								/*		*/
								
								
								$q="INSERT INTO product_property_sets SET price_id='{$price_id}', params_product_id='{$insert_id}', `sets`='$sets'";
								//echo $q;
								$this->db->query($q);
								
								
							}
						}
					}


                    ////////Set product categories
                    $this->db->query("DELETE FROM product_catalog WHERE product_id=?", array($_POST['id']));
                    if(isset($_POST['cat_id'])&&count($_POST['cat_id'])!=0)
                    {
                        if(isset($_POST['cat_id']))
                        {
							$count=count($_POST['cat_id']) - 1;
                            for($i=0; $i<=$count; $i++)
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
							$count=count($_POST['status_id']) - 1;
                            for($i=0; $i<=$count; $i++)
                            {
                                $this->db->query("INSERT INTO product_status_set SET product_id=?, status_id=?", array($_POST['id'], $_POST['status_id'][$i]));
                            }
                        }
                    }

                    $message = $this->checkUrl($this->table, $url, $_POST['id']);

                    $brend="";
                    if(isset($_POST['brend_id']))$brend=",brend_id='{$_POST['brend_id']}'";

                    $param = array($_POST['code'], $_POST['price'], $_POST['discount'], $_POST['unit'], $_POST['stock'], $_POST['name'], $_POST['body_m'], $_POST['body'], $_POST['active'], $_POST['id']);
                    $this->db->query("UPDATE `".$this->registry['key_lang_admin']."_".$this->table."` tb1, ".$this->table." tb2
					                  SET
					                        tb2.`code`=?,
											tb2.`price`=?,
											tb2.`discount`=?,
											tb2.`unit`=?,
											tb2.`stock`=?,
					                        tb1.`name`=?,
					                        tb1.`body_m`=?,
											tb1.`body`=?,
											tb2.active=?
											$brend

					                  WHERE
									  		tb1.product_id=tb2.id AND
                                            tb2.`id`=?
                                            ", $param);

                    ///Price save
                    if(isset($_POST['save_price_id']))
                    {
						$count=count($_POST['save_price_id']) - 1;
                        for($i=0; $i<=$count; $i++)
                        {
							$where=",product_photo_id=NULL";
							if($_POST['price_photo_id'][$i]!='')$where=",product_photo_id='{$_POST['price_photo_id'][$i]}'";
                            $this->db->query("UPDATE `price` 
												SET code=?,
													name=?,
													price=?,
													unit=?,
													stock=?,
													discount=?,
													price_type_id=?
													$where
												WHERE id=?", 
												array($_POST['price_code'][$i], 
														$_POST['price_name'][$i], 
														$_POST['price_price'][$i], 
														$_POST['price_unit'][$i], 
														$_POST['price_stock'][$i], 
														$_POST['price_discount'][$i],
														$_POST['price_type'][$i],
														$_POST['save_price_id'][$i]));
                        }
                    }
					
					 ///Dop photo
                    if(isset($_POST['save_photo_id']))
                    {
						$count=count($_POST['save_photo_id']) - 1;
                        for($i=0; $i<=$count; $i++)
                        {
							$param = array($_POST['photo_name'][$i], $_POST['save_photo_id'][$i]);
                            $this->db->query("UPDATE `".$this->registry['key_lang_admin']."_product_photo` SET name=? WHERE product_photo_id=?", $param);
                        }
                    }
					
					if(isset($_FILES['extra_files']))
                    {
						$dir=Dir::createDir($_POST['id']);
						for($i=0; $i<=count($_FILES['extra_files']) - 1; $i++)
                        {
							if(isset($_FILES['extra_files']['tmp_name'][$i])&&$_FILES['extra_files']['tmp_name'][$i]!="")
							{
								$this->loadExtraPhoto($_FILES['extra_files']['tmp_name'][$i], $_FILES['extra_files']['name'][$i], 'product_photo', 'product', $_POST['id'], $dir[1], $this->settings['width_product_extra'], $this->settings['height_product_extra']);
								
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
	
	public function listView()
	{
        $where=""; 
		$join=""; 
		/////////////////sort start
		if(!isset($_SESSION['by']))$_SESSION['by']='asc';
		if(!isset($_SESSION['order']))$_SESSION['order']='tb.sort';
		
		if(isset($this->params['page'])&&$this->params['page']>0)$_SESSION['page_product']=$this->params['page'];
		
		
		if(isset($this->params['order'])&&$this->params['order']=='name')$_SESSION['order']='tb_lang.name';
		elseif(isset($this->params['order'])&&$this->params['order']=='catalog')$_SESSION['order']='tb_cat_lang.name';
		elseif(isset($this->params['order'])&&$this->params['order']=='id')$_SESSION['order']='tb.id';
		elseif(isset($this->params['order'])&&$this->params['order']=='price')$_SESSION['order']='tb.price';
		elseif(isset($this->params['order'])&&$this->params['order']=='sort')$_SESSION['order']='tb.sort';
		elseif(isset($this->params['order'])&&$this->params['order']=='active')$_SESSION['order']='tb.active';
		
		if(isset($this->params['by'])&&$this->params['by']=='asc')$_SESSION['by']='desc';
		elseif(isset($this->params['by'])&&$this->params['by']=='desc')$_SESSION['by']='asc';
		
		if(isset($this->params['order']))$_SESSION['search_admin']['sort'] = $_SESSION['order'].' '.$_SESSION['by'].', tb.id DESC';
		/////////////////sort end
		
        if($_SESSION['search_admin']['word']!="")$where="AND (tb_lang.name LIKE '%{$_SESSION['search_admin']['word']}%' or 
														 tb.code LIKE '%{$_SESSION['search_admin']['word']}%' or 
														 tb_lang.body LIKE '%{$_SESSION['search_admin']['word']}%' or 
														 tb_lang.body_m LIKE '%{$_SESSION['search_admin']['word']}%' or 
														 tb_cat_lang.name LIKE '%{$_SESSION['search_admin']['word']}%')";
        
        
		if($_SESSION['search_admin']['price_from']!=""&&$_SESSION['search_admin']['price_to']=="")
			$where.="AND (tb.price >='{$_SESSION['search_admin']['price_from']}')";
        elseif($_SESSION['search_admin']['price_from']==""&&$_SESSION['search_admin']['price_to']!="")
			$where.="AND (tb.price <='{$_SESSION['search_admin']['price_to']}')";
        elseif($_SESSION['search_admin']['price_from']!=""&&$_SESSION['search_admin']['price_to']!="")
			$where.="AND (tb.price <='{$_SESSION['search_admin']['price_to']}' AND tb.price >='{$_SESSION['search_admin']['price_from']}')";
		
		if(isset($this->params['cat'])&&$this->params['cat']!='')
		{
			$where.=" AND tb_cat.url='{$this->params['cat']}'";
			$vars['curr_cat']="/admin/product/cat/".$this->params['cat'];
		}
		
		$sort2=explode('-', $_SESSION['search_admin']['cat_id']);
		if($_SESSION['search_admin']['cat_id']!="0")
		{
			if($sort2[0]=='status')
			{
				$where.="AND (tb4.status_id ='{$sort2[1]}')";
			}
			elseif($_SESSION['search_admin']['cat_id']!=''){
				$where.="AND (tb3.catalog_id ='{$_SESSION['search_admin']['cat_id']}')";
			}
		}
		
		if($_SESSION['search_admin']['params']!='')
		{
			$where.="AND pp.params_id ='{$_SESSION['search_admin']['params']}'";
			$join = "LEFT JOIN params_product pp ON pp.product_id=tb.id";
		}
		
		if(!isset($_SESSION['search_admin']['sort']))$_SESSION['search_admin']['sort']="tb.sort ASC, tb.id DESC";
		//$_SESSION['search_admin']['sort']="tb.sort ASC";

		$param = array("select"=>", tb.active, tb.code, tb.sort, tb_cat_lang.name AS catalog, tb_cat.id AS cat_id",
					   "join"=>"LEFT JOIN ".$this->registry['key_lang_admin']."_catalog tb_cat_lang ON tb_cat_lang.catalog_id=tb3.catalog_id 
								LEFT JOIN catalog tb_cat ON tb_cat_lang.catalog_id=tb_cat.id
								$join
								",
					   "where"=>$where,
					   "order"=>$_SESSION['search_admin']['sort'],
					   "price"=>true);
	
		$vars['currency'] = $this->db->row("SELECT icon FROM currency WHERE `base`='1'");
		$q = Catalog::getObject($this->sets)->queryProducts($param);

		$vars['list'] = Product::getObject($this->sets)->find(array_merge($q, array("paging"=>$this->settings['paging_product_admin'])));
		return $vars;
	}
	
	public function addpricetype()
	{
		$message='';
		if(isset($this->registry['access']))$message = $this->registry['access'];
		else
		{
			$this->db->query("INSERT INTO `price_type` SET `name`='New type price'");
			$message .= messageAdmin('Данные успешно сохранены');	
		}
		return $message;
	}
	
	public function save_pricetype()
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
					
					$param = array($_POST['name'][$i], $default, $_POST['id'][$i]);
					$this->db->query("UPDATE `price_type` SET `name`=?, `default`=? WHERE id=?", $param);
				}
				$message .= messageAdmin('Данные успешно сохранены');
			}
			else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
		}
		return $message;
	}
	
	public function default_price_type($type='user')
	{
		if(isset($_SESSION['user_id'],$_SESSION['user_info']['status'])&&$type=='user')
		{
			$row = $this->db->row("SELECT price_type_id AS id FROM `user_status` WHERE `id`=?", array($_SESSION['user_info']['status']));
		}
		else{
			$row = $this->db->row("SELECT id FROM `price_type` WHERE `default`='1'");	
		}
		return $row['id'];
	}
	
    public function getColors($id)
    {
        return $this->db->rows("SELECT tb.*, tb2.*
                                FROM `params` color

                                LEFT JOIN `params` tb
                                ON tb.sub = color.id

                                LEFT JOIN `".$this->registry['key_lang']."_params` tb2
                                ON tb2.`params_id` = tb.`id`

                                LEFT JOIN `params_product` pp
                                ON tb.id = pp.params_id

                                WHERE color.type='color' AND pp.product_id='$id'
								GROUP BY tb.id
                            ");
    }

    public function getSizes($id)
    {
        return $this->db->rows("SELECT tb.id, tb2.name
                                FROM `params` size

                                LEFT JOIN `params` tb
                                ON tb.sub = size.id

                                LEFT JOIN `".$this->registry['key_lang']."_params` tb2
                                ON tb2.`params_id` = tb.`id`

                                LEFT JOIN `params_product` pp
                                ON tb.id = pp.params_id

                                LEFT JOIN `product_catalog` pc
                                ON pp.product_id = pc.product_id

                                WHERE pp.product_id='$id' AND size.type='size'
								
								GROUP BY tb.id
                            ");
    }
	
	public function getPhotoProduct($product_id)
    {
		return $this->find(array('table'=>'product_photo',
										'where'=>"__tb.product_id:={$product_id}__",
										'order'=>'tb.`sort` ASC, tb.id DESC',
										'type'=>'rows'));
	}
	
	////////////Price
	function addPrice($id, $rel='')
	{
		if($rel!='add')
		{
			$row = $this->db->row("SELECT * FROM product WHERE id=?", array($id));	
			$param = array($row['price'], $row['code'], $row['discount'], $row['unit'], $row['stock'], $id, $this->default_price_type());
			$this->db->query("INSERT INTO price SET price=?, code=?, discount=?, unit=?, stock=?, product_id=?, price_type_id=?", $param);
			$vars['price'] = $this->db->rows("SELECT * FROM price WHERE product_id=? ORDER BY price_type_id ASC, sort ASC, id DESC", array($id));
		}
		else $vars['price']=array(1);
		$vars['price_type'] = $this->db->rows("SELECT * FROM price_type ORDER BY id ASC");	

		return $vars;
	}
	
	function delPrice($id)
    {
		if(isset($id))
		{
			$row=$this->db->row("SELECT product_id FROM price WHERE id=?", array($id));
			$this->db->query("DELETE FROM price WHERE id=?", array($id));
			$vars['price'] = $this->db->rows("SELECT * FROM price WHERE product_id=? ORDER BY price_type_id ASC, sort ASC, id DESC", array($row['product_id']));
			$vars['price_type'] = $this->db->rows("SELECT * FROM price_type ORDER BY id ASC");	
			
			return $vars;
		}
    }
	
	function configPrice($id, $product_id)
    {
		if(isset($id, $product_id))
		{
			$vars['params'] = Params::getObject($this->sets)->find(array('where'=>"(pc2.product_id='{$product_id}' AND sub IS NULL AND type!='') OR sub IS NOT NULL",
                                                    					 'join'=>'LEFT JOIN params_catalog pc ON pc.params_id=tb.id
																		 		  LEFT JOIN product_catalog pc2 ON pc.catalog_id=pc2.catalog_id',
																		 'type'=>'rows',
																		 'group'=>'tb.id',
																		 'order'=>'tb.sub ASC, tb.sort ASC'));
			
			$vars['params_set'] = $this->db->rows("SELECT pp.*,pps.price_id
												   FROM `params_product` pp
												   
												   RIGHT JOIN product_property_sets pps
												   ON pps.params_product_id=pp.id
												   
												   WHERE pp.product_id=? AND pps.price_id=?", array($product_id, $id));//var_info($vars['params_set']);

			$vars['photo'] = $this->getPhotoProduct($product_id);							
			return $vars;
		}
    }
	
	function getPrice($id, $price_type_id)
	{
		return $this->db->rows("SELECT * FROM price WHERE product_id=? AND price_type_id=? ORDER BY sort ASC, id DESC", array($id, $_SESSION['price_type_id']));	
	}
	
	
	function get_param($param_id, $product_id)
	{
		$row = $this->db->row("SELECT pp.*, pps.price_id, GROUP_CONCAT(DISTINCT pps.price_id SEPARATOR ', ') as name_remains 
							   FROM product_property_sets pps
							   
							   LEFT JOIN params_product pp
							   ON pp.`id` = pps.`params_product_id`
							   
							   WHERE pp.params_id=? AND pp.product_id=?", array($param_id, $product_id));

		$where='';
		$arr=explode(',', $row['name_remains']);
		foreach($arr as $row)
		{
			$where.=" OR pps.price_id='{$row}'";	
		}
		$where=" AND(".substr($where,3,strlen($where)).")";	
		//echo $where;
		$res = $this->db->rows("SELECT ru.name, ru.params_id as id

								FROM product_property_sets pps
								
								LEFT JOIN params_product pp
								ON pp.`id` = pps.`params_product_id`

								LEFT JOIN ".$this->registry['key_lang']."_params ru
								ON pp.params_id = ru.params_id
								
								LEFT JOIN params p
							    ON p.id=pp.params_id
								
								WHERE p.id!='$param_id' $where
								
								GROUP BY ru.params_id
								ORDER BY ru.name ASC",
								array($product_id));
		//var_info($res);
		return $res;	
	}
	
	function get_remains($param_id, $product_id)
	{
		asort($param_id);//var_info($param_id).'asda';
		$param_id=implode(', ',$param_id);
		$row = $this->db->row("SELECT price.id, 
									  price.`stock`, 
									  price.`price`, 
									  GROUP_CONCAT(DISTINCT p.id ORDER BY p.id ASC SEPARATOR ', ') as name_remains	
		
								FROM price

								LEFT JOIN product_property_sets pps
								ON pps.price_id = price.id
								
								LEFT JOIN params_product pp
								ON pp.`id` = pps.`params_product_id`
								
								LEFT JOIN params p
								ON pp.params_id = p.id

								WHERE pp.`product_id`=?
								
								GROUP BY price.id
								HAVING name_remains='$param_id'",
								array($product_id));
		//var_info($row);
		if($row['stock']>0)$stock='<div style="font-size:16px; color:#0a8527; margin-top:5px;" id="available">Есть в наличии</div>';
        else $stock='<div style="font-size:16px; color:#aaa; margin-top:5px;">Нет в наличии</div>';
		
		$price=Numeric::viewPrice($row['price']);
		return array('cur_price'=>$price['cur_price'], 'price'=>$price['price'], 'stock'=>$stock);
	}
}
?>