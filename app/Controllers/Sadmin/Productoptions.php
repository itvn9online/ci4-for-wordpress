<?php

namespace App\Controllers\Sadmin;

// Libraries
use App\Libraries\TaxonomyType;

//
class Productoptions extends Terms
{
    protected $taxonomy = TaxonomyType::PROD_OTPS;

    public function __construct()
    {
        $this->controller_slug = TaxonomyType::controllerList(TaxonomyType::PROD_OTPS);

        //
        parent::__construct();
    }
}
