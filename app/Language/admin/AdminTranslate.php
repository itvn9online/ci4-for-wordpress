<?php
/*
 * Trang chứa bản dịch cho phần admin
 * Một số chức năng có tính linh động, khách hàng có thể tùy ý đổi tên thành từ dễ nhớ hoặc đúng với lĩnh vực của website hơn
 */
namespace App\Language\admin;

//
class AdminTranslate
{
    const POST = 'Bài viết';

    const USER_TRANS = [
        'guest' => 'Khách vãng lai',
        'member' => 'Thành viên',
        'author' => 'Tác giả',
        'mod' => 'Biên tập viên',
        'admin' => 'Quản trị',
    ];
}