<?php

namespace App\Controllers;

//
class P extends Home
{
    public function __construct()
    {
        parent::__construct();
    }

    /*
    * Kiểm tra xem post type có được đăng ký hiển thị thông qua function register_post_type không
    */
    public function custom_post_type($post_type, $id, $slug = '')
    {
        global $arr_custom_post_type;
        //print_r($arr_custom_post_type);

        //echo $post_type . '<br>' . PHP_EOL;
        //echo $id . '<br>' . PHP_EOL;
        //echo $slug . '<br>' . PHP_EOL;

        // với custom post_type -> kiểm tra xem post_type này phải được đăng ký thì mới hiển thị ra
        if (!isset($arr_custom_post_type[$post_type])) {
            return $this->page404('ERROR ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! Bài viết này chưa được đăng ký hiển thị...');
        }
        // hoặc custom post_type có đăng ký nhưng không hiển thị với người khác ngoại trừ admin
        else if (isset($arr_custom_post_type[$post_type]['public']) && $arr_custom_post_type[$post_type]['public'] != 'on') {
            return $this->page404('ERROR ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! Bạn không được phép xem thông tin bài viết này...');
        }

        //
        return $this->showPostDetails($id, $post_type, $slug);
    }
}
