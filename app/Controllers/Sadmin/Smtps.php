<?php

namespace App\Controllers\Sadmin;

// Libraries
use App\Libraries\ConfigType;

//
class Smtps extends Configs
{
    protected $config_type = ConfigType::SMTP;
    //protected $view_edit = 'smtp';

    public function __construct()
    {
        parent::__construct();
    }
}
