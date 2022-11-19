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
    }

    // hàm này là để ajax request tới server kiểm tra xem đơn hàng đã được thanh toán chưa
    public function check_paid()
    {
        // nếu không có id đơn hàng -> lỗi
        if ($this->current_user_id <= 0) {
            $this->result_json_type([
                'code' => basename(__FILE__) . ':' . __LINE__,
                'error' => 'Không xác định được thông tin tài khoản'
            ]);
        }

        // xem có gồm thông số đơn hàng trên này không
        $order_id = $this->MY_post('order_id', 0);
        // nếu không có id đơn hàng -> lỗi
        if ($order_id <= 0) {
            $this->result_json_type([
                'code' => basename(__FILE__) . ':' . __LINE__,
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
                'code' => basename(__FILE__) . ':' . __LINE__,
                'error' => 'Đơn hàng chưa được thanh toán'
            ]);
        }

        //
        $this->result_json_type([
            'status' => $data['ID'],
            'code' => basename(__FILE__) . ':' . __LINE__
        ]);
    }

    // chức năng thay đổi số tiền trong đơn hàng
    public function change_period()
    {
        //
        $order_money = $this->MY_post('val', 0);
        $order_money *= 1;

        if ($order_money <= 0) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => 'Không xác định được số tiền cần nạp'
            ]);
        }

        //
        $order_id = $this->MY_post('order_id', 0);
        $order_id *= 1;

        if ($order_id <= 0) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => 'Không xác định được hóa đơn cần xử lý'
            ]);
        }

        //
        $result_update = $this->order_model->update_order($order_id, [
            // SET
            // giá trị đơn hàng
            //'menu_order' => $total_price,
            'order_money' => $order_money,
        ], [
                // WHERE
                'post_status' => OrderType::PENDING,
                'post_author' => $this->current_user_id,
            ]);
        //var_dump( $result_update );

        //
        if ($result_update) {
            $this->result_json_type([
                'order_id' => $order_id,
                'total_price' => $order_money,
            ]);
        }
        $this->result_json_type([
            'code' => __LINE__,
            'error' => 'LỖI cập nhật đơn hàng'
        ]);
    }
}