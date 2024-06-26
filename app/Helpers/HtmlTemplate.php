<?php
/*
 * The HTML Helper file contains functions that assist in working with HTML.
 */

namespace App\Helpers;

class HtmlTemplate
{
    // lấy nội dung file trong thư mục mặc định của Helpers sau đỏ render
    public static function html($file, $data = [], $meta = [], $tmp_begin = '{{', $tmp_end = '}}')
    {
        return self::render(file_get_contents(__DIR__ . '/templates/' . $file, 1), $data, $meta, $tmp_begin, $tmp_end);
    }

    public static function render($html, $data = [], $meta = [], $tmp_begin = '{{', $tmp_end = '}}')
    {
        //print_r($data);
        foreach ($data as $k => $v) {
            if (gettype($v) == 'array') {
                continue;
            }
            if ($v == null) {
                $v = '';
            }
            //echo 'render key:' . PHP_EOL;
            //print_r($k);
            //echo PHP_EOL;
            //echo 'render value:' . PHP_EOL;
            //print_r($v);
            //echo PHP_EOL;
            // sử dụng chung mẫu template với angular js
            $html = str_replace($tmp_begin . $k . $tmp_end, $v, $html);
        }
        foreach ($meta as $k => $v) {
            // sử dụng chung mẫu template với angular js
            $html = str_replace($tmp_begin . $k . $tmp_end, $v, $html);
        }
        return $html;
    }
}
