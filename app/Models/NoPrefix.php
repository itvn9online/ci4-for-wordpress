<?php
namespace App\ Models;

// Trong một số trường hợp càn thay đổi prefix table khi truy vấn -> lúc đấy sẽ gọi thông qua model này
class NoPrefix extends Csdl {
    // tùy chỉnh prefix theo ý muốn
    protected $custom_prefix = '';

    public function __construct() {
        parent::__construct();

        // thiết lập prefix mới, dùng cho 1 số code móc nối nhiều database khác nhau
        $this->db->setPrefix( $this->custom_prefix );
    }
}