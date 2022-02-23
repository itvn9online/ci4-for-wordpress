<?php

namespace App\ Models;

//
//use App\ Libraries\ DeletedStatus;

//
class User extends UserMeta {
    public function __construct() {
        parent::__construct();
    }

    private function sync_pass( $data ) {
        // nếu có pass -> tiến hành đồng bộ pass
        if ( isset( $data[ 'ci_pass' ] ) ) {
            // đồng bộ về 1 định dạng pass
            if ( $data[ 'ci_pass' ] != '' ) {
                // tạo mật khẩu cho wordpress
                $data[ 'user_pass' ] = md5( $data[ 'ci_pass' ] );

                // sử dụng hàm md5 tự viết
                //$data[ 'ci_pass' ] = md5( $data[ 'ci_pass' ] );
                $data[ 'ci_pass' ] = $this->base_model->mdnam( $data[ 'ci_pass' ] );
            }
            // hoặc bỏ qua việc cập nhật nếu không có dữ liệu
            else {
                unset( $data[ 'ci_pass' ] );
                if ( isset( $data[ 'user_pass' ] ) ) {
                    unset( $data[ 'user_pass' ] );
                }
            }
        }

        //
        return $data;
    }

    /*
     * check_exist: mặc định sẽ check 1 số dữ liệu xem đã được dùng rồi hay chưa
     */
    public function insert_member( $data, $check_exist = true ) {
        // các dữ liệu mặc định
        $default_data = [
            'user_registered' => date( EBE_DATETIME_FORMAT ),
        ];
        $default_data[ 'last_updated' ] = $default_data[ 'user_registered' ];

        // kiểm tra email đã được sử dụng chưa
        if ( $check_exist === true && $this->check_user_exist( $data[ 'user_email' ] ) === false ) {
            return -1;
        }

        //
        if ( !isset( $data[ 'user_login' ] ) || $data[ 'user_login' ] == '' ) {
            //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
            //echo $data[ 'user_email' ] . '<br>' . "\n";
            $data[ 'user_login' ] = $this->generate_user_login( $data[ 'user_email' ] );
            //echo $data[ 'user_login' ] . '<br>' . "\n";
        }
        $data[ 'user_login' ] = $this->check_user_login_exist( $data[ 'user_login' ] );
        // mã hóa mật khẩu
        $data = $this->sync_pass( $data );

        //
        foreach ( $default_data as $k => $v ) {
            if ( !isset( $data[ $k ] ) ) {
                $data[ $k ] = $v;
            }
        }

        // insert
        //print_r( $data );
        $result_id = $this->base_model->insert( $this->table, $data, true );

        //
        return $result_id;
    }

    /*
     * check_exist: mặc định sẽ check 1 số dữ liệu xem đã được dùng rồi hay chưa
     */
    public function update_member( $id, $data, $where = [], $check_exist = true ) {
        if ( isset( $data[ 'user_login' ] ) ) {
            if ( $data[ 'user_login' ] == '' ) {
                if ( isset( $data[ 'user_email' ] ) && $data[ 'user_email' ] != '' ) {
                    $data[ 'user_login' ] = $this->generate_user_login( $data[ 'user_email' ] );
                    $data[ 'user_login' ] = $this->check_user_login_exist( $data[ 'user_login' ] );
                } else {
                    return 'User login is empty!';
                }
            } else {
                // kiểm tra email này đã có ai dùng chưa
                $check__exist = $this->check_another_user_by( $id, 'user_login', $data[ 'user_login' ] );

                if ( $check__exist !== true ) {
                    return $check__exist;
                }
            }
        }
        if ( !isset( $data[ 'last_updated' ] ) || $data[ 'last_updated' ] == '' ) {
            $data[ 'last_updated' ] = date( EBE_DATETIME_FORMAT );
        }

        // mã hóa mật khẩu
        $data = $this->sync_pass( $data );

        // nếu có email
        if ( $check_exist === true && isset( $data[ 'user_email' ] ) ) {
            // email không được để trống
            if ( $data[ 'user_email' ] != '' ) {
                // kiểm tra email này đã có ai dùng chưa
                $check__exist = $this->check_another_user_by( $id, 'user_email', $data[ 'user_email' ] );

                if ( $check__exist !== true ) {
                    return $check__exist;
                }
            }
            // trống thì return luôn
            else {
                return 'User email is empty!';
            }
        }

        //
        $where[ 'ID' ] = $id;
        //print_r( $data );
        //print_r( $where );

        //
        $result_update = $this->base_model->update_multiple( $this->table, $data, $where, [
            'debug_backtrace' => debug_backtrace()[ 1 ][ 'function' ]
        ] );

        //
        return $result_update;
    }
}