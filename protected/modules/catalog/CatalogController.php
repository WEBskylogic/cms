<?php
/*
 * вывод каталога компаний и их данных
 */
class CatalogController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		$this->tb = "catalog";
		$this->name = "Каталог";
		$this->tb_lang = $this->key_lang.'_'.$this->tb;
		
		$this->tb_p = "product";
		$this->tb_lang_p = $this->key_lang.'_'.$this->tb_p;
		
		$this->registry = $registry;
		parent::__construct($registry, $params);
	}

	public function indexAction()
	{
		$view = new View($this->registry);
		$vars['message'] = "";
		$data['bread_crumbs'] = "";
		$where="";
		
		#Start onpage
        if(!isset($_SESSION['onpage']))$_SESSION['onpage']=9;
		elseif(isset($_POST['onpage'])&&$_POST['onpage']==9)$_SESSION['onpage']=9;
		elseif(isset($_POST['onpage'])&&$_POST['onpage']==18)$_SESSION['onpage']=18;
		elseif(isset($_POST['onpage'])&&$_POST['onpage']==48)$_SESSION['onpage']=48;                 
		if(!isset($_SESSION['catalog']))$_SESSION['catalog']='';
		if(!isset($_SESSION['sub'])||(!isset($_POST['sub'])&&isset($_POST['sub_hid']))||$_SESSION['catalog']!=$this->params['catalog'])$_SESSION['sub']=array();
		if(isset($_POST['sub']))$_SESSION['sub'] = $_POST['sub'];
		$_SESSION['catalog']=$this->params['catalog'];
		#!End onpage

		#Start sort
		if(!isset($_SESSION['sort'])||(isset($_POST['sort'])&&$_POST['sort']==""))$_SESSION['sort']="id desc";
		if(isset($_POST['sort'])&&$_POST['sort']=="price:asc")$_SESSION['sort']="price asc";
		elseif(isset($_POST['sort'])&&$_POST['sort']=="price:desc")$_SESSION['sort']="price desc";
		elseif(isset($_POST['sort'])&&$_POST['sort']=="name:asc")$_SESSION['sort']="name asc";
		elseif(isset($_POST['sort'])&&$_POST['sort']=="name:desc")$_SESSION['sort']="name desc";
		#!End sort
		
		#Start filters
		if(isset($this->params['catalog'])&&$this->params['catalog']!="search"&&$this->params['catalog']!="all")
		{	
			$row=$this->db->row("select * from product_status where url='{$this->params['catalog']}'");
			if($row)
			{
				$where="and tb.id in(select product_id from product_status_set where status_id='{$row['id']}')";
				$vars['curr_cat']['name']=$row['comment'];
				$data['breadcrumbs'] = array('<a href="/catalog/all">'.$this->translation['catalog'].'</a>', $row['comment']);
			}
			else{
				$catrow = $this->db->row("SELECT *
										  FROM ".$this->tb." tb 
										  	LEFT JOIN ".$this->tb_lang." tb2
											ON tb.id=tb2.cat_id
											
										  WHERE tb.url=?", array($this->params['catalog']));
				
				$_SESSION['catalog2']=$catrow['id'];	
				if($catrow)
				{
					$data['meta'] = $catrow;///////Meta data
					$data['breadcrumbs'] = $this->getBreadCat($catrow);////bread crumbs
					$vars['sub'] = $this->db->rows("SELECT tb.id, tb.url, tb2.name
													  FROM ".$this->tb." tb 
														LEFT JOIN ".$this->tb_lang." tb2
														ON tb.id=tb2.cat_id
														
													  WHERE tb.sub=?"
					,array($catrow['id']));
					
					$subcat='';
					foreach($vars['sub'] as $row)
					{
						$subcat.="or tb3.catalog_id='{$row['id']}'";	
					}
					$vars['curr_cat']=$catrow;
					$where="and (tb3.catalog_id='{$catrow['id']}' $subcat)";
				}
				else return Router::act('error', $this->registry);
			}
		}
		elseif(isset($this->params['catalog'])&&$this->params['catalog']=="all"&&isset($this->params['brend'])&&$this->params['brend']!="")
		{
			$vars['curr_cat']=$this->db->row("select id, name, url, text, cnt from brend where url='{$this->params['brend']}'");
			$where="and $tb_product.brend_id='{$vars['curr_cat']['id']}'";
		}
		elseif(isset($this->params['catalog'])&&$this->params['catalog']=="search")
		{
			if(!isset($_SESSION['search']))$_SESSION['search']='';
			if(isset($_POST['search']))$_SESSION['search']=$_POST['search'];
			$vars['curr_cat']['name']='ПОИСК "'.$_SESSION['search'].'"';
			$where="and (tb2.name like '%{$_SESSION['search']}%' or tb2.body_m like '%{$_SESSION['search']}%' or tb2.body like '%{$_SESSION['search']}%')";
		}
		#!End filters
				
		$q=$this->query_products($where);
		
		#Start paging
		$settings = Registry::get('user_settings');
		$size_page =$settings['col_product'];
        $start_page = 0;
        $cur_page = 0;
        $vars['paging'] = '';

        if(isset($this->params['page']))
        {
            $cur_page = $this->params['page'];
            $start_page = ($cur_page-1) * $size_page;//номер начального элемента
        }
		
		$sql = $q." LIMIT ".$start_page.", ".$size_page."";
        //echo $sql;
        $count = $this->db->query($q);//кол страниц
        if($count > $size_page)
        {
            $vars['paging'] = Paging::MakePaging($cur_page, $count, $size_page);//вызов шаблона для постраничной навигации
        }
		
        $vars['product'] = $this->db->rows($sql);
		#!End paging

		$data['content'] = $view->Render('catalog.phtml', $vars);
		return $this->Render($data);
	}
	
	
	
	function query_products($where='')
	{
		$q="SELECT
                tb.*,
				tb.price,
				tb.discount,
                tb2.name,
				tb2.body_m,
				tb3.catalog_id,
				tb4.status_id
				
             FROM ".$this->tb_p." tb

				LEFT JOIN ".$this->tb_lang_p." tb2
                ON tb2.product_id=tb.id

                LEFT JOIN product_catalog tb3
                ON tb3.product_id=tb.id
				
				LEFT JOIN product_status_set tb4
                ON tb4.product_id=tb.id

             WHERE tb.active='1' $where
             GROUP BY tb.id
             ORDER BY tb.`sort` ASC, id DESC";//echo $q;
		return $q;
	}
}
?>