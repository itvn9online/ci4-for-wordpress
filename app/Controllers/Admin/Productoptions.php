<?php

namespace App\Controllers\Admin;

// Libraries
use App\Libraries\TaxonomyType;

//
class Productoptions extends Terms
{
    protected $taxonomy = TaxonomyType::PROD_OTPS;
    protected $controller_slug = 'productoptions';

    public function __construct()
    {
        parent::__construct();
    }
}
