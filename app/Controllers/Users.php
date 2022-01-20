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
        $data = $this->base_model->select( '*', 'users', [
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

    public function logout() {
        // nếu có session login từ admin vào 1 user nào đó -> quay lại session của admin
        $admin_login_as = $this->MY_session( 'admin_login_as' );
        if ( !empty( $admin_login_as ) ) {
            $this->base_model->set_ses_login( $admin_login_as );

            // xóa session login as
            $this->MY_session( 'admin_login_as', '' );

            //
            return redirect()->to( base_url( 'users/profile' ) );
        }
        // còn không thì logout thôi
        else {
            session_destroy();
            //$this->session->destroy();

            // xóa cookie lưu ID đăng nhập
            //delete_cookie( $this->wrg_cookie_login_key );

            //
            return redirect()->to( base_url( 'guest/login' ) );
        }
    }
}