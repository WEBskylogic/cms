<?
class Editorial extends Model
{
    static $table='editorial'; //Главная талица
    static $name='Редактирование'; // primary key

	public function __construct($registry)
    {
        parent::getInstance($registry);
    }

	public static function getObject($registry)
	{
		return new self::$table($registry);
	}

    public function add()
    {
        $message='';
        if(isset($_POST['name']))
        {
            $param = array($_POST['name'], $_POST['icon'], $_POST['rate'], $_POST['position']);
            $this->db->query("INSERT INTO `".$this->table."` SET `name`=?, icon=?, rate=?, position=?", $param);
            $message.= messageAdmin('Данные успешно добавлены');
        }
        //else $message.= messageAdmin('При добавление произошли ошибки', 'error');
        return $message;
    }


    public function save()
    {
        $message='';
        if(isset($this->registry['access']))$message = $this->registry['access'];
        else
        {
            if(isset($_POST['body'], $_POST['id']))
            {
				
				file_put_contents($_POST['id'], String::sanitize(htmlentities($_POST['body'], ENT_QUOTES, "UTF-8"), true));
				$message .= messageAdmin('Данные успешно сохранены');
            }
			else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
        }
        return $message;
    }
	
	public function listView()
	{
		$path = "tpl/".$this->registry['theme'].'/';
		//var_info(get_file_list($path));
		return array_merge(Dir::get_file_list($path), Dir::get_file_list('css/'), Dir::get_file_list($path.'css/'));
	}
	
	public function find($id)
	{
		$url=str_replace('---', '.', $id);
		$url=str_replace('--', '/', $url);
		$url=str_replace('css2', 'css', $url);
		$date = date("Y-m-d H:i:s", filemtime($url));
		$body = file_get_contents($url, true);
		return array('url'=>$url,
					 'date'=>$date,
					 'body'=>$body);
	}
	
	public function save_theme()
    {
        $message='';
        if(isset($_POST['themes']))
        {
            $param = array($_POST['themes'], 'theme');
			$row = $this->db->row("SELECT * FROM `config` WHERE `name`='theme'");
			
            if(!$row)$this->db->query("INSERT INTO `config` SET `value`=?, `name`=?, active='0', comment='Текущая тема', modules_id='159'", $param);
			else $this->db->query("UPDATE `config` SET `value`=? WHERE `name`=?", $param);
            $message.= messageAdmin('Данные успешно добавлены');
        }
        //else $message.= messageAdmin('При добавление произошли ошибки', 'error');
        return $message;
    }
	
	public function save_watermark()
    {
        $message='';
        if(isset($_POST['watermark_position']))
        {
			$arr=array();
			$arr['image']=$_POST['watermark_file_current'];
			$arr['type']=$_POST['watermark_type'];
			$arr['opacity_font']=$_POST['watermark_opacity_font'];
			$arr['opacity_back']=$_POST['watermark_opacity_back'];
			$arr['font_color']=$_POST['watermark_font_color'];
			$arr['font_size']=$_POST['watermark_font_size'];
			$arr['back_color']=$_POST['watermark_back_color'];
			$arr['active']=$_POST['active'];
			$arr['position']=$_POST['watermark_position'];
			$arr['text']=$_POST['watermark_text'];
			$arr['width']=$_POST['watermark_width'];
			$arr['height']=$_POST['watermark_height'];
			$arr['top']=$_POST['watermark_top'];
			$arr['left']=$_POST['watermark_left'];
			$arr['type_image']=$_POST['type_image'];
			$arr['modules']=$_POST['modules'];
			
			if($_POST['watermark_type']==0)
			{
				$image='images/watermark.png';
				$arr['image']=$image;
				$text = $arr['text'];
				$im = imagecreate($arr['width'], $arr['height']);
				
				$opacity_back = $arr['opacity_back']/100;
				$opacity_back = round(127*$opacity_back);
				
				$opacity_font = $arr['opacity_font']/100;
				$opacity_font = round(127*$opacity_font);
				
				if($arr['back_color']!='')
				{
					$bcolor  = imagecolorallocatealpha($im, '0x'.substr($arr['back_color'], 0, 2), '0x'.substr($arr['back_color'], 2, 2), '0x'.substr($arr['back_color'], 4, 2), $opacity_back);
					imagecolorallocate($im, '0x'.substr($arr['back_color'], 0, 2), '0x'.substr($arr['back_color'], 2, 2), '0x'.substr($arr['back_color'], 4, 2));
				}
				else imagecolorallocatealpha($im, 0, 0, 0, 127);
				$color = imagecolorallocate($im, '0x'.substr($arr['font_color'], 0, 2), '0x'.substr($arr['font_color'], 2, 2), '0x'.substr($arr['font_color'], 4, 2));
				//imagestring($im, 10, 5, 50, 50, $text, $color);
				
				$color_font = imagecolorallocatealpha($im, '0x'.substr($arr['font_color'], 0, 2), '0x'.substr($arr['font_color'], 2, 2), '0x'.substr($arr['font_color'], 4, 2), $opacity_font);
				imagettftext($im, $arr['font_size'], 0, $arr['left'], $arr['top']+$arr['font_size'], -$color_font, "protected/classes/Arial.ttf", $text);
				
				
				imagepng($im, $image);
				$arr['image'] = $image;
			}
			else{
				if(isset($_FILES['watermark_file']['tmp_name'])&&$_FILES['watermark_file']['tmp_name']!='')
				{
					$ext = pathinfo($_FILES['watermark_file']['name'], PATHINFO_EXTENSION);
					$arr['image']='images/watermark.'.$ext;
					copy($_FILES['watermark_file']['tmp_name'], $arr['image']);	
				}
			}
            $param = array(json_encode($arr), 'watermark');
			$row = $this->db->row("SELECT * FROM `config` WHERE `name`='watermark'");
			
            if(!$row)$this->db->query("INSERT INTO `config` SET `value`=?, `name`=?, active='0', comment='Параметры водяного знака', modules_id='159'", $param);
			else $this->db->query("UPDATE `config` SET `value`=? WHERE `name`=?", $param);
            $message.= messageAdmin('Данные успешно добавлены');
        }
        //else $message.= messageAdmin('При добавление произошли ошибки', 'error');
        return $message;
    }
	
}
?>