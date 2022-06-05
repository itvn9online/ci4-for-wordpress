<?php

namespace App\ Models;

// Libraries
//use App\ Libraries\ LanguageCost;
use App\ Libraries\ PostType;
use App\ Libraries\ TaxonomyType;
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
            //var_dump( $v[ 'post_meta_data' ] );

            // nếu không có dữ liệu của post meta
            //if ( empty( $v[ 'post_meta_data' ] ) ) {
            if ( $v[ 'post_meta_data' ] === NULL ) {
                $post_meta_data = $this->arr_meta_post( $v[ 'ID' ] );
                //print_r( $post_meta_data );
                //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";

                //
                $this->base_model->update_multiple( $this->table, [
                    'post_meta_data' => json_encode( $post_meta_data ),
                ], [
                    'ID' => $v[ 'ID' ],
                ] );

                // thông báo kiểu dữ liệu trả về
                $data[ $k ][ 'post_meta_data' ] = 'query';
            } else {
                $post_meta_data = ( array )json_decode( $v[ 'post_meta_data' ] );
                //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";

                // thông báo kiểu dữ liệu trả về
                $data[ $k ][ 'post_meta_data' ] = 'cache';
            }
            $data[ $k ][ 'post_meta' ] = $post_meta_data;
        }
        //print_r( $data );

        //
        return $data;
    }

    // trả về bản ghi số 0 -> thường dùng khi lấy meta của 1 post
    public function the_meta_post( $data ) {
        return $this->list_meta_post( [ $data ] )[ 0 ];
    }

    // thêm post meta
    function insert_meta_post( $meta_data, $post_id, $clear_meta = true ) {
        if ( !is_array( $meta_data ) || empty( $meta_data ) ) {
            return false;
        }
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
        //print_r( $meta_data );
        //die( __CLASS__ . ':' . __LINE__ );

        // lấy toàn bộ meta của post này
        $meta_exist = $this->arr_meta_post( $post_id, false );
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
        //print_r( $meta_exist );

        // xử lý riêng đối với post category và tags
        $term_relationships = [];
        if ( isset( $meta_data[ 'post_category' ] ) ) {
            if ( gettype( $meta_data[ 'post_category' ] ) == 'array' ) {
                foreach ( $meta_data[ 'post_category' ] as $v ) {
                    $term_relationships[] = $v;
                }
                $meta_data[ 'post_category' ] = implode( ',', $meta_data[ 'post_category' ] );
            } else {
                $term_relationships[] = $meta_data[ 'post_category' ];
            }
        }
        if ( isset( $meta_data[ 'post_tags' ] ) ) {
            if ( gettype( $meta_data[ 'post_tags' ] ) == 'array' ) {
                foreach ( $meta_data[ 'post_tags' ] as $v ) {
                    $term_relationships[] = $v;
                }
                $meta_data[ 'post_tags' ] = implode( ',', $meta_data[ 'post_tags' ] );
            } else {
                $term_relationships[] = $meta_data[ 'post_tags' ];
            }
        }
        if ( !empty( $term_relationships ) ) {
            $term_relationships = array_unique( $term_relationships );

            // gán danh sách term ID vào đây để đỡ phải select nhiều
            $post_relationships = implode( ',', $term_relationships );
            //$meta_data[ 'post_relationships' ] = $post_relationships;

            $this->term_model->insert_term_relationships( $post_id, $post_relationships );
        }
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
        //print_r( $meta_data );

        // xử lý cho ảnh đại diện -> thêm các size ảnh khác để sau còn tùy ý sử dụng
        if ( isset( $meta_data[ 'image' ] ) ) {
            if ( $meta_data[ 'image' ] != '' ) {
                $origin_size = $meta_data[ 'image' ];
                $file_ext = pathinfo( $origin_size, PATHINFO_EXTENSION );
                foreach ( PostType::media_size() as $k => $v ) {
                    $origin_size = str_replace( '-' . $k . '.' . $file_ext, '.' . $file_ext, $origin_size );
                }
                $meta_data[ 'image_large' ] = $this->get_img_by_size( $origin_size, 'large', $file_ext );
                $meta_data[ 'image_medium_large' ] = $this->get_img_by_size( $origin_size, 'medium_large', $file_ext );
                $meta_data[ 'image_medium' ] = $this->get_img_by_size( $origin_size, 'medium', $file_ext );
                $meta_data[ 'image_thumbnail' ] = $this->get_img_by_size( $origin_size, 'thumbnail', $file_ext );
                $meta_data[ 'image_webp' ] = $meta_data[ 'image_medium' ];

                // phiên bản webp -> có lệnh riêng để tối ưu
                $create_webp = \App\ Libraries\ MyImage::webpConvert( PUBLIC_PUBLIC_PATH . $meta_data[ 'image_webp' ] );
                if ( $create_webp != '' ) {
                    $meta_data[ 'image_webp' ] = $create_webp;
                }
                //echo $meta_data[ 'image_webp' ] . '<br>' . "\n";
            } else {
                $meta_data[ 'image_large' ] = '';
                $meta_data[ 'image_medium_large' ] = '';
                $meta_data[ 'image_medium' ] = '';
                $meta_data[ 'image_thumbnail' ] = '';
                $meta_data[ 'image_webp' ] = '';
            }
        }

        // xem các meta nào không có trong lần update này -> XÓA
        if ( $clear_meta === true ) {
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
        }

        //
        $insert_meta = [];
        $update_meta = [];
        foreach ( $meta_data as $k => $v ) {
            //print_r( $v );
            if ( is_array( $v ) ) {
                if ( !empty( $v ) ) {
                    $v = implode( ',', $v );
                } else {
                    $v = '';
                }
                $meta_data[ $k ] = $v;
            }
            //echo $v . '<br>' . "\n";

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
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
        //print_r( $insert_meta );
        foreach ( $insert_meta as $k => $v ) {
            $this->base_model->insert( $this->metaTable, [
                'post_id' => $post_id,
                'meta_key' => $k,
                'meta_value' => $v,
            ] );
        }

        // các meta có rồi thì update
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
        //print_r( $update_meta );
        foreach ( $update_meta as $k => $v ) {
            $this->base_model->update_multiple( $this->metaTable, [
                'meta_value' => $v,
            ], [
                'post_id' => $post_id,
                'meta_key' => $k,
            ] );
        }

        // cập nhật post meta vào cột của post để đỡ phải query nhiều
        $this->base_model->update_multiple( $this->table, [
            'post_meta_data' => json_encode( $meta_data ),
        ], [
            'ID' => $post_id,
        ] );

        //
        //die( __CLASS__ . ':' . __LINE__ );
        return true;
    }

    function set_meta_post( $post_id, $key = '', $v = '' ) {
        // kiểm tra xem meta này có chưa
        $check_meta_exist = $this->get_meta_post( $post_id, $key );

        // chưa có thì insert
        if ( $check_meta_exist == '' ) {
            $this->base_model->insert( $this->metaTable, [
                'post_id' => $post_id,
                'meta_key' => $key,
                'meta_value' => $v,
            ] );
        }
        // có rồi thì update
        else {
            $this->base_model->update_multiple( $this->metaTable, [
                'meta_value' => $v,
            ], [
                'post_id' => $post_id,
                'meta_key' => $key,
            ] );
        }
    }

    function get_meta_post( $post_id, $key = '' ) {
        // lấy theo key cụ thể
        if ( $key != '' ) {
            $data = $this->base_model->select( '*', $this->metaTable, array(
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

    /*
     * trả về danh sách meta post dưới dạng key => value
     * get_relationships: lấy danh sách relationships từ database nếu không có meta -> khi cần check meta exist thì không lấy, để tránh việc post_category luôn tồn tại -> lệnh update được gọi nhưng không update được
     */
    function arr_meta_post( $post_id, $get_relationships = true ) {
        $data = $this->get_meta_post( $post_id );
        //print_r( $data );

        //
        $meta_data = [];
        foreach ( $data as $k => $v ) {
            $meta_data[ $v[ 'meta_key' ] ] = $v[ 'meta_value' ];
        }
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
        //print_r( $meta_data );

        // hỗ trợ kiểu danh mục từ echbaydotcom
        /*
        if ( !isset( $meta_data[ 'post_category' ] ) ) {
            echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
        } else if ( empty( $meta_data[ 'post_category' ] ) ) {
            echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
        }
        */
        if ( $get_relationships === true ) {
            if ( !isset( $meta_data[ 'post_category' ] ) || empty( $meta_data[ 'post_category' ] ) ) {
                //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
                //print_r( $meta_data );

                //
                $sql = $this->base_model->select( 'term_relationships.term_taxonomy_id, term_taxonomy.taxonomy', 'term_relationships', array(
                    // các kiểu điều kiện where
                    'object_id' => $post_id
                ), array(
                    'join' => array(
                        'term_taxonomy' => 'term_taxonomy.term_id = term_relationships.term_taxonomy_id'
                    ),
                    // hiển thị mã SQL để check
                    //'show_query' => 1,
                    // trả về câu query để sử dụng cho mục đích khác
                    //'get_query' => 1,
                    //'offset' => 2,
                    //'limit' => 3
                ) );
                //print_r( $sql );
                $term_relationships = [
                    TaxonomyType::POSTS => [],
                    TaxonomyType::TAGS => [],
                ];
                foreach ( $sql as $k => $v ) {
                    $term_relationships[ $v[ 'taxonomy' ] ][] = $v[ 'term_taxonomy_id' ];
                }
                //print_r( $term_relationships );
                //die( __CLASS__ . ':' . __LINE__ );
                foreach ( $term_relationships as $k => $v ) {
                    $meta_data[ 'post_' . $k ] = implode( ',', $v );
                }
                //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
                //print_r( $meta_data );
                //die( __CLASS__ . ':' . __LINE__ );
            }
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
    function get_img_by_size( $result, $file_size, $file_ext = '' ) {
        // tạo path tuyệt đối để kiểm tra
        //echo PUBLIC_PUBLIC_PATH . '<br>' . "\n";
        $check_size = PUBLIC_PUBLIC_PATH . str_replace( DYNAMIC_BASE_URL, '', $result );
        //echo $check_size . ':' . __LINE__ . '<br>' . "\n";

        // kiểm tra xem có tồn tại không path tuyệt đối này không
        if ( file_exists( $check_size ) ) {
            if ( $file_ext == '' ) {
                $file_ext = pathinfo( $check_size, PATHINFO_EXTENSION );
                //echo $file_ext . ':' . __LINE__ . '<br>' . "\n";
            }

            // kiểm tra xem có size tương ứng không
            $check_size = str_replace( '.' . $file_ext, '-' . $file_size . '.' . $file_ext, $result );
            //echo $check_size . ':' . __LINE__ . '<br>' . "\n";
            // có thì tạo URL tương đối để trả về
            if ( file_exists( $check_size ) ) {
                $result = str_replace( PUBLIC_PUBLIC_PATH, '', $check_size );
                echo $result . ':' . __LINE__ . '<br>' . "\n";
            }
        }

        //
        return $result;
    }

    // lấy ảnh thumbnail cho phần danh sách bài viế trong admin -> admin thì không cần ảnh đẹp -> lấy mặc định thumbnail
    public function get_list_thumbnail( $data, $file_size = 'thumbnail' ) {
        return $this->get_post_image( $data, 'image', 'images/noavatar.png', $file_size );
    }

    // trả về URL ảnh đại diện của bài viết
    public function get_post_image( $data, $key = 'image', $default_value = 'images/noavatar.png', $file_size = '' ) {
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
    public function get_post_thumbnail( $data ) {
        return $this->get_list_thumbnail( $data, $this->cf_thumbnail_size );
    }
}