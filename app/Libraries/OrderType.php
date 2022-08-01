<?php

namespace App\ Libraries;

//
//use App\ Language\ admin\ AdminTranslate;

//
class OrderType extends PostType {
    public function __construct() {
        //
    }

    public static function arrStatus() {
        return array(
            //self::PUBLICITY => 'Hiển thị',
            self::PRIVATELY => 'Đã thanh toán',
            self::PENDING => 'Mới',
            self::DRAFT => 'Hủy',
            self::DELETED => 'XÓA',
            //self::INHERIT => '',
        );
    }
}