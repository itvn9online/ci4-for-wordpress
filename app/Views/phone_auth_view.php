<br>
<h1 class="text-center"><?php $lang_model->the_text('firebase_title', 'Phone verify'); ?></h1>
<br>
<?php
if ($getconfig->g_firebase_config != '') {
    if ($current_user_id > 0) {
        //print_r($session_data);

        //
?>
        <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
        <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-auth.js"></script>
        <script>
            <?php
            echo $getconfig->g_firebase_config;
            ?>
        </script>
        <script src="https://www.gstatic.com/firebasejs/ui/6.0.2/firebase-ui-auth__vi.js"></script>
        <link type="text/css" rel="stylesheet" href="https://www.gstatic.com/firebasejs/ui/6.0.2/firebase-ui-auth.css" />
        <?php

        //
        $base_model->add_css('javascript/firebasejs/style.css', [
            'cdn' => CDN_BASE_URL,
        ]);

        ?>
        <div class="row">
            <div class="col small-12 medium-3 large-3">
                <div class="col-inner">&nbsp;</div>
            </div>
            <div class="col small-12 medium-6 large-6">
                <div class="col-inner">
                    <div id="loading">Loading...</div>
                    <div id="loaded" class="hidden">
                        <div id="main">
                            <div id="user-signed-in" class="hidden">
                                <div id="firebase_user-info">
                                    <p>Name: <span id="firebase_name"></span></p>
                                    <p>Email: <span id="firebase_email"></span></p>
                                    <p>Phone: <span id="firebase_phone"></span></p>
                                    <div class="clearfix"></div>
                                </div>
                                <div>
                                    <button type="button" id="sign-out" class="btn btn-secondary"><?php $lang_model->the_text('firebase_sign_out', 'Sign Out'); ?></button>
                                    <button type="button" id="delete-account" class="btn btn-danger d-none"><?php $lang_model->the_text('firebase_delete_account', 'Delete account'); ?></button>
                                </div>
                            </div>
                            <div id="user-signed-out" class="hidden">
                                <div id="firebaseui-spa">
                                    <div id="firebaseui-container"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="container">
        </div>
    <?php

        //
        $base_model->JSON_echo([
            // mảng này sẽ in ra dưới dạng JSON hoặc number
        ], [
            // mảng này sẽ in ra dưới dạng string
            'firebase_privacy_policy_url' => $getconfig->g_firebase_privacy_policy_url,
            'firebase_terms_service_url' => $getconfig->g_firebase_terms_service_url,
            'g_firebase_default_country' => $getconfig->g_firebase_default_country,
            'g_firebase_login_hint' => $getconfig->g_firebase_login_hint,
            'default_national_number' => $session_data['user_phone'],
        ]);

        //
        $base_model->add_js('javascript/firebasejs/app.js', [
            'cdn' => CDN_BASE_URL,
        ], [
            'defer'
        ]);
    } else {
    ?>
        <div class="text-center">
            <p>Bạn cần <a href="guest/login" class="bold">Đăng nhập</a> trước khi thực hiện thao tác này!</p>
            <p>Nếu chưa có tài khoản, bạn có thể <a href="guest/register" class="bold">Đăng ký</a> tại đây!</p>
        </div>
    <?php
    }
} else {
    ?>
    <p class="medium18 text-center">SDK setup and configuration is EMPTY!
        <br>Please find <b>g_firebase_config</b> in base code and setup...
    </p>
<?php
}
