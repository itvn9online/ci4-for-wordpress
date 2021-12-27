<?php
namespace App\ Controllers\ Admin;

//
class Dev extends Admin {
    public function __construct() {
        parent::__construct();

        // kiểm tra quyền truy cập của tài khoản hiện tại
        $this->check_permision( __CLASS__ );
    }

    public function index() {
        return $this->server_info();
    }

    public function server_info() {
        $this->teamplate_admin[ 'content' ] = view( 'admin/dev/server_info', array(
            'all_cookie' => $_COOKIE,
            'all_session' => $_SESSION,
            'data' => $_SERVER,
        ) );
        return view( 'admin/admin_teamplate', $this->teamplate_admin );
    }
}