<?php
/*
* Chức năng đăng nhập qua Zalo
* https://github.com/zaloplatform/zalo-php-sdk
*/

namespace App\Controllers;

// chạy vòng lặp nạp thư viện của Zalo -> nạp chính xác từng bước nếu không sẽ lỗi class
foreach ([
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
    require_once APPPATH . 'ThirdParty/zalo-php-sdk/src/' . $v;
}

//
use Zalo\Zalo;

//
//use App\Libraries\UsersType;
//use App\Libraries\DeletedStatus;
//use App\Libraries\PHPMaillerSend;
//use App\Language\Translate;
//use App\Helpers\HtmlTemplate;

//
class Zalos extends Guest
{
    protected $zalo = NULL;
    protected $helper = NULL;

    public function __construct()
    {
        parent::__construct();

        //
    }

    protected function loadConfig()
    {
        if ($this->zalo === NULL) {
            $this->zalo = new Zalo([
                'app_id' => '',
                'app_secret' => ''
            ]);
            $this->helper = $this->zalo->getRedirectLoginHelper();
        }
    }

    /*
    * chức năng đăng nhập qua zalo
    * https://developers.zalo.me/docs/api/social-api/tham-khao/user-access-token-post-4316
    */
    public function login_url()
    {
        //
        ob_end_flush();

        //
        $random = bin2hex(openssl_random_pseudo_bytes(32));
        $verifier = $this->base64url_encode(pack('H*', $random));
        $codeChallenge = $this->base64url_encode(pack('H*', hash('sha256', $verifier)));
        //echo 'codeChallenge: ' . $codeChallenge . ':' . __LINE__ . '<br>';
        //echo strlen($codeChallenge) . ':' . __LINE__ . '<br>';
        $this->cache_challenge($codeChallenge);

        //
        $callBackUrl = base_url('zalos/dang_nhap');
        $state = md5($codeChallenge);
        $this->loadConfig();
        $loginUrl = $this->helper->getLoginUrl($callBackUrl, $codeChallenge, $state); // This is login url
        //echo $loginUrl . '<br>';

        //
        $this->MY_redirect($loginUrl);
    }

    public function dang_nhap()
    {
        //
        ob_end_flush();

        //
        echo $this->cache_challenge() . PHP_EOL;
        echo md5($this->cache_challenge()) . PHP_EOL;
        print_r($_GET);
        //die(__CLASS__ . ':' . __LINE__);

        //
        $codeVerifier = $this->MY_get('code');
        //$codeVerifier = $this->cache_challenge();
        //$codeVerifier = $this->MY_get('state');
        //$codeVerifier = $this->MY_get('code_challenge');
        if (!empty($codeVerifier)) {
            echo $codeVerifier . PHP_EOL;
            $this->loadConfig();
            $zaloToken = $this->helper->getZaloToken($codeVerifier); // get zalo token
            $accessToken = $zaloToken->getAccessToken();
            echo $accessToken . '<br>';
        }
    }

    protected function cache_challenge($str = '')
    {
        if ($str != '') {
            return $this->base_model->MY_session(__FUNCTION__ . session_id(), $str);
        }
        return $this->base_model->MY_session(__FUNCTION__ . session_id());
    }

    protected function base64url_encode($plainText)
    {
        $base64 = base64_encode($plainText);
        $base64 = trim($base64, "=");
        $base64url = strtr($base64, '+/', '-_');
        return ($base64url);
    }
}
