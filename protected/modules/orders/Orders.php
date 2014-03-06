<?
class Orders extends Model
{
    static $table='orders'; //Главная талица
    static $name='Заказы'; // primary key
	
	public function __construct($registry)
    {
        parent::getInstance($registry);
    }

    //для доступа к классу через статичекий метод
	public static function getObject($registry)
	{
		return new self::$table($registry);
	}

	public function add()
	{
		$message='';
		if(isset($_POST['email'], $_POST['username'], $_POST['phone'], $_POST['country'], $_POST['city'])&&$_POST['username']!="")
		{
            $err="";
			if($err=="")
            {
                $date_add=date("Y-m-d H:i:s");
                $id=$this->db->insert_id("INSERT INTO `".$this->table."` SET
                                                                         `username`=?,
                                                                         `email`=?,
                                                                         `country`=?,
                                                                         `city`=?,
                                                                         `phone`=?,
                                                                         `date_add`=?,
																		 `comment`=?,
																		 status_id=?", 
																		 array(
                                                                         $_POST['username'],
                                                                         $_POST['email'],
                                                                        $_POST['country'],
                                                                        $_POST['city'],
                                                                        $_POST['phone'],
                                                                        $date_add,
																		$_POST['comment'],
                                                                        1)
                );
				if(isset($_POST['delivery']))$this->db->query("UPDATE orders SET delivery_id=? WHERE id=?", array($_POST['delivery'], $id));	
				if(isset($_POST['payment']))$this->db->query("UPDATE orders SET payment_id=? WHERE id=?", array($_POST['payment'], $id));
	
                $message.= messageAdmin('Данные успешно добавлены');
            }
            else $message.= messageAdmin($err, 'error');
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
            if(isset($_POST['status'], $_POST['email'], $_POST['username'], $_POST['phone'], $_POST['country'], $_POST['city']))
            {
                $err='';
				
                if($err=="")
                {
					
					/////Update orders product
					$total=0;
					$amount=0;
					
					if(isset($_POST['delivery']))
					{
						$row = $this->db->row("SELECT * FROM delivery WHERE id=?", array($_POST['delivery']));
						$total +=$row['price'];	
						$this->db->query("UPDATE orders SET delivery_id=? WHERE id=?", array($_POST['delivery'], $_POST['id']));	
					}

					if(isset($_POST['payment']))$this->db->query("UPDATE orders SET payment_id=? WHERE id=?", array($_POST['payment'], $_POST['id']));
					
					if(isset($_POST['product_id']))
                    for($i=0; $i<=count($_POST['product_id']) - 1; $i++)
					{
						/*if($_POST['discount'][$i]!=0)$sum=discount($_POST['discount'][$i], $sum=$_POST['price'][$i]*$_POST['amount'][$i]);
						else $sum=$_POST['price'][$i]*$_POST['amount'][$i];*/
						
						$sum=$_POST['price'][$i]*$_POST['amount'][$i];
						$total+=$sum;
						$amount+=$_POST['amount'][$i];
						$this->db->query("UPDATE `orders_product` SET `name`=?, `price`=?, `discount`=?, `amount`=?, `sum`=? WHERE `id`=?", 
										array($_POST['name'][$i],
											  $_POST['price'][$i],
											  $_POST['discount'][$i],
											  $_POST['amount'][$i],
											  $sum,
											  $_POST['product_id'][$i]
										));
					}
					
                    $this->db->insert_id("UPDATE `".$this->table."` 
										  SET
											 `username`=?,
											 `status_id`=?,
											 `email`=?,
											  `phone`=?,
											  `address`=?,
											  `post_index`=?,
											 `country`=?,
											 `city`=?,
											 `comment`=?,
											 `sum`=?,
											 `amount`=?
											 
                                          WHERE id=?", array(
                            $_POST['username'],
                            $_POST['status'],
                            $_POST['email'],
                            $_POST['phone'],
							$_POST['address'],
							$_POST['post_index'],
                            $_POST['country'],
                            $_POST['city'],
							$_POST['comment'],
							$total,
							$amount,
                            $_POST['id'])
                    );
					
					
                    $message.= messageAdmin('Данные успешно сохранены');
                }
            }
            else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
		}
		return $message;
	}
	
	public function recalc($order_id)
	{
		$total=0;
		$res = $this->db->rows("SELECT *, SUM(amount) as amount FROM orders_product WHERE orders_id=?", array($order_id));
		foreach($res as $row)
		{
			$sum=$row['price']*$row['amount'];
			$total+=$sum;
			$this->db->query("UPDATE `orders_product` SET `sum`=? WHERE `id`=?", array($sum, $row['id']));
		}
		$this->db->query("UPDATE `orders` SET `amount`=?, `sum`=? WHERE `id`=?", array($res[0]['amount'], $total, $order_id));
	}
	
	function orderProduct()
    {
		if($_POST['id'])
		{
			$data=array();
			$data['content']='<option value="0">Выберите товар...</option>';
			$res = Product::getObject($this->sets)->find(array('order'=>'tb.`sort` ASC, tb.id DESC', 
															   'select'=>'tb.*, tb_lang.name',
															   'where'=>'__tb.active:=1__ AND __tb3.catalog_id:='.$_POST['id'].'__',
															   'join'=>' LEFT JOIN product_catalog tb3 ON tb3.product_id=tb.id',
															   'type'=>'rows'));
			if(count($res)!=0)
			{
				foreach($res as $row)
				{
					$data['content'].='<option value="'.$row['id'].'">'.$row['name'].'</option>';
				}
			}
			else $data['content']='<option value="0">Товаров нет...</option>';
			
			return json_encode($data);
		}
    }
	
	function orderProductView()
    {
		if(isset($_POST['id'],$_POST['order_id']))
		{
			$data=array();
			$row = Product::getObject($this->sets)->find(array('select'=>'tb.*, tb_lang.name', 'where'=>'__tb.id:='.$_POST['id'].'__'));
															   
			$row2 = $this->db->row("SELECT id FROM orders_product WHERE orders_id=? AND product_id=?", array($_POST['order_id'], $_POST['id']));	
			$param = array($_POST['order_id'], $row['name'], $row['price'], $row['discount'], 1, $row['price'], $_POST['id']);		   
			
			if(!$row2)$this->db->query("INSERT INTO orders_product SET orders_id=?, name=?, price=?, discount=?, amount=?, `sum`=?, `product_id`=?", $param);
			else $this->db->query("UPDATE orders_product SET amount=amount+1, `sum`=`sum`*amount WHERE id=?", array($row2['id']));
			
			$total=0;
			$res = $this->db->rows("SELECT * FROM orders_product WHERE orders_id=?", array($_POST['order_id']));
			foreach($res as $row)
			{
				$sum=$row['price']*$row['amount'];
				$total+=$sum;
				
			}	
			$this->recalc($_POST['order_id']);
			$data['total']=$total;
			$data['res'] = $this->db->rows("SELECT * FROM orders_product WHERE orders_id=?", array($_POST['order_id']));
			$data['currency'] = $this->db->row("SELECT icon FROM currency WHERE `base`='1'");
			
			//$data['amount'] = count($res)-1;
			$data['total'] = 'Итого: '.$total;
			return $data;
		}
    }
	
	function del_product($id)
	{
		$row = $this->db->row("SELECT orders_id FROM orders_product WHERE id=?", array($id));
		$this->db->query("DELETE FROM `orders_product` WHERE id=?", array($id));
		$this->recalc($row['orders_id']);
	}
	
	function sendOrder($query, $info, $user_id)
	{
		if(!isset($_SESSION['order_info']))$_SESSION['order_info']=array();
		foreach($info as $key => $value) 
		{
			//echo $key.'='.$value.'<br />';	
			if($value!=''&$key!='')
			{
				$_SESSION['order_info'][$key]=$value;
			}
		}
		
		///Add order
		$date=date("Y-m-d H:i:s");
		$summa2=0;
		$summa3=0;
		$summa4=0;	
		$text="<h4>Информация о отправителе</h4>
			   ФИО: {$info['name']}<br />
			   Контактный телефон: {$info['phone']}<br />";
				
		$q="INSERT INTO `".$this->table."` SET
						__username:=".$info['name']."__,
						__status_id:=1__,
						__date_add:=".$date."__,
						__currency:=грн.__";
		
		if(isset($info['email']))
		{
			$q.=", __email:=".$info['email']."__";
			$text.="Контактный E-mail: {$info['email']}<br />";
		}
		if(isset($info['code_discount']))
		{
			$q.=", __discount:=".$info['code_discount']."__";
			$text.="Код скидки: {$info['code_discount']}<br />";
		}
		
		
		///////////Destination info
		$d_info='';
		if(isset($info['destination_name']))
		{
			$q.=", __destination_name:=".$info['destination_name']."__";
			$d_info.="ФИО получателя: {$info['destination_name']}<br />";
		}
		if(isset($info['destination_phone']))
		{
			$q.=", __destination_phone:=".$info['destination_phone']."__";
			$d_info.="Телефон получателя: {$info['destination_phone']}<br />";
		}
		if(isset($info['datetime']))
		{
			$q.=", __destination_date:=".$info['datetime']."__";
			$d_info.="Дата и время доставки: {$info['datetime']}<br />";
		}
		if(isset($info['address']))
		{
			$q.=", __address:=".$info['address']."__";
			$d_info.="Адрес: {$info['address']}<br />";
		}
		if(isset($info['city']))
		{
			$q.=", __city:=".$info['city']."__";
			$d_info.="Город: {$info['city']}<br />";
		}
		if(isset($info['text']))
		{
			$q.=", __comment:=".$info['text']."__";
			$d_info.="Примечание: <br />{$info['text']}<br /><br />";
		}
		
		
		if($d_info!='')$text.="<br /><h4>Информация о получателе</h4>".$d_info;
		///////////////
		
		if(isset($info['phone']))$q.=", __phone:=".$info['phone']."__";

		if($user_id!=0)$q.=", __user_id:=".$user_id."__";
		
		//echo $query;
		$order_id = $this->query($q, true);			
		$path=$_SERVER['HTTP_HOST'];
		

		if(isset($info['delivery']))
		{
			$row = $this->db->row("SELECT id FROM modules WHERE `controller`=?", array('delivery'));
			if($row)
			{
				$row = Delivery::getObject($this->sets)->find((int)$info['delivery']);
				$price = Numeric::viewPrice($row['price']);
				$summa4 +=$row['price'];	
				$summa2 =$price['price'];	
				$text.="Способ доставки: {$row['name']}<br />";
				$this->db->query("UPDATE orders SET delivery_id=? WHERE id=?", array($row['id'], $order_id));	
			}
		}
		if(isset($info['payment']))
		{
			$row = $this->db->row("SELECT id FROM modules WHERE `controller`=?", array('payment'));
			if($row)
			{
				$row = Payment::getObject($this->sets)->find((int)$info['payment']);
				$text.="Способ оплаты: {$row['name']}<br />";	
				$this->db->query("UPDATE orders SET payment_id=? WHERE id=?", array($info['payment'], $order_id));
			}
		}

		$text.="<br /><br />
		Товары:<br />
		<table border='1' cellpadding='0' cellspacing='0' width='700' style='border-collapse:collapse;'>
			<tr>
				<th width='60px' style='text-align:center; border:1px solid #cccccc; padding:10px;'>Артикул</th>
				<th style='border:1px solid #cccccc;' width='100'></th>
				<th style='border:1px solid #cccccc;'></th>
				<th width='60px' style='text-align:center; border:1px solid #cccccc; padding:10px;'>Кол-во</th>
				<th width='100px' style='text-align:center; border:1px solid #cccccc; padding:10px;'>Сумма</th>
			</tr>";
		$i=0;
		$res = $query;
		foreach($res as $row)
		{
			////////
			$price=Numeric::viewPrice($row['price'], $row['discount']);		
			$summa = Numeric::formatPrice($price['cur_price'] * $row['amount']);
			$summa2 +=$price['cur_price'] * $row['amount'];
			$summa4 +=$price['base_price'] * $row['amount'];
			$src="/".$row['photo'];
			
			$color="";
			if(isset($row['color'],$row['size'])&&($row['color']!=''||$row['size']!=''))$color=" (".$row['color'].", ".$row['size'].")";
			
			///Add orders products
			$query="INSERT INTO orders_product SET product_id=?, code=?, name=?, price=?, sum=?, discount=?, amount=?, orders_id=?";
			$this->db->query($query, array($row['id'], $row['code'], $row['name'].$color, $price['base_price'], $price['base_price'] * $row['amount'], $row['discount'], $row['amount'], $order_id));
			
			
			$text.="<tr>
						<td style='text-align:center; border:1px solid #cccccc; padding:10px;'>".$row['code']."</td>
						<td style='text-align:center; border:1px solid #cccccc; padding:10px;'>
							<a href='http://$path/product/".$row['url']."'><img src='http://".$path.$src."' width='100' /></a>
						</td>
						<td style='text-align:center; border:1px solid #cccccc; padding:10px;'>
							<a href='http://".$path."/product/".$row['url']."'>".$row['name']." $color</a>";
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
		
		$text.="<tr>
					<td colspan='5' align='right'>
						<div style='font-size:14px;'>
							<div style='font-size:18px;'>Итого: ".Numeric::formatPrice($summa2)."</div>
						</div>
					</td>
				</tr>
			</table>";
		$text="<h2>№ заказа $order_id</h2>".$text;
		$this->db->query("UPDATE `orders` SET `sum`=?, `amount`=? where `id`=?", array($summa2, $i, $order_id));
		

		//////Send to the admin
		Mail::send($this->settings['sitename'], // имя отправителя
						"info@".$_SERVER['HTTP_HOST'], // email отправителя
						$this->settings['sitename'], // имя получателя
						$this->settings['email'], // email получателя
						"utf-8", // кодировка переданных данных
						"utf-8", // кодировка письма
						"Новый заказ на сайте ".$this->settings['sitename'], // тема письма
						$text // текст письма
						);//echo $text;
						
		//////Send to the client
		if(isset($info['email']))
		{
			$text="Добрый день {$info['name']} !
				<h2>№ заказа $order_id</h2>
				<br /><br />
				$date<br />Вы оформили заказ в интернет-магазине <a href='http://".$_SERVER['HTTP_HOST']."' target='_blank'>http://".$_SERVER['HTTP_HOST']."</a><br />
				Ваш заказ принят в обработку.<br /><br />
				---------------------------------------------------------<br />
				$text
				---------------------------------------------------------<br />
				<br /><br />
				Благодарим Вас за заказ! В ближайшее время с Вами по указанному E- mail свяжется менеджер, для уточнения оплаты и доставки.";
			Mail::send($this->settings['sitename'], // имя отправителя
							"info@".$_SERVER['HTTP_HOST'], // email отправителя
							$info['name'], // имя получателя
							$info['email'], // email получателя
							"utf-8", // кодировка переданных данных
							"utf-8", // кодировка письма
							"Вы оформили заказ на сайте ".$this->settings['sitename'], // тема письма
							$text // текст письма
							);//echo $text;	
			
			$where="WHERE ".$this->get_user_id('bascket');
			$this->db->query("DELETE FROM `bascket` $where");
		}
	}
	
	
	function incart($id, $size, $color, $amount=1)
	{
		if($amount=='undefined')$amount=1;
		$where="AND ".$this->get_user_id();
		$row=$this->db->row("SELECT `id` FROM `bascket` b WHERE `price_id`=? AND size=? AND color=? $where", array($id, $size, $color));
		if(!$row)
		{
			if($amount==0)$amount = 1;
			$date = date("Y-m-d H:i:s");
			$row = $this->db->row("SELECT `price`, code, discount, product_id FROM `price` WHERE `id`=?", array($id));
			
			if($row['discount']!=0)$row['price']=Numeric::discount($row['discount'], $row['price']);
			$param = array($row['price'], $_COOKIE['session_id'], $id, $row['product_id'], $row['code'], $row['discount'], $date, $amount, $size, $color);
			
			$where="";
			if(isset($_SESSION['user_id']))$where=", user_id='{$_SESSION['user_id']}'";
			
			$this->db->query("INSERT into bascket SET price=?, session_id=?, price_id=?, product_id=?, code=?, discount=?, date=?, amount=?, size=?, color=? $where", $param);
		}
		else{
			$this->db->query("UPDATE bascket b SET amount=amount+? WHERE price_id=? $where", array($amount, $id));
		}
		return $this->bascket();
	}
	
	///Bascket shop cart
	function bascket($with_discount=true)
	{
		$vars = array();
		$where = "WHERE ".$this->get_user_id();
		//$vars['currency'] = $this->currency();
		$res = $this->db->rows("SELECT tb_price.`price`, tb_price.`discount`, b.amount
							    FROM `bascket` b 
							   
							    							   
							    LEFT JOIN product p
							    ON p.id=b.product_id
								
							    LEFT JOIN `price` tb_price
						 	    ON `tb_price`.id=b.price_id
							
							    $where
								GROUP BY b.id
								");
		$sum=0;
		$amount=0;
		foreach($res as $row)
		{
			if($row['discount']!=0&&$with_discount)
			{
				$row['price']=Numeric::discount($row['discount'], $row['price']);
			}
			$sum+=$row['price']*$row['amount'];
			$amount+=$row['amount'];
		}
		
		$vars['count'] = $amount;
		$vars['total'] = Numeric::formatPrice($sum);
		return $vars;
	}
	
	function removeFromBasket($id)
    {
		$where="AND ".$this->get_user_id('bascket');
        $this->db->query("DELETE FROM `bascket` WHERE `id` = ? $where", array($id));
		return $this->bascket();
    }
	
	function refreshBasket($count, $pid)
    {
		$where="AND ".$this->get_user_id('bascket');
        $this->db->query("UPDATE `bascket` SET amount=? WHERE id=? $where", array($count, $pid));
		$row = $this->db->row("SELECT `price`, `amount` FROM `bascket` WHERE id='$pid' $where");
		$sum = Numeric::formatPrice($row['price'] * $row['amount']);
        return array_merge($this->bascket(), array('sum'=>$sum));
    }
	
	function showBasket()
    {
        $total=0;
		$vars = $this->bascket(false);
		$vars['product'] = $this->db->rows($this->query_basket());
		$vars['translate']=$this->translation;
        return $vars;
    }
	
	function showUserInfo()
    {
        if(isset($_SESSION['user_id']))
		{
			$vars['translate']=$this->translation;
            $vars['user']=$this->db->row("SELECT * FROM `users` WHERE id=?", array($_SESSION['user_id']));
        }
        return $vars;
    }
	
	function showOrders()
    {
        if(isset($_SESSION['user_id']))
		{
			$vars['translate']=$this->translation;
            $vars['orders'] = $this->db->rows("SELECT tb.id, tb.sum, tb.date_add, tb2.name as status
												FROM `orders` tb
												
												LEFT JOIN orders_status tb2
												ON tb.status_id=tb2.id
		
												WHERE tb.user_id=?
												ORDER BY tb.`date_add` DESC", array($_SESSION['user_id']));

            $vars['user']=$this->db->row("SELECT * FROM `users` WHERE id=?", array($_SESSION['user_id']));
        }
        return $vars;
    }
	
	function showOrdering()
    {
        if(isset($_SESSION['user_id']))
		{
            $vars['user']=$this->db->row("SELECT * FROM `users` WHERE id=?", array($_SESSION['user_id']));
        }
		$vars['translate']=$this->translation;

        ////Delivery
		$row = $this->db->row("SELECT id FROM modules WHERE `controller`=?", array('delivery'));
		if($row)$vars['delivery'] = Delivery::getObject($this->sets)->find(array('type'=>'rows', 'where'=>'__tb.active:=1__', 'order'=>'tb.sort ASC'));
		
		////Payment
		$row = $this->db->row("SELECT id FROM modules WHERE `controller`=?", array('payment'));
		if($row)$vars['payment'] = Payment::getObject($this->sets)->find(array('type'=>'rows', 'where'=>'__tb.active:=1__', 'order'=>'tb.sort ASC'));
        return $vars;
    }
	
	function getOrder($id)
    {
        $vars['order'] = $this->db->row("SELECT tb.*, tb2.name, tb3.name as delivery
										 FROM orders tb
	
										 LEFT JOIN orders_status tb2
										 ON tb.status_id=tb2.id
	
										 LEFT JOIN ".$this->registry['key_lang']."_delivery tb3
										 ON tb.delivery_id=tb3.delivery_id
	
										 WHERE tb.id=? AND tb.user_id=?", array($id, $_SESSION['user_id']));

        if(isset($vars['order']['id']))
        {
			$vars['translate']=$this->translation;
            $vars['product'] = $this->db->rows("SELECT * FROM orders_product WHERE orders_id=?", array($vars['order']['id']));
			return $vars;
        }
    }
	
	function allOrders()
    {
		$vars=array();
		$vars['orders'] = $this->db->rows("SELECT
											tb.id,
											tb.sum,
											tb.date_add,
											tb2.name
										 FROM `orders` tb
											LEFT JOIN
												orders_status tb2
											ON tb.status_id=tb2.id
		
										 WHERE tb.user_id=?
										 ORDER BY tb.`date_add` DESC", array($_SESSION['user_id']));

        $vars['translate'] = $this->translation;
        echo $view->Render('all_orders.phtml', $vars);
    }

    function saveProfile($data)
    {
		if(isset($_SESSION['user_id']))
		{
			$pass_q='';
			$err='';
			if($data['new_pass']!=''&&$data['old_pass']!='')
			{
				$row = $this->db->row("SELECT id FROM `users` WHERE `id`=? AND pass=?", array($_SESSION['user_id'], md5($data['old_pass'])));
				if($row)
				{
					$pass_q=", pass='".md5($data['new_pass'])."'";	
				}
			}

			$err.=Validate::check($data['name']);
			if($err=='')
			{
				$param = array($data['name'], $data['phone'], $data['city'], $data['address'], $data['post_index'], $_SESSION['user_id']);
				$this->db->query("UPDATE `users` SET name=?, phone=?, city=?, address=?, post_index=? $pass_q WHERE id=?", $param);
				return "<div class='done'>".$this->translation['save_profile']."</div>";
			}
			else{
				return $err;
			}
		}
    }

	function get_user_id($tb='b')
    {
		if(isset($_SESSION['user_id']))$ssid = "($tb.user_id='".$_SESSION['user_id']."' OR $tb.session_id='".$_COOKIE['session_id']."')";
        else $ssid = "$tb.session_id='".$_COOKIE['session_id']."'";
		return $ssid;
	}
	
	function query_basket()
	{
		$where="WHERE ".$this->get_user_id('b');
		$q="SELECT b.`amount`,
						b.`id` as cart_id,
						p.id,
						b.code,
						p.url,
						tb_price.`price`,
						p.`photo`,
						tb_price.discount,
						p2.name,
						size.name as size,
						color.name as color
						
			  FROM `bascket` b
			  
			  LEFT JOIN product p
			  ON p.id=b.product_id
								
			  LEFT JOIN `price` tb_price
			  ON `tb_price`.id=b.price_id
								
			  LEFT JOIN ".$this->registry['key_lang']."_product p2
			  ON p.id=p2.product_id
	
			  LEFT JOIN ".$this->registry['key_lang']."_params size
			  ON b.size = size.params_id
	
			  LEFT JOIN ".$this->registry['key_lang']."_params color
			  ON b.color = color.params_id
	
			  $where
			  GROUP BY b.id
			  ";
		return $q;	  
	}
	
	function last_order()
	{
		return $this->db->row("SELECT p.url, p.photo, p2.name, o.city
								  FROM `orders` o
								  
								  LEFT JOIN orders_product op
								  ON op.orders_id=o.id
								  
								  LEFT JOIN product p
								  ON op.product_id=p.id
								  
								  LEFT JOIN ".$this->registry['key_lang']."_product p2
								  ON p.id=p2.product_id
								  
								  WHERE (o.status_id!='3' OR o.status_id!='5') 
								  ORDER BY o.date_add DESC");	
	}
	
	function incomplete($user_id=0)
	{
		## Start Statistics
        $cur_start_date=getdate(mktime(0, 0, 0, date("m")-3, date("d"), date("Y")));
		$cur_end_date = date("Y-m-d H:i:s");
		if(strlen($cur_start_date['mon'])==1)$cur_start_date['mon'] = '0'.$cur_start_date['mon'];
		if(strlen($cur_start_date['mday'])==1)$cur_start_date['mday'] = '0'.$cur_start_date['mday'];
		$cur_start_date = $cur_start_date['year'].'-'.$cur_start_date['mon'].'-'.$cur_start_date['mday'];

		
		if(isset($_POST['start']))
		{
			$_SESSION['date_start'] = $_POST['start'];
			$_SESSION['date_end'] = $_POST['end'];
		}
		elseif(!isset($_SESSION['date_start']))
		{
			$_SESSION['date_start'] = $cur_start_date;
			$_SESSION['date_end'] = $cur_end_date;
		}

		$vars['message'] = '';
		$vars['name'] = 'Незавершенные заказы';
		if(isset($this->params['delete'])||isset($_POST['delete']))
		{
			$vars['message'] = $this->delete('bascket');
		}
		
		$where='';
		if($user_id!=0)$where="AND b.user_id='{$user_id}'";
		$vars['product'] = $this->db->rows("SELECT p.*, p2.name, b.*, u.name AS username, u.email
											FROM `bascket` b 
										   
																		   
											LEFT JOIN product p
											ON p.id=b.product_id
											
											LEFT JOIN ".$this->registry['key_lang_admin']."_product p2
											ON p.id=p2.product_id
											
											LEFT JOIN `price` tb_price
											ON `tb_price`.id=b.price_id
											
											LEFT JOIN users u 
											ON u.id=b.user_id 
											
											WHERE b.`date` BETWEEN '{$_SESSION['date_start']}' AND '{$_SESSION['date_end']}' $where
											
											GROUP BY b.id
											ORDER BY session_id DESC, b.date DESC
											");
											
		return $vars;									
	}
}
?>