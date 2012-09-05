<div id="aside" class="box">
<p id="btn-create" class="box"><a href="index.php?go=pages&cat=<?=$_GET['cat']?>&add_page"><span>Создать страницу</span></a></p>
<div id="sidetreecontrol" style="margin-left:10px;;"><a href="?#">Свернуть все</a> | <a href="?#">Развернуть все</a></div>
    <ul id="tree" style="margin-left:5px;">
    <li><a href="index.php?go=pages">Корень</a></li>
    <? 
		$tb="pages";
		$query = "SELECT id, sub, name FROM $tb where sub='0' order by sort asc";
		$result = sql($query);
		while($row = mysql_fetch_assoc($result))
		{
			echo"<li><a href=\"index.php?go={$_GET['go']}&cat={$row['id']}\">{$row['name']}</a>";
			////////////////////////////////////////////////////
			$query2 = "SELECT id, sub, name FROM $tb where sub='{$row['id']}' order by sort asc";
			$result2 = sql($query2);
			if(mysql_num_rows($result2)!=0)
			{
				echo"<ul>";
				while($row2 = mysql_fetch_assoc($result2))
				{
					echo"<li><a href=\"index.php?go={$_GET['go']}&cat={$row2['id']}\">{$row2['name']}</a>";
					////////////////////////////////////////////////////
					$query3 = "SELECT id, sub, name FROM $tb where sub='{$row2['id']}' order by sort asc";
					$result3 = sql($query3);
					if(mysql_num_rows($result3)!=0)
					{
						echo"<ul>";
						while($row3 = mysql_fetch_assoc($result3))
						{
							echo"<li><a href=\"index.php?go={$_GET['go']}&cat={$row3['id']}\">{$row3['name']}</a>";
							////////////////////////////////////////////////////
							$query4 = "SELECT id, sub, name FROM $tb where sub='{$row3['id']}' order by sort asc";
							$result4 = sql($query4);
							if(mysql_num_rows($result4)!=0)
							{
								echo"<ul>";
								while($row4 = mysql_fetch_assoc($result4))
								{
									echo"<li><a href=\"index.php?go={$_GET['go']}&cat={$row4['id']}\">{$row4['name']}</a>";
								}	
								echo"</ul>";
							}
							////////////////////////////////////////////////////
						}	
						echo"</ul>";
					}
					////////////////////////////////////////////////////
					echo"</li>";
				}	
				echo"</ul>";
			}
			////////////////////////////////////////////////////
			echo"</li>";
		}	
     ?>
     </ul>
</div>
<hr class="noscreen" />
		<!-- Content (Right Column) -->
<div id="content" class="box">
<?
if(isset($_GET['add_page']))
{
	$query="insert into pages (sub, name, title, meta, text) values('{$_GET['cat']}', 'Новая страница', 'Новая страница', 'Новая страница', 'Новая страница')";	
	$result = sql($query);
	echo"<p class=\"msg done\">Страница добавлена!</p>";
}
if(isset($_POST['name_content']))
{
	if($_POST['url']=="")$url=translit($_POST['name_content']);
	else $url=$_POST['url'];
	$query="update pages set sub='{$_POST['sub']}', text='{$_POST['content_content']}', name='{$_POST['name_content']}', title='{$_POST['title']}', meta='{$_POST['keywords']}', description='{$_POST['description']}', url='$url'  where id ='{$_GET['edit']}'";	
	$result = sql($query);
	echo"<p class=\"msg done\">Информация изменена!</p>";
}

	$query="select * from pages where id ='{$_GET['edit']}'";	
	$result = sql($query);
	$row = mysql_fetch_assoc($result);			
?>
<div class="tabs box">
	<ul>
      <li><a href="#tab03"><span>Список страниц</span></a></li>
	  <li><a href="#tab02"><span>Редактирование</span></a></li>			
	</ul>
</div>
<div id="tab02">
<form method="POST" enctype="multipart/form-data">
<table class="news">
<tr>
   
    <tr>
    	<td>
        Название:
        </td>
        <td><input type="text" name="name_content" value="<?=$row['name'];?>" />
        </td>
    </tr>
    <tr>
    	<td>
        URL:
        </td>
        <td>
        <input type="text" name="url" value="<?=$row['url'];?>" size="95" />
        </td>
    </tr>
    <tr>
    	<td>
        Родительская страница:
        </td>
        <td>
        <select name="sub">
        	<option value='0'></option>
        <? 
			$query2 = "SELECT id, sub, name FROM $tb where sub='0' and id!='{$row['id']}' order by sort asc";
			$result2 = sql($query2);
			while($row2 = mysql_fetch_assoc($result2))
			{
				echo"<option value='{$row2['id']}'";if($row2['id']==$row['sub'])echo' selected';echo">{$row2['name']}</option>";
				////////////////////////////////////////////////////
				$query3 = "SELECT id, sub, name FROM $tb where sub='{$row2['id']}' and id!='{$row['id']}' order by sort asc";
				$result3 = sql($query3);
				if(mysql_num_rows($result2)!=0)
				{
					echo"<ul>";
					while($row3 = mysql_fetch_assoc($result3))
					{
						echo"<option value='{$row3['id']}'";if($row3['id']==$row['sub'])echo' selected';echo">{$row3['name']}</option>";
					}	
					echo"</ul>";
				}
				////////////////////////////////////////////////////
				echo"</li>";
			}	
		 ?>
         </select>
        </td>
    </tr>
    <tr>
    	<td>
        Описание:
        </td>
        <td>
       <textarea id='elm3' name='content_content' rows='25' cols='50'><?=$row['text'];?></textarea>
        </td>
    </tr>
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
       <textarea  name='keywords' rows='7' cols='60' ><?=$row['keywords'];?></textarea>
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
    	<td>
        
        </td>
        <td>
       <input type="image" src="./images/save.png">
        </td>
    </tr>
</table>    
</form>
</div>

<div id="tab03">
<?
		

	if(isset($_GET['delete']))
	{
		$query2 = "delete from pages where id ='{$_GET['delete']}'";
		$result2 = sql($query2);
		echo"<p class=\"msg done\">Страница удалена!</p>";
	}
	if(isset($_POST['updatename']))
	{
			$query="update pages set name='{$_POST['updatename']}', text='{$_POST['content']}', title='{$_POST['title']}', meta='{$_POST['meta']}' where id ='{$_GET['edit']}'";
			$result = sql($query);
			echo"<p class=\"msg done\">Информация изменина!</p>";	
	}
	if(isset($_GET['visible']))
	{
		$query2 = "update pages set cheked='{$_GET['visible']}' where id='{$_GET['id']}'";
		$result2 = sql($query2);
		echo"<p class=\"msg done\">Информация изменена!</p>";
	}
		$query = "SELECT * FROM pages where sub='{$_GET['cat']}' ORDER BY id  DESC";
		$result = sql($query);
		?>
       <table class="news2">
        <tr>
                <th>
                <b>Название</b>
                </th>
                <th>
              <b>  Ссылка</b>
                </th>
                <th>
              <b>Активация</b>
                </th>
                <th>
              <b>Редактировать</b>
                </th>
                <th>
              <b>Удалить</b>
                </th>
            </tr>
        <?
		while($row = mysql_fetch_assoc($result))
		{
			if($row['title']=="Новая страница")$name="<b>{$row['name']}</b>";
			else $name=$row['name'];
		?>
        	<tr>
            	<td>
                <?=$name?>
                </td>
                <td>
               <a href="/pages/<?=$row['url'];?>" target="_blank">/pages/<?=$row['url'];?></a>
                </td>
                <td>
                <?
                if($row['cheked']==1)
					echo "<a href='index.php?go={$_GET['go']}&cat={$_GET['cat']}&visible=0&id={$row['id']}'><img src='./images/trash_mini.png' /></a>";
				else echo"<a href='index.php?go={$_GET['go']}&cat={$_GET['cat']}&visible=1&id={$row['id']}'><img src='./images/tick.png' /></a>";
				?>
                </td>
                <td>
                <a href="index.php?go=<?=$action?>&edit=<?=$row['id'];?>#tab02"><img src="./images/edit.png" /></a>
                </td>
                <td>
                <a href="index.php?go=<?=$action?>&cat=<?=$_GET['cat']?>&delete=<?=$row['id'];?>" onclick="return confirm('Удалить?')"><img src="./images/del.png" /></a>
                </td>
            </tr>
        <?
		}
	?>
	</table>
</div> 