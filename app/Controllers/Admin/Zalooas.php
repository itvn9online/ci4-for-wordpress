<?php

namespace App\Controllers\Admin;

// Libraries
//use App\Libraries\LanguageCost;

//
require_once __DIR__ . '/vendor/autoload.php';

//
class Zalooas extends Admin
{
    // với 1 số controller, sẽ không nạp cái HTML header vào, nên có thêm tham số này để không nạp header nữa
    public $preload_admin_header = false;

    public function __construct()
    {
        parent::__construct();
    }

    public function testCode()
    {
    }
}
