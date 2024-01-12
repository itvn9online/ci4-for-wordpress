<?php

namespace App\Controllers\Sadmin;

// Libraries
use App\Libraries\ConfigType;

//
class Confighomes extends Configs
{
    protected $config_type = ConfigType::HOME;

    public function __construct()
    {
        parent::__construct();
    }
}
