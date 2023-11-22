<?php

/**
 * File này chỉ để giả lập wordpress, cho mấy thanh niên thích quậy phá nhìn vào ban đầu tưởng là wordpress -> dể chọc phá theo hướng đấy -> faild
 **/

//
//ini_set('display_errors', 1);
//error_reporting(E_ALL);

// Nếu không phải method post
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    //print_r($_GET);

    //
    if (isset($_GET['rsd'])) {
        header("Content-type: text/xml");

        //
        //echo dirname(__DIR__) . '/app/Views/xmlrpc_layout.xml';
        $xml_content = file_get_contents(dirname(__DIR__) . '/app/Views/xmlrpc_layout.xml');
        foreach ([
            'host_name' => $_SERVER['HTTP_HOST'],
        ] as $k => $v) {
            $xml_content = str_replace('{' . $k . '}', $v, $xml_content);
        }

        //
        die($xml_content);
    } else {
        http_response_code(400);
        header('Content-type: application/json; charset=utf-8');

        // kêu nó sử dụng post
        die(json_encode([
            'code' => __LINE__,
            'msg' => 'XML-RPC server accepts POST requests only.'
        ]));
    }
} else {
    http_response_code(400);
    header('Content-type: application/json; charset=utf-8');

    // nó chuyển qua post rồi thì kệ nó thôi =))
    die(json_encode([
        'code' => __LINE__,
        'msg' => 'parse error. not well formed'
    ]));
}
