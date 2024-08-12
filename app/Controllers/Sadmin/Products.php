<?php

namespace App\Controllers\Sadmin;

// Libraries
use App\Libraries\PostType;
use App\Libraries\TaxonomyType;
use App\Libraries\CommentType;

//
class Products extends Posts
{
    protected $post_type = PostType::PROD;
    protected $taxonomy = TaxonomyType::PROD_CATS;
    protected $options = TaxonomyType::PROD_OTPS;
    protected $tags = TaxonomyType::PROD_TAGS;
    protected $comment_type = CommentType::REVIEW;

    //
    public function __construct()
    {
        $this->controller_slug = PostType::controllerList(PostType::PROD);

        //
        parent::__construct();
    }
}
