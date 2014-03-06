<?php
/*
 * вывод каталога компаний и их данных
 */
class MailerController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "mailer";
		$this->registry = $registry;
		$this->mailer = new Mailer($this->sets);
	}
	
	public function indexAction()
	{
		$vars['translate'] = $this->translation;

        if(isset($this->params[$this->tb], $this->params['code'])&&$this->params[$this->tb]=='unsubscribe'&&$this->params['code']!='')
        {
            $vars['message'] = $this->mailer->unsubscribe($this->params['code']);
        }
		
		$data['content'] = $this->view->Render('message.phtml', $vars);
		return $this->Index($data);
	}
	
	//////Subscribers
	function subscribersAction()
	{
		if(isset($_POST['name'], $_POST['email']))
		{
			return json_encode($this->mailer->subscriber($_POST['name'], $_POST['email']));
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
}
?>