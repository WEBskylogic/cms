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
		$this->name = "Заказы";
		$this->registry = $registry;
		parent::__construct($registry, $params);
	}

	public function indexAction()
	{
		$vars['message'] = '';
		$vars['name'] = $this->name;
		if(isset($this->registry['access']))$vars['message'] = $this->registry['access'];
		if(isset($this->params['delete'])||isset($_POST['delete']))$vars['message'] = $this->delete();
		elseif(isset($_POST['update']))$vars['message'] = $this->save();
		elseif(isset($_POST['update_close']))$vars['message'] = $this->save();
		elseif(isset($_POST['add_close']))$vars['message'] = $this->add();
		
		$view = new View($this->registry);
		$vars['list'] = $view->Render('view.phtml', $this->listView());
		$data['content'] = $view->Render('list.phtml', $vars);
		return $this->Render($data);
	}
	
	public function addAction()
	{
		$vars['message'] = '';
		if(isset($_POST['add']))$vars['message'] = $this->add();
		
		////Delivery
		$row = $this->db->row("SELECT id FROM module WHERE `controller`=?", array('delivery'));
		if($row)$vars['delivery'] = $this->db->rows("SELECT * FROM `delivery` WHERE active=? ORDER  BY sort ASC", array(1));
		
		////Payment
		$row = $this->db->row("SELECT id FROM module WHERE `controller`=?", array('payment'));
		if($row)$vars['payment'] = $this->db->rows("SELECT * FROM `payment` WHERE active=? ORDER  BY sort ASC", array(1));
		$vars['list'] = $this->listView();
		$view = new View($this->registry);
		$data['content'] = $view->Render('add.phtml', $vars);
		return $this->Render($data);
	}
	
	public function editAction()
	{
		//if($vars['message']!='')return Router::act('error');
		$vars['message'] = '';
		
		if(isset($this->params['delete']))
		{
			$this->db->query("DELETE FROM `orders_product` WHERE id=?", array($this->params['delete']));
			$this->recalc($this->params['edit']);
		}
		
		if(isset($_POST['update']))$vars['message'] = $this->save();
		$vars['status'] = $this->db->rows("SELECT * FROM orders_status");
		$vars['product'] = $this->db->rows("SELECT * FROM orders_product WHERE orders_id=?", array($this->params['edit']));
		$vars['catalog'] = $this->db->rows("SELECT tb.id, tb.sub, tb2.name 
											FROM catalog tb
											
											LEFT JOIN ".$this->key_lang."_catalog tb2
											ON tb.id-tb2.cat_id
											
								 			ORDER BY tb.sort ASC");
		$vars['edit'] = $this->db->row("SELECT 
											tb.*
										FROM ".$this->tb." tb
										WHERE
											tb.id=?",
										array($this->params['edit']));
		$vars['list'] = $this->listView();
		////Delivery
		$row = $this->db->row("SELECT id FROM module WHERE `controller`=?", array('delivery'));
		if($row)$vars['delivery'] = $this->db->rows("SELECT * FROM `delivery` WHERE active=? ORDER  BY sort ASC", array(1));
		
		////Payment
		$row = $this->db->row("SELECT id FROM module WHERE `controller`=?", array('payment'));
		if($row)$vars['payment'] = $this->db->rows("SELECT * FROM `payment` WHERE active=? ORDER  BY sort ASC", array(1));
		$view = new View($this->registry);
		$data['content'] = $view->Render('edit.phtml', $vars);
		return $this->Render($data);
	}
	
	
	private function add()
	{
		$message='';
		if(isset($_POST['email'], $_POST['username'], $_POST['phone'], $_POST['country'], $_POST['city'])&&$_POST['username']!="")
		{
            $err="";
			if($err=="")
            {
                $date_add=date("Y-m-d H:i:s");
                $id=$this->db->insert_id("INSERT INTO `".$this->tb."` SET
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
				if(isset($_POST['delivery']))$this->db->query("UPDATE orders SET delivery=? WHERE id=?", array($_POST['delivery'], $id));	
				if(isset($_POST['payment']))$this->db->query("UPDATE orders SET payment=? WHERE id=?", array($_POST['payment'], $id));
	
                $message.= messageAdmin('Данные успешно добавлены');
            }
            else $message.= messageAdmin($err, 'error');
		}
		else $message.= messageAdmin('При добавление произошли ошибки', 'error');	
		return $message;
	}
	
	
	private function save()
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
					
                    $this->db->insert_id("UPDATE `".$this->tb."` 
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
					if(isset($_POST['delivery']))$this->db->query("UPDATE orders SET delivery=? WHERE id=?", array($_POST['delivery'], $_POST['id']));	
					if(isset($_POST['payment']))$this->db->query("UPDATE orders SET payment=? WHERE id=?", array($_POST['payment'], $_POST['id']));
					
                    $message.= messageAdmin('Данные успешно сохранены');
                }
            }
            else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
		}
		return $message;
	}
	
	private function delete()
	{
		$message='';
		if(isset($this->registry['access']))$message = $this->registry['access'];
		else
		{
			if(isset($_POST['id'])&&is_array($_POST['id']))
			{
				for($i=0; $i<=count($_POST['id']) - 1; $i++)
				{
					$this->db->query("DELETE FROM `".$this->tb."` WHERE `id`=?", array($_POST['id'][$i]));
				}
				$message = messageAdmin('Запись успешно удалена');
			}
			elseif(isset($this->params['delete'])&& $this->params['delete']!='')
			{
				$id = $this->params['delete'];
				if($this->db->query("DELETE FROM `".$this->tb."` WHERE `id`=?", array($id)))$message = messageAdmin('Запись успешно удалена');
			}
		}
		return $message;
	}
	
	private function listView()
	{
		$size_page =10;
        $start_page = 0;
        $cur_page = 0;
        $vars['paging'] = '';

        if(isset($this->params['page']))
        {
            $cur_page = $this->params['page'];
            $start_page = ($cur_page-1) * $size_page;//номер начального элемента
        }
		
		$q="SELECT
				tb.*,
				tb2.name as status
			 FROM ".$this->tb." tb
				LEFT JOIN
					orders_status tb2
				ON tb.status_id=tb2.id

			 ORDER BY tb.`date_add` DESC";
        $sql = $q." LIMIT ".$start_page.", ".$size_page."";
        //echo $sql;
        $count = $this->db->query($q);//кол страниц
        if($count > $size_page)
        {
            $vars['paging'] = Paging::MakePaging($cur_page, $count, $size_page, $dir="admin_");//вызов шаблона для постраничной навигации
        }
        $vars['list'] = $this->db->rows($sql);
		return $vars;
	}
	
	function recalc($order_id)
	{
		$total=0;
		$res = $this->db->rows("SELECT * FROM orders_product WHERE orders_id=?", array($order_id));
		foreach($res as $row)
		{
			$sum=$row['price']*$row['amount'];
			$total+=$sum;
			
		}	
		$this->db->query("UPDATE `orders` SET `amount`=?, `sum`=? WHERE `id`=?", array(count($res)-1, $total, $order_id));
	}
}
?>