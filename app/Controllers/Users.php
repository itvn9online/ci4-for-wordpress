<?php
//require_once __DIR__ . '/Layout.php';
namespace App\ Controllers;

class Users extends Csrf {

    public function __construct() {
        parent::__construct();

        //
        if ( $this->current_user_id <= 0 ) {
            die( 'Permission deny! ' . basename( __FILE__, '.php' ) . ':' . __LINE__ );
        }

        //
        $this->validation = \Config\ Services::validation();
    }

    public function index() {
        $id = $this->current_user_id;

        //
        if ( !empty( $this->MY_post( 'data' ) ) ) {
            //print_r( $this->MY_post( 'data' ) );
            //die( __FILE__ . ':' . __LINE__ );
            return $this->update( $id );
        }

        // edit
        // select dữ liệu từ 1 bảng bất kỳ
        $data = $this->base_model->select( '*', 'wp_users', [
            'ID' => $id
        ], array(
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 2,
            'limit' => 1
        ) );
        if ( empty( $data ) ) {
            return $this->page404( 'ERROR ' . strtolower( __FUNCTION__ ) . ':' . __LINE__ . '! Không xác định được thông tin thành viên...' );
        }

        //
        $this->teamplate[ 'main' ] = view( 'profile_view', array(
            'seo' => $this->base_model->default_seo( 'Thông tin tài khoản', __FUNCTION__ ),
            'breadcrumb' => '',
            'data' => $data,
            'session_data' => $this->session_data,
        ) );
        return view( 'users_view', $this->teamplate );
    }
    public function profile() {
        return $this->index();
    }

    private function update( $id ) {
        die( 'update profile' );
    }

    // duy trì trạng thái đăng nhập
    public function confirm_login() {
        // thử renew thời hạn cho session
        //$_SESSION[ '__ci_last_regenerate' ] = time();
        $this->session->set( '__ci_last_regenerate', time() );

        // xóa session admin
        //$this->session->remove( 'admin' );
        // xong lưu lại phiên mới
        //$this->session->set( 'admin', $this->session_data );

        //
        header( 'Content-type: application/json; charset=utf-8' );
        die( json_encode( [
            'code' => __LINE__,
            'msg' => 'Confirm user logged from ' . __FUNCTION__
        ] ) );
    }

    public function logout() {
        //die( __FILE__ . ':' . __LINE__ );
        //$this->session->remove( 'admin' );
        $this->session->destroy();
        //echo base_url( 'login' );

        // xóa cookie lưu ID đăng nhập
        //delete_cookie( $this->wrg_cookie_login_key );

        //
        return redirect()->to( base_url( 'guest/login' ) );
    }
}