<?php

namespace App\Controllers\Sadmin;

// Libraries
use App\Libraries\ConfigType;

//
class Checkouts extends Configs
{
    protected $config_type = ConfigType::CHECKOUT;
    //protected $view_edit = 'smtp';

    public function __construct()
    {
        parent::__construct();
    }
}
