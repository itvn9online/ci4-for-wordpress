<?php

namespace App\Libraries;

class MenuType
{

    const SHOW = '0';
    const HIDE = '1';

    // phân loại menu -> các tính năng na ná nhau thì dùng chung luôn
    const MENU = '0';
    const SLIDER = '1';

    private static $arr = array(
        self::MENU => 'Menu',
        self::SLIDER => 'Slider'
    );

    public static function typeList($key = '')
    {
        if ($key == '') {
            return self::$arr;
        }
        return self::$arr[$key];
    }

}