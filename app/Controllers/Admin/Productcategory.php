<?php

namespace App\Controllers\Admin;

// Libraries
use App\Libraries\TaxonomyType;

//
class Productcategory extends Terms
{
    protected $taxonomy = TaxonomyType::PROD_CATS;
    protected $controller_slug = 'productcategory';

    public function __construct()
    {
        parent::__construct();
    }
}
