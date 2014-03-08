<?php
/*
 * вывод каталога компаний и их данных
 */
class CommentsController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "comments";
		$this->registry = $registry;
		$this->comments = new Comments($this->sets);
	}
	
	
	///Add comments
	function addcommentAction()
    {
		echo json_encode($this->comments->addcomment());
    }
}
?>