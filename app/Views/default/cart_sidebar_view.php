<div class="cart-content">
    <div class="cart_totals-th">Cart totals (<span class="total-cart-quantity redcolor"></span> item)</div>
    <br>
</div>
<div class="checkout-content">
    <h3>Your order</h3>
    <div class="cart-sidebar-table"></div>
    <div class="text-right center-if-mobile">
        <button type="button" onclick="return proceed_to_cart();" class="btn btn-link s14"><em>Click here to edit your cart</em></button>
    </div>
</div>
<div class="cart-subtotal">
    <div class="cart-sub-total">
        <?php $lang_model->the_text('cart_sidebar_subtotal', 'Subtotal'); ?>
        <span class="ebe-currency rf cart-subtotal-regular_price"></span>
    </div>
    <div class="cart-sub-total cart-sidebar-coupon">
        <?php $lang_model->the_text('cart_sidebar_coupon', 'Coupon'); ?>:
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
    <div class="cart-sub-total cart-sub-regular_price bold">
        <?php $lang_model->the_text('cart_sidebar_total', 'Total'); ?>
        <span class="ebe-currency rf cart-total-regular_price"></span>
    </div>
    <div class="cart-group-deposit-money d-none bold">
        <div class="cart-sub-total cart-sub-deposit-money">
            <?php $lang_model->the_text('cart_sidebar_deposit_money', 'Deposit'); ?>
            <span class="rf cart-total-deposit-money"></span>
        </div>
        <div class="cart-sub-total cart-sub-deposit_balance">
            <?php $lang_model->the_text('cart_sidebar_deposit_balance', 'Remaining amount'); ?>
            <span class="rf cart-total-deposit_balance ebe-currency"></span>
        </div>
    </div>
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