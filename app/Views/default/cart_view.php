<?php

//
$base_model->JSON_parse([
    'cart_config' => [
        'coupon_code' => $coupon_code,
        'coupon_amount' => $coupon_amount,
        'shipping_fee' => $getconfig->shipping_fee,
        'free_shipping' => $lang_model->get_the_text('shipping_free', 'Free shipping'),
        'calculated_later' => $lang_model->get_the_text('calculated_later', 'Calculated later'),
    ],
]);

?>
<br>
<div class="w90">
    <h1><?php echo $cart_title; ?></h1>
</div>
<br>
<div class="row row-large row-divided row-cart cart-is-product d-none <?php echo $by_get_id; ?>">
    <div class="col small-12 medium-8 large-8">
        <div class="col-inner">
            <form action="" method="post" name="frm_actions_cart" id="frm_actions_cart" accept-charset="utf-8" target="target_eb_iframe">
                <?php $base_model->anti_spam_field(); ?>
                <input type="hidden" id="coupon_code" name="coupon_code" value="">
                <!-- cart content -->
                <div class="cart-content">
                    <div id="append_ajax_cart">
                        <?php

                        // nạp view riêng của từng theme nếu có
                        $theme_default_view = VIEWS_PATH . 'default/cart_table_view.php';
                        // nạp file kiểm tra private view
                        include VIEWS_PATH . 'private_view.php';

                        ?>
                    </div>
                    <br>
                    <div>
                        <a href="./" class="btn btn-link bold upper">&#8592;&nbsp;Continue shopping</a>
                    </div>
                </div>
                <div class="checkout-content">
                    <h3>Billing details</h3>
                    <?php

                    // nạp view riêng của từng theme nếu có
                    $theme_default_view = VIEWS_PATH . 'default/cart_form_view.php';
                    // nạp file kiểm tra private view
                    include VIEWS_PATH . 'private_view.php';

                    ?>
                </div>
            </form>
        </div>
    </div>
    <div class="col small-12 medium-4 large-4">
        <div class="col-inner cart-sidebar">
            <?php

            // nạp view riêng của từng theme nếu có
            $theme_default_view = VIEWS_PATH . 'default/cart_sidebar_view.php';
            // nạp file kiểm tra private view
            include VIEWS_PATH . 'private_view.php';

            ?>
        </div>
    </div>
</div>
<div class="top-menu-space text-center cart-is-empty">
    <p>Your cart is currently empty.</p>
    <p>
        <a href="./" class="btn btn-primary upper bold">Return to shop</a>
    </p>
</div>
<br>