<?php

// 
// use App\Libraries\OrderType;

//
$base_model->adds_css([
    'wp-includes/css/cart.css',
    'wp-includes/css/order_received.css',
    THEMEPATH . 'css/order_received.css',
], [
    'cdn' => CDN_BASE_URL,
]);

?>
<!-- <br> -->
<div class="w90">
    <h1 class="cart-h1-title cart-h1-status cart-h1-<?php echo $data['post_status']; ?>"><?php echo $cart_title; ?></h1>
</div>
<!-- <br> -->
<?php

// 
if (!empty($data)) {
    // 
    $data['order_money'] *= 1;
    $data['order_discount'] *= 1;
    if ($data['order_discount'] < 0) {
        $data['order_discount'] = 0 - $data['order_discount'];
    }
    $data['shipping_fee'] *= 1;
    $data['order_bonus'] *= 1;
    if ($data['order_bonus'] < 0) {
        $data['order_bonus'] = 0 - $data['order_bonus'];
    }
    // 
    $data['order_amount'] = $data['order_money'] - $data['order_bonus'] - $data['order_discount'] + $data['shipping_fee'];

    // 
    $data['post_date'] = strtotime($data['post_date']);
    $data['post_date'] = date(EBE_DATE_FORMAT . ' H:i:s', $data['post_date']);

    // 
    $post_excerpt = json_decode($data['post_excerpt']);
    // print_r($post_excerpt);

    // xem có phần tạm ứng trước hay không
    $depositMoney = 0;
    $deposit_balance = 0;
    if ($data['deposit_money'] != '') {
        $depositMoney = $data['deposit_money'];

        // nếu là tính theo % thì quy đổi từ tổng tiền ra deposit
        if (strpos($depositMoney, '%') !== false) {
            $depositMoney = $base_model->number_only($depositMoney);
            $depositMoney = $data['order_amount'] / 100 * $depositMoney;
        } else {
            $depositMoney *= 1;
        }
        $deposit_balance = $data['order_amount'] - $depositMoney;
    }

    // 
    $order_subtotal = 0;
    $order_item = [];
    foreach ($post_excerpt as $v) {
        // print_r($v);

        // 
        $v->_price *= 1;
        $v->_quantity *= 1;
        $order_subtotal += ($v->_price * $v->_quantity);

        // 
        $order_item[] = [
            'name' => $v->post_title,
            'description' => $v->post_name,
            // 'sku' => $_SERVER['HTTP_HOST'] . '-' . $v->ID,
            'sku' => $v->ID . '',
            'unit_amount' => [
                'currency_code' => 'USD',
                'value' => $v->_price,
            ],
            'quantity' => $v->_quantity,
        ];
    }

    // 
    // echo '<!-- ';
    // print_r($data);
    // echo ' -->';


    // nạp view riêng của từng theme nếu có
    $theme_default_view = VIEWS_PATH . 'default/' . basename(__FILE__);
    // nạp file kiểm tra private view
    include VIEWS_PATH . 'private_view.php';


    // nạp view riêng của từng theme nếu có
    $theme_default_view = VIEWS_PATH . 'default/order_continue_view.php';
    // nạp file kiểm tra private view
    include VIEWS_PATH . 'private_view.php';


    // JSON.parse
    $base_model->JSON_parse([
        'current_order_data' => [
            'order_id' => $data['ID'],
            'reference_id' => $key,
            'order_subtotal' => $order_subtotal,
            'order_status' => $data['post_status'],
            'mail_queue_sending_type' => $smtp_config->mail_queue_sending_type,
            'order_amount' => $data['order_amount'],
            'shipping_fee' => $data['shipping_fee'],
            'order_discount' => $data['order_discount'] + $data['order_bonus'],
            'deposit_money' => $depositMoney,
            'deposit_balance' => $deposit_balance,
            'order_item' => $order_item,
            'shipping' => [
                // 'method' => 'United States Postal Service',
                'address' => [
                    'name' => [
                        'full_name' => $data['first_name'],
                        'surname' => $data['last_name'],
                    ],
                    'address_line_1' => $data['address'],
                    'address_line_2' => '',
                    'admin_area_2' => '',
                    'admin_area_1' => '',
                    'postal_code' => $data['zip_code'],
                    'country_code' => '',
                ],
            ],
        ]
    ]);

    // nếu đơn hàng chưa được thanh toán
    if (!in_array($data['post_status'], $OrderType_COMPLETED)) {
        // dựng mã thanh toán qua paypal nếu chưa có mã nhưng có client_id
        if (empty($getconfig->paypal_sdk_js) && !empty($getconfig->paypal_client_id)) {
            /**
             * 
             * Tìm hiểu tham số disable/ enable funding để tắt cái paylater
             * 
             * Disable funding:
             * https://developer.paypal.com/limited-release/regional/th/checkout/reference/customize-sdk/#disable-funding
             * 
             * Enable funding:
             * https://developer.paypal.com/limited-release/regional/th/checkout/reference/customize-sdk/#enablefunding
             * 
             */
            $getconfig->paypal_sdk_js = '<script src="https://www.paypal.com/sdk/js?client-id=' . $getconfig->paypal_client_id . '" async></script>';
        }

        // nạp mã thanh toán qua paypal nếu có
        if (!empty($getconfig->paypal_sdk_js)) {
            echo $getconfig->paypal_sdk_js;

            // 
            $base_model->add_js('wp-includes/javascript/order_paypal_received.js', [
                'cdn' => CDN_BASE_URL,
            ], [
                'defer'
            ]);
        }
    }


    // các file functions sẽ được nạp trước
    $base_model->adds_js([
        'wp-includes/javascript/cart_functions.js',
        THEMEPATH . 'js/cart_functions.js',
    ], [
        'cdn' => CDN_BASE_URL,
    ]);

    //
    $base_model->adds_js([
        // 'wp-includes/javascript/datetimepicker.js',
        'wp-includes/javascript/order_received.js',
        THEMEPATH . 'js/order_received.js',
    ], [
        'cdn' => CDN_BASE_URL,
    ], [
        'defer'
    ]);
}
