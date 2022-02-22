<?php
namespace App\ Controllers;

//
class Csrf extends Layout {
    // 1 số chức năng của khách sẽ cần đến captcha
    protected $has_captcha = false;

    // xác định target của form để đưa ra code thông báo phù hợp
    protected $form_target = false;

    public function __construct() {
        parent::__construct();

        // bảo mật đầu vào khi submit form
        $this->base_model->check_csrf();
    }

    // các trường hợp có captcha thì so khớp, không thì thôi
    protected function checking_captcha( $msg = 'Mã xác thực không chính xác' ) {
        if ( $_SERVER[ 'REQUEST_METHOD' ] == 'POST' && isset( $_POST[ 'captcha' ] ) && $this->MY_session( 'check_captcha' ) != $this->MY_post( 'captcha' ) ) {
            $this->base_model->msg_error_session( $msg, $this->form_target );
            $this->has_captcha = true;
        }
    }

    // các trường hợp bắt buộc phải có captcha
    protected function check_required_captcha( $msg = 'Mã xác thực không chính xác' ) {
        // với các trường hợp cần dùng đến captcha nhưng không có
        if ( $_SERVER[ 'REQUEST_METHOD' ] == 'POST' && $this->MY_session( 'check_captcha' ) != $this->MY_post( 'captcha' ) ) {
            $this->base_model->msg_error_session( $msg, $this->form_target );

            // -> chuyển tham số này thành true -> các lệnh sau đó sẽ dừng thực thi
            $this->has_captcha = true;
        }
    }

    protected function done_action_login( $login_redirect = '' ) {
        if ( $login_redirect == '' ) {
            $login_redirect = base_url( $_SERVER[ 'REQUEST_URI' ] );
            //die( $login_redirect );
        }
        if ( $this->form_target !== false ) {
            die( '<script>top.done_action_submit("' . $login_redirect . '");</script>' );
        }
        return redirect()->to( $login_redirect );
        exit();
    }
    protected function wgr_target() {
        if ( $this->MY_post( '__wgr_target', '' ) != '' ) {
            $this->form_target = 'error';
        }
        return false;
    }
}