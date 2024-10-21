<?php

namespace App\Models;

// Libraries
use App\Libraries\OrderType;
use App\Libraries\UsersType;

//
class Order extends Post
{
    public $table = 'orders';
    public $metaTable = 'ordermeta';
    public $post_type = OrderType::ORDER;
    public $user_model = null;
    public $request = null;

    //
    public function __construct()
    {
        parent::__construct();

        //
        $this->request = \Config\Services::request();
        $this->user_model = new \App\Models\User();
    }

    public function insert_order($data)
    {
        //$data[ 'guid' ] = ''; // danh sách IDs sản phẩm
        //$data[ 'post_excerpt' ] = ''; // danh sách sản phẩm dạng json
        //$data[ 'order_money' ] = ''; // tổng giá trị giỏ hàng
        //$data[ 'comment_count' ] = ''; // hạn sử dụng/ thời gian bảo hành
        //$data[ 'pinged' ] = ''; // thông tin phản hồi từ các bên thanh toán trung gian
        if (!isset($data['post_status'])) {
            $data['post_status'] = OrderType::PENDING;
        }
        if (!isset($data['post_type'])) {
            $data['post_type'] = $this->post_type;
        }
        if (!isset($data['order_ip'])) {
            $data['order_ip'] = $this->base_model->getIPAddress();
        }
        if (!isset($data['order_agent'])) {
            $data['order_agent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
        }
        $data['created_source'] = implode(' - ', [
            // isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null,
            isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null,
            isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null,
            // isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null,
        ]);

        // nếu chưa có thông tin người gửi
        if (!isset($data['post_author']) || $data['post_author'] < 1) {
            // nếu có email
            if (isset($data['email']) && !empty($data['email']) && strpos($data['email'], '@') !== false) {
                $user_id = $this->user_model->check_user_exist($data['email']);
                // var_dump($user_id);

                // nếu email chưa được sử dụng
                if ($user_id === false) {
                    $user_data = [
                        'user_email' => $data['email'],
                        'user_phone' => isset($data['phone']) ? $data['phone'] : '',
                        'member_type' => UsersType::CUSTOMER,
                    ];
                    // print_r($user_data);
                    $user_id = $this->user_model->insert_member($user_data);
                    // var_dump($user_id);
                }
            }
            // nếu có phone
            else if (isset($data['phone']) && !empty($data['phone'])) {
                $user_id = $this->user_model->check_user_exist($data['phone'], 'user_phone');
                // var_dump($user_id);

                // nếu phone chưa được sử dụng
                if ($user_id === false) {
                    $phone = $this->base_model->_eb_number_only($data['phone']);
                    // echo $phone . '<br>' . PHP_EOL;
                    $user_id = $this->user_model->check_user_exist($phone, 'number_phone');
                    // var_dump($user_id);

                    // 
                    if ($user_id === false && $phone > 0 && strlen($phone) > 5) {
                        $user_data = [
                            'user_email' => $phone . '@' . $_SERVER['HTTP_HOST'],
                            'user_phone' =>  $data['phone'],
                            'member_type' => UsersType::CUSTOMER,
                        ];
                        // print_r($user_data);
                        $user_id = $this->user_model->insert_member($user_data);
                        // var_dump($user_id);
                    }
                }
                // echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
            } else {
                $this->base_model->alert('Billing Email address is a required field.', 'error');
            }

            //
            if ($user_id === false || $user_id < 1) {
                $this->base_model->alert('Customer ID not found!', 'error');

                // 
                $user_id = 0;
            }

            //
            $data['post_author'] = $user_id;
        }

        // tự động tạo họ tên cho đơn hàng nếu chưa có
        if (!isset($data['full_name']) || empty($data['full_name'])) {
            if (isset($data['first_name']) && !empty($data['first_name'])) {
                $data['full_name'] = $data['first_name'];

                // thêm last name nếu có
                if (isset($data['last_name']) && !empty($data['last_name'])) {
                    $data['full_name'] .= ' ' . $data['last_name'];
                }
            } else if (isset($data['last_name']) && !empty($data['last_name'])) {
                $data['full_name'] = $data['last_name'];
            }
        }

        // tự động tạo tiêu đề cho đơn hàng nếu chưa có
        if (!isset($data['post_title']) || empty($data['post_title'])) {
            $data['post_title'] = '';
        }
        if ($data['post_title'] == '') {
            if (isset($data['post_author']) && $data['post_author'] > 0) {
                $data['post_title'] = '#' . $data['post_author'];
            }

            // đặt theo họ tên
            if (isset($data['full_name']) && !empty($data['full_name'])) {
                $data['post_title'] .= ' ' . $data['full_name'];
            }
            // đặt theo email
            else if (isset($data['email']) && !empty($data['email']) && strpos($data['email'], '@') !== false) {
                $data['post_title'] .= ' ' . $data['email'];
            }
            // đặt theo phone
            else if (isset($data['phone']) && !empty($data['phone'])) {
                $data['post_title'] .= ' phone ' . $data['phone'];
            }
        }

        // tự động tạo mã đơn hàng nếu chưa có
        if (!isset($data['post_name']) || empty($data['post_name'])) {
            if (isset($data['post_author']) && $data['post_author'] > 0) {
                $data['post_name'] = $data['post_author'] . 'EB' . date('ymdHis');
            }
        }

        // tạo mã đơn hàng nếu chưa có
        if (!isset($data['post_password']) || empty($data['post_password'])) {
            $data['post_password'] = md5(time() . $this->base_model->MY_sessid());
        }

        //
        if (isset($data['post_content']) && !empty($data['post_content']) && is_array($data['post_content'])) {
            // print_r($data['post_content']);
            $data['post_content'] = json_encode($data['post_content']);
        }

        //
        if (isset($data['post_excerpt']) && !empty($data['post_excerpt']) && is_array($data['post_excerpt'])) {
            // print_r($data['post_excerpt']);
            $data['post_excerpt'] = json_encode($data['post_excerpt']);
        }
        // print_r($data);
        // die(__CLASS__ . ':' . __LINE__);

        //
        $result = parent::insert_post($data, [], false);

        //
        if (isset($data['post_author']) && $data['post_author'] > 0) {
            $this->cache_user_fund($data['post_author']);
        }

        //
        return $result;
    }

    public function get_order($where = [], $filter = [], $select_col = 't1.*')
    {
        $where['t1.post_type'] = $this->post_type;

        //
        return $this->base_model->select($select_col, $this->table . ' AS t1', $where, $filter);
    }

    // trả về đơn hàng đang chờ thanh toán
    public function get_pending_order($where = [], $add_filter = [], $select_col = 't1.*')
    {
        $where['t1.post_status'] = OrderType::PENDING;

        //
        //print_r($where);

        //
        $filter = [
            'order_by' => array(
                't1.ID' => 'DESC'
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
    public function create_pending_order($data)
    {
        //print_r($data);
        //die(__CLASS__ . ':' . __LINE__);

        //
        $result_id = $this->insert_order($data);
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
        if ($post_author < 1) {
            // nếu trong where có thì lấy luôn trong where
            if (isset($where['post_author']) && $where['post_author'] > 0) {
                $post_author = $where['post_author'];
            }
            // không thì lấy theo ID đơn hàng
            else {
                $user_data = $this->get_order(
                    array(
                        // các kiểu điều kiện where
                        't1.ID' => $order_id,
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
                    't1.post_author'
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
            if ($data !== null) {
                return $data * 1;
            }
        }

        //
        $data = $this->get_order(
            array(
                // các kiểu điều kiện where
                't1.post_author' => $user_id,
                't1.post_status' => OrderType::PRIVATELY,
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
            'SUM(t1.order_money) AS money, SUM(t1.order_bonus) AS bonus'
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
                't1.ID' => $order_id,
            ),
            array(
                'order_by' => array(
                    't1.ID' => 'DESC'
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
            't1.post_author'
        );
        //print_r($order_data);

        // -> lấy số dư của user -> cache liên quan đến số dư cũng sẽ được cập nhật luôn
        if (!empty($order_data)) {
            return $this->get_user_fund($order_data['post_author']);
        }

        //
        return false;
    }

    /**
     * tạo thông số cho link actions/order_received
     **/
    public function orderReceiveToken($id, $pass)
    {
        return base_url('actions/order_received') . '?' . implode('&', [
            // 'id=' . $id,
            // 'token=' . $this->base_model->mdhash($id),
            'token_id=' . base64_encode($id . '___' . $this->base_model->mdhash($id)),
            'key=' . $pass,
        ]);
    }
}
