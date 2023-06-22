<?php
/*
 * Chức năng tự động xác thực thanh toán qua ngân hàng của casso
 */

namespace App\Controllers;

// Libraries
use App\Libraries\OrderType;
use App\ThirdParty\Casso;

//
class Cassos extends Payments
{
    public function __construct()
    {
        parent::__construct();
    }

    // nhận thông tin chuyển khoản từ casso.vn và chuyển trạng thái đơn hàng nếu thấy
    public function confirm()
    {
        $data = Casso::phpInput($this->debug_enable);
        if ($data === NULL) {
            $this->result_json_type([
                'status' => 0,
                'code' => __CLASS__ . ':' . __LINE__,
                'error' => 'data NULL',
            ]);
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
        foreach ($data['data']->data as $k => $v) {
            //print_r( $v );
            //continue;

            //
            if ($v === NULL) {
                if ($this->debug_enable === true) {
                    echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
                }
                continue;
            }

            // lấy đơn hàng đang chờ
            $order_data = $this->order_model->get_order(
                array(
                    // các kiểu điều kiện where
                    'ID' => $v->order_id,
                    'post_status' => OrderType::PENDING,
                ),
                array(
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
                )
            );
            // không tìm thấy đơn hàng -> bỏ qua
            if (empty($order_data)) {
                if ($this->debug_enable === true) {
                    echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
                }

                $msg[] = 'Order #' . $v->order_id . ' Not found!';

                continue;
            }
            //print_r( $order_data );

            // nếu số tiền nạp vào không đủ -> bỏ qua luôn
            if ($v->amount < $order_data['order_money']) {
                if ($this->debug_enable === true) {
                    echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
                }

                $msg[] = 'Order #' . $v->order_id . ' Amount not enough!';

                continue;
            }

            // UPDATE order
            $result_update = $this->order_model->update_order($v->order_id, [
                // SET
                'post_status' => OrderType::PRIVATELY,
                //'pinged' => $data[ 'data_string' ],
                'pinged' => json_encode($v),
            ], [
                // WHERE
                'post_status' => OrderType::PENDING,
                'post_author' => $order_data['post_author'],
            ]);

            // cập nhật lại số dư tài khoản người dùng
            if ($result_update === true) {
                $this->order_model->update_user_fund($v->order_id, $order_data['post_author']);
            }

            //
            $msg[] = 'Order #' . $v->order_id . ' PAID';
            $status = 1;
        }
        //die( __CLASS__ . ':' . __LINE__ );

        //
        $this->result_json_type([
            'status' => $status,
            'msg' => json_encode($msg),
            'code' => __CLASS__ . ':' . __LINE__
        ]);
    }
}
