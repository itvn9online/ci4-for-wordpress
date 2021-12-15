<?php
//require_once __DIR__ . '/Layout.php';
namespace App\ Controllers;

//
use App\ Libraries\ UsersType;
use App\ Libraries\ DeletedStatus;

//
class Guest extends Layout {
    public function __construct() {
        parent::__construct();

        //
        $this->validation = \Config\ Services::validation();
    }

    public function index() {
        $session_data = $this->session_data;
        $login_redirect = DYNAMIC_BASE_URL;
        //die( $login_redirect );

        if ( !empty( $session_data ) && isset( $session_data[ 'userID' ] ) && $session_data[ 'userID' ] > 0 ) {
            return redirect()->to( $login_redirect );
        }

        //
        if ( !empty( $this->MY_post( 'username' ) ) ) {
            //print_r( $_POST );
            $this->validation->reset();
            $this->validation->setRules( [
                'username' => 'required',
                'password' => 'required|min_length[6]',
            ] );
            //$this->validation->setRule( 'username', 'Tài khoản', 'required' );
            //$this->validation->setRule( 'password', 'Mật khẩu', 'required' );

            //
            if ( !$this->validation->run( $_POST ) ) {
                $this->set_validation_error( $this->validation->getErrors() );
            } else {
                //die( __FILE__ . ':' . __LINE__ );

                //
                if ( $this->checkaccount() === true ) {
                    $session_data = $this->session->get( 'admin' );

                    //
                    if ( isset( $_REQUEST[ 'login_redirect' ] ) && $_REQUEST[ 'login_redirect' ] != '' ) {
                        $login_redirect = $_REQUEST[ 'login_redirect' ];
                    } else if ( isset( $session_data[ 'userLevel' ] ) && $session_data[ 'userLevel' ] > 0 ) {
                        $login_redirect = base_url( CUSTOM_ADMIN_URI );
                    }
                    //die( $login_redirect );
                    return redirect()->to( $login_redirect );
                }
            }
        }
        //echo 'dfgd fg';
        //return view( 'admin/login_view' );

        //
        $this->teamplate[ 'main' ] = view( 'admin/login_view', array(
            //'option_model' => $this->option_model,

            'seo' => $this->seo( 'Đăng nhập', __FUNCTION__ ),
            'breadcrumb' => '',
            //'cateByLang' => $cateByLang,
            //'serviceByLang' => $serviceByLang,
        ) );
        //print_r( $this->teamplate );
        return view( 'layout_view', $this->teamplate );
    }
    public function login() {
        return $this->index();
    }

    protected function checkaccount() {
        $username = $this->MY_post( 'username' );
        $password = md5( $this->MY_post( 'password' ) );

        //$result = $this->user_model->login( $username, $password );
        $result = $this->user_model->login( $username, $password );
        if ( empty( $result ) ) {
            $sql = $this->base_model->select( 'ID', 'wp_users', array(
                // các kiểu điều kiện where
                'user_email' => $username,
            ), array(
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                'offset' => 0,
                'limit' => 1
            ) );

            //
            if ( empty( $sql ) ) {
                $this->session->setFlashdata( 'msg_error', 'Email đăng nhập không chính xác' );
            } else {
                $this->session->setFlashdata( 'msg_error', 'Mật khẩu đăng nhập không chính xác' );
            }
            return false;
        }

        //
        if ( $result[ 'user_status' ] * 1 != UsersType::FOR_DEFAULT * 1 ) {
            $this->session->setFlashdata( 'msg_error', 'Tài khoản đang bị hạn chế đăng nhập! Vui lòng liên hệ admin.' );
            return false;
        }
        //
        else if ( $result[ 'is_deleted' ] * 1 != DeletedStatus::FOR_DEFAULT * 1 ) {
            $this->session->setFlashdata( 'msg_error', 'Tài khoản không tồn tại trong hệ thống! Vui lòng liên hệ admin.' );
            return false;
        }

        //
        //print_r( $result );
        //die( __FILE__ . ':' . __LINE__ );
        $result[ 'user_pass' ] = '';
        $result[ 'ci_pass' ] = '';
        // hỗ trợ phiên bản code cũ -> tạo thêm dữ liệu tương ứng
        $result[ 'userID' ] = $result[ 'ID' ];
        $result[ 'userName' ] = $result[ 'display_name' ];
        $result[ 'userEmail' ] = $result[ 'user_email' ];
        // quyền admin
        $arr_admin_group = [
            UsersType::AUTHOR,
            UsersType::MOD,
            UsersType::ADMIN,
        ];
        if ( in_array( $result[ 'member_type' ], $arr_admin_group ) ) {
            $result[ 'userLevel' ] = UsersType::ADMIN_LEVEL;
        } else {
            $result[ 'userLevel' ] = UsersType::GUEST_LEVEL;
        }
        //print_r( $result );
        //die( __FILE__ . ':' . __LINE__ );

        //
        $result_id = $this->user_model->update_member( $result[ 'ID' ], [
            'last_login' => date( 'Y-m-d H:i:s' ),
        ] );

        //
        $this->session->set( 'admin', $result );
        /*
        $sess_array = array();
        $this->session->set_userdata( 'admin', array(
            'userID' => $result->id,
            'userName' => $result->username,
            'userEmail' => $result->email,
            'userLevel' => $result->level,
            'userGroup' => $result->user_group,
        ) );
        */
        return true;
    }

    public function register() {
        $session_data = $this->session_data;
        $login_redirect = DYNAMIC_BASE_URL;

        if ( !empty( $session_data ) && isset( $session_data[ 'userID' ] ) && $session_data[ 'userID' ] > 0 ) {
            return redirect()->to( $login_redirect );
        }

        //
        $data = $this->MY_post( 'data' );
        if ( !empty( $data ) && isset( $data[ 'email' ] ) ) {
            $this->validation->setRule( 'email', 'Email', 'required|min_length[5]|max_length[255]|valid_email' );
            $this->validation->setRule( 'password', 'Mật khẩu', 'required|min_length[5]|max_length[255]' );
            $this->validation->setRule( 'password2', 'Mật khẩu xác nhận', 'required|min_length[5]|max_length[255]' );

            // mật khẩu xác nhận
            if ( $data[ 'password' ] != $data[ 'password2' ] ) {
                $this->session->setFlashdata( 'msg_error', 'Mật khẩu xác nhận không chính xác' );
            }
            // lấy lỗi trả về nếu có
            else if ( !$this->validation->run( $data ) ) {
                $this->set_validation_error( $this->validation->getErrors() );
            }
            // tiến hành đăng ký tài khoản
            else {
                $data[ 'user_email' ] = $data[ 'email' ];
                $data[ 'ci_pass' ] = $data[ 'password' ];
                $data[ 'member_type' ] = UsersType::GUEST;
                //$data[ 'username' ] = str_replace( '.', '', str_replace( '@', '', $data[ 'email' ] ) );
                //$data[ 'password' ] = md5( $data[ 'password' ] );
                //$data[ 'level' ] = '0';
                //$data[ 'status' ] = '1';
                //$data[ 'accept_mail' ] = 0;
                //$data[ 'avatar' ] = base_url( 'frontend/images/icon-user-not-login.png' );
                //$data[ 'created' ] = date( 'Y-m-d H:i:s' );
                //print_r( $data );
                //die( 'register' );

                //
                //$insert = $this->base_model->insert( 'tbl_user', $data, true );
                $insert = $this->user_model->insert_member( $data );
                if ( $insert > 0 ) {
                    $this->session->setFlashdata( 'msg', 'Đăng ký thành công' );
                    //header( 'Location:' . base_url( 'signin' ) );
                    return redirect()->to( base_url( 'guest/login' ) );
                } else if ( $insert === -1 ) {
                    $this->session->setFlashdata( 'msg_error', 'Email đã được sử dụng' );
                } else {
                    $this->session->setFlashdata( 'msg_error', 'Lỗi đăng ký tài khoản' );
                }
            }
        }

        //
        $this->teamplate[ 'main' ] = view( 'admin/register_view', array(
            'seo' => $this->seo( 'Đăng ký', __FUNCTION__ ),
            'breadcrumb' => '',
            //'cateByLang' => $cateByLang,
            //'serviceByLang' => $serviceByLang,
        ) );
        return view( 'layout_view', $this->teamplate );
    }

    protected function check_email_exist() {
        $data = $this->MY_post( 'data' );
        return $this->user_model->check_user_exist( $data[ 'email' ] );
    }

    public function resetpass() {
        /*
        $session_data = $this->session_data;
        $login_redirect = DYNAMIC_BASE_URL;

        if ( !empty( $session_data ) && isset( $session_data[ 'userID' ] ) && $session_data[ 'userID' ] > 0 ) {
            return redirect( $login_redirect, 'refresh' );
        }
        */

        //
        $data = $this->MY_post( 'data' );
        if ( !empty( $data ) && isset( $data[ 'email' ] ) ) {
            //print_r( $data );
            //die( __FILE__ . ':' . __LINE__ );
            $this->validation->setRule( 'email', 'Email', 'required|min_length[5]|max_length[255]|valid_email' );

            //
            if ( !$this->validation->run( $data ) ) {
                $this->set_validation_error( $this->validation->getErrors() );
            } else {
                if ( $this->check_resetpass() === true ) {
                    // daidq -----> tính năng này để sau, đang bận chạy deadline nên vất đây đã
                    $this->session->setFlashdata( 'msg', 'Gửi email lấy lại mật khẩu thành công' );
                }
            }
        }

        //
        $this->teamplate[ 'main' ] = view( 'admin/resetpass_view', array(
            'seo' => $this->seo( 'Lấy lại mật khẩu', __FUNCTION__ ),
            'breadcrumb' => '',
            //'cateByLang' => $cateByLang,
            //'serviceByLang' => $serviceByLang,
        ) );
        return view( 'layout_view', $this->teamplate );
    }

    protected function check_resetpass() {
        $data = $this->MY_post( 'data' );
        return $this->user_model->check_resetpass( $data[ 'email' ] );
    }

    protected function seo( $name, $canonical ) {
        return $this->base_model->default_seo( $name, $canonical );
    }

}