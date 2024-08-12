<?php

namespace App\Libraries;

class CommentType
{

    // post_type
    const CONTACT = 'contact';
    const COMMENT = 'comment';
    const REVIEW = 'review';

    //
    const PENDDING = '0';
    const APPROVED = '1';

    private static $arr = array(
        self::CONTACT => 'Contacts',
        self::COMMENT => 'Comments',
        self::REVIEW => 'Reviews',
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
