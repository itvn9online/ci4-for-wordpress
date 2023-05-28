<?php

namespace App\Controllers\Admin;

// Libraries
use App\Libraries\ConfigType;

//
class Firebases extends Configs
{
    protected $config_type = ConfigType::FIREBASE;
    protected $example_prefix = 'firebase_config';

    public function __construct()
    {
        parent::__construct();
    }
}
