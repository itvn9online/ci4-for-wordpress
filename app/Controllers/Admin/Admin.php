<?php
//require_once 'application/controllers/Layout.php';
namespace App\ Controllers\ Admin;

use App\ Controllers\ Layout;

// Libraries
use App\ Libraries\ AdminMenu;
use App\ Libraries\ CommentType;
use App\ Libraries\ UsersType;

//
class Admin extends Layout {
    //public $user_group_list = array();

    public function __construct() {
        parent::__construct( false );

        //
        if ( empty( $this->session_data ) ) {
            $redirect_to = DYNAMIC_BASE_URL . 'guest/login?login_redirect=' . urlencode( base_url( $_SERVER[ 'REQUEST_URI' ] ) ) . '&remove_parameter=';
            //die( $redirect_to );
            //redirect()->to( $redirect_to );
            die( header( 'Location: ' . $redirect_to ) );
        }
        //print_r( $this->session_data );
        //var_dump( $this->session_data );

        // nếu không có quyền admin -> báo lỗi nếu đang vào admin
        if ( $this->session_data[ 'userLevel' ] != UsersType::ADMIN_LEVEL ) {
            die( '404 error line ' . basename( __FILE__ ) . ':' . __LINE__ );
        }

        //
        $this->teamplate_admin = [];
        $this->teamplate_admin[ 'header' ] = view( 'admin/header_view', array(
            'base_model' => $this->base_model,
            'debug_enable' => $this->debug_enable,
        ) );

        $this->teamplate_admin = [
            'arr_admin_menu' => $this->admin_menu(),
            'session_data' => $this->session_data,
        ];
    }

    function index() {
        //echo debug_backtrace()[ 1 ][ 'class' ] . '\\ ' . debug_backtrace()[ 1 ][ 'function' ] . '<br>' . "\n";
        echo 'Controller index not found! <br>' . "\n";
        die( basename( __FILE__ ) . ':' . __LINE__ );
    }

    // chức năng này sẽ kiểm tra quyền truy cập 1 module nào đó theo từng tài khoản -> truyền vào controller class -> role -> xác định theo role
    function check_permision( $role ) {
        $session_data = $this->session_data;
        // TEST
        //$session_data[ 'member_type' ] = UsersType::AUTHOR;
        //print_r( $session_data );

        // khâu kiểm tra quyền không cần đối với tài khoản admin
        if ( $session_data[ 'member_type' ] == UsersType::ADMIN ) {
            return true;
        }

        // role này chính là tên controller của admin -> kiểm tra xem có file này không
        //echo $role . '<br>' . "\n";
        $role = basename( str_replace( '\\', '/', $role ) );
        //echo $role . '<br>' . "\n";
        $check_file = __DIR__ . '/' . $role . '.php';
        //echo $check_file . '<br>' . "\n";
        // nếu không tồn tại -> báo lỗi luôn
        if ( !file_exists( $check_file ) ) {
            die( 'Role not found!' );
        }

        // chuyển role về chữ thường
        $role = strtolower( $role );
        //echo $role . '<br>' . "\n";
        if ( !in_array( $role, UsersType::role( $session_data[ 'member_type' ] ) ) ) {
            die( 'Permission ERROR!' );
        }

        //
        return true;
    }

    /*
    function checkslug() {
        $slug = $this->MY_post( 'slug' );
        $checkslug = $this->base_model->checkslug( $slug );
        if ( $checkslug == 'true' ) {
            echo 'false';
        } else {
            echo 'true';
        }
    }
    */

    private function admin_menu() {
        $arr = AdminMenu::menu_list();
        //print_r( $arr );

        // tạo số thứ tự để sắp xếp menu
        $j = 100;
        foreach ( $arr as $k => $v ) {
            $arr[ $k ][ 'order' ] = $j;
            $j -= 10;
            if ( $j < 0 ) {
                break;
            }
        }
        //print_r( $arr );

        //
        return $arr;
    }
}