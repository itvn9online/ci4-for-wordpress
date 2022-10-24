<?php
namespace App\Controllers\Admin;

// Libraries
use App\Libraries\TaxonomyType;

//
class Blogcategory extends Terms
{
    protected $taxonomy = TaxonomyType::BLOGS;
    protected $controller_slug = 'blogcategory';

    public function __construct()
    {
        parent::__construct();
    }
}