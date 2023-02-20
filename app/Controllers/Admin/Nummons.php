<?php

namespace App\Controllers\Admin;

// Libraries
use App\Libraries\ConfigType;

//
class Nummons extends Configs
{
    protected $config_type = ConfigType::NUM_MON;
    protected $view_edit = 'num_mon';

    public function __construct()
    {
        parent::__construct();
    }
}
