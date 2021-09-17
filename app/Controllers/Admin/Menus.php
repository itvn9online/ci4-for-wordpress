<?php
/*
 * Phần menu sử dụng chung controller với post -> thêm controller này mục đích duy nhất là để phân quyền điều khiển
 */

//require_once __DIR__ . '/Posts.php';
namespace App\ Controllers\ Admin;

//
class Menus extends Posts {
    public function __construct() {
        parent::__construct();

        // kiểm tra quyền truy cập của tài khoản hiện tại
        $this->check_permision( __CLASS__ );
    }
}