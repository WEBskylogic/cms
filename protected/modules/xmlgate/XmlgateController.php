<?php
/*
 * XML шлюз
 */
class XmlgateController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);

		$this->tb_users = "users";
        $this->tb_orders = "orders";
        $this->tb_feedback = "feedback";
		$this->registry = $registry;
	}
	
	public function indexAction() 
	{
		$settings = Registry::get('user_settings');
		$vars['translate'] = $this->translation;
		
		$moderator = $this->db->row("SELECT `id` FROM `moderators` WHERE login=? AND password=?", array($_COOKIE['login'], $_COOKIE['password']));

        $users_limit = 99;
        $orders_limit = 99;
        $fdb_limit = 99;
		
		$xml = new DomDocument('1.0', 'UTF-8');
		$rows = $xml->appendChild($xml->createElement('rows'));
		
		$arr1 = $this->showLastUsers($users_limit);
		$arr2 = $this->showLastOrders($orders_limit);
		$arr3 = $this->showLastFeedback($fdb_limit);
		
		$ALL = array_merge($arr1, $arr2, $arr3);

		foreach($ALL as $row_)
		{
			 $row = $rows->appendChild($xml->createElement('row'));
			 $row_type = $row->appendChild($xml->createElement('row_type'));
			 $type = $row_type->appendChild($xml->createElement('type'));
			 $type->appendChild($xml->createTextNode($row_['_type']));
			 $typename = $row_type->appendChild($xml->createElement('typename'));
			 $typename->appendChild($xml->createTextNode($row_['table']));
			 $ico = $row_type->appendChild($xml->createElement('ico'));
			 $ico->appendChild($xml->createTextNode('http://'.$_SERVER['HTTP_HOST'].'/images/ico/'.$row_['_type'].'.png'));
			 $tema = $row_type->appendChild($xml->createElement('tema'));
			 $tema->appendChild($xml->createTextNode($row_['name']));
			 $url = $row_type->appendChild($xml->createElement('url'));
			 $url->appendChild($xml->createTextNode('http://'.$_SERVER['HTTP_HOST'].'/admin/'.$row_['_type'].'/edit/'.$row_['id'].''));
			 $row_buttons = $row->appendChild($xml->createElement('buttons'));
			 $row_properties = $row->appendChild($xml->createElement('row_properties'));
			 $number = $row_properties->appendChild($xml->createElement('number'));
			 $number->appendChild($xml->createTextNode($row_['id']));
			 $contractor = $row_properties->appendChild($xml->createElement('contractor'));
			 $contr_id = $contractor->appendChild($xml->createElement('contractor_id'));
			 $contr_id->appendChild($xml->createTextNode($moderator['id']));
			 $contr_name = $contractor->appendChild($xml->createElement('contractor_name'));
			 $contr_name->appendChild($xml->createTextNode($settings['sitename']));
			 $new = $row_properties->appendChild($xml->createElement('new'));
			 $new->appendChild($xml->createTextNode('1'));
			 $name = $row_properties->appendChild($xml->createElement('name'));
			 $name->appendChild($xml->createTextNode($row_['name']));
			 $date = $row_properties->appendChild($xml->createElement('date'));
			 $date->appendChild($xml->createTextNode($row_['date']));
		}

        $xml->formatOutput = true;
        $vars['xml'] = $xml->saveXML();
		
        $view = new View($this->registry);
        $data = $view->Render('xml.phtml', $vars);
		return $data;

	}

    private function showLastUsers($limit)
    {
        return $this->db->rows("SELECT tb.*, 
								tb.`start_date` as 'date', 
								m.name as 'table', 
								m.controller as _type 
								FROM `".$this->tb_users."` tb
								LEFT JOIN `modules` m ON m.controller = 'users'
								WHERE tb.`datashow` = '0'
								ORDER BY tb.`start_date` DESC LIMIT ".$limit."");
    }

    private function showLastOrders($limit)
    {
        return $this->db->rows("SELECT tb.*, 
								tb.`username` as 'name', 
								tb.`date_add` as date, 
								m.name as 'table', 
								m.controller as '_type' 
								FROM `".$this->tb_orders."` tb
								LEFT JOIN `modules` m ON m.controller = 'orders'
								WHERE tb.`datashow` = '0'
								ORDER BY tb.`date_add` DESC LIMIT ".$limit."");
    }

    private function showLastFeedback($limit)
    {
        return $this->db->rows("SELECT tb.*, 
								m.name as 'table', 
								m.controller as '_type' 
								FROM `".$this->tb_feedback."` tb
								LEFT JOIN `modules` m ON m.controller = 'feedback'
								WHERE tb.`datashow` = '0'
								ORDER BY tb.`date` DESC LIMIT ".$limit."");
    }
}
?>