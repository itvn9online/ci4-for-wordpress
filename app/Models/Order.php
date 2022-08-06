<?php

namespace App\ Models;

// Libraries
use App\ Libraries\ OrderType;

//
class Order extends Post {
    public $table = 'orders';
    public $metaTable = 'ordermeta';
    public $post_type = OrderType::ORDER;

    //
    public function __construct() {
        parent::__construct();
    }

    public function insert_order( $data_insert ) {
        //$data_insert[ 'guid' ] = ''; // danh sách IDs sản phẩm
        //$data_insert[ 'post_excerpt' ] = ''; // danh sách sản phẩm dạng json
        //$data_insert[ 'post_parent' ] = ''; // tổng giá trị giỏ hàng
        //$data_insert[ 'comment_count' ] = ''; // hạn sử dụng/ thời gian bảo hành
        //$data_insert[ 'pinged' ] = ''; // thông tin phản hồi từ các bên thanh toán trung gian
        $data_insert[ 'post_status' ] = OrderType::PENDING;
        $data_insert[ 'post_type' ] = $this->post_type;

        // tự động tạo mã đơn hàng nếu chưa có
        if ( !isset( $data_insert[ 'post_name' ] ) || $data_insert[ 'post_name' ] == '' ) {
            if ( isset( $data_insert[ 'post_author' ] ) && $data_insert[ 'post_author' ] > 0 ) {
                $data_insert[ 'post_name' ] = $data_insert[ 'post_author' ] . 'EB' . date( 'ymdHi' );
            }
        }

        //
        if ( isset( $data_insert[ 'post_excerpt' ] ) && is_array( $data_insert[ 'post_excerpt' ] ) ) {
            $data_insert[ 'post_excerpt' ] = json_encode( $data_insert[ 'post_excerpt' ] );
        }

        //
        return parent::insert_post( $data_insert );
    }

    public function get_order( $where = [], $filter = [] ) {
        $where[ 'post_type' ] = $this->post_type;

        //
        return $this->base_model->select( '*', $this->table, $where, $filter );
    }

    public function update_order( $order_id, $data, $where = [] ) {
        $where[ 'post_type' ] = $this->post_type;

        //
        //return $this->base_model->update_multiple( $this->table, $data, $where, $filter );
        return parent::update_post( $order_id, $data, $where );
    }
}