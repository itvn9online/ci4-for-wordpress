<?php

namespace App\Libraries;

class CommentType
{

    // post_type
    const CONTACT = 'contact';
    const COMMENT = 'comment';

    //
    const PENDDING = '0';
    const APPROVED = '1';

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
