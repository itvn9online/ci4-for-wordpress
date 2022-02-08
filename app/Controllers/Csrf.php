<?php
namespace App\ Controllers;

//
class Csrf extends Layout {
    // 1 số chức năng của khách sẽ cần đến captcha
    protected $has_captcha = false;
    
    public function __construct() {
        parent::__construct();

        // bảo mật đầu vào khi submit form
        $this->base_model->check_csrf();
    }

    // các trường hợp bắt buộc phải có captcha
    protected function check_required_captcha( $msg = 'Mã xác thực không chính xác' ) {
        // với các trường hợp cần dùng đến captcha nhưng không có
        if ( $this->MY_session( 'check_captcha' ) != $this->MY_post( 'captcha' ) ) {
            $this->base_model->msg_error_session( $msg );
            // -> chuyển tham số này thành true -> các lệnh sau đó sẽ dừng thực thi
            $this->has_captcha = true;
        }
    }
}