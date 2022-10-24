<?php
namespace App\Controllers\Admin;

// Libraries
use App\Libraries\ConfigType;

//
class Socials extends Configs
{
    protected $config_type = ConfigType::SOCIAL;
    //protected $view_edit = 'smtp';

    public function __construct()
    {
        parent::__construct();
    }
}