<?php
/**
 * class to auntificate admin
 * @author
 */
class IndexController extends BaseController {
	protected $params;
    function __construct ($registry, $params)
	{
        $this->registry = $registry;
		$this->tb_p = "product";
		$this->tb_lang_p = $this->key_lang.'_'.$this->tb_p;
        parent::__construct($registry, $params);
    }

	function indexAction()
    {
		$vars['translate'] = $this->translation;///Переводы интерфейса
        $view = new View($this->registry);
        $vars['slider'] = $this->db->rows("SELECT * FROM slider WHERE active=? ORDER BY sort ASC", array(1));
		$vars['slider'] = $view->Render('slider.phtml', $vars);
		
        $vars['body'] = $this->getPage('/');
		$vars['top_ban'] = $this->getBlock(6);
		$vars['bot_ban'] = $this->getBlock(7);
		
		include($_SERVER['DOCUMENT_ROOT'].'/protected/modules/catalog/CatalogController.php');///Include catalog controllers
		
		////Top products
		$vars['product_n']=$this->db->rows(CatalogController::query_products('AND tb4.status_id=?'), array(2));
		$vars['product_t']=$this->db->rows(CatalogController::query_products('AND tb4.status_id=?'), array(3));
		$vars['product_r']=$this->db->rows(CatalogController::query_products('AND tb4.status_id=?'), array(4));

        $data['meta'] = $vars['body'];
        $data['styles'] = array('slider.css');
        $data['scripts'] = array('slider.js');
        $data['content'] = $view->Render('main.phtml', $vars);
		return $this->Render($data);
	}
}
?>