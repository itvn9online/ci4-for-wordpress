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
                include VIEWS_PATH . 'firebase_auth_view.php';
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
