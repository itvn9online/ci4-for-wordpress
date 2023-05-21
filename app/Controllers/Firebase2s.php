<?php

namespace App\Controllers;

// Libraries
use App\Libraries\UsersType;
use App\Libraries\DeletedStatus;
use App\Libraries\PHPMaillerSend;
use App\Helpers\HtmlTemplate;

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

        // chỉ nhận method post
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // kiểm tra tính hợp lệ của url
            $expires_token = $this->MY_post('expires_token');
            if (!empty($expires_token)) {
                // cắt bỏ 3 ký tự cuối của expires_token
                $expires_token = substr($expires_token, 0, -3);
            }
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
                    'error' => $this->lang_model->get_the_text(__FUNCTION__ . 'uid', 'user_id EMPTY!'),
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
                    'error' => $this->lang_model->get_the_text(__FUNCTION__ . 'email_or_phone', 'email or phone?'),
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
                    // xem đã có tài khoản nào sử dụng firebase_uid này chưa
                    $check_uid = $this->base_model->select(
                        'ID',
                        $this->user_model->table,
                        [
                            'firebase_uid' => $this->base_model->mdnam($firebase_uid),
                        ],
                        array(
                            // hiển thị mã SQL để check
                            //'show_query' => 1,
                            // trả về câu query để sử dụng cho mục đích khác
                            //'get_query' => 1,
                            //'offset' => 2,
                            'limit' => 1
                        )
                    );
                    if (!empty($check_uid)) {
                        $this->result_json_type([
                            'code' => __LINE__,
                            'error' => $this->lang_model->get_the_text(__FUNCTION__ . 'check_uid', 'user_id has been using in another account'),
                        ]);
                    }

                    // bật tắt chế độ verify email khi chưa có firebase_uid -> sau có thời gian thì chuyển cái này thành option on/off trong config
                    $verify_email = 0;
                    if ($verify_email > 0) {
                        // muốn an toàn hơn thì nên làm chức năng gửi email xác thực xong mới cập nhật firebase_uid
                        if (!empty($email)) {
                            // trạng thái chờ kích hoạt email
                            $wait_verify = UsersType::NO_LOGIN;
                            if ($data['user_status'] == $wait_verify) {
                                $this->result_json_type([
                                    'code' => __LINE__,
                                    'error' => $this->lang_model->get_the_text(__FUNCTION__ . 'email_block', 'Your account has been block or not active!'),
                                ]);
                            }

                            $this->user_model->update_member($data['ID'], [
                                // cập nhật firebase_uid
                                'firebase_uid' => $this->base_model->mdnam($firebase_uid),
                                // khóa tài khoản lại -> chờ xác thực email xong sẽ mở
                                'user_status' => $wait_verify,
                            ]);

                            // chuẩn bị gửi mail bào xác thực email
                            $smtp_config = $this->option_model->get_smtp();

                            // thiết lập thông tin người nhận
                            $data_send = [
                                'to' => $email,
                                'subject' => 'Please verify your email account',
                                'message' => HtmlTemplate::render(
                                    // mẫu HTML
                                    $this->base_model->get_html_tmp('firebase_verify_email'),
                                    [
                                        // các tham số
                                        'base_url' => base_url('firebase2s/verify_email'),
                                        'email' => $email,
                                        'ip' => $this->request->getIPAddress(),
                                        'agent' => $_SERVER['HTTP_USER_AGENT'],
                                        'date_send' => date('r'),
                                    ]
                                ),
                            ];

                            // Nếu gửi mail thành công
                            if (PHPMaillerSend::the_send($data_send, $smtp_config) === true) {
                                // báo người dùng check email
                                $this->result_json_type([
                                    'code' => __LINE__,
                                    'error' => $this->lang_model->get_the_text(__FUNCTION__ . 'check_email', 'Please check email and verify your account: ') . $email,
                                ]);
                            } else {
                                // gửi mail lỗi -> reset trạng thái
                                $this->user_model->update_member($data['ID'], [
                                    'user_status' => $data['user_status'],
                                ]);
                            }
                        } else {
                            $this->result_json_type([
                                'code' => __LINE__,
                                'error' => $this->lang_model->get_the_text(__FUNCTION__ . 'empty_email', 'user_id not active if email EMPTY?'),
                            ]);
                        }
                    } else {
                        $this->user_model->update_member($data['ID'], [
                            // cập nhật firebase_uid
                            'firebase_uid' => $this->base_model->mdnam($firebase_uid),
                        ]);
                    }
                }
                // nếu có firebase_uid -> so khớp phần dữ liệu này -> coi như đây là password
                else if ($data['firebase_uid'] != $this->base_model->mdnam($firebase_uid)) {
                    $this->result_json_type([
                        'code' => __LINE__,
                        'error' => $this->lang_model->get_the_text(__FUNCTION__ . 'user_id_mismatched', 'user_id mismatched?'),
                    ]);
                }

                // tạo session login
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
                        'error' => $this->lang_model->get_the_text(__FUNCTION__ . 'email_used', 'Email đã được sử dụng'),
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
                        'error' => $this->lang_model->get_the_text(__FUNCTION__ . 'error_create', 'Lỗi đăng ký tài khoản'),
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
