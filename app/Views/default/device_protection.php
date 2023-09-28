<!-- Modal cảnh báo đăng nhập trên nhiều thiết bị -->
<div class="modal fade" id="warningLoggedModal" tabindex="-1" aria-labelledby="warningLoggedLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="warningLoggedLabel"><i class="fa fa-warning"></i> Cảnh báo đăng nhập!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body medium">
                <p class="redcolor">Vui lòng không đăng nhập trên nhiều thiết bị hoặc nhiều trình duyệt khác nhau!</p>
                <p>Lịch sử đăng nhập của bạn đã được lưu lại để kiểm tra. Trong một số trường hợp! Nếu phát hiện hành vi gian lận, chúng tôi sẽ tiến hành khóa tài khoản của bạn.</p>
                <p>Phiên hiện tại: <a href="https://www.iplocation.net" target="_blank" rel="nofollow" class="bold greencolor show-current-ip"><?php echo session_id(); ?></a></p>
                <p>Phiên nghi vấn: <a target="_blank" rel="nofollow" class="show-logged-ip bold redcolor"></a></p>
                <p>Thiết bị: <strong class="show-logged-device"></strong></p>
                <p>Trình duyệt: <strong class="show-logged-agent"></strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fa fa-remove"></i> Bỏ qua</button>
                <button type="button" onclick="return confirm_kip_logged();" class="btn btn-primary" data-bs-dismiss="modal"><i class="fa fa-check"></i> Tôi đã hiểu</button>
            </div>
        </div>
    </div>
</div>
<?php

// lưu session id của người dùng vào file
$user_model->setLogged($current_user_id);

// Nạp url cho request ajax
$base_model->JSON_echo([
    // mảng này sẽ in ra dưới dạng JSON hoặc number
], [
    // mảng này sẽ in ra dưới dạng string
    // request_multi_logout
    'rmlogout' => RAND_MULTI_LOGOUT,
    // request_multi_logged
    'rmlogged' => RAND_MULTI_LOGGED,
]);

// nạp js cảnh báo đăng nhập
$base_model->add_js('wp-includes/javascript/device_protection.js', [
    'cdn' => CDN_BASE_URL,
], [
    'defer'
]);
