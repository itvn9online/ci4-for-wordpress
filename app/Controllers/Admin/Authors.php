<?php
namespace App\Controllers\Admin;

// Libraries
use App\Libraries\UsersType;

//
class Authors extends Users
{
    protected $member_type = UsersType::AUTHOR;

    // tham số dùng để thay đổi URL cho controller nếu muốn
    protected $controller_slug = 'authors';

    public function __construct()
    {
        parent::__construct();
    }
}