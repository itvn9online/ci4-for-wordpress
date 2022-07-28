<?php
namespace App\ Controllers\ Admin;

// Libraries
use App\ Libraries\ PostType;

//
class Oders extends Posts {
    protected $post_type = PostType::ORDER;

    // tham số dùng để thay đổi URL cho controller nếu muốn
    protected $controller_slug = 'oders';
    // tham số dùng để đổi file view khi add hoặc edit bài viết nếu muốn
    protected $add_edit_view = 'oders';
    // tham số dùng để đổi file view khi xem danh sách bài viết nếu muốn
    protected $add_list_view = 'oders';

    //
    public function __construct() {
        parent::__construct();
    }
}