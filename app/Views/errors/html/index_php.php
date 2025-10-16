<?php

/**
 * Thi thoảng có những URL nó tự chèn vào /index.php/ nên gây lỗi 404
 * File này sẽ thực hiện xóa nó đi và redirect lại xem có ổn không
 */
function fixed_url_index_php()
{
    // nếu đã thực hiện redirect trước đó rồi thì thôi
    if (isset($_GET['redirect_to'])) {
        return false;
    }

    // print_r($_SERVER['REQUEST_URI']);
    $base_uri = explode('?', $_SERVER['REQUEST_URI'])[0];
    // echo $base_uri . '<br>' . "\n";
    if (strpos($base_uri, '/index.php/') === false) {
        // echo $base_uri . '<br>' . "\n";
        return false;
    }
    $base_uri = str_replace('/index.php/', '/', $base_uri);
    // print_r($base_uri);

    // 
    $redirect_to = 'https://' . $_SERVER['HTTP_HOST'] . $base_uri . '?redirect_to=0';
    // echo $redirect_to . '<br>' . "\n";
    // die(__FILE__ . ':' . __LINE__);

    // 
    die(header('Location: ' . $redirect_to, true, 301));
}
fixed_url_index_php();
