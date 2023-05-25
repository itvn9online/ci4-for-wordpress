<?php
/*
 * Trang chứa bản dịch mặc định cho phần admin
 * Một số chức năng có tính linh động, khách hàng có thể tùy ý đổi tên thành từ dễ nhớ hoặc đúng với lĩnh vực của website hơn
 */

namespace App\Language\admin;

//
class AdminTranslates
{
    const POST = 'Bài viết';
    const PROD = 'Sản phẩm';

    // bản dịch cho tên các nhóm tài khoản
    const GUEST = 'Khách vãng lai';
    const MEMBER = 'Thành viên';
    const AUTHOR = 'Tác giả';
    const MOD = 'Biên tập viên';
    const ADMIN = 'Quản trị';
    const SUB = 'Theo dõi';
}
