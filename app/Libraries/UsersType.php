<?php

namespace App\Libraries;

//
use App\Language\admin\AdminTranslate;

class UsersType
{
    //
    const GUEST_LEVEL = 0;
    const ADMIN_LEVEL = 1;

    //
    const ALL = 'Tài khoản';
    const GUEST = 'guest';
    const MEMBER = 'member';
    const AUTHOR = 'author';
    const MOD = 'mod';
    const ADMIN = 'admin';
    const SUB = 'subscribe';

    // user status
    const FOR_DEFAULT = '0'; // mặc định là hiển thị
    const NO_1H_LOGIN = '1'; // khóa 1 giờ
    const NO_24h_LOGIN = 24; // khóa 24 giờ
    const NO_WEEK_LOGIN = 168; // khóa 168 giờ = 1 tuần
    const NO_MONTH_LOGIN = 720; // khóa 720 giờ = 1 tháng
    const NO_LOGIN = -1; // tài khoản bị khóa chức năng đăng nhập (vĩnh viễn)

    // member_verified
    const VERIFING = 0;
    const VERIFIED = 1;

    // login_type
    const LOCAL = 'local';
    const FIREBASE = 'firebase';

    private static $arr = array(
        self::GUEST => AdminTranslate::GUEST,
        self::MEMBER => AdminTranslate::MEMBER,
        self::AUTHOR => AdminTranslate::AUTHOR,
        self::MOD => AdminTranslate::MOD,
        self::ADMIN => AdminTranslate::ADMIN,
        self::SUB => AdminTranslate::SUB,
    );

    public static function typeList($key = '')
    {
        if ($key == '') {
            return self::$arr;
        }
        if (isset(self::$arr[$key])) {
            return self::$arr[$key];
        }
        return '';
    }

    public static function statusList($key = '')
    {
        $arr = array(
            self::FOR_DEFAULT => 'Cho phép đăng nhập',
            self::NO_1H_LOGIN => 'KHÓA trong 1 giờ',
            self::NO_24h_LOGIN => 'KHÓA trong 24 giờ',
            self::NO_WEEK_LOGIN => 'KHÓA 01 tuần',
            self::NO_MONTH_LOGIN => 'KHÓA 01 tháng',
            self::NO_LOGIN => 'KHÓA vĩnh viễn',
        );
        if ($key == '') {
            return $arr;
        }
        if (isset($arr[$key])) {
            return $arr[$key];
        }
        return '';
    }

    // quyền truy cập vào controller theo từng nhóm thành viên
    public static function role($key)
    {
        // khách vãng lai và thành viên -> không có quyền gì ở đây cả
        if ($key == self::GUEST || $key == self::MEMBER) {
            return [];
        }

        // mặc định sẽ cho truy cập vào Dashboard
        $arr = [
            'Dashboard',
        ];

        // với tác giả -> cho thêm quyền liên quan đến bài viết, up ảnh
        if ($key == self::MOD || $key == self::AUTHOR) {
            $arr[] = 'Comments';
            $arr[] = 'Posts';
            $arr[] = 'Terms';
            $arr[] = 'Uploads';
        }

        // biên tập viên -> cho thêm quyền điều khiển thành viên
        if ($key == self::MOD) {
            $arr[] = 'Htmlmenus';
            $arr[] = 'Menus';
            $arr[] = 'Users';
        }

        // với admin thì không cần nhập gì cả -> full quyền

        // chuyển hết về chữ thường
        $result = [];
        foreach ($arr as $v) {
            $result[] = strtolower($v);
        }
        //print_r( $result );

        //
        return $result;
    }
}
