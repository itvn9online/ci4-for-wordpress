<?php
namespace App\ Controllers\ Admin;

use App\ Libraries\ UsersType;

class Dashboard extends Optimize {
    // danh sách các thư mục trong quá trình unzip file -> lật ngược lại mới xóa được thư mục
    private $dir_re_cache = [];
    // danh sách các file copy từ cache sang thư mục code
    private $file_re_cache = [];
    // link download code từ github
    //private $link_download_github = 'https://github.com/itvn9online/ci4-for-wordpress/blob/main/ci4-for-wordpress.zip?raw=true';
    private $link_download_github = 'https://github.com/itvn9online/ci4-for-wordpress/archive/refs/heads/main.zip';

    public function __construct() {
        parent::__construct();

        // kiểm tra quyền truy cập của tài khoản hiện tại
        $this->check_permision( __CLASS__ );

        //
        $this->f_env = PUBLIC_HTML_PATH . '.env';
        $this->f_backup_env = PUBLIC_HTML_PATH . 'writable/.env-bak';

        /*
         * các thư mục khi reset code sẽ có thể xóa bỏ để thay thế mà không ảnh hưởng đến website
         */
        // thư mục app
        $this->app_dir = PUBLIC_HTML_PATH . 'app';
        $this->app_deleted_dir = $this->app_dir . '-deleted';
        //die( $this->app_deleted_dir );
        // thư mục themes
        $this->public_dir = PUBLIC_HTML_PATH . 'public/themes';
        $this->public_deleted_dir = $this->public_dir . '-deleted';
        //die( $this->public_deleted_dir );

        // list các thư mục sẽ xóa code vào đây để dùng cho tiện
        $this->cleanup_deleted_code = [
            $this->app_deleted_dir,
            $this->public_deleted_dir,
        ];

        // tham số dùng để copy lại file config -> bắt buộc phải có thì mới chạy được web
        $this->config_deleted_file = $this->app_deleted_dir . '/Config/Database.php';
        //echo $this->config_deleted_file . '<br>' . "\n";
        $this->config_file = str_replace( $this->app_deleted_dir, $this->app_dir, $this->config_deleted_file );
        //echo $this->config_file . '<br>' . "\n";

        //
        //echo THEMEPATH . '<br>' . "\n";
        //echo basename( THEMEPATH ) . '<br>' . "\n";
    }

    public function index() {
        echo '<!-- ' . "\n";
        $this->vendor_sync();
        //$this->unzip_ci4_for_wordpress();
        $this->cleanup_old_cache( 24 * 3600 );
        echo ' -->';

        //
        $this->auto_create_htaccess_deny();

        //
        $current_dbname = \Config\ Database::connect()->database;

        //
        $last_enabled_debug = 0;
        // tự động tắt chế độ debug sau 7 ngày
        $auto_disable_debug = WEEK;
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

        // optimize code
        $this->optimize_css_js();

        //
        $client_ip = $this->request->getIPAddress();
        //$client_ip = ( isset( $_SERVER[ 'HTTP_X_REAL_IP' ] ) ) ? $_SERVER[ 'HTTP_X_REAL_IP' ] : $_SERVER[ 'REMOTE_ADDR' ];


        // TEST xem cache có chạy hay không -> gọi đến cache được gọi trong dashboard để xem có NULL hay không
        $check_cache_active = $this->base_model->scache( 'auto_sync_table_column' );
        //echo $check_cache_active . '<br>' . "\n";
        //echo $base_model->dcache( 'auto_sync_table_column' ) . '<br>' . "\n";


        // kiểm tra file robots.txt
        $robots_txt = PUBLIC_PUBLIC_PATH . 'robots.txt';
        // mặc định là không có file robots
        $robots_exist = 0;
        if ( file_exists( $robots_txt ) ) {
            // có thì mới bắt đầu kiểm tra
            $robots_exist = 1;

            // nếu không xác định được nội dung cần thiết trong robot txt -> cảnh báo
            if ( strpos( file_get_contents( $robots_txt, 1 ), DYNAMIC_BASE_URL ) === false ) {
                $robots_exist = 2;
            }
        }

        //
        //print_r( $_SERVER );
        //print_r( $_SESSION );
        //echo mysqli_get_client_info();
        //echo mysql_get_server_info();
        //print_r( opcache_get_status() );

        //
        $this->teamplate_admin[ 'content' ] = view( 'admin/dashboard_view', array(
            //'topPostHighestView' => $topPostHighestView,
            'session_data' => $this->session_data,
            'request_ip' => $client_ip,
            //'debug_enable' => $this->debug_enable,
            'last_enabled_debug' => $last_enabled_debug,
            'auto_disable_debug' => $auto_disable_debug,
            'current_dbname' => $current_dbname,
            'f_env' => $this->f_env,
            'f_backup_env' => $this->f_backup_env,
            'robots_exist' => $robots_exist,
            'check_cache_active' => $check_cache_active,
            'user_type' => [
                'admin' => UsersType::ADMIN,
            ],
        ) );
        return view( 'admin/admin_teamplate', $this->teamplate_admin );
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
                chmod( $f_backup, DEFAULT_FILE_PERMISSION );
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

    private function unzip_ci4_for_wordpress() {
        $file_zip = PUBLIC_HTML_PATH . 'ci4-for-wordpress.zip';

        //
        if ( file_exists( $file_zip ) ) {
            echo $file_zip . '<br>' . "\n";

            //
            if ( $this->MY_unzip( $file_zip, PUBLIC_HTML_PATH ) === TRUE ) {
                $this->MY_unlink( $file_zip );
            }
        }
    }

    public function unzip_system() {
        $system_zip = PUBLIC_HTML_PATH . 'system.zip';
        if ( !file_exists( $system_zip ) ) {
            $this->base_model->alert( 'Không tồn tại file ' . basename( $system_zip ), 'error' );
        }

        //
        $current_ci_version = \CodeIgniter\ CodeIgniter::CI_VERSION;
        //echo $current_ci_version . '<br>' . "\n";

        // tên thư mục sẽ backup system cũ
        $to = PUBLIC_HTML_PATH . 'system-' . $current_ci_version;
        if ( is_dir( $to ) ) {
            $to .= '-' . date( 'Ymd-His' );
            //$this->base_model->alert( 'Vui lòng XÓA ' . basename( $to ) . ' backup trước khi tiếp tục', 'error' );
        }

        // đổi tên thư mục system -> backup
        rename( PUBLIC_HTML_PATH . 'system', $to );

        // giải nén system zip
        if ( $this->MY_unzip( $system_zip, PUBLIC_HTML_PATH ) === TRUE ) {
            $this->MY_unlink( $system_zip );
            //$this->base_model->alert( 'DONE! giải nén system.zip thành công' );
            die( '<script>top.done_unzip_system();</script>' );
        }

        //
        //die( __CLASS__ . ':' . __LINE__ );

        //
        $this->base_model->alert( 'LỖI trong quá trình giải nén system.zip', 'error' );

        //
        return true;
    }

    private function unzip_code() {
        //die( PUBLIC_HTML_PATH );

        // 1 số định dạng file không cho phép upload trực tiếp
        $allow_upload = [
            'zip'
        ];

        //
        $upload_path = PUBLIC_HTML_PATH;
        //echo $upload_path . '<br>' . "\n";

        // với 1 số host, chỉ upload được vào thư mục có permission 777 -> cache
        $upload_via_ftp = $this->using_via_ftp( $upload_path );
        if ( $upload_via_ftp === true ) {
            $upload_path = WRITEPATH . 'updates/';

            // tạo thư mục nếu chưa có
            if ( !is_dir( $upload_path ) ) {
                $this->mk_dir( $upload_path, __CLASS__ . ':' . __LINE__ );
            }
        }
        //die( $upload_path );
        echo $upload_path . ':' . basename( __FILE__ ) . ':' . __LINE__ . '<br>' . "\n";

        //
        $file_path = '';
        if ( $zipfile = $this->request->getFiles() ) {
            //print_r( $zipfile );

            //
            $file = $zipfile[ 'upload_code' ];
            //print_r( $file );

            //
            if ( $file->isValid() && !$file->hasMoved() ) {
                $file_name = $file->getName();
                //echo $file_name . '<br>' . "\n";
                $file_name = $this->base_model->_eb_non_mark_seo( $file_name );
                $file_name = sanitize_filename( $file_name );
                //echo $file_name . '<br>' . "\n";

                //
                $file_ext = $file->guessExtension();
                //echo $file_ext . '<br>' . "\n";
                $file_ext = strtolower( $file_ext );
                //echo $file_ext . '<br>' . "\n";

                //
                $file_path = $upload_path . $file_name;
                //echo $file_path . '<br>' . "\n";

                // kiểm tra định dạng file
                $mime_type = $file->getMimeType();
                //echo $mime_type . '<br>' . "\n";

                // nếu có kiểm duyệt định dạng file -> chỉ các file trong này mới được upload
                if ( !in_array( $file_ext, $allow_upload ) ) {
                    $this->base_model->alert( 'Định dạng file chưa được hỗ trợ! Hiện chỉ hỗ trợ định dạng .ZIP', 'error' );
                }
                //die( __CLASS__ . ':' . __LINE__ );

                // xóa các file zip cũ đi
                $this->cleanup_zip( $upload_path, 'Không xóa được file ZIP cũ trước khi upload file mới' );

                //
                $file->move( $upload_path, $file_name, true );

                //
                if ( !file_exists( $file_path ) ) {
                    $this->base_model->alert( 'Upload thất bại! Không xác định được file sau khi upload', 'error' );
                }
                chmod( $file_path, DEFAULT_FILE_PERMISSION );
                //die( __CLASS__ . ':' . __LINE__ );

                // giải nén sau khi upload
                $this->after_unzip_code( $file_path, $upload_path, $upload_via_ftp );

                /*
                 * dọn dẹp code dư thừa sau khi giải nén (nếu tồn tại thư mục này)
                 */
                if ( is_dir( $this->app_deleted_dir ) || is_dir( $this->public_deleted_dir ) ) {
                    $this->cleanup_deleted_dir( $this->cleanup_deleted_code, $upload_via_ftp );
                }
            } else {
                throw new\ RuntimeException( $file->getErrorString() . '(' . $file->getError() . ')' );
            }
        }

        // đồng bộ lại thirdparty và database
        ///echo basename( $file_path ) . '<br>';
        if ( strpos( basename( $file_path ), 'ci4-for-wordpress' ) !== false ) {
            $this->vendor_sync( false );
        }

        //
        //die( __CLASS__ . ':' . __LINE__ );
        die( '<script>top.done_submit_update_code("' . basename( $file_path ) . '");</script>' );
    }

    // xóa file zip sau khi xử lý code
    private function cleanup_zip( $upload_path, $msg ) {
        echo $upload_path . ':' . basename( __FILE__ ) . ':' . __LINE__ . '<br>' . "\n";
        foreach ( glob( rtrim( $upload_path, '/' ) . '/*.zip' ) as $filename ) {
            echo $filename . ':' . basename( __FILE__ ) . ':' . __LINE__ . '<br>' . "\n";

            //
            if ( is_file( $filename ) ) {
                if ( !$this->MY_unlink( $filename ) ) {
                    $this->base_model->alert( $msg, 'error' );
                }
            }
        }
    }

    private function rmdir_from_cache( $upload_path ) {
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
        //die( $upload_path );
        //echo $upload_path . '<br>' . "\n";

        // xử lý các file đặc biệt -> ví dụ: .git
        foreach ( glob( rtrim( $upload_path, '/' ) . '/.*' ) as $filename ) {
            if ( is_dir( $filename ) ) {
                //echo $filename . '<br>' . "\n";
                $check_dot = basename( $filename );

                // không lấy các thư mục đặc biệt
                if ( $check_dot == '.' || $check_dot == '..' ) {
                    continue;
                }
                //echo $check_dot . '<br>' . "\n";

                //
                $this->dir_re_cache[] = $filename;

                //
                //echo $filename . '<br>' . "\n";

                //
                $this->rmdir_from_cache( $filename );
            }
        }

        // xử lý các thư mục thông thường
        foreach ( glob( rtrim( $upload_path, '/' ) . '/*' ) as $filename ) {
            if ( is_dir( $filename ) ) {
                $this->dir_re_cache[] = $filename;
                //echo $filename . '<br>' . "\n";

                //
                $this->rmdir_from_cache( $filename );
            }
        }
    }

    private function get_all_file_in_folder( $upload_path ) {
        //die( $upload_path );

        // xử lý các file đặc biệt -> ví dụ: .htaccess
        foreach ( glob( rtrim( $upload_path, '/' ) . '/.*' ) as $filename ) {
            if ( is_file( $filename ) ) {
                //echo $filename . '<br>' . "\n";
                //unlink( $filename );

                //
                $this->file_re_cache[] = $filename;
            } else if ( is_dir( $filename ) ) {
                //echo $filename . '<br>' . "\n";
                $check_dot = basename( $filename );

                // không lấy các thư mục đặc biệt
                if ( $check_dot == '.' || $check_dot == '..' ) {
                    continue;
                }
                //echo $check_dot . '<br>' . "\n";

                //
                $this->get_all_file_in_folder( $filename );
            }
        }

        // xử lý các file thông thường
        foreach ( glob( rtrim( $upload_path, '/' ) . '/*' ) as $filename ) {
            if ( is_file( $filename ) ) {
                //echo $filename . '<br>' . "\n";
                //unlink( $filename );

                //
                $this->file_re_cache[] = $filename;
            } else if ( is_dir( $filename ) ) {
                //echo $filename . '<br>' . "\n";
                $this->get_all_file_in_folder( $filename );
            }
        }
    }

    // chức năng download code từ github nhưng kèm tính năng xóa code cũ -> nạp code mới hoàn toàn
    public function reset_code() {
        return $this->download_code( true );
    }

    // chức năng upload file code zip lên host và giải nén -> update code
    public function download_code( $reset_code = false ) {
        // kiểm tra phiên bản code xem có khác nhau không
        /*
        if ( file_get_contents( APPPATH . 'VERSION', 1 ) == file_get_contents( 'https://raw.githubusercontent.com/itvn9online/ci4-for-wordpress/main/app/VERSION', 1 ) ) {
            $this->base_model->alert( 'Download thất bại! Phiên bản của bạn đang là bản mới nhất', 'warning' );
        }
        */

        //
        $upload_path = PUBLIC_HTML_PATH;
        //echo $upload_path . '<br>' . "\n";

        // chức năng download file main zip từ github
        $from_main_github = true;

        // với 1 số host, chỉ upload được vào thư mục có permission 777 -> cache
        $upload_via_ftp = $this->using_via_ftp( $upload_path );

        //
        if ( $from_main_github === true || $upload_via_ftp === true ) {
            $upload_path = WRITEPATH . 'updates/';

            // tạo thư mục nếu chưa có
            if ( !is_dir( $upload_path ) ) {
                $this->mk_dir( $upload_path, __CLASS__ . ':' . __LINE__ );
            }
        }

        //
        $this->cleanup_zip( $upload_path, 'Không xóa được file ZIP cũ trước khi upload file mới' );
        //die( $upload_path );

        //
        $file_path = $upload_path . explode( '?', basename( $this->link_download_github ) )[ 0 ];
        //die( $file_path );

        //
        $file_model = new\ App\ Models\ File();
        if ( $file_model->download_file( $file_path, $this->link_download_github ) === true ) {
            chmod( $file_path, DEFAULT_FILE_PERMISSION );

            /*
             * Khi có tham số reset code -> đổi tên thư mục app, public để upload code từ đầu
             */
            if ( $reset_code === true ) {
                //echo $this->app_deleted_dir . '<br>' . "\n";

                //
                if ( is_dir( $this->app_deleted_dir ) ) {
                    die( 'DIR EXIST! ' . $this->app_deleted_dir );
                } else {
                    if ( !$this->MY_rename( $this->app_dir, $this->app_deleted_dir ) ) {
                        die( 'ERROR rename! ' . $this->app_dir );
                    }
                    if ( !$this->MY_rename( $this->public_dir, $this->public_deleted_dir ) ) {
                        die( 'ERROR rename! ' . $this->public_dir );
                    }

                    // tạo thư mục thông qua FTP
                    if ( $upload_via_ftp === true ) {
                        $file_model->create_dir( $this->app_dir );
                        $file_model->create_dir( $this->public_dir );
                    }
                    // tạo mặc định
                    else {
                        $this->mk_dir( $this->app_dir, __CLASS__ . ':' . __LINE__, 0755 );
                        $this->mk_dir( $this->public_dir, __CLASS__ . ':' . __LINE__, 0755 );
                    }

                    //
                    //die( __CLASS__ . ':' . __LINE__ );
                }
            }

            // giải nén sau khi upload
            $this->after_unzip_code( $file_path, $upload_path, $upload_via_ftp, $from_main_github );

            // nếu tồn tại file config -> copy nó sang thư mục chính
            if ( file_exists( $this->config_deleted_file ) && !file_exists( $this->config_file ) ) {
                // không copy được file config thì restore code lại
                if ( !$this->MY_copy( $this->config_deleted_file, $this->config_file ) ) {
                    $this->MY_rename( $this->app_deleted_dir, $this->app_dir );
                    $this->MY_rename( $this->public_deleted_dir, $this->public_dir );
                }
            }
        } else {
            $this->base_model->alert( 'Download thất bại! Không xác định được file sau khi download', 'error' );
        }
        //die( $file_path );

        //
        //die( __CLASS__ . ':' . __LINE__ );
        die( '<script>top.done_submit_update_code("' . basename( $file_path ) . '");</script>' );
    }

    /*
     * giải nén sau khi upload
     * main_zip: dành cho unzip code từ main github
     */
    private function after_unzip_code( $file_path, $upload_path, $upload_via_ftp, $main_zip = false ) {
        $filename = '';
        if ( $this->MY_unzip( $file_path, $upload_path ) === TRUE ) {
            $this->cleanup_zip( $upload_path, 'Không xóa được file ZIP sau khi giải nén code' );
            //echo $upload_path . '<br>' . "\n";
            //var_dump( $upload_via_ftp );
            //die( __CLASS__ . ':' . __LINE__ );

            // nếu là giải nén trong cache -> copy file sang thư mục public
            if ( $main_zip === true || $upload_via_ftp === true ) {
                //var_dump( $main_zip );
                //var_dump( $upload_via_ftp );
                if ( $main_zip === true ) {
                    $upload_path .= 'ci4-for-wordpress-main/';
                }
                //die( $upload_path );

                //
                $file_model = new\ App\ Models\ File();

                // lấy danh sách các file để còn copy
                $this->file_re_cache = [];
                // chỉ update các file trong thư mục chỉ định
                if ( $main_zip === true ) {
                    $this->get_all_file_in_folder( $upload_path . 'app/' );
                    $this->get_all_file_in_folder( $upload_path . 'public/' );
                } else {
                    $this->get_all_file_in_folder( $upload_path );
                }

                // lấy danh sách các thư mục để lát còn XÓA
                $this->dir_re_cache = [];
                $this->rmdir_from_cache( $upload_path );

                //
                //print_r( $this->file_re_cache );
                //print_r( $this->dir_re_cache );
                //die( $upload_path );

                // tạo thư mục nếu chưa có -> tạo trước khi lật ngược mảng
                foreach ( $this->dir_re_cache as $dir ) {
                    $dir = str_replace( $upload_path, PUBLIC_HTML_PATH, $dir );

                    //
                    if ( !is_dir( $dir ) ) {
                        echo 'Create dir: ' . $dir . '<br>' . "\n";
                        //continue;

                        // tạo thư mục thông qua FTP
                        if ( $upload_via_ftp === true ) {
                            $file_model->create_dir( $dir );
                        }
                        // tạo mặc định
                        else {
                            $this->mk_dir( $dir, __CLASS__ . ':' . __LINE__ );
                        }
                    }
                }

                /*
                 * copy file bằng php thông thường -> nhanh
                 */
                if ( $upload_via_ftp !== true ) {
                    // chuyển file
                    foreach ( $this->file_re_cache as $file ) {
                        echo $file . '<br>' . "\n";

                        //
                        $to = str_replace( $upload_path, PUBLIC_HTML_PATH, $file );
                        echo $to . '<br>' . "\n";

                        // đổi tên file -> tương đương với copy và unlink
                        if ( !rename( $file, $to ) ) {
                            // không được thì thử copy xong xóa
                            if ( copy( $file, $to ) ) {
                                unlink( $file );
                            }
                        }
                    }
                }
                /*
                 * chuyển file thông qua tạo kết nối qua FTP
                 */
                else {
                    $check_dir = $file_model->root_dir();
                    $has_ftp = false;
                    if ( $check_dir === true ) {
                        echo 'ftp server: ' . $file_model->ftp_server . '<br>' . "\n";
                        echo 'base dir: ' . $file_model->base_dir . '<br>' . "\n";

                        // tạo kết nối
                        $conn_id = ftp_connect( $file_model->ftp_server );

                        // đăng nhập
                        if ( $file_model->base_dir != '' ) {
                            if ( ftp_login( $conn_id, FTP_USER, FTP_PASS ) ) {
                                $has_ftp = true;
                            }
                        }
                    } else {
                        echo 'FTP ERROR! ' . $check_dir . '<br>' . PHP_EOL;
                    }

                    // chuyển file
                    foreach ( $this->file_re_cache as $file ) {
                        echo $file . '<br>' . "\n";

                        //
                        $to = str_replace( $upload_path, PUBLIC_HTML_PATH, $file );
                        if ( $has_ftp === true ) {
                            // nếu trong chuỗi file không có root dir -> báo lỗi
                            if ( strpos( $to, '/' . $file_model->base_dir . '/' ) !== false ) {
                                $to = strstr( $to, '/' . $file_model->base_dir . '/' );
                            }

                            //
                            if ( ftp_put( $conn_id, $to, $file, FTP_BINARY ) ) {
                                echo '<em>' . $to . '</em><br>' . "\n";
                            } else {
                                echo 'ERROR:' . __LINE__ . ' <strong>' . $to . '</strong><br>' . "\n";
                            }
                        } else {
                            echo 'ERROR:' . __LINE__ . ' <strong>' . $to . '</strong><br>' . "\n";
                        }

                        // xong thì xóa luôn file
                        unlink( $file );
                    }

                    // close the connection
                    if ( $check_dir === true ) {
                        ftp_close( $conn_id );
                    }
                }

                /*
                 * dọn dẹp các file dư thừa sau khi copy xong
                 */
                if ( $main_zip === true ) {
                    $this->file_re_cache = [];
                    $this->get_all_file_in_folder( $upload_path );
                    //print_r( $this->file_re_cache );

                    // xóa file -> thì mới xóa được thư mục
                    foreach ( $this->file_re_cache as $file ) {
                        echo 'un-link: ' . $file . '<br>' . "\n";
                        unlink( $file );
                    }

                    //
                    //die( $upload_path );
                }

                /*
                 * dọp dẹp thư mục sau khi xử lý xong các file
                 */
                //print_r( $this->dir_re_cache );
                $this->dir_re_cache = array_reverse( $this->dir_re_cache );
                //print_r( $this->dir_re_cache );
                foreach ( $this->dir_re_cache as $dir ) {
                    echo 'rm dir: ' . $dir . '<br>' . "\n";
                    rmdir( $dir );
                }
            }
        }
    }

    // chức năng upload file code zip lên host và giải nén -> update code
    public function update_code() {
        if ( !empty( $this->MY_post( 'data' ) ) ) {
            $this->unzip_code();
        }

        //
        /*
        $theme_deleted_exist = false;
        if ( basename( THEMEPATH ) != 'echbayfour' ) {
            $theme_deleted_exist = $this->check_deleted_exist( [
                dirname( $this->public_deleted_dir ) . '/' . basename( THEMEPATH ),
            ] );
        }
        */

        //
        $this->teamplate_admin[ 'content' ] = view( 'admin/update_view', array(
            // xác định các thư mục deleted code có tồn tại không
            'app_deleted_exist' => $this->check_deleted_exist(),
            // xác định xem thư mục theme cũ có tồn tại không
            //'theme_deleted_exist' => $theme_deleted_exist,
            'link_download_github' => $this->link_download_github
        ) );
        return view( 'admin/admin_teamplate', $this->teamplate_admin );
    }

    private function check_deleted_exist( $arr = NULL ) {
        //
        if ( $arr === NULL ) {
            $arr = $this->cleanup_deleted_code;
        }

        //
        $result = false;
        foreach ( $arr as $dir ) {
            if ( is_dir( $dir ) ) {
                $result = true;
                break;
            }
        }
        return $result;
    }

    // tạo thư mục
    private function mk_dir( $dir, $msg, $permision = DEFAULT_DIR_PERMISSION ) {
        mkdir( $dir, $permision )or die( 'ERROR create dir (' . $msg . ')! ' . $dir );
        chmod( $dir, $permision );
    }

    private function cleanup_deleted_dir( $dirs, $upload_via_ftp ) {
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
        //print_r( $dirs );
        //die( __CLASS__ . ':' . __LINE__ );

        // lấy danh sách file và thư mục để XÓA
        $this->file_re_cache = [];
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
        //print_r( $this->file_re_cache );
        $this->dir_re_cache = [];
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
        //print_r( $this->dir_re_cache );
        foreach ( $dirs as $v ) {
            if ( !is_dir( $v ) ) {
                continue;
            }
            //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
            //echo $v . '<br>' . "\n";

            // file
            $this->get_all_file_in_folder( $v );

            // dir
            $this->dir_re_cache[] = $v;
            $this->rmdir_from_cache( $v );
        }
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
        //print_r( $this->file_re_cache );
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
        //print_r( $this->dir_re_cache );
        $this->dir_re_cache = array_reverse( $this->dir_re_cache );
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
        //print_r( $this->dir_re_cache );
        //die( __CLASS__ . ':' . __LINE__ );

        // xóa bằng php thường
        if ( $upload_via_ftp !== true ) {
            foreach ( $this->file_re_cache as $file ) {
                if ( !file_exists( $file ) ) {
                    continue;
                }
                echo $file . '<br>' . "\n";
                unlink( $file );
            }

            //
            foreach ( $this->dir_re_cache as $dir ) {
                if ( !is_dir( $dir ) ) {
                    continue;
                }
                echo $dir . '<br>' . "\n";
                rmdir( $dir );
            }
        }
        // xóa thông qua ftp
        else {
            $file_model = new\ App\ Models\ File();

            //
            $check_dir = $file_model->root_dir();
            $has_ftp = false;
            if ( $check_dir === true ) {
                echo 'ftp server: ' . $file_model->ftp_server . '<br>' . "\n";
                echo 'base dir: ' . $file_model->base_dir . '<br>' . "\n";

                // tạo kết nối
                $conn_id = ftp_connect( $file_model->ftp_server );

                // đăng nhập
                if ( $file_model->base_dir != '' ) {
                    if ( ftp_login( $conn_id, FTP_USER, FTP_PASS ) ) {
                        $has_ftp = true;
                    }
                }
            } else {
                echo 'FTP ERROR! ' . $check_dir . '<br>' . PHP_EOL;
            }

            // XÓA file
            foreach ( $this->file_re_cache as $file ) {
                if ( !file_exists( $file ) ) {
                    continue;
                }
                echo $file . ':' . basename( __FILE__ ) . ':' . __LINE__ . '<br>' . "\n";
                //continue;

                //
                $to = $file;
                if ( $has_ftp === true ) {
                    // nếu trong chuỗi file không có root dir -> báo lỗi
                    if ( strpos( $to, '/' . $file_model->base_dir . '/' ) !== false ) {
                        $to = strstr( $to, '/' . $file_model->base_dir . '/' );
                    }

                    //
                    if ( ftp_delete( $conn_id, $to ) ) {
                        echo '<em>' . $to . '</em><br>' . "\n";
                    } else {
                        echo 'ERROR:' . __LINE__ . ' <strong>' . $to . '</strong><br>' . "\n";
                    }
                } else {
                    echo 'ERROR:' . __LINE__ . ' <strong>' . $to . '</strong><br>' . "\n";
                }
            }

            // XÓA thư mục
            foreach ( $this->dir_re_cache as $dir ) {
                if ( !is_dir( $dir ) ) {
                    continue;
                }
                echo $dir . ':' . basename( __FILE__ ) . ':' . __LINE__ . '<br>' . "\n";
                //continue;

                //
                $to = $dir;
                if ( $has_ftp === true ) {
                    // nếu trong chuỗi dir không có root dir -> báo lỗi
                    if ( strpos( $to, '/' . $file_model->base_dir . '/' ) !== false ) {
                        $to = strstr( $to, '/' . $file_model->base_dir . '/' );
                    }

                    //
                    if ( ftp_rmdir( $conn_id, $to ) ) {
                        echo '<em>' . $to . '</em><br>' . "\n";
                    } else {
                        echo 'ERROR:' . __LINE__ . ' <strong>' . $to . '</strong><br>' . "\n";
                    }
                } else {
                    echo 'ERROR:' . __LINE__ . ' <strong>' . $to . '</strong><br>' . "\n";
                }
            }

            // close the connection
            if ( $check_dir === true ) {
                ftp_close( $conn_id );
            }
        }
    }

    public function restore_code() {
        if ( $this->check_deleted_exist() !== true ) {
            $this->base_model->alert( 'Không tồn tại thư mục deleted', 'error' );
        }

        // xóa code trong thư mục app hiện tại
        $this->cleanup_deleted_dir( [
            $this->app_dir,
            $this->public_dir,
        ], $this->using_via_ftp() );

        // đổi lại tên thư mục
        if ( !$this->MY_rename( $this->app_deleted_dir, $this->app_dir ) ) {
            die( 'ERROR rename! ' . $this->app_deleted_dir );
        }
        if ( !$this->MY_rename( $this->public_deleted_dir, $this->public_dir ) ) {
            die( 'ERROR rename! ' . $this->public_deleted_dir );
        }

        //die(__FILE__.':'.__LINE__);
        die( '<script>top.done_submit_restore_code();</script>' );
    }

    // kiểm tra xem có put file bằng php được hay phải dùng ftp
    private function using_via_ftp( $upload_path = PUBLIC_HTML_PATH ) {
        if ( @!file_put_contents( $upload_path . 'test_permission.txt', time() ) ) {
            return true;
        } else {
            unlink( $upload_path . 'test_permission.txt' );
        }
        return false;
    }

    public function cleanup_matching_cache() {
        $data = $this->MY_post( 'data', '' );

        if ( empty( $data ) || strlen( $data ) < 4 ) {
            $this->base_model->alert( 'Từ khóa khớp dữ liệu quá ngắn', 'error' );
        }

        //
        $this->cleanup_cache( $data . '-' );
        // xóa thêm đối với term
        if ( $data == 'term' ) {
            $this->cleanup_cache( 'get_all_taxonomy-' );
        }

        //
        $this->base_model->alert( 'Xóa cache theo key thành công. Matching: ' . $data );
    }

    public function unzip_thirdparty() {
        $this->vendor_sync( false );
        echo '<script>top.after_unzip_thirdparty();</script>';
        $this->base_model->alert( 'Đồng bộ lại Code bên thứ 3 và Database thành công!' );
    }
}