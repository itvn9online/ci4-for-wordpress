<?php

namespace App\Controllers\Admin;

// Libraries
use App\Libraries\ConfigType;

//
class Checkboxs extends Configs
{
    protected $config_type = ConfigType::CHECKBOX;
    protected $view_edit = 'checkbox';

    public function __construct()
    {
        parent::__construct();
    }
}
