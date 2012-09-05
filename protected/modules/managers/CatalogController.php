<?php
/*
 * вывод каталога компаний и их данных
 */
class PagesController extends BaseController{
	
       protected $params;
       protected $db;

       function  __construct($registry, $params)
	   {
			$this->registry = $registry;
			parent::__construct($registry, $params);
			$this->Constant();
       }

       public function indexAction()
	   {
			$vars['module'] = "catalog";
			$settings = Registry::get('user_settings');
			$vars['translate'] = $this->translation;
			$tb_catalog=$this->key_lang."_catalog";
			$tb_product=$this->key_lang."_product";
			$tb_product_name=$this->key_lang."_product_name";
			$where="";
			if(!isset($_SESSION['onpage']))$_SESSION['onpage']=9;
			elseif(isset($_POST['onpage'])&&$_POST['onpage']==9)$_SESSION['onpage']=9;
			elseif(isset($_POST['onpage'])&&$_POST['onpage']==18)$_SESSION['onpage']=18;
			elseif(isset($_POST['onpage'])&&$_POST['onpage']==48)$_SESSION['onpage']=48;                 
			if(!isset($_SESSION['catalog']))$_SESSION['catalog']='';
			if(!isset($_SESSION['sub'])||(!isset($_POST['sub'])&&isset($_POST['sub_hid']))||$_SESSION['catalog']!=$this->params['catalog'])$_SESSION['sub']=array();
			if(isset($_POST['sub']))$_SESSION['sub'] = $_POST['sub'];
			$_SESSION['catalog']=$this->params['catalog'];

			if(!isset($_SESSION['sort'])||(isset($_POST['sort'])&&$_POST['sort']==""))$_SESSION['sort']="p.sort asc, p.id desc";
			if(isset($_POST['sort'])&&$_POST['sort']=="price:asc")$_SESSION['sort']="price asc";
			elseif(isset($_POST['sort'])&&$_POST['sort']=="price:desc")$_SESSION['sort']="price desc";
			elseif(isset($_POST['sort'])&&$_POST['sort']=="name:asc")$_SESSION['sort']="name asc";
			elseif(isset($_POST['sort'])&&$_POST['sort']=="name:desc")$_SESSION['sort']="name desc";

			$size_page = $_SESSION['onpage'];//количество выводимых элементов
			$start_page = 0;
			$cur_page = 0;
			$vars['paging'] = '';
			
			if(isset($this->params['page']))
			{
				$cur_page = $this->params['page'];
				$start_page = ($cur_page-1) * $size_page;//номер начального элемента
			}
			
			/////////////////////////////query
			$q="SELECT 
						p.id, 
						p.url, 
						p.brend_id,
						p.status_id, 
						pn.name, 
						price.price,
						pp.body_m
				FROM 
						product p
							LEFT JOIN
								$tb_product pp
							ON
								pp.product_id=p.id
							
							LEFT JOIN
								product_catalog pc
							ON
								pc.product_id=p.id
								
							LEFT JOIN
								catalog c
							ON
								c.id=pc.catalog_id
							
							LEFT JOIN
								product_price price
							ON
								price.product_id=p.id
							
							LEFT JOIN
								$tb_product_name pn
							ON
								pn.price_id=price.id	
						
				WHERE 
						p.active='1'
						$where 
				ORDER BY 
						{$_SESSION['sort']}";
			$sql = $q." LIMIT ".$start_page.", ".$size_page."";
			//echo $q.'<br /><br />';
			$count = $this->db->query($q);//кол страниц 
			if($count > $size_page)
			{
				$vars['paging'] = Paging::MakePaging($cur_page, $count, $size_page);//вызов шаблона для постраничной навигации
			}
			$vars['count']=$count;
			$vars['product'] = $this->db->rows($sql);
			$vars['discount'] = 0;
			$view = new View($this->registry);
			$data['content'] = $view->Render('catalog.phtml', $vars);
			return $this->Render($data);
		}
}
?>