<?php

namespace App\ Libraries;

class UsersType {

    const GUEST = 'guest';
    const MEMBER = 'member';
    const AUTHOR = 'author';
    const MOD = 'mod';
    const ADMIN = 'admin';

    private static $arr = array(
        self::GUEST => 'Khách vãng lai',
        self::MEMBER => 'Thành viên',
        self::AUTHOR => 'Tác giả',
        self::MOD => 'Biên tập viên',
        self::ADMIN => 'Quản trị',
    );

    public static function list( $key = '' ) {
        if ( $key == '' ) {
            return self::$arr;
        }
        if ( isset( self::$arr[ $key ] ) ) {
            return self::$arr[ $key ];
        }
        return '';
    }

    // quyền truy cập vào controller theo từng nhóm thành viên
    public static function role( $key ) {
        // khách vãng lai và thành viên -> không có quyền gì ở đây cả
        if ( $key == self::GUEST || $key == self::MEMBER ) {
            return [];
        }

        // mặc định sẽ cho truy cập vào Dashboard
        $arr = [
            'Dashboard',
        ];

        // với tác giả -> cho thêm quyền liên quan đến bài viết, up ảnh
        if ( $key == self::MOD || $key == self::AUTHOR ) {
            $arr[] = 'Comments';
            $arr[] = 'Posts';
            $arr[] = 'Terms';
            $arr[] = 'Uploads';
        }

        // biên tập viên -> cho thêm quyền điều khiển thành viên
        if ( $key == self::MOD ) {
            $arr[] = 'Menus';
            $arr[] = 'Users';
        }

        // với admin thì không cần nhập gì cả -> full quyền

        // chuyển hết về chữ thường
        $result = [];
        foreach ( $arr as $v ) {
            $result[] = strtolower( $v );
        }
        //print_r( $result );

        //
        return $result;
    }
}