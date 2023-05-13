<br>
<h1 class="text-center"><?php $lang_model->the_text('firebase_title', 'Xác minh số điện thoại'); ?></h1>
<br>
<?php
if ($current_user_id > 0) {
    include VIEWS_PATH . 'firebase_auth_view.php';
} else {
?>
    <div class="text-center">
        <p>Bạn cần <a href="guest/login" class="bold">Đăng nhập</a> trước khi thực hiện thao tác này!</p>
        <p>Nếu chưa có tài khoản, bạn có thể <a href="guest/register" class="bold">Đăng ký</a> tại đây!</p>
    </div>
<?php
}
