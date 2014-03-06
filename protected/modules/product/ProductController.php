<?php
/*
 * вывод каталога компаний и их данных
 */
class ProductController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "product";
		$this->name = "Товары";
		$this->registry = $registry;
		$this->product = new Product($this->sets);
		$this->catalog = new Catalog($this->sets);
		$this->photos = new Photos($this->sets);
		$this->orders = new Orders($this->sets);
	}

	public function indexAction()
	{
		if(!isset($this->params['product']))return Router::act('error', $this->registry);	
		$vars['message'] = '';
		$vars['translate'] = $this->translation;
		$vars['currency'] = $this->model->currency();

		///Product										  
		$vars['product'] = $this->product->find(array("join"=>" LEFT JOIN `product_catalog` tb3 ON tb3.product_id=tb.id
																LEFT JOIN catalog ON catalog.id=tb3.catalog_id
																LEFT JOIN (SELECT * FROM `price` WHERE price_type_id='".$_SESSION['price_type_id']."' ORDER BY `sort` ASC, id DESC) as `tb_price`
						 										ON `tb_price`.product_id=tb.id
																",
																
													  "select"=>"tb.*, 
													  			 tb_lang.*, 
																 tb3.*,
																 catalog.sub as catalog_sub, 
																 tb_price.price, 
																 tb_price.discount, 
																 tb_price.id as price_id",
													  "where"=>"__tb.url:={$this->params['product']}__ AND tb.active='1'"));
		if(!$vars['product'])return Router::act('error', $this->registry);
		
		if($vars['product']['catalog_sub']==NULL)
		{
			$row = $this->db->row("SELECT * FROM product_catalog WHERE product_id='{$vars['product']['id']}' AND catalog_id!='{$vars['product']['catalog_id']}'");
			if($row)
			{
				$vars['product']['catalog_id']=$row['catalog_id'];	
			}
		}
		
		$vars['price'] = $this->product->getPrice($vars['product']['id'], $this->registry['price_type_id']);
		
		if($vars['product']['brend_id']!=0)
			$vars['brend'] = Brend::getObject($this->sets)->find((int)$vars['product']['brend_id']);
			
		///Other products
		$q = Catalog::getObject($this->sets)->queryProducts(array('where'=>"AND
																		    __tb3.catalog_id:={$vars['product']['catalog_id']}__ AND 
																		    __tb3.product_id!:={$vars['product']['id']}__",
																  'order'=>'rand()',
																  'limit'=>3));
		$vars['other'] = $this->product->find($q);
		
		///////Extra photo
		$vars['photo'] = $this->product->find(array('table'=>'product_photo',
													'where'=>"__tb.product_id:={$vars['product']['id']}__ AND tb.active='1'",
													'order'=>'tb.`sort` ASC',
													'type'=>'rows'));

        /*
		$vars['colors'] = $this->product->getColors($vars['product']['id']);
        $vars['sizes'] = $this->product->getSizes($vars['product']['id']);
		*/
		
		
		////Fast order
		if(isset($_POST['f3_name']))
		{
			$error="";
			if(!Captcha3D::check($_POST['f3_captcha']))$error.="<div class='err'>".$this->translation['wrong_code']."</div>";
			$error.=Validate::check(array($_POST['f3_name'], $_POST['f3_phone'], $_POST['f3_captcha']), $this->translation);
			if($error=="")
			{
				$user_id = 0;
				if(isset($_SESSION['user_id']))
				{
					$row = $this->db->row("SELECT * FROM `users` WHERE id=?", array($_SESSION['user_id']));
					$user_id = $row['id'];
				}
				
				$query = "SELECT '1' AS `amount`,
							p.id,
							p.code,
							p.url,
							tb_price.`price`,
							p.`photo`,
							tb_price.discount,
							p2.name
				
				  FROM product p
				  
				  LEFT JOIN ".$this->registry['key_lang']."_product p2
				  ON p.id=p2.product_id
				  
				  LEFT JOIN (SELECT * FROM `price` WHERE price_type_id='".$_SESSION['price_type_id']."' ORDER BY `sort` ASC, id DESC) as `tb_price`
				  ON `tb_price`.product_id=p.id
		
				  WHERE p.id='{$vars['product']['id']}'";
	
				$info['name']=$_POST['f3_name'];
				$info['phone']=$_POST['f3_phone'];
				$this->orders->sendOrder($query, $info, $user_id);
				$vars['message']='<div class="done" style="margin:15px 0;">'.htmlspecialchars_decode($this->translation['message_sent_order']).'</div>';
			}
			else $vars['message']=$error;
		}
		
		if(isset($this->settings['comment_product'])&&$this->settings['comment_product']==1)
			$vars['comments'] = Comments::getObject($this->sets)->list_comments($vars['product']['id'], $this->tb);
			
			
		$data['breadcrumbs'] = $this->model->getBreadCat($vars['product']['catalog_id'], $vars['product']['name']);
		$data['cur_product'] = $vars['product']['id'];
		$data['styles'] = array('comments.css', 'jqzoom.css');
		$data['scripts'] = array('jquery.jqzoom1.0.1.js');
		$data['content'] = $this->view->Render('product_in.phtml', $vars);
		return $this->Index($data);
	}
	
	///Load size
	function loadparamAction()
    {
		if(isset($_POST['id'], $_POST['product_id']))
		{
			$_POST['id']=explode('-',$_POST['id']);
			$_POST['id']=$_POST['id'][1];
			$return=array();
			$res = $this->product->get_param($_POST['id'], $_POST['product_id']);
			
			$return['option']='';
			$return['select_size']='';
			$cnt=count($res);
			
			if($cnt>1)
			{
				foreach($res as $row)
				{
					if($return['option']!='')$return['option'].=',';
					$return['option'].=$row['id'];
				}
			}
			elseif($cnt==1)
			{
				$row = $this->product->get_remains(array($_POST['id'],$res[0]['id']),  $_POST['product_id']);
				$return['price'] = $row['price'];
				$return['cur_price'] = $row['cur_price'];
				$return['remains'] = $row['stock'];
				$return['option']=$res[0]['id'];	
			}
			elseif($cnt==0)
			{
				$row = $this->product->get_remains(array($_POST['id']),  $_POST['product_id']);
				$return['price'] = $row['price'];
				$return['cur_price'] = $row['cur_price'];
				$return['remains'] = $row['stock'];
				$return['option']='';	
			}
			/*$return['image']='';
			$dir=createDir($_POST['product_id']);
			$path=$dir[0].$_POST['product_id'].".jpg";
			if(file_exists($path))
			{
				$return['image']='<a href="/'.$path.'" rel="lightbox">
									<img src="/'.$path.'" />
								  </a>';	
			}
			$row = $this->db->row("SELECT code as id_1c FROM product WHERE id='{$_POST['product_id']}'");
			if($row['id_1c']!='')$return['code']='<div class="parag" style="margin:0;"><span>Артикул: </span>'.$row['id_1c'].'</div>';*/
			
			$return['option']=explode(',',$return['option']);
			return json_encode($return);
		}
    }
	
	function remainsAction()
    {	
		$param=array();
		if($_POST['color_id']!='')array_push($param, $_POST['color_id']);
		if($_POST['size_id']!='')array_push($param, $_POST['size_id']);
		$row = $this->product->get_remains($param,  $_POST['product_id']);
		$return['price'] = $row['price'];
		$return['cur_price'] = $row['cur_price'];
		$return['remains'] = $row['stock'];
		return json_encode($return);
	}
	
	public function sendfastorderAction()
	{
		$error="";
		if(!Captcha3D::check($_POST['captcha']))$error.="<div class='err'>".$this->translation['wrong_code']."</div>";
		$error.=Validate::check(array($_POST['name'], $_POST['phone'], $_POST['captcha']), $this->translation);
		if($error=="")
		{
			$vars['send']=1;
		}
		else $vars['message']=$error;
		return json_encode($vars);
	}
}
?>