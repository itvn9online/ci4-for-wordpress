<?php
namespace App\Controllers\Admin;

// Libraries
use App\Libraries\TaxonomyType;

//
class Adsoptions extends Terms
{
    protected $taxonomy = TaxonomyType::ADS;
    protected $controller_slug = 'adsoptions';

    public function __construct()
    {
        parent::__construct();
    }
}