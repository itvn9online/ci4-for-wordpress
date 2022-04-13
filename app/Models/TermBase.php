<?php

namespace App\ Models;

//
class TermBase extends EbModel {
    public $table = 'terms';
    public $primaryKey = 'term_id';

    public $metaTable = 'termmeta';
    //public $metaKey = 'meta_id';

    public $taxTable = 'term_taxonomy';
    public $taxKey = 'term_taxonomy_id';

    public $relaTable = 'term_relationships';
    public $relaKey = 'object_id';

    //protected $primaryTaxonomy = 'category';

    public function __construct() {
        parent::__construct();
    }

    // vòng lặp đệ quy -> tạo option cho phần select của term
    public function term_add_child_option( $data, $term_id = 0, $gach_ngang = '' ) {
        if ( empty( $data ) ) {
            return false;
        }
        //print_r( $data );
        //return false;

        //
        foreach ( $data as $v ) {
            //print_r( $v );
            //continue;
            if ( $v[ 'term_id' ] == $term_id || $v[ 'parent' ] == $term_id ) {
                continue;
            }
            echo '<option value="' . $v[ 'term_id' ] . '">' . $gach_ngang . $v[ 'name' ] . '</option>';

            //
            $this->term_add_child_option( $v[ 'child_term' ], $term_id, $gach_ngang . '&#8212; ' );
        }
    }

    public function terms_meta_post( $data ) {
        //print_r( $data );
        foreach ( $data as $k => $v ) {
            //print_r( $v );

            // nếu không có dữ liệu của term meta
            if ( $v[ 'term_meta_data' ] === NULL ) {
                $term_meta_data = $this->arr_meta_terms( $v[ 'term_id' ] );
                //print_r( $term_meta_data );
                //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";

                //
                $this->base_model->update_multiple( $this->table, [
                    'term_meta_data' => json_encode( $term_meta_data ),
                ], [
                    'term_id' => $v[ 'term_id' ],
                ] );

                // thông báo kiểu dữ liệu trả về
                $data[ $k ][ 'term_meta_data' ] = 'query';
            } else {
                $term_meta_data = ( array )json_decode( $v[ 'term_meta_data' ] );
                //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";

                // thông báo kiểu dữ liệu trả về
                $data[ $k ][ 'term_meta_data' ] = 'cache';
            }
            $data[ $k ][ 'term_meta' ] = $term_meta_data;
        }
        //print_r( $data );

        //
        return $data;
    }

    // trả về danh sách meta terms dưới dạng key => value
    public function arr_meta_terms( $term_id ) {
        $data = $this->get_meta_terms( $term_id, '', $this->metaTable );

        //
        $meta_data = [];
        foreach ( $data as $k => $v ) {
            $meta_data[ $v[ 'meta_key' ] ] = $v[ 'meta_value' ];
        }
        return $meta_data;
    }

    public function get_meta_terms( $term_id, $key = '' ) {
        // lấy theo key cụ thể
        if ( $key != '' ) {
            $data = $this->base_model - select( '*', $this->metaTable, array(
                // các kiểu điều kiện where
                'term_id' => $term_id,
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
            'term_id' => $term_id
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

    // hàm này sẽ kiểm tra xem có meta tương ứng của post không, có thì in ra luôn
    function return_meta_term( $data, $key, $default_value = '' ) {
        if ( isset( $data[ $key ] ) ) {
            return $data[ $key ];
        } else if ( isset( $data[ 'term_meta' ] ) ) {
            return $this->return_meta_term( $data[ 'term_meta' ], $key );
        }

        //
        return $default_value;
    }

    //
    function echo_meta_term( $data, $key, $default_value = '' ) {
        echo $this->return_meta_term( $data[ 'term_meta' ], $key, $default_value );
    }

    // đồng bộ tham số đầu vào
    public function sync_term_parms( $prams, $ops ) {
        // nếu đầu vào không phải array
        if ( !is_array( $prams ) ) {
            if ( empty( $prams ) ) {
                $prams = [];
                //return debug_backtrace()[ 1 ][ 'function' ] . ' $prams is NULL!';
            }
            // tự tạo theo term id
            else if ( is_numeric( $prams ) ) {
                $prams = [
                    'term_id' => $prams
                ];
            }
            // hoặc slug
            else if ( is_string( $prams ) ) {
                $prams = [
                    'slug' => $prams
                ];
            }
        }

        if ( !isset( $prams[ 'term_id' ] ) ) {
            $prams[ 'term_id' ] = isset( $ops[ 'term_id' ] ) ? $ops[ 'term_id' ] : 0;
        }
        if ( !isset( $prams[ 'limit' ] ) ) {
            $prams[ 'limit' ] = isset( $ops[ 'limit' ] ) ? $ops[ 'limit' ] : 0;
        }
        if ( !isset( $prams[ 'offset' ] ) ) {
            $prams[ 'offset' ] = isset( $ops[ 'offset' ] ) ? $ops[ 'offset' ] : 0;
        }
        if ( !isset( $prams[ 'order_by' ] ) ) {
            $prams[ 'order_by' ] = isset( $ops[ 'order_by' ] ) ? $ops[ 'order_by' ] : [];
        }
        //print_r( $prams );
        //die( 'hkj dfsdfgsdgsdgs' );

        //
        return $prams;
    }

    // trả về key cho term cache
    public function key_cache( $id ) {
        return 'term-' . $id . '-';
    }
    // cache cho phần term -> gán key theo mẫu thống nhất để sau còn xóa cache cho dễ
    public function the_cache( $id, $key, $value = '', $time = MEDIUM_CACHE_TIMEOUT ) {
        return $this->base_model->scache( $this->key_cache( $id ) . $key, $value, $time );
    }
}