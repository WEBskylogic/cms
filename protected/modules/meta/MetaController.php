<?php
/*
 * вывод каталога компаний и их данных
 */
class MetaController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "meta";
		$this->registry = $registry;
		$this->meta = new Meta($this->sets);
	}
	
	function sitemapAction()
	{
		if(isset($this->settings['sitemap_generation'])&&$this->settings['sitemap_generation']==1)
		{
			header("Content-type: text/xml; charset=utf-8");
			/////Create file sitemap
			echo $this->meta->sitemap_generate();
		}
	}
	
	//////Yandex market xml
	function yandexAction()
	{
		if(isset($this->settings['yandex_market'])&&$this->settings['yandex_market']==1)
		{
			header("Content-Type: text/xml");
			$vars=$this->meta->yandex_market();
			$vars['sitename'] = $this->settings['sitename'];
			echo $this->view->Render('yandex.phtml', $vars);
		}
	}
}
?>