<?php
/*
 * вывод каталога компаний и их данных
 */
class ImportexelController extends BaseController{
	
	protected $package;
	protected $db;
	
	function  __construct($registry, $package)
	{
		parent::__construct($registry, $package);
		$this->tb = "importexel";
		$this->name = "Выгрузка";
		$this->registry = $registry;
		$this->importexel = new Importexel($this->sets);
	}

	public function indexAction()
	{
		/*$file_tmp="files/tmp/product/tmp_image.png";
		grab_image("http://cdn.sellbe.com/shop-16214/product/201/643276.png", $file_tmp);
		images($file_tmp, "s.jpg", "s_s.jpg", 139, 139);*/
		//$this->get_package_price();
		$vars['message'] = '';
		$vars['name'] = $this->name;
		if(isset($_POST['update']))$vars['message'] = $this->save();
		elseif(isset($_POST['update2']))$vars['message']=$this->importexel->export_products();

		$data['content'] = $this->view->Render('edit.phtml', $vars);
		return $this->Index($data);
	}
	
	private function save()
	{
		$message='';
		if(isset($this->registry['access']))$message = $this->registry['access'];
		else
		{
			if(isset($_POST['id']))
			{	
				$this->importexel->import_products(array('width_image'=>$this->settings['width_product'], 'height_image'=>$this->settings['height_product']));
				$message .= messageAdmin('Данные успешно сохранены');
			}
			else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
		}
		return $message;
	}
	
	function insertproductsAction()
	{
		$path="files/tmp/products.xlsx";
		
		if(file_exists($path))
		{
			$cols = $this->importexel->set_import($path);
			$this->registry->set('admin', 'importexel');
			
			echo $this->view->Render('cols.phtml', array('cols'=>$cols));
		}
	}
	
	////Create small Photo
    function uploadfileAction()
	{ 
		if(isset($_GET['qqfile']))
        {
			$_POST['path']="files/tmp/products.xlsx";
			$input = fopen("php://input", "r");
			$fp = fopen($_POST['path'], "w");
			while ($data = fread($input, 1024))
			{			
				fwrite($fp, $data);
			}
			fclose($fp);
			fclose($input);
        }
        elseif(isset($_FILES['qqfile']))
        {
			 copy($_FILES['qqfile']['tmp_name'], $_POST['path']);
        }
	}
}
?>