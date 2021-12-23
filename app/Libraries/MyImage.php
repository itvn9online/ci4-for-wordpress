<?php

/*
 * Chức năng xử lý hình ảnh từ codeigniter4
 * https://codeigniter4.github.io/userguide/libraries/images.html
 */

namespace App\ Libraries;

class MyImage {
    //
    const NEN = 85; // mức độ nén ảnh

    public function __construct() {
        //
    }

    private static function loadLib( $source ) {
        if ( class_exists( 'Imagick' ) ) {
            echo 'with imagick library <br>' . "\n";
            $image = \Config\ Services::image( 'imagick' );
        }
        //
        else {
            echo 'with gd library <br>' . "\n";
            $image = \Config\ Services::image();
        }
        return $image->withFile( $source );
    }

    private static function fixCom( $compression ) {
        if ( $compression > 100 ) {
            $compression = 100;
        } else if ( $compression <= 0 ) {
            $compression = self::NEN;
        }
        return $compression;
    }

    /*
     * https://codeigniter4.github.io/userguide/libraries/images.html#image-quality
     * save() can take an additional parameter $quality to alter the resulting image quality. Values range from 0 to 100 with 90 being the framework default. This parameter only applies to JPEG images and will be ignored otherwise:
     */
    public static function quality( $source, $desc = '', $compression = 0, $withResource = false ) {
        if ( !file_exists( $source ) ) {
            return [
                'code' => __LINE__,
                'error' => __FUNCTION__ . ': File not exist'
            ];
        }

        //
        if ( $desc == '' ) {
            $desc = $source;
        }

        //
        $image = self::loadLib( $source );
        $compression = self::fixCom( $compression );

        // If you are only interested in changing the image quality without doing any processing. You will need to include the image resource or you will end up with an exact copy:
        if ( $withResource !== false ) {
            $image->withResource();
        }

        // processing methods
        $image->save( $desc, $compression );
    }

    /*
     * https://codeigniter4.github.io/userguide/libraries/images.html#cropping-images
     * Images can be cropped so that only a portion of the original image remains. This is often used when creating thumbnail images that should match a certain size/aspect ratio. This is handled with the crop() method:
     */
    public static function crop( $source, $desc = '', $width = 150, $height = 150, $x = 0, $y = 0, $compression = 0, $maintainRatio = false, $masterDim = 'auto' ) {
        if ( !file_exists( $source ) ) {
            return [
                'code' => __LINE__,
                'error' => __FUNCTION__ . ': File not exist'
            ];
        }

        //
        if ( $desc == '' ) {
            $desc = $source;
        }

        //
        $image = self::loadLib( $source );
        $compression = self::fixCom( $compression );

        //
        $image->crop( $width, $height, $x, $y, $maintainRatio, $masterDim );

        // processing methods
        $image->save( $desc, $compression );
    }

    /*
     * https://codeigniter4.github.io/userguide/libraries/images.html#resizing-images
     * Images can be resized to fit any dimension you require with the resize() method:
     */
    public static function resize( $source, $desc = '', $width = 150, $height = 0, $compression = 0 ) {
        if ( !file_exists( $source ) ) {
            return [
                'code' => __LINE__,
                'error' => __FUNCTION__ . ': File not exist'
            ];
        }

        //
        if ( $width <= 0 && $height <= 0 ) {
            return [
                'code' => __LINE__,
                'error' => __FUNCTION__ . ': width AND height not set number value'
            ];
        }

        //
        if ( $desc == '' ) {
            $desc = $source;
        }

        //
        $image = self::loadLib( $source );
        $compression = self::fixCom( $compression );

        //
        $maintainRatio = false;
        $masterDim = 'auto';
        // resize theo chiều rộng -> chiều cao sẽ tính toán theo tỉ lệ mới của chiều rộng
        if ( $height <= 0 ) {
            $maintainRatio = true;
            $masterDim = 'width';
        }
        // resize theo chiều cao -> chiều rộng sẽ tính toán theo tỉ lệ mới của chiều cao
        else if ( $width <= 0 ) {
            $maintainRatio = true;
            $masterDim = 'height';
        }

        //
        $image->resize( $width, $height, $maintainRatio, $masterDim );

        // processing methods
        $image->save( $desc, $compression );
    }

    /*
     * https://codeigniter4.github.io/userguide/libraries/images.html#adding-a-text-watermark
     * You can overlay a text watermark onto the image very simply with the text() method. This is useful for placing copyright notices, photographer names, or simply marking the images as a preview so they won’t be used in other people’s final products.
     */
    public static function watermark( $source, $desc = '', $text = '', $ops = [], $compression = 0 ) {
        if ( !file_exists( $source ) ) {
            return [
                'code' => __LINE__,
                'error' => __FUNCTION__ . ': File not exist'
            ];
        }

        //
        if ( $desc == '' ) {
            $desc = $source;
        }

        //
        if ( $text == '' ) {
            $text = $_SERVER[ 'HTTP_HOST' ];
        }

        //
        $image = self::loadLib( $source );
        $compression = self::fixCom( $compression );

        //
        if ( empty( $ops ) ) {
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
        $image->text( $text, $ops );

        // processing methods
        $image->save( $desc, $compression );
    }
}