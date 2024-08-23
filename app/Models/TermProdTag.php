<?php

namespace App\Models;

// Libraries
// use App\Libraries\LanguageCost;
// use App\Libraries\DeletedStatus;
use App\Libraries\TaxonomyType;
use App\Libraries\PostType;

//
class TermProdTag extends TermTag
{
    public $taxonomy = TaxonomyType::PROD_TAGS;
    public $post_type = PostType::PROD;

    public function __construct()
    {
        parent::__construct();
    }
}
