<?php
/*
 * вывод каталога компаний и их данных
 */
class DeliveryController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "delivery";
		$this->registry = $registry;
		$this->delivery = new Delivery($this->sets);
	}
	
	
	//////Delivery price
	function deliverypriceAction()
	{
		if(isset($_POST['id']))
		{
			$row=$this->db->row("SELECT * FROM `delivery` WHERE `id`=?", array($_POST['id']));
			if($row['price']!=0.00)
			{
				$price = Numeric::viewPrice($row['price']);
				echo"+ ".$price['price'];
			}
		}
	}
	
}
?>