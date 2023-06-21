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
                'error' => 'Please enter phone number for test: ' . base_url('admin/zalooas') . '?support_tab=data_zns_phone_for_test',
            ]);
        }

        //
        $this->result_json_type([
            'code' => __LINE__,
            'phone' => $this->zalooa_config->zns_phone_for_test,
            'back' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
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

    /**
     * Cập nhật lại zalo access token bằng refresh token
     **/
    public function refresh_access_token()
    {
        $result = $this->zaloa_model->zaloRefreshToken();
        // nếu quá trình update token thành công
        if ($result !== false && isset($result->access_token)) {
            $this->MY_redirect(base_url('admin/zalooas') . '?support_tab=data_zns_phone_for_test');
        }

        //
        $this->result_json_type([
            'code' => __LINE__,
            'result' => $result,
        ]);
    }
}
