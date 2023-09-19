<?php

namespace App\Controllers;

// Libraries
use App\Libraries\PostType;

//
class Searchs extends Search
{
    // tìm kiếm sản phẩm
    protected $post_type = PostType::PROD;
    protected $base_slug = 'searchs';

    public function __construct()
    {
        $this->post_per_page = $this->base_model->get_config($this->getconfig, 'eb_products_per_page', 10);

        //
        parent::__construct();
    }
}
