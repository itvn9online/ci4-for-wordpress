<?php

namespace App\Controllers\Admin;

// Libraries
use App\Libraries\TaxonomyType;

//
class Producttags extends Terms
{
    protected $taxonomy = TaxonomyType::PROD_TAGS;

    public function __construct()
    {
        $this->controller_slug = TaxonomyType::controllerList(TaxonomyType::PROD_TAGS);

        //
        parent::__construct();
    }
}
