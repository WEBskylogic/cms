<?php
/*
 * вывод каталога компаний и их данных
 */
class PhotosController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "photos";
		$this->name = "Фотогалерея";
		$this->registry = $registry;
        $this->photos = new Photos($this->sets);
	}

	public function indexAction()
	{
		$vars['message'] = '';
		$vars['name'] = $this->name;
        if(isset($this->params['subsystem']))return $this->Index($this->photos->subsystemAction());
		if(isset($this->registry['access']))$vars['message'] = $this->registry['access'];
		if(isset($this->params['delete'])||isset($_POST['delete']))$vars['message'] = $this->photos->delete();
		elseif(isset($_POST['update']))$vars['message'] = $this->photos->save();
		elseif(isset($_POST['update_close']))$vars['message'] = $this->photos->save();
		elseif(isset($_POST['add_open']))$vars['message'] = $this->photos->add(true);

		$vars['list'] = $this->view->Render('view.phtml', array('list'=>$this->photos->find(array('type'=>'rows', 'order'=>'tb.sort ASC, tb.id DESC', 'paging'=>$this->settings['paging_photos_admin']))));
		$data['left_menu'] = $this->model->left_menu_admin(array('action'=>$this->tb, 'name'=>$this->name));
		$data['content'] = $this->view->Render('list.phtml', $vars);
		return $this->Index($data);
	}
	
	public function addAction()
	{
		$vars['message'] = '';
		if(isset($_POST['add']))$vars['message'] = $this->photos->add();
		
		$vars['height']=$this->settings['height_photos'];	
		$vars['width']=$this->settings['width_photos'];
		$data['content'] = $this->view->Render('add.phtml', $vars);
		return $this->Index($data);
	}
	
	public function editAction()
	{
		$vars['message'] = '';
		if(isset($_POST['update']))$vars['message'] = $this->photos->save();

		$vars['edit'] = $this->photos->find((int)$this->params['edit']);
		
		/////Load meta
		$row = $this->meta->load_meta($this->tb, $vars['edit']['url']);
		if($row)
		{
			$vars['edit']['title'] = $row['title'];	
			$vars['edit']['keywords'] = $row['keywords'];	
			$vars['edit']['description'] = $row['description'];	
		}
		
        //Удаление нескольки, или одного фото из альбома
        if(isset($_POST['dell'])) $this->photos->deletePhotos();
        elseif(isset($this->params['dellone'])&& $this->params['dellone']!='')$this->photos->delPhoto($this->params['dellone']);

        ////Загрузка фоток для текушего альбома
		$vars['height']=$this->settings['height_photos'];	
		$vars['width']=$this->settings['width_photos'];
		$vars['height_extra']=$this->settings['height_photos_extra'];	
		$vars['width_extra']=$this->settings['width_photos_extra'];
		$vars['action']=$this->tb;
		$vars['action2']='photo';
		$vars['path']="files/photos/".$vars['edit']['id']."/";	
        $photo = $this->photos->loadPhotos($vars['edit']['id']);
		$vars['photo'] = $this->view->Render('extra_photo_one.phtml', array('photo'=>$photo, 'action'=>$this->tb, 'path'=>$vars['path'], 'sub_id'=>$vars['edit']['id']));
        $vars['photo'] = $this->view->Render('extra_photo.phtml', $vars);
		$vars['path']="files/photos/";	
		
		$data['styles']=array('default.css', 'uploadify.css', 'bootstrap.css');
		$data['scripts']=array('swfobject.js', 'jquery.uploadify.v2.1.4.min.js', 'bootstrap-modal.js');
		//////////////
		
		$data['content'] = $this->view->Render('edit.phtml', $vars);
		return $this->Index($data);
	}

	/////Gallery tpl
    function photosAction()
    {
		if(isset($_REQUEST['id']))
		{
			$this->registry->set('admin', 'photos');
			echo $this->view->Render('photos.phtml', array('photo'=>$this->photos->loadPhotos($_REQUEST['id'])));	
		}
	}
}
?>