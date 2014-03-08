<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Virgo
 * Date: 17.09.13
 * Time: 10:29
 * To change this template use File | Settings | File Templates.
 */

class Transition extends Model{

    static $table='product';    //main table
    static $name='Товары';      // primary key
    private $data = array();

    public function __construct($registry)
    {
        parent::getInstance($registry);
        /*
        foreach($registry as $val){
            var_dump($val);
            echo '<br />';
        }
        */
    }

    //для доступа к классу через статичекий метод
    public static function getObject($registry)
    {
        return new self::$table($registry);
    }

    public function flushTables(){
        $this->db->query('DELETE FROM product;
                          DELETE FROM catalog;
                          SELECT * FROM catalog;');
        return true;
    }


    public function getProds($id = false){

        if($id){
            $this->data['sql'] = $this->db->row("SELECT * FROM ru_product WHERE name = '$id'");
        } else {
            $this->data['sql'] = $this->db->rows("  SELECT
                                                        tb.*,
                                                        tb2.name,
                                                        tb4.catalog_id as cid,
                                                        tb4.name as catalog,
                                                        GROUP_CONCAT(DISTINCT CONCAT_WS('|', params_names.name, ps.sub) ORDER BY params_names.params_id ASC SEPARATOR ',') AS options
                                                     FROM product tb
                                                        LEFT JOIN " . $this->registry['key_lang_admin']. "_product tb2 ON tb.id=tb2.product_id
                                                        LEFT JOIN product_catalog tb3 ON tb3.product_id=tb.id
                                                        LEFT JOIN " . $this->registry['key_lang_admin'] . "_catalog tb4 ON tb4.catalog_id=tb3.catalog_id
                                                        LEFT JOIN catalog tb_cat ON tb4.catalog_id=tb_cat.id
                                                        LEFT JOIN product_status_set pss ON pss.product_id=tb.id
                                                        LEFT JOIN params_product params ON tb.id = params.product_id
                                                        LEFT JOIN ru_params params_names ON params.params_id = params_names.params_id
                                                        LEFT JOIN params ps ON ps.id = params_names.params_id
                                                     WHERE tb.id!='0'
                                                     GROUP BY tb.id ");
        }

        return $this->data['sql'];
    }

    public function savePrd($data = array()){
        $this->data['insert_id'] = $this->db->insert_id("INSERT INTO `product`( code, active, price, date_add, url ) VALUES ('".$data['code']."',  '".$data['active']."',  '".$data['price']."',  '".$data['date_add']."','".$data['url']."');");
        if($this->data['insert_id']){
            //'".$data['body_m']."', '".$data['body']."',

            return $this->db->query("INSERT INTO product_catalog (product_id, catalog_id) VALUES( ".$this->data['insert_id'].", ".$data['cat_id'].");
                                 INSERT INTO ru_product (name, title, keywords, body, product_id) VALUES( '".$data['name']."', '".$data['title']."', '".$data['title']."', '".$data['description']."',  ".$this->data['insert_id'].");");
        } else return false;
    }


    public function saveCat($data = array()){
        if($data['parent'] === 0) $data['parent'] = 'NULL';
        $this->data['insert_id'] = $this->db->insert_id("INSERT INTO `catalog`( sub, sort, active, url) VALUES (".$data['parent'].",  '0', '1',  '".$data['url']."');");
        if($this->data['insert_id']){
            $this->db->query("INSERT INTO ru_catalog (catalog_id, name) VALUES( ".$this->data['insert_id'].", '".$data['name']."');
                          INSERT INTO params_catalog (params_id, catalog_id) VALUES( 1, ".$this->data['insert_id'].");");
            return $this->data['insert_id'];
        } else return 0;
    }

    public function saveProduct($data = array()){
        $this->data['insert_id'] = $this->db->insert_id("INSERT INTO `".$this->tb."`( code, active, price, old_price, material, date_add, url ) VALUES ('".$data['code']."',  '".$data['active']."',  '".$data['price']."',  '".$data['old_price']."',  '".$data['material']."',  '".$data['date_add']."','".$data['url']."');");
        return $this->data['insert_id'];
    }



    public function saveProductCat($data = array()){
        return $this->db->query("INSERT INTO product_catalog SET product_id=?, catalog_id=?", array($data['prod_id'], $data['cat_id']));
    }

    public function saveProductRu($data = array()){
        $this->data['sql'] = array($data['name'], $data['title'], $data['keywords'], $data['description'], $data['body_m'], $data['body'], $data['id']);
        return $this->db->query("INSERT INTO `ru_product` SET `name`=?, `title`=?, `keywords`=?, `description`=?, `body_m`=?, `body`=?, product_id=?", $this->data['sql']);
    }

    public function getCats($id = false){
        if($id){
             $this->data['sql'] = $this->db->row("SELECT rc.cat_id, c.sub as parent_id  FROM ru_catalog rc LEFT JOIN catalog c ON c.id = rc.catalog_id WHERE rc.name = '$id'");
        } else {
            $this->data['sql'] = $this->db->rows("  SELECT
                                                      c.*,
                                                      rc.name as cat_name
                                                    FROM catalog c
                                                    LEFT JOIN
                                                      ru_catalog rc ON c.id =rc.catalog_id ");
        }
        return $this->data['sql'];
    }

    public function getParentCat($id = false){
        if($id) return $this->data['sql'] = $this->db->row("SELECT id FROM catalog WHERE sub = '$id'");
        else return false;
    }

    public function addCat($data){
        if(!empty($data)){
            $data['id'] = $this->db->insert_id("INSERT INTO `catalog` SET `url`=?, `on_main`= 1, ``=?, `description`=?, `body`=?, `catalog_id`=?", $data);
            $this->db->query("INSERT INTO `ru_catalog` SET `name`=?, `title`=?, `keywords`=?, `description`=?, `body`=?, `cat_id`=?", $data);
        } else $data['id'] = 0;
        return $data['id'];
    }

    public function getParents(){
        $this->data['sql'] = $this->db->rows('SELECT rp.params_id, rp.name
                                              FROM ru_params rp, params p
                                              WHERE rp.params_id = p.sub');

        return $this->data['sql'];
    }

    public function getAllParams(){
        $this->data['sql'] = $this->db->rows('SELECT rp.params_id, rp.name, p.sub
                                              FROM ru_params rp, params p
                                              WHERE rp.params_id = p.id');

        return $this->data['sql'];
    }

    public function checkForDuplicate($url) {
        if($this->data['slug'] = $this->db->row("SELECT COUNT(*) from `product` where url !=?", array($url)))
        {
            $this->data['slug'] = ((int)$this->data['slug']['COUNT(*)'])+1;
            return ($url.((int)$this->data['slug']));
        }
        else return $url;
    }

}

?>