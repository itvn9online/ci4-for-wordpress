<?php
namespace App\Controllers;

//
class Captcha extends Ajaxs
{
    public function __construct()
    {
        parent::__construct();
    }

    public function three($len = 3, $width = 50)
    {
        header('Content-Type: image/png');

        //
        $rand = md5(time());
        $rand = substr($rand, 3, $len);
        $this->MY_session('check_captcha', $rand);

        // màu nền -> mặc định đen
        $b_color = [0, 0, 0];
        $bg_color = $this->MY_get('bg_color', '');
        if ($bg_color != '') {
            $bg_color = explode(',', $bg_color);
            foreach ($bg_color as $k => $v) {
                $v = trim($v);
                if ($v != '') {
                    $b_color[$k] = $v * 1;
                }
            }
        }

        // màu chữ -> mặc định trắng
        $t_color = [255, 255, 255];
        $text_color = $this->MY_get('text_color', '');
        if ($text_color != '') {
            $text_color = explode(',', $text_color);
            foreach ($text_color as $k => $v) {
                $v = trim($v);
                if ($v != '') {
                    $t_color[$k] = $v * 1;
                }
            }
        }

        //
        $height = 36;
        $image = imagecreate($width, $height);
        $bg = imagecolorallocate($image, $b_color[0], $b_color[1], $b_color[2]);
        $text = imagecolorallocate($image, $t_color[0], $t_color[1], $t_color[2]);

        //
        imagefill($image, 0, 0, $bg);
        imagestring($image, 5, 12, 9, $rand, $text);
        imagerectangle($image, 0, 0, $width, $height, $bg);

        //
        imagepng($image);
        imagedestroy($image);

        //
        exit();
        //return false;
    }

    public function six()
    {
        return $this->three(6, 80);
    }
}