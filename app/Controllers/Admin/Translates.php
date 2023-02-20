<?php

namespace App\Controllers\Admin;

// Libraries
use App\Libraries\ConfigType;

//
class Translates extends Configs
{
    protected $config_type = ConfigType::TRANS;
    protected $view_edit = 'translate';

    public function __construct()
    {
        parent::__construct();
    }
}
