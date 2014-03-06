<?php
/**
 * 3D Captcha
 * @author TUX <liketux_at_gmail_dot_com>
 * @original Marc S. Ressl
 */
class Captcha3d
{
	// Размер шрифта
	public $fontsize = 24;
	// Файл шрифта
	public $fontfile = 'protected/classes/Arial.ttf';
	// Уровень выделения
	public $bevel = 3;
	// Размер по x
	public $image3d_x = 120;//160
	// Размер по y
	public $image3d_y = 65;//100
	// Размер сетки (0.1 - 1)
	public $scale = 0.98;
	// Цвет фона
	public $bgcolor = array(255, 255, 255);
	// Цвет текста
	public $fontcolor = array(0, 0, 0);
	// Ресурс изображения
	private $image3d;

	/**
	 * Конструктор captcha3D
	 */
	public function __construct() {
		$this->fontcolor = array(rand(0,80), rand(0,80), rand(0,80));
		// Получаем текст
		$captchaText = $this->randomtext();
		// Вычисляем размер шрифта
		$details = imagettfbbox($this->fontsize, 0, $this->fontfile, $captchaText);
		$image2d_x = $details[4] + 10;
		$image2d_y = $this->fontsize * 1.3;
		// Создаем 2d изображение
		$image2d = imagecreatetruecolor($image2d_x, $image2d_y);
		$black = imagecolorallocate($image2d, 0, 0, 0);
		$white = imagecolorallocate($image2d, 255, 255, 255);
		imagefill($image2d, 0, 0, $black);
		imagettftext($image2d, $this->fontsize, 0, 2, $this->fontsize, $white, $this->fontfile, $captchaText);
		// Рассчитываем матрицу проекции
		$T = $this->cameraTransform(array(rand(-80, 80), -200, rand(150, 250)), array(0, 0, 0));
		$T = $this->matrixProduct($T, $this->viewingTransform(60, 300, 3000));
		// Рассчитываем координаты
		$coord = array($image2d_x * $image2d_y);
		$count = 0;
		for ($y = 0; $y < $image2d_y; $y+=2)
		{
			for ($x = 0; $x < $image2d_x; $x++)
			{
				$xc = $x - $image2d_x / 2;
				$zc = $y - $image2d_y / 2;
				$yc = -(imagecolorat($image2d, $x, $y) & 0xff) / 256 * $this->bevel;
				$xyz = array($xc, $yc, $zc, 1);
				$xyz = $this->vectorProduct($xyz, $T);
				$coord[$count] = $xyz;
				$count++;
			}
		}
		// Создаем 3d изображение
		$this->image3d = imagecreatetruecolor($this->image3d_x, $this->image3d_y);
		$fgcolor = imagecolorallocate($this->image3d, $this->fontcolor[0], $this->fontcolor[1], $this->fontcolor[2]);
		$bgcolor = imagecolorallocate($this->image3d, $this->bgcolor[0], $this->bgcolor[1], $this->bgcolor[2]);
		//imageantialias($this->image3d, true);
		imagefill($this->image3d, 0, 0, $bgcolor);
		$count = 0;
		for ($y = 0; $y < $image2d_y; $y++)
		{
			for ($x = 0; $x < $image2d_x; $x++)
			{
				if ($x > 0)
				{
					$tmp = isset($coord[$count - 1][0]) ? $coord[$count - 1][0] : 0;
					$x0 = $tmp * $this->scale + $this->image3d_x / 2;
					$tmp = isset($coord[$count - 1][1]) ? $coord[$count - 1][1] : 0;
					$y0 = $tmp * $this->scale + $this->image3d_y / 2;
					$tmp = isset($coord[$count][0]) ? $coord[$count][0] : 0;
					$x1 = $tmp * $this->scale + $this->image3d_x / 2;
					$tmp = isset($coord[$count][1]) ? $coord[$count][1] : 0;
					$y1 = $tmp * $this->scale + $this->image3d_y / 2;
					imageline($this->image3d, $x0, $y0, $x1, $y1, $fgcolor);
				}
				$count++;
			}
		}

	}

	/**
	 * Выводит картинку
	 * @return bool
	 */
	public function show()
	{
		$types = array('png', 'gif'); // , 'jpeg' // Дает артефакты(плохо при цветной капче)
		// Отдаем картинку случайного типа =)
		$curtype = $types[array_rand($types)];
		header("Content-type: image/".$curtype);
		header("Cache-Control: no-cache, must-revalidate");
		header("Expires: Mon, 03 Apr 1977 00:00:00 GMT");
		$func = 'image'.$curtype;
		return $func($this->image3d);
	}

	/**
	 * Проверяет введенный текст
	 * @param string $text
	 * @return bool
	 */
	public static function check($text)
	{
		if(!isset($_SESSION['3DCaptchaText']))$_SESSION['3DCaptchaText']='';
		self::session();
		return (md5(strtoupper($text)) == $_SESSION['3DCaptchaText']);
	}

	/**
	 * Проверяет наличие сессии и запускает если ее нет
	 * @return bool
	 */
	private static function session()
	{
		if(!session_id())
		{
			return session_start();
		}
		return true;
	}

	/**
	 * Возращает случайный текст
	 * @param int $len
	 * @return string
	 */
	private function randomtext($len = 3)
	{
		$this->session();
		$str = '1234567890';
		$rerult = '';
		for($i = 0; $i < $len; $i++)
		{
			$rerult .= $str{rand(0, strlen($str)-1)};
		}
		$_SESSION['3DCaptchaText'] = md5($rerult);
		return $rerult;
	}

	/* Далее функции расчета изображения */

	private function addVector($a, $b)
	{
		return array($a[0] + $b[0], $a[1] + $b[1], $a[2] + $b[2]);
	}

	private function scalarProduct($vector, $scalar)
	{
		return array($vector[0] * $scalar, $vector[1] * $scalar, $vector[2] * $scalar);
	}

	private function dotProduct($a, $b)
	{
		return ($a[0] * $b[0] + $a[1] * $b[1] + $a[2] * $b[2]);
	}

	private function norm($vector)
	{
		return sqrt($this->dotProduct($vector, $vector));
	}

	private function normalize($vector)
	{
		return $this->scalarProduct($vector, 1 / $this->norm($vector));
	}

	private function crossProduct($a, $b)
	{
		return array(
			($a[1] * $b[2] - $a[2] * $b[1]),
			($a[2] * $b[0] - $a[0] * $b[2]),
			($a[0] * $b[1] - $a[1] * $b[0])
			);
	}

	private function vectorProductIndexed($v, $m, $i)
	{
		return array(
			$v[$i + 0] * $m[0] + $v[$i + 1] * $m[4] + $v[$i + 2] * $m[8] + $v[$i + 3] * $m[12],
			$v[$i + 0] * $m[1] + $v[$i + 1] * $m[5] + $v[$i + 2] * $m[9] + $v[$i + 3] * $m[13],
			$v[$i + 0] * $m[2] + $v[$i + 1] * $m[6] + $v[$i + 2] * $m[10]+ $v[$i + 3] * $m[14],
			$v[$i + 0] * $m[3] + $v[$i + 1] * $m[7] + $v[$i + 2] * $m[11]+ $v[$i + 3] * $m[15]
			);
	}

	private function vectorProduct($v, $m)
	{
		return $this->vectorProductIndexed($v, $m, 0);
	}

	private function matrixProduct($a, $b)
	{
		$o1 = $this->vectorProductIndexed($a, $b, 0);
		$o2 = $this->vectorProductIndexed($a, $b, 4);
		$o3 = $this->vectorProductIndexed($a, $b, 8);
		$o4 = $this->vectorProductIndexed($a, $b, 12);
		return array(
			$o1[0], $o1[1], $o1[2], $o1[3],
			$o2[0], $o2[1], $o2[2], $o2[3],
			$o3[0], $o3[1], $o3[2], $o3[3],
			$o4[0], $o4[1], $o4[2], $o4[3]
			);
	}

	private function cameraTransform($C, $A)
	{
		$w = $this->normalize($this->addVector($C, $this->scalarProduct($A, -1)));
		$y = array(0, 1, 0);
		$u = $this->normalize($this->crossProduct($y, $w));
		$v = $this->crossProduct($w, $u);
		$t = $this->scalarProduct($C, -1);
		return array(
			$u[0], $v[0], $w[0], 0,
			$u[1], $v[1], $w[1], 0,
			$u[2], $v[2], $w[2], 0,
			$this->dotProduct($u, $t), $this->dotProduct($v, $t), $this->dotProduct($w, $t), 1
			);
	}

	private function viewingTransform($fov, $n, $f)
	{
		$fov *= (M_PI / 180);
		$cot = 1 / tan($fov / 2);
		return array(
			$cot,	0,		0,		0,
			0,		$cot,	0,		0,
			0,		0,		($f + $n) / ($f - $n),		-1,
			0,		0,		2 * $f * $n / ($f - $n),	0
			);
	}

}
?>
