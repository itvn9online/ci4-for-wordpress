<?php
namespace App\ Controllers;

//
use App\ Language\ Translate;

//
class Users extends Csrf {
    protected $controller_name = 'Cá nhân';

    // danh sách các cột user được phép update
    protected $allow_update = [
        'display_name',
        'user_nicename',
        'user_birthday',
        'user_phone',
    ];

    //
    public function __construct() {
        parent::__construct();

        //
        if ( $this->current_user_id <= 0 ) {
            die( 'Permission deny! ' . basename( __FILE__, '.php' ) . ':' . __LINE__ );
        }

        //
        $this->validation = \Config\ Services::validation();

        //
        $this->breadcrumb[] = '<li><a href="users/profile">' . $this->controller_name . '</a></li>';
    }

    public function index() {
        return $this->profile();
    }
    public function profile() {
        $id = $this->current_user_id;

        //
        if ( !empty( $this->MY_post( 'data' ) ) ) {
            //print_r( $this->MY_post( 'data' ) );
            //die( __CLASS__ . ':' . __LINE__ );
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
        $this->teamplate[ 'breadcrumb' ] = view( 'breadcrumb_view', array(
            'breadcrumb' => $this->breadcrumb
        ) );

        //
        $this->teamplate[ 'main' ] = view( 'profile_view', array(
            'seo' => $this->base_model->default_seo( 'Thông tin tài khoản', $this->getClassName( __CLASS__ ) . '/' . __FUNCTION__ ),
            'breadcrumb' => '',
            'data' => $data,
            'session_data' => $this->session_data,
        ) );
        return view( 'users_view', $this->teamplate );
    }

    private function update( $id ) {
        $data = $this->MY_post( 'data' );
        //print_r( $data );
        if ( isset( $data[ 'ci_pass' ] ) ) {
            if ( empty( $data[ 'ci_pass' ] ) ) {
                $this->base_model->alert( 'Không xác định được mật khẩu cần thay đổi', 'warning' );
            }
            $this->wgr_target();

            //
            $this->validation->reset();
            $this->validation->setRules( [
                'ci_pass' => [
                    'label' => Translate::PASSWORD,
                    'rules' => 'required|min_length[5]|max_length[255]',
                    'errors' => [
                        'required' => Translate::REQUIRED,
                        'min_length' => Translate::MIN_LENGTH,
                        'max_length' => Translate::MAX_LENGTH,
                    ],
                ]
            ] );
            if ( !$this->validation->run( $data ) ) {
                $this->set_validation_error( $this->validation->getErrors(), $this->form_target );
                //die( __CLASS__ . ':' . __LINE__ );
            }

            // cập nhật mật khẩu mới cho user
            $this->user_model->update_member( $id, [
                'ci_pass' => $data[ 'ci_pass' ],
            ] );

            //
            $this->base_model->alert( 'Cập nhật mật khẩu mới thành công' );
        }

        //
        $data_update = [];
        foreach ( $data as $k => $v ) {
            if ( !in_array( $k, $this->allow_update ) ) {
                continue;
            }
            $data_update[ $k ] = $v;
        }
        //print_r( $data_update );

        //
        if ( empty( $data_update ) ) {
            $this->base_model->alert( 'Không xác định được thông tin cần thay đổi', 'warning' );
        }

        // cập nhật thông tin mới cho user
        $this->user_model->update_member( $id, $data_update );

        /*
         * lưu thông tin đăng nhập mới vào session
         */
        //print_r( $this->session_data );
        foreach ( $data as $k => $v ) {
            $this->session_data[ $k ] = $v;
        }
        //print_r( $this->session_data );
        $data = $this->sync_login_data( $this->session_data );
        //print_r( $data );
        $this->base_model->set_ses_login( $data );

        //
        $this->base_model->alert( 'Cập nhật thông tin tài khoản thành công' );
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
            // xóa cache theo user để các chức năng liên quan đến user có thể tái sử dụng
            $has_cache = $this->base_model->dcache( $this->user_model->key_cache( $this->current_user_id ) );
            //echo 'Using cache delete Matching Total clear: ' . $has_cache . '<br>' . "\n";
            //die( __CLASS__ . ':' . __LINE__ );

            //
            session_destroy();
            //$this->session->destroy();

            // xóa cookie lưu ID đăng nhập
            //delete_cookie( $this->wrg_cookie_login_key );

            //
            return redirect()->to( base_url( 'guest/login' ) );
        }
    }
}