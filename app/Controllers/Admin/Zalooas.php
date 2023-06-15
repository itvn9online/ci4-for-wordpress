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

        //
        $this->zaloa_model = new \App\Models\Zaloa();
    }

    /**
     * Gửi thử OTP qua ZNS
     **/
    public function send_test_otp_zns()
    {
        if (empty($this->getconfig->phone)) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => 'Please enter phone number for test: ' . base_url('admin/configs') . '?support_tab=data_phone',
            ]);
        }

        //
        $this->result_json_type([
            'code' => __LINE__,
            'result' => $this->zaloa_model->send_otp_zns($this->getconfig->phone, rand(100000, 999999)),
        ]);
    }

    public function testCode()
    {
        $arr = $this->option_model->arr_config($this->config_type);
        print_r($arr);
    }
}
