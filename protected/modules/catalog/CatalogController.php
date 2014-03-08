<?php
/*
 * вывод каталога компаний и их данных
 */
class CatalogController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "catalog";
		$this->name = "Каталог";
		
		$this->registry = $registry;
        $this->catalog = new Catalog($this->sets);
	}

	public function indexAction()
	{
		$settings = Registry::get('user_settings');
		$vars['message'] = "";
		$data['bread_crumbs'] = "";
		$vars['translate'] = $this->translation; ///Переводы интерфейса
		$param=array();
		$param['join']='';
		$vars['curr_cat']['name'] = $vars['translate']['catalog'];
		
		#Start onpage
        if(!isset($_SESSION['onpage']))$_SESSION['onpage']=9;
		elseif(isset($_POST['onpage'])&&$_POST['onpage']==9)$_SESSION['onpage']=9;
		elseif(isset($_POST['onpage'])&&$_POST['onpage']==18)$_SESSION['onpage']=18;
		elseif(isset($_POST['onpage'])&&$_POST['onpage']==48)$_SESSION['onpage']=48;
		if(!isset($_SESSION['search']))$_SESSION['search']='';
		
		if(!isset($_SESSION['catalog']))$_SESSION['catalog']='';
		if(!isset($_SESSION['sub'])||(!isset($_POST['sub'])&&isset($_POST['sub_hid']))||$_SESSION['catalog']!=$this->params['catalog'])$_SESSION['sub']=array();
		if(isset($_POST['sub']))$_SESSION['sub'] = $_POST['sub'];
		$_SESSION['catalog']=$this->params['catalog'];
		$_SESSION['catalog_contin']=LINK."/catalog/{$this->params['catalog']}";
		#!End onpage

		#Start sort
		if(!isset($_SESSION['sort'])||(isset($_POST['sort'])&&$_POST['sort']==""))$_SESSION['sort']="tb.sort ASC, tb.id desc";
		if(isset($_POST['sort'])&&$_POST['sort']=="price:asc")$_SESSION['sort']="price asc";
		elseif(isset($_POST['sort'])&&$_POST['sort']=="price:desc")$_SESSION['sort']="price desc";
		elseif(isset($_POST['sort'])&&$_POST['sort']=="name:asc")$_SESSION['sort']="name asc";
		elseif(isset($_POST['sort'])&&$_POST['sort']=="name:desc")$_SESSION['sort']="name desc";
		#!End sort
		
		$param['where']='';
		$_SESSION['params']= array();

        /*
         * Получаем фильтры из params в ссылке
         */

        if (isset($this->params['params']))
        {

            $par = explode(';', $this->params['params']);
            $filters = '';

            foreach ($par as $row)
            {
                $arr = explode('-', $row);
                if ($filters != '') $filters .= ',';
                $filters .= $arr[0];
            }

            $filterIds = explode(',', $filters);

            /*
             *Получаем id родителей у фильтров
             */

            foreach ($filterIds as $row) {
                $_SESSION['params'][$row] = $this->db->cell("SELECT `sub` FROM `params` WHERE `id`=?", array($row));
            }
        }


		//Если открыт конкретный каталог

		if(isset($this->params['catalog'])&&$this->params['catalog']!="search"&&$this->params['catalog']!="all")
		{	
			$productStatus = Product_status::getObject($this->sets)->find($this->params['catalog']);
			if($productStatus)
			{
				$param['where']="AND tb.id in(select product_id from product_status_set where status_id='{$productStatus['id']}')";
				$vars['curr_cat']['name'] = $productStatus['name'];
				$data['breadcrumbs'] = array('<a href="'.LINK.'/catalog/all">'.$this->translation['catalog'].'</a>', $productStatus['name']);
			}
			else{
				$catrow = $this->catalog->find(array('where'=>'__tb.active:=1__ AND __tb.url:='.$this->params['catalog'].'__'));
				if(!isset($catrow['id']))return Router::act('error', $this->registry);
				$_SESSION['catalog2']=$catrow['id'];	
				if($catrow)
				{
					$data['breadcrumbs'] = $this->catalog->getBreadCat($catrow);////bread crumbs

					$vars['sub'] = $this->catalog->find(array('select'=>'tb.id2, tb.url, tb_lang.name',
															  'where'=>'__sub:='.$catrow['id'].'__', 
															  'order'=>'sort asc', 
															  'type'=>'rows'));
					$subcat='';
					foreach($vars['sub'] as $row)
					{
						$subcat.="OR tb3.catalog_id='{$row['id']}'";	
					}
					$vars['curr_cat']=$catrow;
					$param['where']="AND (tb3.catalog_id='{$catrow['id']}' $subcat)";

                    // Cоставляем запрос с выбраными фильтрами
                    if(isset($_SESSION['params']) && count($_SESSION['params'])>0 )
                    {
                        $filters = $this->getFilterQueries();
                        $param['where'] .= $filters['where'];
                        $param['join'] .= $filters['join'];
                    }
				}
				else return Router::act('error', $this->registry);			
			}
		}


        //Если открыт общий каталог и бренд

		elseif(isset($this->params['catalog'])&&$this->params['catalog']=="all"&&isset($this->params['brend'])&&$this->params['brend']!="")
		{
			$vars['curr_cat'] = Brend::getObject($this->sets)->find($this->params['brend']);
			$param['where']="AND tb.brend_id='{$vars['curr_cat']['id']}'";

		}


        //Если производится поиск по каталогу

		elseif(isset($this->params['catalog'])&&isset($_POST['search']))
		{
			$_SESSION['search']=$_POST['search'];
			$vars['cat']['search']='ПОИСК "'.$_SESSION['search'].'"';
			$param['where']="AND (tb_lang.name like '%{$_SESSION['search']}%' OR tb_lang.body_m like '%{$_SESSION['search']}%' OR tb_lang.body like '%{$_SESSION['search']}%')";
		}
		$param['where'].=" AND tb.active='1'";
		$param['order']=$_SESSION['sort'];
		$param['order']=str_replace('price', 'tb_price.price', $param['order']);
		$q = $this->catalog->queryProducts($param);


        //загрузка фильтров

		if(isset($vars['curr_cat']['id']))
		{
			$prod = Product::getObject($this->sets)->find($q);
			$params = $this->catalog->getParams($prod, $vars['curr_cat']['id']);
			if(count($params)!=0)
			{
				$filter = $this->view->Render('cat_filters_ajax.phtml', array('params'=>$params, 'translate'=>$vars['translate']));
				$vars['filters_menu'] = $this->view->Render('cat_filters.phtml', array('filter'=>$filter, 'translate'=>$vars['translate']));
			}
			 $vars['subcats'] = $this->catalog->subcats($vars['curr_cat']);
		}
  
		#Start paging
		$vars['list'] = Product::getObject($this->sets)->find(array_merge($q, array("paging"=>$this->settings['paging_product'])));
		if(!$vars['list'])return Router::act('error', $this->registry);
		#!End paging	
		
		$data['styles'] = array('catalog.css');
		$data['content'] = $this->view->Render('catalog.phtml', $vars);
		return $this->Index($data);
	}

    /*
     * Returns array of filtered products and refreshed params
     * @return json
     */

    function getfilterAction()
    {
        $where = '';
        $join = '';
        $data = array();
        $return = array();
        $filterParams = array();

        $categoryId = str_replace('licat_', '', $_POST['cat_id']);
        $data['translate'] = $this->translation;

        $row = $this->db->row("SELECT id, url FROM catalog WHERE id=?", array($categoryId));

        $_SERVER['REQUEST_URI'] = '/catalog/' . $row['url'];
        $_SESSION['catalog2'] = $row['id'];

        if ((isset($_POST['items']) && $_POST['items'] != '') || (isset($_POST['clear_id']) && $_POST['clear_id'] != ''))
        {
            if (isset($_POST['items']) && $_POST['items'] != '')
            {
                $filter = explode(',', $_POST['items']);

                foreach ($filter as $row)
                {
                    $tmp = explode("|", $row);
                    $filterParams[$tmp[0]] = $tmp[1];
                }
            }

            /* Удаление выбраного параметра */

            if (isset($_POST['clear_id'])) {
                unset($_SESSION['params'][(int)$_POST['clear_id']]);
                $filterParams = $_SESSION['params'];
            }

            $_SESSION['params'] = $filterParams;

            /* Если есть выбраные параметры  */

            if (isset($_SESSION['params']) && count($_SESSION['params']) > 0) {

                $filters = $this->getFilterQueries();
                $where .= $filters['where'];
                $join .= $filters['join'];
            }

        }
        elseif (($_POST['items'] == '') && (!isset($_POST['clear_id'])))
        {
            $_SESSION['params'] = array();
        }

        //Условие where с подкаталогами для запроса продуктов
        if ($categoryId != '') $where .= " AND tb3.catalog_id='{$categoryId}'";
        else $where .= " AND tb3.catalog_id!='{$categoryId}'";


        $productObj = new Product($this->sets);
        $q = $this->catalog->queryProducts(array('where' => $where, 'join' => $join, 'sort' => $_SESSION['sort']));

        $data['prod'] = $productObj->find($q);
        $data['params'] = $this->catalog->getParams($data['prod'], $categoryId);

        $data['list'] = $productObj->find(array_merge($q, array("paging" => $this->settings['paging_product'])));

        $return['product'] = $this->view->Render('catalog_ajax.phtml', $data);
        $return['filters'] = $this->view->Render('cat_filters_ajax.phtml', $data);

        return json_encode($return);
    }

    /*
     * Creates query for product filters
     * @return array
     */

    private function getFilterQueries()
    {

        $param = array();
        $joinGroups = array();

        $param['where'] = '';
        $param['join'] = '';

        foreach ($_SESSION['params'] as $id => $group) {

            if (!in_array($group, $joinGroups)) {
                array_push($joinGroups, $group);

                // Подключаем группы параметров
                $param['join'] .= " INNER JOIN params_product pgroup_" . $group . " ON tb.id = pgroup_" . $group . ".product_id ";
            }
        }

        foreach ($joinGroups as $row) {

            $subWhere = '';
            $param['where'] .= " AND "; // между группами - "И"

            foreach ($_SESSION['params'] as $id => $group) {
                if ($group == $row)
                {
                    if ($subWhere != '') $subWhere .= " OR "; // внутри группы - ИЛИ
                    $subWhere .= " pgroup_" . $group . ".params_id = '" . $id . "' ";
                }
            }

            $param['where'] .= "(" . $subWhere . ")";
        }

        return $param;
    }
}