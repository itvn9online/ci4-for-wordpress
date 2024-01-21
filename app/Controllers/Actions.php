<?php

namespace App\Controllers;

//
use App\Libraries\PostType;

//
class Actions extends Layout
{
    // chức năng này không cần nạp header
    // public $preload_header = false;

    public function __construct()
    {
        parent::__construct();
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
                    'post_type' => PostType::PROD,
                    'post_status' => PostType::PUBLICITY
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
                        'post_type' => PostType::PROD,
                        'post_status' => PostType::PUBLICITY
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
        $data = $this->MY_post('data');
        print_r($data);

        // lấy danh sách sản phẩm
        $cart_id = $this->MY_post('cart_id', []);
        print_r($cart_id);
        $cart_quantity = $this->MY_post('cart_quantity', []);
        print_r($cart_quantity);

        // chạy vòng lặp lấy các sp có số lượng > 0
        $ids = [];
        foreach ($cart_quantity as $k => $v) {
            if (empty($v) || !is_numeric($v) || $v < 1) {
                continue;
            }

            //
            $ids[] = $cart_id[$k];
        }
        print_r($ids);

        //
        if (empty($ids)) {
            $this->base_model->alert('No valid products were found in your cart!', 'warning');
        }

        // lấy các sản phẩm theo ids tìm thấy được
        $products = $this->base_model->select(
            '*',
            // WGR_POST_VIEW,
            'posts',
            array(
                // các kiểu điều kiện where
                'post_status' => PostType::PUBLICITY,
            ),
            array(
                'where_in' => array(
                    'ID' => $ids
                ),
                'order_by' => array(
                    'ID' => 'DESC'
                ),
                // hiển thị mã SQL để check
                'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                // trả về COUNT(column_name) AS column_name
                //'selectCount' => 'ID',
                // trả về tổng số bản ghi -> tương tự mysql num row
                //'getNumRows' => 1,
                //'offset' => 0,
                'limit' => -1
            )
        );
        print_r($products);

        //
        $this->base_model->alert('OK');
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

        // thông báo khi coupon hết hạn
        // $this->base_model->alert('This coupon has expired!', 'warning');

        //
        if ($coupon_code != 'xuan2024') {
            $this->base_model->alert('Coupon `' . $coupon_code . '` does not exist!', 'error');
        }

        // TEST
        $coupon_amount = rand(25, 50);
        // $coupon_amount .= '%';

        //
        $coupon_amount = trim(str_replace(' ', '', $coupon_amount));

        // coupon amount không có giá trị -> báo lỗi
        if ($this->base_model->_eb_number_only($coupon_amount) < 1) {
            $this->base_model->alert('Coupon amount has not been setup yet!', 'warning');
        }

        // Thiết lập lại thông số cho coupon
        echo '<script>top.add_coupon_code("' . $coupon_amount . '", "' . $coupon_code . '");</script>';

        //
        $this->base_model->alert('Coupon code applied successfully.');
    }
}
