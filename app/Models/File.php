<?php
/*
 * model này không xử lý với database
 * -> chỉ xử lý với file, dùng trong trường hợp sử dụng host không có quyền thực thi file trực tiếp
 * -> không cần extends tới Models để đỡ bị qua thủ tục kết nối database
 */
namespace App\ Models;

class File extends EbModel {
    private $base_dir = '';
    private $ftp_server = '';

    public function __construct() {
        //parent::__construct();

        //$this->post_model = new\ App\ Models\ Post();
    }

    // EBE_check_ftp_account
    private function get_server() {
        if ( !defined( 'FTP_USER' ) || !defined( 'FTP_PASS' ) ) {
            //echo 'ERROR FTP: FTP USER or FTP PASS not found<br>' . "\n";
            return false;
        }

        //
        if ( defined( 'FTP_HOST' ) && FTP_HOST != '' ) {
            $this->ftp_server = FTP_HOST;
        } else {
            //$this->ftp_server = $_SERVER['HTTP_HOST'];
            $this->ftp_server = $_SERVER[ 'SERVER_ADDR' ];
        }
        //echo $this->ftp_server . '<br>' . "\n";

        return true;
    }

    // EBE_get_ftp_root_dir
    private function root_dir() {
        if ( $this->base_dir != '' ) {
            return true;
        }

        // xác định server kết nối
        if ( $this->ftp_server == '' ) {
            if ( $this->get_server() === false ) {
                return 'FTP host not found';
            }
        }

        // tạo kết nối
        $conn_id = ftp_connect( $this->ftp_server );
        if ( !$conn_id ) {
            return 'ERROR FTP connect to server';
        }

        // đăng nhập
        if ( !ftp_login( $conn_id, FTP_USER, FTP_PASS ) ) {
            return 'ERROR FTP login false';
        }

        // tạo file trong cache để xác định root cho tài khoản FTP đang được thiết lập
        $cache_for_ftp = PUBLIC_HTML_PATH . 'writable/ftp_' . __FUNCTION__ . '.txt';

        // Tạo một file bằng hàm của PHP thường -> không dùng FTP
        if ( !file_exists( $cache_for_ftp ) ) {
            $filew = fopen( $cache_for_ftp, 'x+' );
            fclose( $filew );

            // nếu không tạo được file
            if ( !$filew ) {
                return 'ERROR create file: ' . $cache_for_ftp;
            } else {
                // nhớ set 777 cho file
                chmod( $cache_for_ftp, 0777 );
            }
            file_put_contents( $cache_for_ftp, date( 'r' ) );
        }
        //die( $cache_for_ftp );

        // lấy thư mục gốc của tài khoản FTP
        $a = explode( '/', $cache_for_ftp );
        $ftp_dir_root = '';
        //	print_r( $a );
        foreach ( $a as $v ) {
            //echo $v . "\n";
            if ( $ftp_dir_root == '' && $v != '' ) {
                $file_test = strstr( $cache_for_ftp, '/' . $v . '/' );
                //echo $file_test . " - \n";

                //
                if ( $file_test != '' ) {
                    if ( ftp_nlist( $conn_id, '.' . $file_test ) != false ) {
                        $ftp_dir_root = $v;
                        break;
                    }
                }
            }
        }

        //
        ftp_close( $conn_id );
        //die( $ftp_dir_root );

        //
        if ( $ftp_dir_root != '' ) {
            $this->base_dir = $ftp_dir_root;
            return true;
        }

        //
        return 'ftp dir root is empty!';
    }

    // WGR_ftp_copy
    public function FTP_copy( $source, $path, $file_permission = 0777 ) {
        $check_dir = $this->root_dir();;
        if ( $check_dir !== true ) {
            echo $check_dir . '<br>' . "\n";
            return false;
        }
        //echo $this->base_dir . '<br>' . "\n";
        //echo $this->ftp_server . '<br>' . "\n";
        //die( __FILE__ . ':' . __LINE__ );

        /*
         * các khâu kết nối và kiểm tra đã diễn ra ở bước root dir -> sau đây chỉ việc sử dụng
         */
        // tạo kết nối
        $conn_id = ftp_connect( $this->ftp_server );

        // đăng nhập
        if ( !ftp_login( $conn_id, FTP_USER, FTP_PASS ) ) {
            echo 'ERROR FTP login false <br>' . "\n";
            return false;
        }

        //
        $file_for_ftp = $path;
        //	echo $file_for_ftp . '<br>';

        // nếu trong chuỗi file không có root dir -> báo lỗi
        if ( strpos( $file_for_ftp, '/' . $this->base_dir . '/' ) === false ) {
            echo 'ERROR FTP root dir not found #' . $this->base_dir . '<br>' . "\n";
            return false;
        }
        $file_for_ftp = strstr( $file_for_ftp, '/' . $this->base_dir . '/' );
        //die( $file_for_ftp );

        // copy qua FTP_BINARY thì mới copy ảnh chuẩn được
        if ( ftp_put( $conn_id, $file_for_ftp, $source, FTP_BINARY ) ) {
            if ( $file_permission > 0 ) {
                ftp_chmod( $conn_id, $file_permission, $file_for_ftp );
            }
            return true;
        } else {
            echo 'ERROR copy file via FTP #' . $path . ' <br>' . "\n";
        }

        //
        return false;
    }

    // EBE_ftp_remove_file
    public function FTP_unlink( $file_ ) {
        $check_dir = $this->root_dir();;
        if ( $check_dir !== true ) {
            echo $check_dir . '<br>' . "\n";
            return false;
        }
        //echo $this->base_dir . '<br>' . "\n";
        //echo $this->ftp_server . '<br>' . "\n";
        //die( __FILE__ . ':' . __LINE__ );

        /*
         * các khâu kết nối và kiểm tra đã diễn ra ở bước root dir -> sau đây chỉ việc sử dụng
         */
        // tạo kết nối
        $conn_id = ftp_connect( $this->ftp_server );

        // đăng nhập
        if ( !ftp_login( $conn_id, FTP_USER, FTP_PASS ) ) {
            echo 'ERROR FTP login false <br>' . "\n";
            return false;
        }

        //
        $file_for_ftp = $file_;
        $file_for_ftp = strstr( $file_, '/' . $this->base_dir . '/' );
        //die( $file_for_ftp );

        // xóa file
        $result = true;
        if ( !ftp_delete( $conn_id, $file_for_ftp ) ) {
            $result = 'ERROR FTP: ftp_delete error';
        }

        // close the connection
        ftp_close( $conn_id );

        //
        return $result;
    }
}