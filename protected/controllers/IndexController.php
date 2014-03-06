<?php
/**
* class to auntificate admin
* @author
*/
 
class IndexController extends BaseController{

    protected $params;
    protected $registry;

    function __construct ($registry, $params)
    {
        parent::__construct($registry, $params);
        $this->registry = $registry;
    }

    function indexAction()
    {
		$vars['catalog'] = Catalog::getObject($this->sets)->find(array(
										'where'=>'__tb.sub:=9__',
										'type'=>'rows',
										'order'=>'tb.sort ASC'));

		$vars['body'] = $this->model->getPage('/');

		////Top products
		$q=Catalog::getObject($this->sets)->queryProducts(array('where'=>"AND tb.status_id='2'"));
		$vars['product_h'] = Product::getObject($this->sets)->find(array_merge($q, array("limit"=>5)));

        $vars['brends'] = Brend::getObject($this->sets)->find(array('where'=>"tb.sub is null", 'paging'=>true));
        $vars['collections'] = Brend::getObject($this->sets)->find(array('where'=>"tb.sub is not NULL", 'paging'=>true));
		
		$vars['block_9'] = $this->model->getBlock(9);
        //$data['meta'] = $vars['body'];
		
		$slider = Slider::getObject($this->sets)->find(array('where'=>"tb.active='1'", 'type'=>'rows', 'order'=>'tb.sort ASC'));
		$vars['slider'] = $this->view->Render('slider.phtml', array('slider'=>$slider, 'translate'=>$this->translation, 'settings'=>$this->settings));
        $data['styles'] = array('jquery-anyslider.css');
        $data['scripts'] = array('jquery.anyslider.js');
        $data['content'] = $this->view->Render('main.phtml', $vars);
        return $this->Index($data);
	}
}
?>