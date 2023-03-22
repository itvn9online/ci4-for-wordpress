<?php

namespace App\Controllers\Admin;

// Libraries
use App\Libraries\ConfigType;

//
class Configblogss extends Configs
{
    protected $config_type = ConfigType::BLOGS;

    public function __construct()
    {
        parent::__construct();
    }
}
