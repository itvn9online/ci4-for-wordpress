<?php

namespace App\Controllers;

// Libraries
use App\Libraries\TaxonomyType;

//
class C extends Home
{
    public function __construct()
    {
        parent::__construct();
    }

    public function custom_taxonomy($taxonomy_type, $id, $slug = '', $page_name = '', $page_num = 1)
    {
        // chỉ kiểm tra custom taxonomy
        if (!in_array($taxonomy_type, [
            TaxonomyType::TAGS,
            //TaxonomyType::OPTIONS,
            TaxonomyType::PROD_OTPS,
            //TaxonomyType::BLOG_TAGS,
            TaxonomyType::PROD_TAGS,
        ])) {
            global $arr_custom_taxonomy;
            //print_r($arr_custom_taxonomy);

            //echo $taxonomy_type . '<br>' . PHP_EOL;
            //echo $id . '<br>' . PHP_EOL;
            //echo $slug . '<br>' . PHP_EOL;
            //echo $page_num . '<br>' . PHP_EOL;

            // với custom taxonomy -> kiểm tra xem taxonomy này phải được đăng ký thì mới hiển thị ra
            if (!isset($arr_custom_taxonomy[$taxonomy_type])) {
                return $this->page404('ERROR ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! Danh mục này chưa được đăng ký hiển thị...');
            }
            // hoặc custom taxonomy có đăng ký nhưng không hiển thị với người khác ngoại trừ admin
            else if (isset($arr_custom_taxonomy[$taxonomy_type]['public']) && $arr_custom_taxonomy[$taxonomy_type]['public'] != 'on') {
                return $this->page404('ERROR ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! Bạn không được phép xem thông tin danh mục này...');
            }
        }

        //
        return $this->showCategory($id, $taxonomy_type, $page_num, $slug);
    }
}
