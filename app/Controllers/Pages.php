<?php

namespace App\Controllers;

// Libraries
use App\Libraries\PostType;

//
class Pages extends Home
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_page($slug)
    {
        if ($slug == '') {
            die('404 slug error!');
        }
        //echo $slug . '<br>' . PHP_EOL;

        //
        $in_cache = __FUNCTION__ . '-' . $slug;
        $data = $this->base_model->scache($in_cache);
        if ($data === NULL) {
            $data = $this->post_model->select_public_post(0, [
                'post_name' => $slug,
                'post_type' => PostType::PAGE,
            ]);

            //
            $this->base_model->scache($in_cache, $data, 300);
        }

        //
        if (!empty($data)) {
            //print_r( $data );
            return $this->pageDetail($data);
        }

        //
        return $this->page404('ERROR ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! Không xác định được trang tĩnh...');
    }
}
