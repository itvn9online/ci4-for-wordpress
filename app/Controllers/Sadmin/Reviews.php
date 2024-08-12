<?php

namespace App\Controllers\Sadmin;

// Libraries
use App\Libraries\CommentType;

//
class Reviews extends Comments
{
    protected $comment_type = CommentType::REVIEW;
    protected $controller_slug = 'reviews';

    public function __construct()
    {
        parent::__construct();
    }
}
