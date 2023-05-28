<?php

namespace App\Controllers;

// Libraries
use App\Libraries\OrderType;

//
class Payments extends Layout
{
    // với 1 số controller, sẽ không nạp cái HTML header vào, nên có thêm tham số này để không nạp header nữa
    public $preload_header = false;

    public function __construct()
    {
        parent::__construct();

        //
        $this->order_model = new \App\Models\Order();
        $this->payment_model = new \App\Models\Payment();
    }

    // hàm này là để ajax request tới server kiểm tra xem đơn hàng đã được thanh toán chưa
    public function check_paid()
    {
        // nếu không có id user -> lỗi
        if ($this->current_user_id <= 0) {
            $this->result_json_type([
                'code' => __CLASS__ . ':' . __LINE__,
                'error' => 'Không xác định được thông tin tài khoản'
            ]);
        }

        // xem có gồm thông số đơn hàng trên này không
        $order_id = $this->MY_post('order_id', 0);
        // nếu không có id đơn hàng -> lỗi
        if ($order_id <= 0) {
            $this->result_json_type([
                'code' => __CLASS__ . ':' . __LINE__,
                'error' => 'Không xác định được thông tin đơn hàng'
            ]);
        }

        //
        $data = $this->order_model->get_order(
            array(
                // các kiểu điều kiện where
                'ID' => $order_id,
                'post_status' => OrderType::PRIVATELY,
                'post_author' => $this->current_user_id,
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
            ),
            'ID'
        );

        //
        if (empty($data)) {
            $this->result_json_type([
                'code' => __CLASS__ . ':' . __LINE__,
                'error' => 'Đơn hàng chưa được thanh toán'
            ]);
        }

        //
        $this->result_json_type([
            'status' => $data['ID'],
            'code' => __CLASS__ . ':' . __LINE__
        ]);
    }

    protected function getOrderId()
    {
        //
        $order_id = $this->MY_post('order_id', 0);
        $order_id *= 1;

        if ($order_id <= 0) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => 'Không xác định được hóa đơn cần xử lý'
            ]);
        }
        return $order_id;
    }

    public function updateOrder($order_id, $update_data)
    {
        //
        $result_update = $this->order_model->update_order($order_id, $update_data, [
            // WHERE
            'post_status' => OrderType::PENDING,
            'post_author' => $this->current_user_id,
        ]);
        //var_dump( $result_update );

        //
        if ($result_update) {
            $this->result_json_type([
                'order_id' => $order_id,
                'order_money' => $update_data['order_money'],
                'order_discount' => $update_data['order_discount'],
                'order_bonus' => $update_data['order_bonus'],
                'total_price' => $update_data['order_money'] - $update_data['order_discount'],
            ]);
        }
        $this->result_json_type([
            'code' => __LINE__,
            'error' => 'LỖI cập nhật đơn hàng'
        ]);
    }

    // thay đổi gói cước cố định trong config
    public function change_period()
    {
        //
        $order_id = $this->getOrderId('order_id', 0);

        // xác định gói cước
        $checkout_config = $this->payment_model->getCheckoutConfig();
        //print_r($checkout_config);
        //die(__CLASS__ . ':' . __LINE__);

        // xác định ID gói cước
        $period_id = $this->MY_post('val', 0);
        $period_id *= 1;

        //
        $arr_period_price = $checkout_config['period_price'];
        if (!isset($arr_period_price[$period_id])) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => 'period not found!'
            ]);
        }
        $arr_period_discount = $checkout_config['period_discount'];
        $arr_period_bonus = $checkout_config['period_bonus'];

        //
        $update_data = [
            // ID gói cước
            'order_period' => $period_id,
            // giá trị đơn hàng
            'order_money' => $arr_period_price[$period_id],
            // giảm giá
            'order_discount' => $arr_period_discount[$period_id],
            // tặng thêm
            'order_bonus' => $arr_period_bonus[$period_id],
        ];

        // TEST
        if (1 > 2) {
            $this->result_json_type([
                'code' => __LINE__,
                'update_data' => $update_data,
                'period_price' => $arr_period_price,
                'period_discount' => $arr_period_discount,
                'test' => $checkout_config,
            ]);
        }

        //
        return $this->updateOrder($order_id, $update_data);
    }

    // chức năng thay đổi số tiền trong đơn hàng
    public function change_fund()
    {
        //
        $order_id = $this->getOrderId('order_id', 0);

        // xác định gói cước
        $checkout_config = $this->payment_model->getCheckoutConfig();
        //print_r($checkout_config);
        //die(__CLASS__ . ':' . __LINE__);

        //
        $order_money = $this->MY_post('val', 0);
        $order_money *= 1;

        if ($order_money <= $checkout_config['min_product_price']) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => 'Không xác định được số tiền cần nạp'
            ]);
        }

        //
        return $this->updateOrder($order_id, [
            // giá trị đơn hàng
            'order_money' => $order_money,
        ]);
    }
}
