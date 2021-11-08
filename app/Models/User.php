<?php

namespace App\ Models;

//
use App\ Libraries\ DeletedStatus;

//
class User extends EB_Model {
    public $table = 'wp_users';
    protected $primaryKey = 'ID';

    function __construct() {
        parent::__construct();

        //
        $this->session = \Config\ Services::session();
    }

    /*
     * Chức năng này sẽ tạo ra user dựa theo email đăng ký
     */
    function check_user_login_exist( $user_login, $i = 0 ) {
        $user_login = explode( '@', $user_login );
        $user_login = trim( $user_login[ 0 ] );
        if ( $user_login == '' ) {
            die( 'user_login is NULL' );
        }
        //
        $new_user_login = $i > 0 ? $user_login . $i : $user_login;

        //
        $data = $this->base_model->select( '*', $this->table, [
            'user_login' => $new_user_login
        ], array(
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 2,
            'limit' => 1
        ) );
        //print_r( $data );

        //
        if ( empty( $data ) ) {
            return $new_user_login;
        }
        return $this->check_user_login_exist( $user_login, $i + 1 );
    }

    function insert_member( $data ) {
        // các dữ liệu mặc định
        $default_data = [
            'user_registered' => date( 'Y-m-d H:i:s' ),
        ];
        $default_data[ 'last_updated' ] = $default_data[ 'user_registered' ];

        // kiểm tra email đã được sử dụng chưa
        if ( $this->check_user_exist( $data[ 'user_email' ] ) === false ) {
            return -1;
        }

        //
        if ( !isset( $data[ 'user_login' ] ) || $data[ 'user_login' ] == '' ) {
            $data[ 'user_login' ] = $this->check_user_login_exist( $data[ 'user_email' ] );
        }
        // mã hóa mật khẩu
        if ( isset( $data[ 'ci_pass' ] ) && $data[ 'ci_pass' ] != '' ) {
            $data[ 'ci_pass' ] = md5( $data[ 'ci_pass' ] );
            // tạo mật khẩu cho wordpress
            $data[ 'user_pass' ] = $data[ 'ci_pass' ];
        }

        //
        foreach ( $default_data as $k => $v ) {
            if ( !isset( $data[ $k ] ) ) {
                $data[ $k ] = $v;
            }
        }

        // insert post
        //print_r( $data );
        //die( 'fj sfssf sf' );
        $result_id = $this->base_model->insert( $this->table, $data, true );

        if ( $result_id > 0 ) {
            return $result_id;
        }
        return false;
    }

    function update_member( $id, $data, $where = [] ) {
        if ( isset( $data[ 'user_login' ] ) && $data[ 'user_login' ] == '' ) {
            $data[ 'user_login' ] = $this->check_user_login_exist( $data[ 'user_email' ] );
        }
        if ( !isset( $data[ 'last_updated' ] ) || $data[ 'last_updated' ] == '' ) {
            $data[ 'last_updated' ] = date( 'Y-m-d H:i:s' );
        }
        // mã hóa mật khẩu
        if ( isset( $data[ 'ci_pass' ] ) && $data[ 'ci_pass' ] != '' ) {
            $data[ 'ci_pass' ] = md5( $data[ 'ci_pass' ] );
            // tạo mật khẩu cho wordpress
            $data[ 'user_pass' ] = $data[ 'ci_pass' ];
        }

        //
        $where[ $this->primaryKey ] = $id;
        //print_r( $data );
        //print_r( $where );

        //
        $this->base_model->update_multiple( $this->table, $data, $where, [
            'debug_backtrace' => debug_backtrace()[ 1 ][ 'function' ]
        ] );
    }

    public function login( $name, $password, $level = '0' ) {
        $builder = $this->base_model->db->table( $this->table );
        //$builder->select( '*' );

        $builder->groupStart();
        $builder->orWhere( 'user_pass', $password );
        $builder->orWhere( 'ci_pass', $password );
        $builder->groupEnd();

        $builder->groupStart();
        $builder->orWhere( 'user_login', $name );
        $builder->orWhere( 'user_email', $name );
        $builder->groupEnd();

        //
        $builder->orderBy( $this->primaryKey, 'DESC' );
        $builder->limit( 1, 0 );

        $query = $builder->get();
        //print_r( $this->base_model->db->getLastQuery()->getQuery() );
        $a = $query->getResultArray();
        //print_r( $a );
        //die( __FILE__ . ':' . __LINE__ );
        if ( !empty( $a ) ) {
            return $a[ 0 ];
        }
        return false;
    }

    public function check_user_exist( $email, $col = 'user_email', $set_flash = false ) {
        if ( $col == '' ) {
            $col = 'user_email';
        }

        // select dữ liệu từ 1 bảng bất kỳ
        $sql = $this->base_model->select( '*', $this->table, array(
            // các kiểu điều kiện where
            // mặc định
            $col => $email,
            'is_deleted' => DeletedStatus::DEFAULT,
        ), array(
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 2,
            'limit' => 1
        ) );

        // có rồi
        if ( !empty( $sql ) ) {
            if ( $set_flash === true ) {
                $this->session->setFlashdata( 'msg_error', 'Email đã tồn tại !!!' );
            }
            // trả về false
            return false;
        }

        // chưa có -> true
        return true;
    }

    public function check_resetpass( $email ) {
        // chưa có -> báo lỗi
        if ( $this->check_user_exist( $email ) === true ) {
            $this->session->setFlashdata( 'msg_error', 'Email không tồn tại !!!' );
            return false;
        }
        // có thì trả về true
        return true;
    }
}