<?php

namespace App\Controllers\Admin;

// Libraries
use App\Libraries\ConfigType;

//
class Zalooas extends Configs
{
    protected $config_type = ConfigType::ZALO;
    protected $example_prefix = 'zalooa_config';

    public function __construct()
    {
        parent::__construct();
    }

    public function testCode()
    {
        $arr = $this->option_model->arr_config($this->config_type);
        print_r($arr);
    }
}
