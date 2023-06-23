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

    /**
     * chức năng đăng nhập qua zalo OA
     * https://developers.zalo.me/docs/api/social-api/tham-khao/user-access-token-post-4316
     **/
    public function login_url()
    {
        // xác định URL trả về sau khi hoàn tất quá trình kết nối
        $redirect_uri = $this->MY_post('redirect_uri', $this->MY_get('redirect_uri'));
        if (empty($redirect_uri)) {
            $redirect_uri = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            if (!empty($redirect_uri) && strpos($redirect_uri, base_url()) === false) {
                $redirect_uri = '';
            }
        }

        // lưu cache URL redirect để lát còn redirect tới sau khi kết nối xong
        if (!empty($redirect_uri)) {
            $this->oa_redirect($redirect_uri);
        }

        //
        $this->MY_redirect($this->zaloa_model->zaloOaAccessToken(base_url('zalos/oa_connect')));
    }
    /**
     * chỉ đơn giản là móc nối tới login_url -> oa_permission -> thể hiện ý nghĩa là xin cấp quyền kết nối zalo OA
     **/
    public function oa_permission()
    {
        return $this->login_url();
    }
    public function oa_connect()
    {
        //
        //$redirect_url = base_url();
        $redirect_url = $this->oa_redirect();

        //
        $accessToken = $this->zaloa_model->zaloOaAfterAccessToken();
        if ($accessToken === false) {
            if (!empty($redirect_url)) {
                $this->MY_redirect($redirect_url);
            }

            //
            $this->result_json_type([
                'code' => __LINE__,
                'error' => 'Zalo Access Token is EMPTY!',
                'url' => $redirect_url,
            ]);

            //
            return false;
        }

        //
        $result = $this->zaloa_model->getZaloIdName($accessToken);
        //$this->result_json_type($result);
        //die(__CLASS__ . ':' . __LINE__);

        //
        if (isset($result['error'])) {
            // không có lỗi lầm gì
            if ($result['error'] == '0' || $result['error'] == 0) {
                // nếu người dùng đã đăng nhập
                if ($this->current_user_id > 0 && isset($result['id'])) {
                    // cập nhật thông tin cho người dùng
                    $this->user_model->update_member($this->current_user_id, [
                        'zalo_oa_id' => $result['id'],
                        'zalo_oa_data' => json_encode($result),
                    ]);

                    //
                    //$this->result_json_type($this->session_data);
                    //die($result['picture']['data']['url']);
                    if ($this->session_data['avatar'] == '' && isset($result['picture']) && isset($result['picture']['data']) && isset($result['picture']['data']['url'])) {
                        $this->user_model->update_member($this->current_user_id, [
                            'avatar' => $result['picture']['data']['url'],
                        ]);
                    }
                }

                //
                if (!empty($redirect_url)) {
                    $this->MY_redirect($redirect_url);
                }

                // in ra mảng json để TEST
                $this->result_json_type([
                    'code' => __LINE__,
                    'url' => $redirect_url,
                    'result' => $result
                ]);

                // trả về dữ liệu
                return $result;
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
        return false;
    }
    /**
     * lưu cache URL redirect để lát còn redirect tới sau khi kết nối xong
     **/
    protected function oa_redirect($uri = '')
    {
        if (!empty($uri)) {
            return $this->base_model->scache(__FUNCTION__ . session_id(), $uri, 3600);
        }
        return $this->base_model->scache(__FUNCTION__ . session_id());
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
            'result' => $response,
        ]);
    }

    /**
     * Webhook lưu lại các sự kiện mà người dùng thực thi với OA -> xác định thời gian tương tác cuối -> dùng để gửi thông báo qua OA
     * https://developers.zalo.me/docs/api/official-account-api/webhook/gioi-thieu-ve-webhook-post-4219
     **/
    public function webhook()
    {
        // URL này chỉ nhận method post
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => 'data type ERROR',
            ]);
        }

        //
        $data_input = file_get_contents('php://input');
        if (empty($data_input)) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => 'data_input EMPTY',
            ]);
        }

        //
        //$f = WRITEPATH . 'logs/zalo-oa-webhook-' . date('Y-m-d') . '.txt';
        //file_put_contents($f, $data_input . PHP_EOL, FILE_APPEND);
        //chmod($f, DEFAULT_FILE_PERMISSION);

        //
        $data_obj = json_decode($data_input);

        // INSERT
        $result_id = $this->base_model->insert('webhook_zalo_oa', [
            'ip' => $this->request->getIPAddress(),
            'event_name' => $data_obj->event_name,
            'app_id' => $data_obj->app_id,
            'content' => $data_input,
            'created_at' => time(),
        ], true);
        //print_r( $result_id );

        //
        $this->result_json_type([
            'code' => __LINE__,
            //'data' => $data_input,
            'ok' => $result_id,
        ]);
    }
}
