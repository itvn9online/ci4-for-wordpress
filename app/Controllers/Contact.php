<?php

namespace App\Controllers;

// Libraries
use App\Libraries\CommentType;

class Contact extends ContactBase
{
    protected $comment_type = CommentType::CONTACT;

    public function __construct()
    {
        parent::__construct();
    }
}
