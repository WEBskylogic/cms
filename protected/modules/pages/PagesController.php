<?php
/*
 * вывод каталога компаний и их данных
 */
class PagesController extends BaseController{
	
       protected $params;
       protected $db;

       function  __construct($registry, $params)
	   {
            $this->tb = "pages";
			$this->registry = $registry;
			parent::__construct($registry, $params);
       }

       public function indexAction()
	   {
           $view = new View($this->registry);
           $settings = Registry::get('user_settings');
           $vars['translate'] = $this->translation;
           $vars['body'] = $this->getPage($this->params['topic']);
           if(!$vars['body'])return Router::act('error', $this->registry);

           if($vars['body']['form']==1)$vars['form'] = $view->Render('feedback.phtml',	$vars);
		   elseif($vars['body']['form']==2)
		   {
			   $vars['type_comment'] = $vars['body']['type'];
			   $vars['id'] = $vars['body']['id'];
			   $vars['comments'] = $this->db->rows("SELECT * FROM `comments` WHERE content_id=? AND active=? AND type=? ORDER  BY date DESC", array($vars['body']['id'], 1, $vars['type_comment']));
			   $vars['form'] = $view->Render('comments.phtml',	$vars);
			   $data['styles'] = array('comments.css');
		   }

           ////Meta
           $data['meta'] = $vars['body'];
		   $data['breadcrumbs'] = array($vars['body']['name']);
           $data['content'] = $view->Render('body.phtml', $vars);
           return $this->Render($data);
		}
}
?>