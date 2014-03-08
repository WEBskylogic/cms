<?
class Modules extends Model
{
    static $table='modules'; //Главная талица
    static $name='Модули'; // primary key
	
	public function __construct($registry)
    {
        parent::getInstance($registry);
		$this->separator="@@@";
		$this->separator2="@@";
    }

    //для доступа к классу через статичекий метод
	public static function getObject($registry)
	{
		return new self::$table($registry);
	}
	
	
	function set_module_data($module)
	{
		//////Info module
		$row = $this->db->row("SELECT * FROM `modules` WHERE controller='$module'");
		if(!is_dir(MODULES.$module."/admin/data/"))mkdir(MODULES.$module."/admin/data/", 0755, true);
			
		$file=MODULES.$module."/admin/data/info.txt";
		if(!file_exists($file))
		{
			$fp = fopen($file, "w");
			fwrite($fp, "");
			fclose ($fp);
		}
		$fd = fopen($file, "w");
		$string ="";
		fwrite($fd, $string);
		
		$string = $row['sub']."\r\n".
				  $row['name']."\r\n".
				  $row['controller']."\r\n".
				  $row['url']."\r\n".
				  $row['tables']."\r\n".
				  $row['photo']."\r\n".
				  $row['comment']."\r\n".
				  $row['sort']."\r\n";
		fwrite($fd, $string);
		fclose($fd);
			
		
		////////////Translation
		$translate=$this->db->rows("SELECT *
									FROM translate tb
									
									LEFT JOIN ".$this->registry['key_lang_admin']."_translate tb2
									ON tb.id=tb2.translate_id
									
									WHERE modules_id='{$row['id']}'
									ORDER BY `key` ASC
									");
		if(count($translate)!=0)
		{
			$file=MODULES.$module."/admin/data/translate.txt";
			$fp = fopen($file, "w");
			fwrite($fp, "");
			fclose ($fp);
			$fd = fopen($file, "w");
			$string ="";
			fwrite($fd, $string);
			foreach($translate as $row2)
			{
					$string = $row2['key']."#@@#".
							  $row2['comment']."#@@#".
						      $row2['value']."\r\n";
			  fwrite($fd, $string);
			}
			fclose($fd);
		}
		
		
		
		////////////Configuration
		$config=$this->db->rows("SELECT *
									FROM config tb
									
									WHERE modules_id='{$row['id']}'
									ORDER BY `name` ASC
									");
		if(count($config)!=0)
		{
			$file=MODULES.$module."/admin/data/config.txt";
			$fp = fopen($file, "w");
			fwrite($fp, "");
			fclose ($fp);
			$fd = fopen($file, "w");
			$string ="";
			fwrite($fd, $string);
			foreach($config as $row2)
			{
					$string = $row2['name']."#@@#".
							  $row2['value']."#@@#".
							  $row2['type']."#@@#".
							  $row2['comment']."#@@#".
						      $row2['active']."\r\n";
			  fwrite($fd, $string);
			}
			fclose($fd);
		}
	}
	
	public function add()
    {
        $message='';
        if(isset($_POST['comment'], $_POST['sub'], $_POST['url'])&&$_POST['module']!=""&&$_POST['sub']!="")
        {
            ////Добавляем таблицы, если есть db.sql
            $dir=MODULES.$_POST['module']."/admin/data/db.sql";//echo $dir;
            $this->addModule($dir);

			$param = array($_POST['name'], $_POST['url'], $_POST['module'], $_POST['comment'], $_POST['tables'], $_POST['sub'], $_POST['sort']);
            $insert_id = $this->db->insert_id("INSERT INTO `".$this->table."` SET name=?, url=?, controller=?, comment=?, tables=?, sub=?, sort=?", $param);
            
			///Права доступа
            if(count($_POST['module_id'])!=0)
            {
                for($i=0; $i<=count($_POST['module_id']) - 1; $i++)
                {
                    $id = $_POST['module_id'][$i];
                    if(isset($_POST['read'.$id])&&isset($_POST['del'.$id])&&isset($_POST['add'.$id])&&isset($_POST['edit'.$id]))$chmod=800;
					elseif(isset($_POST['read'.$id])&&isset($_POST['del'.$id])&&isset($_POST['add'.$id])&&!isset($_POST['edit'.$id]))$chmod=700;
                    elseif(isset($_POST['read'.$id])&&!isset($_POST['del'.$id])&&isset($_POST['add'.$id])&&isset($_POST['edit'.$id]))$chmod=600;
                    elseif(isset($_POST['read'.$id])&&isset($_POST['del'.$id])&&!isset($_POST['add'.$id])&&isset($_POST['edit'.$id]))$chmod=500;
                    elseif(isset($_POST['read'.$id])&&!isset($_POST['del'.$id])&&isset($_POST['add'.$id])&&!isset($_POST['edit'.$id]))$chmod=400;
					elseif(isset($_POST['read'.$id])&&isset($_POST['del'.$id])&&!isset($_POST['add'.$id])&&!isset($_POST['edit'.$id]))$chmod=300;
					elseif(isset($_POST['read'.$id])&&!isset($_POST['del'.$id])&&!isset($_POST['add'.$id])&&isset($_POST['edit'.$id]))$chmod=200;
					elseif(isset($_POST['read'.$id])&&!isset($_POST['del'.$id])&&!isset($_POST['add'.$id])&&!isset($_POST['edit'.$id]))$chmod=100;
                    else $chmod="000";
					
                    //echo $chmod.'<br />';'000'-off; '100'-read; '200'-read/edit; '300'-read/del; '400'-read/add; '500'-read/edit/del; '600'-read/edit/add; '700'-read/del/add; '800'-read/edit/del/add;
                    $param = array($id, $insert_id);
                    $row = $this->db->row("SELECT moderators_type_id FROM `moderators_permission` WHERE moderators_type_id=? AND module_id=?", $param);

                    $param = array($chmod, $id, $insert_id);
                    if($row)$this->db->query("UPDATE `moderators_permission` SET `permission`=? WHERE moderators_type_id=? AND module_id=?", $param);
                    else $this->db->query("INSERT INTO `moderators_permission` SET `permission`=?, moderators_type_id=?, module_id=?", $param);
                }
				
				$param = array(800, 1, $insert_id);
				if($row)$this->db->query("UPDATE `moderators_permission` SET `permission`=? WHERE moderators_type_id=? AND module_id=?", $param);
				else $this->db->query("INSERT INTO `moderators_permission` SET `permission`=?, moderators_type_id=?, module_id=?", $param);
            }
			
			if(isset($_POST['subsystem_id'])&&count($_POST['subsystem_id'])!=0)
			{
				$chmod=800;
				$this->db->query("UPDATE `moderators_permission` SET `permission`='000' WHERE subsystem_id!='0'");
				$count=count($_POST['subsystem_id']) - 1;
				for($i=0; $i<=$count; $i++)
				{
					$id = explode('-', $_POST['subsystem_id'][$i]);
					$param = array($id[0], $insert_id, $id[1]);//echo $id[1].'=';
					$row = $this->db->row("SELECT moderators_type_id FROM `moderators_permission` WHERE moderators_type_id=? AND module_id=? AND subsystem_id=?", $param);

					$param = array($chmod, $id[0], $insert_id, $id[1]);
					if($row)$this->db->query("UPDATE `moderators_permission` SET `permission`=? WHERE moderators_type_id=? AND module_id=? AND subsystem_id=?", $param);
					else $this->db->query("INSERT INTO `moderators_permission` SET `permission`=?, moderators_type_id=?, module_id=?, subsystem_id=?", $param);
					/*
					$param = array(800, 1, $id[0], $id[1]);
					$row = $this->db->row("SELECT moderators_type_id FROM `moderators_permission` WHERE moderators_type_id=? AND module_id=? AND subsystem_id=?", array(1, $id[0], $id[1]));
					if(!$row)$this->db->query("INSERT INTO `moderators_permission` SET `permission`=?, moderators_type_id=?, module_id=?, subsystem_id=?", $param);*/
				}
			}
					
			if(isset($_POST['create_dir']))
			{
				$dir="files/{$_POST['module']}/";
				if(!is_dir($dir))
				{
					mkdir($dir, 0755) ;
				}	
			}
			
			if(isset($_POST['get_translate']))
			{
				$dir=MODULES.$_POST['module']."/admin/data/translate.txt";//echo $dir;
				if(file_exists($dir))
				{
					$lines = file($dir);
					$i=0;
					$data=array();
					foreach ($lines as $line_num=>$line)
					{
						$line = explode('#@@#', $line);

						$translate_id = $this->db->insert_id("INSERT INTO `translate` SET `modules_id`=?, `key`=?, `comment`=?", array($insert_id, $line[0], $line[2]));
						
						$language = $this->db->rows("SELECT * FROM `language`");
						foreach($language as $lang)
						{
							$tb=$lang['language']."_translate";
							$this->db->query("INSERT INTO `$tb` SET `value`=?, `translate_id`=?", array($line[1], $translate_id));
						}
					}
				}
			}
			
			if(isset($_POST['get_config']))
			{
				$dir=MODULES.$_POST['module']."/admin/data/config.txt";//echo $dir;
				if(file_exists($dir))
				{
					$lines = file($dir);
					$i=0;
					$data=array();
					foreach ($lines as $line_num=>$line)
					{
						$line = explode('#@@#', $line);

						$this->db->query("INSERT INTO `config` SET `modules_id`=?, `name`=?, `value`=?, `type`=?, `comment`=?, `active`=?", array($insert_id, $line[0], $line[1], $line[2], $line[3], $line[4]));
					}
				}
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
                if(isset($_POST['save_id'], $_POST['name']))
                {
                    for($i=0; $i<=count($_POST['save_id']) - 1; $i++)
                    {
                        $param = array($_POST['name'][$i], $_POST['save_id'][$i]);
                        $this->db->query("UPDATE `".$this->table."` SET `name`=? WHERE id=?", $param);
                    }
                    $message .= messageAdmin('Данные успешно сохранены');
                }
                else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
            }
            else{
                if(isset($_POST['name'], $_POST['module_id'], $_POST['sub'], $_POST['url'])&&$_POST['sub']!="")
                {
                    $param = array($_POST['name'], $_POST['url'], $_POST['comment'], $_POST['tables'], $_POST['sub'], $_POST['id']);
                    $this->db->query("UPDATE `".$this->table."` SET `name`=?, `url`=?, `comment`=?, `tables`=?, `sub`=? WHERE id=?", $param);

                    ///
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
                    if(count($_POST['module_id'])!=0)
                    {
                        for($i=0; $i<=count($_POST['module_id']) - 1; $i++)
                        {
                            $id = $_POST['module_id'][$i];
                            if(isset($_POST['read'.$id])&&isset($_POST['edit'.$id])&&isset($_POST['del'.$id])&&isset($_POST['add'.$id]))$chmod=800;
                            elseif(isset($_POST['read'.$id])&&!isset($_POST['edit'.$id])&&!isset($_POST['del'.$id])&&!isset($_POST['add'.$id]))$chmod=100;
                            elseif(isset($_POST['read'.$id])&&isset($_POST['edit'.$id])&&!isset($_POST['del'.$id])&&!isset($_POST['add'.$id]))$chmod=200;
                            elseif(isset($_POST['read'.$id])&&!isset($_POST['edit'.$id])&&isset($_POST['del'.$id])&&!isset($_POST['add'.$id]))$chmod=300;
							elseif(isset($_POST['read'.$id])&&!isset($_POST['edit'.$id])&&!isset($_POST['del'.$id])&&isset($_POST['add'.$id]))$chmod=400;
                            elseif(isset($_POST['read'.$id])&&isset($_POST['edit'.$id])&&isset($_POST['del'.$id])&&!isset($_POST['add'.$id]))$chmod=500;
                            elseif(isset($_POST['read'.$id])&&isset($_POST['edit'.$id])&&!isset($_POST['del'.$id])&&isset($_POST['add'.$id]))$chmod=600;
							elseif(isset($_POST['read'.$id])&&!isset($_POST['edit'.$id])&&isset($_POST['del'.$id])&&isset($_POST['add'.$id]))$chmod=700;
                            else $chmod="000";
                            //echo $chmod.'<br />';
                            $param = array($id, $_POST['id']);
                            $row = $this->db->row("SELECT moderators_type_id FROM `moderators_permission` WHERE moderators_type_id=? AND module_id=?", $param);

                            $param = array($chmod, $id, $_POST['id']);
                            if($row)$this->db->query("UPDATE `moderators_permission` SET `permission`=? WHERE moderators_type_id=? AND module_id=?", $param);
                            else $this->db->query("INSERT INTO `moderators_permission` SET `permission`=?, moderators_type_id=?, module_id=?", $param);
							
                        }
						$param = array(800, 1, $_POST['id']);
						if($row)$this->db->query("UPDATE `moderators_permission` SET `permission`=? WHERE moderators_type_id=? AND module_id=?", $param);
						else $this->db->query("INSERT INTO `moderators_permission` SET `permission`=?, moderators_type_id=?, module_id=?", $param);
                    }
					
					
					if(isset($_POST['subsystem_id'])&&count($_POST['subsystem_id'])!=0)
                    {
						$chmod=800;
						$this->db->query("UPDATE `moderators_permission` SET `permission`='000' WHERE subsystem_id!='0'");
						$count=count($_POST['subsystem_id']) - 1;
                        for($i=0; $i<=$count; $i++)
                        {
                            $id = explode('-', $_POST['subsystem_id'][$i]);
                            $param = array($id[0], $_POST['id'], $id[1]);//echo $id[1].'=';
                            $row = $this->db->row("SELECT moderators_type_id FROM `moderators_permission` WHERE moderators_type_id=? AND module_id=? AND subsystem_id=?", $param);

                            $param = array($chmod, $id[0], $_POST['id'], $id[1]);
                            if($row)$this->db->query("UPDATE `moderators_permission` SET `permission`=? WHERE moderators_type_id=? AND module_id=? AND subsystem_id=?", $param);
                            else $this->db->query("INSERT INTO `moderators_permission` SET `permission`=?, moderators_type_id=?, module_id=?, subsystem_id=?", $param);
							/*
							$param = array(800, 1, $id[0], $id[1]);
							$row = $this->db->row("SELECT moderators_type_id FROM `moderators_permission` WHERE moderators_type_id=? AND module_id=? AND subsystem_id=?", array(1, $id[0], $id[1]));
							if(!$row)$this->db->query("INSERT INTO `moderators_permission` SET `permission`=?, moderators_type_id=?, module_id=?, subsystem_id=?", $param);*/
                        }
                    }
					
                    $message .= messageAdmin('Данные успешно сохранены');
                }
                else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
            }
        }
        return $message;
    }

    public function delete()
    {
        $message='';
        if(isset($this->registry['access']))$message = $this->registry['access'];
        else
        {
            if(isset($_POST['id'])&&is_array($_POST['id']))
            {
                for($i=0; $i<=count($_POST['id']) - 1; $i++)
                {
                    $id = $_POST['id'][$i];
                    $this->delete_module($id);
                    $message = messageAdmin('Запись успешно удалена');
                }
            }
            elseif(isset($this->params['delete'])&& $this->params['delete']!='')
            {
                $id = $this->params['delete'];
                $this->delete_module($id);
            }
        }
        return $message;
    }
	
	function delete_module($id)
	{
		$row= $this->db->row("SELECT tables, controller FROM `".$this->table."` WHERE id=?", array($id));

		if($row&&$row['tables']!="")
		{
			$table=explode(',',$row['tables']);
			$cnt=count($table)-1;

			foreach(explode(",", $row['tables']) as $row2)
			{
				foreach($this->language as $lang)
				{
					$tb=$lang['language']."_".$row2;
					$this->db->query("DROP TABLE IF EXISTS `".$tb."`");
				}
				$this->db->query("DROP TABLE IF EXISTS `".$row2."`");
			}
		}
		$dir="files/{$row['controller']}/";
		if(is_dir($dir)&&$row['controller']!='')
		{
			Dir::removeDir($dir);
		}
		$this->db->query("DELETE FROM `config` WHERE `modules_id`=?", array($id));
		$this->db->query("DELETE FROM `translate` WHERE `modules_id`=?", array($id));
		$this->db->query("DELETE FROM `moderators_permission` WHERE `module_id`=?", array($id));
		if($this->db->query("DELETE FROM `".$this->table."` WHERE `id`=?", array($id)))$message = messageAdmin('Запись успешно удалена');
	}
	
	public function addModule($dir)
	{
		if(file_exists($dir))
		{
			$handle = fopen($dir, "r");
			$contents = fread($handle, filesize($dir));
			fclose($handle);//echo($contents);
			$part = explode($this->separator, $contents);
			//var_info($part[0]); 
			$this->db->query($part[0]);
			if(isset($part[1]))
			{			
				$cnt_tb_lang=explode('#@@#', $part[1]);
				for($i=0;$i<=count($cnt_tb_lang)-1;$i++)
				{
					preg_match('^@@(.*)\@@^', $cnt_tb_lang[$i], $match);
					$tb_lang=$match[1];
					foreach($this->language as $lang)
					{
						$tb=$lang['language']."_".$tb_lang;
						$q=str_replace('@@'.$match[1].'@@', $tb, $cnt_tb_lang[$i]);
						$this->db->query($q);
					}
				}
				/*if(isset($part[2]))
				{
					$cnt_tb_lang=explode('#@@#', $part[2]);
					for($i=0;$i<=count($cnt_tb_lang)-1;$i++)
					{
						preg_match('^@@(.*)\@@^', $cnt_tb_lang[$i], $match);
						$tb_lang=$match[1];var_info($match);
						foreach($this->language as $lang)
						{
							$tb=$lang['language']."_".$tb_lang;
							$q=str_replace('@@'.$match[1].'@@', $tb, $cnt_tb_lang[$i]);
							//var_info($q);
							$this->db->query($q);
						}
					}
					if(isset($part[3]))
					{
						$cnt_tb_lang=explode('#@@#', $part[3]);
						for($i=0;$i<=count($cnt_tb_lang)-1;$i++)
						{
							//var_info($cnt_tb_lang[$i]);	
							$this->db->query($q);
						}
						
					}
				}*/
			}
		}	
	}
}
?>