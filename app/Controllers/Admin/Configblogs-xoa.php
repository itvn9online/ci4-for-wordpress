<?php

namespace App\Controllers\Admin;

// Libraries
use App\Libraries\ConfigType;

//
class Configblogs extends Configs
{
    protected $config_type = ConfigType::BLOG;

    public function __construct()
    {
        parent::__construct();
    }
}
