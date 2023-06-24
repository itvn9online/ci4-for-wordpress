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
                'error' => 'Please enter phone number for test',
                'url' => base_url('admin/zalooas') . '?support_tab=data_zns_phone_for_test',
            ]);
        }

        //
        $this->result_json_type([
            'code' => __LINE__,
            'phone' => $this->zalooa_config->zns_phone_for_test,
            'back' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
            'result' => $this->zaloa_model->sendOtpZns($this->zalooa_config->zns_phone_for_test, rand(1000, 9999), [
                //'mode' => 'development',
            ]),
        ]);
    }

    /**
     * Gửi tin nhắn thông qua Zalo OA
     **/
    public function send_test_msg_oa()
    {
        //$this->result_json_type($this->session_data);

        //
        if (empty($this->zalooa_config->zalooa_user_id_test)) {
            $zalo_oa_id = $this->session_data['zalo_oa_id'];
        } else {
            $zalo_oa_id = $this->zalooa_config->zalooa_user_id_test;
        }

        //
        if (empty($zalo_oa_id)) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => 'Tài khoản của bạn chưa được kết nối với Zalo OA, vui lòng kết nối trước khi tiếp tục',
                'url' => base_url('zalos/login_url'),
            ]);
        }

        //
        $this->result_json_type([
            'code' => __LINE__,
            'zalo_oa_id' => $zalo_oa_id,
            'back' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
            // test lấy thông tin OA
            //'user_profile' => $this->zaloa_model->getOaUserProfile($zalo_oa_id),
            //'user_follower' => $this->zaloa_model->getOaListFollower(),
            //'oa_rofile' => $this->zaloa_model->getOaProfile(),
            //'recent_chat' => $this->zaloa_model->getOaListRecentChat(),
            //'user_chat' => $this->zaloa_model->getOaConversation($zalo_oa_id),
            //'oa_quota' => $this->zaloa_model->getOaQuota(),
            'oa_promotion_quota' => $this->zaloa_model->getOaPromotionQuota($zalo_oa_id),
            // test gửi tin nhắn
            'result' => $this->zaloa_model->sendOaText($zalo_oa_id, implode(PHP_EOL, [
                'Đây là đài tiếng nói Việt Nam!',
                'Phát đi từ Hà Nội, thủ đô nước cộng hòa xã hội chủ nghĩa Việt Nam.',
                'Bây giờ là: ' . date('r') . '.',
                'IP: ' . $this->request->getIPAddress(),
                'Agent: ' . $_SERVER['HTTP_USER_AGENT'],
                base_url(),
            ])),
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
