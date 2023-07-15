<?php

namespace App\Controllers\Admin;

// Libraries
use App\Libraries\ConfigType;

//
class Configprods extends Configs
{
    protected $config_type = ConfigType::PROD;

    public function __construct()
    {
        parent::__construct();
    }
}
