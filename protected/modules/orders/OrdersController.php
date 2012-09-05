<?php
/*
 * вывод каталога компаний и их данных
 */
class OrdersController extends BaseController{

    protected $params;
    protected $db;

    function  __construct($registry, $params)
    {
        $this->tb = "orders";
		$this->tb_b = "bascket";
		$this->tb_p = "product";
		$this->tb_p_lang = $this->key_lang.'_product';
        $this->registry = $registry;
        parent::__construct($registry, $params);
    }

    public function indexAction()
    {
		
        $view = new View($this->registry);
		$vars['currency'] = $this->currency();
		$vars['translate'] = $this->translation;

        $vars['discount'] = 0;
		$vars['message'] = '';
		$user_id = 0;
		$where="";
		if(isset($_SESSION['user_id']))
		{
			$row = $this->db->row("SELECT * FROM `users` WHERE id=?", array($_SESSION['user_id']));
			$vars['discount'] = $row['discount'];
			$user_id = $row['id'];
			$vars['user_info'] = $row;
		}
		
		///Delete products
		if(isset($this->params['del']))
		{
			$this->db->query("DELETE FROM `bascket` WHERE `id`=?", array($this->params['del']));
		}
		
		//////////Update amount products
		if(isset($_POST['amount']))
		{
			for($i=0; $i<=count($_POST['id']) - 1; $i++)
			{
				if($_POST['amount'][$i]<=0)$this->db->query("DELETE FROM `bascket` WHERE `id`=?", array($_POST['id'][$i]));
				else $this->db->query("UPDATE `bascket` SET `amount`=? WHERE `id`=?", array($_POST['amount'][$i], $_POST['id'][$i]));
			}	
		}
		
		//////////
		
		$query="SELECT tb2.id, 
					   tb2.url, 
					   tb3.name, 
					   tb2.price, 
					   tb2.discount, 
					   tb2.status_id, 
					   tb.amount, 
					   tb.id as cart_id
				FROM bascket tb
				
				 LEFT JOIN `".$this->tb_p."` tb2 
				 ON tb2.id=tb.product_id
				 
				 LEFT JOIN `".$this->tb_p_lang."` tb3 
				 ON tb2.id=tb3.product_id
				 
				WHERE 
				tb.session_id='".session_id()."' $where";
				
		$vars['product'] = $this->db->rows($query);
		
		////Delivery
		$row = $this->db->row("SELECT id FROM module WHERE `controller`=?", array('delivery'));
		if($row)$vars['delivery'] = $this->db->rows("SELECT * FROM `delivery` WHERE active=? ORDER  BY sort ASC", array(1));
		
		////Payment
		$row = $this->db->row("SELECT id FROM module WHERE `controller`=?", array('payment'));
		if($row)$vars['payment'] = $this->db->rows("SELECT * FROM `payment` WHERE active=? ORDER  BY sort ASC", array(1));

		
		if(isset($_POST['name'])&&count($vars['product'])!=0)
		{
			if(isset($vars['user_info']))
			{
				$where2='';
				if($vars['user_info']['address']=='')$where2.=",address='{$_POST['address']}'";
				if($vars['user_info']['city']=='')$where2.=",city='{$_POST['city']}'";
				if($vars['user_info']['post_index']=='')$where2.=",post_index='{$_POST['post_index']}'";
				if($vars['user_info']['phone']=='')$where2.=",phone='{$_POST['phone']}'";
				if($where2!='')
				{
					$where2=substr($where2, 1, 1000);
					$this->db->query("UPDATE users SET $where2 WHERE id=?", array($_SESSION['user_id']));	
				}
			}
			else{/////New users
				$row=$this->db->row("SELECT id FROM users WHERE email=?", array($_POST['email']));
				if(!$row)
				{
					$pass = genPassword();
					$start_date=date("Y-m-d H:i:s");
					$code=md5(mktime());
					$settings = Registry::get('user_settings');
					$this->db->query("INSERT INTO users SET 
															name=?, 
															email=?, 
															pass=?, 
															status_id=?, 
															phone=?, 
															address=?, 
															post_index=?, 
															city=?, 
															start_date=?,
															active_email=?", 
					array($_POST['name'], $_POST['email'], md5($pass), 1, $_POST['phone'], $_POST['address'], $_POST['post_index'], $_POST['city'], $start_date, $code));
					$text="Вы зарегистрировались на сайте {$_SERVER['HTTP_HOST']}.<br /><br />
	
							Для завершения регистрации перейдите по адресу<br />
							<a href=\"http://{$_SERVER['HTTP_HOST']}/users/active/code/$code\" target=\"_blank\">http://{$_SERVER['HTTP_HOST']}/users/active/code/$code</a><br /><br />

								Ваш логин: {$_POST['email']}<br />
								Ваш пароль: $pass<br /><br />
								
								P.S. Если вы получили это письмо, но не проходили процесс регистрации (возможно кто-то использовал ваш e-mail), просто проигнорируйте это письмо.";
					send_mime_mail($settings['sitename'], // имя отправителя
						"info@".$_SERVER['HTTP_HOST'], // email отправителя
						$_POST['name'], // имя получателя
						$_POST['email'], // email получателя
						"utf-8", // кодировка переданных данных
						"windows-1251", // кодировка письма
						"Вы зарегистрировались на сайте ".$_SERVER['HTTP_HOST'], // тема письма
						$text // текст письма
						);//echo $text;
									
					$vars['user_info'] = $this->db->row("SELECT id FROM users WHERE email=?", array($_POST['email']));
					$user_id = $vars['user_info']['id'];
				}
			}
			$this->sendOrder($query, $user_id, $vars['currency']);
			$vars['message']="<div class='done'><b>Благодарим Вас за заказ!</b><br />В ближайшее время с Вами свяжется менеджер по указанному Вами E-mail!</div>";
		}
		
		$data['styles'] = array('validationEngine.jquery.css', 'user.css');
        $data['scripts'] = array('jquery.validationEngine.js', 'jquery.validationEngine-ru.js');
		$data['content'] = $view->Render('cart.phtml', $vars);
		return $this->Render($data);
    }
	
	function sendOrder($query, $user_id, $currency)
	{
		$settings = Registry::get('user_settings');

		///Add order
		$date=date("Y-m-d H:i:s");
		$q="insert into orders set username=?, email=?, phone=?, city=?, address=?, post_index=?, comment=?, date_add=?, status_id=?, user_id=?";
		$order_id=$this->db->insert_id($q, array($_POST['name'], $_POST['email'], $_POST['phone'], $_POST['city'], $_POST['address'], $_POST['post_index'], $_POST['text'], $date, 1, $user_id));


		$path=$_SERVER['HTTP_HOST'];
		$summa2=0;
		$summa3=0;
		$summa4=0;	
		$text="Фамилия, имя: {$_POST['name']}<br />
				Контактный E-mail: {$_POST['email']}<br />
				Контактный телефон: {$_POST['phone']}<br />
				Адрес: {$_POST['address']}<br />";
		if(isset($_POST['delivery']))
		{
			$text.="Способ доставки: {$_POST['delivery']}<br />";
			$this->db->query("UPDATE orders SET delivery=? WHERE id=?", array($_POST['delivery'], $order_id));	
		}
		if(isset($_POST['payment']))
		{
			$text.="Способ оплаты: {$_POST['payment']}<br />";	
			$this->db->query("UPDATE orders SET payment=? WHERE id=?", array($_POST['payment'], $order_id));
		}

		$text.="Примечание: <br />
				{$_POST['text']}<br /><br />
				
				Товары:<br />";

		$text.="
		<table border='1' cellpadding='0' cellspacing='0' width='700' style='border-collapse:collapse;'>
			<tr>
				<th width='60px' style='text-align:center; border:1px solid #cccccc; padding:10px;'>ID</th>
				<th style='border:1px solid #cccccc;' width='100'></th>
				<th style='border:1px solid #cccccc;'></th>
				<th width='60px' style='text-align:center; border:1px solid #cccccc; padding:10px;'>Кол-во</th>
				<th width='100px' style='text-align:center; border:1px solid #cccccc; padding:10px;'>Сумма</th>
			</tr>";
		$i=0;
		$res = $this->db->rows($query);
		foreach($res as $row)
		{
			////////
			$price=viewPrice($row['price'], $row['discount']);		
			$summa = formatPrice($price['cur_price'] * $row['amount']);
			$summa2 +=$price['cur_price'] * $row['amount'];
			$summa4 +=$price['base_price'] * $row['amount'];
			$dir=createDir($row['id']);
			$src="/".$dir[0]."{$row['id']}_s.jpg";
			
			///Add orders products
			$query="INSERT INTO orders_product SET product_id=?, name=?, price=?, sum=?, discount=?, amount=?, orders_id=?";
			$this->db->query($query, array($row['id'], $row['name'], $price['base_price'], $price['base_price'] * $row['amount'], $row['discount'], $row['amount'], $order_id));
			
			
			
			$text.="<tr>
						<td style='text-align:center; border:1px solid #cccccc; padding:10px;'>#".$row['id']."</td>
						<td style='text-align:center; border:1px solid #cccccc; padding:10px;'>
							<a href='http://$path/product/".$row['url']."'><img src='http://".$path.$src."' width='100' /></a>
						</td>
						<td style='text-align:center; border:1px solid #cccccc; padding:10px;'>
							<a href='http://".$path."/product/".$row['url']."'>".$row['name']."</a>";
							if($row['discount']!=0)
								$text.="<br /><font style='color:red;'>Скидка ".$row['discount']."%</font><br />
										<font style='text-decoration:line-through;'>".$price['old_price']."</font>";
			$text.="<br />{$price['price']}<br />";
			$text.="	</td>
						<td style='text-align:center; border:1px solid #cccccc; padding:10px;'>".$row['amount']."</td>
						<td style='text-align:center; border:1px solid #cccccc; padding:10px;'>".$summa."</td>
					</tr>";	
			$i+=$row['amount'];		
		}
		
		$this->db->query("UPDATE `orders` SET `sum`=?, `amount`=? where `id`=?", array($summa4, $i, $order_id));
		$text.="</table>";

		$text.="<h3>№ заказа $order_id &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Итого: ".formatPrice($summa2)."</h3>";	
		
		send_mime_mail($settings['sitename'], // имя отправителя
						"info@".$_SERVER['HTTP_HOST'], // email отправителя
						$settings['sitename'], // имя получателя
						$settings['email'], // email получателя
						"utf-8", // кодировка переданных данных
						"windows-1251", // кодировка письма
						"Новый заказ на сайте ".$settings['sitename'], // тема письма
						$text // текст письма
						);//echo $text;
		
		$text="Добрый день {$_POST['name']} !
				<br /><br />
				$date<br />Вы оформили заказ в интернет-магазине <a href='http://".$_SERVER['HTTP_HOST']."' target='_blank'>http://".$_SERVER['HTTP_HOST']."</a><br />
				Ваш заказ принят в обработку.<br /><br />
				---------------------------------------------------------<br />
				$text
				---------------------------------------------------------<br />
				<br /><br />
				Благодарим Вас за заказ! В ближайшее время с Вами по указанному E- mail свяжется менеджер, для уточнения оплаты и доставки.";
		
		send_mime_mail($settings['sitename'], // имя отправителя
						"info@".$_SERVER['HTTP_HOST'], // email отправителя
						$_POST['name'], // имя получателя
						$_POST['email'], // email получателя
						"utf-8", // кодировка переданных данных
						"windows-1251", // кодировка письма
						"Вы оформили заказ на сайте ".$settings['sitename'], // тема письма
						$text // текст письма
						);//echo $text;					

		$this->db->query("DELETE FROM `bascket` WHERE `session_id`=?", array(session_id()));
	}
}
?>