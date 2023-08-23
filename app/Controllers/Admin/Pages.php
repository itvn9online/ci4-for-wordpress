<?php

namespace App\Controllers\Admin;

// Libraries
use App\Libraries\PostType;

//
class Pages extends Posts
{
    protected $post_type = PostType::PAGE;

    //
    public function __construct()
    {
        $this->controller_slug = PostType::controllerList(PostType::PAGE);

        //
        parent::__construct();
    }
}
