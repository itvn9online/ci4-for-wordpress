<?php

// nạp js cảnh báo đăng nhập
$base_model->add_js('wp-includes/javascript/ebe_captcha.js', [
    'cdn' => CDN_BASE_URL,
], [
    'defer'
]);

// chức năng đăng nhập tự động có cache nên không dùng hide-captcha được
//$hide_rememberme_captcha = 'hide-rememberme' . RAND_MULTI_LOGGED . '-captcha';

?>
<script type="text/javascript" {csp-script-nonce}>
    (function() {
        function before_ebe_captcha(max_i) {
            if (max_i < 0) {
                console.log('max i:', max_i);
                return false;
            } else if (typeof action_ebe_captcha != 'function') {
                setTimeout(function() {
                    before_ebe_captcha(max_i - 1)
                }, 100);
                return false;
            }
            action_ebe_captcha('<?php echo RAND_GET_ANTI_SPAM; ?>');
        }
        before_ebe_captcha(99);
    })();
</script>