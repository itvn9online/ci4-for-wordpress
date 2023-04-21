<?php

namespace App\ThirdParty;

/*
 * hàm xử lý hình ảnh viết bằng PHP thuần, lấy từ hệ thống webgiare.org sang
 */

use App\Libraries\MyImage;

//
class WGRSimpleImage
{
    var $image;
    var $image_width;
    var $image_height;
    var $image_type;

    function load($filename)
    {
        $image_info = getimagesize($filename) or die($filename);
        $this->image_width = $image_info[0];
        $this->image_height = $image_info[1];
        $this->image_type = $image_info[2];

        //
        if ($this->image_type == IMAGETYPE_GIF) {
            $this->image = imagecreatefromgif($filename);
        } else if ($this->image_type == IMAGETYPE_PNG) {
            $this->image = imagecreatefrompng($filename);
        } else {
            $this->image = imagecreatefromjpeg($filename);
        }
    }

    function save($filename, $image_type = '', $compression = -1, $permissions = null)
    {
        if ($image_type == '') {
            $image_type = $this->image_type;
        }

        if ($image_type == IMAGETYPE_GIF) {
            imagegif($this->image, $filename);
        } else if ($image_type == IMAGETYPE_PNG) {
            imagepng($this->image, $filename, 0, PNG_NO_FILTER);
        } else {
            imagejpeg($this->image, $filename, $compression);
        }
        $this->destroy();

        if ($permissions != null) {
            chmod($filename, $permissions);
        }
    }

    function output($image_type = '')
    {
        if ($image_type == '')
            $image_type = $this->image_type;

        if ($image_type == IMAGETYPE_JPEG) {
            imagejpeg($this->image);
        } else if ($image_type == IMAGETYPE_GIF) {
            imagegif($this->image);
        } else if ($image_type == IMAGETYPE_PNG) {
            imagepng($this->image);
        }
        $this->destroy();
    }

    function getWidth()
    {
        return imagesx($this->image);
    }

    function getHeight()
    {
        return imagesy($this->image);
    }

    function resizeToHeight($height)
    {
        $ratio = $height / $this->getHeight();
        $width = ceil($this->getWidth() * $ratio) - 1;
        $this->resize($width, $height);

        return $width;
    }

    function resizeToWidth($width)
    {
        $ratio = $width / $this->getWidth();
        $height = ceil($this->getheight() * $ratio) - 1;
        $this->resize($width, $height);

        return $height;
    }

    function scale($scale)
    {
        $width = ceil($this->getWidth() * $scale / 100) - 1;
        $height = ceil($this->getheight() * $scale / 100) - 1;
        $this->resize($width, $height);

        return [
            $width,
            $height
        ];
    }

    function resize($width, $height)
    {
        $new_image = imagecreatetruecolor($width, $height);
        //echo $this->image_type; exit();

        // set transparent for png file
        if ($this->image_type == IMAGETYPE_PNG) {
            imagealphablending($new_image, false);
            imagesavealpha($new_image, true);
            $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
            imagefilledrectangle($new_image, 0, 0, $width, $height, $transparent);
        }

        imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());

        $this->image = $new_image;
    }

    // optimize -> change Image Quality
    public function optimize($source_file, $dst_file = '', $width = 0, $height = 0, $compression = 75)
    {
        return MyImage::quality($source_file, $dst_file, $compression);

        // sử dụng php thuần
        /*
        $this->load($source_file);
        if ($this->image_type == IMAGETYPE_JPEG) {
            if ($width <= 0) {
                $width = $this->getWidth();
            }
            if ($height <= 0) {
                $height = $this->getHeight();
            }
            $this->resize($width, $height);
            $this->save($dst_file);
        }
        $this->destroy();
        */
    }

    public function destroy()
    {
        imagedestroy($this->image);
    }

    public function WGR_resize_images($source_file, $dst_file = '', $new_width = 0, $new_height = 0, $width = 0, $height = 0, $compression = -1)
    {
        return MyImage::resize($source_file, $dst_file, $new_width);
    }
}
