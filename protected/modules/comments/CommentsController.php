<?php
/*
 * вывод каталога компаний и их данных
 */
class CommentsController extends BaseController{
	
       protected $params;
       protected $db;

       function  __construct($registry, $params)
	   {
            $this->tb = "comments";
			$this->registry = $registry;
			parent::__construct($registry, $params);
       }

       public function indexAction($id, $db)
	   {
           $view = new View($this->registry);
           $settings = Registry::get('user_settings');
           $vars['translate'] = $this->translation;
           $vars['body'] = $db->rows("SELECT tb1.* FROM `comments` tb1
                                            WHERE tb1.content_id=? AND tb1.active=?
                                            ORDER BY tb1.`date` DESC",
           array($id, 1));
           $data['content'] = $view->Render('body.phtml', $vars);
           return $this->Render($data);
		}
}
?>