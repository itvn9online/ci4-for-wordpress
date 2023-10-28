<?php

namespace App\Libraries;

class DeletedStatus
{
    const FOR_DEFAULT = '0'; // mặc định là hiển thị
    const DELETED = '1'; // ẩn hỏi trang khách hàng -> admin vẫn thấy
    const REMOVED = -1; // ẩn hoàn toàn khỏi hệ thống admin

    const TERM_SHOW = '0';
    const TERM_HIDE = '1';

    // tham số sẽ thêm vào các thông số có tính unique khi xóa -> và bỏ đi lúc restore
    const FOR_TRASH = '___trash___';
}
