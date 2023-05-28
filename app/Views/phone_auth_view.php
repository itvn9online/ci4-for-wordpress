<br>
<h1 class="text-center"><?php $lang_model->the_text('firebase_title', 'Xác minh số điện thoại'); ?></h1>
<br>
<?php
if ($current_user_id > 0 || !empty($phone_number)) {
?>
    <div class="row">
        <div class="col small-12 medium-3 large-3">
            <div class="col-inner">&nbsp;</div>
        </div>
        <div class="col small-12 medium-6 large-6">
            <div class="col-inner text-center">
                <?php
                if ($firebase_config->firebase_verify_phone === 'on') {
                    include VIEWS_PATH . 'firebase_auth_view.php';

                    //
                    if (empty($firebase_config->g_firebase_config)) {
                ?>
                        <p class="medium18 text-center">SDK setup and configuration is EMPTY!
                            <br>Please find <b>g_firebase_config</b> in base code and setup...
                        </p>
                    <?php
                    }
                } else {
                    ?>
                    <div class="redcolor"><?php $lang_model->the_text('firebase_verify_phone_warning', 'Chức năng xác thực số điện thoại đang tạm ngừng!'); ?></div>
                <?php
                }
                ?>
            </div>
        </div>
    </div>
<?php
} else {
?>
    <div class="text-center">
        <p>Bạn cần <a href="guest/login" class="bold">Đăng nhập</a> trước khi thực hiện thao tác này!</p>
        <p>Nếu chưa có tài khoản, bạn có thể <a href="guest/register" class="bold">Đăng ký</a> tại đây!</p>
    </div>
<?php
}
