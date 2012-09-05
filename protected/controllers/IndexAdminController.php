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
        $this->tb = "comments";
        $this->name = "Комментарии";
        $this->registry = $registry;
        parent::__construct($registry, $params);
    }

	function indexAction()
    {
        $vars['admin'] = 'admin';
        $view = new View($this->registry);
        $data['content'] = $view->Render('adm_main.phtml', $vars);
        return $this->Render($data);
	}
}
?>