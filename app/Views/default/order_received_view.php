<div class="row row-divided">
    <div class="col small-12 medium-8 large-8">
        <div class="col-inner">
            <h2>Order details</h2>
            <table class="cart-table cart-order_received">
                <thead>
                    <tr class="upper">
                        <th class="product-name">Product</th>
                        <th class="product-subtotal"><?php $lang_model->the_text('cart_sidebar_total', 'Total'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php

                    //
                    foreach ($data['post_excerpt'] as $v) {
                    ?>
                        <tr>
                            <td>
                                <?php echo $v->post_title; ?> x <?php echo $v->_quantity; ?>
                            </td>
                            <td>
                                <span class="ebe-currency-format"><?php echo $v->_price; ?></span>
                            </td>
                        </tr>
                    <?php
                    }

                    ?>
                </tbody>
                <tfoot>
                    <tr class="order-overview__subtotal subtotal">
                        <td>
                            <?php $lang_model->the_text('cart_sidebar_subtotal', 'Subtotal'); ?>:
                        </td>
                        <td>
                            <strong class="ebe-currency-format"><?php echo $data['order_money']; ?></strong>
                        </td>
                    </tr>
                    <?php
                    if ($data['order_discount'] > 0) {
                    ?>
                        <tr class="order-overview__coupon coupon">
                            <td>
                                <?php $lang_model->the_text('cart_sidebar_coupon', 'Coupon'); ?>:
                            </td>
                            <td>
                                <strong class="ebe-currency-format"><?php echo $data['order_discount']; ?></strong>
                            </td>
                        </tr>
                    <?php
                    }

                    // 
                    if ($data['order_bonus'] > 0) {
                    ?>
                        <tr class="order-overview__bonus bonus">
                            <td>
                                <?php $lang_model->the_text('cart_sidebar_bonus', 'Bonus'); ?>:
                            </td>
                            <td>
                                <strong class="ebe-currency-format"><?php echo $data['order_bonus']; ?></strong>
                            </td>
                        </tr>
                    <?php
                    }

                    // 
                    if ($data['shipping_fee'] > 0) {
                    ?>
                        <tr class="order-overview__shipping shipping">
                            <td>
                                <?php $lang_model->the_text('cart_sidebar_shipping', 'Shipping'); ?>:
                            </td>
                            <td>
                                <strong class="ebe-currency-format"><?php echo $data['shipping_fee']; ?></strong>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                    <tr class="order-overview__payment-method method">
                        <td>
                            <?php $lang_model->the_text('cart_sidebar_payment_method', 'Payment method'); ?>:
                        </td>
                        <td>Cash on delivery</td>
                    </tr>
                    <tr class="order-overview__total total">
                        <td>
                            <?php $lang_model->the_text('cart_sidebar_total', 'Total'); ?>:
                        </td>
                        <td>
                            <strong class="ebe-currency-format"><?php echo $data['order_amount']; ?></strong>
                        </td>
                    </tr>
                </tfoot>
            </table>
            <br>
            <div class="row">
                <div class="col">
                    <h2>Billing address</h2>
                    <p><?php echo $data['first_name']; ?> <?php echo $data['last_name']; ?></p>
                    <p><?php echo $data['company']; ?></p>
                    <p><?php echo $data['address']; ?></p>
                    <p><?php echo $data['zip_code']; ?></p>
                    <p><?php echo $data['phone']; ?></p>
                    <p><?php echo $data['email']; ?></p>
                </div>
                <div class="col d-none">
                    <h2>Shipping address</h2>
                </div>
            </div>
        </div>
    </div>
    <div class="col small-12 medium-4 large-4">
        <div class="col-inner cart-sidebar ul-default-style">
            <h2>Payments</h2>
            <ul class="thankyou-order-details">
                <li class="order-overview__order order">
                    <?php $lang_model->the_text('cart_sidebar_order_id', 'Order number'); ?>:
                    <strong>#<?php echo $data['ID']; ?></strong>
                </li>
                <li class="order-overview__date date">
                    Date: <strong><?php echo $data['post_date']; ?></strong>
                </li>
                <li class="order-overview__email email">
                    Email: <strong><?php echo $data['email']; ?></strong>
                </li>
                <li class="order-overview__total total">
                    <?php $lang_model->the_text('cart_sidebar_total', 'Total'); ?>:
                    <strong class="ebe-currency-format"><?php echo $data['order_amount']; ?></strong>
                </li>
                <li class="order-overview__payment-method method">
                    <?php $lang_model->the_text('cart_sidebar_payment_method', 'Payment method'); ?>:
                    <strong>Cash on delivery</strong>
                </li>
            </ul>
            <?php
            if (!in_array($data['post_status'], $OrderType_COMPLETED)) {
            ?>
                <div id="paypal-button-container"></div>
            <?php
            } else {
            ?>
                <p class="text-center">
                    <button type="button" class="btn btn-success">
                        <?php echo $OrderType_arrStatus[$data['post_status']]; ?>
                    </button>
                </p>
            <?php
            }
            ?>
        </div>
    </div>
</div>
<br>