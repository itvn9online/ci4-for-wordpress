<?php

namespace App\Controllers\Admin;

// Libraries
use App\Libraries\UsersType;

//
class Admins extends Users
{
    protected $member_type = UsersType::ADMIN;

    // tham số dùng để thay đổi URL cho controller nếu muốn
    protected $controller_slug = 'admins';

    public function __construct()
    {
        parent::__construct();
    }
}
