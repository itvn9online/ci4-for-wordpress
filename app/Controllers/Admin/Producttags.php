<?php

namespace App\Controllers\Admin;

// Libraries
use App\Libraries\TaxonomyType;

//
class Producttags extends Terms
{
    protected $taxonomy = TaxonomyType::PROD_TAGS;
    protected $controller_slug = 'producttags';

    public function __construct()
    {
        parent::__construct();
    }
}
