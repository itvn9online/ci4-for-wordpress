<?php

namespace App\ Models;

// Libraries
//use App\ Libraries\ LanguageCost;
//use App\ Libraries\ PostType;
//use App\ Libraries\ TaxonomyType;
//use App\ Libraries\ DeletedStatus;

//
class PostMeta extends PostBase {
    public function __construct() {
        parent::__construct();
    }

    // lấy về danh sách meta post cho toàn bộ data được truyền vào
    function list_meta_post( $data ) {
        foreach ( $data as $k => $v ) {
            //print_r( $v );
            $data[ $k ][ 'post_meta' ] = $this->arr_meta_post( $v[ 'ID' ] );
        }
        //print_r( $data );

        //
        return $data;
    }

    // thêm post meta
    function insert_meta_post( $meta_data, $post_id ) {
        if ( !is_array( $meta_data ) || empty( $meta_data ) ) {
            return false;
        }
        //print_r( $meta_data );

        // lấy toàn bộ meta của post này
        $meta_exist = $this->arr_meta_post( $post_id );
        //print_r( $meta_exist );

        // xử lý riêng đối với post_category
        if ( isset( $meta_data[ 'post_category' ] ) && gettype( $meta_data[ 'post_category' ] ) == 'array' ) {
            $meta_data[ 'post_category' ] = implode( ',', $meta_data[ 'post_category' ] );
        }
        if ( isset( $meta_data[ 'post_category' ] ) ) {
            $this->term_model->insert_term_relationships( $post_id, $meta_data[ 'post_category' ] );
        }

        // xử lý cho ảnh đại diện -> thêm các size ảnh khác để sau còn tùy ý sử dụng
        if ( isset( $meta_data[ 'image' ] ) && $meta_data[ 'image' ] != '' ) {
            $meta_data[ 'image_large' ] = $this->get_img_by_size( $meta_data[ 'image' ], 'large' );
            $meta_data[ 'image_medium_large' ] = $this->get_img_by_size( $meta_data[ 'image' ], 'medium_large' );
            $meta_data[ 'image_medium' ] = $this->get_img_by_size( $meta_data[ 'image' ], 'medium' );
            $meta_data[ 'image_thumbnail' ] = $this->get_img_by_size( $meta_data[ 'image' ], 'thumbnail' );
            $meta_data[ 'image_webp' ] = $this->get_img_by_size( $meta_data[ 'image' ], 'medium' );

            // phiên bản webp -> có lệnh riêng để tối ưu
            $create_webp = \App\ Libraries\ MyImage::webpConvert( PUBLIC_PUBLIC_PATH . $meta_data[ 'image_webp' ] );
            if ( $create_webp != '' ) {
                $meta_data[ 'image_webp' ] = $create_webp;
            }
            //echo $meta_data[ 'image_webp' ] . '<br>' . "\n";
        }

        // xem các meta nào không có trong lần update này -> XÓA
        foreach ( $meta_exist as $k => $v ) {
            if ( !isset( $meta_data[ $k ] ) ) {
                //echo 'DELETE ' . $k . ' ' . $v . '<br>' . "\n";

                //
                $this->base_model->delete_multiple( $this->metaTable, [
                    'post_id' => $post_id,
                    'meta_key' => $k,
                ] );
            }
        }

        //
        $insert_meta = [];
        $update_meta = [];
        foreach ( $meta_data as $k => $v ) {
            // thêm vào mảng update nếu có rồi
            if ( isset( $meta_exist[ $k ] ) ) {
                $update_meta[ $k ] = $v;
            }
            // thêm vào mảng insert nếu chưa có
            else if ( $v != '' ) {
                $insert_meta[ $k ] = $v;
            }
        }

        // các meta chưa có thì insert
        //print_r( $insert_meta );
        foreach ( $insert_meta as $k => $v ) {
            $this->base_model->insert( $this->metaTable, [
                'post_id' => $post_id,
                'meta_key' => $k,
                'meta_value' => $v,
            ] );
        }

        // các meta có rồi thì update
        //print_r( $update_meta );
        foreach ( $update_meta as $k => $v ) {
            $this->base_model->update_multiple( $this->metaTable, [
                'meta_value' => $v,
            ], [
                'post_id' => $post_id,
                'meta_key' => $k,
            ] );
        }

        //
        //die( __FILE__ . ':' . __LINE__ );
        return true;
    }

    function get_meta_post( $post_id, $key = '' ) {
        // lấy theo key cụ thể
        if ( $key != '' ) {
            $data = $this->base_model - select( '*', $this->metaTable, array(
                // các kiểu điều kiện where
                'post_id' => $post_id,
                'meta_key' => $key,
            ), array(
                'order_by' => array(
                    'meta_id' => 'DESC'
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 2,
                'limit' => 1
            ) );

            //
            if ( empty( $data ) ) {
                return '';
            }
            return $data[ 'meta_value' ];
        }

        // lấy toàn bộ meta
        return $this->base_model->select( '*', $this->metaTable, array(
            // các kiểu điều kiện where
            'post_id' => $post_id
        ), array(
            'group_by' => array(
                'meta_key',
            ),
            'order_by' => array(
                'meta_id' => 'DESC'
            ),
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 2,
            //'limit' => 3
        ) );
    }

    // trả về danh sách meta post dưới dạng key => value
    function arr_meta_post( $post_id ) {
        $data = $this->get_meta_post( $post_id, '', $this->metaTable );
        //print_r( $data );

        //
        $meta_data = [];
        foreach ( $data as $k => $v ) {
            $meta_data[ $v[ 'meta_key' ] ] = $v[ 'meta_value' ];
        }

        // hỗ trợ kiểu danh mục từ echbaydotcom
        if ( !isset( $meta_data[ 'post_category' ] ) || $meta_data[ 'post_category' ] == '' ) {
            $sql = $this->base_model->select( 'term_taxonomy_id', 'wp_term_relationships', array(
                // các kiểu điều kiện where
                'object_id' => $post_id
            ), array(
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 2,
                //'limit' => 3
            ) );
            //print_r( $sql );
            $term_relationships = [];
            foreach ( $sql as $k => $v ) {
                $term_relationships[] = $v[ 'term_taxonomy_id' ];
            }
            $meta_data[ 'post_category' ] = implode( ',', $term_relationships );
        }

        //
        return $meta_data;
    }

    // hàm này sẽ kiểm tra xem có meta tương ứng của post không, có thì in ra luôn
    function return_meta_post( $data, $key, $default_value = '' ) {
        if ( isset( $data[ $key ] ) ) {
            return $data[ $key ];
        } else if ( isset( $data[ 'post_meta' ] ) ) {
            return $this->return_meta_post( $data[ 'post_meta' ], $key );
        }

        //
        return $default_value;
    }

    function show_meta_post( $data, $key, $default_value = '' ) {
        echo $this->return_meta_post( $data, $key, $default_value );
    }

    // tương tự show meta post -> chỉ khác là sẽ truyền thẳng data post_meta vào luôn
    function echo_meta_post( $data, $key, $default_value = '' ) {
        echo $this->return_meta_post( $data[ 'post_meta' ], $key, $default_value );
    }

    // trả về ảnh với kích thước khác -> dựa theo ảnh gốc
    function get_img_by_size( $result, $file_size ) {
        // tạo path tuyệt đối để kiểm tra
        //echo PUBLIC_PUBLIC_PATH . '<br>' . "\n";
        $check_size = PUBLIC_PUBLIC_PATH . str_replace( DYNAMIC_BASE_URL, '', $result );
        //echo $check_size . '<br>' . "\n";

        // kiểm tra xem có tồn tại không path tuyệt đối này không
        if ( file_exists( $check_size ) ) {
            $file_ext = pathinfo( $check_size, PATHINFO_EXTENSION );
            //echo $file_ext . '<br>' . "\n";

            // kiểm tra xem có size tương ứng không
            $check_size = str_replace( '.' . $file_ext, '-' . $file_size . '.' . $file_ext, $result );
            //echo $check_size . '<br>' . "\n";
            // có thì tạo URL tương đối để trả về
            if ( file_exists( $check_size ) ) {
                $result = str_replace( PUBLIC_PUBLIC_PATH, '', $check_size );
                //echo $result . '<br>' . "\n";
            }
        }

        //
        return $result;
    }

    // lấy ảnh thumbnail cho phần danh sách bài viế trong admin -> admin thì không cần ảnh đẹp -> lấy mặc định thumbnail
    function get_list_thumbnail( $data, $file_size = 'thumbnail' ) {
        return $this->get_post_image( $data, 'image', 'images/noavatar.png', $file_size );
    }

    // trả về URL ảnh đại diện của bài viết
    function get_post_image( $data, $key = 'image', $default_value = 'images/noavatar.png', $file_size = '' ) {
        // nếu có yêu cầu lấy size ảnh khác thì kiểm tra size đó có tồn tại không
        if ( $file_size != '' ) {
            //echo $file_size . '<br>' . "\n";
            $result = $this->return_meta_post( $data, $key . '_' . $file_size );
            if ( $result == '' ) {
                $result = $this->return_meta_post( $data, $key );
            }
        } else {
            $result = $this->return_meta_post( $data, $key );
        }
        //echo $result . '<br>' . "\n";

        // hỗ trợ dữ liệu từ echbaydotcom
        if ( $result == '' ) {
            $result = $this->return_meta_post( $data, '_eb_product_avatar' );
            //echo $result . '<br>' . "\n";
        }

        // nếu không tìm được thì trả về dữ liệu trống
        if ( $result == '' ) {
            $result = $default_value;
        }

        //
        return $result;
    }

    // lấy ảnh đại diện cho bài viết
    function get_post_thumbnail( $data ) {
        return $this->get_list_thumbnail( $data, $this->cf_thumbnail_size );
    }
}