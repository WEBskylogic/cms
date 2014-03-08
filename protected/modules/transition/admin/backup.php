<?php

class Backup extends BaseController{
    private $data = array();

    public function index(){
        //YML import handling

    }


    private function properSanitizeForString($str){
        return strtr($str,array ('&' => '&#038;', '"' => '&#034;', "'" => '&#039;', '%' => '&#037;', '(' => '&#040;', ')' => '&#041;', '+' => '&#043;', '<' => '&lt;','>' => '&gt;'));
    }

}

?>