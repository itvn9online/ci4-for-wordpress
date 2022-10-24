<?php
namespace App\Controllers\Admin;

// Libraries
use App\Libraries\UsersType;

//
class Guests extends Users
{
    protected $member_type = UsersType::GUEST;

    // tham số dùng để thay đổi URL cho controller nếu muốn
    protected $controller_slug = 'guests';

    public function __construct()
    {
        parent::__construct();
    }
}