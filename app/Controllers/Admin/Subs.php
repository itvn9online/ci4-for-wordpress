<?php

namespace App\Controllers\Admin;

// Libraries
use App\Libraries\UsersType;

//
class Subs extends Users
{
    protected $member_type = UsersType::SUB;

    // tham số dùng để thay đổi URL cho controller nếu muốn
    protected $controller_slug = 'subs';

    public function __construct()
    {
        parent::__construct();
    }
}
