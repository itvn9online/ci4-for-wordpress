<?php
namespace App\ Controllers;

class Ajax extends Layout {
    // chức năng này không cần nạp header
    public $preload_header = false;

    public function __construct() {
        parent::__construct();
    }

    // trả về URL hiện tại của website -> dùng để kiểm tra URL có https với www không
    public function check_ssl() {
        $test_url = DYNAMIC_BASE_URL;
        // bỏ https
        $test_url = str_replace( 'https://', 'http://', $test_url );
        // bỏ www.
        $test_url = str_replace( 'http://www.', 'http://', $test_url );
        // thêm lại www.
        $test_url = str_replace( 'http://', 'http://www.', $test_url );

        // rồi lấy url xem nó có tự redirect về url chuẩn mình mong muốn không
        die( json_encode( [
            'test_url' => $test_url,
            'base_url' => file_get_contents( $test_url . 'ajax/the_base_url', 1 )
        ] ) );
    }
    public function the_base_url() {
        echo DYNAMIC_BASE_URL;
    }
}