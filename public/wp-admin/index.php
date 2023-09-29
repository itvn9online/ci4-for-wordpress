<?php

/**
 * File này chỉ để giả lập wordpress, cho mấy thanh niên thích quậy phá nhìn vào ban đầu tưởng là wordpress -> dể chọc phá theo hướng đấy -> faild
 * Truy cập vào đường dẫn admin thì redirect sang trang login như đúng rồi
 **/

//
session_start();
//print_r($_SESSION);

//
if (isset($_SESSION['_wgr_logged']) && !empty($_SESSION['_wgr_logged'])) {
    // nếu người dùng đã đăng nhập rồi thì chuyển luôn về trang chủ
    die(header('Location: https://' . $_SERVER['HTTP_HOST'] . '/'));
}

die(header('Location: https://' . $_SERVER['HTTP_HOST'] . '/wp-login.php?redirect_to=' . urlencode('https://' . $_SERVER['HTTP_HOST'] . '/wp-admin/') . '&reauth=1'));
