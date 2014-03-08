<?php
include MODULES.'importexel/SimpleXLSX.php';

class Importexel extends Model{
	static $table='catalog'; //Главная талица
    static $name='Каталог'; // primary key

	public function __construct($registry)
    {
        parent::getInstance($registry);
    }

    //для доступа к классу через статичекий метод
	
	///поля: артикул / название товара / текущий каталог / родительский каталог / Цена / краткое описание / дополнительное описание
	public static function getObject($registry)
	{
		return new self::$table($registry);
	}
	
	function export_products()
	{
		
		$path="files/tmp/";//, c_parent.catalog_id as parent_id, c_parent.name as parent_name
		/*$query="SELECT tb.id, tb.code, tb.stock, tb2.name, price.price, tb2.body_m, tb2.body, c2.name as cat_name, c.id as catalog_id
				FROM product tb
				
				LEFT JOIN ru_product tb2
				ON tb.id=tb2.product_id
				
				LEFT JOIN product_catalog pc
				ON tb.id=pc.product_id
				
				LEFT JOIN catalog c
				ON c.id=pc.catalog_id
				
				LEFT JOIN ru_catalog c2
				ON c.id=c2.catalog_id
				
				LEFT JOIN ru_catalog c_parent
				ON c.sub=c_parent.catalog_id
				
				LEFT JOIN price
				ON tb.id=price.product_id
				
				GROUP BY tb.id
				ORDER BY tb.sort ASC, tb.id DESC";*/
		$query="SELECT tb.id AS export_id, 
					   tb.code AS export_code, 
					   tb.stock AS export_stock, 
					   tb2.name AS export_name, 
					   tb_price.price AS export_price, 
					   tb2.body_m AS export_body_m, 
					   tb2.body AS export_body, 
					   c2.name as export_cat_name, 
					   c.id as export_catalog_id
					   
				FROM product tb
				
				LEFT JOIN ru_product tb2
				ON tb.id=tb2.product_id
				
				LEFT JOIN product_catalog pc
				ON tb.id=pc.product_id
				
				LEFT JOIN catalog c
				ON c.id=pc.catalog_id
				
				LEFT JOIN ru_catalog c2
				ON c.id=c2.catalog_id
				
				LEFT JOIN (SELECT * FROM `price` ORDER BY `sort` ASC, id DESC) as `tb_price`
				ON `tb_price`.product_id=tb.id
				
				GROUP BY tb.id
				ORDER BY tb.sort ASC, tb.id DESC";		
		$res = $this->db->rows($query);//var_info($res);
		if(count($res)!=0)
		{
			$file=$path."products.csv";
			if(!file_exists($file))
			{
				$fp = fopen($file, "w");
				fwrite($fp, "");
				fclose($fp);
			}
			
			$arr=$this->abc();
			$fd = fopen($file, "w");
			$order =$arr['A2'].";".
					$arr['B2'].";".
					$arr['C2'].";".
					$arr['D2'].";".
					$arr['E2'].";".
					$arr['F2'].";".
					$arr['G2'].";".
					$arr['H2'].";".
					$arr['I2'].";".
					$arr['J2'].";".
					$arr['K2'].";".
					$arr['L2'].";\r\n";
			fwrite($fd, iconv('UTF-8', 'WINDOWS-1251', $order));
			
				
			if($_POST['export_id'])
			foreach($res as $row)
			{
				$row['export_name'] = $this->parse2($row['export_name']);
				$row['export_cat_name'] = $this->parse2($row['export_cat_name']);
				$row['export_body_m'] = $this->parse2($row['export_body_m']);
				$row['export_body'] = $this->parse2($row['export_body']);
				$row['export_code'] = $this->parse2($row['export_code']);
				
				
				if(!isset($row[$arr['A']]))$row[$arr['A']]='';
				if(!isset($row[$arr['B']]))$row[$arr['B']]='';
				if(!isset($row[$arr['C']]))$row[$arr['C']]='';
				if(!isset($row[$arr['D']]))$row[$arr['D']]='';
				if(!isset($row[$arr['E']]))$row[$arr['E']]='';
				if(!isset($row[$arr['F']]))$row[$arr['F']]='';
				if(!isset($row[$arr['G']]))$row[$arr['G']]='';
				if(!isset($row[$arr['H']]))$row[$arr['H']]='';
				if(!isset($row[$arr['I']]))$row[$arr['I']]='';
				if(!isset($row[$arr['J']]))$row[$arr['J']]='';
				if(!isset($row[$arr['K']]))$row[$arr['K']]='';
				if(!isset($row[$arr['L']]))$row[$arr['L']]='';
				
				$dir=Dir::createDir($row['export_id']);
				if(file_exists($dir[0]."{$row['export_id']}.jpg"))$row['export_image']='http://'.$_SERVER['HTTP_HOST'].'/'.$dir[0]."{$row['export_id']}.jpg";
				else $row['export_image']='';
				
				$order =$row[$arr['A']].";".
						$row[$arr['B']].";".
						$row[$arr['C']].";".
						$row[$arr['D']].";".
						$row[$arr['E']].";".
						$row[$arr['F']].";".
						$row[$arr['G']].";".
						$row[$arr['H']].";".
						$row[$arr['I']].";".
						$row[$arr['J']].";".
						$row[$arr['K']].";".
						$row[$arr['L']].";\r\n";
				fwrite($fd, $order);
			}
			fclose($fd);

			header ("Content-Type: application/octet-stream");
			header ("Accept-Ranges: bytes");
			header ("Content-Length: ".filesize($file));
			header ("Content-Disposition: attachment; filename=products.csv");  
			readfile($file);
		}
	}
	
	function parse2($str)
	{
		/*array('&','&#038;'),
			array('"','&#034;'),
			array("'",'&#039;'),
			array('%','&#037;'),
			array('(','&#040;'),
			array(')','&#041;'),
			array('+','&#043;'),
			array('<','&lt;'),
			array('>','&gt;');*/

		$replace=array(
			"&#038;"=>"&",
			"&#034;"=>'"',
			"&#039;"=>"'",
			"&#037;"=>"%",
			"&#040;"=>"(",
			"&#041;"=>")",
			"&#043;"=>"+",
			"&lt;"=>"<",
			"&gt;"=>">",
			"&nbsp;"=>" ",
			";"=>""
		);
		
		$str = iconv('UTF-8', 'WINDOWS-1251', htmlspecialchars_decode($str));
		$str = strtr($str,$replace);
		//$str = strip_tags($str);
		$str = str_replace(';', '', $str);
		$str = str_replace('\r\n', '', $str);
		$str = str_replace('\n', '', $str);
		$str = str_replace('\r', '', $str);
		$str = str_replace('
', '', $str);
		
		return $str;
	}
	
	function abc()
	{
		$arr = array('export_id', 'export_name', 'export_body_m', 'export_body', 'export_code', 'export_image', 'export_price', 'export_stock', 'export_catalog_id', 'export_cat_name');
		$arr2 = array();
		foreach($arr as $row)
		{
			if(isset($_POST[$row]))
			{
				$arr[''.$_POST[$row]]=$row;
				
				if($row=='export_id')$arr[$_POST[$row].'2']='id товара';
				elseif($row=='export_name')$arr[$_POST[$row].'2']='Название';
				elseif($row=='export_body_m')$arr[$_POST[$row].'2']='Краткое описание';
				elseif($row=='export_body')$arr[$_POST[$row].'2']='Описание';
				elseif($row=='export_code')$arr[$_POST[$row].'2']='Артикул';
				elseif($row=='export_image')$arr[$_POST[$row].'2']='Изображение';
				elseif($row=='export_price')$arr[$_POST[$row].'2']='Цена';
				elseif($row=='export_stock')$arr[$_POST[$row].'2']='В наличии';
				elseif($row=='export_catalog_id')$arr[$_POST[$row].'2']='ID категории';
				elseif($row=='export_cat_name')$arr[$_POST[$row].'2']='Название категории';
			}
		}
		if(!isset($arr['A']))$arr['A']='';
		if(!isset($arr['B']))$arr['B']='';
		if(!isset($arr['C']))$arr['C']='';
		if(!isset($arr['D']))$arr['D']='';
		if(!isset($arr['E']))$arr['E']='';
		if(!isset($arr['F']))$arr['F']='';
		if(!isset($arr['G']))$arr['G']='';
		if(!isset($arr['H']))$arr['H']='';
		if(!isset($arr['I']))$arr['I']='';
		if(!isset($arr['J']))$arr['J']='';
		if(!isset($arr['K']))$arr['K']='';
		if(!isset($arr['L']))$arr['L']='';
		
		if(!isset($arr['A2']))$arr['A2']='';
		if(!isset($arr['B2']))$arr['B2']='';
		if(!isset($arr['C2']))$arr['C2']='';
		if(!isset($arr['D2']))$arr['D2']='';
		if(!isset($arr['E2']))$arr['E2']='';
		if(!isset($arr['F2']))$arr['F2']='';
		if(!isset($arr['G2']))$arr['G2']='';
		if(!isset($arr['H2']))$arr['H2']='';
		if(!isset($arr['I2']))$arr['I2']='';
		if(!isset($arr['J2']))$arr['J2']='';
		if(!isset($arr['K2']))$arr['K2']='';
		if(!isset($arr['L2']))$arr['L2']='';
		
		return $arr;
	}
	
	function import_products($data=array())
	{
		$date=date("Y-m-d H:i:s");
		$cols=$this->select_cols();//echo $cols['select'];return false;
		$res = $this->db->rows("SELECT ".$cols['select']." FROM `tmp_products`");
		$y=0;	
		if($_POST['id']=='')$_POST['id']='id';
		if($_POST['catalog_id']=='')$_POST['catalog_id']='catalog_id';
		
		$parser = new Parser();
		$parser->parse_recursive_tree($res);
		foreach($res as $row)
		{
			//echo $row['col0'];
			$where="";
			$where2="";
			
			///////////////Brends
			if(isset($_POST['brend_name'])&&$_POST['brend_name']!='')
			{
				if(isset($_POST['brend_id'])&&$_POST['brend_id']!='')
					$brend=$this->db->row("SELECT id FROM brend WHERE id=?", array($row[$_POST['brend_id']]));
				else
					$brend=$this->db->row("SELECT brend_id as id FROM ru_brend WHERE name=?", array($row[$_POST['brend_name']]));
					
				if(!isset($brend['id']))
				{
					$url=translit($row[$_POST['brend_name']]);
					$www="";
					if(isset($row[$_POST['brend_id']]))$www="id='".$row[$_POST['brend_id']]."', ";
					$insert_id=$this->db->insert_id("INSERT INTO brend SET $www url='$url'");
					foreach($this->language as $row2)
					{
						$this->db->query("INSERT INTO ".$row2['language']."_brend SET brend_id='$insert_id', name='".$row[$_POST['brend_name']]."'");
					}
					$row[$_POST['brend_id']] = $insert_id;
				}
				else{
					$row[$_POST['brend_id']] = $brend['id'];
					foreach($this->language as $row2)
					{
						$this->db->query("UPDATE ".$row2['language']."_brend SET name='".$row[$_POST['brend_name']]."' WHERE brend_id='{$brend['id']}'");
					}	
				}
				$where.="brend_id='".$row[$_POST['brend_id']]."',";
			}

			///////////////Products
			if(isset($update))unset($update);
			
			if(isset($_POST['id'])&&$_POST['id']!=''&&$_POST['id']!='id')
			{
				$product=$this->db->row("SELECT id FROM product WHERE id=?", array($row[$_POST['id']]));
				if(!isset($product['id']))
				{
					$where.="id='".$row[$_POST['id']]."',";
				}
				else $update=$product['id'];				
			}
			if(isset($_POST['name'])&&$_POST['name']!='')$where2.="name='".$row[$_POST['name']]."',";
			if(isset($_POST['body'])&&$_POST['body']!='')
			{
				$row[$_POST['body']]=str_replace('<br />','&gt;',$row[$_POST['body']]);
				$where2.="body='".$row[$_POST['body']]."',";
			}
			if(isset($_POST['body_m'])&&$_POST['body_m']!='')$where2.="body_m='".$row[$_POST['body_m']]."',";
			
			if(isset($_POST['code'])&&$_POST['code']!='')$where.="code='".$row[$_POST['code']]."',";
			if(isset($_POST['price'])&&$_POST['price']!='')$where.="price='".$row[$_POST['price']]."',";
			if(isset($_POST['stock'])&&$_POST['stock']!='')$where.="stock='".$row[$_POST['stock']]."',";
	
	
			if($where!='')$where=', '.substr($where, 0, strlen($where)-1);
				
			#Insert new data product#
			if(!isset($update)&&$where2!='')
			{
				$where2=', '.substr($where2, 0, strlen($where2)-1);
				if(isset($_POST['code'],$row[$_POST['code']]))
				{
					$product=$this->db->row("SELECT id FROM product WHERE code=?", array($row[$_POST['code']]));	
					if(isset($product['id']))$update=$product['id'];
				}
				
				$q="INSERT INTO product SET date_add='$date' $where";//echo $q;
				$insert_id=$this->db->insert_id($q);
				
				if(isset($_POST['name'])&&$_POST['name']!='')
				{
					$where.=",url='".$insert_id.'-'.$row[$_POST['name']]."'";
					$this->db->query("UPDATE product SET date_edit='$date' $where WHERE id='$insert_id'");
				}
				
				$_POST['id']='';
				$row[$_POST['id']]=$insert_id;
				if($insert_id!='')
				{
					if(isset($_POST['price'])&&$_POST['price']!='')
					{
						$www="";
						if(isset($row[$_POST['code']]))$www="code='{$row[$_POST['code']]}', ";
						$this->db->query("INSERT INTO `price` SET $www `product_id`='$insert_id', `price`='".$row[$_POST['price']]."', price_type_id='1'");
					}
					
					foreach($this->language as $row2)
					{
						$q="INSERT INTO ".$row2['language']."_product SET product_id='$insert_id' $where2";//echo $q.'<br /><br />';
						$this->db->query($q);
					}
				}
			}
			
			#Update data product#
			elseif(isset($update))
			{
				if($where2!='')$where2=substr($where2, 0, strlen($where2)-1);
				if(!isset($row[$_POST['id']]))$row[$_POST['id']]=$update;
				
				if(isset($_POST['price'])&&$_POST['price']!='')
				{
					$row_price=$this->db->row("SELECT id FROM `price` WHERE `product_id`='$update' ORDER BY `sort` ASC, id DESC");
					
					$www="";
					if(isset($row[$_POST['code']]))$www="code='{$row[$_POST['code']]}', ";
					$this->db->query("UPDATE `price` SET $www `price`='".$row[$_POST['price']]."' WHERE id='{$row_price['id']}'");
				}
				$q="UPDATE product SET date_edit='$date' $where WHERE id='$update'";
				$this->db->query($q);
				
				if($where2!='')
				{
					foreach($this->language as $row2)
					{
						$q="UPDATE ".$row2['language']."_product SET $where2 WHERE product_id='$update'";//echo $q;
						$this->db->query($q);
					}	
				}
			}
			
			
			
			//////Upload image
			if(isset($_POST['image'],$row[$_POST['id']],$data['width_image'],$data['height_image'])&&$_POST['image']!=''&&$row[$_POST['image']]!='')
			{
				$dir=Dir::createDir($row[$_POST['id']]);
				$ext=end(explode('.', $row[$_POST['image']]));
				$file_tmp="files/tmp/product/tmp_image.".$ext;
				Images::grab_image($row[$_POST['image']], $file_tmp);
				//echo $dir['0'].$row[$_POST['id']].".jpg";
				//resizeImage($file_tmp, $dir['0'].$row[$_POST['id']].".jpg", $dir['0'].$row[$_POST['id']]."_s.jpg", 139, 139);
				Images::resizeImage($file_tmp, $dir['0'].$row[$_POST['id']].".jpg", $dir['0'].$row[$_POST['id']]."_s.jpg", $data['width_image'], $data['height_image']);	
				$q="UPDATE product SET photo='".$dir['0'].$row[$_POST['id']]."_s.jpg' WHERE id='{$row[$_POST['id']]}'";//echo $q;
				$this->db->query($q);
			}
				
				
			
			
			///////////////Catalog
			if(isset($_POST['cat_name'])&&$_POST['cat_name']!='')
			{
				if(isset($_POST['catalog_id'])&&$_POST['catalog_id']!=''&&$_POST['catalog_id']!='catalog_id')
					$cat=$this->db->row("SELECT id FROM catalog WHERE id=?", array($row[$_POST['catalog_id']]));
				else
					$cat=$this->db->row("SELECT catalog_id as id FROM ru_catalog WHERE name=?", array($row[$_POST['cat_name']]));
					
				if(!isset($cat['id']))
				{
					$insert_id=$this->db->insert_id("INSERT INTO catalog SET date_edit='$date', id='".$row[$_POST['catalog_id']]."'");
					foreach($this->language as $row2)
					{
						$this->db->query("INSERT INTO ".$row2['language']."_catalog SET catalog_id='$insert_id', name='".$row[$_POST['cat_name']]."'");
					}
					$url=$insert_id.'-'.translit($row[$_POST['cat_name']]);
					$this->db->query("UPDATE catalog SET url='$url' WHERE id='$insert_id'");
					$row[$_POST['catalog_id']] = $insert_id;
				}
				else{
					$row[$_POST['catalog_id']] = $cat['id'];
					foreach($this->language as $row2)
					{
						$this->db->query("UPDATE ".$row2['language']."_catalog SET name='".$row[$_POST['cat_name']]."' WHERE catalog_id='{$cat['id']}'");
					}	
				}
				if(isset($row[$_POST['id']])&&$row[$_POST['id']]!=''&&$row[$_POST['catalog_id']]!='')
				{
					
					$param=array($row[$_POST['catalog_id']], $row[$_POST['id']]);
					$cat=$this->db->row("SELECT catalog_id FROM product_catalog WHERE catalog_id=? AND product_id=?", $param);	
					if(!isset($cat['catalog_id']))
					{
						$this->db->query("INSERT INTO product_catalog SET catalog_id=?, product_id=?", $param);
					}
				}
				//$where.="catalog_id='".$row[$_POST['catalog_id']]."',";
			}
			$y++;
			//if($y==2)break;
		}
	}
	
	function select_cols()
	{
		$return=array();
		$return['select']='';
		$fields=array('id', 'name', 'body', 'body_m', 'code', 'image', 'price', 'stock', 'catalog_id', 'cat_name', 'brend_id', 'brend_name');
		foreach($fields as $row)
		{
			if(isset($_POST[$row])&&$_POST[$row]!='')$return['select'].=$_POST[$row].',';	
		}
		//echo $return['select'];
		if($return['select']!='')$return['select']=substr($return['select'], 0, strlen($return['select'])-1);
		//exit();
		return $return;
	}
	
	function set_import($file, $path_to_img='/home/yuma/www/incoming/1c_change/Pictures/')
	{
		$xlsx = new SimpleXLSX($file);
		$result = $xlsx->rows();
		$count_col=count($result[0])-1;
		if($count_col!=0)
		{
			$date = date("Y-m-d H:i:s");
			$this->create_tmp_table($count_col);
			$y=0;
			
			$parser = new Parser();
			$parser->parse_recursive_tree($result);
			foreach($result as $row)
			{
				if($y>0)
				{
					$cols="";
					for($i=0;$i<=$count_col;$i++)
					{
						$cols.="`col".$i."`='".$row[$i]."'";
						if($count_col!=$i)$cols.=",";	
					}
					$this->db->query("INSERT INTO `tmp_products` SET ".$cols);
					//var_info($row);break;
				}
				else $cols_name=$row;
				$y++;
			}
			return $cols_name;
			//var_info($cols_name);
		}
	}
	
	function create_tmp_table($count_col)
	{
		$this->db->query("DROP TABLE IF EXISTS `tmp_products`");
		
		$cols="";
		for($i=0;$i<=$count_col;$i++)
		{
			$cols.="`col".$i."` text DEFAULT NULL,";	
		}
		$this->db->query("CREATE TABLE `tmp_products` (
							  `id` int(11) NOT NULL AUTO_INCREMENT,
							  ".$cols."
							  PRIMARY KEY (`id`)
							) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");
	}
	
	function load_img($id, $path)
	{
		$path777="/home/cycom/public_html/";
		$id = substr($id, 1, strlen($id)-1);
		for($i=1;;$i++)
		{
			$path2=$path.$id.'_'.$i.'.JPEG';//echo $path2.'<br />';
			if(file_exists($path2))	
			{
				if($i==1)
				{
					$dir = createDir($id, $path777);
					//resizeImage($path2, $dir['0'].$id.".jpg", $dir['0'].$id."_s.jpg", 160, 160);
					Images::resizeImage($path2, $dir[0].$id.".jpg", $dir[0].$id."_s.jpg", 160, 160);	
				}
				else{
					$name = $id.'_'.$i;
					$insert_id = $this->db->insert_id("INSERT INTO product_photo SET product_id=?, active=?", array($id, 1));
					foreach($this->language as $lang)
					{
						$tb=$lang['language']."_product_photo";
						$param = array($name, $insert_id);
						$this->db->query("INSERT INTO `$tb` SET `name`=?, `photo_id`=?", $param);
					}
					$dir = createDir($id, $path777);
					//resizeImage($path2, $dir[1].$insert_id.".jpg", $dir[1].$insert_id."_s.jpg", 160, 160);
					Images::resizeImage($path2, $dir[1].$insert_id.".jpg", $dir[1].$insert_id."_s.jpg", 160, 160);	
				}
				unlink($path2);
			}
			else break;
		}
	}
}
?>