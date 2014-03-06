<?php

class Catalog extends Model
{
    static $table='catalog'; //Главная талица
    static $name='Каталог'; // primary key

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
        if(isset($_POST['active'], $_POST['url'], $_POST['name'], $_POST['title'], $_POST['keywords'], $_POST['description'], $_POST['body'], $_POST['sub'])&&$_POST['name']!="")
        {
			
			
            if($_POST['sub']==0)$_POST['sub']=NULL;
            $param = array($_POST['active'], $_POST['sub']);
            $insert_id = $this->db->insert_id("INSERT INTO `".$this->table."` SET `active`=?, sub=?", $param);
			
			if($_POST['url']=='')$url = String::translit($_POST['name'].'-'.$insert_id);
            else $url = String::translit($_POST['url']);
			$message = $this->checkUrl($this->table, $url, $insert_id);
			
            $languages = $this->db->rows("SELECT * FROM language");
            foreach($languages as $lang)
            {
                $tb=$lang['language']."_".$this->table;
                $param = array($_POST['name'], $_POST['body'], $insert_id);
                $this->db->query("INSERT INTO `$tb` SET `name`=?, `body`=?, `catalog_id`=?", $param);
            }
			
			
			
			//////Save meta data
			$meta = new Meta($this->sets);
			$meta->save_meta($this->table, $url, $_POST['title'], $_POST['keywords'], $_POST['description']);
			
			//////Save catalog params
			if(isset($_POST['cat_id'])&&count($_POST['cat_id'])!=0)
			{
				if(isset($_POST['cat_id']))
				{
					$count=count($_POST['cat_id']) - 1;
					for($i=0; $i<=$count; $i++)
					{
						$this->db->query("INSERT INTO params_catalog SET params_id=?, catalog_id=?", array($_POST['cat_id'][$i], $insert_id));
					}
				}
			}
			
			////Photo
            if(isset($_POST['current_photo'])&&file_exists($_POST['current_photo']))
            {
				$ext = pathinfo($_POST['current_photo'], PATHINFO_EXTENSION);
                $dir="files/catalog/";
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
			
			$message = $this->checkUrl($this->table, $url, $insert_id);
			
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
					$count=count($_POST['save_id']) - 1;
                    for($i=0; $i<=$count; $i++)
                    {
                        if($_POST['url'][$i]=='')$url = String::translit($_POST['name'][$i]);
                        else $url = $_POST['url'][$i];

                        $message = $this->checkUrl($this->table, $url, $_POST['save_id'][$i]);
                        $param = array($_POST['name'][$i], $_POST['save_id'][$i]);
                        $this->db->query("UPDATE `".$this->registry['key_lang_admin']."_".$this->table."` SET `name`=? WHERE catalog_id=?", $param);
                    }
                    $message .= messageAdmin('Данные успешно сохранены');
                }
                else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
            }
            else{
                if(isset($_POST['active'], $_POST['url'], $_POST['id'], $_POST['name'], $_POST['title'], $_POST['keywords'], $_POST['description'], $_POST['body']))
                {
                    if($_POST['url']=='')$url = String::translit($_POST['name'].'-'.$_POST['id']);
                    else $url = String::translit($_POST['url']);
					
					//////Save meta data
					$meta = new Meta($this->sets);
					$meta->save_meta($this->table, $url, $_POST['title'], $_POST['keywords'], $_POST['description']);

                    if($_POST['sub']==0)$sub = NULL;
                    else $sub = $_POST['sub'];

                    $message = $this->checkUrl($this->table, $url, $_POST['id']);
                    $param = array($_POST['active'], $sub, $_POST['id']);
                    $this->db->query("UPDATE `".$this->table."` SET `active`=?, sub=? WHERE id=?", $param);

                    $param = array($_POST['name'], $_POST['title'],  $_POST['id']);
                    $this->db->query("UPDATE `".$this->registry['key_lang_admin']."_".$this->table."` SET `name`=?, `body`=? WHERE `catalog_id`=?", $param);
					
					//////Save catalog params
					$this->db->query("DELETE FROM `params_catalog` WHERE `catalog_id`=?", array($_POST['id']));
					if(isset($_POST['cat_id'])&&count($_POST['cat_id'])!=0)
					{
                        if(isset($_POST['cat_id']))
						{
							$count=count($_POST['cat_id']) - 1;
                            for($i=0; $i<=$count; $i++)
							{
                                $this->db->query("INSERT INTO params_catalog SET params_id=?, catalog_id=?", array($_POST['cat_id'][$i], $_POST['id']));
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

    public function queryProducts($param=array())
    {
		if(!isset($param['select']))$param['select']="";
		if(!isset($param['join']))$param['join']="";	
		if(!isset($param['where']))$param['where']="";	
		if(!isset($param['group']))$param['group']="tb.id";	
		if(!isset($param['order']))$param['order']="tb.`sort` ASC, tb.id DESC";
		if(!isset($param['limit']))$param['limit']="";
		
		if(!isset($param['price']))
		{
			$param['select'].=", tb_price.id as price_id, tb_price.price, tb_price.discount, tb_price.stock, tb_price.unit";	
			$param['join'].="LEFT JOIN (SELECT * FROM `price` WHERE price_type_id='".$_SESSION['price_type_id']."' ORDER BY `sort` ASC, id DESC) as `tb_price`
						 	 ON `tb_price`.product_id=tb.id";
		}
		else{
			$param['select'].=", tb.id as price_id, tb.price, tb.discount, tb.stock, tb.unit";		
		}
			
        $q=array("select"=>"tb.id,
							tb.url,
							tb.photo,
							tb.status_id,
							tb_lang.name,
							tb_lang.body_m,
							tb3.catalog_id".$param['select'],
				"join"=>"LEFT JOIN product_catalog tb3
						 ON tb3.product_id=tb.id

						 ".$param['join'],
				"where"=>"tb.id!='0' ".$param['where'],
				"group"=>$param['group'],
				"order"=>$param['order'],
				"limit"=>$param['limit'],
				"type"=>"rows");
        return $q;
    }

    public function subcats($catrow)
    {

        $subcats = $this->db->rows("SELECT tb.url, tb2.*, count(distinct prod.id) as count
									FROM `catalog` tb
			
									LEFT JOIN `ru_catalog` tb2
									ON tb.id = tb2.catalog_id
			
									LEFT JOIN product_catalog prodcat
									ON prodcat.catalog_id = tb.id
			
									LEFT JOIN product prod
									ON prod.id = prodcat.product_id
			
									WHERE tb.sub=? GROUP BY tb.id", array($catrow['id']));
		
		if(count($subcats)==0&&isset($catrow['sub']))
		{
			$subcats = $this->db->rows("SELECT tb.url, tb2.*, count(distinct prod.id) as count
										FROM `catalog` tb
				
										LEFT JOIN `ru_catalog` tb2
										ON tb.id = tb2.catalog_id
				
										LEFT JOIN product_catalog prodcat
										ON prodcat.catalog_id = tb.id
				
										LEFT JOIN product prod
										ON prod.id = prodcat.product_id
				
										WHERE tb.sub=? GROUP BY tb.id", array($catrow['sub']));	
		}

        return $subcats;
    }
	
	
	public function getParams($products, $cat_id)
    {
		$product_q = "";
        foreach($products as $row)
        {
            if($product_q!='')$product_q.=" OR ";
			$product_q.="pp.product_id='".$row['id']."'";
        }
		if($product_q!='')$product_q=" AND (".$product_q.")";
		else $product_q=" AND pp.product_id='0'";
		
		$query = "  SELECT tb1.id, tb1.url, tb1.sub, tb2.name, COUNT(DISTINCT pp.product_id) as count, pp.product_id, pp.params_id

					FROM `params` tb1

					LEFT JOIN ".$this->registry['key_lang']."_params tb2
					ON tb1.id=tb2.params_id

					LEFT JOIN params_product pp
					ON pp.params_id = tb1.id $product_q

					LEFT JOIN params_catalog cp
					ON tb1.id = cp.params_id

					WHERE tb1.active='1' AND cp.catalog_id='".$cat_id."'
					
					GROUP BY tb1.id
					ORDER BY tb1.`sort` ASC, tb1.id DESC";

         $res = $this->db->rows($query);

		 return $res;
	}

    public function sub_id($table, $id, $table_dop="id")
    {
        $sel = '';
        $query = "SELECT * FROM `$table` WHERE `sub` = ? and `active`='1' ORDER BY `$table`.`sub` asc, `$table`.`sort` asc";
        $result = $this->db->rows($query, array($id));

        foreach ($result as $row)
		{
            $sub = $row['id'];
            $sel = $sel."$table_dop ='$sub' OR ".$this->sub_id($table, $sub, $table_dop);
        }
        return $sel;
    }
}