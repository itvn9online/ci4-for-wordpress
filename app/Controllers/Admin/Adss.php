<?php
namespace App\Controllers\Admin;

// Libraries
use App\Libraries\PostType;
use App\Libraries\TaxonomyType;

//
class Adss extends Posts
{
    protected $post_type = PostType::ADS;
    protected $taxonomy = TaxonomyType::ADS;
    protected $tags = '';

    protected $controller_slug = 'adss';

    //
    public function __construct()
    {
        parent::__construct();
    }
}