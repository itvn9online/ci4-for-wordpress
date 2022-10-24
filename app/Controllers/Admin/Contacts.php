<?php
namespace App\Controllers\Admin;

// Libraries
use App\Libraries\CommentType;

//
class Contacts extends Comments
{
    protected $comment_type = CommentType::CONTACT;
    protected $controller_slug = 'contacts';

    public function __construct()
    {
        parent::__construct();
    }
}