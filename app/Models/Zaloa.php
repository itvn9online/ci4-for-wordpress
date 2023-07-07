<?php

/**
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
 *
 * Giới thiệu Zalo Official Account API
 * https://developers.zalo.me/docs/api/official-account-api-230
 **/

namespace App\Models;

// sử dụng composer để tải zalo-php-sdk về, sau đó lấy code và up lên host
require_once APPPATH . 'ThirdParty/zalo-php-sdk/autoload.php';

//
use Zalo\Zalo;
use App\Libraries\ConfigType;
use App\Libraries\LanguageCost;

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

        // kiểm tra và nạp lại token nếu đã hết hạn
        $this->zaloRefreshToken(true);
    }

    /**
     * Chức năng này vừa kiểm tra thông số của zalo OA vừa nạp zalo code
     **/
    protected function loadConfig()
    {
        if ($this->zalo === NULL) {
            $this->checkZaloOaConfig(__FUNCTION__);

            //
            $this->zalo = new Zalo([
                'app_id' => $this->zalooa_config->zalooa_app_id,
                'app_secret' => $this->zalooa_config->zalooa_app_secret
            ]);
            $this->loadHelper();
        }
    }

    /**
     * nạp helper để Zalo API còn hoạt động
     **/
    protected function loadHelper()
    {
        if ($this->helper === NULL) {
            $this->helper = $this->zalo->getRedirectLoginHelper();
        }
    }

    /**
     * Chức năng này sẽ kiểm tra thông số của zalo OA
     **/
    protected function checkZaloOaConfig($fname = '')
    {
        if (empty($this->zalooa_config->zalooa_app_id)) {
            $this->base_model->result_json_type([
                'code' => __LINE__,
                'error' => 'Zalo OA app ID is EMPTY!',
                'function' => $fname,
            ]);
        } else if (empty($this->zalooa_config->zalooa_app_secret)) {
            $this->base_model->result_json_type([
                'code' => __LINE__,
                'error' => 'Zalo OA app secret is EMPTY!',
                'function' => $fname,
            ]);
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
        if ($login_url === true) {
            $this->loadConfig();
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
        // TEST
        //$this->base_model->result_json_type($_GET);

        // nếu có lỗi -> người dùng có thể đã bấm từ chối
        if (isset($_GET['error'])) {
            return false;
        }

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

    /*
    * lấy access token sau khi request xong
    * https://developers.zalo.me/docs/api/official-account-api/xac-thuc-va-uy-quyen/cach-1-xac-thuc-voi-giao-thuc-oauth/yeu-cau-cap-moi-oa-access-token-post-4307
    */
    public function zaloAfterAccessToken()
    {
        //die($this->zalooa_config->zalooa_app_id);
        //$this->base_model->result_json_type([$this->cache_challenge()]);

        //
        //$this->checkZaloOaConfig(__FUNCTION__);

        //
        $data = [
            'code' => (isset($_GET['code']) ? $_GET['code'] : ''),
            'app_id' => $this->zalooa_config->zalooa_app_id,
            'grant_type' => 'authorization_code',
            'code_verifier' => $this->cache_verifier(),
        ];
        //$this->base_model->result_json_type($data);
        //$data = json_encode($data);
        $postfield = [];
        foreach ($data as $k => $v) {
            $postfield[] = $k . '=' . $v;
        }
        $postfield = implode('&', $postfield);
        //die($postfield);

        //
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
            CURLOPT_POSTFIELDS => $postfield,
            CURLOPT_HTTPHEADER => array(
                'secret_key: ' . $this->zalooa_config->zalooa_app_secret,
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        //echo $response;
        $response = json_decode($response);

        //
        $this->updateAccessToken($response, true);

        //
        return $response;
    }

    /**
     * Lấy OA Access Token từ OA Refresh Token
     * https://developers.zalo.me/docs/api/official-account-api/xac-thuc-va-uy-quyen/cach-1-xac-thuc-voi-giao-thuc-oauth/lay-access-token-tu-refresh-token-post-4970
     **/
    public function zaloRefreshToken($return_false = false)
    {
        // nếu đây là quá trình update tự động -> kiểm tra hạn của token -> còn hạn thì không update
        if ($return_false !== false) {
            if (!empty($this->zalooa_config->zalooa_expires_token) && $this->zalooa_config->zalooa_expires_token > time()) {
                return false;
            }
        }

        // kiểm tra nếu thiếu thông số thì trả về lỗi luôn
        if (empty($this->zalooa_config->zalooa_app_id)) {
            // 1 số trường hợp sẽ trả về false để tiến trình sau đấy vẫn tiếp tục được
            if ($return_false !== false) {
                return false;
            }
            // mặc định sẽ trả về thông báo lỗi -> die
            $this->base_model->result_json_type([
                'code' => __LINE__,
                'error' => 'Zalo App ID EMPTY',
            ]);
        } else if (empty($this->zalooa_config->zalooa_app_secret)) {
            if ($return_false !== false) {
                return false;
            }
            $this->base_model->result_json_type([
                'code' => __LINE__,
                'error' => 'Zalo App Secret EMPTY',
            ]);
        } else if (empty($this->zalooa_config->zalooa_refresh_token)) {
            if ($return_false !== false) {
                return false;
            }
            $this->base_model->result_json_type([
                'code' => __LINE__,
                'error' => 'Zalo Refresh token EMPTY',
            ]);
        }

        //
        $data = [
            'refresh_token' => $this->zalooa_config->zalooa_refresh_token,
            'app_id' => $this->zalooa_config->zalooa_app_id,
            'grant_type' => 'refresh_token',
        ];
        //$this->base_model->result_json_type($data);
        //$data = json_encode($data);
        $postfield = [];
        foreach ($data as $k => $v) {
            $postfield[] = $k . '=' . $v;
        }
        $postfield = implode('&', $postfield);
        //die($postfield);

        //
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
            CURLOPT_POSTFIELDS => $postfield,
            CURLOPT_HTTPHEADER => array(
                'secret_key: ' . $this->zalooa_config->zalooa_app_secret,
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        //echo $response;
        $response = json_decode($response);

        //
        $this->updateAccessToken($response);

        //
        return $response;
    }

    /**
     * Cập nhật lại Access token và Refresh token mỗi khi request
     **/
    protected function updateAccessToken($response)
    {
        //$this->base_model->result_json_type($response);
        if (isset($response->access_token)) {
            $this->removeAndInsert('zalooa_access_token', $response->access_token);

            //
            if (isset($response->refresh_token)) {
                $this->removeAndInsert('zalooa_refresh_token', $response->refresh_token);
            }

            //
            if (isset($response->expires_in)) {
                $response->expires_in *= 1;
                $this->removeAndInsert('zalooa_expires_token', time() + $response->expires_in);
            }

            // xóa cache liên quan
            $this->clear_cache(ConfigType::ZALO);

            // sau đó nạp lại
            $this->zalooa_config = $this->obj_config(ConfigType::ZALO);

            //
            return true;
        }
        return false;
    }

    /**
     * Xóa xong tạo 1 option cho zalo config
     **/
    protected function removeAndInsert($option_name, $option_value)
    {
        //
        $last_updated = date(EBE_DATETIME_FORMAT);
        $insert_time = date('YmdHis');

        // delete
        $where = [
            'option_type' => ConfigType::ZALO,
            'option_name' => $option_name,
            'lang_key' => LanguageCost::lang_key(),
        ];
        $this->base_model->delete_multiple(
            'options',
            $where,
            [
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
            ]
        );

        // insert
        $this->insert_options(
            [
                'option_name' => $option_name,
                'option_value' => $option_value,
                'option_type' => ConfigType::ZALO,
                'lang_key' => LanguageCost::lang_key(),
                'last_updated' => $last_updated,
                'insert_time' => $insert_time,
            ]
        );
    }

    /*
    * Gửi ZNS
    * https://developers.zalo.me/docs/api/zalo-notification-service-api/gui-thong-bao-zns/gui-thong-bao-zns-post-5208
    */
    public function sendZns($phone, $template_id, $template_data, $custom_data = [], $reset_token = true)
    {
        if (empty($this->zalooa_config->zalooa_access_token)) {
            return 'zalooa_access_token EMPTY';
        }

        // chuyển định dạng số điện thoại về chuẩn mà Zalo yêu cầu
        $phone = $this->syncPhoneNumber($phone);
        $phone = preg_replace('/^0/', '84', $phone);
        //die($phone);

        //
        $data = [
            'phone' => $phone,
            'template_id' => $template_id,
            'template_data' => $template_data,
            'tracking_id' => session_id() . time(),
        ];
        foreach ($custom_data as $k => $v) {
            $data[$k] = $v;
        }
        //$this->base_model->result_json_type($data);
        $data = json_encode($data);

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
            CURLOPT_POSTFIELDS => $data,
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
        $this->oaLogs($response);

        //
        return $response;
    }

    public function sendOtpZns($phone, $otp, $custom_data = [])
    {
        if (empty($this->zalooa_config->zalooa_template_otp_id)) {
            return 'zalooa_template_otp_id EMPTY';
        }

        //
        return $this->sendZns($phone, $this->zalooa_config->zalooa_template_otp_id, [
            'otp' => $otp
        ], $custom_data);
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
        $this->loadConfig();

        //
        $params = ['fields' => 'id,name,picture'];
        $response = $this->zalo->get(\Zalo\ZaloEndPoint::API_GRAPH_ME, $accessToken, $params);
        $result = $response->getDecodedBody(); // result
        //print_r($result);
        return $result;
    }

    /**
     * Chức năng gửi tin nhắn thông qua Zalo OA
     * https://github.com/zaloplatform/zalo-php-sdk
     **/
    public function sendOaText($oa_id, $content, $btn = [], $reset_token = true)
    {
        return $this->sendOaMyText($oa_id, $content, $btn, $reset_token);
        // gửi bằng code cung cấp bởi zalo đang bị đứt do gửi lỗi thì web dừng luôn -> hạn chế dùng
        //return $this->sendOaSdkText($oa_id, $content, $btn, $reset_token);
    }

    /**
     * Chức năng gửi tin nhắn thông qua Zalo OA -> sử dụng code tự build qua postman
     **/
    public function sendOaMyText($oa_id, $content, $btn = [], $reset_token = true)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://openapi.zalo.me/v3.0/oa/message/cs',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode([
                'recipient' => [
                    'user_id' => $oa_id
                ],
                'message' => [
                    'text' => $content
                ],
            ]),
            CURLOPT_HTTPHEADER => array(
                'access_token: ' . $this->zalooa_config->zalooa_access_token,
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        //echo $response;
        $response = json_decode($response);

        //
        return $response;
    }

    /**
     * Chức năng gửi tin nhắn thông qua Zalo OA -> sử dụng code cung cấp bởi Zalo
     * Chức năng này lúc lỗi thì nó đứt luôn -> cũng hạn chế dùng -> vì mình cần lấy lỗi trả về thôi
     **/
    public function sendOaSdkText($oa_id, $content, $btn = [], $reset_token = true)
    {
        //die(\Zalo\ZaloEndPoint::API_OA_SEND_CONSULTATION_MESSAGE_V3);
        //die($this->zalooa_config->zalooa_access_token);

        // trước khi gửi thì cứ kiểm tra cấu hình đã
        $this->loadConfig();

        // build data
        $msgBuilder = new \Zalo\Builder\MessageBuilder(\Zalo\Builder\MessageBuilder::MSG_TYPE_TXT);
        $msgBuilder->withUserId($oa_id);
        $msgBuilder->withText($content);

        //
        $msgText = $msgBuilder->build();

        // send request
        //die(__CLASS__ . ':' . __LINE__);
        $response = $this->zalo->post(\Zalo\ZaloEndPoint::API_OA_SEND_CONSULTATION_MESSAGE_V3, $this->zalooa_config->zalooa_access_token, $msgText);
        //print_r($response);
        //die(__CLASS__ . ':' . __LINE__);
        $result = $response->getDecodedBody();

        //
        $this->oaLogs($result);

        //
        return $result;
    }

    /**
     * Lấy thông tin người quan tâm
     **/
    public function getOaUserProfile($user_id)
    {
        // trước khi gửi thì cứ kiểm tra cấu hình đã
        $this->loadConfig();

        //
        $data = [
            'data' => json_encode(array(
                'user_id' => $user_id
            ))
        ];
        $response = $this->zalo->get(\Zalo\ZaloEndPoint::API_OA_GET_USER_PROFILE, $this->zalooa_config->zalooa_access_token, $data);
        $result = $response->getDecodedBody(); // result
        //print_r($result);

        //
        return $result;
    }

    /**
     * Lấy danh sách người quan tâm
     **/
    public function getOaListFollower($offset = 0)
    {
        // trước khi gửi thì cứ kiểm tra cấu hình đã
        $this->loadConfig();

        //
        $data = [
            'data' => json_encode(array(
                'offset' => $offset,
                'count' => 10
            ))
        ];
        $response = $this->zalo->get(\Zalo\ZaloEndPoint::API_OA_GET_LIST_FOLLOWER, $this->zalooa_config->zalooa_access_token, $data);
        $result = $response->getDecodedBody(); // result
        //print_r($result);

        //
        return $result;
    }

    /**
     * Lấy thông tin OA
     **/
    public function getOaProfile()
    {
        // trước khi gửi thì cứ kiểm tra cấu hình đã
        $this->loadConfig();

        //
        $response = $this->zalo->get(\Zalo\ZaloEndPoint::API_OA_GET_PROFILE, $this->zalooa_config->zalooa_access_token, []);
        $result = $response->getDecodedBody(); // result
        //print_r($result);

        //
        return $result;
    }

    /**
     * Lấy danh sách tin nhắn gần nhất
     **/
    public function getOaListRecentChat($offset = 0)
    {
        // trước khi gửi thì cứ kiểm tra cấu hình đã
        $this->loadConfig();

        //
        $data = [
            'data' => json_encode(array(
                'offset' => $offset,
                'count' => 10
            ))
        ];
        $response = $this->zalo->get(\Zalo\ZaloEndPoint::API_OA_GET_LIST_RECENT_CHAT, $this->zalooa_config->zalooa_access_token, $data);
        $result = $response->getDecodedBody(); // result
        //print_r($result);

        //
        return $result;
    }

    /**
     * Lấy danh sách tin nhắn với người quan tâm
     **/
    public function getOaConversation($user_id, $offset = 0)
    {
        // trước khi gửi thì cứ kiểm tra cấu hình đã
        $this->loadConfig();

        //
        $data = [
            'data' => json_encode(array(
                'user_id' => $user_id,
                'offset' => $offset,
                'count' => 10
            ))
        ];
        $response = $this->zalo->get(\Zalo\ZaloEndPoint::API_OA_GET_CONVERSATION, $this->zalooa_config->zalooa_access_token, $data);
        $result = $response->getDecodedBody(); // result
        //print_r($result);

        //
        return $result;
    }

    /**
     * Kiểm tra hạn mức Tin tư vấn miễn phí
     **/
    public function getOaQuota()
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://openapi.zalo.me/v2.0/oa/quota/message',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'access_token: ' . $this->zalooa_config->zalooa_access_token
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        //echo $response;
        $response = json_decode($response);

        //
        return $response;
    }

    /**
     * Kiểm tra hạn mức tin Truyền thông
     **/
    public function getOaPromotionQuota($user_id)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://openapi.zalo.me/v2.0/oa/quota/message',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode([
                'user_id' => $user_id,
                'type' => 'promotion',
            ]),
            CURLOPT_HTTPHEADER => array(
                'access_token: ' . $this->zalooa_config->zalooa_access_token,
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);

        curl_close($curl);
        //echo $response;
        $response = json_decode($response);

        //
        return $response;
    }

    /**
     * lưu trữ log mỗi lần gửi tin qua OA để tiện theo dõi
     **/
    protected function oaLogs($response)
    {
        $f = WRITEPATH . 'logs/zalo-oa-' . date('Y-m-d') . '.txt';
        if (!file_exists($f)) {
            if (!file_put_contents($f, __CLASS__ . ':' . __LINE__ . PHP_EOL, LOCK_EX)) {
                return false;
            }
            chmod($f, DEFAULT_FILE_PERMISSION);
        }
        return file_put_contents($f, json_encode($response) . PHP_EOL, FILE_APPEND);
    }
}
