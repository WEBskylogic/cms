<?php
/*
 * вывод каталога компаний и их данных
 */
class PhotosController extends BaseController{
	
	protected $params;
	protected $db;
	
	function  __construct($registry, $params)
	{
		$this->tb = "photos";
		$this->name = "Фотогалерея";
		$this->tb_lang = $this->key_lang.'_'.$this->tb;
		$this->registry = $registry;
		//$this->db->row("SELECT FROM `moderators_permission` WHERE `id`=?", array($_SESSION['admin']['id']));
		parent::__construct($registry, $params);
	}

	public function indexAction()
	{
		$vars['message'] = '';
		$vars['name'] = $this->name;
		if(isset($this->registry['access']))$vars['message'] = $this->registry['access'];
		if(isset($this->params['delete'])||isset($_POST['delete']))$vars['message'] = $this->delete();
		elseif(isset($_POST['update']))$vars['message'] = $this->save();
		elseif(isset($_POST['update_close']))$vars['message'] = $this->save();
		elseif(isset($_POST['add_close']))$vars['message'] = $this->add();
		
		$view = new View($this->registry);
		$vars['list'] = $view->Render('view.phtml', $this->listView());
		$data['content'] = $view->Render('list.phtml', $vars);
		return $this->Render($data);
	}
	
	public function addAction()
	{
		$vars['message'] = '';
		if(isset($_POST['add']))$vars['message'] = $this->add();
		
		$vars['list'] = $this->listView();
		$view = new View($this->registry);
		$data['content'] = $view->Render('add.phtml', $vars);
		return $this->Render($data);
	}
	
	public function editAction()
	{
		//if($vars['message']!='')return Router::act('error');
		$vars['message'] = '';
        $view = new View($this->registry);
		if(isset($_POST['update']))$vars['message'] = $this->save();
        if(isset($_POST['dell']))
        {
            for($i=0; $i<=count($_POST['photo_id']) - 1; $i++)
            {
                $this->db->query("DELETE FROM `photo` WHERE `id`=?", array($_POST['photo_id'][$i]));
                if(file_exists("files/photos/{$this->params['edit']}/{$_POST['photo_id'][$i]}.jpg"))unlink("files/photos/{$this->params['edit']}/{$_POST['photo_id'][$i]}.jpg");
                if(file_exists("files/photos/{$this->params['edit']}/{$_POST['photo_id'][$i]}_s.jpg"))unlink("files/photos/{$this->params['edit']}/{$_POST['photo_id'][$i]}_s.jpg");

            }
            $message = messageAdmin('Запись успешно удалена');
        }
        elseif(isset($this->params['delete'])&& $this->params['delete']!='')
        {
            $id = $this->params['delete'];
            if($this->db->query("DELETE FROM `photo` WHERE `id`=?", array($id)))$message = messageAdmin('Запись успешно удалена');
            if(file_exists("files/photos/{$this->params['edit']}/{$id}.jpg"))unlink("files/photos/{$this->params['edit']}/{$id}.jpg");
            if(file_exists("files/photos/{$this->params['edit']}/{$id}_s.jpg"))unlink("files/photos/{$this->params['edit']}/{$id}_s.jpg");
        }

		$vars['edit'] = $this->db->row("SELECT 
											tb.*,
											tb2.* 
										FROM ".$this->tb." tb
											LEFT JOIN
												".$this->tb_lang." tb2
											ON
												tb.id=tb2.photos_id
										WHERE
											tb.id=?",
										array($this->params['edit']));

        $vars['photo'] = $this->db->rows("SELECT * FROM `photo` tb
                                          LEFT JOIN `".$this->key_lang."_photo` tb2
                                          ON tb.id=tb2.photo_id
                                          WHERE photos_id=?
                                          ORDER BY sort ASC",
        array($vars['edit']['id']));
        $vars['photo'] = $view->Render('photo.phtml', $vars);
		$vars['list'] = $this->listView();

		$data['styles']=array('default.css', 'uploadify.css');
		$data['scripts']=array('swfobject.js', 'jquery.uploadify.v2.1.4.min.js');
		$data['content'] = $view->Render('edit.phtml', $vars);
		return $this->Render($data);
	}
	
	
	private function add()
	{
		$message='';
		if(isset($_POST['active'], $_POST['url'], $_POST['name'], $_POST['title'], $_POST['keywords'], $_POST['description'], $_POST['body'])&&$_POST['name']!="")
		{
			if($_POST['url']=='')$url = translit($_POST['name']);
			else $url = translit($_POST['url']);

			$param = array($_POST['active']);
			$insert_id = $this->db->insert_id("INSERT INTO `".$this->tb."` SET `active`=?", $param);
			$message = $this->checkUrl($this->tb, $url, $insert_id);
			foreach($this->language as $lang)
			{
				$tb=$lang['language']."_".$this->tb;
				$param = array($_POST['name'], $_POST['title'], $_POST['keywords'], $_POST['description'], $_POST['body'], $insert_id);
				$this->db->query("INSERT INTO `$tb` SET `name`=?, `title`=?, `keywords`=?, `description`=?, `body`=?, `photos_id`=?", $param);
			}
			$message.= messageAdmin('Данные успешно добавлены');
		}
		else $message.= messageAdmin('При добавление произошли ошибки', 'error');	
		return $message;
	}
	
	
	private function save()
	{
		$message='';
		if(isset($this->registry['access']))$message = $this->registry['access'];
		else
		{
			if(isset($_POST['save_id'])&&is_array($_POST['save_id']))
			{
				if(isset($_POST['save_id'], $_POST['name'], $_POST['url']))
				{
					for($i=0; $i<=count($_POST['save_id']) - 1; $i++)
					{
						if($_POST['url'][$i]=='')$url = translit($_POST['name'][$i]);
						else $url = $_POST['url'][$i];

						//echo $_POST['name'][$i].'<br>';
						$message = $this->checkUrl($this->tb, $url, $_POST['save_id'][$i]);
						$param = array($_POST['name'][$i], $_POST['save_id'][$i]);
						$this->db->query("UPDATE `".$this->tb_lang."` SET `name`=? WHERE photos_id=?", $param);
					}
					$message .= messageAdmin('Данные успешно сохранены');
				}
				else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
			}
			else{
				if(isset($_POST['active'], $_POST['url'], $_POST['id'], $_POST['name'], $_POST['title'], $_POST['keywords'], $_POST['description'], $_POST['body']))
				{
					if($_POST['url']=='')$url = translit($_POST['name']);
					else $url = translit($_POST['url']);

					$message = $this->checkUrl($this->tb, $url, $_POST['id']);
					$param = array($_POST['active'], $_POST['id']);
					$this->db->query("UPDATE `".$this->tb."` SET `active`=? WHERE id=?", $param);
					
					$param = array($_POST['name'], $_POST['title'], $_POST['keywords'], $_POST['description'], $_POST['body'], $_POST['id']);
					$this->db->query("UPDATE `".$this->tb_lang."` SET `name`=?, `title`=?, `keywords`=?, `description`=?, `body`=? WHERE `photos_id`=?", $param);

                    if(isset($_POST['save_photo_id'], $_POST['photo_name']))
                    {
                        for($i=0; $i<=count($_POST['save_photo_id']) - 1; $i++)
                        {
                            $param = array($_POST['photo_name'][$i], $_POST['save_photo_id'][$i]);
                            $this->db->query("UPDATE `".$this->key_lang."_photo` SET `name`=? WHERE photo_id=?", $param);
                        }
                    }

                    if(isset($_FILES['photo']['tmp_name'])&&$_FILES['photo']['tmp_name']!="")
                    {
                        $dir="files/photos/";
                        resizeImage($_FILES['photo']['tmp_name'], $dir.$_POST['id'].".jpg", $dir.$_POST['id']."_s.jpg", 214, 145);
                    }
                    $message .= messageAdmin('Данные успешно сохранены');
				}
				else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
			}
		}
		return $message;
	}
	
	private function delete()
	{
		$message='';
		if(isset($this->registry['access']))$message = $this->registry['access'];
		else
		{
			if(isset($_POST['id'])&&is_array($_POST['id']))
			{
				for($i=0; $i<=count($_POST['id']) - 1; $i++)
				{
					removeDir('files/photos/'.$_POST['id'][$i].'/');
					$this->db->query("DELETE FROM `".$this->tb."` WHERE `id`=?", array($_POST['id'][$i]));
				}
				$message = messageAdmin('Запись успешно удалена');
			}
			elseif(isset($this->params['delete'])&& $this->params['delete']!='')
			{
				$id = $this->params['delete'];
				removeDir('files/photos/'.$id.'/');
				if($this->db->query("DELETE FROM `".$this->tb."` WHERE `id`=?", array($id)))$message = messageAdmin('Запись успешно удалена');
			}
		}
		return $message;
	}
	
	private function listView()
	{
		$vars['list'] = $this->db->rows("SELECT
											tb.*,
											tb2.name
										 FROM ".$this->tb." tb
											LEFT JOIN
												".$this->tb_lang." tb2
											ON
												tb.id=tb2.photos_id
										 ORDER BY tb.`sort` ASC, id DESC");
		return $vars;
	}
}
?>