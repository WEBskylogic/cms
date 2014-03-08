<?php
/**
 * class to auntificate admin
 * @author
 */
class IndexAdminController extends BaseController {

    protected $params;
    protected $db;

    function  __construct($registry, $params)
    {
		parent::__construct($registry, $params);
        $this->tb = "comments";
        $this->name = "Комментарии";
        $this->registry = $registry;
    }

    function indexAction()
    {
        $vars['admin'] = 'admin';
        $data['styles'] = array('jquery.simple-dtpicker.css');
		$data['scripts'] = array('jquery.simple-dtpicker.js');

        ## Start Statistics
        $cur_start_date=getdate(mktime(0, 0, 0, date("m")-1, date("d"), date("Y")));
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
		$date_start =  $_SESSION['date_start'];
		$date_end =  $_SESSION['date_end'];
		
		////////////////
		if(isset($_POST['start2']))
		{
			$_SESSION['date_start2'] = $_POST['start2'];
			$_SESSION['date_end2'] = $_POST['end2'];
		}
		elseif(!isset($_SESSION['date_start2']))
		{
			$_SESSION['date_start2'] = $cur_start_date;
			$_SESSION['date_end2'] = $cur_end_date;
		}
		$date_start2 =  $_SESSION['date_start2'];
		$date_end2 =  $_SESSION['date_end2'];
		
		
		///////////
		if(isset($_POST['start3']))
		{
			$_SESSION['date_start3'] = $_POST['start3'];
			$_SESSION['date_end3'] = $_POST['end3'];
		}
		elseif(!isset($_SESSION['date_start3']))
		{
			$_SESSION['date_start3'] = $cur_start_date;
			$_SESSION['date_end3'] = $cur_end_date;
		}
		$date_start3 =  $_SESSION['date_start3'];
		$date_end3 =  $_SESSION['date_end3'];
		
		///////////
		if(isset($_POST['start4']))
		{
			$_SESSION['date_start4'] = $_POST['start4'];
			$_SESSION['date_end4'] = $_POST['end4'];
		}
		elseif(!isset($_SESSION['date_start4']))
		{
			$_SESSION['date_start4'] = $cur_start_date;
			$_SESSION['date_end4'] = $cur_end_date;
		}
		$date_start4 =  $_SESSION['date_start4'];
		$date_end4 =  $_SESSION['date_end4'];

		
		$check = $this->db->row("SELECT id FROM modules WHERE controller='orders'");
		if($check)
		{
			$vars['list'] = $this->db->rows("SELECT 
												date_add, sum
												FROM `orders`
												WHERE `date_add` BETWEEN '$date_start' AND '$date_end'
												ORDER BY `date_add`");
			
			$vars['list2'] = $this->db->rows("SELECT 
												date_add, sum
												FROM `orders`
												WHERE `date_add` BETWEEN '$date_start2' AND '$date_end2'
												ORDER BY `date_add`");
												
			$bascket = $this->db->rows("SELECT 
												date, SUM(amount) AS amount
												FROM `bascket`
												WHERE `date` BETWEEN '$date_start4' AND '$date_end4'
												
												GROUP BY date
												ORDER BY `date`");	
			
			$vars['bascket_count']=0;
			$vars['bascket'] = "['Дата', 'Кол-во']";								
			foreach($bascket as $row)
			{
				$date = date("d.m.Y", strtotime($row['date']));
				$vars['bascket'].=",['".$date."', ".$row['amount']."]";
				$vars['bascket_count']+=$row['amount'];
			}
			
			$orders_sum_array = "['Дата', 'Сумма']";
			$orders_array = "['Дата', 'Кол-во']";
			
			$total_sum = 0;
	
			$order_sum = array();
			$order_count = array();
			
			foreach($vars['list'] as $Rows)
			{
				$date = date("d.m.Y", strtotime($Rows['date_add']));
				$sum = (int)$Rows['sum'];
				if(!isset($order_sum[$date]))$order_sum[$date] = $Rows['sum'];
				else $order_sum[$date] = $order_sum[$date] + $sum;
			}
			
			foreach($vars['list2'] as $Rows)
			{
				$i = 0;
				$i++;
				$date = date("d.m.Y", strtotime($Rows['date_add']));
				if(!isset($order_count[$date]))$order_count[$date] = $i;
				else $order_count[$date] = $order_count[$date] + $i;	
			}
			
			
			//var_info($bascket_count);
			if (sizeof($order_sum) == 0)
			{
				$orders_sum_array.=",\n[0, 0]";
			}
			else{
				foreach($order_sum as $key=>$value)
				{
					$date = date("d.m.Y", strtotime($key));
					$sum = (int)$value;
					$orders_sum_array.=",\n['{$date}', {$sum}]";
					$total_sum += (int)$value;
				}
			}
	
			if (sizeof($order_count) == 0)
			{
				$orders_array.=",\n[0, 0]";
			}
			else{
				foreach($order_count as $key=>$value)
				{
					$date = date("d.m.Y", strtotime($key));
					$sum = (int)$value;
					$orders_array.=",\n['{$date}', {$sum}]";
				}
			}

			$vars['total_sum'] = $total_sum;
			$vars['text'] = $orders_sum_array;
			$vars['text2'] = $orders_array;
		}

        $vars['feedback'] = $this->db->rows("SELECT 
                                            date
                                            FROM `feedback`
                                            WHERE `date` BETWEEN '$date_start3' AND '$date_end3'
                                            ORDER BY `date`");

		
		////Feedback
        $feedback_array = "['Дата', 'Кол-во']";
        $feedback_count = array();
        
        foreach($vars['feedback'] as $Rows)
		{
			$i = 0;
			$i++;
			$date = date("d.m.Y", strtotime($Rows['date']));
			if(!isset($feedback_count[$date]))
					$feedback_count[$date] = $i;
			else $feedback_count[$date] = $feedback_count[$date] + $i;	
        }

        if (sizeof($feedback_count) == 0)
		{
            $feedback_array.=",\n[0, 0]";
        }
        else{
			foreach($feedback_count as $key=>$value)
			{
				$date = date("d.m.Y", strtotime($key));
				$sum = (int)$value;
				$feedback_array.=",\n['{$date}', {$sum}]";
			}
        }
        
        $vars['start'] = $date_start;
        $vars['end'] = $date_end;
        $vars['start2'] = $date_start2;
        $vars['end2'] = $date_end2;
        $vars['start3'] = $date_start3;
        $vars['end3'] = $date_end3;
		$vars['start4'] = $date_start4;
        $vars['end4'] = $date_end4;
        
        $vars['text3'] = $feedback_array;
	
        $vars['key']=$this->key_lang_admin;

		$check = $this->db->row("SELECT id FROM modules WHERE controller='users'");
		if($check)
		{
			$vars['rowsuser']=$this->db->rows("SELECT user_status.`name` as comment, user_status.`id`,
							(SELECT count(id) FROM `users` WHERE `users`.`status_id`=  user_status.`id`)  as col , 
							(SELECT count(id) FROM `users`)  as 'summa' FROM user_status ");
		}

        $vars['rowsmadem']=$this->db->rows("SELECT moderators_type.`comment` as 'name' , moderators_type.`id`,
                        (SELECT count(`moderators`.id)   FROM `moderators` Where `moderators`.`type_moderator`=  moderators_type.`id` )  as col , 
                        (SELECT count(id)    FROM `moderators`  )  as 'summa'                             
                        FROM moderators_type ");        

		$check = $this->db->row("SELECT id FROM modules WHERE controller='catalog'");
		if($check)
		{
			$vars['rowstovar']=$this->db->row("SELECT  
					( select  count(id) from product ) as 'all',
					( select  count(id) from product where  `active`='1') as 'active1',
					( select  count(id) from product where `active`='0') as 'active0'                       
	
			");     
	
			$vars['rowscatalog']=$this->db->row("SELECT  
					( select  count(id) from catalog ) as 'all',
					( select  count(id) from catalog where `active`='1') as 'active1',
					( select  count(id) from catalog where `active`='0') as 'active0'                       
	
			");        
		}
        ## End Statistics		
        $data['content'] = $this->view->Render('adm_main.phtml', $vars);
        return $this->Index($data);
	}
}
?>