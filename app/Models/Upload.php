<?php

namespace App\Models;

// Libraries
use App\Libraries\PostType;

//
class Upload extends Post
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_media($data, $size = '')
    {
        /*
         * size:
         medium
         large
         thumbnail
         medium_large
         */
        //$size = 'medium';
        //print_r( $data );

        // media từ wordpress
        if ($data['post_type'] == PostType::WP_MEDIA) {
            $uri = PostType::WP_MEDIA_URI;
        } else {
            $uri = PostType::MEDIA_URI;
        }

        //
        $src = '';

        // Don't attempt to unserialize data that wasn't serialized going in.
        if (isset($data['post_meta']['_wp_attachment_metadata']) && $data['post_meta']['_wp_attachment_metadata'] != '') {
            //if ( is_serialized( $v[ 'post_meta' ][ '_wp_attachment_metadata' ] ) ) {
            $attachment_metadata = unserialize($data['post_meta']['_wp_attachment_metadata']);
            //}

            //
            //print_r( $attachment_metadata );
            //echo 'size: ' . $size . '<br>' . PHP_EOL;
            if (empty($attachment_metadata)) {
                return '';
            }
            //print_r( $attachment_metadata );
            if ($size == 'all') {
                $list_media = [];
                $src = $attachment_metadata['file'];
                $list_media['full'] = $uri . $src;

                //
                if (isset($attachment_metadata['sizes'])) {
                    foreach ($attachment_metadata['sizes'] as $size_name => $size) {
                        $list_media[$size_name] = $uri . dirname($src) . '/' . $size['file'];
                    }
                }
                //print_r( $list_media );
                //die( __CLASS__ . ':' . __LINE__ );
                return $list_media;
            }

            $src = $attachment_metadata['file'];
            if ($size != '' && isset($attachment_metadata['sizes'][$size])) {
                $src = dirname($src) . '/' . $attachment_metadata['sizes'][$size]['file'];
            }
        } else if (isset($data['post_meta']['_wp_attached_file']) && $data['post_meta']['_wp_attached_file'] != '') {
            $src = $data['post_meta']['_wp_attached_file'];
        }
        $src = $uri . $src;
        //echo 'src: ' . $src . '<br>' . PHP_EOL;
        //die( __CLASS__ . ':' . __LINE__ );

        //
        return $src;
    }

    public function get_thumb($data)
    {
        return $this->get_media($data, PostType::MEDIA_THUMBNAIL);
    }
    public function get_thumbnail($data)
    {
        return $this->get_media($data, PostType::MEDIA_THUMBNAIL);
    }

    public function get_all_media($data)
    {
        return $this->get_media($data, 'all');
    }
}
