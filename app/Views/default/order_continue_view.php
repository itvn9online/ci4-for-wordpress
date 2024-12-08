<?php

/**
 * Trang này dùng để hiển thị sản phẩm khác trong giỏ hàng nếu có
 * Không còn sản phẩm nào thì sẽ chuyển về trang sản phẩm
 */
?>
<div class="row">
    <div class="col small-12 medium-8 large-8">
        <div class="col-inner">
            <div id="append_ajax_cart"></div>
            <div class="order_received-empty">
                <?php
                if (in_array($data['post_status'], $OrderType_COMPLETED)) {
                ?>
                    <a href="./" class="btn btn-link bold">&#8592;&nbsp;Back to home page</a>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>