<?php
//require_once __DIR__ . '/Admin.php';
namespace App\ Controllers\ Admin;

use App\ Libraries\ UsersType;

class Dashboard extends Admin {
    public function __construct() {
        parent::__construct();

        // kiểm tra quyền truy cập của tài khoản hiện tại
        $this->check_permision( __CLASS__ );

        //
        $this->f_env = PUBLIC_HTML_PATH . '.env';
        $this->f_backup_env = PUBLIC_HTML_PATH . 'writable/.env-bak';
    }

    public function index() {
        echo '<!-- ' . "\n";
        $this->vendor_sync();
        echo ' -->';

        //
        $this->auto_create_htaccess_deny();

        //
        $current_dbname = \Config\ Database::connect()->database;

        //
        $last_enabled_debug = 0;
        // tự động tắt chế độ debug sau 7 ngày
        $auto_disable_debug = 24 * 3600 * 7;
        // tự động tắt chế độ debug sau 4 giờ
        $auto_disable_debug = 4 * 3600;
        if ( file_exists( $this->f_env ) ) {
            $last_enabled_debug = filemtime( $this->f_env );
            //echo date( 'r', $last_enabled_debug );

            //
            if ( $last_enabled_debug < time() - $auto_disable_debug ) {
                //echo 'Auto disable debug via .env';
                $this->action_disable_env( $this->f_env, $this->f_backup_env );
            }
        }

        // Nếu chế độ không phải là đang chạy chính thức
        /*
        $debug_enable = false;
        if ( ( ENVIRONMENT !== 'production' ) === true ) {
            //var_dump( ( ENVIRONMENT !== 'production' ) );

            //
            $debug_enable = true;

            // kiểm tra nếu chế độ debug đang được bật -> đưa ra cảnh báo
            //echo PUBLIC_HTML_PATH . '<br>' . "\n";
            //echo PUBLIC_HTML_PATH . '.env';
        }
        */

        //
        $this->teamplate_admin[ 'content' ] = view( 'admin/dashboard_view', array(
            //'topPostHighestView' => $topPostHighestView,
            'session_data' => $this->session_data,
            //'debug_enable' => $this->debug_enable,
            'last_enabled_debug' => $last_enabled_debug,
            'auto_disable_debug' => $auto_disable_debug,
            'current_dbname' => $current_dbname,
            'f_env' => $this->f_env,
            'f_backup_env' => $this->f_backup_env,
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

    // tắt chế độ debug qua .env
    public function disable_env() {
        if ( $this->session_data[ 'member_type' ] != UsersType::ADMIN ) {
            $this->base_model->alert( 'Bạn không có quyền thực hiện thao tác này', 'error' );
        }

        //
        $f = $this->f_env;
        if ( !file_exists( $f ) ) {
            $this->base_model->alert( 'Không tồn tại file ' . basename( $f ), 'error' );
        }

        // END v2
        return $this->action_disable_env( $f, $this->f_backup_env );
    }
    private function action_disable_env( $f, $f_backup, $for_alert = 1 ) {
        // bakup file .env nếu chưa có
        if ( !file_exists( $f_backup ) ) {
            if ( !$this->MY_copy( $f, $f_backup ) ) {
                if ( $for_alert === 1 ) {
                    $this->base_model->alert( 'LỖI! không backup được file ' . basename( $f ), 'error' );
                }
            } else {
                chmod( $f_backup, 0777 );
            }
            if ( !file_exists( $f_backup ) ) {
                if ( $for_alert === 1 ) {
                    $this->base_model->alert( 'LỖI! tồn tại file: ' . basename( $f_backup ), 'error' );
                }
            }
        }

        // xong XÓA file .env
        if ( $this->MY_unlink( $f ) ) {
            if ( $for_alert === 1 ) {
                $this->base_model->alert( '', base_url( CUSTOM_ADMIN_URI ) );
                //$this->base_model->alert( 'TẮT chế độ debug thành công (LƯU TRỮ file ' . basename( $f ) . ')' );
            }
        } else if ( $for_alert === 1 ) {
            $this->base_model->alert( 'LỖI! Không xóa được file ' . basename( $f ), 'error' );
        }

        // END v2
        return true;
    }

    public function enable_env() {
        if ( $this->session_data[ 'member_type' ] != UsersType::ADMIN ) {
            $this->base_model->alert( 'Bạn không có quyền thực hiện thao tác này', 'error' );
        }

        // nếu tồn tại file .env -> bỏ qua
        $f = $this->f_env;
        if ( file_exists( $f ) ) {
            //$this->base_model->alert( 'File đã tồn tại ' . basename( $f ), 'error' );
        }

        // phải tồn tại file .env bak thì mới tiếp tục
        $f_backup = $this->f_backup_env;
        if ( file_exists( $f_backup ) ) {
            // restore lại file env
            if ( $this->MY_copy( $f_backup, $f ) ) {
                $this->base_model->alert( '', base_url( CUSTOM_ADMIN_URI ) );
                //$this->base_model->alert( 'BẬT chế độ debug thành công (copy file ' . basename( $f ) . ')' );
            } else {
                $this->base_model->alert( 'LỖI! không restore được file ' . basename( $f ), 'error' );
            }
        } else {
            $this->base_model->alert( 'Không tồn tại file ' . basename( $f_backup ), 'error' );
        }

        //
        return true;
    }
}