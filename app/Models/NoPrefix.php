<?php
namespace App\ Models;

// Trong một số trường hợp sẽ không sử dụng đến prefix cho table khi truy vấn -> lúc đấy sẽ gọi thông qua model này
class NoPrefix extends Csdl {
    public function __construct() {
        parent::__construct();

        // bỏ prefix đi -> vì code móc nối tới không dùng prefix
        $this->db->setPrefix( '' );
    }
}