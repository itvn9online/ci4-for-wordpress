<?php

/*
 * Chức năng xử lý hình ảnh từ codeigniter4
 * https://codeigniter4.github.io/userguide/libraries/images.html
 */

namespace App\Libraries;

class MyImage
{
    //
    const NEN = 75; // mức độ nén ảnh

    public function __construct()
    {
        //
    }

    private static function loadLib($source)
    {
        /*
         if ( class_exists( 'Imagick' ) ) {
         echo 'with imagick library <br>' . PHP_EOL;
         $image = \Config\Services::image( 'imagick' );
         }
         //
         else {
         echo 'with gd library <br>' . PHP_EOL;
         */
        $image = \Config\Services::image();
        //}
        return $image->withFile($source);
    }

    private static function fixCom($compression)
    {
        if ($compression > 100) {
            $compression = 100;
        } else if ($compression <= 0) {
            $compression = self::NEN;
        }
        return $compression;
    }

    // chuyển định dạng ảnh sang webp
    public static function webpConvert($source, $desc = '', $quality = -1)
    {
        $source = explode('?', $source)[0];
        //echo $source . '<br>' . PHP_EOL;
        if (!file_exists($source)) {
            return '';
        }

        // tạo path webp
        if ($desc == '') {
            $desc = $source . '.webp';
        }
        //echo $desc . '<br>' . PHP_EOL;

        // nếu có rồi thì trả về luôn
        if (file_exists($desc)) {
            return str_replace(PUBLIC_PUBLIC_PATH, '', $desc);
        }

        // nếu chưa có -> tạo thôi
        //$file_ext = pathinfo($source, PATHINFO_EXTENSION);
        //echo $file_ext . '<br>' . PHP_EOL;

        //
        $mime_type = mime_content_type($source);
        //die($source . ':' . __CLASS__ . ':' . __LINE__);
        //die($mime_type . ':' . __CLASS__ . ':' . __LINE__);
        if (strpos($mime_type, 'image') === false) {
            return '';
        }
        // 1 số định dạng file không sử dụng quality được
        else if (
            in_array(
                $mime_type,
                [
                    'image/webp'
                ]
            )
        ) {
            return '';
        }

        //
        // bắt đầu chuyển đổi sang webp
        $create_webp = false;
        if ($mime_type == 'image/webp') {
            return str_replace(PUBLIC_PUBLIC_PATH, '', $source);
        } else if ($mime_type == 'image/png') {
            $img = imagecreatefrompng($source);
            $create_webp = true;
        } else if ($mime_type == 'image/jpg' || $mime_type == 'image/jpeg') {
            $img = imagecreatefromjpeg($source);
            $create_webp = true;
        } else if ($mime_type == 'image/gif') {
            $img = imagecreatefromgif($source);
            $create_webp = true;
        }

        //
        if ($create_webp !== true) {
            return '';
        }
        //echo 'Create webp<br>' . PHP_EOL;

        //
        imagepalettetotruecolor($img);
        imagealphablending($img, true);
        imagesavealpha($img, true);
        imagewebp($img, $desc, $quality);
        imagedestroy($img);
        chmod($desc, DEFAULT_FILE_PERMISSION);

        // kiểm tra lại xem có chưa
        if (file_exists($desc)) {
            //echo $desc . '<br>' . PHP_EOL;
            return str_replace(PUBLIC_PUBLIC_PATH, '', $desc);
        }

        //
        return '';
    }

    /*
     * https://codeigniter4.github.io/userguide/libraries/images.html#image-quality
     * save() can take an additional parameter $quality to alter the resulting image quality. Values range from 0 to 100 with 90 being the framework default. This parameter only applies to JPEG images and will be ignored otherwise:
     */
    public static function quality($source, $desc = '', $compression = 0, $withResource = false)
    {
        if (!file_exists($source)) {
            return [
                'code' => __LINE__,
                'error' => __CLASS__ . ': File not exist'
            ];
        }

        //
        $mime_type = mime_content_type($source);
        if (strpos($mime_type, 'image') === false) {
            return false;
        }
        // 1 số định dạng file không sử dụng quality được
        else if (
            in_array(
                $mime_type,
                [
                    'image/webp'
                ]
            )
        ) {
            return false;
        }

        //
        if ($desc == '') {
            $desc = $source;
        }
        $compression = self::fixCom($compression);

        // sử dụng Imagick (nếu có)
        if (class_exists('Imagick')) {
            /*
             * https://phpimagick.com/Imagick/setCompressionQuality?quality=85&image_path=Lorikeet
             */
            //echo 'Imagick - ' . $mime_type . ' - ' . IMAGETYPE_JPEG . ' - ' . $this->image_type . ' - ' . \Imagick::COMPRESSION_JPEG . ' <br>' . PHP_EOL;

            //
            $image = new \Imagick($source);
            if ($mime_type == 'image/jpg' || $mime_type == 'image/jpeg') {
                $image->setImageFormat('jpg');
                $image->setImageCompression(\Imagick::COMPRESSION_JPEG);
                $image->setImageCompressionQuality($compression);
            } else {
                $image->setImageCompression(\Imagick::COMPRESSION_UNDEFINED);
                $image->optimizeImageLayers();
            }
            $image->stripImage();
            $image->writeImages($desc, true);
            $image->destroy();
        } else {
            // https://codeigniter4.github.io/userguide/libraries/images.html#image-quality
            $image = \Config\Services::image()->withFile($source);

            // If you are only interested in changing the image quality without doing any processing. You will need to include the image resource or you will end up with an exact copy:
            if ($withResource !== false) {
                $image->withResource();
            }

            //
            $image->save($desc, $compression);
        }
    }

    public static function optimize($source, $desc = '', $compression = 0, $withResource = false)
    {
        return self::quality($source, $desc, $compression, $withResource);
    }

    /*
     * https://codeigniter4.github.io/userguide/libraries/images.html#cropping-images
     * Images can be cropped so that only a portion of the original image remains. This is often used when creating thumbnail images that should match a certain size/aspect ratio. This is handled with the crop() method:
     */
    public static function crop($source, $desc = '', $width = 150, $height = 150, $x = 0, $y = 0, $compression = 0, $maintainRatio = false, $masterDim = 'auto')
    {
        if (!file_exists($source)) {
            return [
                'code' => __LINE__,
                'error' => __CLASS__ . ': File not exist'
            ];
        }

        //
        if ($desc == '') {
            $desc = $source;
        }

        //
        $image = self::loadLib($source);
        $compression = self::fixCom($compression);

        //
        $image->crop($width, $height, $x, $y, $maintainRatio, $masterDim);

        // processing methods
        $image->save($desc, $compression);
    }

    /*
     * https://codeigniter4.github.io/userguide/libraries/images.html#resizing-images
     * Images can be resized to fit any dimension you require with the resize() method:
     */
    public static function resize($source, $desc = '', $width = 150, $height = 0, $compression = 0)
    {
        if (!file_exists($source)) {
            return [
                'code' => __LINE__,
                'error' => __CLASS__ . ': File not exist'
            ];
        }

        //
        if ($width <= 0 && $height <= 0) {
            return [
                'code' => __LINE__,
                'error' => __CLASS__ . ': width AND height not set number value'
            ];
        }

        //
        if ($desc == '') {
            $desc = $source;
        }

        //
        $resize_ext = pathinfo($source, PATHINFO_EXTENSION);

        // với file gif -> hiện chỉ có thể copy
        if (strtolower($resize_ext) == 'gif') {
            copy($source, $desc) or die('ERROR copy for resize file for .gif');
        }
        // các file ảnh khác có thể resize
        else {
            $compression = self::fixCom($compression);

            //
            $get_file_info = getimagesize($source);
            // nếu size cần resize mà nhỏ hơn size chính -> copy luôn cho nhanh
            if ($width > $get_file_info[0]) {
                if ($source != $desc) {
                    copy($source, $desc) or die('ERROR copy for resize file width ' . $width . ' to ' . $get_file_info[0]);

                    // optimize file sau mỗi lần copy
                    $new_quality = self::quality($desc, $desc, $compression);
                }
            }
            // còn lại sẽ thực hiện resize
            else {
                $image = self::loadLib($source);

                //
                $maintainRatio = false;
                $masterDim = 'auto';
                // resize theo chiều rộng -> chiều cao sẽ tính toán theo tỉ lệ mới của chiều rộng
                if ($height <= 0) {
                    $maintainRatio = true;
                    $masterDim = 'width';
                }
                // resize theo chiều cao -> chiều rộng sẽ tính toán theo tỉ lệ mới của chiều cao
                else if ($width <= 0) {
                    $maintainRatio = true;
                    $masterDim = 'height';
                }

                //
                $image->resize($width, $height, $maintainRatio, $masterDim);

                // processing methods
                $image->save($desc, $compression);
            }
        }
        chmod($desc, DEFAULT_FILE_PERMISSION);

        //
        clearstatcache();
        $get_file_info = getimagesize($desc);
        return [
            'file' => basename($desc),
            'file_size' => filesize($desc),
            'width' => $get_file_info[0],
            'height' => $get_file_info[1],
        ];
    }

    /*
     * https://codeigniter4.github.io/userguide/libraries/images.html#adding-a-text-watermark
     * You can overlay a text watermark onto the image very simply with the text() method. This is useful for placing copyright notices, photographer names, or simply marking the images as a preview so they won’t be used in other people’s final products.
     */
    public static function watermark($source, $desc = '', $text = '', $ops = [], $compression = 0)
    {
        if (!file_exists($source)) {
            return [
                'code' => __LINE__,
                'error' => __CLASS__ . ': File not exist'
            ];
        }

        //
        if ($desc == '') {
            $desc = $source;
        }

        //
        if ($text == '') {
            $text = $_SERVER['HTTP_HOST'];
        }

        //
        $image = self::loadLib($source);
        $compression = self::fixCom($compression);

        //
        if (empty($ops)) {
            $ops = [
                'color' => '#fff',
                'opacity' => 0.5,
                'withShadow' => true,
                'hAlign' => 'center',
                'vAlign' => 'bottom',
                'fontSize' => 20
            ];
        }

        //
        $image->text($text, $ops);

        // processing methods
        $image->save($desc, $compression);
    }
}
