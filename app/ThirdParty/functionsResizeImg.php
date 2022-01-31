<?php
namespace App\ ThirdParty;

/*
 * hàm xử lý hình ảnh viết bằng PHP thuần, lấy từ hệ thống webgiare.org sang
 */

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
        } else if ( $this->image_type == IMAGETYPE_PNG ) {
            $this->image = imagecreatefrompng( $filename );
        } else {
            $this->image = imagecreatefromjpeg( $filename );
        }
    }

    function save( $filename, $image_type = '', $compression = -1, $permissions = null ) {
        if ( $image_type == '' ) {
            $image_type = $this->image_type;
        }

        if ( $image_type == IMAGETYPE_GIF ) {
            imagegif( $this->image, $filename );
        } else if ( $image_type == IMAGETYPE_PNG ) {
            imagepng( $this->image, $filename, 0, PNG_NO_FILTER );
        } else {
            imagejpeg( $this->image, $filename, $compression );
        }
        $this->destroy();

        if ( $permissions != null ) {
            chmod( $filename, $permissions );
        }
    }

    function output( $image_type = '' ) {
        if ( $image_type == '' )$image_type = $this->image_type;

        if ( $image_type == IMAGETYPE_JPEG ) {
            imagejpeg( $this->image );
        } else if ( $image_type == IMAGETYPE_GIF ) {
            imagegif( $this->image );
        } else if ( $image_type == IMAGETYPE_PNG ) {
            imagepng( $this->image );
        }
        $this->destroy();
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

    // optimize -> change Image Quality
    public function optimize( $source_file, $dst_file = '', $width = 0, $height = 0, $compression = 75 ) {
        if ( $dst_file == '' ) {
            $dst_file = $source_file;
        }

        // sử dụng Imagick (nếu có)
        if ( class_exists( 'Imagick' ) ) {
            /*
             * https://phpimagick.com/Imagick/setCompressionQuality?quality=85&image_path=Lorikeet
             */
            //echo 'Imagick - ' . mime_content_type( $source_file ) . ' - ' . IMAGETYPE_JPEG . ' - ' . $this->image_type . ' - ' . Imagick::COMPRESSION_JPEG . ' <br>' . "\n";

            //
            $image = new\ Imagick( $source_file );
            if ( mime_content_type( $source_file ) == 'image/jpeg' ) {
                $image->setImageFormat( 'jpg' );
                $image->setImageCompression( \Imagick::COMPRESSION_JPEG );
                $image->setImageCompressionQuality( $compression );
            } else {
                $image->setImageCompression( \Imagick::COMPRESSION_UNDEFINED );
                $image->optimizeImageLayers();
            }
            $image->stripImage();
            $image->writeImages( $dst_file, true );
            $image->destroy();
        } else {
            // https://codeigniter4.github.io/userguide/libraries/images.html#image-quality
            $image = \Config\ Services::image()->withFile( $source_file )
                // processing methods
                ->save( $dst_file, $compression );

            // sử dụng php thuần
            /*
            $this->load( $source_file );
            if ( $this->image_type == IMAGETYPE_JPEG ) {
                if ( $width <= 0 ) {
                    $width = $this->getWidth();
                }
                if ( $height <= 0 ) {
                    $height = $this->getHeight();
                }
                $this->resize( $width, $height );
                $this->save( $dst_file );
            }
            $this->destroy();
            */
        }
    }

    public function destroy() {
        imagedestroy( $this->image );
    }

    public function WGR_resize_images( $source_file, $dst_file = '', $new_width = 0, $new_height = 0, $width = 0, $height = 0, $compression = -1 ) {
        return $this->EBE_resize_images( $source_file, $dst_file, [
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

        // kích thước file trước khi resize
        $file_before_size = filesize( $source_file );
        //echo $file_before_size . '<br>' . "\n";
        //die( __FILE__ . ':' . __LINE__ );
        clearstatcache();

        // gán các giá trị mặc định cho việc resize
        $new_width = $this->EBE_default_resize_ops( 'new_width', $ops );
        $new_height = $this->EBE_default_resize_ops( 'new_height', $ops );
        $width = $this->EBE_default_resize_ops( 'width', $ops );
        $height = $this->EBE_default_resize_ops( 'height', $ops );
        $compression = $this->EBE_default_resize_ops( 'compression', $ops, 75 );
        if ( $compression > 100 ) {
            $compression = 100;
        } else if ( $compression <= 0 ) {
            $compression = -1;
        }

        //
        $this->load( $source_file );

        if ( $new_width == 0 && $new_height == 0 ) {
            die( 'new size not set for resize: ' . __FUNCTION__ );
        }
        if ( $width == 0 ) {
            $width = $this->getWidth();
            //echo $width . '<br>' . "\n";
        }
        if ( $height == 0 ) {
            $height = $this->getheight();
            //echo $height . '<br>' . "\n";
        }
        //$a = getimagesize( $source_file );
        //print_r( $a );

        //
        if ( $new_width == $new_height ) {
            if ( $width > $height ) {
                $new_height = $this->resizeToWidth( $new_width );
            } else {
                $new_width = $this->resizeToHeight( $new_height );
            }
            //$this->resize( $new_width, $new_height );
        } else if ( $new_width > $new_height ) {
            $new_height = $this->resizeToWidth( $new_width );
        } else {
            $new_width = $this->resizeToHeight( $new_height );
        }

        //
        $resize_ext = pathinfo( $source_file, PATHINFO_EXTENSION );
        if ( $dst_file == '' ) {
            $dst_file = dirname( $source_file ) . '/' . basename( $source_file, '.' . $resize_ext ) . '-' . $new_width . 'x' . $new_height . '.' . $resize_ext;
            //echo $dst_file . '<br>' . "\n";
        }
        //die( __FILE__ . ':' . __LINE__ );

        // với file .gif cũng không resize -> do lỗi mất frame
        $for_copy_img = false;
        if ( strtolower( $resize_ext ) == 'gif' ) {
            $for_copy_img = 'ERROR copy for resize file for .gif';
        }
        // nếu size cần resize mà nhỏ hơn size chính -> copy luôn cho nhanh
        else if ( $new_width > 0 && $new_width > $width ) {
            $for_copy_img = 'ERROR copy for resize file with new width';
        } else if ( $new_height > 0 && $new_height > $height ) {
            $for_copy_img = 'ERROR copy for resize file with new height';
        }

        // thực hiện copy thay vì resize
        if ( $for_copy_img !== false ) {
            copy( $source_file, $dst_file )or die( $for_copy_img );
            // giải phóng bộ nhớ sau khi copy
            $this->destroy();

            // copy thì size sẽ là size thật không qua resize
            $new_width = $width;
            $new_height = $height;
        }
        // còn lại sẽ thực hiện resize
        else {
            /*
            if ( !file_exists( $dst_file ) ) {
                copy( $source_file, $dst_file )or die( 'ERROR copy for before resize' );
            }
            */
            //$this->save( $dst_file, '', 100 );

            // riêng với jpg thì mình tự save
            if ( $this->image_type == IMAGETYPE_JPEG ) {
                $this->save( $dst_file );
            }
            // các định dạng khác -> sử dụng thư viện của codeigniter 4 -> do mình code chưa chuẩn với mấy định dạng đó
            else {
                // giải phóng bộ nhớ luôn và ngay
                $this->destroy();

                // https://codeigniter4.github.io/userguide/libraries/images.html#resizing-images
                $image = \Config\ Services::image();
                $image->withFile( $source_file )->resize( $new_width, $new_height )
                    // processing methods
                    ->save( $dst_file );
            }
        }
        chmod( $dst_file, 0777 );

        //echo ' <strong>SimpleImage</strong>; ';
        //echo $new_width . '<br>' . "\n";
        //echo $new_height . '<br>' . "\n";
        //die( __FILE__ . ':' . __LINE__ );

        // kích cỡ file sau khi resize
        $file_after_size = filesize( $dst_file );
        if ( $file_after_size > $file_before_size ) {
            $this->optimize( $dst_file, '', $new_width, $new_height );
            clearstatcache();
            $file_after_size = filesize( $dst_file );
        }

        //
        return [
            'file' => basename( $dst_file ),
            'file_size' => $file_after_size,
            'width' => $new_width,
            'height' => $new_height,
        ];
    }
}