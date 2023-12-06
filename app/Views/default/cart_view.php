<div class="row <?php echo $by_get_id; ?>">
    <div class="col small-12 medium-12 large-12">
        <div class="col-inner">
            <h1><?php echo $cart_title; ?></h1>
        </div>
    </div>
    <div class="col small-12 medium-9 large-9">
        <div class="col-inner">
            <?php

            //
            $sub_total = 0;

            // nạp view riêng của từng theme nếu có
            $theme_default_view = VIEWS_PATH . 'default/cart_table_view.php';
            // nạp file kiểm tra private view
            include VIEWS_PATH . 'private_view.php';

            ?>
            <div class="text-right cart-sub-total">Sub-total (1 item): <span class="ebe-currency"><?php echo $sub_total; ?></span></div>
        </div>
    </div>
    <div class="col small-12 medium-3 large-3">
        <div class="col-inner">&nbsp;</div>
    </div>
</div>