<?php
namespace App\Controllers\Admin;

// Libraries
use App\Libraries\TaxonomyType;

//
class Tags extends Terms
{
    protected $taxonomy = TaxonomyType::TAGS;
    protected $controller_slug = 'tags';

    public function __construct()
    {
        parent::__construct();
    }
}