<?php
namespace App\Controllers\Admin;

// Libraries
use App\Libraries\UsersType;

//
class Mods extends Users
{
    protected $member_type = UsersType::MOD;

    // tham số dùng để thay đổi URL cho controller nếu muốn
    protected $controller_slug = 'mods';

    public function __construct()
    {
        parent::__construct();
    }
}