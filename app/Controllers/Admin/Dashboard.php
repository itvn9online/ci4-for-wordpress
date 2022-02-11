<?php
namespace App\ Controllers\ Admin;

use App\ Libraries\ UsersType;

class Dashboard extends Optimize {
    // danh sách các thư mục trong quá trình unzip file -> lật ngược lại mới xóa được thư mục
    private $dir_re_cache = [];
    // danh sách các file copy từ cache sang thư mục code
    private $file_re_cache = [];
    // link download code từ github
    private $link_download_github = 'https://github.com/itvn9online/ci4-for-wordpress/blob/main/ci4-for-wordpress.zip?raw=true';

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
        //$this->unzip_ci4_for_wordpress();
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

        // optimize code
        $this->optimize_css_js();

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
        //die( __FILE__ . ':' . __LINE__ );

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
        $upload_to_cache = false;
        if ( @!file_put_contents( $upload_path . 'test_permission.txt', time() ) ) {
            $upload_to_cache = true;

            $upload_path = WRITEPATH . 'updates/';

            // tạo thư mục nếu chưa có
            if ( !is_dir( $upload_path ) ) {
                mkdir( $upload_path, DEFAULT_DIR_PERMISSION )or die( 'ERROR create dir (' . basename( __FILE__ ) . ':' . __FUNCTION__ . ':' . __LINE__ . ')! ' . $upload_path );
                chmod( $upload_path, DEFAULT_DIR_PERMISSION );
            }
        } else {
            unlink( $upload_path . 'test_permission.txt' );
        }
        //die( $upload_path );

        //
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
                //die( __FILE__ . ':' . __LINE__ );

                // xóa các file zip cũ đi
                $this->cleanup_zip( $upload_path, 'Không xóa được file ZIP cũ trước khi upload file mới' );

                //
                $file->move( $upload_path, $file_name, true );

                //
                if ( !file_exists( $file_path ) ) {
                    $this->base_model->alert( 'Upload thất bại! Không xác định được file sau khi upload', 'error' );
                }
                chmod( $file_path, DEFAULT_FILE_PERMISSION );

                // giải nén sau khi upload
                $this->after_unzip_code( $file_path, $upload_path, $upload_to_cache );
            } else {
                throw new\ RuntimeException( $file->getErrorString() . '(' . $file->getError() . ')' );
            }
        }

        //
        //die( __FILE__ . ':' . __LINE__ );
        die( '<script>top.done_submit_update_code();</script>' );
    }

    // xóa file zip sau khi xử lý code
    private function cleanup_zip( $upload_path, $msg ) {
        foreach ( glob( $upload_path . '*.zip' ) as $filename ) {
            //echo $filename . '<br>' . "\n";

            //
            if ( is_file( $filename ) ) {
                if ( !$this->MY_unlink( $filename ) ) {
                    $this->base_model->alert( $msg, 'error' );
                }
            }
        }
    }

    private function rmdir_from_cache( $upload_path ) {
        //die( $upload_path );
        foreach ( glob( $upload_path . '*' ) as $filename ) {
            if ( is_dir( $filename ) ) {
                $this->dir_re_cache[] = $filename;

                //
                $filename = rtrim( $filename, '/' ) . '/';
                //echo $filename . '<br>' . "\n";

                //
                $this->rmdir_from_cache( $filename );
            }
        }
    }

    private function unzip_from_cache( $upload_path ) {
        //die( $upload_path );

        // xử lý các file đặc biệt -> ví dụ: .htaccess
        foreach ( glob( $upload_path . '.*' ) as $filename ) {
            if ( is_file( $filename ) ) {
                //echo $filename . '<br>' . "\n";
                //unlink( $filename );

                //
                $this->file_re_cache[] = $filename;
            }
        }

        // xử lý các file thông thường
        foreach ( glob( $upload_path . '*' ) as $filename ) {
            if ( is_file( $filename ) ) {
                //echo $filename . '<br>' . "\n";
                //unlink( $filename );

                //
                $this->file_re_cache[] = $filename;
            } else if ( is_dir( $filename ) ) {
                $filename = rtrim( $filename, '/' ) . '/';
                //echo $filename . '<br>' . "\n";
                $this->unzip_from_cache( $filename );
            }
        }
    }

    // chức năng upload file code zip lên host và giải nén -> update code
    public function download_code() {
        // kiểm tra phiên bản code xem có khác nhau không
        if ( file_get_contents( APPPATH . 'VERSION', 1 ) == file_get_contents( 'https://raw.githubusercontent.com/itvn9online/ci4-for-wordpress/main/app/VERSION', 1 ) ) {
            $this->base_model->alert( 'Download thất bại! Phiên bản của bạn đang là bản mới nhất', 'warning' );
        }

        //
        $upload_path = PUBLIC_HTML_PATH;
        //echo $upload_path . '<br>' . "\n";

        // với 1 số host, chỉ upload được vào thư mục có permission 777 -> cache
        $upload_to_cache = false;
        if ( @!file_put_contents( $upload_path . 'test_permission.txt', time() ) ) {
            $upload_to_cache = true;

            $upload_path = WRITEPATH . 'updates/';

            // tạo thư mục nếu chưa có
            if ( !is_dir( $upload_path ) ) {
                mkdir( $upload_path, DEFAULT_DIR_PERMISSION )or die( 'ERROR create dir (' . basename( __FILE__ ) . ':' . __FUNCTION__ . ':' . __LINE__ . ')! ' . $upload_path );
                chmod( $upload_path, DEFAULT_DIR_PERMISSION );
            }
        } else {
            unlink( $upload_path . 'test_permission.txt' );
        }

        //
        $this->cleanup_zip( $upload_path, 'Không xóa được file ZIP cũ trước khi upload file mới' );
        //die( $upload_path );

        //
        $file_path = $upload_path . explode( '?', basename( $this->link_download_github ) )[ 0 ];

        //
        $file_model = new\ App\ Models\ File();
        if ( $download_file->download_file( $file_path, $this->link_download_github ) === true ) {
            chmod( $file_path, DEFAULT_FILE_PERMISSION );

            // giải nén sau khi upload
            $this->after_unzip_code( $file_path, $upload_path, $upload_to_cache );
        } else {
            $this->base_model->alert( 'Upload thất bại! Không xác định được file sau khi upload', 'error' );
        }
        //die( $file_path );

        //
        //die( __FILE__ . ':' . __LINE__ );
        die( '<script>top.done_submit_update_code();</script>' );
    }

    // giải nén sau khi upload
    private function after_unzip_code( $file_path, $upload_path, $upload_to_cache ) {
        $filename = '';
        if ( $this->MY_unzip( $file_path, $upload_path ) === TRUE ) {
            $this->cleanup_zip( $upload_path, 'Không xóa được file ZIP sau khi giải nén code' );

            // nếu là giải nén trong cache -> copy file sang thư mục public
            if ( $upload_to_cache === true ) {
                //die( $upload_path );

                //
                $file_model = new\ App\ Models\ File();

                /*
                 * copy file
                 */
                $this->file_re_cache = [];
                $this->unzip_from_cache( $upload_path );

                /*
                 * xóa thư mục sau khi update file thành công
                 */
                $this->dir_re_cache = [];
                $this->rmdir_from_cache( $upload_path );

                // tạo thư mục nếu chưa có -> tạo trước khi lật ngược mảng
                foreach ( $this->dir_re_cache as $dir ) {
                    $dir = str_replace( $upload_path, PUBLIC_HTML_PATH, $dir );

                    //
                    if ( !is_dir( $dir ) ) {
                        echo 'Create dir: ' . $dir . '<br>' . "\n";
                        $file_model->create_dir( $dir );
                    }
                }

                // tạo kết nối qua FTP
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

                //
                //print_r( $this->dir_re_cache );
                $this->dir_re_cache = array_reverse( $this->dir_re_cache );
                //print_r( $this->dir_re_cache );
                foreach ( $this->dir_re_cache as $dir ) {
                    echo $dir . '<br>' . "\n";
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
        $this->teamplate_admin[ 'content' ] = view( 'admin/update_view', array(
            'link_download_github' => $this->link_download_github
        ) );
        return view( 'admin/admin_teamplate', $this->teamplate_admin );
    }
}