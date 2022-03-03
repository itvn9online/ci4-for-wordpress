<?php
namespace App\ Controllers;

//
use App\ Libraries\ UsersType;
use App\ Libraries\ DeletedStatus;

//
class Guest extends Csrf {
    public function __construct() {
        parent::__construct();

        //
        $this->validation = \Config\ Services::validation();

        //
        //echo $_SERVER[ 'REQUEST_METHOD' ] . '<br>' . "\n";
        // quá trình submit bắt buộc phải có các tham số sau -> chống tắt javascript
        if ( $_SERVER[ 'REQUEST_METHOD' ] == 'POST' ) {
            //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
            if ( !isset( $_POST[ '__wgr_request_from' ] ) || !isset( $_POST[ '__wgr_nonce' ] ) ) {
                //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
                // chuyển tham số này thành true -> dùng chung với captcha
                $this->has_captcha = true;
            }
        }
    }

    public function index() {
        $login_redirect = DYNAMIC_BASE_URL;
        //die( $login_redirect );

        if ( $this->current_user_id > 0 ) {
            return $this->done_action_login( $login_redirect );
        }

        //
        if ( !empty( $this->MY_post( 'username' ) ) ) {
            $this->wgr_target();

            // xem có phải nhập mã captcha không -> khi đăng nhập sai quá nhiều lần -> bắt buộc phải nhập captcha
            if ( $this->base_model->check_faild_login() > 0 ) {
                //die( __CLASS__ . ':' . __LINE__ );
                $this->check_required_captcha();
            }
            //die( __CLASS__ . ':' . __LINE__ );

            //
            if ( $this->has_captcha === false ) {
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
                    $this->set_validation_error( $this->validation->getErrors(), $this->form_target );
                } else {
                    //die( __CLASS__ . ':' . __LINE__ );

                    //
                    if ( $this->checkaccount() === true ) {
                        $session_data = $this->base_model->get_ses_login();

                        //
                        /*
                        if ( function_exists( 'set_cookie' ) ) {
                            //die( $this->wrg_cookie_login_key );
                            //set_cookie( $this->wrg_cookie_login_key, $this->session_data[ 'ID' ] . '|' . time() . '|' . md5( $this->wrg_cookie_login_key . $this->session_data[ 'ID' ] ), 3600, '.' . $_SERVER[ 'HTTP_HOST' ], '/' );
                        }
                        */

                        //
                        if ( isset( $_REQUEST[ 'login_redirect' ] ) && $_REQUEST[ 'login_redirect' ] != '' ) {
                            $login_redirect = $_REQUEST[ 'login_redirect' ];
                        } else if ( !empty( $session_data ) && isset( $session_data[ 'userLevel' ] ) && $session_data[ 'userLevel' ] > 0 ) {
                            $login_redirect = base_url( CUSTOM_ADMIN_URI );
                        }
                        //die( $login_redirect );

                        //
                        return $this->done_action_login( $login_redirect );
                    }
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
        $user_pass = md5( $this->MY_post( 'password' ) );
        $ci_pass = $this->base_model->mdnam( $this->MY_post( 'password' ) );

        //$result = $this->user_model->login( $username, $user_pass );
        $result = $this->user_model->login( $username, $user_pass, $ci_pass );
        if ( empty( $result ) ) {
            $sql = $this->base_model->select( 'ID', 'users', array(
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

            // thêm số lần đăng nhập sai
            $this->base_model->push_faild_login();

            //
            if ( empty( $sql ) ) {
                $this->base_model->msg_error_session( 'Email đăng nhập không chính xác', $this->form_target );
            } else {
                $this->base_model->msg_error_session( 'Mật khẩu đăng nhập không chính xác', $this->form_target );
            }

            //
            return false;
        }

        // reset lại captcha login
        $this->base_model->reset_faild_login();

        // tài khoản bị KHÓA
        $result[ 'user_status' ] *= 1;
        if ( $result[ 'user_status' ] != UsersType::FOR_DEFAULT * 1 ) {
            // kiểm tra xem đã đến hạn mở khóa chưa
            if ( $result[ 'user_status' ] > 0 ) {
                $auto_unlock = strtotime( $result[ 'last_login' ] ) + ( $result[ 'user_status' ] * 3600 );

                // nếu đã hết hạn bị KHÓA -> tự động mở khóa cho tài khoản
                if ( $auto_unlock < time() ) {
                    $this->user_model->update_member( $result[ 'ID' ], [
                        'user_status' => UsersType::FOR_DEFAULT,
                    ] );

                    //
                    $this->base_model->msg_session( 'Mở khóa tài khoản thành công! Vui lòng đăng nhập lại.' );

                    return $this->done_action_login();
                }
                $auto_unlock = date( EBE_DATETIME_FORMAT, $auto_unlock );
            } else {
                $auto_unlock = '<strong>Không xác định</strong>. Vui lòng liên hệ admin.';
            }
            $this->base_model->msg_error_session( 'Tài khoản đang bị <strong>' . UsersType::listStatus( $result[ 'user_status' ] ) . '</strong>! Thời gian mở khóa: ' . $auto_unlock, $this->form_target );
            return false;
        }
        // tài khoản bị XÓA
        else if ( $result[ 'is_deleted' ] * 1 != DeletedStatus::FOR_DEFAULT * 1 ) {
            $this->base_model->msg_error_session( 'Tài khoản không tồn tại trong hệ thống! Vui lòng liên hệ admin.', $this->form_target );
            return false;
        }

        //
        //print_r( $result );
        //die( __CLASS__ . ':' . __LINE__ );
        $result = $this->sync_login_data( $result );
        $result[ 'user_activation_key' ] = session_id();

        //
        $result_id = $this->user_model->update_member( $result[ 'ID' ], [
            'last_login' => date( EBE_DATETIME_FORMAT ),
            'user_activation_key' => $result[ 'user_activation_key' ],
        ] );

        //
        $this->base_model->set_ses_login( $result );

        //
        return true;
    }

    public function register() {
        $login_redirect = DYNAMIC_BASE_URL;

        if ( $this->current_user_id > 0 ) {
            return $this->done_action_login( $login_redirect );
        }

        //
        $data = $this->MY_post( 'data' );
        if ( !empty( $data ) && isset( $data[ 'email' ] ) ) {
            $this->wgr_target();

            // đăng ký tài khoản bắt buộc phải có captcha
            $this->check_required_captcha();

            //
            if ( $this->has_captcha === false ) {
                $this->validation->reset();
                $this->validation->setRule( 'email', 'Email', 'required|min_length[5]|max_length[255]|valid_email' );
                $this->validation->setRule( 'password', 'Mật khẩu', 'required|min_length[5]|max_length[255]' );
                //$this->validation->setRule( 'password2', 'Mật khẩu xác nhận', 'required|min_length[5]|max_length[255]' );

                // mật khẩu xác nhận
                if ( $data[ 'password' ] != $data[ 'password2' ] ) {
                    $this->base_model->msg_error_session( 'Mật khẩu xác nhận không chính xác', $this->form_target );
                }
                // lấy lỗi trả về nếu có
                else if ( !$this->validation->run( $data ) ) {
                    $this->set_validation_error( $this->validation->getErrors(), $this->form_target );
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
                    //$data[ 'created' ] = date( EBE_DATETIME_FORMAT );
                    //print_r( $data );
                    //die( 'register' );

                    //
                    //$insert = $this->base_model->insert( 'tbl_user', $data, true );
                    $insert = $this->user_model->insert_member( $data );
                    if ( $insert < 0 ) {
                        $this->base_model->msg_error_session( 'Email đã được sử dụng', $this->form_target );
                    } else if ( $insert !== false ) {
                        $this->base_model->msg_session( 'Đăng ký tài khoản thành công!' );

                        return $this->done_action_login( base_url( 'guest/login' ) );
                    } else {
                        $this->base_model->msg_error_session( 'Lỗi đăng ký tài khoản', $this->form_target );
                    }
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
        $data = $this->MY_post( 'data' );
        if ( !empty( $data ) && isset( $data[ 'email' ] ) ) {
            $this->wgr_target();
            $this->checking_captcha();

            //
            if ( $this->has_captcha === false ) {
                //print_r( $data );
                //die( __CLASS__ . ':' . __LINE__ );
                $this->validation->reset();
                $this->validation->setRule( 'email', 'Email', 'required|min_length[5]|max_length[255]|valid_email' );

                //
                if ( !$this->validation->run( $data ) ) {
                    $this->set_validation_error( $this->validation->getErrors(), $this->form_target );
                } else {
                    if ( $this->check_resetpass() === true ) {
                        // daidq -----> tính năng này để sau, đang bận chạy deadline nên vất đây đã
                        $this->base_model->msg_session( 'Gửi email lấy lại mật khẩu thành công' );

                        return $this->done_action_login();
                    }
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