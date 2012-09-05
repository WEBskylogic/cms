<?php
/**
 * Represents captcha as an image and writes the answer to the session.
 */
class CaptchaController {
	function  __construct() {
		$captcha = new Captcha3D();
		$vars['captcha'] = $captcha->show();
	}
}
?>