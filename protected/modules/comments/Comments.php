<?
class Comments extends Model
{
    static $table='comments'; //Главная талица
    static $name='Комментарии'; // primary key

    public function __construct($registry)
    {
        parent::getInstance($registry);
    }

    //для доступа к классу через статичекий метод
    public static function getObject($registry)
    {
        return new self::$table($registry);
    }

    public function save()
    {
        $message='';
        if(isset($this->registry['access']))$message = $this->registry['access'];
        else
        {
            if(isset($_POST['active'], $_POST['name'], $_POST['text']))
            {
                $param = array($_POST['name'], $_POST['name2'], $_POST['text'], $_POST['text2'], $_POST['active'],$_SESSION['admin']['id'], $_POST['id']);
                $this->db->query("UPDATE `".$this->table."` SET `author`=?, `author2`=?, `text`=?, `text2`=?, `active`=?, moderator_id=? WHERE `id`=?", $param);
                $message .= messageAdmin('Данные успешно сохранены');
            }
            else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
        }
        return $message;
    }
	
	public function list_comments($id, $type='product')
	{
		$vars=array();
		$vars['type']=$type;
		$vars['translate']=$this->translation;
		$vars['id']=$id;

		$vars['comments'] = $this->db->rows("SELECT * FROM `comments` WHERE content_id=? AND active=? AND type=? ORDER BY date DESC", array($id, 1, $type));
				
		$view = new View($this->registry);
		return $view->Render('comments.phtml', $vars);
	}
	
	public function list_comments_admin($id, $type='product')
	{
		$vars=array();
		$vars['type']=$type;
		$vars['id']=$id;

		$vars['comments'] = $this->db->rows("SELECT * FROM `comments` WHERE content_id=? AND type=? ORDER BY date DESC", array($id, $type));
		
		if(isset($vars['comments'][0]['id']))
		{
			$view = new View($this->registry);
			$vars['module']='comments/admin';
			return $view->Render('comments_list.phtml', $vars);
		}
		else return'';
	}
	
	public function list_comments_product($limit='')
	{
		if($limit!='')$limit='LIMIT '.$limit;
		$comments = $this->db->rows("SELECT c.id, c.author, c.text, c.date, c.photo, p.url, p2.name
									 FROM `comments` c
									 
									 LEFT JOIN product p
									 ON p.id=c.content_id
									 
									 LEFT JOIN ".$this->registry['key_lang']."_product p2
									 ON p2.product_id=p.id
									 
									 WHERE c.active='1'
									 GROUP BY c.id
									 ORDER BY c.date DESC ".$limit);
		$view = new View($this->registry);
		return $view->Render('comments.phtml', array('comments'=>$comments, 'translate'=>$this->translation));
	}
	
	public function addcomment()
    {
		if(isset($_POST['name'], $_POST['message'], $_POST['id'], $_POST['type'], $_POST['photo'])&&$_POST['name']!=""&&$_POST['message']!="")
		{
			$data=array();
			$date=date("Y-m-d H:i:s");
			if(isset($this->settings['moderation'])&&$this->settings['moderation']==0)
			{
				$data['message']="<div class='message'>".$this->translation['comment_added']."!</div>";
				$where=", active='1'";
			}
			else{
				$data['message']="<div class='message'>".$this->translation['comment_add']."!</div>";
				$where=", active='0'";	
			}
			if($_POST['photo']=='undefined')$_POST['photo']='';
			$query = "INSERT INTO `comments` SET `author`=?, `text`=?, `content_id`=?, `type`=?, `date`=?, `session_id`=?, `language`=?, `photo`=? $where";
			$this->db->query($query, array(strip_tags($_POST['name']), strip_tags($_POST['message']), $_POST['id'], $_POST['type'], $date, session_id(), $this->registry['key_lang'], $_POST['photo']));
			if($where==", active='1'")$data['list']=Comments::getObject($this->sets)->list_comments($_POST['id'], $_POST['type']);
			return $data;
		}
    }
	
	
}
?>