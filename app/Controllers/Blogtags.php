<?php

namespace App\Controllers;

// Libraries
use App\Libraries\TaxonomyType;

//
class Blogtags extends Blogs
{
    protected $taxonomy = TaxonomyType::BLOG_TAGS;

    public function __construct()
    {
        parent::__construct();
    }
}
