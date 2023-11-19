<?php

namespace App\Controllers\Sadmin;

// Libraries
use App\Libraries\TaxonomyType;

//
class Productcategory extends Terms
{
    protected $taxonomy = TaxonomyType::PROD_CATS;

    public function __construct()
    {
        $this->controller_slug = TaxonomyType::controllerList(TaxonomyType::PROD_CATS);

        //
        parent::__construct();
    }
}
