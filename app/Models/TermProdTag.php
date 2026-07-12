<?php

namespace App\Models;

use App\Models\Traits\TermTagTrait;
use App\Libraries\TaxonomyType;
use App\Libraries\PostType;

//
class TermProdTag extends Term
{
    use TermTagTrait;

    public $taxonomy = TaxonomyType::PROD_TAGS;
    public $post_type = PostType::PROD;
}
