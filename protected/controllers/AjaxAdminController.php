<?php
/**
 * class to Ajax action
 * @author mvc
 */

class AjaxAdminController extends BaseController{
	
	function __construct ($registry, $params)
	{
		$this->registry = $registry;
		parent::__construct($registry, $params);
	}

	function indexAction()
	{
		
	}
	
	///On/off
	function activeAction()
	{
		if(isset($_POST['id'], $_POST['tb']))
		{
			if($_POST['tb']=='meta_data')$tb='meta';
			else $tb=$_POST['tb'];
			$data=array();
			$data['message'] = $this->checkAccess('edit', $tb);//echo $row['controller'];
			if($data['message']=='')
			{
				$_POST['id']=str_replace("active", "", $_POST['id']);
				$tb=$_POST['tb'];
				$row=$this->db->row("SELECT `active` FROM `$tb` WHERE `id`=?", array($_POST['id']));
				if($row['active']==1)
				{
					$this->db->query("UPDATE `$tb` SET `active`=? WHERE `id`=?", array(0, $_POST['id']));
					$data['active']='<div class="selected-status status-d"><a> Выкл. </a></div>';
				}
				else{
					$this->db->query("UPDATE `$tb` SET `active`=? WHERE `id`=?", array(1, $_POST['id']));
					$data['active']='<div class="selected-status status-a"><a> Вкл. </a></div>';
				}
				$data['message']=messageAdmin('Данные успешно сохранены');
			}
			echo json_encode($data);
		}
	}
	
	/////
	function sortAction()
	{
		if(isset($_POST['arr'], $_POST['tb']))
		{
			if($_POST['tb']=='module')$tb='modules';
			elseif($_POST['tb']=='info_blocks')$tb='info';
			else $tb=$_POST['tb'];
			$data=array();
			$data['message'] = $this->checkAccess('edit', $tb);//echo $data['access'];
			if($data['message']=='')
			{
				$tb=$_POST['tb'];
				$_POST['arr']=str_replace("sort", "", $_POST['arr']);
				preg_match_all("/=(\d+)/",$_POST['arr'],$a);//echo var_dump($a);
				foreach($a[1] as $pos=>$id)
				{
					$pos2=$pos+1;
					//echo"update {$_POST['tb']} set sort='$pos2' WHERE id='".$id."'";
					$this->db->query("update `$tb` set `sort`=? WHERE `id`=?", array($pos2, $id));
				}
				$data['message']=messageAdmin('Данные успешно сохранены');
			}
			echo json_encode($data);
		}
	}

    
	/////Product Gallery tpl
    function photoproductAction()
    {
		if(isset($_REQUEST['id']))
		{
			$res = $this->db->rows("SELECT * FROM `product_photo` tb
											  LEFT JOIN `".$this->key_lang."_product_photo` tb2
											  ON tb.id=tb2.photo_id
											  WHERE product_id=?
											  ORDER BY sort ASC",
			array($_REQUEST['id']));
			?>
			<tr class="noDrop">
				<th width="25"></th>
				<th width="20" class="center"><input type="checkbox" class="check_all2" title="Отметить/снять все" value="Y" name="check_all"></th>
				<th width="20">ID</th>
				<th width="50">Фото</th>
				<th>Название</th>
				<th width="100">Статус</th>
				<th width="80">&nbsp;</th>
				</tr>
				<?php
				$dir=createDir($_REQUEST['id']);
				foreach($res as $row)
				{
					if($row['active']==1)$active='<div class="selected-status status-a"><a> Вкл. </a></div>';
					else $active='<div class="selected-status status-d"><a> Выкл. </a></div>';
	
					echo'<tr id="sort'.$row['id'].'">
									<td class="move"></td>
									<td class="center"><input type="checkbox" class="check-item" value="'.$row['id'].'" name="photo_id[]" /></td>
									<td><span>'.$row['id'].'</span></td>
									<td><img src="/'.$dir['1'].$row['id'].'_s.jpg" alt="" width="50" /></td>
									<td><input type="text" value="'.$row['name'].'" name="photo_name[]" class="input-text"></td>
									<td><input type="hidden" value="'.$row['id'].'" name="save_photo_id[]" />
										<div class="select-popup-container active_status" id="active'.$row['id'].'">'.$active.' </div>
									</td>
									<td width="10%">
										<ul class="cm-tools-list tools-list">
											<li><a href="/admin/product/edit/'.$row['product_id'].'/delete/'.$row['id'].'" class="cm-confirm">Удалить</a></li>
										</ul>
									</td>
								</tr>';
				}
		}
    }
	
	
	/////Product Gallery multiload
	function uploadifyproductAction()
    {
		if(isset($_FILES['Filedata'], $_REQUEST['id']))
		{
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$name =str_replace(strchr($_FILES['Filedata']['name'], "."), "", $_FILES['Filedata']['name']);
			$insert_id=$this->db->insert_id("insert into product_photo set product_id=?, active=?", array($_REQUEST['id'], 1));
			foreach($this->language as $lang)
			{
				$tb=$lang['language']."_product_photo";
				$param = array($name, $insert_id);
				$this->db->query("INSERT INTO `$tb` SET `name`=?, `photo_id`=?", $param);
			}
			$dir=createDir($_REQUEST['id']);
			resizeImage($tempFile, $dir[1].$insert_id.".jpg", $dir[1].$insert_id."_s.jpg", 70, 47);
			
			switch ($_FILES['Filedata']['error'])
			{     
				case 0:
				 $msg = ""; // comment this out if you don't want a message to appear on success.
				 break;
				case 1:
				  $msg = "The file is bigger than this PHP installation allows";
				  break;
				case 2:
				  $msg = "The file is bigger than this form allows";
				  break;
				case 3:
				  $msg = "Only part of the file was uploaded";
				  break;
				case 4:
				 $msg = "No file was uploaded";
				  break;
				case 6:
				 $msg = "Missing a temporary folder";
				  break;
				case 7:
				 $msg = "Failed to write file to disk";
				 break;
				case 8:
				 $msg = "File upload stopped by extension";
				 break;
				default:
				$msg = "unknown error ".$_FILES['Filedata']['error'];
				break;
			}
			
			if ($msg)
				{ $stringData = "Error: ".$_FILES['Filedata']['error']." Error Info: ".$msg; }
			else
				{ $stringData = "1"; } // This is required for onComplete to fire on Mac OSX
			echo $stringData;
			//$targetFile =  str_replace('//','/',$targetPath) . $_FILES['Filedata']['name'];
			//echo str_replace($_SERVER['DOCUMENT_ROOT'],'',$targetFile);
		}
    }
	
	
	/////Gallery multiload
    function uploadifyAction()
    {
		if(isset($_FILES['Filedata'], $_REQUEST['id']))
		{
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$name =str_replace(strchr($_FILES['Filedata']['name'], "."), "", $_FILES['Filedata']['name']);
			$insert_id=$this->db->insert_id("insert into photo set photos_id=?, active=?", array($_REQUEST['id'], 1));
			foreach($this->language as $lang)
			{
				$tb=$lang['language']."_photo";
				$param = array($name, $insert_id);
				$this->db->query("INSERT INTO `$tb` SET `name`=?, `photo_id`=?", $param);
			}
			$dir="files/photos/{$_REQUEST['id']}/";
			if(!is_dir($dir))
			{
				mkdir($dir, 0755) ;
			}
			resizeImage($tempFile, $dir.$insert_id.".jpg", $dir.$insert_id."_s.jpg", 214, 145);
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$targetPath = $_SERVER['DOCUMENT_ROOT'] . $_REQUEST['folder'] . '/';
			$targetFile =  str_replace('//','/',$targetPath) . $_FILES['Filedata']['name'];
			echo str_replace($_SERVER['DOCUMENT_ROOT'],'',$targetFile);
		}
    }
	
	
	/////Gallery tpl
    function photosAction()
    {
		if(isset($_REQUEST['id']))
		{
			$res = $this->db->rows("SELECT * FROM `photo` tb
											  LEFT JOIN `".$this->key_lang."_photo` tb2
											  ON tb.id=tb2.photo_id
											  WHERE photos_id=?
											  ORDER BY sort ASC",
				array($_REQUEST['id']));
			?>
				<tr class="noDrop">
					<th width="25"></th>
					<th width="1%" class="center"><input type="checkbox" class="check_all2" title="Отметить/снять все" value="Y" name="check_all"></th>
					<th width="2%">ID</th>
					<th width="50">Фото</th>
					<th>Название</th>
					<th width="20%">Статус</th>
					<th width="15%">&nbsp;</th>
				</tr>
		<?php
			foreach($res as $row)
			{
				if($row['active']==1)$active='<div class="selected-status status-a"><a> Вкл. </a></div>';
				else $active='<div class="selected-status status-d"><a> Выкл. </a></div>';
	
				echo'<tr id="sort'.$row['id'].'">
									<td class="move"></td>
									<td class="center"><input type="checkbox" class="check-item" value="'.$row['id'].'" name="photo_id[]" /></td>
									<td><span>'.$row['id'].'</span></td>
									<td><img src="/files/photos/'.$row['photos_id'].'/'.$row['id'].'_s.jpg" alt="" width="50" /></td>
									<td><input type="text" value="'.$row['name'].'" name="photo_name[]" class="input-text"></td>
									<td><input type="hidden" value="'.$row['id'].'" name="save_photo_id[]" />
										<div class="select-popup-container active_status" id="active'.$row['id'].'">'.$active.' </div>
									</td>
									<td width="10%">
										<ul class="cm-tools-list tools-list">
											<li><a href="/admin/photos/edit/'.$row['photos_id'].'/delete/'.$row['id'].'" class="cm-confirm">Удалить</a></li>
										</ul>
									</td>
								</tr>';
			}
		}
    }
	
	
	////Include  modules
    function addModuleAction()
    {
		if($_POST['id'])
		{
			$dir=MODULES.$_POST['id']."/admin/data/info.txt";//echo $dir;
			if(file_exists($dir))
			{
				$lines = file($dir);
				$i=0;
				$data=array();
				foreach ($lines as $line_num => $line)
				{
					if($i==0)$data['name']=$line;
					elseif($i==1)$data['comment']=$line;
					else $data['tables']=$line;
					$i++;
				}
				return json_encode($data);
			}
		}
    }
	
	
	////Create small Photo
    function createphotoAction()
    {
		if(isset($_POST['path'], $_POST['width'], $_POST['height'], $_POST['src']))
		{
			$tmp_dir = "{$_POST['path']}";
			$targ_w = $_POST['width']; 
			$targ_h = $_POST['height'];
			$jpeg_quality = 100;
			
			$src = $_POST['src'];
			$usr = $_POST['usr'];
		
			
			$params = getimagesize($tmp_dir.$src);
			
			$imageType = image_type_to_mime_type($params[2]);
			switch($imageType)
			{
				case "image/gif":
				   $img_r=imagecreatefromgif($tmp_dir . $src); 
				   break;
				case "image/pjpeg":
				case "image/jpeg":
				case "image/jpg":
				   $img_r=imagecreatefromjpeg($tmp_dir . $src); 
				   break;
				case "image/png":
				case "image/x-png":
				   $img_r=imagecreatefrompng($tmp_dir . $src); 
				   break;
			}
			$dst_r = imagecreatetruecolor($targ_w, $targ_h);
			imagecopyresampled($dst_r, $img_r, 0, 0, $_POST['x'], $_POST['y'], $targ_w, $targ_h, $_POST['w'], $_POST['h']);
			$name = $usr;
			header('Content-type: image/jpeg');
			imagejpeg($dst_r, "{$_POST['path']}" . $name . "_s.jpg", $jpeg_quality);
			unlink("{$_POST['path']}" . $name . "_b.jpg");  
			echo json_encode($name . "_s.jpg");
		}
    }
	
	////Create small Photo
    function includephotoAction()
	{ 
		copy($_SERVER['DOCUMENT_ROOT']."/files/default.jpg", $_SERVER['DOCUMENT_ROOT'].'/files/product/defaul2t.jpg');
		$result = $this->handleUpload($_SESSION['tovar_write']);
	}
	
	
	function handleUpload($id_foto)
	{
		$pref = ""; //var_info($_SESSION['path']);
		$uploaddir = $_SESSION['path'];
	 
		$maxFileSize = 100 * 1024 * 1024;
		
		//var_dump($_FILES['qqfile']);
		//var_dump($_GET['qqfile']);
		
		if(isset($_GET['qqfile']))
		{
			$file = new UploadFileXhr();
		}
		elseif(isset($_FILES['qqfile']))
		{
			copy($_SERVER['DOCUMENT_ROOT']."/files/default.jpg", $_SERVER['DOCUMENT_ROOT'].'/files/product/defaul2t.jpg');
			$file = new UploadFileForm();
		} 
		else{
			return array(success=>false);
		}
	 
		$pathinfo = pathinfo($file->getName());
		$filename = $pathinfo['filename'];			
		$ext = $pathinfo['extension'];
		//var_dump($pathinfo);var_dump($ext);var_dump($filename);
				
		while(file_exists($pref . $uploaddir . $filename . '.' . $ext))
		{
			$local_data=date("Y_m_d_h_i_s");
			$filename .= rand(10, 99);
		}	
		$filename2 = $id_foto.'_b' . '.jpg';
		//$file->save($pref . $uploaddir . $filename . '.' . $ext);
		$file->save($pref . $uploaddir . $filename2, $pref . $uploaddir .$id_foto.'.jpg');
		
		//Copyright($pref . $uploaddir .$id_foto.'.jpg');
		//copy( $pref.$uploaddir.$filename2, $pref.$uploaddir.$filename2);
		return  array("success"=>true);
	}
	
	
	function orderProductAction()
    {
		if($_POST['id'])
		{
			$data=array();
			$data['content']='<option value="0">Выберите товар...</option>';
			$q="SELECT
                tb.*,
                tb2.name
				
             FROM product tb

				LEFT JOIN ".$this->key_lang."_product tb2
                ON tb2.product_id=tb.id

                LEFT JOIN product_catalog tb3
                ON tb3.product_id=tb.id

             WHERE tb.active='1' AND tb3.catalog_id=?
			 GROUP BY tb.id
             ORDER BY tb.`sort` ASC, tb.id DESC";//echo $q;
			$res = $this->db->rows($q, array($_POST['id']));
			if(count($res)!=0)
			{
				foreach($res as $row)
				{
					$data['content'].='<option value="'.$row['id'].'">'.$row['name'].'</option>';
				}
			}
			else $data['content']='<option value="0">Товаров нет...</option>';
			
			return json_encode($data);
		}
    }
	
	function orderProductViewAction()
    {
		if(isset($_POST['id'],$_POST['order_id']))
		{
			$row = $this->db->row("SELECT tb.*, tb2.name 
								   FROM product tb
								   
								   LEFT JOIN ".$this->key_lang."_product tb2
								   ON tb.id=tb2.product_id 
								   
								   WHERE tb.`id`='{$_POST['id']}'");
			
			$row2 = $this->db->row("SELECT id FROM orders_product WHERE orders_id=? AND product_id=?", array($_POST['order_id'], $_POST['id']));					   
			if(!$row2)$this->db->query("INSERT INTO orders_product SET orders_id=?, name=?, price=?, discount=?, amount=?, `sum`=?, `product_id`=?", array($_POST['order_id'], $row['name'], $row['price'], $row['discount'], 1, $row['price'], $_POST['id']));
			else $this->db->query("UPDATE orders_product SET amount=amount+1, `sum`=`sum`*amount WHERE id=?", array($row2['id']));
			$res = $this->db->rows("SELECT * FROM orders_product WHERE orders_id=?", array($_POST['order_id']));	
			$data=array();
			$data['content']='
				<tr>
					<th width="50">ID</th>
					<th>Товар</th>
					<th width="10%">Кол-во</th>
					<th width="10%">Скидка</th>
					<th width="10%">Цена</th>
					<th width="10%">Сумма</th>
					<th width="5%">&nbsp;</th>
				</tr>';
			foreach($res as $row)
			{
				$data['content'].='<tr>
						<td><span>'.$row['product_id'].'</span></td>
						<td><input type="text" name="name[]" value="'.$row['name'].'" size="40" /></td>
						<td><input type="text" name="amount[]" value="'.$row['amount'].'" size="3" style="text-align:center;" /></td>
						<td><input type="text" name="discount[]" value="'.$row['discount'].'" size="3" style="text-align:center;" /> %</td>
						<td><input type="text" name="price[]" value="'.$row['price'].'" size="10" /></td>
						<td>'.$row['sum'].'</td>
						<td width="10%">
							<input type="hidden" name="product_id[]" value="'.$row['id'].'" />
							<ul class="cm-tools-list tools-list">
								<li><a href="/admin/orders/edit/'.$_POST['order_id'].'/delete/'.$row['id'].'" class="cm-confirm">Удалить</a></li>
							</ul>
						</td>
					</tr>';
			}
			$total=0;
			$res = $this->db->rows("SELECT * FROM orders_product WHERE orders_id=?", array($_POST['order_id']));
			foreach($res as $row)
			{
				$sum=$row['price']*$row['amount'];
				$total+=$sum;
				
			}	
			$this->db->query("UPDATE `orders` SET `amount`=?, `sum`=? WHERE `id`=?", array(count($res)-1, $total, $_POST['order_id']));
			//$data['amount'] = count($res)-1;
			$data['total'] = 'Итого: '.$total;
			return json_encode($data);
		}
    }
	
	
	function addcommentAction()
    {
		$view = new View($this->registry);
		if(isset($_POST['name'],$_POST['message'],$_POST['id'],$_POST['sub']))
		{
			//$_POST['sub']=0;
			$name=$_POST['name'];
			$pos = strpos($name, "<a");
			if($pos === false&&$_POST['name']!=""&&$_POST['message']!="")
			{
				$row = $this->db->row("SELECT type, content_id FROM comments WHERE `id`=?", array($_POST['sub']));
				$date=date("Y-m-d H:i:s");
				$query = "INSERT INTO `comments` SET `sub`=?, `author`=?, `text`=?, `content_id`=?, `type`=?, `date`=?, `session_id`=?, `language`=?, active=?";
				$this->db->query($query, array($_POST['sub'], $_POST['name'], $_POST['message'], $row['content_id'], $row['type'], $date, session_id(), $this->key_lang, 1));
				$data['message'] = "<div class='message'>".$this->translation['comment_add']."!</div>";
			}
		
			$res = $this->db->rows("SELECT * FROM comments WHERE sub=? ORDER BY date DESC", array($_POST['sub']));	
			$data=array();
			$data['content']='
				<tr>
					<th width="50">ID</th>
					<th width="10%">Дата добавления</th>
					<th width="10%">Автор</th>
					<th>Комментарий</th>
					<th width="5%">&nbsp;</th>
				</tr>';
			foreach($res as $row)
			{
				$data['content'].='<tr>
						<td><span>'.$row['id'].'</span></td>
						<td>'.$view->date_view($row['date'], "dd/mm/YY, hh:ii").'</td>
						<td>'.$row['author'].'</td>
						<td>'.$row['text'].'</td>
						<td width="10%">
							<input type="hidden" name="product_id[]" value="'.$row['id'].'" />
							<ul class="cm-tools-list tools-list">
								<li><a href="/admin/comments/edit/'.$row['sub'].'/delanswer/'.$row['id'].'" class="cm-confirm">Удалить</a></li>
							</ul>
						</td>
					</tr>';
			}
			return json_encode($data);
		}
    }
}
?>