<?php

namespace App\Controllers;

//
//use App\Libraries\UsersType;

//
class Zalos extends Guest
{
    public function __construct()
    {
        parent::__construct();

        //
        $this->zaloa_model = new \App\Models\Zaloa();
    }

    /*
    * chức năng đăng nhập qua zalo
    * https://developers.zalo.me/docs/api/social-api/tham-khao/user-access-token-post-4316
    */
    public function login_url()
    {
        $this->MY_redirect($this->zaloa_model->zaloOaAccessToken(base_url('zalos/dang_nhap')));
    }
    public function dang_nhap()
    {
        $accessToken = $this->zaloa_model->zaloOaAfterAccessToken();
        if ($accessToken === false) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => 'Zalo Access Token is EMPTY!',
            ]);
            return false;
        }

        //
        $result = $this->zaloa_model->getZaloIdName($accessToken);
        //$this->result_json_type($result);

        //
        if (isset($result['error'])) {
            if ($result['error'] == '0' || $result['error'] == 0) {
                $this->result_json_type($result);
            } else {
                $this->result_json_type([
                    'code' => __LINE__,
                    'error' => 'ERROR code ' . $result['error'], ' (' . $result['message'] . ')',
                ]);
            }
        } else {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => 'Result parameter mismatched!',
            ]);
        }
    }

    /*
    * Update Access Token cho ZNS
    */
    public function get_access_token()
    {
        //print_r($_GET);
        //$this->result_json_type($_GET);

        //
        $response = $this->zaloa_model->zaloAfterAccessToken();

        //
        //die(__CLASS__ . ':' . __LINE__);
        $this->result_json_type([
            'code' => __LINE__,
            //'app_id' => $this->zalooa_config->zalooa_app_id,
            'result' => $response,
        ]);
    }
}
