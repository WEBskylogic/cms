<?php
/*
 * вывод каталога компаний и их данных
 */
class VideoController extends BaseController{
	
       protected $params;
       protected $db;

       function  __construct($registry, $params)
	   {
            $this->tb = "video";
			$this->registry = $registry;
			parent::__construct($registry, $params);
       }

       public function indexAction()
	   {
           $view = new View($this->registry);
           $settings = Registry::get('user_settings');
           $vars['translate'] = $this->translation;
           $vars['video'] = $this->db->rows("SELECT tb1.*, tb2.* FROM `".$this->tb."` tb1
                                             LEFT JOIN ".$this->key_lang."_".$this->tb." tb2
                                             ON tb1.id=tb2.".$this->tb."_id
                                             WHERE tb1.active=?
                                             ORDER BY tb1.`sort` ASC",
           array(0));
           $vars['body'] = $this->db->row("SELECT tb.body FROM `".$this->key_lang."_pages` tb LEFT JOIN pages tb2 ON tb.pages_id=tb2.id WHERE tb2.url=?", array('video'));
           $vars['translate'] = $this->translation;
           $data['content'] = $view->Render($this->tb.'.phtml', $vars);
           return $this->Render($data);
		}
}
?>