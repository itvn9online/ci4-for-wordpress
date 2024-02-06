<br>
<div class="w90">
    <h1 class="greencolor"><?php echo $cart_title; ?></h1>
</div>
<br>
<?php
if (!empty($data)) {
    print_r($data);
?>
    <div class="row row-divided">
        <div class="col small-12 medium-8 large-8">
            <div class="col-inner">
                <h2>Order details</h2>
                <div class="row">
                    <div class="col">
                        <h2>Billing address</h2>
                    </div>
                    <div class="col">
                        <h2>Shipping address</h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col small-12 medium-4 large-4">
            <div class="col-inner cart-sidebar ul-default-style">
                <ul class="thankyou-order-details">
                    <li class="order-overview__order order">
                        Order number: <strong><?php echo $data['ID']; ?></strong>
                    </li>
                    <li class="order-overview__date date">
                        Date: <strong><?php echo $data['post_date']; ?></strong>
                    </li>
                    <li class="order-overview__email email">
                        Email: <strong><?php echo $data['email']; ?></strong>
                    </li>
                    <li class="order-overview__total total">
                        Total: <strong class="ebe-currency-format"><?php echo $data['order_money']; ?></strong>
                    </li>
                    <li class="order-overview__payment-method method">
                        Payment method: <strong>Cash on delivery</strong>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <br>
<?php
}
