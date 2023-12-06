<div class="row <?php echo $by_get_id; ?>">
    <div class="col small-12 medium-12 large-12">
        <div class="col-inner">
            <h1><?php echo $cart_title; ?></h1>
        </div>
    </div>
    <div class="col small-12 medium-9 large-9">
        <div class="col-inner">
            <form action="" method="post" name="frm_actions_cart" id="frm_actions_cart" accept-charset="utf-8" target="target_eb_iframe">
                <?php $base_model->anti_spam_field(); ?>
                <div id="append_ajax_cart">
                    <?php

                    // nạp view riêng của từng theme nếu có
                    $theme_default_view = VIEWS_PATH . 'default/cart_table_view.php';
                    // nạp file kiểm tra private view
                    include VIEWS_PATH . 'private_view.php';

                    ?>
                </div>
                <?php

                // nạp view riêng của từng theme nếu có
                $theme_default_view = VIEWS_PATH . 'default/cart_form_view.php';
                // nạp file kiểm tra private view
                include VIEWS_PATH . 'private_view.php';

                ?>
            </form>
        </div>
    </div>
    <div class="col small-12 medium-3 large-3">
        <div class="col-inner">&nbsp;</div>
    </div>
</div>