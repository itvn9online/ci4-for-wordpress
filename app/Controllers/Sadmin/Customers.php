<?php

namespace App\Controllers\Sadmin;

// Libraries
use App\Libraries\UsersType;

//
class Customers extends Users
{
    protected $member_type = UsersType::CUSTOMER;

    // tham số dùng để thay đổi URL cho controller nếu muốn
    protected $controller_slug = 'customers';

    public function __construct()
    {
        parent::__construct();
    }
}
