<?php
//require_once __DIR__ . '/Layout.php';
namespace App\ Controllers;

class Logged extends Layout {
    // chức năng này không cần nạp header
    public $preload_header = false;

    public function __construct() {
        parent::__construct();
    }

    // duy trì trạng thái đăng nhập
    public function confirm_login() {
        if ( empty( $this->session_data ) ) {
            die( json_encode( [
                'code' => __LINE__,
                'error' => 'Session logged is empty from ' . __FUNCTION__
            ] ) );
        }

        // thử renew thời hạn cho session
        $this->MY_session( '__ci_last_regenerate', time() );

        //
        sleep( 1 );

        // xong lưu lại phiên mới
        $this->MY_session( 'admin', $this->session_data );

        //
        //die( json_encode( $this->session_data ) );

        //
        header( 'Content-type: application/json; charset=utf-8' );
        die( json_encode( [
            'code' => __LINE__,
            'msg' => 'Confirm user logged from ' . __FUNCTION__
        ] ) );
    }
}