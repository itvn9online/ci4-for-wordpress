<?php

namespace App\Controllers;

// 
// use App\Libraries\UsersType;
// use App\Libraries\DeletedStatus;
use App\Libraries\PHPMaillerSend;
use App\Helpers\HtmlTemplate;

//
class Firebases extends Guest
{
    public $file_auth = 'phone_auth';

    public function __construct()
    {
        parent::__construct();
    }

    // view cho phần xác thực số điện thoại  bằng firebase
    public function phone_auth()
    {
        $this->teamplate['main'] = view(
            'phone_auth_view',
            array(
                'seo' => $this->base_model->default_seo(
                    $this->lang_model->get_the_text('firebase_title', 'Verify phone number'),
                    $this->getClassName(__CLASS__) . '/' . __FUNCTION__
                ),
                'breadcrumb' => '',
                'phone_number' => $this->MY_get('phone_number'),
                // tạo chuỗi dùng để xác thực url sau khi đăng nhập thành công
                'sign_in_params_success' => $this->firebaseSignInSuccessParams(),
                'expires_time' => $this->expires_time,
                'file_auth' => $this->file_auth,
                'firebase_config' => $this->firebase_config,
                'zalooa_config' => $this->zalooa_config,
            )
        );

        // còn không sẽ tiến hành lưu cache
        return view('layout_view', $this->teamplate);
    }

    // kiểm tra URL có hợp lệ hay không
    protected function firebaseUrlExpires($expires_token, $expires_time, $hash_code = '')
    {
        // bắt đầu kiểm tra
        if (!empty($expires_token)) {
            // cắt bỏ 3 ký tự cuối của expires_token
            $expires_token = substr($expires_token, 0, -3);
        }
        if (empty($expires_token) || time() - $expires_token > $expires_time) {
            // URL hết hạn
            $this->result_json_type([
                'code' => __LINE__,
                'reload' => __LINE__,
                'auto_logout' => __LINE__,
                'error' => $this->firebaseLang('expires_expires', 'expires_token đã hết hạn sử dụng. Vui lòng thử lại sau giây lát...'),
            ]);
        }
        // tạo hash code mặc định nếu chưa có
        if (empty($hash_code)) {
            $hash_code = $this->base_model->MY_sessid();
        }

        //
        $at = $this->myAccessToken();
        if (empty($at) || $this->base_model->mdnam($expires_token . $hash_code) != $at) {
            $this->result_json_type([
                'code' => __LINE__,
                'reload' => __LINE__,
                'auto_logout' => __LINE__,
                'error' => $this->firebaseLang('access_token', 'access_token not suitable!'),
            ]);
        }

        //
        return $expires_token;
    }

    // gửi email xác thực lại thông tin đăng nhập qua firebase
    protected function reVerifyFirebaseEmail($data, $ops = [])
    {
        // $this->result_json_type($data);

        // chuẩn bị gửi mail báo xác thực email
        $smtp_config = $this->option_model->get_smtp();

        //
        if (isset($ops['uid']) && !empty($ops['uid'])) {
            $verify_params = $this->firebaseSignInSuccessParams($ops['uid']);
            $verify_params['uid'] = $ops['uid'];
            $verify_params['verify_key'] = $this->base_model->mdnam($data['ID'] . $ops['uid']);
        } else {
            $verify_params = $this->firebaseSignInSuccessParams();
            $verify_params['verify_key'] = $this->base_model->mdnam($data['ID'] . $this->base_model->MY_sessid());
        }
        // print_r($verify_params);

        //
        $verify_url = base_url('firebase2s/verify_email') . '?nse=' . $data['ID'];
        foreach ($verify_params as $k => $v) {
            if (in_array($k, [
                'success_url',
                // 'token_url',
                // 'user_token',
            ])) {
                continue;
            }
            $verify_url .= '&' . $k . '=' . $v;
        }

        // thiết lập thông tin người nhận
        $data_send = [
            'to' => $data['user_email'],
            'subject' => $this->firebaseLang('reverify_subject', 'Xac minh dia chi email'),
            'message' => HtmlTemplate::render(
                // mẫu HTML
                $this->base_model->get_html_tmp('firebase_verify_email'),
                [
                    // các tham số
                    'verify_url' => $verify_url,
                    'base_url' => base_url(),
                    'email' => $data['user_email'],
                    'ip' => $this->base_model->getIPAddress(),
                    'agent' => $_SERVER['HTTP_USER_AGENT'],
                    'date_send' => date('r'),
                ]
            ),
        ];

        // Nếu mail mới được gửi trong thời gian còn cache thì nhắc lại thông báo
        if ($this->user_model->the_cache($data['ID'], __FUNCTION__) !== null) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => $this->firebaseLang('check_email', 'Vui lòng kiểm tra email và kích hoạt tài khoản của bạn: ') . $data['user_email'],
            ]);
        }

        // Nếu gửi mail thành công
        if (PHPMaillerSend::the_send($data_send, $smtp_config) === true) {
            // không cho gửi mail liên tục
            $this->user_model->the_cache($data['ID'], __FUNCTION__, time(), MINI_CACHE_TIMEOUT);

            // cập nhật verify_key -> nếu người dùng bấm vào đây thì sẽ tiến hành cập nhật firebase uid
            $this->user_model->update_member($data['ID'], [
                'user_activation_key' => $verify_params['verify_key'],
            ]);

            // báo người dùng check email
            $this->result_json_type([
                'code' => __LINE__,
                'error' => $this->firebaseLang('check_email', 'Vui lòng kiểm tra email và kích hoạt tài khoản của bạn: ') . $data['user_email'],
            ]);
        } else {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => $this->firebaseLang('error_email', 'The email system is having an error, please notify the administrator'),
            ]);
        }
    }

    public function verify_email()
    {
        $this->firebaseUrlExpires($this->MY_get('expires_token'), $this->expires_reverify_time, $this->MY_get('uid'));
        // $this->result_json_type($_GET);

        //
        $user_id = $this->checkEmptyParams($this->MY_get('nse'), [
            'code' => __LINE__,
            'error' => $this->firebaseLang('verify_nse', 'nse trống'),
        ]);
        $verify_key = $this->checkEmptyParams($this->MY_get('verify_key'), [
            'code' => __LINE__,
            'error' => $this->firebaseLang('verify_key', 'verify_key trống'),
        ]);
        $uid = $this->checkEmptyParams($this->MY_get('uid'), [
            'code' => __LINE__,
            'error' => $this->firebaseLang('uid', 'uid trống'),
        ]);

        // so khới với verify_key
        if ($this->base_model->mdnam($user_id . $uid) == $verify_key) {
            // khớp thông tin thì cập nhật lại firebase uid
            $this->user_model->update_member($user_id, [
                // 'user_status' => UsersType::FOR_DEFAULT,
                'firebase_uid' => $this->base_model->mdnam($uid),
                'firebase_source_uid' => date('r') . '|' . __CLASS__ . '|' . __FUNCTION__ . ':' . __LINE__,
                'user_activation_key' => '',
            ], [
                // where
                'user_activation_key' => $verify_key,
            ]);

            //
            $this->base_model->msg_session($this->firebaseLang('verify_complete', 'Kích hoạt lại tài khoản thành công! Cảm ơn bạn.'));
        } else {
            $this->base_model->msg_error_session($this->firebaseLang('verify_error_params', 'Thông số trên URL không hợp lệ!'));
        }

        // chuyển đến trang đăng nhập
        $this->MY_redirect(base_url('guest/login'));
    }

    // kiểm tra xem token có đúng theo session không
    protected function firebaseSessionToken($expires_token, $ut)
    {
        if (empty($ut) || $this->base_model->mdnam($expires_token . $this->base_model->MY_sessid()) != $ut) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => $this->firebaseLang('user_token', 'user_token not suitable!'),
            ]);
        }
    }

    protected function sign_in_token($uid)
    {
        //
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // lưu cache để đến phiên đăng nhập thì nạp lại -> không cho 1 phiên lưu quá lâu
            $this->id_cache_token($uid);

            //
            $this->result_json_type($_POST);
        }

        //
        $this->result_json_type([
            'code' => __LINE__,
            'error' => 'No money no love!'
        ]);
    }

    protected function id_cache_token($str = '')
    {
        //
        $key = __FUNCTION__ . $this->base_model->MY_sessid();
        // return $key;

        //
        if ($str != '') {
            // nếu có lệnh giữ lại session -> dùng cache -> session bị xóa khi bấm logout -> không giữ được
            if ($this->firebase_config->save_firebase_session == 'on') {
                return $this->base_model->scache($key, $str, DAY);
            }
            return $this->base_model->MY_session($key, $str);
        }
        if ($this->firebase_config->save_firebase_session == 'on') {
            return $this->base_model->scache($key);
        }
        return $this->base_model->MY_session($key);
    }

    // trả về bản dịch của chức năng đăng nhâp qua firebase
    protected function firebaseLang($key, $default_value, $a = [])
    {
        $str = $this->lang_model->get_the_text('firebase_' . $key, $default_value);
        foreach ($a as $k => $v) {
            $str = str_replace('{' . $k . '}', $v, $str);
        }
        return $str;
    }

    // kiểm tra dữ liệu trống
    protected function checkEmptyParams($str, $err)
    {
        if (empty($str)) {
            $this->result_json_type($err);
        }
        return $str;
    }

    // kiểm tra dữ liệu trống hoặc không có trong config
    protected function checkConfigParams($str, $err)
    {
        if (empty($str) || strpos($this->firebase_config->g_firebase_config, $str) === false) {
            $this->result_json_type($err);
        }
        return $str;
    }
}
