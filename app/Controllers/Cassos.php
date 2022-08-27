<?php
/*
 * Chức năng tự động xác thực thanh toán qua ngân hàng của casso
 */
namespace App\ Controllers;

// Libraries
use App\ Libraries\ PostType;
use App\ ThirdParty\ Casso;

//
class Cassos extends Layout {
    // với 1 số controller, sẽ không nạp cái HTML header vào, nên có thêm tham số này để không nạp header nữa
    public $preload_header = false;

    public function __construct() {
        parent::__construct();

        //
        $this->order_model = new\ App\ Models\ Order();
    }

    public function confirm() {
        $data = Casso::phpInput();
        if ( $data === NULL ) {
            $this->result_json_type( [
                'status' => 0,
                'code' => basename( __FILE__ ) . ':' . __LINE__,
                'error' => 'data NULL',
            ] );
        }
        //print_r( $data );
        //die( __CLASS__ . ':' . __LINE__ );

        // TEST
        //$this->result_json_type( $data[ 'data' ]->data );
        //$this->result_json_type( $data[ 'data' ] );
        //$this->result_json_type( $data );

        //
        $msg = [];
        $status = 0;
        foreach ( $data[ 'data' ]->data as $k => $v ) {
            //print_r( $v );
            //continue;

            //
            if ( $v === NULL ) {
                if ( $this->debug_enable === true )echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
                continue;
            }

            // lấy đơn hàng đang chờ
            $order_data = $this->order_model->get_order( array(
                // các kiểu điều kiện where
                'ID' => $v->order_id,
                'post_status' => PostType::PENDING,
            ), array(
                'order_by' => array(
                    'ID' => 'DESC'
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                // trả về COUNT(column_name) AS column_name
                //'selectCount' => 'ID',
                // trả về tổng số bản ghi -> tương tự mysql num row
                //'getNumRows' => 1,
                //'offset' => 0,
                'limit' => 1
            ) );
            // không tìm thấy đơn hàng -> bỏ qua
            if ( empty( $order_data ) ) {
                if ( $this->debug_enable === true )echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";

                $msg[] = 'Order #' . $v->order_id . ' Not found!';

                continue;
            }
            //print_r( $order_data );

            // nếu số tiền nạp vào không đủ -> bỏ qua luôn
            if ( $v->amount < $order_data[ 'post_parent' ] ) {
                if ( $this->debug_enable === true )echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";

                $msg[] = 'Order #' . $v->order_id . ' Amount not enough!';

                continue;
            }

            // UPDATE order
            $this->order_model->update_order( $v->order_id, [
                // SET
                'post_status' => PostType::PRIVATELY,
                //'pinged' => $data[ 'data_string' ],
                'pinged' => json_encode( $v ),
            ], [
                // WHERE
                'post_status' => PostType::PENDING,
                'post_author' => $order_data[ 'post_author' ],
            ] );

            //
            $msg[] = 'Order #' . $v->order_id . ' PAID';
            $status = 1;
        }
        //die( __CLASS__ . ':' . __LINE__ );

        //
        $this->result_json_type( [
            'status' => $status,
            'msg' => json_encode( $msg ),
            'code' => basename( __FILE__ ) . ':' . __LINE__
        ] );
    }
}