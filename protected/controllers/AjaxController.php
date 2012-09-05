<?php
/**
 * class to Ajax action
 * @author mvc
 */

class AjaxController extends BaseController{
	
	function __construct ($registry, $params)
	{
		$this->registry = $registry;
		parent::__construct($registry, $params);
	}

	function indexAction()
	{
		
	}

	//////Put in shop cart
	function incartAction()
	{
		if(isset($_POST['id'],$_POST['amount']))
		{
			$count=$this->db->rows("SELECT `id` FROM `bascket` WHERE `session_id`=? and `product_id`=?", array(session_id(), $_POST['id']));
			if(count($count)==0)
			{
				$date=date("Y-m-d H:i:s");echo $_POST['id'];
				$row=$this->db->row("SELECT `price` FROM `product` WHERE `id`=?", array($_POST['id']));
				$this->db->query("INSERT into bascket SET price=?, session_id=?, product_id=?, date=?, amount=?", array($row['price'], session_id(), $_POST['id'], $date, $_POST['amount']));
			}
			else{
				$this->db->query("UPDATE bascket SET amount=amount+? WHERE session_id=? AND product_id=?", array($_POST['amount'], session_id(), $_POST['id']));
			}	
		}
	}
	
	///Bascket shop cart
	function bascketAction()
	{
		$currency = $this->currency();
		$total=0;
		$res=$this->db->rows("SELECT b.`amount`, b.`price`, p.discount
							  FROM `bascket` b
							  LEFT JOIN product p
							  ON p.id=b.product_id
							  WHERE b.`session_id`=?", array(session_id()));
		foreach($res as $row)
		{
			$price = viewPrice($row['price'], $row['discount']);
			$total += $price['cur_price'] * $row['amount'];	
		}
		$count=$this->db->row("SELECT SUM(amount) as count FROM `bascket` WHERE `session_id`=?", array(session_id()));

		$total=formatPrice($total);
		
		$view = new View($this->registry);
		echo $view->Render('bascket.phtml', array('total'=>$total, 'count'=>$count['count'], 'translate'=>$this->translation));
	}
	
	///Add comments
	function addcommentAction()
    {
		if(isset($_POST['name'],$_POST['message'],$_POST['id'],$_POST['photo']))
		{
			if(!isset($_POST['type']))$_POST['type']='';
			if(!isset($_POST['sub']))$_POST['sub']=0;
			$name=$_POST['name'];
			$pos = strpos($name, "<a");
			if($pos === false&&$_POST['name']!=""&&$_POST['message']!="")
			{
				$date=date("Y-m-d H:i:s");
				$query = "INSERT INTO `comments` SET `sub`=?, `author`=?, `text`=?, `content_id`=?, `type`=?, `date`=?, `session_id`=?, `language`=?, `photo`=?";
				$this->db->query($query, array($_POST['sub'], $_POST['name'], $_POST['message'], $_POST['id'], $_POST['type'], $date, session_id(), $this->key_lang, $_POST['photo']));
				echo"<div class='message'>".$this->translation['comment_add']."!</div>";
			}
		}
    }
	
	///Add email
	function mailtoAction()
    {
		if(isset($_POST['email']))
		{
			$err='';
			$err = $this->validate($_POST['email'], 'email');
			$row=$this->db->row("SELECT `id` FROM `email` WHERE `email`=?", array($_POST['email']));
			if($row)$err = "<div class='err'>".$this->translation['email_exists']."</div>";
			if($err=="")
			{
				$date=date("Y-m-d H:i:s");
				$query = "INSERT INTO `email` SET `email`=?, `date`=?";
				$this->db->query($query, array($_POST['email'], date("Y-m-d H:i:s")));
				echo"<div class='done'>".$this->translation['email_added']."!</div>";
			}
			else echo $err;
		}
    }

	///Send message
    function feedbackAction()
    {
		if(isset($_POST['name'], $_POST['message'], $_POST['email']))
		{
			// echo strlen($_POST['message']);
			$send=0;
			if(strlen($_POST['message'])<5||strlen($_POST['name'])<3)$message="".$this->translation['required']."";
			elseif(!preg_match('|([a-z0-9_\.\-]{1,20})@([a-z0-9\.\-]{1,20})\.([a-z]{2,4})|is', $_POST['email']))$message="".$this->translation['wrong_email']."";
			else{
				$settings = Registry::get('user_settings');
				send_mime_mail($settings['sitename'], // имя отправителя
					"info@".$_SERVER['HTTP_HOST'], // email отправителя
					$settings['sitename'], // имя получателя
					$settings['email'], // email получателя
					"utf-8", // кодировка переданных данных
					"windows-1251", // кодировка письма
					"Обратная связь: ".$_SERVER['HTTP_HOST'], // тема письма
					"
							Имя:{$_POST['name']}<br />
							E-mail:{$_POST['email']}<br />
							<br />
							Сообщение:{$_POST['message']}
						" // текст письма
				);
				$send=1;
				$message="<font style='color:green;'>".$this->translation['message_sent']."</font>";
			}
			$message = array($send, $message);
			echo json_encode($message);
		}
    }
}
?>