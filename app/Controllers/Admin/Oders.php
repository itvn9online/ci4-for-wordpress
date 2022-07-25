<?php
namespace App\ Controllers\ Admin;

// Libraries
use App\ Libraries\ PostType;

//
class Oders extends Posts {
    protected $post_type = PostType::ORDER;
    protected $controller_slug = 'oders';

    //
    public function __construct() {
        parent::__construct();
    }
}