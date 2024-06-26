<?php

/**
 * Một số chức năng sẽ xác định xem có phải bot spam hay không -> gọi hàm: check_csrf
 * Trong form submit đi thì dùng hàm csrf_field để tạo input ẩn
 **/

namespace App\Controllers;

//
use App\Libraries\ConfigType;

//
class Csrf extends Layout
{
    // 1 số chức năng của khách sẽ cần đến captcha
    protected $has_captcha = false;

    // xác định target của form để đưa ra code thông báo phù hợp
    protected $form_target = false;

    public function __construct()
    {
        parent::__construct();

        // bảo mật đầu vào khi submit form
        // $this->base_model->check_csrf();
    }

    protected function checking_recaptcha()
    {
        // kiểm tra recaptcha (nếu có)
        $check_recaptcha = $this->googleCaptachStore();
        if ($check_recaptcha === null) {
            return true;
        }
        // != true -> có lỗi -> in ra lỗi
        else if ($check_recaptcha !== true) {
            $this->base_model->msg_error_session($check_recaptcha, $this->form_target);

            // -> chuyển tham số này thành true -> các lệnh sau đó sẽ dừng thực thi
            $this->has_captcha = true;
        }

        //
        return $check_recaptcha;
    }

    // các trường hợp có captcha thì so khớp, không thì thôi
    protected function checking_captcha($msg = 'Mã xác thực không chính xác')
    {
        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
            // ưu tiên sử dụng recaptcha
            if (
                isset($_REQUEST['ebe_grecaptcha_response']) &&
                $this->checking_recaptcha() !== true
            ) {
                // không cần làm gì ở đây cả -> trong function checking recaptcha đã làm rồi
            }
            // không có thì sử dụng MY_captcha
            else if (
                isset($_REQUEST['captcha']) &&
                $this->MY_session('check_captcha') != $this->MY_post('captcha')
            ) {
                $this->base_model->msg_error_session($msg, $this->form_target);
                $this->has_captcha = true;
            }
        }
    }

    // các trường hợp bắt buộc phải có captcha
    protected function check_required_captcha($msg = 'Mã xác thực không chính xác')
    {
        // với các trường hợp cần dùng đến captcha nhưng không có trong đầu vào
        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
            // nạp config cho phần đăng nhập
            $firebase_config = $this->option_model->obj_config(ConfigType::FIREBASE);

            if (!empty($firebase_config->g_recaptcha_secret_key)) {
                // ưu tiên sử dụng recaptcha
                if ($this->checking_recaptcha() !== true) {
                    // không cần làm gì ở đây cả -> trong function checking recaptcha đã làm rồi
                }
            }
            // không có thì sử dụng MY_captcha
            else if (
                $this->MY_session('check_captcha') != '' &&
                $this->MY_session('check_captcha') != $this->MY_post('captcha')
            ) {
                //die(__CLASS__ . ':' . __LINE__);
                $this->base_model->msg_error_session($msg, $this->form_target);

                // -> chuyển tham số này thành true -> các lệnh sau đó sẽ dừng thực thi
                $this->has_captcha = true;
            }
        }
    }

    protected function done_action_login($login_redirect = '', $token_rememberme = [])
    {
        if ($login_redirect == '') {
            $login_redirect = base_url($_SERVER['REQUEST_URI']);
            //die( $login_redirect );
        }
        if ($this->form_target !== false) {
            //
            if (!empty($token_rememberme)) {
                $token_rememberme = urlencode(json_encode($token_rememberme));
            } else {
                $token_rememberme = '';
            }

            //
            die('<script {csp-script-nonce}>top.done_action_submit("' . $login_redirect . '", "' . $token_rememberme . '", "' . WGR_COOKIE_LOGIN_KEY . '");</script>');
        }
        $this->MY_redirect($login_redirect);
    }

    protected function wgr_target()
    {
        if ($this->MY_post('__wgr_target', '') != '') {
            $this->form_target = 'error';
        }
        return false;
    }
}
