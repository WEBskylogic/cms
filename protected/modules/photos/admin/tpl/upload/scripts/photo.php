<?php
	include_once '../../../../config.php';
	include_once '../../../../library/registry.php';
	include_once '../../../../library/library.php';
	include_once '../../../../library/initializer.php';
	include_once '../../../../library/log.php';
	include_once '../../../../library/pdo.php';
	header("Content-Type: text/html; charset=utf-8");
	session_start();
	$settings = Registry::getParam('db_settings'); 	
	$user =$settings['user']; $pass =$settings['password'];    //var_info($this->language);
	unset($settings);

	$db = new PDOchild(NULL,$user,$pass);
	$res=$db->rows("SELECT * FROM product_photo WHERE product_id='".$_POST['id']."' order by `sort` asc");
?>
<tr class="noDrop">
              
              <th width="30"><input type="checkbox" name="set" onclick="setChecked(this)" id="checkall" /></th>
              <th width="5"></th>
	            <th>Фото</th>
				<th>Название</th>
                <th width="50">Удалить</th>
              </tr>
            	<?  foreach($res as $rows){?>
              <tr align="center" id="<?=$rows['id']?>">
                 
                 <td><input type="checkbox" name="del_photo[]" value="<?=$rows['id']?>" size="25" class="del_photo" /></td>
                 <td class="move"></td>
                 <td><? if(file_exists("../../../../files/product/{$rows['product_id']}/{$rows['id']}_s.jpg"))echo"<img src='/files/product/{$rows['product_id']}/{$rows['id']}_s.jpg'  />";?> </td>
                 <td><input type="text" name="title_photo[]" value="<?=$rows['name']?>" size="25" /></td>  
                <td align="center">
					<input name="id_photo[]" type="hidden" value="<?=$rows['id'];?>" />
                    <a href="/admin/product/write/<?=$_POST['id']?>/delphoto/<?=$rows['id'];?>"  onclick="return confirm('Удалить?')"><img src="/tpl/images/admin/delete_16.gif" /></a>
                </td>
              </tr>  
               <? }?>
<tr class="noDrop">
                  <th width="30" style="background-image:none; border:none; background-color:transparent;">
                  <input type="image" src="/tpl/images/admin/delete_16.gif" style="border:none;" name="delphotos" />
                  </th>
                  <th colspan="5" style="background-image:none; border:none; background-color:transparent;"></th>
                  </tr>