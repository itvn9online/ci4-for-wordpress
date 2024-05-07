<?php

//
use App\Libraries\ConfigType;

//
// nạp config cho phần đăng nhập
$firebase_config = $option_model->obj_config(ConfigType::FIREBASE);
//echo $firebase_config->g_recaptcha_site_key;

// 
if (!empty($firebase_config->g_recaptcha_site_key)) {
?>
    <script src="https://www.google.com/recaptcha/api.js?render=<?php echo $firebase_config->g_recaptcha_site_key; ?>" defer {csp-script-nonce}></script>
    <script {csp-script-nonce}>
        function my_grecaptcha_ready(e) {
            // e.preventDefault();
            grecaptcha.ready(function() {
                grecaptcha
                    .execute("<?php echo $firebase_config->g_recaptcha_site_key; ?>", {
                        action: "submit"
                    })
                    .then(function(token) {
                        // Add your logic to submit to your backend server here.
                        my_grecaptcha_then(token);
                    });
            });
        }
    </script>
<?php

    // 
    $base_model->add_js('wp-includes/javascript/g_recaptcha.js', [
        'cdn' => CDN_BASE_URL,
    ], [
        'defer'
    ]);
}
