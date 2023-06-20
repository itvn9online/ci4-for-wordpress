<?php

namespace App\Controllers\Admin;

// Libraries
use App\Libraries\ConfigType;

//
class Zalooas extends Configs
{
    protected $config_type = ConfigType::ZALO;
    protected $example_prefix = 'zalooa_config';
    protected $zalooa_config = NULL;

    public function __construct()
    {
        parent::__construct();

        //
        $this->zaloa_model = new \App\Models\Zaloa();

        //
        $this->zalooa_config = $this->option_model->obj_config(ConfigType::ZALO);
        //print_r($this->zalooa_config);
        //die(__CLASS__ . ':' . __LINE__);
    }

    /**
     * Gửi thử OTP qua ZNS
     **/
    public function send_test_otp_zns()
    {
        if (empty($this->zalooa_config->zns_phone_for_test)) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => 'Please enter phone number for test: ' . base_url('admin/configs') . '?support_tab=data_phone',
            ]);
        }

        //
        $this->result_json_type([
            'code' => __LINE__,
            'phone' => $this->zalooa_config->zns_phone_for_test,
            'result' => $this->zaloa_model->sendOtpZns($this->zalooa_config->zns_phone_for_test, rand(1000, 9999), [
                'mode' => 'development',
            ]),
        ]);
    }

    /*
    * Tạo URL update Access Token cho ZNS
    */
    public function before_zns()
    {
        //print_r($_GET);
        $this->MY_redirect($this->zaloa_model->zaloAccessToken(base_url('zalos/get_access_token')));
    }
}
