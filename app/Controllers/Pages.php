<?php

namespace App\Controllers;

// Libraries
use App\Libraries\PostType;
use App\Libraries\LanguageCost;

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
        $in_cache = __FUNCTION__ . LanguageCost::lang_key() . '-' . $slug;
        $data = $this->base_model->scache($in_cache);
        if ($data === null) {
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
        return $this->page404('ERROR ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! Cannot be determined page...');
    }
}
