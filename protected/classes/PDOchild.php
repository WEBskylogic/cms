<?php
/**
 * Represents a connection between PHP and a database server
 * using built-in PDO library.
 * To read documentation about PDO follow the link http://php.net/manual/en/class.pdo.php
 */
class PDOchild Extends PDO
{
	private $registry;
	public function __construct($registry)
	{
		$this->registry = $registry;//echo var_dump($registry);
        $db_setts = $this->registry['db_settings'];
        $db_setts['host'] = 'mysql:host='.$db_setts['host'].';dbname='.$db_setts['name'];
		$db_setts = array($db_setts['host'],$db_setts['user'],$db_setts['password'],array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '".$db_setts['charset']."'"));
		if (($num_args = func_num_args()) > 0)
		{
			$args = func_get_args();  
			for($i=1;$i<$num_args;$i++) if($db_setts[$i] !== NULL)$db_setts[$i] = $args[$i];
		}
		try{
            //var_info($db_setts);
			$dbh = call_user_func_array(array('PDO','__construct'), $db_setts);
			$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			return $dbh;
		}
		catch(Exception $e)
		{
			Log::echoLog("Cannot connect to database!<br />\n Caught exception:\n".$e->getMessage()." in ".$e->getFile()." on line ".$e->getLine());
			//".$e->getMessage()." in ".$e->getFile()." on line ".$e->getLine()."
			return;
		}
	}

	/* Returns all rows fetched from db
	 * as an array of associated arrays.
	 *	@param string $pattern - pattern of the request to database.
	 *	@param array $vars - array of variables that PDO checks
	 * before putting them into request.
	 *  @return int or bool the number of affected rows
	 * or false if the query could't be executed. */
	public function query($pattern,$vars = null) {
		try{
			$sth = $this->prepare($pattern);
			$sth->execute($vars);
		} catch(PDOException $e){
			$info = debug_backtrace();
			$this->error($e,$pattern,$info,$vars);
			return false;
		}
		return $sth->rowCount(); //var_dump($sth->rowCount());
	}

	public function insert_id($pattern,$vars = null) {
		try{
			$sth = $this->prepare($pattern);
			$sth->execute($vars);
		} catch(PDOException $e){
			$info = debug_backtrace();
			$this->error($e,$pattern,$info,$vars);
			return false;
		}
		
		return $this->lastInsertId();
	}
	
	/* Returns all rows fetched from db
	 * as an array of associated arrays.
	 *	@param string $pattern - pattern of the request to database.
	 *	@param array $vars - array of variables that PDO checks
	 * before putting them into request. */
	public function rows($pattern,$vars = null) {
		try{
			$sth = $this->prepare($pattern);
			$sth->execute($vars);
		} catch(PDOException $e){
			$info = debug_backtrace();
			$this->error($e,$pattern,$info,$vars);
			return false;
		}
		return $sth->fetchAll(PDO::FETCH_ASSOC);
	}

	/* Returns a row fetched from db
	 * as an associated array.
	 *	@param string $pattern - pattern of the request to database.
	 *	@param array $vars - array of variables that PDO checks
	 * before putting them into request. */
	public function row($pattern,$vars = null) {
		try{
			$sth = $this->prepare($pattern);
			$sth->execute($vars);
		} catch(PDOException $e){
			$info = debug_backtrace();
			$this->error($e,$pattern,$info,$vars);
			return false;
		}
		return $sth->fetch(PDO::FETCH_ASSOC);
	}

	/* Returns first cell value of a row fetched from db.
	 *	@param string $pattern - pattern of the request to database.
	 *	@param array $vars - array of variables that PDO checks
	 * before putting them into request. */
	public function cell($pattern,$vars = null) {
		try{
			$sth = $this->prepare($pattern);
			$sth->execute($vars);
		}catch(PDOException $e){
			$info = debug_backtrace();
			$this->error($e,$pattern,$info,$vars);
			return false;
		}
		return $sth->fetch(PDO::FETCH_COLUMN, PDO::FETCH_ORI_FIRST);
	}

	public function error($e,$pattern,$info,$vars)
	{
		$msg = 'Catched error 406' .$e->getCode().': '.$e->getMessage()."\n	in ".$info[0]['file'].' on line '.	$info[0]['line'].".\n	Query: '$pattern'";
		if($vars!='')$msg .= ":'".implode(", ", $vars)."'";
		Log::echoLog($msg);
		//header("location:/error");exit();
	}
	
	public function rows_key($pattern, $vars = null)
	{
		try{
			$sth = $this->prepare($pattern);
			$sth->execute($vars);
		}catch(PDOException $e){
			$info = debug_backtrace();
			$this->error($e,$pattern,$info,$vars);
			return false;
		}
		return $sth->fetchAll(PDO::FETCH_KEY_PAIR);
	}
}
?>