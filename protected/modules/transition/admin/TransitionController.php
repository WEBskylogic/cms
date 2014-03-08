<?php
include('Transition.php');

class TransitionController extends BaseController {

    protected $params;
    protected $db;
    private $transition;

    function  __construct($registry, $params)
    {
        parent::__construct($registry, $params);
        $this->tb = "product";
        $this->name = "Товары";
        $this->width=144;
        $this->height=217;
        $this->width_extra=86;
        $this->height_extra=130;
        $this->height2=455;
        $this->width2=302;
        $this->registry = $registry;
        $this->product = new Product($this->sets);
        $this->transition = new Transition($this->sets);
    }

    public function indexAction()
    {
        if(isset($this->params['subsystem']))return $this->Index($this->product->subsystemAction());
        if(!isset($_SESSION['search_admin'])||isset($_POST['clear']))
        {
            $_SESSION['search_admin']=array();
            $_SESSION['search_admin']['cat_id']=0;
            $_SESSION['search_admin']['price_from']="";
            $_SESSION['search_admin']['price_to']="";
            $_SESSION['search_admin']['word']="";
            $_SESSION['search_admin']['onpage']=10;
            $_SESSION['search_admin']['paging']='';
        }
        elseif(isset($_POST['word']))
        {
            $_SESSION['search_admin']['cat_id']=$_POST['cat_id'];
            $_SESSION['search_admin']['price_from']=$_POST['price_from'];
            $_SESSION['search_admin']['price_to']=$_POST['price_to'];
            $_SESSION['search_admin']['word']=$_POST['word'];
            $_SESSION['search_admin']['onpage']=$_POST['onpage'];
        }
        $vars['message'] = '';
        $vars['name'] = $this->name;

        if(isset($this->registry['access']))$vars['message'] = $this->registry['access'];

        if(isset($_POST['import'])){

            if(!isset($_POST['empty'])){
                $_POST['empty'] = false;
            } else $_POST['empty'] = (bool)$_POST['empty'];

            if(($_POST['import'] == 'regular') && !empty($_FILES)){
                if (move_uploaded_file($_FILES['import_list']['tmp_name'], './files/'.basename($_FILES['import_list']['name']))) {
                    $vars['message'] = $this->importXml('./files/'.basename($_FILES['import_list']['name']), $_POST['empty']);
                } else exit();
            }

            //Virtuemart import handling
            if(($_POST['import'] == 'virtuemart') && !empty($_FILES)){
                if ((move_uploaded_file($_FILES['import_list']['tmp_name'], './files/'.basename($_FILES['import_list']['name']))) && (move_uploaded_file($_FILES['import_list_cat']['tmp_name'], './files/'.basename($_FILES['import_list']['name'])))) {
                    $vars['message'] = $this->importXmlVirtue('./files/'.basename($_FILES['import_list_cat']['name']), './files/'.basename($_FILES['import_list']['name']), $_POST['empty']);
                } else exit();
            }
            //OpenCart
            if(($_POST['import'] == 'opencart') && !empty($_FILES)){
                if ((move_uploaded_file($_FILES['import_list']['tmp_name'], './files/'.basename($_FILES['import_list']['name'])))) {
                    $vars['message'] = $this->importXmlOpen('./files/'.basename($_FILES['import_list']['name']), $_POST['empty']);
                } else exit();
            }
        }

        if(isset($_POST['export'])){
            $this->data['products'] = $this->Transition_model->getProds();
            $this->data['cats']     = $this->Transition_model->getCats();
            if($this->exportXml($this->transition->getProds(), $this->transition->getCats())){
                //GET RID OF THE PAGE PARTS IN XML
                header('Content-type: application/xml');
                header('Content-Disposition: attachment; filename="export.xml"');
                readfile('export.xml');
            }
        }

        $vars['list'] = $this->view->Render('view.phtml', $this->product->listView());
        $vars['status'] = Product_status::getObject($this->sets)->find(array('type'=>'rows', 'order'=>'id ASC'));
        $vars['catalog'] = Catalog::getObject($this->sets)->find(array('select'=>'tb.*, tb_lang.name, tb2.product_id',
            'join'=>' LEFT JOIN `product_catalog` tb2 ON tb.id=tb2.catalog_id',
            'group'=>'tb.id',
            'order'=>'tb.sort',
            'type'=>'rows'));
        $vars['URL']='';
        $data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name));
        $data['content'] = $this->view->Render('list.phtml', $vars);
        return $this->Index($data);
    }

    private function exportXml($prods, $cats){
        $this->data['date'] = date('Y-m-d H:i:s');
        $this->data['shop_name'] = 'TestingInterface';

        $this->data['parents'] = array();
        foreach($this->transition->getParents() as $key => $value){
            $this->data['parents'][$value['params_id']] = $value['name'];
        }

        $this->data['xml_export'] = '<?xml version="1.0" encoding="UTF-8"?>
         <yml_catalog date="'.$this->data['date'].'">
            <shop>
                <name>'.$this->data['shop_name'].'</name>
                <company>'.$this->data['shop_name'].'</company>
                <url>'.$_SERVER['HTTP_HOST'].'</url>
                <platform>SkylogicCMS</platform>
                <version>4.x</version>
                <agency></agency>
                <email></email>
                <currencies>';
        $this->data['xml_export'] .= '<currency id="UAH" rate="1" /><currency id="USD" rate="NBU" /><currency id="EUR" rate="NBU" />';
        $this->data['xml_export'] .= '</currencies>
                <categories>';
        //each category
        foreach($cats as $key => $value){
            if($value['sub']) $this->data['xml_export'] .= '
                    <category id="'.$value['id'].'" parentId="'.$value['sub'].'">'.$value['cat_name'].'</category>';
            else $this->data['xml_export'] .= '
                    <category id="'.$value['id'].'">'.$value['cat_name'].'</category>';
        }
        $this->data['xml_export'] .= '
                </categories>
                <offers>';

        $this->data['server_name'] = $_SERVER['SERVER_NAME'];
        foreach($prods as $key => $value){
            $this->data['xml_param'] = '
                    </offer>';
            if(!empty($value['options'])){
                $value['options'] = explode(',', $value['options']);
                foreach($value['options'] as $key => $option){
                    $value['options'][$key] = explode('|', $option);
                    $value['options'][$key][1] = $this->data['parents'][$value['options'][$key][1]];
                }

                foreach($value['options'] as $key => $option){
                    $this->data['xml_param'] .= '
                        <param name="'.$option[1].'">'.$option[0].'</param>
                    </offer>';
                }
			}


            if($value['active']) $this->data['active'] = 'true';
            else $this->data['active'] = 'false';

            if($value->photo){
                $src="<picture>http://".$this->data['server_name']."/".$value['photo']."</picture>";
            } else if($value->photo_s){
                $src="<picture>http://".$this->data['server_name']."/".$value['photo_s']."</picture>";
            } else $src="";

            $this->data['xml_export'].='
                    <offer id="'.$value['id'].'" type="vendor.model" available="'.$this->data['active'].'">
                        <url>http://'.$_SERVER['SERVER_NAME'].'/product/'.$value['url'].'</url>
                        <price>'.$value['price'].'</price>
                        <currencyId>UAH</currencyId>
                        <categoryId>'.$value['cid'].'</categoryId>
                        '.$src.'
                        <delivery>true</delivery>
                        <vendor>vendor</vendor>
                        <vendorCode>'.$value['code'].'</vendorCode>
                        <model>'.$value['name'].'</model>
                        <description>description</description>
                '.$this->data['xml_param'];
        }

        $this->data['xml_export'] .= '
                </offers>
            </shop>
        </yml_catalog>';


        ob_start();
        if(file_put_contents("./export.xml", $this->data['xml_export'], LOCK_EX)){
            //header("Location: ../export.xml");
            header('Content-Description: Prices export from catalogue');
            header('Content-type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename('export.xml').'"');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: '.filesize('export.xml'));
            ob_clean();
            flush();
            readfile('./export.xml');
            exit;
        } else return false;
        ob_end_clean();
    }

    //OpenCart
    private function importXmlOpen($filename_prod = false, $trunc = false){
        $filename_prod = './files/oc/prod.xml';

        //both categories and products
        //formatting for already existing method of import in YML
        if($filename_prod){
            libxml_use_internal_errors(true);
            $this->data['file_prod'] = file_get_contents($filename_prod);
            $this->data['file_prod'] = str_replace('&', '&amp;',$this->data['file_prod']);
            $this->data['file_prod'] = $this->data['file_prod'];

            $this->data['xml_prod'] = simplexml_load_string($this->data['file_prod']);
            foreach(libxml_get_errors() as $error) {
                print_r("\t".$error->message);
            }
            //print_r($this->data['file_prod']);
            $this->data['xml_export'] = '<?xml version="1.0" encoding="UTF-8"?>
             <yml_catalog date="'.$this->data['date'].'">
                <shop>
                    <name>'.$this->data['shop_name'].'</name>
                    <company>'.$this->data['shop_name'].'</company>
                    <url>'.$_SERVER['HTTP_HOST'].'</url>
                    <platform>SkylogicCMS</platform>
                    <version>3.x</version>
                    <agency></agency>
                    <email></email>
                    <currencies><currency id="UAH" rate="1" /><currency id="USD" rate="NBU" /><currency id="EUR" rate="NBU" /></currencies>
                    <offers>';
            //Ignoring the first line, we don't need it
            unset($this->data['xml_prod']->DATE_CREATED);

            //Product formatting
            foreach($this->data['xml_prod'] as $key => $value){
                $this->data['xml_param'] = '';
                $this->data['xml_export'].='
                    <offer id="'.$value->PRODUCT_ID.'" type="vendor.model" available="true">
                        <url>http://'.$_SERVER['SERVER_NAME'].'/product/'.$value->slug.'</url>
                        <price>'.$value->PRICE.'</price>
                        <currencyId>UAH</currencyId>
                        <delivery>true</delivery>
                        <vendor>vendor</vendor>
                        <vendorCode>'.$value->SKU.'</vendorCode>
                        <model>'.$value->NAME.' '.$value->MODEL.'</model>
                        <description><![CDATA['.$value->DESCRIPTION.']]></description>
                    </offer>';
            }
        }

        $this->data['xml_export'] .= '
                </offers>
            </shop>
        </yml_catalog>';

        //put in one file
        if(file_put_contents("./files/export_opencart.xml", $this->data['xml_export'])){
            unlink("./files/oc/export_opencart.xml");
            file_put_contents("./files/oc/export_opencart.xml", $this->data['xml_export']);
            //calling the method
            $this->importXml("./files/export_opencart.xml", $trunc);
        } else return false;
    }

    //Joomla Virtuemart 2 with CSVI
    private function importXmlVirtue($filename_cat = false, $filename_prod = false, $trunc = false){
        $filename_prod = './files/joomla/prod.xml';
        $filename_cat  = './files/joomla/cats.xml';
        $this->data['xml_export'] = '<?xml version="1.0" encoding="UTF-8"?>
         <yml_catalog date="'.$this->data['date'].'">
            <shop>
                <name>'.$this->data['shop_name'].'</name>
                <company>'.$this->data['shop_name'].'</company>
                <url>'.$_SERVER['HTTP_HOST'].'</url>
                <platform>SkylogicCMS</platform>
                <version>3.x</version>
                <agency></agency>
                <email></email>
                <currencies><currency id="UAH" rate="1" /><currency id="USD" rate="NBU" /><currency id="EUR" rate="NBU" /></currencies>
                <categories>';

        //both categories and products
        //formatting for already existing method of import in YML
        if($filename_cat){
            $this->data['file_cat'] = file_get_contents($filename_cat);
            $this->data['xml_cat'] = simplexml_load_string($this->data['file_cat'], 'SimpleXMLElement', LIBXML_NOCDATA);

            foreach($this->data['xml_cat']->channel->item as $value){
                $this->data['xml_export'] .= '<category id="'.$value->virtuemart_category_id.'">'.$value->category_name.'</category>';
            }

            $this->data['xml_export'] .= '</categories>
                <offers>';

        } else $this->data['xml_cat'] = array();

        if($filename_prod){
            $this->data['file_prod'] = file_get_contents($filename_prod);
            $this->data['xml_prod'] = simplexml_load_string($this->data['file_prod'], 'SimpleXMLElement', LIBXML_NOCDATA);

            foreach($this->data['xml_prod']->channel->item as $value){
                $this->data['xml_param'] = '';
                /*
                if($value['active']) $this->data['active'] = 'true';
                else $this->data['active'] = 'false';
                */
                //if('')  $src="<picture>http://</picture>";
                //else
                $src="";

                $this->data['xml_export'].='
                    <offer id="'.$value->virtuemart_product_id.'" type="vendor.model" available="true">
                        <url>http://'.$_SERVER['SERVER_NAME'].'/product/'.$value->slug.'</url>
                        <price>'.$value->product_price.'</price>
                        <currencyId>UAH</currencyId>
                        <categoryId>'.$value->category_id.'</categoryId>
                        '.$src.'
                        <delivery>true</delivery>
                        <vendor>vendor</vendor>
                        <vendorCode>'.$value->product_sku.'</vendorCode>
                        <model>'.$value->manufacturer_name.' '.$value->product_name.'</model>
                        <description>'.$value->custom_title.'</description>
                    </offer>';
            }
        }

        $this->data['xml_export'] .= '
                </offers>
            </shop>
        </yml_catalog>';

        //put in one file
        if(file_put_contents("./files/export_virtuemart.xml", $this->data['xml_export'])){
            file_put_contents("./files/joomla/export_virtuemart.xml", $this->data['xml_export']);
            //calling the method
            $this->importXml("./files/export_virtuemart.xml", $trunc);
        } else return false;
    }




}

?>