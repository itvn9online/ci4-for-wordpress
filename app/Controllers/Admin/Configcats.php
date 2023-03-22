<?php

namespace App\Controllers\Admin;

// Libraries
use App\Libraries\ConfigType;

//
class Configcats extends Configs
{
    protected $config_type = ConfigType::CATEGORY;

    public function __construct()
    {
        parent::__construct();
    }
}
