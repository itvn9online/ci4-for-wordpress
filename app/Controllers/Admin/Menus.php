<?php
namespace App\Controllers\Admin;

// Libraries
use App\Libraries\PostType;

//
class Menus extends Posts
{
    protected $post_type = PostType::MENU;

    // tham số dùng để thay đổi URL cho controller nếu muốn
    protected $controller_slug = 'menus';
    // tham số dùng để đổi file view khi add hoặc edit bài viết nếu muốn
    protected $add_view_path = 'menus';
    // tham số dùng để đổi file view khi xem danh sách bài viết nếu muốn
    //protected $list_view_path = 'menus';
    protected $list_table_path = 'menus';

    //
    public function __construct()
    {
        parent::__construct();

        // kiểm tra quyền truy cập của tài khoản hiện tại
        $this->check_permision(__CLASS__);
    }
}