<?php

namespace App\Models;

//
use App\Libraries\PostType;

class Htmlmenu extends Menu
{
    protected $post_type = PostType::HTML_MENU;

    public function __construct()
    {
        parent::__construct();
    }
}