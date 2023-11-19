<?php

namespace App\Controllers\Sadmin;

// Libraries
use App\Libraries\ConfigType;

//
class Configposts extends Configs
{
    protected $config_type = ConfigType::POST;

    public function __construct()
    {
        parent::__construct();
    }
}
