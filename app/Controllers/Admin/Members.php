<?php
namespace App\Controllers\Admin;

// Libraries
use App\Libraries\UsersType;

//
class Members extends Users
{
    protected $member_type = UsersType::MEMBER;

    // tham số dùng để thay đổi URL cho controller nếu muốn
    protected $controller_slug = 'members';

    public function __construct()
    {
        parent::__construct();
    }
}