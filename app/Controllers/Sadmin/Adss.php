<?php

namespace App\Controllers\Sadmin;

// Libraries
use App\Libraries\PostType;
use App\Libraries\TaxonomyType;

//
class Adss extends Posts
{
    protected $post_type = PostType::ADS;
    protected $taxonomy = TaxonomyType::ADS;
    protected $tags = '';

    //
    public function __construct()
    {
        $this->controller_slug = PostType::controllerList(PostType::ADS);

        //
        parent::__construct();
    }
}
