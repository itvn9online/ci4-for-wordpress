<?php
namespace App\ Controllers\ Admin;

use App\ Libraries\ UsersType;

class Dashboard extends Optimize {
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
                foreach ( glob( $upload_path . '*.zip' ) as $filename ) {
                    //echo $filename . '<br>' . "\n";

                    //
                    if ( is_file( $filename ) ) {
                        if ( !$this->MY_unlink( $filename ) ) {
                            $this->base_model->alert( 'Không xóa được file ZIP cũ trước khi upload file mới', 'error' );
                        }
                    }
                }

                //
                $file->move( $upload_path, $file_name, true );

                //
                if ( !file_exists( $file_path ) ) {
                    $this->base_model->alert( 'Upload thất bại! Không xác định được file sau khi upload', 'error' );
                }
                chmod( $file_path, DEFAULT_FILE_PERMISSION );

                // giải nén sau khi upload
                $filename = '';
                if ( $this->MY_unzip( $file_path, $upload_path ) === TRUE ) {
                    foreach ( glob( $upload_path . '*.zip' ) as $filename ) {
                        //echo $filename . '<br>' . "\n";

                        //
                        if ( is_file( $filename ) ) {
                            if ( !$this->MY_unlink( $filename ) ) {
                                $this->base_model->alert( 'Không xóa được file ZIP sau khi giải nén code', 'error' );
                            }
                        }
                    }
                }
            } else {
                throw new\ RuntimeException( $file->getErrorString() . '(' . $file->getError() . ')' );
            }
        }

        //
        //die( __FILE__ . ':' . __LINE__ );
        die( '<script>top.done_submit_update_code();</script>' );
    }

    // chức năng upload file code zip lên host và giải nén -> update code
    public function update_code() {
        if ( !empty( $this->MY_post( 'data' ) ) ) {
            $this->unzip_code();
        }

        //
        $this->teamplate_admin[ 'content' ] = view( 'admin/update_view', array(
            //
        ) );
        return view( 'admin/admin_teamplate', $this->teamplate_admin );
    }
}