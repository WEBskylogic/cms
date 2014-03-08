<?php
class Router{
	private $registry;
	private $uri_arr = NULL;
  	public $classObj;
	protected $db;
	public function __construct($registry, $uri = NULL)
	{
		$this->registry = $registry;
		$this->db = new PDOchild($registry);//Connect to database
		if(!isset($uri))
		{
			$uri=$_SERVER['REQUEST_URI'];
			$uri=filter_var($uri, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
			//удаляем последний пустой елемент если есть
			$rest = mb_substr($uri, -1 );
			if($rest=='/')$uri=mb_substr($uri, 0, strlen($uri)-1, 'UTF-8');
		}
		
		$pos = stripos($uri,'?qqfile=');
		if($pos !== false)
		{
			$uri = mb_substr($uri, 0, $pos);
		}
		 
		$uri = current(explode('?', $uri));
		if(!preg_match('/^[-a-zA-Z0-9\/\=\?]*$/',$uri)) $err = true;
		$this->uri_arr=explode("/",$uri);
		
		//удаляем первый пустой елемент
		array_splice($this->uri_arr, 0, 1);
		if(isset($err))
		{
			$this->uri_arr[0] = 'Error';
			$this->uri_arr[1] = 'index';
		}
	}
	
	public function getParams($create = true)
	{
		$url = $this->uri_arr;
		$uri_params = array();
		$params = array();
		$uri_params['action']="index";
		//определения контролера и экшина
		if(isset($url[0])&&$url[0]!='')
		{
			if($url[0]=="ajax")
			{	
				if(isset($url[1]))$uri_params['action']=$url[1]; 
				else $uri_params['action']='index'; 
				$uri_params['controller'] = 'Ajax';
				$params['topic'] = 'ajax';
			}
			elseif($url[0]=="ajaxadmin")
			{
				if(isset($url[1]))$uri_params['action']=$url[1]; 
				else $uri_params['action']='index'; 
				$uri_params['controller'] = 'AjaxAdmin';
				$params['topic'] = 'ajaxadmin';
			}
            elseif($url[0]=="captcha")
			{
                $uri_params['controller'] = 'Captcha';
                $params['topic'] = 'captcha';
            }
			elseif($url[0]=="agentik")
			{
				$params['topic'] = 'agentik';
				if(isset($url[2])&&$url[2]=="logout")
                {
                    unset($_SESSION['admin']);
					setcookie('login', '',  time()+3600*24);
					setcookie('password', '',  time()+3600*24);
                }
				if(checkAuthAgentik($this->db))
				{
					$this->registry->set('agentik', $url[1]);
					$uri_params['controller'] = 'Xmlgate';
					$uri_params['action']='index';
					$params['module'] = 'xmlgate';
				}
				else 
				{
					$this->registry->set('agentik', $url[1]);
					$uri_params['controller'] = 'Agentik';
					$uri_params['action']='index';
				}
			}
			elseif($url[0]=="admin")
			{
                $params['topic'] = 'admin';
                if(isset($url[1])&&$url[1]=="logout")
                {
                    unset($_SESSION['admin']);
                }
				//echo $_SESSION['admin'];

				if(checkAuthAdmin())
				{
					$uri_params['controller'] = 'indexAdmin';
					$uri_params['action']='index';

					if(isset($url[1]))
					{
						$row2 = $this->db->row("SELECT `id`, `name` FROM `modules` WHERE `controller`=?", array($url[1]));
						if($row2)
						{
							$param=array($_SESSION['admin']['id'], $row2['id']);
							$row = $this->db->row("SELECT mm.`permission` 
												   FROM `moderators_permission` mm
												   
												   LEFT JOIN `moderators` m
												   ON mm.moderators_type_id=m.type_moderator
												   WHERE m.id=? AND mm.module_id =?", $param);
							if($row['permission']!=000)
							{
								$uri_params['controller'] = ucfirst($url[1]);
								if(isset($url[2])&&$url[2]=="edit")$uri_params['action']="edit";
								elseif(isset($url[2])&&$url[2]=="add")$uri_params['action']="add";

								Registry::set('topic_value', $url[1]);
								//$params['module'] = $url[1];
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
								
								if(isset($url[2])&&($url[2]=='delete'||(isset($_POST['delete'])&&$url[2]=='update'))&&($row['permission']!=500&&$row['permission']!=300&&$row['permission']!=700&&$row['permission']!=800))
								{
									$this->registry->set('access', messageAdmin('Отказано в доступе', 'error'));
									$uri_params['action']='index';
								}
								elseif(isset($url[2])&&($url[2]=='edit'||$url[2]=='update')&&($row['permission']!=200&&$row['permission']!=500&&$row['permission']!=600&&$row['permission']!=800))
								{
									$this->registry->set('access', messageAdmin('Отказано в доступе', 'error'));
									$uri_params['action']='index';
								}
								elseif(isset($url[2])&&($url[2]=='add'||$url[2]=='duplicate')&&($row['permission']!=400&&$row['permission']!=600&&$row['permission']!=700&&$row['permission']!=800))
								{
									$this->registry->set('access', messageAdmin('Отказано в доступе', 'error'));
									$uri_params['action']='index';
								}

								$this->registry->set('admin', $url[1]);
								$params['action'] = $uri_params['action'];
								$params['controller'] =ucfirst($url[1]);
								$params['module'] = $row2['name'];
							}
                            else return Router::act('error', $this->registry);
						}
                        else return Router::act('error', $this->registry);
					}
                    else{
                        $this->registry->set('admin', 'index');
                        $uri_params['controller'] = 'IndexAdmin';
                        $uri_params['action'] = 'index';
                    }
				}
				else{
					$this->registry->set('admin', 'login');
					$uri_params['controller'] = 'Login';
					if(!empty($url[1])) $params['error'] = $url[1];
					$uri_params['action'] = 'index';
				}
				$params['topic'] = 'admin';
			}
			else
			{
				if(isset($_SESSION['user_info']))
				{
					if($_SESSION['user_info']['agent']!=$_SERVER['HTTP_USER_AGENT'])$error=1;
					if($_SESSION['user_info']['ip']!=$_SERVER['REMOTE_ADDR'])$error=1;
					//if($_SESSION['admin']['referer']!=$_SERVER['HTTP_REFERER'])$error=1;
				}
				if(isset($error))unset($_SESSION['user_info']);
				
                $row = $this->db->row("SELECT `controller` FROM `modules` WHERE `controller`=?", array($url[0]));
                if($row)
                {
                    $uri_params['controller'] = ucfirst($url[0]);
                    $uri_params['action'] = "index";
                    $params['topic'] = $url[0];
                    if(isset($url[1]))$params[$url[0]] = $url[1];
                }
                else{
                    $uri_params['controller'] = 'Pages';
                    $uri_params['action'] = "index";
                    $params['topic'] = $url[0];
                    $params[$url[0]] = '';
                }
				//if(isset($url[1]))$params[$row['name']] = $url[1];
			}
		}
		else{
			$uri_params['controller'] = 'Index';
			$uri_params['action'] = 'index';
			$params['topic'] = 'index';
		}
		//echo $uri_params['controller'].'asd';
		//---------------------------------------------------------------
		
		if(isset($url[0])&&!isset($url[1]))
		{
			if($url[0]=='catalog'||$url[0]=='news'||$url[0]=='photos'||$url[0]=='orders')
			{
				header("Location: ".LINK."/{$url[0]}/all");
				exit();
			}
		}
		
		$url_count = count($url);
		for($i=2;$i<$url_count;)
		{
			if(($url[$i]!='delete'&&$url[$i]!='duplicate')||(($url[$i]=='delete'||$url[$i]=='duplicate')&&$i==2))
			{
				if(isset($url[$i+1]))$val=$url[$i+1];
				else $val='';
				$params[$url[$i]]=$val;
			}
			$i+=2;
		}
		
		//var_info($_SESSION['topic_value']);
		$className = $uri_params['controller'].'Controller';
		$cs = $this->registry['controllers_settings'];
		//echo $cs;

		$filePath = CONROLLERS.$className.'.php';
		
		$method_exists = false;
		//echo $filePath;
		if(file_exists($filePath))
		{
			include_once $filePath;
			$this->classObj = new $className($this->registry, $params);
			if (method_exists($this->classObj, $uri_params['action'].'Action'))
			{
				$method_exists = true;
				if(!$create)unset($this->classObj);
			}
		}
		else
		{
			if($params['topic']=="admin")$filePath = MODULES.strtolower($uri_params['controller']).'/admin/'.$className.'.php';
			else $filePath = MODULES.strtolower($uri_params['controller']).'/'.$className.'.php';//echo $filePath;
			if(file_exists($filePath))
			{
				//echo $filePath;
				$path=MODULES.strtolower($uri_params['controller']).'/models/'.$uri_params['controller'].'Model.php';
				if(file_exists($path))include_once $path;
				include_once $filePath;
				$this->classObj = new $className($this->registry, $params);
				if (method_exists($this->classObj, $uri_params['action'].'Action'))
				{
					$method_exists = true;
					if(!$create)unset($this->classObj);
				}
				
			}
		}
		if(!$method_exists)
		{
			$uri_params['controller'] = 'Error';
			$uri_params['action'] = 'index';
		}//echo $uri_params['controller'];
		if($create)
		{
			if(!$method_exists)
			{	//echo $cs['dirName'].$className.'.php';
				//echo var_dump($this->registry);
				$className = $uri_params['controller'].'Controller';
				include_once CONROLLERS.$className.'.php';
				$this->classObj = new $className($this->registry, $params);
			}
			return $this->dispatch($uri_params['action'], $this->classObj);
		}
		return $uri_params;
	}
	
	public function load($controller, $registry,$params = array())
	{
		$className = ucfirst($controller.'Controller');
		include_once CONROLLERS.$className.'.php';
		return new $className($registry, $params);
	}

	public static function act($controller, $registry, $action = 'index', $params = array())
	{
		$obj = self::load($controller, $registry,$params);
		$res = self::dispatch($action,$obj);
		return $res;
	}

	public function dispatch($strActionName='index',$obj = NULL){
		$objName = ($obj ? '$obj' : '$this->classObj');
		eval('$results = '.$objName.'->'.$strActionName.'Action();');
		unset($obj);
		return $results;
	}
}
?>