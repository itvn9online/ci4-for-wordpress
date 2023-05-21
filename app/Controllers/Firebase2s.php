<?php

namespace App\Controllers;

// Libraries
use App\Libraries\UsersType;
use App\Libraries\DeletedStatus;

//require_once APPPATH . 'ThirdParty/firebase-tokens-php-4.2.0/src/JWT/Error/IdTokenVerificationFailed.php';
//require_once APPPATH . 'ThirdParty/firebase-tokens-php-4.2.0/src/JWT/IdTokenVerifier.php';

//use Kreait\Firebase\JWT\Error\IdTokenVerificationFailed;
//use Kreait\Firebase\JWT\IdTokenVerifier;

//
class Firebase2s extends Firebases
{
    // với 1 số controller, sẽ không nạp cái HTML header vào, nên có thêm tham số này để không nạp header nữa
    public $preload_header = false;
    // thời gian hết hạn của mỗi token -> để thấp cho nó bảo mật
    public $expires_time = 120;

    public function __construct()
    {
        parent::__construct();
    }

    public function sign_in_success($callBack = true)
    {
        // kiểm tra url submit tới xem có khớp nhau không
        if (!isset($_SERVER['HTTP_REFERER']) || empty($_SERVER['HTTP_REFERER'])) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => $this->lang_model->get_the_text(__FUNCTION__ . 'referer', 'referer is EMPTY!'),
            ]);
        }
        $referer = explode('//', $_SERVER['HTTP_REFERER']);
        if (!isset($referer[1])) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => $this->lang_model->get_the_text(__FUNCTION__ . 'referer_https', 'referer not //'),
            ]);
        }
        //$this->result_json_type([$_SERVER['HTTP_REFERER']]);
        $referer = explode('/', $referer[1]);
        $referer = $referer[0];
        if ($referer != $_SERVER['HTTP_HOST']) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => $this->lang_model->get_the_text(__FUNCTION__ . 'referer_host', 'referer mismatched!'),
            ]);
        }
        //$this->result_json_type([$referer]);

        // xem có config cho chức năng đăng nhập qua firebase không
        if (empty(trim($this->getconfig->g_firebase_config))) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => $this->lang_model->get_the_text(__FUNCTION__ . 'firebase_config', 'firebase_config is EMPTY!'),
            ]);
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // kiểm tra tính hợp lệ của url
            $expires_token = $this->MY_post('expires_token');
            if (empty($expires_token) || time() - $expires_token > $this->expires_time) {
                // URL hết hạn
                $this->result_json_type([
                    'code' => __LINE__,
                    'reload' => 1,
                    'error' => $this->lang_model->get_the_text(__FUNCTION__ . 'expires_expires', 'expires_token is expires!'),
                ]);
            }

            //
            $access_token = $this->MY_post('access_token');
            if (empty($access_token) || $this->base_model->mdnam($expires_token . session_id()) != $access_token) {
                $this->result_json_type([
                    'code' => __LINE__,
                    'error' => $this->lang_model->get_the_text(__FUNCTION__ . 'access_token', 'access_token mismatched!'),
                ]);
            }

            //
            $user_token = $this->MY_post('user_token');
            if (empty($user_token) || $this->base_model->mdnam($expires_token . $this->current_user_id) != $user_token) {
                $this->result_json_type([
                    'code' => __LINE__,
                    'error' => $this->lang_model->get_the_text(__FUNCTION__ . 'user_token', 'user_token mismatched!'),
                ]);
            }

            //
            $id_token = $this->MY_post('id_token');
            if (empty($id_token)) {
                $this->result_json_type([
                    'code' => __LINE__,
                    'error' => $this->lang_model->get_the_text(__FUNCTION__ . 'id_token', 'id_token EMPTY!'),
                ]);
            }

            //
            $firebase_uid = $this->MY_post('uid', '');
            if (empty($firebase_uid)) {
                $this->result_json_type([
                    'code' => __LINE__,
                    'error' => $this->lang_model->get_the_text(__FUNCTION__ . 'uid', 'uid EMPTY!'),
                ]);
            }

            //
            $project_id = $this->MY_post('project_id');
            if (empty($project_id) || strpos($this->getconfig->g_firebase_config, $project_id) === false) {
                $this->result_json_type([
                    'code' => __LINE__,
                    'error' => $this->lang_model->get_the_text(__FUNCTION__ . 'project_id', 'project_id mismatched!'),
                ]);
            }

            // apikey phải tồn tại trong config thì mới cho tiếp tục
            $apikey = $this->MY_post('apikey');
            if (empty($apikey) || strpos($this->getconfig->g_firebase_config, $apikey) === false) {
                $this->result_json_type([
                    'code' => __LINE__,
                    'error' => $this->lang_model->get_the_text(__FUNCTION__ . 'apikey', 'apikey mismatched!'),
                ]);
            }
            // apiurl phải tồn tại trong config thì mới cho tiếp tục
            $apiurl = $this->MY_post('apiurl');
            if (empty($apiurl) || strpos($this->getconfig->g_firebase_config, $apiurl) === false) {
                $this->result_json_type([
                    'code' => __LINE__,
                    'error' => $this->lang_model->get_the_text(__FUNCTION__ . 'apiurl', 'apiurl mismatched!'),
                ]);
            }

            // kiểm tra tính xác thực của token
            //$this->verifyFirebaseIdToken($project_id, $id_token);
            //$this->result_json_type($_POST);

            //
            $name = $this->MY_post('name');
            $email = trim($this->MY_post('email'));
            //$email = 'itvn9online@yahoo.com';
            $phone = trim($this->MY_post('phone'));
            //$phone = '+84984533228';
            $photo = $this->MY_post('photo', '');

            //
            $where = [
                'is_deleted' => DeletedStatus::FOR_DEFAULT,
            ];
            $where_like = [];
            if (!empty($email)) {
                $where['user_email'] = $email;
            } else if (!empty($phone)) {
                $where_like['user_phone'] = substr($phone, -9);
            } else {
                $this->result_json_type([
                    'code' => __LINE__,
                    'error' => 'email or phone?',
                ]);
            }

            // select lại dữ liệu
            $data = $this->base_model->select(
                '*',
                $this->user_model->table,
                $where,
                array(
                    'like_before' => $where_like,
                    'order_by' => [
                        'ID' => 'ASC'
                    ],
                    // hiển thị mã SQL để check
                    //'show_query' => 1,
                    // trả về câu query để sử dụng cho mục đích khác
                    //'get_query' => 1,
                    //'offset' => 2,
                    'limit' => 1
                )
            );
            //print_r($data);
            // nếu có dữ liệu trả về -> thiết lập session đăng nhập
            if (!empty($data)) {
                // cập nhật firebase_uid nếu chưa có
                if (empty($data['firebase_uid'])) {
                    $this->user_model->update_member($data['ID'], [
                        'firebase_uid' => $this->base_model->mdnam($firebase_uid),
                    ]);
                }
                // nếu có firebase_uid -> so khớp phần dữ liệu này -> coi như đây là password
                else if ($data['firebase_uid'] != $this->base_model->mdnam($firebase_uid)) {
                    $this->result_json_type([
                        'code' => __LINE__,
                        'error' => 'user_id mismatched?',
                    ]);
                }

                //
                $data = $this->sync_login_data($data);
                $data['user_activation_key'] = session_id();

                // cập nhật 1 số thông tin kiểu cố định
                $this->user_model->update_member($data['ID'], [
                    'last_login' => date(EBE_DATETIME_FORMAT),
                    'login_type' => UsersType::FIREBASE,
                    'user_activation_key' => $data['user_activation_key'],
                    'member_verified' => UsersType::VERIFIED,
                ]);

                // cho phép đăng nhập nếu tài khoản chưa bị đánh dấu xóa
                $this->user_model->update_member($data['ID'], [
                    'user_status' => UsersType::FOR_DEFAULT,
                ], [
                    // nếu tài khoản không bị khóa vĩnh viễn
                    'user_status !=' => UsersType::NO_LOGIN,
                    // và chưa bị đánh dấu xóa
                    'is_deleted' => DeletedStatus::FOR_DEFAULT,
                ]);

                // cập nhật avt nếu chưa có
                if (empty($data['avatar']) && $photo != '') {
                    $this->user_model->update_member($data['ID'], [
                        'avatar' => $photo,
                    ]);
                }

                //
                $this->base_model->set_ses_login($data);
            } else {
                if (empty($email)) {
                    $email = substr($phone, -9) . '@' . $_SERVER['HTTP_HOST'];
                }

                // chưa có -> tiến hành đăng ký tài khoản mới
                $data = [
                    'email' => $email,
                    'user_email' => $email,
                    'display_name' => $name,
                    'user_phone' => $phone,
                    'member_type' => UsersType::GUEST,
                    'avatar' => $photo,
                    'firebase_uid' => $this->base_model->mdnam($firebase_uid),
                    'member_verified' => UsersType::VERIFIED,
                ];
                //$this->result_json_type($data);
                $insert = $this->user_model->insert_member($data);
                if ($insert < 0) {
                    $this->result_json_type([
                        'code' => __LINE__,
                        'error' => 'Email đã được sử dụng',
                    ]);
                } else if ($insert !== false) {
                    // đăng ký thành công thì gọi lại chính function này để nó trả về dữ liệu
                    if ($callBack === true) {
                        return $this->sign_in_success(false);
                    } else {
                        $this->result_json_type([
                            'ok' => 0,
                        ]);
                    }
                } else {
                    $this->result_json_type([
                        'code' => __LINE__,
                        'error' => 'Lỗi đăng ký tài khoản',
                    ]);
                }
            }
            //$this->result_json_type($data);
            //$this->result_json_type($_POST);
            $this->result_json_type([
                'ok' => __LINE__,
            ]);
        }

        //
        $this->result_json_type($_GET);
    }

    /*
    * TEST -> code lỗi chưa dùng được
    * Xác thực token của firebase
    * https://github.com/kreait/firebase-tokens-php
    */
    protected function verifyFirebaseIdToken($projectId = '', $idToken = '')
    {
        //require_once APPPATH . 'ThirdParty/firebase-tokens-php-4.2.0/src/JWT/Error/IdTokenVerificationFailed.php';
        //require_once APPPATH . 'ThirdParty/firebase-tokens-php-4.2.0/src/JWT/IdTokenVerifier.php';
        //die(__CLASS__ . ':' . __LINE__);

        $projectId = 'echbay-login';
        $idToken = 'eyJhbGciOiJSUzI1NiIsImtpZCI6ImQwZTFkMjM5MDllNzZmZjRhNzJlZTA4ODUxOWM5M2JiOTg4ZjE4NDUiLCJ0eXAiOiJKV1QifQ.eyJuYW1lIjoiRGFvIFF1b2MgRGFpIiwicGljdHVyZSI6Imh0dHBzOi8vbGgzLmdvb2dsZXVzZXJjb250ZW50LmNvbS9hL0FHTm15eFlvUWY0c0hCUVFxZmtBWnNCODRCTVdJZ3ZaMlJra2RqNjZvbnVzZ2c9czk2LWMiLCJpc3MiOiJodHRwczovL3NlY3VyZXRva2VuLmdvb2dsZS5jb20vZWNoYmF5LWxvZ2luIiwiYXVkIjoiZWNoYmF5LWxvZ2luIiwiYXV0aF90aW1lIjoxNjg0NTkyMTA1LCJ1c2VyX2lkIjoiZTRQMThGbTY2SWVxMzZJc01MbFZUbldqa2hoMSIsInN1YiI6ImU0UDE4Rm02NkllcTM2SXNNTGxWVG5XamtoaDEiLCJpYXQiOjE2ODQ2MjU5NzEsImV4cCI6MTY4NDYyOTU3MSwiZW1haWwiOiJpdHZuOW9ubGluZUBnbWFpbC5jb20iLCJlbWFpbF92ZXJpZmllZCI6dHJ1ZSwiZmlyZWJhc2UiOnsiaWRlbnRpdGllcyI6eyJnb29nbGUuY29tIjpbIjEwODY4MjU2MzUzNzYxMTIyMjAwMiJdLCJnaXRodWIuY29tIjpbIjI0OTI4OTk1Il0sImVtYWlsIjpbIml0dm45b25saW5lQGdtYWlsLmNvbSJdfSwic2lnbl9pbl9wcm92aWRlciI6Imdvb2dsZS5jb20ifX0.iFhHDaN4bI8cJZwGULa8nClmgFLiFlLYrfOnZ_WXFm2ikvcK5uupV0WfXuzJ5jmjcRKRdLWIfhihWvtwZ7DIb2RNIFwJKD4DKRZTNxqk3YMh8MiO7roTEyaok2JYpoqm7YYlkr_-3eDft5VmIDuNk8TD4Imz7WvuHVa7YtD3EjFCjKeaUjWr9XtPMnkr4X6fiOFNKD3lcM-RZYRMQIEpd9HFgrrI6XToJlJ-5_ySMdYipvF0UNVi714s-op6adgXeG4PmZCbrFi3XXm574t5XUsV-C4OOJW81zAzOVg4JRS5wB03oPnCx4_cA7TKz7JC7-0TmtSJCA1yvaYi5GUTDw';

        //
        $aaaaaaa = new \Kreait\Firebase\JWT\IdTokenVerifier();
        die(__CLASS__ . ':' . __LINE__);
        $verifier = $aaaaaaa::createWithProjectId($projectId);
        //$verifier = IdTokenVerifier::createWithProjectId($projectId);

        try {
            $token = $verifier->verifyIdToken($idToken);
            $this->result_json_type([
                'code' => __LINE__,
                'error' => __LINE__,
                'token' => $token,
            ]);
        } catch (IdTokenVerificationFailed $e) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
