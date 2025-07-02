<?php

//
//echo WGR_COOKIE_LOGIN_KEY;

// nạp js cảnh báo đăng nhập
$base_model->add_js('wp-includes/javascript/login_rememberme.js', [
    'cdn' => CDN_BASE_URL,
], [
    'defer'
]);

// chức năng đăng nhập tự động có cache nên không dùng hide-captcha được
//$hide_rememberme_captcha = 'hide-rememberme' . RAND_MULTI_LOGGED . '-captcha';

?>
<script {csp-script-nonce}>
    (function() {
        function before_login_rememberme(max_i) {
            if (max_i < 0) {
                console.log('max i:', max_i);
                return false;
            } else if (typeof action_login_rememberme != 'function') {
                setTimeout(function() {
                    before_login_rememberme(max_i - 1)
                }, 100);
                return false;
            }
            action_login_rememberme('<?php echo WGR_COOKIE_LOGIN_KEY; ?>', '<?php echo RAND_REMEMBER_LOGIN; ?>');
        }
        before_login_rememberme(99);
    })();
</script>