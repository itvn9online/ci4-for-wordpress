<?php

/**
 * File này dùng để thực hiện xóa file app/Config/DynamicConstants.php nếu quá trình cập nhật constants động gây ra lỗi cho website
 */

// 
// session_start();
// echo session_id();

//
// print_r($_SERVER);

//
if (!isset($_GET['token'])) {
    die('Token not found!');
} else if (!isset($_GET['code'])) {
    die('Code not found!');
}

//
$file_path = dirname(__DIR__) . '/app/Config/DynamicConstants.php';

//
if (!is_file($file_path)) {
    die('File not found!');
}

//
if (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === false) {
    die('Bad request!');
}

//
$token = trim($_GET['token']);
if (empty($token)) {
    die('Token is EMPTY!');
} else if (strlen($token) < 16) {
    die('Token too short!');
}
// echo strlen($token) . '<br>' . "\n";

//
$code = trim($_GET['code']);
if (empty($code)) {
    die('Code is EMPTY!');
} else if (strlen($code) < 16) {
    die('Code too short!');
}
// echo strlen($code) . '<br>' . "\n";

// 
$file_content = file_get_contents($file_path);

// so khớp token với nội dung file -> nếu có thì mới cho xóa
if (strpos($file_content, $token) === false) {
    die('Token mismatch!');
}
// so khớp code với nội dung file -> nếu có thì mới cho xóa
else if (strpos($file_content, $code) === false) {
    die('Code mismatch!');
}

//
echo $file_path . '<br>' . "\n";
unlink($file_path);
