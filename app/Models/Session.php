<?php

namespace App\ Models;

//use CodeIgniter\ Model;

class Session {
    public function __construct() {
        $this->key_csrf_hash = 'my_csrf_hash';
    }

    public function MY_session( $key, $value = NULL ) {
        if ( $value !== NULL ) {
            $_SESSION[ $key ] = $value;
            return true;
        }
        return isset( $_SESSION[ $key ] ) ? $_SESSION[ $key ] : '';
    }

    /*
     * Kiểm tra đầu vào của dữ liệu xem chuẩn không
     */
    public function check_csrf() {
        $csrf_name = csrf_token();
        //echo $csrf_name . '<br>' . "\n";
        if ( isset( $_REQUEST[ $csrf_name ] ) ) {
            //print_r( $_REQUEST );

            //
            $my_csrf_hash = $this->MY_session( $this->key_csrf_hash );
            if ( $_REQUEST[ $csrf_name ] != $my_csrf_hash ) {
                die( json_encode( [
                    'code' => __LINE__,
                    //'in' => $_REQUEST[ $csrf_name ],
                    //'out' => $my_csrf_hash,
                    'error' => 'CSRF Invalid token from your request!'
                ] ) );
                //die( __FILE__ . ':' . __LINE__ );
            }
        }
        //die( __FILE__ . ':' . __LINE__ );

        //
        return true;
    }

    // trả về input chứa csrf và lưu vào session để nếu submit thì còn kiểm tra được
    public function csrf_field() {
        // mỗi phiên -> lưu lại csrf token dưới dạng session
        $this->MY_session( $this->key_csrf_hash, csrf_hash() );

        //
        echo csrf_field();
    }
}