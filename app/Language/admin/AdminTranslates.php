<?php

/**
 * Trang chứa bản dịch mặc định cho phần admin
 * Một số chức năng có tính linh động, khách hàng có thể tùy ý đổi tên thành từ dễ nhớ hoặc đúng với lĩnh vực của website hơn
 **/

namespace App\Language\admin;

//
class AdminTranslates
{
    const POST = 'Posts'; // Bài viết
    const PROD = 'Product'; // Sản phẩm

    // bản dịch cho tên các nhóm tài khoản
    const GUEST = 'Guest'; // Khách vãng lai
    const MEMBER = 'Member'; // Thành viên
    const CUSTOMER = 'Customer';
    const AUTHOR = 'Author'; // Tác giả
    const MOD = 'Editor'; // Biên tập viên
    const ADMIN = 'Admin'; // Quản trị
    const SUB = 'Subscriber'; // Theo dõi
}
