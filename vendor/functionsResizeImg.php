<?php


//
class WGR_SimpleImage {
    var $image;
    var $image_width;
    var $image_height;
    var $image_type;

    function load( $filename ) {
        $image_info = getimagesize( $filename )or die( $filename );
        $this->image_width = $image_info[ 0 ];
        $this->image_height = $image_info[ 1 ];
        $this->image_type = $image_info[ 2 ];

        //
        if ( $this->image_type == IMAGETYPE_GIF ) {
            $this->image = imagecreatefromgif( $filename );
        } elseif ( $this->image_type == IMAGETYPE_PNG ) {
            $this->image = imagecreatefrompng( $filename );
        } else {
            $this->image = imagecreatefromjpeg( $filename );
        }
    }

    function save( $filename, $image_type = '', $compression = 80, $permissions = null ) {
        if ( $image_type == '' ) {
            $image_type = $this->image_type;
        }

        if ( $image_type == IMAGETYPE_GIF ) {
            imagegif( $this->image, $filename );
        } elseif ( $image_type == IMAGETYPE_PNG ) {
            imagepng( $this->image, $filename, 0, PNG_NO_FILTER );
        } else {
            imagejpeg( $this->image, $filename, $compression );
        }

        if ( $permissions != null ) {
            chmod( $filename, $permissions );
        }
    }

    function output( $image_type = '' ) {
        if ( $image_type == '' )$image_type = $this->image_type;

        if ( $image_type == IMAGETYPE_JPEG ) {
            imagejpeg( $this->image );
        } elseif ( $image_type == IMAGETYPE_GIF ) {
            imagegif( $this->image );
        } elseif ( $image_type == IMAGETYPE_PNG ) {
            imagepng( $this->image );
        }
    }

    function getWidth() {
        return imagesx( $this->image );
    }

    function getHeight() {
        return imagesy( $this->image );
    }

    function resizeToHeight( $height ) {
        $ratio = $height / $this->getHeight();
        $width = ceil( $this->getWidth() * $ratio ) - 1;
        $this->resize( $width, $height );

        return $width;
    }

    function resizeToWidth( $width ) {
        $ratio = $width / $this->getWidth();
        $height = ceil( $this->getheight() * $ratio ) - 1;
        $this->resize( $width, $height );

        return $height;
    }

    function scale( $scale ) {
        $width = ceil( $this->getWidth() * $scale / 100 ) - 1;
        $height = ceil( $this->getheight() * $scale / 100 ) - 1;
        $this->resize( $width, $height );

        return [
            $width,
            $height
        ];
    }

    function resize( $width, $height ) {
        $new_image = imagecreatetruecolor( $width, $height );
        //echo $this->image_type; exit();

        // set transparent for png file
        if ( $this->image_type == IMAGETYPE_PNG ) {
            imagealphablending( $new_image, false );
            imagesavealpha( $new_image, true );
            $transparent = imagecolorallocatealpha( $new_image, 255, 255, 255, 127 );
            imagefilledrectangle( $new_image, 0, 0, $width, $height, $transparent );
        }

        imagecopyresampled( $new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight() );

        $this->image = $new_image;
    }
}


function WGR_resize_images( $source_file, $dst_file = '', $new_width = 0, $new_height = 0, $width = 0, $height = 0, $compression = 80 ) {
    return EBE_resize_images( $source_file, $dst_file, [
        'new_width' => $new_width,
        'new_height' => $new_height,
        'width' => $width,
        'height' => $height,
        'compression' => $compression,
    ] );
}

function EBE_default_resize_ops( $key, $ops, $default_value = 0 ) {
    if ( !isset( $ops[ $key ] ) ) {
        $ops[ $key ] = $default_value;
    }
    return $ops[ $key ];
}

function EBE_resize_images( $source_file, $dst_file = '', $ops = [] ) {
    if ( !file_exists( $source_file ) ) {
        die( __FUNCTION__ . ' source file not found!' );
    }

    // gán các giá trị mặc định cho việc resize
    $new_width = EBE_default_resize_ops( 'new_width', $ops );
    $new_height = EBE_default_resize_ops( 'new_height', $ops );
    $width = EBE_default_resize_ops( 'width', $ops );
    $height = EBE_default_resize_ops( 'height', $ops );
    $compression = EBE_default_resize_ops( 'compression', $ops );
    if ( $compression > 100 ) {
        $compression = 100;
    } else if ( $compression < 50 ) {
        $compression = 50;
    }

    //
    $image = new WGR_SimpleImage();
    $image->load( $source_file );

    if ( $new_width == 0 && $new_height == 0 ) {
        die( 'new size not set for resize: ' . __FUNCTION__ );
    }
    if ( $width == 0 ) {
        $width = $image->getWidth();
        //echo $width . '<br>' . "\n";
    }
    if ( $height == 0 ) {
        $height = $image->getheight();
        //echo $height . '<br>' . "\n";
    }
    //$a = getimagesize( $source_file );
    //print_r( $a );
    //die( 'dh dhdfhd' );

    //
    if ( $new_width == $new_height ) {
        if ( $width > $height ) {
            $new_height = $image->resizeToWidth( $new_width );
        } else {
            $new_width = $image->resizeToHeight( $new_height );
        }
        //$image->resize( $new_width, $new_height );
    } else if ( $new_width > $new_height ) {
        $new_height = $image->resizeToWidth( $new_width );
    } else {
        $new_width = $image->resizeToHeight( $new_height );
    }

    //
    $resize_ext = pathinfo( $source_file, PATHINFO_EXTENSION );
    if ( $dst_file == '' ) {
        $dst_file = dirname( $source_file ) . '/' . basename( $source_file, '.' . $resize_ext ) . '-' . $new_width . 'x' . $new_height . '.' . $resize_ext;
        //echo $dst_file . '<br>' . "\n";
    }

    // với file .gif cũng không resize -> do lỗi mất frame
    if ( strtolower( $resize_ext ) == 'gif' ) {
        copy( $source_file, $dst_file )or die( 'ERROR copy for resize file for .gif' );
    }
    // nếu size cần resize mà nhỏ hơn size chính -> copy luôn cho nhanh
    else if ( $new_width > 0 && $new_width > $width ) {
        copy( $source_file, $dst_file )or die( 'ERROR copy for resize file with new_width' );
    } else if ( $new_height > 0 && $new_height > $height ) {
        copy( $source_file, $dst_file )or die( 'ERROR copy for resize file with new_height' );
    }
    // còn lại sẽ thực hiện resize
    else {
        if ( !file_exists( $dst_file ) ) {
            copy( $source_file, $dst_file )or die( 'ERROR copy for before resize' );
        }
        //$image->save( $dst_file, '', 100 );
        $image->save( $dst_file );
    }
    chmod( $dst_file, 0777 );

    //echo ' <strong>SimpleImage</strong>; ';
    //echo $new_width . '<br>' . "\n";
    //echo $new_height . '<br>' . "\n";

    //
    return [
        'file' => basename( $dst_file ),
        'width' => $new_width,
        'height' => $new_height,
    ];
}