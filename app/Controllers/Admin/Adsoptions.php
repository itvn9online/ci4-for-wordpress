<?php

namespace App\Controllers\Admin;

// Libraries
use App\Libraries\TaxonomyType;

//
class Adsoptions extends Terms
{
    protected $taxonomy = TaxonomyType::ADS;

    public function __construct()
    {
        $this->controller_slug = TaxonomyType::controllerList(TaxonomyType::ADS);

        //
        parent::__construct();
    }
}
