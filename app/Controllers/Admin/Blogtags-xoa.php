<?php
namespace App\Controllers\Admin;

// Libraries
use App\Libraries\TaxonomyType;

//
class Blogtags extends Terms
{
    protected $taxonomy = TaxonomyType::BLOG_TAGS;
    protected $controller_slug = 'blogtags';

    public function __construct()
    {
        parent::__construct();
    }
}