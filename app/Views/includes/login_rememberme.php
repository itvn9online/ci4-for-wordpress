<?php

//
//echo $wrg_cookie_login_key;

// nạp js cảnh báo đăng nhập
$base_model->add_js('wp-includes/javascript/login_rememberme.js', [
    'cdn' => CDN_BASE_URL,
], [
    'defer'
]);

//
$hide_rememberme_captcha = 'hide-rememberme' . RAND_MULTI_LOGGED . '-captcha';

?>
<div id="<?php echo $hide_rememberme_captcha; ?>"><?php $base_model->hide_captcha_field(30); ?></div>
<script type="text/javascript">
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
        action_login_rememberme('<?php echo $wrg_cookie_login_key; ?>', '<?php echo $hide_rememberme_captcha; ?>');
    }
    before_login_rememberme(99);
</script>