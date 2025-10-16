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
    public $order_model = null;
    public $mail_queue_model = null;

    public function __construct()
    {
        parent::__construct();

        // 
        $this->order_model = new \App\Models\Order();
        $this->mail_queue_model = new \App\Models\MailQueue();
        // print_r($this->getconfig);
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
        // giỏ hàng sẽ được load qua ajax
        if (1 > 2 && $product_id > 0) {
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
                'other_data' => null,
                'by_get_id' => $by_get_id,
                'product_id' => $product_id,
                'shop_id' => $this->MY_get('shop_id', 0),
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
                        'order_by' => array(
                            'post_author' => 'ASC',
                            'menu_order' => 'DESC',
                            'ID' => 'DESC',
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
                    $result_data = [];
                    $other_data = [];
                    $order_received = $this->MY_post('order_received');
                    $shop_cart_id = 0;

                    // nếu ở trang xác nhận đơn hàng thì chỉ hiển thị theo mẫu của other_data
                    if (!empty($order_received)) {
                        $other_data = $this->post_model->list_meta_post($data);
                    }
                    // nếu là sàn thương mại điện tử -> chỉ hiển thị thông tin đơn hàng của từng shop
                    else if (THIS_IS_E_COMMERCE_SITE == 'yes' && count($data) > 1) {
                        $shop_cart_id = $this->MY_post('shop_cart_id');

                        // xác định ID sản phẩm đang được thêm vào
                        $product_cart_id = $this->MY_post('product_cart_id');
                        // nếu có ID sản phẩm
                        if (!empty($product_cart_id)) {
                            foreach ($data as $v) {
                                // xác định ID của shop
                                if ($product_cart_id == $v['ID']) {
                                    $shop_cart_id = $v['post_author'];
                                    break;
                                }
                            }
                        }

                        // 
                        foreach ($data as $v) {
                            // nếu chưa có tác giả nào được thiết lập thì gán luôn cái đầu tiên
                            if (empty($shop_cart_id) || $shop_cart_id == $v['post_author']) {
                                // thiết lập các sản phẩm cùng tác giả
                                $shop_cart_id = $v['post_author'];
                                // gán dữ liệu chính
                                $result_data[] = $v;
                            } else {
                                // gán dữ liệu còn lại
                                $other_data[] = $v;
                            }
                        }
                        $result_data = $this->post_model->list_meta_post($result_data);
                        $other_data = $this->post_model->list_meta_post($other_data);
                    } else {
                        $result_data = $this->post_model->list_meta_post($data);
                        // nếu chỉ có 1 sản phẩm -> lấy ID của người đăng sản phẩm
                        $shop_cart_id = $data[0]['post_author'];
                    }
                    // print_r($result_data);
                    // die(__CLASS__ . ':' . __LINE__);

                    // trả về dữ liệu theo json
                    $this->result_json_type([
                        'ok' => __LINE__,
                        // 'count' => count($data),
                        'ids' => implode(',', $ids),
                        'shop_cart_id' => $shop_cart_id,
                        'table' => view('default/cart_table_view', [
                            'data' => $result_data,
                            'other_data' => $other_data,
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
        // $coupon_amount = 0;
        $coupon_number = 0;
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
                // $coupon_amount = $check_coupon['coupon_amount'];
                $coupon_number = $check_coupon['coupon_number'];
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
                'ID' => $ids,
            ),
            'order_by' => array(
                'menu_order' => 'DESC',
                'ID' => 'ASC',
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
        $product_ids = [];
        $post_parent = 0;
        // ID của người đăng sản phẩm -> dùng để gửi mail báo cho họ nếu có yêu cầu
        $mail_author_id = 0;

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
            if ($mail_author_id < 1) {
                $mail_author_id = $v['post_author'] * 1;
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
            $product_ids[] = $v['ID'];

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
        $data['product_ids'] = implode(',', $product_ids);

        // nếu có thành phố -> thử tìm phí vận chuyển theo thành phố
        if (isset($data['city'])) {
            $data['shipping_fee'] = $this->shippingFee($data['city']);
        }

        // nếu có tiểu bang -> thử tìm theo tiểu bang
        if (!isset($data['shipping_fee']) || empty($data['shipping_fee'])) {
            if (isset($data['state'])) {
                $data['shipping_fee'] = $this->shippingFee($data['state']);
            }
        }

        // phí vận chuyển (nếu có)
        if (!isset($data['shipping_fee']) || empty($data['shipping_fee'])) {
            if ($this->getconfig->shippings_fee != '') {
                $data['shipping_fee'] = $this->getconfig->shippings_fee;
            }
        }

        // 
        if (isset($data['shipping_fee']) && strpos($data['shipping_fee'], '[qty]') !== false) {
            $data['shipping_fee'] = trim(explode('*', $data['shipping_fee'])[0]) * $total_quantity;
        }

        // đặt cọc trước (nếu có)
        if ($this->getconfig->deposits_money != '') {
            $data['deposit_money'] = $this->getconfig->deposits_money;
        }

        // 
        // print_r($data);
        // die(__CLASS__ . ':' . __LINE__);

        // tính số tiền giảm giá
        $order_discount = 0;
        if ($coupon_number > 0) {
            // nếu là giảm theo %
            if ($discount_type == TaxonomyType::DISCOUNT_PERCENT) {
                // echo $order_money . '<br>' . "\n";
                // echo $coupon_amount . '<br>' . "\n";
                // echo $coupon_number . '<br>' . "\n";
                // die(__CLASS__ . ':' . __LINE__);
                $order_discount = $order_money / 100 * $coupon_number;
            }
            // giảm theo tổng số lượng sản phẩm trong giỏ hàng
            else if ($discount_type == TaxonomyType::DISCOUNT_FIXED) {
                $order_discount = $total_quantity * $coupon_number;
            }
            // giảm giá trị cố định
            else {
                $order_discount = $coupon_number;
            }

            // 
            $data['order_discount'] = $order_discount;
            $data['discount_type'] = $discount_type;
        }
        // echo $order_money . '<br>' . "\n";
        // echo $order_discount . '<br>' . "\n";
        // print_r($data);
        // die(__CLASS__ . ':' . __LINE__);

        // 
        if (function_exists('before_inserts_order')) {
            $data = before_inserts_order([
                'data' => $data,
                'mail_author_id' => $mail_author_id,
            ]);
        }

        //
        $result_id = $this->order_model->insert_order($data);
        // var_dump($result_id);
        // print_r($result_id);

        // 
        if (function_exists('after_inserts_order')) {
            $data = after_inserts_order([
                'data' => $data,
                'result_id' => $result_id,
            ]);
        }

        //
        if ($result_id !== false && is_numeric($result_id) && $result_id > 0) {
            $smtp_config = $this->option_model->get_smtp();
            // print_r($smtp_config);

            /**
             * thiết lập mail thông báo
             */
            // thông tin của người đăng bài
            $author_data = [];
            $mail_author_email = null;
            if ($mail_author_id > 0) {
                $author_data = $this->user_model->get_user_by_id($mail_author_id);
                $mail_author_email = $author_data['user_email'];
            }

            // dữ liệu gửi mail mặc định
            $default_mail_data = $this->mail_queue_model->bookingDoneMail('', $result_id, null, $data, $author_data);

            // gửi mail cho khách hàng
            $mail_queue_customer = $smtp_config->mail_queue_customer;
            if ($mail_queue_customer != 'none') {
                if (isset($data['email']) && !empty($data['email'])) {
                    $mailto = $data['email'];

                    // email phải có @
                    if (strpos($mailto, '@') !== false) {
                        // chỉ hỗ trợ các định dạng email sau -> tránh spam
                        if (
                            strpos($mailto, '@gmail.com') !== false ||
                            strpos($mailto, '@yahoo.') !== false ||
                            strpos($mailto, '@hotmail.com') !== false
                        ) {
                            $status = OrderType::PENDING;
                        } else {
                            // các định dạng khác cho vào bản nháp -> không gửi
                            $status = OrderType::DRAFT;
                        }

                        // 
                        $this->mail_queue_model->insertMailq(
                            $default_mail_data,
                            [
                                'mailto' => $mailto,
                                'order_id' => $result_id,
                                'status' => $status,
                            ]
                        );
                    }
                }
            }

            // 
            $mail_queue_admin = $smtp_config->mail_queue_admin;
            $mailto = !empty($this->getconfig->emailnotice) ? $this->getconfig->emailnotice : $this->getconfig->emailcontact;

            // bằng trống -> mặc định sẽ gửi chung nội dung với khách hàng
            if ($mail_queue_admin == '') {
                $this->mail_queue_model->insertMailq(
                    $default_mail_data,
                    [
                        'mailto' => $mailto,
                        'order_id' => $result_id,
                    ]
                );
            } else if ($mail_queue_admin == 'private') {
                $this->mail_queue_model->insertMailq(
                    $this->mail_queue_model->bookingDoneMail('admin', $result_id, null, $data, $author_data),
                    [
                        'mailto' => $mailto,
                        'order_id' => $result_id,
                    ]
                );
            }

            // default -> mặc định sẽ gửi chung nội dung với khách hàng
            $mail_queue_author = $smtp_config->mail_queue_author;
            if ($mail_queue_author == 'default') {
                $this->mail_queue_model->insertMailq(
                    $default_mail_data,
                    [
                        'mailto' => $mail_author_email,
                        'order_id' => $result_id,
                    ]
                );
            } else if ($mail_queue_author == 'private') {
                $this->mail_queue_model->insertMailq(
                    $this->mail_queue_model->bookingDoneMail('author', $result_id, null, $data, $author_data),
                    [
                        'mailto' => $mail_author_email,
                        'order_id' => $result_id,
                    ]
                );
            }
            // die(__CLASS__ . ':' . __LINE__);


            // Chuyển tới trang đặt hàng thành công và xóa session giỏ hàng (nếu có)
            echo '<script>top.remove_session_cart("' . $this->order_model->orderReceiveToken($result_id, $data['post_password']) . '", "' . implode(',', $ids) . '");</script>';

            // Chuyển tới trang đặt hàng thành công
            $this->base_model->alert($this->lang_model->get_the_text('order_received_view_h1', $this->thank_you));
        }

        // không thành công thì sẽ báo lỗi
        // var_dump($result_id);
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
        $data_type = $this->MY_post('data_type');

        //
        $coupon_code = $this->MY_post('coupon_custom_code');
        if (empty($coupon_code)) {
            // Trả về dữ liệu dạng json
            if ($data_type == 'json') {
                $this->result_json_type([
                    'code' => __LINE__,
                    'error' => 'Please enter a Coupon code.',
                ]);
            }

            // 
            $this->base_model->alert('Please enter a Coupon code.', 'error');
        }
        $coupon_code = trim($coupon_code);

        //
        $data = $this->checkCouponCode($coupon_code);

        // Trả về dữ liệu dạng json
        if ($data_type == 'json') {
            $this->result_json_type([
                'code' => __LINE__,
                'data' => [
                    'coupon_amount' => $data['coupon_amount'],
                    'coupon_code' => $coupon_code,
                    'discount_type' => $data['discount_type']
                ],
            ]);
        }

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
        $data['coupon_number'] = $data['coupon_amount'];

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
        $data = null;

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
        $OrderType_COMPLETED = [
            OrderType::PRIVATELY,
            OrderType::INHERIT,
        ];

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
                        't1.ID' => 'DESC',
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
                    'limit' => 1,
                )
            );

            // 
            if (!empty($data)) {
                if (!in_array($data['post_status'], $OrderType_COMPLETED)) {
                    $cart_title = $this->lang_model->get_the_text('order_pending_view_h1', $this->thank_you);
                }
            }

            // 
            // print_r($data);
            // $test_str = $this->mail_queue_model->bookingDoneMail('', $id, $data);
            // print_r($test_str);
        }

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
                'smtp_config' => $this->option_model->get_smtp(),
                'OrderType_COMPLETED' => $OrderType_COMPLETED,
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

    /**
     * thực hiện gửi email thông qua ajax (mặc định sẽ gửi theo session hiện tại)
     **/
    public function mail_my_queue()
    {
        // chỉ chấp nhật POST
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => 'Bad request!',
            ]);
        }

        // 
        $this->result_json_type([
            'code' => __LINE__,
            'result' => $this->mail_queue_model->session_sending_mailq($this->MY_post('order_id')),
        ]);
    }

    /**
     * Trả về phí vận chuyển theo từng thành phố nếu có
     **/
    protected function shippingFee($term_id)
    {
        if (empty($term_id) || !is_numeric($term_id)) {
            return null;
        }

        // 
        $data = $this->base_model->select(
            'meta_value',
            'termmeta',
            array(
                'term_id' => $term_id,
                'meta_key' => 'shipping_fee',
            ),
            array(
                'order_by' => array(
                    'meta_id' => 'DESC',
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
                'limit' => 1,
            )
        );

        // 
        if (!empty($data)) {
            return $data['meta_value'];
        }

        // 
        return null;
    }
}
