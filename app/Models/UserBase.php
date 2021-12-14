<?php

namespace App\ Models;

//
use App\ Libraries\ DeletedStatus;

//
class UserBase extends EbModel {
    public $table = 'wp_users';
    public $primaryKey = 'ID';

    public $metaTable = 'wp_usermeta';
    public $metaKey = 'umeta_id';

    public function __construct() {
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

    // kiểm tra user có hay chưa theo 1 thuộc tính unique
    public function check_another_user_by( $id, $key, $val ) {
        // lấy dữ liệu trong db
        $check_exist = $this->base_model->select( 'ID', $this->table, array(
            // các kiểu điều kiện where
            'ID !=' => $id,
            $key => $val,
        ), array(
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 2,
            'limit' => 1
        ) );

        //
        if ( !empty( $check_exist ) ) {
            return $key . ' has been using by another user #' . $check_exist[ 'ID' ];
        }
        //
        return true;
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
        $builder->orderBy( 'ID', 'DESC' );
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
        $sql = $this->base_model->select( 'ID', $this->table, array(
            // các kiểu điều kiện where
            // mặc định
            $col => $email,
            // kiểm tra email đã được sử dụng rồi hay chưa thì không cần kiểm tra trạng thái XÓA -> vì có thể user này đã bị xóa vĩnh viễn
            //'is_deleted' => DeletedStatus::FOR_DEFAULT,
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