<?php
/*
 * Trang chứa bản dịch mặc định cho website
 * Một số chức năng có tính linh động,khách hàng có thể tùy ý đổi tên thành từ dễ nhớ hoặc đúng với lĩnh vực của website hơn
 */

namespace App\Language;

class Translates
{
    const PASSWORD = 'Mật khẩu';
    const USERNAME = 'Tài khoản';
    const FULLNAME = 'Họ và tên';
    const TITLE = 'Tiêu đề';
    const CONTENT = 'Nội dung';

    const REQUIRED = 'Không xác định được {field} bạn đã nhập!';
    const MIN_LENGTH = '{field} quá ngắn!';
    const MAX_LENGTH = '{field} quá dài!';
    const VALID_EMAIL = '{field} không đúng định dạng được hỗ trợ!';
}
