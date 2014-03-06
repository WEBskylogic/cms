<?
class Language extends Model
{
    static $table='language';
    static $name='Языки';
	
	public function __construct($registry)
    {
        parent::getInstance($registry);
    }

	public static function getObject($registry)
	{
		return new self::$table($registry);
	}

    public function add()
    {
        $message='';
        if(isset($_POST['comment'],$_POST['language'])&&$_POST['comment']!=''&&$_POST['language']!='0')
        {
            try
            {
                $this->db->beginTransaction();
                $rollBack='';

                $param = array($_POST['comment'],$_POST['language'],'domen'.$_POST['language']);
                $insert_id=$this->db->insert_id("INSERT INTO `".$this->table."` SET `comment`=?,`language`=?,`domen`=?", $param);
                if(!$insert_id)$rollBack.='ok';

                $language_default=$this->db->cell("Select `language`  FROM `".$this->table."` WHERE `default`=?", array(1));

                $Tables=$this->db->rows('show tables');
                $mass =array();
                sort($Tables);


                $db_name  =$this->registry['db_settings']["name"];

                $array=$Tables;
                $filtered_array = array_filter($array,
                    function ($element ) use ($language_default)
                    {

                        $mm =explode('_',$element[key($element)]);
                        return ($mm[0] == $language_default);
                    }
                );

                foreach($filtered_array as $ky => $val)
                {
                    $nTablDef = $val["Tables_in_{$db_name}"]; // имя текущщей таблицы
                    $nTablNew = str_replace($language_default."_",$_POST['language'].'_',$val["Tables_in_{$db_name}"]); // имя новой таблицы
                    $CreatTablDef = $this->db->row("SHOW CREATE TABLE `{$nTablDef}` " ); // структура текк. таблицы

                    $patterns = array();
                    $patterns[] = '/'.$nTablDef.'/';
                    $patterns[] = '/CONSTRAINT(.*)FOREIGN KEY/Uis';

                    $replacements = array();
                    $replacements[] = ''.$nTablNew.'';
                    $replacements[] = 'CONSTRAINT `FK_'.$nTablNew.time().'`FOREIGN KEY ';

                    $CreatTabl_new=preg_replace($patterns, $replacements, $CreatTablDef["Create Table"]); // структура Новой таблицы
                    $query=$this->db->query($CreatTabl_new); if(!$query) $rollBack.='ok';

                    $COLUMNS=$this->db->rows("SHOW  COLUMNS FROM `$nTablDef` ");
                    $COLUMNS=Arr::arrayKeys($COLUMNS,'Field') ;
                    $SqlINSERT="INSERT INTO `$nTablNew` (`".implode('`,`',array_keys($COLUMNS) )."`)
									SELECT `".implode('`,`',array_keys($COLUMNS) )."` FROM `$nTablDef`;";
                    $query=$this->db->query($SqlINSERT);   if(!$query) $rollBack.='ok';

                }

                if($rollBack == 'ok')
                {
                    $this->db->rollBack(); // отмена всех add
                    $message.= messageAdmin('При добавление произошли ошибки', 'error');
                }
                else
                {
                    $this->db->commit();   // save Transaction
                    $message.= messageAdmin('Данные успешно добавлены');
                }
            }
            catch(PDOException $e)
            {
                $this->db->rollBack();// отмена всех add
                $message.= messageAdmin('При добавление произошли ошибки!', 'error');
            }
        }
        else $message.= messageAdmin('При добавление произошли ошибки', 'error');
        return $message;
    }

    public function save()
    {
        $message='';
        if(isset($this->registry['access']))$message = $this->registry['access'];
        else{
            if(isset($_POST['save_id'])&&is_array($_POST['save_id']))
            {
                if(isset($_POST['save_id'], $_POST['comment']))
                {
                    for($i=0; $i<=count($_POST['save_id']) - 1; $i++)
                    {
                        $def= (isset($_POST['default'][0]) and $_POST['default'][0]== $_POST['save_id'][$i])?1:0;
                        $param = array($_POST['comment'][$i], $def, $_POST['save_id'][$i]);
                        $this->db->query("UPDATE `".$this->table."` SET `comment`=?, `default`=? WHERE id=?", $param);
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
        $id = $this->params['delete'];

        $default=$this->db->cell("Select `default`  FROM `".$this->table."` WHERE `id`=?", array($id)) ;
        if($default==1)$message .= messageAdmin('При удалении произошли ошибки<br /> Нельзя удалить основной язык!', 'error');

        if(isset($this->registry['access']))
            $message = $this->registry['access'];
        elseif($default<>1)
        {
            if(isset($this->params['delete']) && $this->params['delete']>0)
            {
                $key=$this->db->cell("Select `language`  FROM `".$this->table."` WHERE `id`=?", array($id)) ;

                $db_name = $this->registry['db_settings']["name"];

                $tables=$this->db->rows('show tables');
                $mass =array();
                sort($tables);

                $my_value = $key;
                $array=$tables;
                $filtered_array = array_filter($array, function ($element) use ($my_value)
                { $mm =explode('_',$element[key($element)]);
                    return ($mm[0] == $my_value);
                });


                foreach($filtered_array as $ky=>$val)
                {
                    $this->db->query("DROP TABLE `".$val["Tables_in_{$db_name}"]."`  " );
                }
                if($this->db->query("DELETE FROM `".$this->table."` WHERE `id`=?", array($id)))$message = messageAdmin('Запись успешно удалена');
            }
        }
        return $message;
    }

}
?>