<?php

/**
 * File này chỉ để giả lập wordpress, cho mấy thanh niên thích quậy phá nhìn vào ban đầu tưởng là wordpress -> dể chọc phá theo hướng đấy -> faild
 **/

//
header('Content-type: application/json; charset=utf-8');

// Nếu không phải method post
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    // kêu nó sử dụng post
    die(json_encode([
        'code' => __LINE__,
        'msg' => 'cron server accepts POST requests only.'
    ]));
} else {
    // nó chuyển qua post rồi thì kệ nó thôi =))
    die(json_encode([
        'code' => __LINE__,
        'msg' => 'parse error. not well formed'
    ]));
}
