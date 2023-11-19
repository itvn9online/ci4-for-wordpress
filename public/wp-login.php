<?php

/**
 * File này chỉ để giả lập wordpress, cho mấy thanh niên thích quậy phá nhìn vào ban đầu tưởng là wordpress -> dể chọc phá theo hướng đấy -> faild
 * Ở đây viết ít code giống như chức năng đăng nhập của wordpress vậy, nhưng thực tế không đăng nhập
 * Kiếm tí request xong trả về thông báo chung chung thôi
 **/

//
// session_start();
// print_r($_SESSION);

//
$user_login = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //print_r($_POST);
    if (isset($_POST['log'])) {
        $user_login = trim($_POST['log']);
    }
    // } else if (isset($_SESSION['_wgr_logged']) && !empty($_SESSION['_wgr_logged'])) {
    //     // nếu người dùng đã đăng nhập rồi thì chuyển luôn về trang chủ
    //     die(header('Location: https://' . $_SERVER['HTTP_HOST'] . '/'));
}

//
$action = isset($_GET['action']) ? $_GET['action'] : '';

?>
<!DOCTYPE html>
<html lang="vi" id="html">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Đăng nhập &#8212; WordPress</title>
    <meta name='robots' content='max-image-preview:large, noindex, noarchive' />
    <link rel='stylesheet' id='login-css' href='https://<?php echo $_SERVER['HTTP_HOST']; ?>/wp-includes/css/login.min.css' type='text/css' media='all' />
    <meta name='referrer' content='strict-origin-when-cross-origin' />
    <meta name="viewport" content="width=device-width" />
</head>

<body class="login no-js login-action-login wp-core-ui locale-vi">
    <script type="text/javascript">
        document.body.className = document.body.className.replace('no-js', 'js');
    </script>
    <div id="login">
        <h1><a href="https://vi.wordpress.org/" data-wpel-link="external">Powered by WordPress</a></h1>
        <?php
        if ($action == 'lostpassword') {
        ?>
            <p class="message">Please enter your username or email address. You will receive an email message with instructions on how to reset your password.</p>
            <?php
            if (!empty($user_login)) {
            ?>
                <div id="login_error"> <strong>Error:</strong> There is no account with that username or email address.<br>
                </div>
            <?php
            }
        } else if (!empty($user_login)) {
            if (strpos($user_login, '@') !== false) {
            ?>
                <div id="login_error"> Unknown email address. Check again or try your username.<br>
                </div>
            <?php
            } else {
            ?>
                <div id="login_error"> <strong>Error:</strong> The username <strong><?php echo $user_login; ?></strong> is not registered on this site. If you are unsure of your username, try your email address instead.<br>
                </div>
        <?php
            }
        }
        ?>
        <form name="loginform" id="loginform" action="" method="post">
            <p>
                <label for="user_login">Username or Email Address</label>
                <input type="text" name="log" id="user_login" class="input" value="<?php echo $user_login; ?>" size="20" autocapitalize="off" autocomplete="username" required="required" />
            </p>
            <?php
            if ($action != 'lostpassword') {
            ?>
                <div class="user-pass-wrap">
                    <label for="user_pass">Password</label>
                    <div class="wp-pwd">
                        <input type="password" name="pwd" id="user_pass" class="input password-input" value="" size="20" autocomplete="current-password" spellcheck="false" required="required" />
                    </div>
                </div>
                <p class="forgetmenot"><input name="rememberme" type="checkbox" id="rememberme" value="forever" /> <label for="rememberme">Remember Me</label></p>
            <?php
            }
            ?>
            <p class="submit">
                <input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php echo ($action == 'lostpassword' ? 'Get New Password' : 'Log In'); ?>" />
                <input type="hidden" name="redirect_to" value="https://<?php echo $_SERVER['HTTP_HOST']; ?>/wp-admin/" />
                <input type="hidden" name="testcookie" value="1" />
            </p>
        </form>
        <p id="nav">
            <?php
            if ($action == 'lostpassword') {
            ?>
                <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/wp-login.php" data-wpel-link="internal">Log in</a>
            <?php
            } else {
            ?>
                <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/wp-login.php?action=lostpassword" data-wpel-link="internal">Lost your password?</a>
            <?php
            }
            ?>
        </p>
        <p id="backtoblog">
            <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/" data-wpel-link="internal">&larr; Go to Homepage</a>
        </p>
    </div>
</body>

</html>