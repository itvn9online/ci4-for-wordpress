<?php
namespace App\ Controllers;

//
class Captcha extends Layout {
    // với 1 số controller, sẽ không nạp cái HTML header vào, nên có thêm tham số này để không nạp header nữa
    public $preload_header = false;

    public function __construct() {
        parent::__construct();
    }

    public function three( $len = 3, $width = 50 ) {
        $rand = md5( time() );
        $rand = substr( $rand, 3, $len );
        $this->MY_session( 'check_captcha', $rand );

        //
        $height = 36;
        $image = imagecreate( $width, $height );
        $black = imagecolorallocate( $image, 0, 0, 0 );
        $white = imagecolorallocate( $image, 255, 255, 255 );
        $grey = imagecolorallocate( $image, 0, 255, 0 );

        //
        imagefill( $image, 0, 0, $black );
        imagestring( $image, 5, 12, 9, $rand, $white );
        imagerectangle( $image, 0, 0, $width, $height, $black );

        //
        header( 'Content-Type: image/png' );
        imagepng( $image );
        imagedestroy( $image );

        //
        return false;
    }

    public function six() {
        return $this->three( 6, 80 );
    }
}