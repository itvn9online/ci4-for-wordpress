<?php
/*
 * The HTML Helper file contains functions that assist in working with HTML.
 */

namespace App\Helpers;

class HtmlTemplate
{
    // lấy nội dung file trong thư mục mặc định của Helpers sau đỏ render
    public static function html($file, $data = [], $meta = [])
    {
        return self::render(file_get_contents(__DIR__ . '/templates/' . $file, 1), $data, $meta);
    }

    public static function render($html, $data = [], $meta = [])
    {
        //print_r( $data );
        foreach ($data as $k => $v) {
            // sử dụng chung mẫu template với angular js
            $html = str_replace('{{' . $k . '}}', $v, $html);
        }
        foreach ($meta as $k => $v) {
            // sử dụng chung mẫu template với angular js
            $html = str_replace('{{' . $k . '}}', $v, $html);
        }
        return $html;
    }
}
