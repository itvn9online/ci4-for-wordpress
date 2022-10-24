<?php
namespace App\Controllers\Admin;

// Libraries
use App\Libraries\PostType;
use App\Libraries\TaxonomyType;

//
class Blogs extends Posts
{
    protected $post_type = PostType::BLOG;
    protected $taxonomy = TaxonomyType::BLOGS;
    protected $tags = TaxonomyType::BLOG_TAGS;
    protected $controller_slug = 'blogs';

    //
    public function __construct()
    {
        parent::__construct();
    }
}