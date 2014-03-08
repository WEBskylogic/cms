<?php

class Paging extends BaseController{
    protected $params;
    protected $db;

    function  __construct($registry, $params)
    {
        $this->registry = $registry;
        parent::__construct($registry, $params);
    }

	public function MakePaging($page, $itemCount, $perSlade, $dir="") {

		$vars['link_padding'] = 2;
        $vars['page_var'] = "page";
		$vars['page_var_prev'] = "<<";
		$vars['page_var_next'] = ">>";
        $vars['count'] = ceil($itemCount/$perSlade);
		$vars['page'] = $page;
		if($page==0)$vars['page']=1;
		$vars['page_size'] = $perSlade;
		return $this->view->Render($dir.'paging.phtml', $vars);
	}

}
?>