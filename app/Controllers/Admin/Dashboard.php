<?php
//require_once __DIR__ . '/Admin.php';
namespace App\ Controllers\ Admin;

class Dashboard extends Admin {
    public function __construct() {
        parent::__construct();

        // kiểm tra quyền truy cập của tài khoản hiện tại
        $this->check_permision( __CLASS__ );
    }
    public function index() {
        echo '<!-- ' . "\n";
        $this->vendor_sync();
        echo ' -->';

        //
        $this->auto_create_htaccess_deny();

        //
        $this->teamplate_admin[ 'content' ] = view( 'admin/dashboard_view', array(
            //'topPostHighestView' => $topPostHighestView
        ) );
        return view( 'admin/admin_teamplate', $this->teamplate_admin );
    }

    // kiểm tra và tạo file htaccess chặn truy cập ở các thư mục không phải public
    private function auto_create_htaccess_deny() {
        // các thư mục được phép truy cập
        $arr_allow_dir = [
            // thư mục upload ảnh
            'upload',
            // thư mục chứa các file tĩnh để người dùng sử dụng
            'public',
            // hướng dẫn sử dụng của codeigiter
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

                if ( !file_exists( $f ) && !in_array( basename( $filename ), $arr_allow_dir ) ) {
                    echo $f . '<br>' . "\n";

                    //
                    $this->base_model->_eb_create_file( $f, trim( '
<IfModule authz_core_module>
	Require all denied
</IfModule>
<IfModule !authz_core_module>
	Deny from all
</IfModule>' ), [
                        'set_permission' => 0644,
                    ] );
                }
            }
            //include $filename;
        }
    }
}