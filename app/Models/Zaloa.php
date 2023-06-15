<?php

namespace App\Models;

// chạy vòng lặp nạp thư viện của Zalo -> nạp chính xác từng bước nếu không sẽ lỗi class
foreach ([
    'ZaloEndPoint.php',
    'Authentication/ZaloToken.php',
    'Exceptions/ZaloSDKException.php',
    'Exceptions/ZaloOAException.php',
    'Exceptions/ZaloResponseException.php',
    'ZaloResponse.php',
    'Http/GraphRawResponse.php',
    'Http/RequestBodyInterface.php',
    'Http/RequestBodyUrlEncoded.php',
    'Url/ZaloUrlManipulator.php',
    'ZaloRequest.php',
    'Authentication/OAuth2Client.php',
    'Authentication/ZaloRedirectLoginHelper.php',
    'Url/UrlDetectionInterface.php',
    'Url/ZaloUrlDetectionHandler.php',
    'ZaloApp.php',
    'HttpClients/ZaloCurl.php',
    'HttpClients/ZaloHttpClientInterface.php',
    'HttpClients/ZaloCurlHttpClient.php',
    'HttpClients/HttpClientsFactory.php',
    'ZaloClient.php',
    'Zalo.php',
] as $v) {
    $f = APPPATH . 'ThirdParty/zalo-php-sdk/src/' . $v;
    if (!file_exists($f)) {
        ob_end_flush();
        //echo $f . '<br>' . PHP_EOL;
        die('File not found: ' . $v);
    }
    require_once $f;
}

//
use Zalo\Zalo;
use App\Libraries\ConfigType;

//
class Zaloa extends Option
{
    protected $zalo = NULL;
    protected $helper = NULL;
    protected $zalooa_config = NULL;

    public function __construct()
    {
        parent::__construct();

        //
        $this->zalooa_config = $this->obj_config(ConfigType::ZALO);
        //print_r($this->zalooa_config);
        //die(__CLASS__ . ':' . __LINE__);
    }

    protected function loadConfig()
    {
        if ($this->zalo === NULL) {
            if (empty($this->zalooa_config->zalooa_app_id)) {
                $this->base_model->result_json_type([
                    'code' => __LINE__,
                    'error' => 'Zalo OA app ID is EMPTY!',
                ]);
            } else if (empty($this->zalooa_config->zalooa_app_secret)) {
                $this->base_model->result_json_type([
                    'code' => __LINE__,
                    'error' => 'Zalo OA app secret is EMPTY!',
                ]);
            }

            //
            $this->zalo = new Zalo([
                'app_id' => $this->zalooa_config->zalooa_app_id,
                'app_secret' => $this->zalooa_config->zalooa_app_secret
            ]);
            $this->helper = $this->zalo->getRedirectLoginHelper();
        }
    }

    /*
    * chức năng tạo URL để Yêu cầu cấp mới OA Access Token
    * https://developers.zalo.me/docs/api/official-account-api/phu-luc/official-account-access-token-post-4307
    */
    public function zaloAccessToken($callBackUrl, $login_url = false)
    {
        //
        ob_end_flush();

        //
        $random = bin2hex(openssl_random_pseudo_bytes(32));
        $verifier = $this->base64url_encode(pack('H*', $random));
        //die($verifier);
        $this->cache_verifier($verifier);
        $codeChallenge = $this->base64url_encode(pack('H*', hash('sha256', $verifier)));
        //echo 'codeChallenge: ' . $codeChallenge . ':' . __LINE__ . '<br>';
        //echo strlen($codeChallenge) . ':' . __LINE__ . '<br>';
        $this->cache_challenge($codeChallenge);

        //
        $state = md5($codeChallenge);
        $this->loadConfig();
        if ($login_url === true) {
            $loginUrl = $this->helper->getLoginUrl($callBackUrl, $codeChallenge, $state); // This is login url
        } else {
            $loginUrl = 'https://oauth.zaloapp.com/v4/oa/permission?app_id=' . $this->zalooa_config->zalooa_app_id . '&redirect_uri=' . urlencode($callBackUrl) . '&code_challenge=' . $codeChallenge . '&state=' . $state;
        }
        //die($loginUrl);
        //echo $loginUrl . '<br>';

        //
        return $loginUrl;
    }

    public function zaloOaAccessToken($callBackUrl)
    {
        return $this->zaloAccessToken($callBackUrl, true);
    }

    // lấy access token sau khi request xong
    public function zaloOaAfterAccessToken()
    {
        //
        ob_end_flush();

        //
        $codeVerifier = $this->cache_verifier();
        if (empty($codeVerifier)) {
            return false;
        }
        //echo $codeVerifier . PHP_EOL;

        //
        $this->loadConfig();
        $zaloToken = $this->helper->getZaloToken($codeVerifier); // get zalo token
        $accessToken = $zaloToken->getAccessToken();
        //die($accessToken);
        //echo $accessToken . '<br>';

        //
        //ini_set('display_errors', 1);
        //error_reporting(E_ALL);

        //
        return $accessToken;
    }

    // lấy access token sau khi request xong
    public function zaloAfterAccessToken()
    {
        //die($this->zalooa_config->zalooa_app_id);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://oauth.zaloapp.com/v4/oa/access_token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
    "code": "' . $_GET['code'] . '",
    "app_id": "' . $this->zalooa_config->zalooa_app_id . '",
    "grant_type": "authorization_code",
    "code_verifier": "' . $this->cache_verifier() . '"
}',
            CURLOPT_HTTPHEADER => array(
                'secret_key: ' . $this->zalooa_config->zalooa_app_secret,
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
    }

    public function send_otp_zns($phone, $otp)
    {
        if (empty($this->zalooa_config->zalooa_access_token)) {
            return 'zalooa_access_token EMPTY';
        }
        if (empty($this->zalooa_config->zalooa_template_otp_id)) {
            return 'zalooa_template_otp_id EMPTY';
        }

        // chuyển định dạng số điện thoại về chuẩn mà Zalo yêu cầu
        $phone = $this->syncPhoneNumber($phone);
        $phone = preg_replace('/^0/', '84', $phone);
        //die($phone);

        //
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
    "phone": "' . $phone . '",
    "template_id": "' . $this->zalooa_config->zalooa_template_otp_id . '",
    "template_data": {
        "otp": "' . $otp . '",
    }
}',
            CURLOPT_HTTPHEADER => array(
                'access_token: ' . $this->zalooa_config->zalooa_access_token,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        //
        //die($response);
        //echo $response;
        $response = json_decode($response);
        //print_r($response);

        //
        return $response;
    }

    // đồng bộ số điện thoại về 1 định dạng
    protected function syncPhoneNumber($phone_number, $before_number = '0')
    {
        $phone_number = trim($phone_number);
        $phone_number = str_replace(' ', '', $phone_number);
        //die($phone_number);
        $phone_number = preg_replace('/^\+84/', $before_number, $phone_number);
        //die($phone_number);
        $phone_number = preg_replace('/^84/', $before_number, $phone_number);
        //die($phone_number);
        $phone_number = preg_replace('/^00/', $before_number, $phone_number);
        //die($phone_number);
        return $phone_number;
    }

    protected function cache_verifier($str = '')
    {
        return $this->zalo_session(__FUNCTION__, $str);
    }

    protected function cache_challenge($str = '')
    {
        return $this->zalo_session(__FUNCTION__, $str);
    }

    // lưu session của phiên request qua zalo
    protected function zalo_session($key, $str = '')
    {
        if ($str != '') {
            return $this->base_model->MY_session($key . session_id(), $str);
        }
        return $this->base_model->MY_session($key . session_id());
    }

    protected function base64url_encode($plainText)
    {
        $base64 = base64_encode($plainText);
        $base64 = trim($base64, "=");
        $base64url = strtr($base64, '+/', '-_');
        return ($base64url);
    }

    public function getZaloIdName($accessToken)
    {
        $params = ['fields' => 'id,name,picture'];
        $response = $this->zalo->get(\Zalo\ZaloEndPoint::API_GRAPH_ME, $accessToken, $params);
        $result = $response->getDecodedBody(); // result
        //print_r($result);
        return $result;
    }
}
