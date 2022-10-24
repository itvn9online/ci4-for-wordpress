<?php
namespace App\Controllers\Admin;

// Libraries
use App\Libraries\PostType;

//
class Pages extends Posts
{
    protected $post_type = PostType::PAGE;
    protected $controller_slug = 'pages';

    //
    public function __construct()
    {
        parent::__construct();
    }
}