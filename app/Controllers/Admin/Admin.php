<?php
namespace App\ Controllers\ Admin;

use App\ Controllers\ Ajax;

// Libraries
use App\ Libraries\ AdminMenu;
use App\ Libraries\ CommentType;
use App\ Libraries\ UsersType;
//use App\ Libraries\ MyImage;

//
class Admin extends Ajax {
    //public $user_group_list = array();

    protected $body_class = '';

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
            $j -= 5;
            if ( $j < 0 ) {
                break;
            }
        }
        //print_r( $arr );

        // thêm custom menu nếu có
        if ( function_exists( 'register_admin_menu' ) ) {
            foreach ( register_admin_menu() as $k => $v ) {
                // nếu menu đã tồn tại -> gộp vào menu đó
                if ( isset( $arr[ $k ] ) ) {
                    foreach ( $v[ 'arr' ] as $k2 => $v2 ) {
                        $arr[ $k ][ 'arr' ][ $k2 ] = $v2;
                    }
                }
                // nếu không -> tạo mới luôn
                else {
                    $arr[ $k ] = $v;
                }
            }
        }

        //
        return $arr;
    }

    // kiểm tra và tạo file htaccess chặn truy cập ở các thư mục không phải public
    protected function auto_create_htaccess_deny( $remove_file = false ) {
        // các thư mục được phép truy cập
        $arr_allow_dir = [
            // thư mục upload ảnh
            'upload',
            // thư mục chứa các file tĩnh để người dùng sử dụng
            'public',
            // hướng dẫn sử dụng của codeigniter
            'user_guide',
            // thư mục chứa file design của từng website
            'design',
        ];

        //
        foreach ( glob( PUBLIC_HTML_PATH . '*' ) as $filename ) {
            // chỉ kiểm tra đối với thư mục
            if ( is_dir( $filename ) ) {
                //echo $filename . '<br>' . "\n";
                //echo basename( $filename ) . '<br>' . "\n";
                $f = $filename . '/.htaccess';

                // không xử lý file htaccess trong các thư mục được nêu tên
                if ( !in_array( basename( $filename ), $arr_allow_dir ) ) {
                    // cập nhật lại nội dung file htaccess
                    if ( $remove_file === true && file_exists( $f ) ) {
                        $this->MY_unlink( $f );
                    }

                    //
                    if ( !file_exists( $f ) ) {
                        echo $f . '<br>' . "\n";

                        //
                        $this->base_model->_eb_create_file( $f, trim( '

<IfModule authz_core_module>
	Require all denied
</IfModule>
<IfModule !authz_core_module>
	Deny from all
</IfModule>

# too many redirect for all extensions -> in apache, openlitespeed
RewriteRule ^(.*) ' . DYNAMIC_BASE_URL . '$1 [F]

#

' ), [
                            'set_permission' => 0644,
                            'ftp' => 1,
                        ] );
                    }
                }
            }
            //include $filename;
        }
    }

    // reset sesion login -> giữ trạng thái đăng nhập nếu không dùng máy tính mà vẫn bật trình duyệt
    public function admin_logged() {
        $this->base_model->alert( 'CẢNH BÁO! Hãy đăng xuất admin khi không sử dụng!', 'warning' );
    }

    public function testCode() {
        // test chức năng xử lý ảnh của codeigniter 4
        //MyImage::quality( PUBLIC_PUBLIC_PATH . 'upload/2021/11/002.jpg', PUBLIC_PUBLIC_PATH . 'upload/2021/11/002-test-quality.jpg' );
        //MyImage::crop( PUBLIC_PUBLIC_PATH . 'upload/2021/11/002.jpg', PUBLIC_PUBLIC_PATH . 'upload/2021/11/002-test-crop.jpg' );
        //MyImage::resize( PUBLIC_PUBLIC_PATH . 'upload/2021/11/002.jpg', PUBLIC_PUBLIC_PATH . 'upload/2021/11/002-test-resize.jpg', 0, 150 );
        //MyImage::watermark( PUBLIC_PUBLIC_PATH . 'upload/2021/11/002.jpg', PUBLIC_PUBLIC_PATH . 'upload/2021/11/002-test-watermark.jpg' );
    }
}