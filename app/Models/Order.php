<?php

namespace App\Models;

// Libraries
use App\Libraries\OrderType;

//
class Order extends Post
{
    public $table = 'orders';
    public $metaTable = 'ordermeta';
    public $post_type = OrderType::ORDER;

    //
    public function __construct()
    {
        parent::__construct();

        //
        $this->user_model = new \App\Models\User();
    }

    public function insert_order($data_insert)
    {
        //$data_insert[ 'guid' ] = ''; // danh sách IDs sản phẩm
        //$data_insert[ 'post_excerpt' ] = ''; // danh sách sản phẩm dạng json
        //$data_insert[ 'order_money' ] = ''; // tổng giá trị giỏ hàng
        //$data_insert[ 'comment_count' ] = ''; // hạn sử dụng/ thời gian bảo hành
        //$data_insert[ 'pinged' ] = ''; // thông tin phản hồi từ các bên thanh toán trung gian
        if (!isset($data_insert['post_status'])) {
            $data_insert['post_status'] = OrderType::PENDING;
        }
        if (!isset($data_insert['post_type'])) {
            $data_insert['post_type'] = $this->post_type;
        }

        // tự động tạo mã đơn hàng nếu chưa có
        if (!isset($data_insert['post_name']) || $data_insert['post_name'] == '') {
            if (isset($data_insert['post_author']) && $data_insert['post_author'] > 0) {
                $data_insert['post_name'] = $data_insert['post_author'] . 'EB' . date('ymdHis');
            }
        }

        //
        if (isset($data_insert['post_excerpt']) && is_array($data_insert['post_excerpt'])) {
            $data_insert['post_excerpt'] = json_encode($data_insert['post_excerpt']);
        }
        //print_r($data_insert);
        //die(__CLASS__ . ':' . __LINE__);

        //
        $result = parent::insert_post($data_insert, [], false);

        //
        if (isset($data_insert['post_author']) && $data_insert['post_author'] > 0) {
            $this->cache_user_fund($data_insert['post_author']);
        }

        //
        return $result;
    }

    public function get_order($where = [], $filter = [], $select_col = '*')
    {
        $where['post_type'] = $this->post_type;

        //
        return $this->base_model->select($select_col, $this->table, $where, $filter);
    }

    // trả về đơn hàng đang chờ thanh toán
    public function get_pending_order($where = [], $add_filter = [], $select_col = '*')
    {
        $where['post_status'] = OrderType::PENDING;

        //
        //print_r($where);

        //
        $filter = [
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
        ];
        foreach ($add_filter as $k => $v) {
            $filter[$k] = $v;
        }
        //print_r($filter);

        //
        return $this->get_order($where, $filter, $select_col);
    }

    // tạo 1 đơn hàng mới
    public function create_pending_order($data_insert)
    {
        //print_r($data_insert);
        //die(__CLASS__ . ':' . __LINE__);

        //
        $result_id = $this->insert_order($data_insert);
        //print_r( $result_id );
        if (is_array($result_id) && isset($result_id['error'])) {
            die($result_id['error'] . ' --- ERROR! ' . __CLASS__ . ':' . __LINE__);
        }

        //
        return $result_id;
    }

    public function update_order($order_id, $data, $where = [])
    {
        $where['post_type'] = $this->post_type;

        //
        //return $this->base_model->update_multiple( $this->table, $data, $where, $filter );
        $result = parent::update_post($order_id, $data, $where);

        //
        $this->cache_user_fund($order_id);

        //
        return $result;
    }

    public function update_user_fund($order_id, $post_author = 0, $where = [])
    {
        if ($post_author === 0) {
            // nếu trong where có thì lấy luôn trong where
            if (isset($where['post_author']) && $where['post_author'] > 0) {
                $post_author = $where['post_author'];
            }
            // không thì lấy theo ID đơn hàng
            else {
                $user_data = $this->get_order(
                    array(
                        // các kiểu điều kiện where
                        'ID' => $order_id,
                    ),
                    array(
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
                    'post_author'
                );
                //print_r($user_data);

                //
                if (!empty($user_data)) {
                    $post_author = $user_data['post_author'];
                }
            }
        }

        //
        if ($post_author > 0) {
            return $this->base_model->update_multiple('users', [
                // SET
                'money' => $this->get_user_fund($post_author),
            ], [
                // WHERE
                'ID' => $post_author,
            ], [
                'debug_backtrace' => debug_backtrace()[1]['function'],
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                // mặc định sẽ remove các field không có trong bảng, nếu muốn bỏ qua chức năng này thì kích hoạt no_remove_field
                //'no_remove_field' => 1

            ]);
        }

        //
        return false;
    }

    // trả về số dư tài khoản của 1 user
    public function get_user_fund($user_id, $using_cache = false)
    {
        // có sử dụng cache thì lấy trong cache trước
        if ($using_cache === true) {
            $data = $this->user_model->the_cache($user_id, __FUNCTION__);
            if ($data !== NULL) {
                return $data * 1;
            }
        }

        //
        $data = $this->get_order(
            array(
                // các kiểu điều kiện where
                'post_author' => $user_id,
                'post_status' => OrderType::PRIVATELY,
            ),
            array(
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
            'SUM(order_money) AS money, SUM(order_bonus) AS bonus'
        );
        //print_r($data);

        //
        $data['money'] *= 1;
        $data['bonus'] *= 1;
        $user_fund = $data['money'] + $data['bonus'];

        //
        if ($user_fund !== 0) {
            $this->user_model->the_cache($user_id, __FUNCTION__, $user_fund);
        } else {
            $this->user_model->the_cache($user_id, __FUNCTION__, '0');
        }
        //$this->base_model->alert($user_fund, 'error');

        //
        return $user_fund;
    }

    // cập nhật lại số dư user trong cache
    public function cache_user_fund($order_id, $post_author = 0)
    {
        // nếu có ID user -> dùng luôn ID này
        if ($post_author > 0) {
            return $this->get_user_fund($post_author);
        }

        // lấy ID user dựa theo ID đơn hàng
        $order_data = $this->get_order(
            array(
                // các kiểu điều kiện where
                'ID' => $order_id,
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
            'post_author'
        );
        //print_r($order_data);

        // -> lấy số dư của user -> cache liên quan đến số dư cũng sẽ được cập nhật luôn
        if (!empty($order_data)) {
            return $this->get_user_fund($order_data['post_author']);
        }

        //
        return false;
    }
}
