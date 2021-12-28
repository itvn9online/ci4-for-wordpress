<?php

namespace App\ Models;

//use CodeIgniter\ Model;

class Session {
    // key dùng lưu session cho các phiên kiểm tra csrf
    private $key_csrf_hash = '_wgr_csrf_hash';
    // key lưu phiên đăng nhập của khách
    private $key_member_login = '_wgr_logged';

    public function __construct() {
        //
    }

    public function MY_session( $key, $value = NULL ) {
        if ( $value !== NULL ) {
            $_SESSION[ $key ] = $value;
            return true;
        }
        /*
        if ( empty( $key ) ) {
            echo debug_backtrace()[ 1 ][ 'class' ] . '\\ ' . debug_backtrace()[ 1 ][ 'function' ] . '<br>' . "\n";
            die( __FILE__ . ':' . __LINE__ );
        }
        */
        return isset( $_SESSION[ $key ] ) ? $_SESSION[ $key ] : '';
    }

    /*
     * Kiểm tra đầu vào của dữ liệu xem chuẩn không
     */
    public function check_csrf() {
        $csrf_name = csrf_token();
        //echo $csrf_name . '<br>' . "\n";
        // nếu tồn tại hash
        if ( isset( $_REQUEST[ $csrf_name ] ) &&
            // -> kiểm tra khớp dữ liệu
            $_REQUEST[ $csrf_name ] != $this->MY_session( $this->key_csrf_hash ) ) {
            die( json_encode( [
                'code' => __LINE__,
                //'in' => $_REQUEST[ $csrf_name ],
                //'out' => $this->MY_session( $this->key_csrf_hash ),
                'error' => 'CSRF Invalid token from your request!'
            ] ) );
        }
        //die( __FILE__ . ':' . __LINE__ );

        //
        return true;
    }

    // trả về input chứa csrf và lưu vào session để nếu submit thì còn kiểm tra được
    public function csrf_field() {
        // mỗi phiên -> lưu lại csrf token dưới dạng session
        $this->MY_session( $this->key_csrf_hash, csrf_hash() );

        // in html
        echo csrf_field();

        //
        return true;
    }

    // set session login -> lưu phiên đăng nhập của người dùng
    public function set_ses_login( $data ) {
        return $this->MY_session( $this->key_member_login, $data );
    }
    // get session login -> trả về dữ liệu đăng nhập của người dùng
    public function get_ses_login() {
        return $this->MY_session( $this->key_member_login );
    }

    // lưu hoặc lấy session báo lỗi
    public function msg_error_session( $value = NULL ) {
        return $this->MY_session( 'msg_error', $value );
    }
    // lưu hoặc lấy session thông báo
    public function msg_session( $value = NULL ) {
        return $this->MY_session( 'msg', $value );
    }
}