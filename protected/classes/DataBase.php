<?
class DataBase{
    public $sql='';
    public $params_db=array();
	
    /*
     * Блокируем __construct и __clone для того,
     * чтобы невозможно было создать новый объект через new.
     *
     * */
	 
    public function __construct()
    {
        return false;
    }

    private function __clone()
    {
        return false;
    }

    /*
     * Синглтон подключения к базе данных. Если объект уже создан,
     * то просто возвращается экземпляр объекта, если нет,
     * то создается новое подключение к базн данных.
     * Можно напрямую пользоваться классом PDOChild
     *
     * */
    public function getInstance($registry)
    {
		//var_info($registry);
		$this->registry = $registry['registry'];
		$this->settings = $registry['settings'];
		$this->params = $registry['params'];
		$this->db = $registry['db'];
		$model = str_replace('Controller', '', get_called_class());//echo $model;
		$this->table = $model::$table;
		$this->name = $model::$name;
		$this->language = $this->db->rows("SELECT * FROM language ORDER BY `id` ASC");//Languages
		$this->translation=$registry['translation'];
												
		$this->sets = array('settings'=>$this->settings, 'registry'=>$this->registry, 'params'=>$this->params, 'db'=>$this->db, 'translation'=>$this->translation);
		if(isset($model::$lang_table))$this->lang_table = $this->registry['key_lang'].$model::$lang_table;
    }

    /*
     * Генерируем SELECT sql
     * select- поля которые нужно выбрать в виде массива
     * where- условие выбора в виде массива array(поле=>array(знак=,значение))
     * limit-лимит записей в виде массива array(начальная запись, количество)
     * order- сортировка array (поле=>направление)
     * join- массив join
     * debug- если true то в свойство класса sql записывается текущий sql запрос и в свойство params записываются параметры
     *
     * */
    protected function select($sql)
    {
		if(isset($sql['table']))$this->table=$sql['table'];
		
		$join='';
		if($this->db->row("SHOW TABLES LIKE '".$this->registry['key_lang']."_".$this->table."'"))
		{
			if(isset($this->registry['admin'])&&$this->registry['admin']!='')$lang = $this->registry['key_lang_admin'];
			else $lang = $this->registry['key_lang'];
			
			$join="LEFT JOIN `".$lang."_".$this->table."` tb_lang ON tb.id=tb_lang.".$this->table."_id ";	
		}

		$sql=$this->checkSql($sql);
		$where=$this->checkWhere($sql['where']);
		
		
		//$join.=$this->checkJoin($sql['join']);
		$join.=$sql['join'];

		$query="SELECT ".$sql['select']." 
			    FROM `".$this->table."` `tb`  
			    ".$join." 
			    ".$where['query']." 
			    ".$sql['having']." 
			    ".$sql['group']." 
			    ".$sql['order']." 
			    ".$sql['limit'];
		//echo $query;
		//$params=$this->checkParams($select['params'], $where['params']);
		if ($sql['debug'])
		{
			$this->sql=$query;
			$this->params_db=$where['params'];
		}//echo $sql;

        if((isset($sql['type'])&&$sql['type']=='rows')||isset($sql['paging'])&&is_array($sql)){return $this->db->rows($query, $where['params']);}
        elseif(isset($sql['type'])&&$sql['type']=='rows_keys'){return $this->db->rows($query, $where['params']);}
		else return $this->db->row($query, $where['params']);
		
    }
	
	protected function query($sql, $insert_id=false)
    {
		if(isset($sql['table']))$this->table=$sql['table'];

		$where = $this->checkWhere($sql);
		
		//$join.=$this->checkJoin($sql['join']);
		$join=$sql['join'];

		$query=$where['query'];
		//echo $query;
		//$params=$this->checkParams($select['params'], $where['params']);
		if ($sql['debug'])
		{
			$this->sql=$query;
			$this->params_db=$where['params'];
		}
		//echo $sql;
		
		if($insert_id)return $this->db->insert_id($query, $where['params']);
		else return $this->db->query($query, $where['params']);
    }

    /*
     * Генерируем Insert sql
     * data- массив пар поле-значение для вставки
     * table- таблица куда вставляется значение
     * debug- если true то в свойство класса sql записывается текущий sql запрос и в свойство params записываются параметры
     *
     * */
    protected function prepareInsertSQL($data, $table, $debug=false)
    {
        $params=$values=$column=array();
        foreach ($data as $c=>$p)
        {
            $column[]=$c;
            $values[]="?";
            $params[]=$p;

        }

        $sql=" INSERT INTO `".$table."` (".implode(",",$column).") VALUES (".implode(',',$values).")";
        if($debug)
        {
            $this->sql=$sql;
            $this->params_db=$params;
        }
        return array('sql'=>$sql,'params'=>$params);
    }

    /*
     * Генерируем Delete sql
     * where- Услови для удаления
     * table- таблица из которой удаляются записи
     * debug- если true то в свойство класса sql записывается текущий sql запрос и в свойство params записываются параметры
     *
     * */
    protected function prepareDeleteSQL($table,$where,$debug=false)
    {
        $where=$this->checkWhere($where);
        $sql="DELETE FROM `".$table."` ".$where['column'];
        $params=$this->checkParams($select,$where['params']);
        if ($debug)
        {
            $this->sql=$sql;
            $this->params_db=$params;
        }
        return array('sql'=>$sql,'params'=>$params);
    }

    /*
     * Генерируем Update sql
     * table- таблица из которой удаляются записи
     * what - массив поле значение для обновления
     * where- Услови для обновления
     * debug- если true то в свойство класса sql записывается текущий sql запрос и в свойство params записываются параметры
     */

    protected function prepareUpdateSQL($table,$select,$where,$debug=false)
    {
        $select=$this->checkWhat($select);
        $where=$this->checkWhere($where);
        $sql="UPDATE `".$table."` SET ".$select['column']." ".$where['column'];
        $params=$this->checkParams($select['params'],$where['params']);
        if ($debug)
        {
            $this->sql=$sql;
            $this->params_db=$params;
        }
        return array('sql'=>$sql,'params'=>$params);

    }
	
	
	 /*
     * Добавляем join к запросу.
     * type - тип нужного join
     * tables - массив таблиц которые будут связываться
     * pseudoName - псевдонимы для таблиц
     * row - поля по которым производится связть
     *
     *
     * */
    protected  function addJoin($type=' INNER ',$tables,$pseudoName,$rows)
    {
        if ($type!=='' && is_array($tables) && is_array($rows))
        {
            $t0=$tables[0];
            $t1=$tables[1];
            if (is_array($pseudoName) && count($pseudoName)>0)
            {
                $t0=$pseudoName[0];
                $t1=$pseudoName[1];
            }
            return $type." JOIN `".$tables[1]."` `".$pseudoName[1]."` ON `".$t0."`.`".$rows[0]."`=`".$t1."`.`".$rows[1]."`";
        }
        else {
            return false;
        }
    }

	
    /*
     * Добавляем несколько join к запросу
     * join - массив массивов join array(join,join)
     * */
    protected function addJoinArray($join)
    {
        $res=array();
        if (is_array($join))
        {
            foreach ($join as $j)
            {
                $res[]=$this->addJoin($j[0],$j[1],$j[2],$j[3]);
            }
        }
        return $res;

    }
	
	
    /*
     * Проверяем наличие параметра join
     * Если он есть, то проверяем является ли он единственным, если да то addJoin
     * если нет, то addJoinArray
     * Если join нет, то ничего не возвращаем
     * */
    protected function checkJoin($join)
    {
        $res='';
        if (is_array($join) && count($join)>0)
        {
            if (!is_array($join[0]))
            {
                $res[]=$this->addJoin($join[0],$join[1],$join[2],$join[3]);
            }
            else {
                $res=$this->addJoinArray($join);
            }
			//echo implode(" ",$res);
            return implode(" ",$res);
        }
        else {
            return false;
        }
    }


    /*
     * Проверяем наличие параметров для prepare sql
     * Параметры состоят из массива параметров WHAT и массива параметров WHERE.
     * Это нужно для того, чтобы prepare sql
     * работал и с update, select, delete, insert
     * Объединяет два массива what и where
     * */
    protected function checkParams($select,$where)
    {
        if (!isset($select) || !is_array($select))
        {
            $params=$where;
        }
        else if (!isset($where) && !is_array($where))
        {
            $params=$select;
        }
        else {
            $params=array_merge($select,$where);
        }
        return $params;
    }
	
	
	protected function checkWhere($query)
	{
		if($query!='')
		{
			$sep = '__';
			$change = '=?'.$sep;
			$out = preg_replace('/\:.+'.$sep.'/Uis', $change, $query); // перебиваем все на знаки равно и вопроса
				 //preg_replace('/\:.+@@/Uis', $change, $in)
			$out = str_replace($sep, '', $out);
			//echo $out.'<br />';
			
			// это для удобства, может тебе понадобится
			preg_match_all('/'.$sep.'(.*)'.$sep.'/Uis', $query, $array); // регулярка на полный шаблон от @@ до @@;
			// preg_match_all('/@@(.*)@@/Uis', $query, $array);
			$full_matches = $array[1]; // массив с найдеными значениями
			$x=array();
			foreach($full_matches as $key => $value) // я не помню нужны тебе были эти значения (названия перед двоеточием) на всякий случай массив с ними.
			{
				$x[] = explode(':=', $value);
			}
	
			$params = array();
			foreach ($x as $key => $value)
			{
				array_push($params, $value[1]);
			}
			//echo '<br />Строка для ПДО: <br />';
			//var_info($params);
			//у тебя как-то так было там, короче в строку string собрались все значения после двоеточия, ты их уже там по-свойски)) сам разберешься))
			// это для удобства, может тебе понадобится
			// и прострели Игорёню по почке разочек, чтобы не расслаБЛЯлся))	
			return array('query'=>$out, 'params'=>$params);
		}
		else return array('query'=>NULL, 'params'=>NULL);
	}
	
	
	protected function checkSql($sql)
    {
		if(!isset($sql['select']))$sql['select']='*';
		if(!isset($sql['where'])||(isset($sql['where'])&&$sql['where']==''))$sql['where']='';
		else{
			$sql['where']='WHERE '.$sql['where'];
		}
		
		if(!isset($sql['order']))$sql['order']='';
		else $sql['order']='ORDER BY '.$sql['order'];
		
		if(!isset($sql['group']))$sql['group']='';
		else $sql['group']='GROUP BY '.$sql['group'];
		
		if(!isset($sql['limit']))$sql['limit']='';
		elseif($sql['limit']!='') $sql['limit']='LIMIT '.$sql['limit'];
		
		if(!isset($sql['having']))$sql['having']='';
		if(!isset($sql['join']))$sql['join']='';
		if(!isset($sql['debug']))$sql['debug']=true;
		
		return $sql;
	}

    public function error($e,$info,$vars)
    {
        $msg = 'Catched error 406' .$e->getCode().': '.$e->getMessage()."\n	in ".$info[0]['file'].' on line '.	$info[0]['line'].".\n	Query: '$pattern'";
        if($vars!='')$msg .= ":'".implode(", ", $vars)."'";
        Log::echoLog($msg);
        //header("location:/error");exit();
    }
}
?>