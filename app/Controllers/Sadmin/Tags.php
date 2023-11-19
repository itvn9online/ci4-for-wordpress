<?php

namespace App\Controllers\Sadmin;

// Libraries
use App\Libraries\TaxonomyType;

//
class Tags extends Terms
{
    protected $taxonomy = TaxonomyType::TAGS;

    public function __construct()
    {
        $this->controller_slug = TaxonomyType::controllerList(TaxonomyType::TAGS);

        //
        parent::__construct();
    }
}
