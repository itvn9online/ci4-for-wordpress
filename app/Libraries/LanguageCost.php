<?php

namespace App\Libraries;

class LanguageCost
{

    const VIETNAMESE = 'vn';
    const ENGLISH = 'en';
    //const JAPANESE = 'jp';

    const CK_LANG_NAME = 'show_language';

    private static $items = array(
        self::VIETNAMESE => array(
            "value" => self::VIETNAMESE,
            "text_en" => "Vietnamese",
            "text_vn" => "Tiếng Việt",
            "css_class" => "text-muted"
        ),
        self::ENGLISH => array(
            "value" => self::ENGLISH,
            "text_en" => "English",
            "text_vn" => "Tiếng Anh",
            "css_class" => "text-success"
        ),
    );

    public static function get_list($textlang = null)
    {
        $returnslist = array();
        if (empty($textlang) || $textlang == null)
            $text = "text_vn";
        else
            $text = "text_en";

        foreach (self::$items as $key => $values) {
            $returnslist[$key]["value"] = $values["value"];
            $returnslist[$key]["text"] = $values[$text];
            $returnslist[$key]["css_class"] = $values['css_class'];
        }

        return $returnslist;
    }

    private static $arr = array(
        self::VIETNAMESE => 'Tiếng Việt',
        self::ENGLISH => 'English'
    );

    public static function typeList($key = '')
    {
        if ($key == '') {
            return self::$arr;
        }
        return self::$arr[$key];
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

    public static function set_lang()
    {
        if (isset($_GET['set_lang'])) {
            $lang = trim($_GET['set_lang']);
            //echo $lang . '<br>' . "\n";
            if (isset(self::$arr[$lang])) {
                setcookie(self::CK_LANG_NAME, $lang, time() + (86400 * 30), "/"); // 86400 = 1 day * 30 = 1 month

                // nếu có chỉ định redirect tới khu nào đó
                if (isset($_GET['redirect_to'])) {
                    $redirect_to = $_GET['redirect_to'];
                    echo $redirect_to . '<br>' . PHP_EOL;
                    header('location:' . $redirect_to);
                    die(basename(__FILE__) . ':' . __LINE__);
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

        //
        return self::lang_key();
    }

    public static function lang_key()
    {
        if (isset($_COOKIE[self::CK_LANG_NAME])) {
            $lang = $_COOKIE[self::CK_LANG_NAME];
        } else {
            $lang = self::VIETNAMESE;
        }
        return $lang;
    }

    public static function default_lang()
    {
        return self::VIETNAMESE;
    }

}