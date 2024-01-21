<div class="cart-content">
    <div class="cart_totals-th">Cart totals (<span class="total-cart-quantity redcolor"></span> item)</div>
    <br>
</div>
<div class="checkout-content">
    <h3>Your order</h3>
    <table class="cart-table cart-sidebar-table">
        <tr class="upper">
            <th class="product-name">Product</th>
            <th class="product-subtotal text-right">Subtotal</th>
        </tr>
        <?php

        //
        foreach ($data as $v) {
            $post_meta = $v['post_meta'];
            foreach ([
                '_regular_price',
            ] as $k2) {
                if (!isset($post_meta[$k2])) {
                    $post_meta[$k2] = '';
                }
            }

            //
            $post_meta['_regular_price'] = str_replace(',', '', $post_meta['_regular_price']);
        ?>
            <tr>
                <td>
                    <?php echo $v['post_title']; ?> <i class="fa fa-remove"></i> <strong data-id="<?php echo $v['ID']; ?>" class="change-product-quantity">1</strong>
                </td>
                <td class="cart-regular_price text-right">
                    <span data-id="<?php echo $v['ID']; ?>" class="ebe-currency change-cart-regular_price"></span>
                </td>
            </tr>
        <?php
        }

        ?>
    </table>
    <div class="text-right center-if-mobile">
        <button type="button" onclick="return proceed_to_cart();" class="btn btn-link s14"><em>Click here to edit your cart</em></button>
    </div>
</div>
<div class="cart-subtotal">
    <div class="cart-sub-total">Subtotal <span class="ebe-currency rf cart-subtotal-regular_price"></span></div>
    <div class="cart-sub-total cart-sidebar-coupon">Coupon:
        <span class="upper cart-discount-code"></span>
        <span onclick="return remove_coupon_code();" class="cur redcolor small">[Remove]</span>
        <span class="rf">
            -<span class="cart-discount-value bold"></span>
        </span>
    </div>
    <div class="cart-sub-total">
        <?php $lang_model->the_text('cart_sidebar_shipping', 'Shipping'); ?>
        <span class="rf cart-sidebar-shipping"></span>
    </div>
    <div class="cart-sub-total bold">Total <span class="ebe-currency rf cart-total-regular_price"></span></div>
</div>
<br>
<div class="cart-content">
    <div class="proceed-to-checkout">
        <button type="button" onclick="return proceed_to_checkout();" class="btn btn-danger f100 bold upper">Proceed to checkout</button>
    </div>
    <div>
        <div class="cart_totals-th"><i class="fa fa-tag"></i> Coupon</div>
        <div class="checkout_coupon">
            <form method="post" action="actions/add_coupon" accept-charset="utf-8" target="target_eb_iframe">
                <div class="coupon-code">
                    <input type="text" id="coupon_custom_code" name="coupon_custom_code" value="" placeholder="Coupon code" aria-required="true" required>
                </div>
                <div>
                    <button type="submit" class="btn btn-secondary f100">Apply coupon</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="checkout-content">
    <p>Have a coupon? <button type="button" onclick="return proceed_to_coupon();" class="btn btn-link">Click here to enter your code</button></p>
    <div class="place-order">
        <input type="submit" form="frm_actions_cart" value="Place order" class="btn btn-success f100 bold upper" />
    </div>
</div>