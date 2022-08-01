<?php
namespace App\ Controllers\ Admin;

// Libraries
use App\ Libraries\ OrderType;

//
class Orders extends Posts {
    protected $post_type = OrderType::ORDER;

    // tham số dùng để thay đổi bảng cần gọi dữ liệu
    public $table = 'orders';
    // tham số dùng để thay đổi URL cho controller nếu muốn
    protected $controller_slug = 'orders';
    // tham số dùng để đổi file view khi add hoặc edit bài viết nếu muốn
    protected $add_view_path = 'orders';
    // tham số dùng để đổi file view khi xem danh sách bài viết nếu muốn
    protected $list_view_path = 'orders';

    //
    public function __construct() {
        parent::__construct();

        //
        $this->post_arr_status = OrderType::arrStatus();
    }
}