<?php
namespace App\Controllers\Admin;

// Libraries
use App\Libraries\TaxonomyType;

//
class Postoptions extends Terms
{
    protected $taxonomy = TaxonomyType::OPTIONS;
    protected $controller_slug = 'postoptions';

    public function __construct()
    {
        parent::__construct();
    }
}