<?php
/*
 * Trang chứa bản dịch mặc định cho website
 * Một số chức năng có tính linh động,khách hàng có thể tùy ý đổi tên thành từ dễ nhớ hoặc đúng với lĩnh vực của website hơn
 */

namespace App\Language;

class Translates
{
    const PASSWORD = 'Password';
    const USERNAME = 'Account';
    const FULLNAME = 'Full name';
    const TITLE = 'Title';
    const CONTENT = 'Content';

    const REQUIRED = 'Cannot be determined {field} you entered!';
    const MIN_LENGTH = '{field} too short!';
    const MAX_LENGTH = '{field} too long!';
    const VALID_EMAIL = '{field} format is not supported!';
}
