<?php

namespace App\Controllers\Sadmin;

// Libraries
use App\Libraries\ConfigType;

//
class Displays extends Configs
{
    protected $config_type = ConfigType::DISPLAY;
    //protected $view_edit = 'smtp';

    public function __construct()
    {
        parent::__construct();
    }
}
