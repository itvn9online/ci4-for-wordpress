<?php
/*
 * model này không xử lý với database
 * -> chỉ xử lý với file, dùng trong trường hợp sử dụng host không có quyền thực thi file trực tiếp
 * -> không cần extends tới Models để đỡ bị qua thủ tục kết nối database
 */
namespace App\Models;

class File extends EbModel
{
    public $base_dir = '';
    public $ftp_server = '';

    public function __construct()
    {
        parent::__construct();
    }

    // EBE_check_ftp_account
    public function get_server()
    {
        if (!defined('FTP_USER') || !defined('FTP_PASS')) {
            //echo 'ERROR FTP: FTP USER or FTP PASS not found<br>' . PHP_EOL;
            return false;
        }

        //
        if (defined('FTP_HOST') && FTP_HOST != '') {
            $this->ftp_server = FTP_HOST;
        } else {
            //$this->ftp_server = $_SERVER['HTTP_HOST'];
            //$this->ftp_server = $_SERVER[ 'SERVER_ADDR' ];
            $this->ftp_server = '127.0.0.1';
        }
        //echo $this->ftp_server . '<br>' . PHP_EOL;

        return true;
    }

    // EBE_get_ftp_root_dir
    public function root_dir()
    {
        if ($this->base_dir != '') {
            return true;
        }

        // xác định server kết nối
        if ($this->ftp_server == '') {
            if ($this->get_server() === false) {
                echo debug_backtrace()[1]['class'] . '\\ ' . debug_backtrace()[1]['function'] . '<br>' . PHP_EOL;
                return 'FTP host not found';
            }
        }

        // tạo kết nối
        $conn_id = ftp_connect($this->ftp_server);
        if (!$conn_id) {
            return 'ERROR FTP connect to server';
        }

        // đăng nhập
        if (!ftp_login($conn_id, FTP_USER, FTP_PASS)) {
            return 'ERROR FTP login false';
        }

        // tạo file trong cache để xác định root cho tài khoản FTP đang được thiết lập
        $cache_for_ftp = WRITEPATH . 'ftp_' . __FUNCTION__ . '.txt';

        // Tạo một file bằng hàm của PHP thường -> không dùng FTP
        if (!file_exists($cache_for_ftp)) {
            echo $cache_for_ftp . '<br>' . PHP_EOL;
            $this->base_model->_eb_create_file($cache_for_ftp, date('r'));
        }
        //die( $cache_for_ftp );

        // lấy thư mục gốc của tài khoản FTP
        $a = explode('/', $cache_for_ftp);
        $ftp_dir_root = '';
        //	print_r( $a );
        foreach ($a as $v) {
            //echo $v . PHP_EOL;
            if ($ftp_dir_root == '' && $v != '') {
                $file_test = strstr($cache_for_ftp, '/' . $v . '/');
                //echo $file_test . " - \n";

                //
                if ($file_test != '') {
                    if (ftp_nlist($conn_id, '.' . $file_test) != false) {
                        $ftp_dir_root = $v;
                        break;
                    }
                }
            }
        }

        //
        ftp_close($conn_id);
        //die( $ftp_dir_root );

        //
        if ($ftp_dir_root != '') {
            $this->base_dir = $ftp_dir_root;
            return true;
        }

        //
        return 'ftp dir root is empty!';
    }

    // WGR_ftp_copy
    public function FTP_copy($source, $path, $file_permission = DEFAULT_FILE_PERMISSION)
    {
        $check_dir = $this->root_dir();
        if ($check_dir !== true) {
            echo $check_dir . '<br>' . PHP_EOL;
            return false;
        }
        //echo $this->base_dir . '<br>' . PHP_EOL;
        //echo $this->ftp_server . '<br>' . PHP_EOL;
        //die( __CLASS__ . ':' . __LINE__ );

        /*
         * các khâu kết nối và kiểm tra đã diễn ra ở bước root dir -> sau đây chỉ việc sử dụng
         */
        // tạo kết nối
        $conn_id = ftp_connect($this->ftp_server);

        // đăng nhập
        if (!ftp_login($conn_id, FTP_USER, FTP_PASS)) {
            echo 'ERROR FTP login false <br>' . PHP_EOL;
            return false;
        }

        //
        $file_for_ftp = $path;
        //	echo $file_for_ftp . '<br>';

        // nếu trong chuỗi file không có root dir -> báo lỗi
        if (strpos($file_for_ftp, '/' . $this->base_dir . '/') === false) {
            echo 'ERROR FTP root dir not found #' . $this->base_dir . '<br>' . PHP_EOL;
            return false;
        }
        $file_for_ftp = strstr($file_for_ftp, '/' . $this->base_dir . '/');
        //die( $file_for_ftp );

        // copy qua FTP_BINARY thì mới copy ảnh chuẩn được
        if (ftp_put($conn_id, $file_for_ftp, $source, FTP_BINARY)) {
            if ($file_permission > 0) {
                ftp_chmod($conn_id, $file_permission, $file_for_ftp);
            }
            return true;
        } else {
            echo 'ERROR copy file via FTP #' . $path . ' <br>' . PHP_EOL;
        }

        // close the connection
        ftp_close($conn_id);

        //
        return false;
    }

    //
    public function FTP_rename($source, $path)
    {
        $check_dir = $this->root_dir();
        if ($check_dir !== true) {
            echo $check_dir . '<br>' . PHP_EOL;
            return false;
        }
        //echo $this->base_dir . '<br>' . PHP_EOL;
        //echo $this->ftp_server . '<br>' . PHP_EOL;
        //die( __CLASS__ . ':' . __LINE__ );

        /*
         * các khâu kết nối và kiểm tra đã diễn ra ở bước root dir -> sau đây chỉ việc sử dụng
         */
        // tạo kết nối
        $conn_id = ftp_connect($this->ftp_server);

        // đăng nhập
        if (!ftp_login($conn_id, FTP_USER, FTP_PASS)) {
            echo 'ERROR FTP login false <br>' . PHP_EOL;
            return false;
        }

        //
        $file_for_ftp = $path;
        //echo $file_for_ftp . '<br>';

        // nếu trong chuỗi file không có root dir -> báo lỗi
        if (strpos($file_for_ftp, '/' . $this->base_dir . '/') === false) {
            echo 'ERROR FTP root dir not found #' . $this->base_dir . '<br>' . PHP_EOL;
            return false;
        }
        $file_for_ftp = strstr($file_for_ftp, '/' . $this->base_dir . '/');
        //die( $file_for_ftp );
        //echo $file_for_ftp . '<br>';
        $source = strstr($source, '/' . $this->base_dir . '/');
        //echo $source . '<br>';

        // copy qua FTP_BINARY thì mới copy ảnh chuẩn được
        if (ftp_rename($conn_id, $source, $file_for_ftp)) {
            return true;
        } else {
            echo 'ERROR rename file via FTP #' . $path . ' <br>' . PHP_EOL;
        }

        // close the connection
        ftp_close($conn_id);

        //
        return false;
    }

    // EBE_ftp_remove_file
    public function FTP_unlink($file_)
    {
        $check_dir = $this->root_dir();
        if ($check_dir !== true) {
            echo $check_dir . '<br>' . PHP_EOL;
            return false;
        }
        //echo $this->base_dir . '<br>' . PHP_EOL;
        //echo $this->ftp_server . '<br>' . PHP_EOL;
        //die( __CLASS__ . ':' . __LINE__ );

        /*
         * các khâu kết nối và kiểm tra đã diễn ra ở bước root dir -> sau đây chỉ việc sử dụng
         */
        // tạo kết nối
        $conn_id = ftp_connect($this->ftp_server);

        // đăng nhập
        if (!ftp_login($conn_id, FTP_USER, FTP_PASS)) {
            echo 'ERROR FTP login false <br>' . PHP_EOL;
            return false;
        }

        //
        $file_for_ftp = $file_;
        $file_for_ftp = strstr($file_, '/' . $this->base_dir . '/');
        //die( $file_for_ftp );

        // xóa file
        $result = true;
        if (!ftp_delete($conn_id, $file_for_ftp)) {
            $result = 'ERROR FTP: ftp delete error';
        }

        // close the connection
        ftp_close($conn_id);

        //
        return $result;
    }

    //
    public function FTP_chmod($path, $file_permission = DEFAULT_FILE_PERMISSION)
    {
        $check_dir = $this->root_dir();
        if ($check_dir !== true) {
            echo $check_dir . '<br>' . PHP_EOL;
            return false;
        }
        //echo $this->base_dir . '<br>' . PHP_EOL;
        //echo $this->ftp_server . '<br>' . PHP_EOL;
        //die( __CLASS__ . ':' . __LINE__ );

        /*
         * các khâu kết nối và kiểm tra đã diễn ra ở bước root dir -> sau đây chỉ việc sử dụng
         */
        // tạo kết nối
        $conn_id = ftp_connect($this->ftp_server);

        // đăng nhập
        if (!ftp_login($conn_id, FTP_USER, FTP_PASS)) {
            echo 'ERROR FTP login false <br>' . PHP_EOL;
            return false;
        }

        //
        $file_for_ftp = $path;
        //echo $file_for_ftp . '<br>';

        // nếu trong chuỗi file không có root dir -> báo lỗi
        if (strpos($file_for_ftp, '/' . $this->base_dir . '/') === false) {
            echo 'ERROR FTP root dir not found #' . $this->base_dir . '<br>' . PHP_EOL;
            return false;
        }
        $file_for_ftp = strstr($file_for_ftp, '/' . $this->base_dir . '/');
        //die( $file_for_ftp );

        // copy qua FTP_BINARY thì mới copy ảnh chuẩn được
        if (ftp_chmod($conn_id, $file_permission, $file_for_ftp)) {
            return true;
        } else {
            echo 'ERROR chmod file via FTP #' . $path . ' <br>' . PHP_EOL;
        }

        // close the connection
        ftp_close($conn_id);

        //
        return false;
    }

    public function create_file($file_, $content_, $ops = [])
    {
        if ($content_ == '') {
            echo 'ERROR FTP: content is NULL <br>' . PHP_EOL;
            return false;
        }

        //
        $check_dir = $this->root_dir();
        if ($check_dir !== true) {
            echo $check_dir . '<br>' . PHP_EOL;
            return false;
        }
        //echo $this->base_dir . '<br>' . PHP_EOL;
        //echo $this->ftp_server . '<br>' . PHP_EOL;
        //die( __CLASS__ . ':' . __LINE__ );

        /*
         * các khâu kết nối và kiểm tra đã diễn ra ở bước root dir -> sau đây chỉ việc sử dụng
         */
        // tạo kết nối
        $conn_id = ftp_connect($this->ftp_server);

        // đăng nhập
        if (!ftp_login($conn_id, FTP_USER, FTP_PASS)) {
            echo 'ERROR FTP login false <br>' . PHP_EOL;
            return false;
        }

        //
        $file_for_ftp = $file_;
        if ($this->base_dir != '') {
            // nếu trong chuỗi file không có root dir -> báo lỗi
            if (strpos($file_, '/' . $this->base_dir . '/') === false) {
                echo 'ERROR FTP root dir not found #' . $this->base_dir . '<br>' . PHP_EOL;
                return false;
            }

            $file_for_ftp = strstr($file_, '/' . $this->base_dir . '/');
        }
        //die( $file_for_ftp );

        //
        $local_filename = $this->create_cache_for_ftp($content_);
        if (!file_exists($local_filename)) {
            echo 'ERROR FTP local_filename not create!<br>' . PHP_EOL;
            return false;
        }

        // upload file
        $result = true;
        if ($ops['add_line'] != '') {
            if (!ftp_append($conn_id, '.' . $file_for_ftp, $local_filename, FTP_BINARY)) {
                echo 'ERROR FTP: ftp append error <br>' . PHP_EOL;
                $result = false;
            }
        } else {
            if (!ftp_put($conn_id, '.' . $file_for_ftp, $local_filename, FTP_BINARY)) {
                echo 'ERROR FTP: ftp put error <br>' . PHP_EOL;
                $result = false;
            }
        }
        if ($result === true && $ops['set_permission'] > 0) {
            ftp_chmod($conn_id, $ops['set_permission'], $file_for_ftp);
        }

        // close the connection
        ftp_close($conn_id);

        //
        return $result;
    }

    public function create_dir($dir_, $ops = [])
    {
        $check_dir = $this->root_dir();
        if ($check_dir !== true) {
            echo $check_dir . '<br>' . PHP_EOL;
            return false;
        }
        //echo $this->base_dir . '<br>' . PHP_EOL;
        //echo $this->ftp_server . '<br>' . PHP_EOL;
        //die( __CLASS__ . ':' . __LINE__ );

        /*
         * các khâu kết nối và kiểm tra đã diễn ra ở bước root dir -> sau đây chỉ việc sử dụng
         */
        // tạo kết nối
        $conn_id = ftp_connect($this->ftp_server);

        // đăng nhập
        if (!ftp_login($conn_id, FTP_USER, FTP_PASS)) {
            echo 'ERROR FTP login false <br>' . PHP_EOL;
            return false;
        }

        //
        $dir_for_ftp = $dir_;
        if ($this->base_dir != '') {
            // nếu trong chuỗi file không có root dir -> báo lỗi
            if (strpos($dir_, '/' . $this->base_dir . '/') === false) {
                echo 'ERROR FTP root dir not found #' . $this->base_dir . '<br>' . PHP_EOL;
                return false;
            }

            $dir_for_ftp = strstr($dir_, '/' . $this->base_dir . '/');
        }
        //die( $dir_for_ftp );

        //
        if (!ftp_mkdir($conn_id, $dir_for_ftp)) {
            echo 'ERROR FTP create dir! <br>' . PHP_EOL;
            return false;
        }
        if (!isset($ops['set_permission'])) {
            $ops['set_permission'] = 0755;
        }
        ftp_chmod($conn_id, $ops['set_permission'], $dir_for_ftp);

        // close the connection
        ftp_close($conn_id);

        //
        return true;
    }

    private function create_cache_for_ftp($content_ = '')
    {
        $f = WRITEPATH . 'cache_for_ftp.txt';

        if ($content_ != '') {
            echo __CLASS__ . ':' . __LINE__ . ':' . $f . '<br>' . PHP_EOL;
            $this->base_model->_eb_create_file($f, $content_);
        }
        return $f;
    }

    public function download_file($file_path, $url)
    {
        if (@!file_put_contents($file_path, file_get_contents($url))) {
            //if ( @!copy( $url, $file_path ) ) {
            $ch = curl_init($url);
            $fp = fopen($file_path, 'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
        }

        //
        if (file_exists($file_path)) {
            return true;
        }
        return false;
    }
}