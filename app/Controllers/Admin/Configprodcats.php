<?php

namespace App\Controllers\Admin;

// Libraries
use App\Libraries\ConfigType;

//
class Configprodcats extends Configs
{
    protected $config_type = ConfigType::PROD_CATS;

    public function __construct()
    {
        parent::__construct();
    }
}
