<?php

namespace App\Controllers;

// Libraries
use App\Libraries\TaxonomyType;

//
class Producttags extends Products
{
    protected $taxonomy = TaxonomyType::PROD_TAGS;

    public function __construct()
    {
        parent::__construct();
    }
}
