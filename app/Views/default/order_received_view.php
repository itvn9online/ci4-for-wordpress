<div class="row row-small row-divided-xoa cart-is-product">
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
                    foreach ($post_excerpt as $v) {
                    ?>
                        <tr>
                            <td class="global-a-posi">
                                <a href="?p=<?php echo $v->ID; ?>" target="_blank">&nbsp;</a>
                                <div><img src="<?php echo $v->image; ?>" height="90" /></div>
                                <div class="bluecolor"><?php echo $v->post_title; ?> x <?php echo $v->_quantity; ?></div>
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
                    <tr class="order-overview__subtotal">
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
                        <tr class="order-overview__coupon">
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
                        <tr class="order-overview__bonus">
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
                    <tr class="order-overview__payment_method d-none">
                        <td>
                            <?php $lang_model->the_text('cart_sidebar_payment_method', 'Payment method'); ?>:
                        </td>
                        <td><?php $lang_model->the_text('cart_cod_payment', 'Cash on delivery'); ?></td>
                    </tr>
                    <tr class="order-overview__total">
                        <td>
                            <?php $lang_model->the_text('cart_sidebar_total', 'Total'); ?>:
                        </td>
                        <td>
                            <strong class="ebe-currency-format"><?php echo $data['order_amount']; ?></strong>
                        </td>
                    </tr>
                    <?php
                    if ($depositMoney > 0) {
                    ?>
                        <tr class="order-overview__deposit_money">
                            <td>
                                <?php $lang_model->the_text('cart_sidebar_deposit_money', 'Deposit'); ?>:
                            </td>
                            <td>
                                <strong class="ebe-currency"><?php echo $depositMoney; ?></strong>
                            </td>
                        </tr>
                        <tr class="order-overview__deposit_balance">
                            <td>
                                <?php $lang_model->the_text('cart_sidebar_deposit_balance', 'Remaining amount'); ?>:
                            </td>
                            <td>
                                <strong class="ebe-currency"><?php echo $deposit_balance; ?></strong>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
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
                <li class="order-overview__order">
                    <?php $lang_model->the_text('cart_sidebar_order_id', 'Order number'); ?>:
                    <strong>#<?php echo $data['ID']; ?></strong>
                </li>
                <li class="order-overview__date">
                    Date: <strong><?php echo $data['post_date']; ?></strong>
                </li>
                <li class="order-overview__email">
                    Email: <strong><?php echo $data['email']; ?></strong>
                </li>
                <li class="order-overview__total">
                    <?php $lang_model->the_text('cart_sidebar_total', 'Total'); ?>:
                    <strong class="ebe-currency-format"><?php echo $data['order_amount']; ?></strong>
                </li>
                <li class="order-overview__payment_method d-none">
                    <?php $lang_model->the_text('cart_sidebar_payment_method', 'Payment method'); ?>:
                    <strong><?php $lang_model->the_text('cart_cod_payment', 'Cash on delivery'); ?></strong>
                </li>
                <?php
                if ($depositMoney > 0) {
                ?>
                    <li class="order-overview__deposit_money">
                        <?php $lang_model->the_text('cart_sidebar_deposit_money', 'Deposit'); ?>:
                        <strong class="ebe-currency"><?php echo $depositMoney; ?></strong>
                    </li>
                    <li class="order-overview__deposit_balance">
                        <?php $lang_model->the_text('cart_sidebar_deposit_balance', 'Remaining amount'); ?>:
                        <strong class="ebe-currency"><?php echo $deposit_balance; ?></strong>
                    </li>
                <?php
                }
                ?>
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
<!-- <br> -->