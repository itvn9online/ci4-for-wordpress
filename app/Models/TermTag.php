<?php

namespace App\Models;

use App\Models\Traits\TermTagTrait;
use App\Libraries\TaxonomyType;
use App\Libraries\PostType;

//
class TermTag extends Term
{
    use TermTagTrait;

    public $taxonomy = TaxonomyType::TAGS;
    public $post_type = PostType::POST;
}
