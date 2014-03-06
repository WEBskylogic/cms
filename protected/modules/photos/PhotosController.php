<?php
/*
 * вывод каталога компаний и их данных
 */
class PhotosController extends BaseController{

    protected $params;
    protected $db;

    function  __construct($registry, $params)
    {
		parent::__construct($registry, $params);
        $this->tb = "photos";
        $this->registry = $registry;
        $this->photos = new Photos($this->sets);
    }

    public function indexAction()
    {
        $vars['translate'] = $this->translation;
        if(!isset($this->params[$this->tb]))header("Location: ".LINK."/".$this->tb."/all");

        if(!isset($this->params[$this->tb])||$this->params[$this->tb]=='all')
        {
            $vars['list'] = $this->photos->find(array('where'=>'__tb.active:=1__',
                'paging'=>$this->settings['paging_photos'],
                'order'=>'tb.sort ASC'));

			$data['breadcrumbs'] = array($this->translation['photos']);
        }
        else{
            $vars['photos'] = $this->photos->find(array('where'=>'__tb.url:='.$this->params[$this->tb].'__ AND __tb.active:=1__'));
			if(!isset($vars['photos']['id']))return Router::act('error', $this->registry);
            
			$vars['photo'] = $this->photos->find(array('where'=>'__tb.active:=1__ AND __tb.photos_id:='.$vars['photos']['id'].'__',
														'table'=>'photo',
														'paging'=>$this->settings['paging_photo'],
														'order'=>'tb.sort ASC'));

			$data['breadcrumbs'] = array('<a href="'.LINK.'/photos/all">'.$this->translation['photos'].'</a>', $vars['photos']['name']);
        }

        $data['content'] = $this->view->Render($this->tb.'.phtml', $vars);
        return $this->Index($data);
    }
}
?>