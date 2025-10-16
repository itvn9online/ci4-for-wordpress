<?php

namespace App\Controllers;

// Libraries
use App\Libraries\DeletedStatus;
use App\Libraries\TaxonomyType;
use App\Libraries\PostType;

//
class Category extends Home
{
    public function __construct()
    {
        parent::__construct();
    }

    public function category_list($slug, $set_page = '', $page_num = 1)
    {
        if ($slug == '') {
            die('404 slug error!');
        }
        //echo $slug . '<br>' . "\n";
        //echo $set_page . '<br>' . "\n";
        //echo $page_num . '<br>' . "\n";

        //
        $data = $this->term_model->get_taxonomy(array(
            // các kiểu điều kiện where
            'slug' => $slug,
            'is_deleted' => DeletedStatus::FOR_DEFAULT,
            'lang_key' => $this->lang_key,
            'taxonomy' => TaxonomyType::POSTS
        ));

        //
        if (!empty($data)) {
            return $this->category($data, PostType::POST, TaxonomyType::POSTS, 'category_view', [
                'page_num' => $page_num,
            ]);
        }

        //
        return $this->page404('ERROR ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! Cannot be determined post category...');
    }
}
