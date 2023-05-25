<?php

namespace App\Controllers\Admin;

// Libraries
use App\Libraries\PostType;
use App\Libraries\TaxonomyType;

//
class Products extends Posts
{
    protected $post_type = PostType::PROD;
    protected $taxonomy = TaxonomyType::PROD_CATS;
    protected $options = TaxonomyType::PROD_OTPS;
    protected $tags = TaxonomyType::PROD_TAGS;
    protected $controller_slug = 'products';

    //
    public function __construct()
    {
        parent::__construct();
    }
}
