<?php

namespace App\Libraries;

class CommentType
{

    // post_type
    const CONTACT = 'contact';
    const COMMENT = 'comment';

    private static $arr = array(
        self::CONTACT => 'Liên hệ',
        self::COMMENT => 'Bình luận',
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
}