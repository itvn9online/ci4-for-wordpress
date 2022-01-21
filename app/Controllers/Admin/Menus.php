<?php
namespace App\ Controllers\ Admin;

// Libraries
use App\ Libraries\ PostType;

//
class Menus extends Posts {
    protected $post_type = PostType::MENU;
    protected $controller_slug = 'menus';

    public function __construct() {
        parent::__construct();

        // kiểm tra quyền truy cập của tài khoản hiện tại
        $this->check_permision( __CLASS__ );
    }
}