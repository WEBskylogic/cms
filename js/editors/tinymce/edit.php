<?
	if(isset($_POST['updatename']))
	{
		if($_POST['url']=="")$url=translit($_POST['updatename']);
		else $url=$_POST['url'];
		
		$query="update top_menu set name='{$_POST['updatename']}', text='{$_POST['content']}',  title='{$_POST['title']}', meta='{$_POST['meta']}', description='{$_POST['description']}', url='$url' where id='{$_GET['edit']}'";
		$result = sql($query);
		
		if(isset($_FILES['head']['tmp_name']))
		{
			include "../mod/imageresizer.php";
			resizeImage($_FILES['head']['tmp_name'], "", "../images/head/".$_GET['edit'].".jpg", 1000, 280);
			//copy($_FILES['head']['tmp_name'], "../images/head/{$_GET['edit']}.jpg");	
		}
	}
	
	$query = "SELECT * FROM top_menu where id='{$_GET['edit']}'";
	$result = sql($query);
	$row = mysql_fetch_assoc($result);
?>
<a href="index.php?go=menu">Назад</a><br />
<form method="POST" enctype="multipart/form-data" >
<table class="news">
	
    <tr>
    	<td>
        Title:
        </td>
        <td>
       <textarea name='title' rows='7' cols='60' ><?=$row['title'];?></textarea>
        </td>
    </tr>
    <tr>
    	<td>
        Keywords:
        </td>
        <td>
       <textarea  name='meta' rows='7' cols='60' ><?=$row['meta'];?></textarea>
        </td>
    </tr>
    <tr>
    	<td>
        Description:
        </td>
        <td>
       <textarea  name='description' rows='7' cols='60' ><?=$row['description'];?></textarea>
        </td>
    </tr>
    <tr>
    	<td height="30px" bgcolor="#000000">
       
        </td>
        <td>
       
        </td>
    </tr>
    <tr>
    	<td>
        Название:
        </td>
        <td>
        <input type="text" name="updatename" value="<?=$row['name'];?>" size="55" />
        </td>
    </tr>
    <tr>
    	<td>
        URL:
        </td>
        <td>
        <input type="text" name="url" value="<?=$row['url'];?>" size="55" />
        </td>
    </tr>
    <tr>
    	<td>
        Описание:
        </td>
        <td>
       <textarea id='elm2' name='content' rows='25' cols='40'><?=$row['text'];?></textarea>
        </td>
    </tr>
    <tr>
    	<td>
        
        </td>
        <td>
       <input type="image" src="./images/save.png">
        </td>
    </tr>
</table>    
</form>