<?php

namespace App\Controllers;

//
// use App\Libraries\PostType;
use App\Libraries\DeletedStatus;
use App\Libraries\TaxonomyType;
use App\Libraries\OrderType;

//
class Actions extends Layout
{
    // chức năng này không cần nạp header
    // public $preload_header = false;

    // thông điệp sau khi đặt hàng thành công
    protected $thank_you = 'Thank you. Your order has been received.';

    public function __construct()
    {
        parent::__construct();

        // 
        $this->order_model = new \App\Models\Order();
    }

    /**
     * Giỏ hàng
     **/
    public function cart()
    {
        //
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            return $this->submit_cart();
        }

        //
        $by_get_id = '';

        // nếu có ID truyền vào -> lấy theo ID đó
        $product_id = $this->MY_get('id', 0);
        if ($product_id > 0) {
            $by_get_id = 'quick-cart-id';

            //
            $data = $this->base_model->select(
                '*',
                'posts',
                array(
                    // các kiểu điều kiện where
                    'ID' => $product_id,
                    'post_type' => OrderType::PROD,
                    'post_status' => OrderType::PUBLICITY
                ),
                array(
                    // hiển thị mã SQL để check
                    // 'show_query' => 1,
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
            // print_r($data);
            if (!empty($data)) {
                $data = [$this->post_model->the_meta_post($data)];
                // print_r($data);
            }
        } else {
            $data = [];
        }

        //
        $cart_url = 'actions/' . __FUNCTION__;
        $cart_title = $this->lang_model->get_the_text('cart_view_h1', 'Giỏ hàng');

        //
        $this->create_breadcrumb($cart_title, $cart_url);

        //
        $this->teamplate['breadcrumb'] = view(
            'breadcrumb_view',
            array(
                'breadcrumb' => $this->breadcrumb
            )
        );

        // TEST
        $coupon_code = '';
        $coupon_amount = 0;

        //
        $this->teamplate['main'] = view(
            'cart_view',
            array(
                //'option_model' => $this->option_model,
                'seo' => $this->base_model->default_seo($cart_title, $cart_url, [
                    'canonical' => base_url($cart_url),
                ]),
                // 'breadcrumb' => '',
                'cart_title' => $cart_title,
                'data' => $data,
                'by_get_id' => $by_get_id,
                'product_id' => $product_id,
                'coupon_code' => $coupon_code,
                'coupon_amount' => $coupon_amount,
                // 'products_id' => $this->MY_post('ids'),
            )
        );
        //print_r( $this->teamplate );
        return view('layout_view', $this->teamplate);
    }

    /**
     * Trả về dữ liệu giỏ hàng bằng json
     **/
    public function ajax_cart()
    {
        // nếu là phương thức POST -> truyền qua ajax
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => 'Bad request!',
            ]);
        }

        //
        $products_id = $this->MY_post('ids');
        if (!empty($products_id)) {
            $ids = [];
            $products_id = explode(',', $products_id);
            foreach ($products_id as $v) {
                if (!empty($v)) {
                    $ids[] = $v;
                }
            }

            //
            if (!empty($ids)) {
                $data = $this->base_model->select(
                    '*',
                    'posts',
                    array(
                        // các kiểu điều kiện where
                        'post_type' => OrderType::PROD,
                        'post_status' => OrderType::PUBLICITY
                    ),
                    array(
                        'where_in' => array(
                            'ID' => $ids
                        ),
                        // hiển thị mã SQL để check
                        // 'show_query' => 1,
                        // trả về câu query để sử dụng cho mục đích khác
                        //'get_query' => 1,
                        // trả về COUNT(column_name) AS column_name
                        //'selectCount' => 'ID',
                        // trả về tổng số bản ghi -> tương tự mysql num row
                        //'getNumRows' => 1,
                        //'offset' => 0,
                        // 'limit' => -1
                    )
                );
                // print_r($data);
                // die(__CLASS__ . ':' . __LINE__);

                //
                if (!empty($data)) {
                    $data = $this->post_model->list_meta_post($data);
                    // print_r($data);
                    // die(__CLASS__ . ':' . __LINE__);

                    // trả về dữ liệu theo json
                    $this->result_json_type([
                        'ok' => __LINE__,
                        'ids' => implode(',', $ids),
                        'table' => view('default/cart_table_view', [
                            'data' => $data,
                        ]),
                    ]);
                } else {
                    $this->result_json_type([
                        'code' => __LINE__,
                        'error' => 'EMPTY data',
                    ]);
                }
            } else {
                $this->result_json_type([
                    'code' => __LINE__,
                    'error' => 'EMPTY ids',
                ]);
            }
        } else {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => 'EMPTY parameter',
            ]);
        }
    }

    /**
     * Tạo đơn hàng
     **/
    protected function submit_cart()
    {
        // kiểm tra spam bot
        // $this->base_model->antiRequiredSpam();

        // 
        // print_r($_POST);
        // die(__CLASS__ . ':' . __LINE__);

        //
        $data = $this->MY_post('data');
        // print_r($data);

        // nếu có mã giảm giá
        $coupon_code = $this->MY_post('coupon_code');
        $coupon_code = trim($coupon_code);
        $coupon_amount = 0;
        $discount_type = '';
        if (!empty($coupon_code)) {
            $data['coupon'] = $coupon_code;

            // kiểm tra mã ko hợp lệ thì báo với người dùng
            $check_coupon = $this->checkCouponCode($coupon_code);
            // print_r($check_coupon);

            // 
            if (!empty($check_coupon)) {
                // print_r($data);

                // 
                $coupon_amount = $check_coupon['coupon_amount'];
                $discount_type = $check_coupon['discount_type'];
            }
        }

        // lấy danh sách sản phẩm
        $cart_id = $this->MY_post('cart_id', []);
        // print_r($cart_id);
        $cart_quantity = $this->MY_post('cart_quantity', []);
        // print_r($cart_quantity);

        // chạy vòng lặp lấy các sp có số lượng > 0
        $ids = [];
        $prod_ids = [];
        foreach ($cart_quantity as $k => $v) {
            if (empty($v) || !is_numeric($v) || $v < 1) {
                continue;
            }

            //
            $ids[] = $cart_id[$k];
            $prod_ids[$cart_id[$k]] = $v;
        }
        // print_r($ids);
        // print_r($prod_ids);

        //
        if (empty($ids)) {
            $this->base_model->alert('No valid products were found in your cart!', 'warning');
        }
        $ids = array_unique($ids);

        // lấy các sản phẩm theo ids tìm thấy được
        $products = $this->post_model->select_post(0, [
            'post_type' => OrderType::PROD,
            'post_status' => OrderType::PUBLICITY,
        ], [
            'where_in' => array(
                'ID' => $ids
            ),
            'order_by' => array(
                'menu_order' => 'DESC',
                'ID' => 'ASC'
            ),
            // 'show_query' => 1,
            'limit' => 99,
        ]);
        // print_r($products);
        if (empty($products)) {
            $this->base_model->alert('No valid products were found in your cart!', 'error');
        }

        // 
        $products = $this->post_model->list_meta_post($products);
        // print_r($products);
        // die(__CLASS__ . ':' . __LINE__);

        // tổng giá trị đơn hàng
        $order_money = 0;
        // tổng số tiền giảm giá
        $total_sale = 0;
        // tổng số sp trong đơn hàng
        $total_quantity = 0;
        // $total_products = 0;
        // thông tin đơn hàng lưu dạng meta data
        $post_excerpt = [];
        $post_parent = 0;

        // 
        foreach ($products as $v) {
            $post_meta = $v['post_meta'];

            // 
            $quantity = $prod_ids[$v['ID']] * 1;
            $total_quantity += $quantity;

            // 
            if (isset($post_meta['_sale_price']) && $post_meta['_sale_price'] > 0) {
                $price_type = '_sale_price';
                if (isset($post_meta['_regular_price']) && $post_meta['_regular_price'] > 0) {
                    $total_sale += ($post_meta['_regular_price'] - $post_meta['_sale_price']) * $quantity;
                }
            } else {
                $price_type = '_regular_price';
            }

            // 
            $total = $post_meta[$price_type] * $quantity;

            // 
            $order_money += $total;

            //
            if ($post_parent < 1) {
                $post_parent = $v['ID'];
            }

            // 
            $post_excerpt[] = [
                'ID' => $v['ID'] * 1,
                'post_author' => $v['post_author'] * 1,
                'post_title' => $v['post_title'],
                'post_name' => $v['post_name'],
                // meta
                'image' => $post_meta['image'],
                // '_price' => $total,
                '_price' => $post_meta[$price_type],
                '_quantity' => $quantity,
                'price_type' => $price_type,
            ];

            // 
            // $total_products++;
        }
        // print_r($post_excerpt);
        // die(__CLASS__ . ':' . __LINE__);

        //
        // $data['post_title'] = 'New order ' . date('Y-m-d H:i:s');
        $data['post_excerpt'] = $post_excerpt;
        $data['order_money'] = $order_money;
        $data['post_parent'] = $post_parent;
        $data['post_password'] = md5(time() . $this->base_model->MY_sessid());
        // $data['post_password'] = substr($data['post_password'], 0, 16);

        // phí vận chuyển (nếu có)
        if ($this->getconfig->shipping_fee != '') {
            $data['shipping_fee'] = $this->getconfig->shipping_fee;
        }

        // đặt cọc trước (nếu có)
        if ($this->getconfig->deposit_money != '') {
            $data['deposit_money'] = $this->getconfig->deposit_money;
        }

        // tính số tiền giảm giá
        $order_discount = 0;
        if ($coupon_amount > 0) {
            // nếu là giảm theo %
            if ($discount_type == TaxonomyType::DISCOUNT_PERCENT) {
                $order_discount = $order_money / 100 * $coupon_amount;
            }
            // giảm theo tổng số lượng sản phẩm trong giỏ hàng
            else if ($discount_type == TaxonomyType::DISCOUNT_FIXED) {
                $order_discount = $total_quantity * $coupon_amount;
            }
            // giảm giá trị cố định
            else {
                $order_discount = $coupon_amount;
            }

            // 
            $data['order_discount'] = $order_discount;
            $data['discount_type'] = $discount_type;
        }
        // echo $order_money . '<br>' . PHP_EOL;
        // echo $order_discount . '<br>' . PHP_EOL;
        // print_r($data);
        // die(__CLASS__ . ':' . __LINE__);

        //
        $result_id = $this->order_model->insert_order($data);
        // var_dump($result_id);
        // print_r($result_id);

        //
        if ($result_id !== false && is_numeric($result_id) && $result_id > 0) {
            // Chuyển tới trang đặt hàng thành công và xóa session giỏ hàng (nếu có)
            echo '<script>top.remove_session_cart("' . base_url('actions/order_received') . '?' . implode('&', [
                // 'id=' . $result_id,
                // 'token=' . $this->base_model->mdhash($result_id),
                'token_id=' . base64_encode($result_id . '___' . $this->base_model->mdhash($result_id)),
                'key=' . $data['post_password'],
            ]) . '");</script>';

            // Chuyển tới trang đặt hàng thành công
            $this->base_model->alert($this->lang_model->get_the_text('order_received_view_h1', $this->thank_you));
        }

        // không thành công thì sẽ báo lỗi
        $this->base_model->alert('ERROR! Your order not create.', 'error');
    }

    /**
     * Gửi đơn hàng vào hệ thống
     **/
    // public function checkout($custom_data = [])
    // {
    // }

    /**
     * Thêm coupon
     **/
    public function add_coupon()
    {
        // nếu là phương thức POST -> truyền qua ajax
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => 'Bad request!',
            ]);
        }

        //
        $coupon_code = $this->MY_post('coupon_custom_code');
        if (empty($coupon_code)) {
            $this->base_model->alert('Please enter a coupon code.', 'error');
        }
        $coupon_code = trim($coupon_code);

        //
        $data = $this->checkCouponCode($coupon_code);

        // Thiết lập lại thông số cho coupon
        echo '<script>top.add_coupon_code("' . $data['coupon_amount'] . '", "' . $coupon_code . '", "' . $data['discount_type'] . '");</script>';

        //
        $this->base_model->alert('Coupon code applied successfully.');
    }

    /**
     * Kiểm tra 1 mã giảm giá còn khả dụng hay ko
     **/
    protected function checkCouponCode($code)
    {
        $data = $this->getCouponCode($code);
        if (empty($data)) {
            $this->base_model->alert('Coupon `' . $code . '` does not exist!', 'error');
        }

        //
        if (empty($data['coupon_amount'])) {
            $this->base_model->alert('Coupon amount not found!', 'warning');
        }
        $data['coupon_amount'] *= 1;

        // coupon amount không có giá trị -> báo lỗi
        if ($data['coupon_amount'] < 1) {
            $this->base_model->alert('Coupon amount has not been setup yet!', 'warning');
        }

        // chuyển sang giảm giá theo %
        if ($data['discount_type'] == 'percent') {
            $data['coupon_amount'] .= '%';
        }
        // print_r($data);

        // xem hạn sử dụng
        if (strlen(substr($data['expiry_date'], 0, 10)) == 10 && strtotime($data['expiry_date']) < time()) {
            // thông báo khi coupon hết hạn
            $this->base_model->alert('This coupon has expired!', 'warning');
        }

        //
        return $data;
    }

    /**
     * Trả về thông tin chi tiết của 1 mã giảm giá nếu có
     **/
    protected function getCouponCode($code)
    {
        //
        if (empty($code)) {
            return false;
        }

        // lấy data theo key
        $data = $this->base_model->select(
            '*',
            'termmeta',
            array(
                // các kiểu điều kiện where
                'UPPER(meta_value)' => strtoupper($code),
                'meta_key' => 'coupon_code',
            ),
            array(
                'group_by' => array(
                    'term_id',
                ),
                // hiển thị mã SQL để check
                // 'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                // trả về COUNT(column_name) AS column_name
                //'selectCount' => 'ID',
                // trả về tổng số bản ghi -> tương tự mysql num row
                //'getNumRows' => 1,
                //'offset' => 0,
                'limit' => 10
            )
        );
        // print_r($data);

        //
        if (empty($data)) {
            return false;
        }

        // list ra danh sách term_id
        $ids = [];
        foreach ($data as $v) {
            $ids[] = $v['term_id'];
        }
        // print_r($ids);

        // lấy thông tin term -> vì có phân biệt theo ngôn ngữ nữa
        $data = $this->base_model->select(
            '*',
            'terms',
            array(
                // các kiểu điều kiện where
                'lang_key' => $this->lang_key,
                'is_deleted' => DeletedStatus::FOR_DEFAULT,
            ),
            array(
                'where_in' => array(
                    'term_id' => $ids
                ),
                // hiển thị mã SQL để check
                // 'show_query' => 1,
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

        //
        if (empty($data)) {
            return false;
        }
        // print_r($data);
        $data = $this->term_model->terms_meta_post([$data]);
        // print_r($data);

        //
        $data = $data[0];
        // print_r($data);

        // 
        $term_meta = $data['term_meta'];
        // print_r($term_meta);

        // bổ sung các meta còn thiếu
        $meta_default = $this->term_model->taxonomy_meta_default(TaxonomyType::SHOP_COUPON);
        // print_r($meta_default);

        //
        foreach ($meta_default as $k => $v) {
            if (!isset($term_meta[$k])) {
                $term_meta[$k] = '';
            }
        }
        // print_r($term_meta);

        //
        return $term_meta;
    }

    /**
     * Gửi đơn hàng thành công và xử lý các dữ liệu tiếp theo tại đây
     * Ví dụ xử lý khâu thanh toán online...
     **/
    public function order_received()
    {
        //
        $cart_url = 'actions/' . __FUNCTION__;
        $cart_title = $this->lang_model->get_the_text('order_received_view_h1', $this->thank_you);

        //
        $this->create_breadcrumb($cart_title, $cart_url);

        //
        $this->teamplate['breadcrumb'] = view(
            'breadcrumb_view',
            array(
                'breadcrumb' => $this->breadcrumb
            )
        );

        //
        $data = NULL;

        // 2 thông số này lấy trực tiếp từ URL hoặc có thể decode ra để lấy
        $id = $this->MY_get('id', 0);
        $token = $this->MY_get('token');
        // 
        $key = $this->MY_get('key');
        $token_id = $this->MY_get('token_id');
        if (!empty($token_id)) {
            $token_id = base64_decode($token_id);
            // print_r($token_id);
            $token_id = explode('___', $token_id);
            // print_r($token_id);

            //
            if (is_numeric($token_id[0] * 1)) {
                $id = $token_id[0];
            }

            // 
            if (isset($token_id[1])) {
                $token = $token_id[1];
            }
        }
        $id *= 1;

        //
        if ($id > 0 && !empty($token) && !empty($key) && $this->base_model->mdhash($id) == $token) {
            // lấy đơn hàng đang chờ
            $data = $this->order_model->get_order(
                array(
                    // các kiểu điều kiện where
                    't1.ID' => $id,
                    't1.post_password' => $key,
                    // 't1.post_status' => OrderType::PENDING,
                ),
                array(
                    /*
                    'where_in' => [
                        't1.post_status' => [
                            OrderType::PENDING,
                        ],
                    ],
                    */
                    'where_not_in' => [
                        't1.post_status' => [
                            OrderType::DELETED,
                        ],
                    ],
                    'order_by' => array(
                        't1.ID' => 'DESC'
                    ),
                    // hiển thị mã SQL để check
                    // 'show_query' => 1,
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
        }

        // 
        $this->teamplate['main'] = view(
            'order_received_view',
            array(
                //'option_model' => $this->option_model,
                'seo' => $this->base_model->default_seo($cart_title, $cart_url, [
                    'canonical' => base_url($cart_url),
                ]),
                // 'breadcrumb' => '',
                'cart_title' => $cart_title,
                'key' => $key,
                'data' => $data,
                'OrderType_COMPLETED' => [
                    OrderType::PRIVATELY,
                    OrderType::INHERIT,
                ],
                'OrderType_PRIVATELY' => OrderType::PRIVATELY,
                'OrderType_arrStatus' => OrderType::arrStatus(),
            )
        );
        //print_r( $this->teamplate );
        return view('layout_view', $this->teamplate);
    }

    /**
     * xác nhận quá trình thanh toán qua PayPal thành công
     **/
    public function capture_paypal_order()
    {
        // chỉ chấp nhật POST
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => 'Bad request!',
            ]);
        }

        // 
        $order_id = $this->MY_post('order_id');
        $reference_id = $this->MY_post('reference_id');
        if (empty($order_id) || empty($reference_id)) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => 'Require data is EMPTY!',
            ]);
        }

        // 
        $result_update = $this->order_model->update_order($order_id, [
            'post_status' => OrderType::PRIVATELY,
            'approve_data' => $this->MY_post('approve_data'),
            'order_capture' => $this->MY_post('order_capture'),
            'deposit_value' => $this->MY_post('deposit_value', null),
        ], [
            'post_password' => $reference_id,
            'post_status !=' => OrderType::PRIVATELY,
        ]);

        // 
        $this->result_json_type([
            'code' => __LINE__,
            // 'data' => $_POST,
            'result_update' => $result_update === true ? 1 : 0,
        ]);
    }
}
