<?php

namespace App\Libraries;

class LanguageCost
{

    //const VIETNAMESE = 'vn';
    //const ENGLISH = 'en';
    //const JAPANESE = 'jp';

    const CK_LANG_NAME = 'show_language';

    // hiển thị ngôn ngữ theo segment
    private static $seg_lang = NULL;
    private static $langs = NULL;
    private static $items = SITE_LANGUAGE_SUPPORT;

    public static function get_list($textlang = null)
    {
        return self::$items;
    }

    public static function typeList($key = '')
    {
        // tạo mảng chứa danh sách các ngôn ngữ được hỗ trợ
        if (self::$langs == NULL) {
            $arr = [];
            foreach (self::$items as $values) {
                $arr[$values["value"]] = $values["text"];
            }
            self::$langs = $arr;
        }
        //print_r($arr);
        //die(__CLASS__ . ':' . __LINE__);

        //
        if ($key == '') {
            return self::$langs;
        }
        return self::$langs[$key];
    }

    private static function unparse_url($parsed_url)
    {
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass = isset($parsed_url['pass']) ? ':' . $parsed_url['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
        return "$scheme$user$pass$host$port$path$query$fragment";
    }

    // segment lang -> hiển thị ngôn ngữ theo segment
    public static function segLang($lang)
    {
        self::$seg_lang = $lang;
    }

    public static function saveLang($lang)
    {
        //echo $lang . '<br>' . PHP_EOL;

        //
        $arr = self::typeList();
        if (isset($arr[$lang])) {
            setcookie(self::CK_LANG_NAME, $lang, time() + (86400 * 30), "/"); // 86400 = 1 day * 30 = 1 month

            // nếu có chỉ định redirect tới khu nào đó
            if (isset($_GET['redirect_to'])) {
                $redirect_to = $_GET['redirect_to'];
                echo $redirect_to . '<br>' . PHP_EOL;
                header('location:' . $redirect_to);
                die(__CLASS__ . ':' . __LINE__);
            }

            // mặc địh thì redirect về trang chủ
            //print_r( $_SERVER );
            $full_url = parse_url(DYNAMIC_BASE_URL . ltrim($_SERVER['REQUEST_URI'], '/'));
            //print_r( $full_url );
            if (isset($full_url['query'])) {
                $full_url['query'] = '';
                unset($full_url['query']);
            }
            //print_r( $full_url );
            $full_url = self::unparse_url($full_url);
            //print_r( $full_url );

            //
            //print_r( $_GET );
            $query = [];
            foreach ($_GET as $k => $v) {
                if ($k == 'set_lang') {
                    continue;
                }
                $query[] = $k . '=' . $v;
            }
            //print_r( $query );
            if (!empty($query)) {
                $full_url .= '?' . implode('&', $query);
            }
            //print_r( $full_url );
            header('location:' . $full_url);

            //
            return $lang;
        }
    }

    public static function setLang()
    {
        if (isset($_GET['set_lang'])) {
            self::saveLang(trim($_GET['set_lang']));
        }

        //
        return self::lang_key();
    }

    public static function lang_key()
    {
        if (self::$seg_lang !== NULL) {
            $lang = self::$seg_lang;
            //echo $lang;
        } else if (isset($_COOKIE[self::CK_LANG_NAME])) {
            $lang = $_COOKIE[self::CK_LANG_NAME];
        } else {
            //$lang = self::VIETNAMESE;
            $lang = self::default_lang();
        }
        return $lang;
    }

    public static function default_lang()
    {
        //return self::VIETNAMESE;
        //return self::$items[0]['value'];
        return SITE_LANGUAGE_DEFAULT;
    }
}
