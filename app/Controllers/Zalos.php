<?php
/*
* Chức năng đăng nhập qua Zalo
* https://github.com/zaloplatform/zalo-php-sdk
*
* Gửi thông báo qua Zalo ZNS
* https://developers.zalo.me/docs/api/zalo-notification-service-api/gui-thong-bao-zns/gui-thong-bao-zns-post-5208
*
* Gửi ZNS thông qua API
* https://zalo.cloud/zns/guidelines/zns-api
*
* Hướng dẫn tạo ứng dụng (App ID) và liên kết với Zalo OA
* https://zalo.cloud/blog/huong-dan-tao-ung-dung-app-id-va-lien-ket-voi-zalo-oa-/kgua7vnkkvbyy88rma
*
* Quản lý các app đã kết nối zalo -> khi cần XÓA đi để test thì vào đây XÓA
* https://zalo.me/profile/app-management
*/

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
    * Chức năng gửi tin nhắn qua Zalo ZNS
    * https://zalo.cloud/blog/huong-dan-tao-ung-dung-app-id-va-lien-ket-voi-zalo-oa-/kgua7vnkkvbyy88rma
    */
    public function before_zns()
    {
        //print_r($_GET);
        $this->MY_redirect($this->zaloa_model->zaloAccessToken(base_url('zalos/send_zns')));
    }
    public function send_zns()
    {
        //print_r($_GET);
        //die(__CLASS__ . ':' . __LINE__);
        $accessToken = $this->zaloa_model->zaloAfterAccessToken();
        if ($accessToken === false) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => 'Zalo Access Token is EMPTY!',
            ]);
            return false;
        }
        die($accessToken);

        //
        //$accessToken = '8xEZJqod25qdkQf0Jg09LYE-gafGqMTTQiwy00Er5qCrs-eVEOCAII-MuHueqqfk6foVBWprTaeTdfef1yCtVI3Nw3OrbY5tMlNM7Kpq1rzpkUSCMzumQr6Xxmnau1PzL9-q46B7MmT7eeXwS-rZ4MYMZbPPxbm0SOw9GKZxNX5RbBDWGjvU7rE-jGjC-byTHgEH3N-xQNbRfRKpMUGPOqs2rH5soGv-6xFDDX3pDI4vg_rBIQeK8oMskMaWtda-JAYwM4xUQp9IliPHGACB9ctCtKzZenO8IBZ8M6pSCHTSbvbvSjT9BbtFuKr7eZKWIhV9S7Mh2H1XxiTOCbqicYLAiGXS';
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://business.openapi.zalo.me/message/template',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
    "mode": "development",
    "phone": "84984533228",
    "template_id": "264275",
    "template_data": {
        "otp": "' . rand(1000, 9999) . '",
    }
}',
            CURLOPT_HTTPHEADER => array(
                'access_token: ' . $accessToken,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        //echo $response;

        die($response);
    }
}
