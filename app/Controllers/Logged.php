<?php
namespace App\ Controllers;

class Logged extends Ajax {
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

        //
        sleep( 1 );

        // xong lưu lại phiên mới
        $this->base_model->set_ses_login( $this->session_data );

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