<?php
//require_once 'application/controllers/Layout.php';
namespace App\ Controllers\ Admin;

use App\ Controllers\ Layout;

// Libraries
use App\ Libraries\ AdminMenu;
use App\ Libraries\ CommentType;
use App\ Libraries\ UsersType;
//use App\ Libraries\ MyImage;

//
class Admin extends Layout {
    //public $user_group_list = array();

    protected $body_class = '';

    // admin thì không cần nạp header
    public $preload_header = false;

    public function __construct() {
        parent::__construct();

        //
        if ( $this->current_user_id <= 0 ) {
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
        $this->post_model = new\ App\ Models\ PostAdmin();

        //
        $this->teamplate_admin = [
            'arr_admin_menu' => $this->admin_menu(),
            'session_data' => $this->session_data,
            'body_class' => $this->body_class,
            // các biến mà view con cần sử dụng thì cho vào view trung gian này
            'header' => view( 'admin/header_view', array(
                'base_model' => $this->base_model,
                //'menu_model' => $this->menu_model,
                //'option_model' => $this->option_model,
                'post_model' => $this->post_model,
                'term_model' => $this->term_model,
                //'lang_model' => $this->lang_model,

                //
                'debug_enable' => $this->debug_enable,
                'session_data' => $this->session_data,
            ) ),
        ];
    }

    public function index() {
        //echo debug_backtrace()[ 1 ][ 'class' ] . '\\ ' . debug_backtrace()[ 1 ][ 'function' ] . '<br>' . "\n";
        echo 'Controller index not found! <br>' . "\n";
        die( basename( __FILE__ ) . ':' . __LINE__ );
    }

    // chức năng này sẽ kiểm tra quyền truy cập 1 module nào đó theo từng tài khoản -> truyền vào controller class -> role -> xác định theo role
    protected function check_permision( $role ) {
        // role này chính là tên controller của admin -> kiểm tra xem có file này không
        //echo $role . '<br>' . "\n";
        $role = $this->get_class_name( $role );
        //echo $role . '<br>' . "\n";

        //
        $this->body_class = strtolower( $role );

        //
        $session_data = $this->session_data;
        // TEST
        //$session_data[ 'member_type' ] = UsersType::AUTHOR;
        //print_r( $session_data );

        // khâu kiểm tra quyền không cần đối với tài khoản admin
        if ( $session_data[ 'member_type' ] == UsersType::ADMIN ) {
            return true;
        }

        //
        $check_file = __DIR__ . '/' . $role . '.php';
        //echo $check_file . '<br>' . "\n";
        // nếu không tồn tại -> báo lỗi luôn
        if ( !file_exists( $check_file ) ) {
            die( 'Role not found!' );
        }

        // chuyển role về chữ thường
        $role = $this->body_class;
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

    public function testCode() {
        // test chức năng xử lý ảnh của codeigniter 4
        //MyImage::quality( PUBLIC_PUBLIC_PATH . 'upload/2021/11/002.jpg', PUBLIC_PUBLIC_PATH . 'upload/2021/11/002-test-quality.jpg' );
        //MyImage::crop( PUBLIC_PUBLIC_PATH . 'upload/2021/11/002.jpg', PUBLIC_PUBLIC_PATH . 'upload/2021/11/002-test-crop.jpg' );
        //MyImage::resize( PUBLIC_PUBLIC_PATH . 'upload/2021/11/002.jpg', PUBLIC_PUBLIC_PATH . 'upload/2021/11/002-test-resize.jpg', 0, 150 );
        //MyImage::watermark( PUBLIC_PUBLIC_PATH . 'upload/2021/11/002.jpg', PUBLIC_PUBLIC_PATH . 'upload/2021/11/002-test-watermark.jpg' );
    }
}