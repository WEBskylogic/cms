<?php
/*
 * вывод каталога компаний и их данных
 */
class PagesController extends BaseController {
	
       protected $params;
       protected $db;

       function  __construct($registry, $params)
	   {
			parent::__construct($registry, $params);
			$this->tb = "pages";
			$this->registry = $registry;
			$this->pages = new Pages($this->sets);
       }

       public function indexAction()
	   {
           $vars['translate'] = $this->translation;
		   $vars['message']='';
           $vars['body'] = $this->model->getPage($this->params['topic']);
           if(!isset($vars['body']['form']))return Router::act('error', $this->registry);
			
		   if(isset($_POST['f_name']))
			{
				$error = $this->pages->feedback($_POST['f_name'], $_POST['f_email'], $_POST['f_phone'], $_POST['f_text'], $_POST['f_captcha']);
				$vars['message'] = $error['message'];
			}
			
			
           if($vars['body']['form']==1)
		   {
			   $data['styles'] = array('validationEngine.jquery.css', 'user.css');
			   if($this->key_lang=='ru')$scr='jquery.validationEngine-ru.js';
			   else $scr='jquery.validationEngine-en.js';
			   
        	   $data['scripts'] = array('jquery.validationEngine.js', $scr);
			   $vars['form'] = $this->view->Render('feedback.phtml',	$vars);
		   }
		   elseif($vars['body']['form']==2)
		   {
			   $vars['form'] = Comments::getObject($this->sets)->list_comments($vars['body']['id'], $vars['body']['type']);
			   $data['styles'] = array('comments.css');
			   $data['comments'] = 1;
			   $this->catalog = new Catalog($this->sets);
		   }

		   $data['breadcrumbs'] = array($vars['body']['name']);
           $data['content'] = $this->view->Render('body.phtml', $vars);
           return $this->Index($data);
		}
		
		
		public function sendfeedbackAction()
		{
			$error="";
			if(!Captcha3D::check($_POST['captcha']))$error.="<div class='err'>".$this->translation['wrong_code']."</div>";
			$error.=Validate::check($_POST['email'], $this->translation, 'email');
			$error.=Validate::check(array($_POST['name'], $_POST['text'], $_POST['captcha']), $this->translation);
			if($error=="")
			{
				$vars['send']=1;
			}
			else $vars['message']=$error;
			return json_encode($vars);
		}
		
		public function captchaAction()
		{
			$validateValue=$_REQUEST['fieldValue'];
			$validateId=$_REQUEST['fieldId'];
			
			
			$validateError= "This username is already taken";
			$validateSuccess= "This username is available";
			
			
			
			/* RETURN VALUE */
			$arrayToJs = array();
			$arrayToJs[0] = $validateId;
			
			if(Captcha3D::check($validateValue))// validate??
			{
				$arrayToJs[1] = true;	// RETURN TRUE
				echo json_encode($arrayToJs);	// RETURN ARRAY WITH success
			}
			else{
				for($x=0;$x<1000000;$x++)
				{
					if($x == 990000)
					{
						$arrayToJs[1] = false;
						echo json_encode($arrayToJs);	// RETURN ARRAY WITH ERROR
					}
				}
			}
		}
}
?>