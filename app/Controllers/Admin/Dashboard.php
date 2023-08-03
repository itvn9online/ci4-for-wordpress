<?php

namespace App\Controllers\Admin;

use App\Libraries\UsersType;
use App\Libraries\PostType;
use App\Libraries\TaxonomyType;

class Dashboard extends Optimize
{
    // danh sách các thư mục trong quá trình unzip file -> lật ngược lại mới xóa được thư mục
    private $dir_re_cache = [];
    // danh sách các file copy từ cache sang thư mục code
    private $file_re_cache = [];
    // link download code từ github
    //private $link_download_github = 'https://github.com/itvn9online/ci4-for-wordpress/blob/main/ci4-for-wordpress.zip?raw=true';
    private $link_download_github = 'https://github.com/itvn9online/ci4-for-wordpress/archive/refs/heads/main.zip';
    private $link_download_system_github = 'https://github.com/itvn9online/ci4-for-wordpress/raw/main/system.zip';
    // mảng các file sẽ được copy lại ngay sau khi update
    private $copy_after_updated = [];
    // giãn cách reser permalink
    private $space_reset_permalink = 60;

    public function __construct()
    {
        parent::__construct();

        // kiểm tra quyền truy cập của tài khoản hiện tại
        $this->check_permision(__CLASS__);

        // Luôn bật display error để tiện theo dõi vấn đề
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        //
        $this->f_env = PUBLIC_HTML_PATH . '.env';
        $this->f_backup_env = PUBLIC_HTML_PATH . 'writable/.env-bak';

        /*
         * các thư mục khi reset code sẽ có thể xóa bỏ để thay thế mà không ảnh hưởng đến website
         */
        $arrs_dir_list = [
            'app',
            'public/admin',
            'public/css',
            'public/images',
            'public/javascript',
            'public/libraries',
            'public/themes/echbayfour',
            'public/thirdparty',
        ];

        // -> tạo thư mục gốc và thư mục sau khi XÓA
        $this->dir_list = [];
        $this->dir_deleted_list = [];
        foreach ($arrs_dir_list as $v) {
            $this->dir_list[] = PUBLIC_HTML_PATH . $v;
            $this->dir_deleted_list[] = PUBLIC_HTML_PATH . $v . '-deleted';
        }

        // thư mục app
        //$this->app_dir = PUBLIC_HTML_PATH . 'app';
        //$this->app_deleted_dir = $this->app_dir . '-deleted';
        //die( $this->app_deleted_dir );

        // tham số dùng để copy lại file config -> bắt buộc phải có thì mới chạy được web
        //$this->config_deleted_file = PUBLIC_HTML_PATH . 'app-deleted/Config/Database.php';
        //echo $this->config_deleted_file . '<br>' . PHP_EOL;
        //$this->config_file = PUBLIC_HTML_PATH . 'app/Config/Database.php';
        //echo $this->config_file . '<br>' . PHP_EOL;

        //
        $this->copy_after_updated = [
            PUBLIC_HTML_PATH . 'app-deleted/Config/Database.php' => PUBLIC_HTML_PATH . 'app/Config/Database.php',
            PUBLIC_HTML_PATH . 'app-deleted/Config/' . basename(DYNAMIC_CONSTANTS_PATH) => PUBLIC_HTML_PATH . 'app/Config/' . basename(DYNAMIC_CONSTANTS_PATH),
        ];

        //
        //echo THEMEPATH . '<br>' . PHP_EOL;
        //echo basename( THEMEPATH ) . '<br>' . PHP_EOL;
    }

    public function index()
    {
        // TEST thời gian chạy module -> host dùng ftp chạy có vẻ lâu
        //$begin_t = time();

        echo '<!-- ' . PHP_EOL;
        $this->vendor_sync();
        //echo 'begin t: ' . (time() - $begin_t) . PHP_EOL;
        //$this->unzip_ci4_for_wordpress();
        //$this->cleanup_old_cache(DAY);
        //echo 'begin t: ' . (time() - $begin_t) . PHP_EOL;
        echo ' -->';

        //
        $this->auto_create_htaccess_deny();
        //echo 'begin t: ' . (time() - $begin_t) . PHP_EOL;

        //
        $current_dbname = \Config\Database::connect()->database;

        //
        $last_enabled_debug = 0;
        // tự động tắt chế độ debug sau 7 ngày
        //$auto_disable_debug = WEEK;
        // tự động tắt chế độ debug sau 4 giờ
        $auto_disable_debug = 4 * 3600;
        if (file_exists($this->f_env)) {
            $last_enabled_debug = filemtime($this->f_env);
            //echo date( 'r', $last_enabled_debug );

            //
            if ($last_enabled_debug < time() - $auto_disable_debug) {
                //echo 'Auto disable debug via .env';
                $this->action_disable_env($this->f_env, $this->f_backup_env);
            }
        }
        //echo 'begin t: ' . (time() - $begin_t) . PHP_EOL;

        // Nếu chế độ không phải là đang chạy chính thức
        /*
        $debug_enable = false;
        if ( ( ENVIRONMENT !== 'production' ) === true ) {
        //var_dump( ( ENVIRONMENT !== 'production' ) );
        //
        $debug_enable = true;
        // kiểm tra nếu chế độ debug đang được bật -> đưa ra cảnh báo
        //echo PUBLIC_HTML_PATH . '<br>' . PHP_EOL;
        //echo PUBLIC_HTML_PATH . '.env';
        }
        */

        // optimize code
        $this->before_compress_css_js();
        //echo 'begin t: ' . (time() - $begin_t) . PHP_EOL;

        //
        $client_ip = $this->request->getIPAddress();
        //$client_ip = ( isset( $_SERVER[ 'HTTP_X_REAL_IP' ] ) ) ? $_SERVER[ 'HTTP_X_REAL_IP' ] : $_SERVER[ 'REMOTE_ADDR' ];


        // TEST xem cache có chạy hay không -> gọi đến cache được gọi trong dashboard để xem có NULL hay không
        $check_cache_active = $this->base_model->scache('auto_sync_table_column');
        //echo $check_cache_active . '<br>' . PHP_EOL;
        //echo $base_model->dcache( 'auto_sync_table_column' ) . '<br>' . PHP_EOL;


        // kiểm tra file robots.txt
        $robots_txt = PUBLIC_PUBLIC_PATH . 'robots.txt';
        // mặc định là không có file robots
        $robots_exist = 0;
        if (file_exists($robots_txt)) {
            // có thì mới bắt đầu kiểm tra
            $robots_exist = 1;

            // nếu không xác định được nội dung cần thiết trong robot txt -> cảnh báo
            if (strpos(file_get_contents($robots_txt, 1), DYNAMIC_BASE_URL) === false) {
                $robots_exist = 2;
            }
        }
        //echo 'begin t: ' . (time() - $begin_t) . PHP_EOL;
        //die(__CLASS__ . ':' . __LINE__);

        //
        //print_r( $_SERVER );
        //print_r( $_SESSION );
        //echo mysqli_get_client_info();
        //echo mysql_get_server_info();
        //print_r( opcache_get_status() );

        //
        $this->teamplate_admin['content'] = view(
            'admin/dashboard_view',
            array(
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
            )
        );
        return view('admin/admin_teamplate', $this->teamplate_admin);
    }

    // tắt chế độ debug qua .env
    public function disable_env()
    {
        if ($this->session_data['member_type'] != UsersType::ADMIN) {
            $this->base_model->alert('Bạn không có quyền thực hiện thao tác này', 'error');
        }

        //
        $f = $this->f_env;
        if (!file_exists($f)) {
            $this->base_model->alert('Không tồn tại file ' . basename($f), 'error');
        }

        // END v2
        return $this->action_disable_env($f, $this->f_backup_env);
    }
    private function action_disable_env($f, $f_backup, $for_alert = 1)
    {
        // bakup file .env nếu chưa có
        if (!file_exists($f_backup)) {
            if (!$this->MY_copy($f, $f_backup)) {
                if ($for_alert === 1) {
                    $this->base_model->alert('LỖI! không backup được file ' . basename($f), 'error');
                }
            } else {
                chmod($f_backup, DEFAULT_FILE_PERMISSION);
            }
            if (!file_exists($f_backup)) {
                if ($for_alert === 1) {
                    $this->base_model->alert('LỖI! tồn tại file: ' . basename($f_backup), 'error');
                }
            }
        }

        // xong XÓA file .env
        if ($this->MY_unlink($f)) {
            if ($for_alert === 1) {
                $this->base_model->alert('', base_url(CUSTOM_ADMIN_URI));
                //$this->base_model->alert( 'TẮT chế độ debug thành công (LƯU TRỮ file ' . basename( $f ) . ')' );
            }
        } else if ($for_alert === 1) {
            $this->base_model->alert('LỖI! Không xóa được file ' . basename($f), 'error');
        }

        // END v2
        return true;
    }

    public function enable_env()
    {
        if ($this->session_data['member_type'] != UsersType::ADMIN) {
            $this->base_model->alert('Bạn không có quyền thực hiện thao tác này', 'error');
        }

        // nếu tồn tại file .env -> bỏ qua
        $f = $this->f_env;
        if (file_exists($f)) {
            //$this->base_model->alert( 'File đã tồn tại ' . basename( $f ), 'error' );
        }

        // phải tồn tại file .env bak thì mới tiếp tục
        $f_backup = $this->f_backup_env;
        if (file_exists($f_backup)) {
            // restore lại file env
            if ($this->MY_copy($f_backup, $f)) {
                $this->base_model->alert('', base_url(CUSTOM_ADMIN_URI));
                //$this->base_model->alert( 'BẬT chế độ debug thành công (copy file ' . basename( $f ) . ')' );
            } else {
                $this->base_model->alert('LỖI! không restore được file ' . basename($f), 'error');
            }
        } else {
            $this->base_model->alert('Không tồn tại file ' . basename($f_backup), 'error');
        }

        //
        return true;
    }

    private function unzip_ci4_for_wordpress()
    {
        $file_zip = PUBLIC_HTML_PATH . 'ci4-for-wordpress.zip';

        //
        if (file_exists($file_zip)) {
            echo $file_zip . '<br>' . PHP_EOL;

            //
            if ($this->MY_unzip($file_zip, PUBLIC_HTML_PATH) === TRUE) {
                $this->MY_unlink($file_zip);
            }
        }
    }

    public function unzip_system($non_stop = false)
    {
        $system_zip = PUBLIC_HTML_PATH . 'system.zip';
        if (!file_exists($system_zip)) {
            $this->base_model->alert('Không tồn tại file ' . basename($system_zip), 'error');
        }

        //
        $current_ci_version = \CodeIgniter\CodeIgniter::CI_VERSION;
        //echo $current_ci_version . '<br>' . PHP_EOL;

        // tên thư mục sẽ backup system cũ
        $to = PUBLIC_HTML_PATH . 'system-' . $current_ci_version;
        if (is_dir($to)) {
            $to .= '-' . date('Ymd-His');
            //$this->base_model->alert( 'Vui lòng XÓA ' . basename( $to ) . ' backup trước khi tiếp tục', 'error' );
        }

        // đổi tên thư mục system -> backup
        rename(PUBLIC_HTML_PATH . 'system', $to);

        // giải nén system zip
        if ($this->MY_unzip($system_zip, PUBLIC_HTML_PATH) === TRUE) {
            $this->MY_unlink($system_zip);

            // non_stop = true -> dừng lại tại đây thôi
            if ($non_stop !== false) {
                return true;
            }

            // còn không thì trả về thông báo
            //$this->base_model->alert( 'DONE! giải nén system.zip thành công' );
            die('<script>top.done_unzip_system();</script>');
        }

        //
        //die( __CLASS__ . ':' . __LINE__ );

        //
        $this->base_model->alert('LỖI trong quá trình giải nén system.zip', 'error');

        //
        return true;
    }

    private function unzip_code()
    {
        //die( PUBLIC_HTML_PATH );

        // 1 số định dạng file không cho phép upload trực tiếp
        $allow_upload = [
            'zip'
        ];

        //
        $upload_path = PUBLIC_HTML_PATH;
        //echo $upload_path . '<br>' . PHP_EOL;

        // với 1 số host, chỉ upload được vào thư mục có permission 777 -> cache
        if ($this->using_via_ftp() === true) {
            $upload_path = WRITEPATH . 'updates/';

            // tạo thư mục nếu chưa có
            if (!is_dir($upload_path)) {
                $this->mk_dir($upload_path, __CLASS__ . ':' . __LINE__);
            }
        }
        //die( $upload_path );
        echo $upload_path . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;

        //
        $file_path = '';
        if ($zipfile = $this->request->getFiles()) {
            //print_r( $zipfile );

            //
            $file = $zipfile['upload_code'];
            //print_r( $file );

            //
            if ($file->isValid() && !$file->hasMoved()) {
                $file_name = $file->getName();
                //echo $file_name . '<br>' . PHP_EOL;
                $file_name = $this->base_model->_eb_non_mark_seo($file_name);
                $file_name = sanitize_filename($file_name);
                //echo $file_name . '<br>' . PHP_EOL;

                //
                $file_ext = $file->guessExtension();
                //echo $file_ext . '<br>' . PHP_EOL;
                $file_ext = strtolower($file_ext);
                //echo $file_ext . '<br>' . PHP_EOL;

                //
                $file_path = $upload_path . $file_name;
                //echo $file_path . '<br>' . PHP_EOL;

                // kiểm tra định dạng file
                $mime_type = $file->getMimeType();
                //echo $mime_type . '<br>' . PHP_EOL;

                // nếu có kiểm duyệt định dạng file -> chỉ các file trong này mới được upload
                if (!in_array($file_ext, $allow_upload)) {
                    $this->base_model->alert('Định dạng file chưa được hỗ trợ! Hiện chỉ hỗ trợ định dạng .ZIP', 'error');
                }
                //die( __CLASS__ . ':' . __LINE__ );

                // xóa các file zip cũ đi
                $this->cleanup_zip($upload_path, 'Không xóa được file ZIP cũ trước khi upload file mới');

                //
                $file->move($upload_path, $file_name, true);

                //
                if (!file_exists($file_path)) {
                    $this->base_model->alert('Upload thất bại! Không xác định được file sau khi upload', 'error');
                }
                chmod($file_path, DEFAULT_FILE_PERMISSION);

                // nếu là update system của Codeigniter thì sử dụng chức năng unzip riêng
                //echo $file_name . '<br>' . PHP_EOL;
                if (basename($file_name) == 'system.zip') {
                    return $this->unzip_system();
                    //die( __CLASS__ . ':' . __LINE__ );
                }
                //die( __CLASS__ . ':' . __LINE__ );

                // giải nén sau khi upload
                $this->after_unzip_code($file_path, $upload_path);

                /*
                 * dọn dẹp code dư thừa sau khi giải nén (nếu tồn tại thư mục này)
                 */
                //$this->cleanup_deleted_dir($this->dir_deleted_list);
            } else {
                throw new \RuntimeException($file->getErrorString() . '(' . $file->getError() . ')');
            }
        }

        // đồng bộ lại thirdparty và database
        ///echo basename( $file_path ) . '<br>';
        if (strpos(basename($file_path), 'ci4-for-wordpress') !== false) {
            $this->vendor_sync(false);
        }

        //
        //die( __CLASS__ . ':' . __LINE__ );
        die('<script>top.done_submit_update_code("' . basename($file_path) . '");</script>');
    }

    // xóa file zip sau khi xử lý code
    private function cleanup_zip($upload_path, $msg)
    {
        echo $upload_path . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
        foreach (glob(rtrim($upload_path, '/') . '/*.zip') as $filename) {
            echo $filename . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;

            //
            if (is_file($filename)) {
                if (!$this->MY_unlink($filename)) {
                    $this->base_model->alert($msg, 'error');
                }
            }
        }
    }

    private function rmdir_from_cache($upload_path)
    {
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
        //die( $upload_path );
        //echo $upload_path . '<br>' . PHP_EOL;

        // xử lý các file đặc biệt -> ví dụ: .git
        foreach (glob(rtrim($upload_path, '/') . '/.*') as $filename) {
            if (is_dir($filename)) {
                //echo $filename . '<br>' . PHP_EOL;
                $check_dot = basename($filename);

                // không lấy các thư mục đặc biệt
                if ($check_dot == '.' || $check_dot == '..') {
                    continue;
                }
                //echo $check_dot . '<br>' . PHP_EOL;

                //
                $this->dir_re_cache[] = $filename;

                //
                //echo $filename . '<br>' . PHP_EOL;

                //
                $this->rmdir_from_cache($filename);
            }
        }

        // xử lý các thư mục thông thường
        foreach (glob(rtrim($upload_path, '/') . '/*') as $filename) {
            if (is_dir($filename)) {
                $this->dir_re_cache[] = $filename;
                //echo $filename . '<br>' . PHP_EOL;

                //
                $this->rmdir_from_cache($filename);
            }
        }
    }

    private function get_all_file_in_folder($upload_path)
    {
        //die( $upload_path );

        // xử lý các file đặc biệt -> ví dụ: .htaccess
        foreach (glob(rtrim($upload_path, '/') . '/.*') as $filename) {
            if (is_file($filename)) {
                //echo $filename . '<br>' . PHP_EOL;
                //unlink( $filename );

                //
                $this->file_re_cache[] = $filename;
            } else if (is_dir($filename)) {
                //echo $filename . '<br>' . PHP_EOL;
                $check_dot = basename($filename);

                // không lấy các thư mục đặc biệt
                if ($check_dot == '.' || $check_dot == '..') {
                    continue;
                }
                //echo $check_dot . '<br>' . PHP_EOL;

                //
                $this->get_all_file_in_folder($filename);
            }
        }

        // xử lý các file thông thường
        foreach (glob(rtrim($upload_path, '/') . '/*') as $filename) {
            if (is_file($filename)) {
                //echo $filename . '<br>' . PHP_EOL;
                //unlink( $filename );

                //
                $this->file_re_cache[] = $filename;
            } else if (is_dir($filename)) {
                //echo $filename . '<br>' . PHP_EOL;
                $this->get_all_file_in_folder($filename);
            }
        }
    }

    // chức năng download và update system
    protected function update_system()
    {
        //
        $upload_path = PUBLIC_HTML_PATH;
        //echo $upload_path . '<br>' . PHP_EOL;

        //
        $this->cleanup_zip($upload_path, 'Không xóa được file ZIP cũ trước khi upload file mới');

        //
        $file_path = $upload_path . explode('?', basename($this->link_download_system_github))[0];
        //echo $file_path . '<br>' . PHP_EOL;

        //
        $this->file_model->download_file($file_path, $this->link_download_system_github);

        //
        return $this->unzip_system(true);
    }

    // chức năng download code từ github nhưng kèm tính năng xóa code cũ -> nạp code mới hoàn toàn
    public function reset_code()
    {
        return $this->download_code(true);
    }

    // chức năng upload file code zip lên host và giải nén -> update code
    public function download_code($reset_code = false)
    {
        // update system trước
        $this->update_system();
        //die(__CLASS__ . ':' . __LINE__);

        // kiểm tra phiên bản code xem có khác nhau không
        /*
        if ( file_get_contents( APPPATH . 'VERSION', 1 ) == file_get_contents( 'https://raw.githubusercontent.com/itvn9online/ci4-for-wordpress/main/app/VERSION', 1 ) ) {
        $this->base_model->alert( 'Download thất bại! Phiên bản của bạn đang là bản mới nhất', 'warning' );
        }
        */

        //
        $upload_path = PUBLIC_HTML_PATH;
        //echo $upload_path . '<br>' . PHP_EOL;

        // chức năng download file main zip từ github
        $from_main_github = true;

        // với 1 số host, chỉ upload được vào thư mục có permission 777 -> cache
        if ($from_main_github === true || $this->using_via_ftp() === true) {
            $upload_path = WRITEPATH . 'updates/';

            // tạo thư mục nếu chưa có
            if (!is_dir($upload_path)) {
                $this->mk_dir($upload_path, __CLASS__ . ':' . __LINE__);
            }
        }
        echo 'upload path: ' . $upload_path . ' --- ' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;

        //
        $this->cleanup_zip($upload_path, 'Không xóa được file ZIP cũ trước khi upload file mới');
        //die( $upload_path );

        //
        $file_path = $upload_path . explode('?', basename($this->link_download_github))[0];
        //die( $file_path );

        //
        if ($this->file_model->download_file($file_path, $this->link_download_github) === true) {
            // Khi có tham số reset code -> đổi tên thư mục app, public để upload code từ đầu
            if ($reset_code === true) {
                foreach ($this->dir_list as $v) {
                    // nếu không có thư mục gốc -> bỏ qua
                    if (!is_dir($v)) {
                        echo 'DIR NOT EXIST! ' . $v . ' --- ' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
                        continue;
                    }

                    // nếu có thư mục delete -> dừng lại tiến trình
                    $v_deleted = $v . '-deleted';
                    if (is_dir($v_deleted)) {
                        echo ('<script>top.WGR_body_opacity();</script>');
                        $this->base_model->alert('DIR EXIST! ' . $v_deleted . ' --- ' . __CLASS__ . ':' . __LINE__, 'error');
                    }

                    //
                    if (!$this->MY_rename($v, $v_deleted)) {
                        echo ('<script>top.WGR_body_opacity();</script>');
                        $this->base_model->alert('ERROR rename! ' . $v . ' --- ' . __CLASS__ . ':' . __LINE__, 'error');
                    }
                }

                // tạo thư mục thông qua FTP
                /*
                if ( $this->using_via_ftp() === true ) {
                $this->file_model->create_dir( $this->app_dir );
                }
                // tạo mặc định
                else {
                $this->mk_dir( $this->app_dir, __CLASS__ . ':' . __LINE__, 0755 );
                }
                */

                //
                //die( __CLASS__ . ':' . __LINE__ );
            }

            // giải nén sau khi upload
            $this->after_unzip_code($file_path, $upload_path, $from_main_github);

            // nếu tồn tại file config -> copy nó sang thư mục chính
            /*
            if (file_exists($this->config_deleted_file) && !file_exists($this->config_file)) {
                // không copy được file config thì restore code lại
                if (!$this->MY_copy($this->config_deleted_file, $this->config_file)) {
                    $this->restore_code();
                }
            }
            */

            // copy lại file cần thiết sau khi update
            foreach ($this->copy_after_updated as $f_delete => $f_copy) {
                if (file_exists($f_delete) && !file_exists($f_copy)) {
                    // không copy được file config thì restore code lại
                    if (!$this->MY_copy($f_delete, $f_copy)) {
                        $this->restore_code();
                        break;
                    }
                }
            }

            // đồng bộ lại thirdparty và database
            $this->vendor_sync(false);
        } else {
            $this->base_model->alert('Download thất bại! Không xác định được file sau khi download', 'error');
        }
        //die( $file_path );

        //
        //die( __CLASS__ . ':' . __LINE__ );
        die('<script>top.done_submit_update_code("' . basename($file_path) . '");</script>');
    }

    /*
     * giải nén sau khi upload
     * main_zip: dành cho unzip code từ main github
     */
    private function after_unzip_code($file_path, $upload_path, $main_zip = false)
    {
        echo 'upload path: ' . $upload_path . ' --- ' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
        echo 'upload via ftp: ' . $this->using_via_ftp() . ' --- ' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
        echo 'main zip: ' . $main_zip . ' --- ' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;

        //
        //$filename = '';
        if ($this->MY_unzip($file_path, $upload_path) === TRUE) {
            //
            $this->cleanup_zip($upload_path, 'Không xóa được file ZIP sau khi giải nén code');
            //die(__CLASS__ . ':' . __LINE__);

            // nếu là giải nén trong cache -> copy file sang thư mục public
            if ($main_zip === true || $this->using_via_ftp() === true) {
                if ($main_zip === true) {
                    $upload_path .= 'ci4-for-wordpress-main/';
                }
                //die( $upload_path );
                echo 'upload path: ' . $upload_path . ' --- ' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;

                // lấy danh sách các file để còn copy
                $this->file_re_cache = [];
                // chỉ update các file trong thư mục chỉ định
                if ($main_zip === true) {
                    $this->get_all_file_in_folder($upload_path . 'app/');
                    $this->get_all_file_in_folder($upload_path . 'public/');
                } else {
                    $this->get_all_file_in_folder($upload_path);
                }

                // lấy danh sách các thư mục để lát còn XÓA
                $this->dir_re_cache = [];
                $this->rmdir_from_cache($upload_path);

                //
                //print_r( $this->file_re_cache );
                //print_r( $this->dir_re_cache );
                //die( $upload_path );

                // tạo thư mục nếu chưa có -> tạo trước khi lật ngược mảng
                foreach ($this->dir_re_cache as $dir) {
                    $dir = str_replace($upload_path, PUBLIC_HTML_PATH, $dir);

                    //
                    if (!is_dir($dir)) {
                        echo 'Create dir: ' . $dir . ' --- ' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
                        //continue;

                        // tạo thư mục thông qua FTP
                        if ($this->using_via_ftp() === true) {
                            $this->file_model->create_dir($dir);
                        }
                        // tạo mặc định
                        else {
                            $this->mk_dir($dir, __CLASS__ . ':' . __LINE__);
                        }
                    }
                }

                // các file không cần update
                $deny_file_update = [
                    '.gitattributes',
                    '.gitignore',
                    'system.zip',
                    'database.zip',
                ];

                // copy file bằng php thông thường -> nhanh
                if ($this->using_via_ftp() !== true) {
                    // chuyển file
                    foreach ($this->file_re_cache as $file) {
                        if (in_array(basename($file), $deny_file_update)) {
                            echo 'deny file update: ' . $file . '<br>' . PHP_EOL;
                            continue;
                        }
                        echo 'from: ' . $file . '<br>' . PHP_EOL;

                        //
                        $to = str_replace($upload_path, PUBLIC_HTML_PATH, $file);
                        echo 'to: ' . $to . ' --- ' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;

                        // đổi tên file -> tương đương với copy và unlink
                        if (!rename($file, $to)) {
                            // không được thì thử copy xong xóa
                            if (copy($file, $to)) {
                                unlink($file);
                            }
                        }
                    }
                }
                // chuyển file thông qua tạo kết nối qua FTP
                else {
                    $check_dir = $this->file_model->root_dir();
                    $has_ftp = false;
                    if ($check_dir === true) {
                        echo 'ftp server: ' . $this->file_model->ftp_server . '<br>' . PHP_EOL;
                        echo 'base dir: ' . $this->file_model->base_dir . '<br>' . PHP_EOL;

                        // tạo kết nối
                        $conn_id = ftp_connect($this->file_model->ftp_server);

                        // đăng nhập
                        if ($this->file_model->base_dir != '') {
                            if (ftp_login($conn_id, FTP_USER, FTP_PASS)) {
                                $has_ftp = true;
                            }
                        }
                    } else {
                        echo 'FTP ERROR! ' . $check_dir . '<br>' . PHP_EOL;
                    }

                    // chuyển file
                    foreach ($this->file_re_cache as $file) {
                        if (in_array(basename($file), $deny_file_update)) {
                            echo 'deny file update: ' . $file . '<br>' . PHP_EOL;
                            continue;
                        }
                        echo 'from: ' . $file . '<br>' . PHP_EOL;

                        //
                        $to = str_replace($upload_path, PUBLIC_HTML_PATH, $file);
                        echo 'to: ' . $to . ' --- ' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
                        if ($has_ftp === true) {
                            // nếu trong chuỗi file không có root dir -> báo lỗi
                            if (strpos($to, '/' . $this->file_model->base_dir . '/') !== false) {
                                $to = strstr($to, '/' . $this->file_model->base_dir . '/');
                            }

                            //
                            if (ftp_put($conn_id, $to, $file, FTP_BINARY)) {
                                echo '<em>' . $to . '</em><br>' . PHP_EOL;
                            } else {
                                echo 'ERROR:' . __LINE__ . ' <strong>' . $to . '</strong><br>' . PHP_EOL;
                            }
                        } else {
                            echo 'ERROR:' . __LINE__ . ' <strong>' . $to . '</strong><br>' . PHP_EOL;
                        }

                        // xong thì xóa luôn file
                        unlink($file);
                    }

                    // close the connection
                    if ($check_dir === true) {
                        ftp_close($conn_id);
                    }
                }

                /*
                 * dọn dẹp các file dư thừa sau khi copy xong
                 */
                if ($main_zip === true) {
                    $this->file_re_cache = [];
                    $this->get_all_file_in_folder($upload_path);
                    //print_r( $this->file_re_cache );

                    // xóa file -> thì mới xóa được thư mục
                    foreach ($this->file_re_cache as $file) {
                        echo 'un-link: ' . $file . ' --- ' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
                        unlink($file);
                    }

                    //
                    //die( $upload_path );
                }

                /*
                 * dọp dẹp thư mục sau khi xử lý xong các file
                 */
                //print_r( $this->dir_re_cache );
                $this->dir_re_cache = array_reverse($this->dir_re_cache);
                //print_r( $this->dir_re_cache );
                foreach ($this->dir_re_cache as $dir) {
                    echo 'rm dir: ' . $dir . ' --- ' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
                    rmdir($dir);
                }
            }
        }
    }

    // chức năng upload file code zip lên host và giải nén -> update code
    public function update_code()
    {
        if (!empty($this->MY_post('data'))) {
            $this->unzip_code();
        }

        //
        $arr_list_thirdparty = $this->get_list_thirdparty([
            'public/thirdparty',
            'vendor',
            'app/ThirdParty',
        ]);
        //print_r( $arr_list_thirdparty );

        //
        $arr_download_thirdparty = [
            //'https://github.com/vuejs/vue/releases',
            'https://v2.vuejs.org/v2/guide/installation.html?current_version=2.7.13',
            'https://github.com/PHPMailer/PHPMailer',
            'https://jquery.com/download/?current_version=3.6.1',
            'https://getbootstrap.com/docs/5.0/getting-started/download/?current_version=5.0.2',
            'https://icons.getbootstrap.com/?current_version=1.9.0',
            'https://www.tiny.cloud/get-tiny/?current_version=4.9.11',
            'https://fontawesome.com/v4/icons/?current_version=4.7.0',
            'https://plugins.jquery.com/datetimepicker/?current_version=2.3.6',
            'https://github.com/select2/select2/releases/tag/4.0.13',
            'https://jqueryui.com/download/?current_version=1.13.2',
            'https://github.com/jquery-validation/jquery-validation/releases/tag/1.9.0',
            'https://angularjs.org/?current_version=1.8.2',
            'https://github.com/zaloplatform/zalo-php-sdk',
            'https://github.com/itvn9online/Nestable?current_version=2',
        ];

        //
        $this->teamplate_admin['content'] = view(
            'admin/update_view',
            array(
                // xác định các thư mục deleted code có tồn tại không
                'app_deleted_exist' => $this->check_deleted_exist(),
                // xác định xem thư mục theme cũ có tồn tại không
                //'theme_deleted_exist' => $theme_deleted_exist,
                'link_download_github' => $this->link_download_github,
                'link_download_system_github' => $this->link_download_system_github,
                'arr_list_thirdparty' => $arr_list_thirdparty,
                'arr_download_thirdparty' => $arr_download_thirdparty,
            )
        );
        return view('admin/admin_teamplate', $this->teamplate_admin);
    }

    private function get_list_thirdparty($dirs)
    {
        $arr = [];

        //
        foreach ($dirs as $dir) {
            foreach (glob(PUBLIC_HTML_PATH . $dir . '/*.zip') as $filename) {
                //echo $filename . '<br>' . PHP_EOL;

                //
                $arr[] = $dir . '/' . basename($filename);
            }
        }

        //
        return $arr;
    }

    private function check_deleted_exist($arr = NULL)
    {
        //
        if ($arr === NULL) {
            $arr = $this->dir_deleted_list;
        }
        //print_r( $arr );

        //
        $result = false;
        foreach ($arr as $dir) {
            if (is_dir($dir)) {
                $result = true;
                break;
            }
        }
        return $result;
    }

    private function cleanup_deleted_dir($dirs)
    {
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
        //print_r( $dirs );
        //die( __CLASS__ . ':' . __LINE__ );

        // lấy danh sách file và thư mục để XÓA
        $this->file_re_cache = [];
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
        //print_r( $this->file_re_cache );
        $this->dir_re_cache = [];
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
        //print_r( $this->dir_re_cache );
        foreach ($dirs as $v) {
            if (!is_dir($v)) {
                continue;
            }
            //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
            //echo $v . '<br>' . PHP_EOL;

            // file
            $this->get_all_file_in_folder($v);

            // dir
            $this->dir_re_cache[] = $v;
            $this->rmdir_from_cache($v);
        }
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
        //print_r( $this->file_re_cache );
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
        //print_r( $this->dir_re_cache );
        $this->dir_re_cache = array_reverse($this->dir_re_cache);
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
        //print_r( $this->dir_re_cache );
        //die( __CLASS__ . ':' . __LINE__ );

        // xóa bằng php thường
        if ($this->using_via_ftp() !== true) {
            foreach ($this->file_re_cache as $file) {
                if (!file_exists($file)) {
                    continue;
                }
                echo $file . '<br>' . PHP_EOL;
                unlink($file);
            }

            //
            foreach ($this->dir_re_cache as $dir) {
                if (!is_dir($dir)) {
                    continue;
                }
                echo $dir . '<br>' . PHP_EOL;
                rmdir($dir);
            }
        }
        // xóa thông qua ftp
        else {
            $check_dir = $this->file_model->root_dir();
            $has_ftp = false;
            if ($check_dir === true) {
                echo 'ftp server: ' . $this->file_model->ftp_server . '<br>' . PHP_EOL;
                echo 'base dir: ' . $this->file_model->base_dir . '<br>' . PHP_EOL;

                // tạo kết nối
                $conn_id = ftp_connect($this->file_model->ftp_server);

                // đăng nhập
                if ($this->file_model->base_dir != '') {
                    if (ftp_login($conn_id, FTP_USER, FTP_PASS)) {
                        $has_ftp = true;
                    }
                }
            } else {
                echo 'FTP ERROR! ' . $check_dir . '<br>' . PHP_EOL;
            }

            // XÓA file
            foreach ($this->file_re_cache as $file) {
                if (!file_exists($file)) {
                    continue;
                }
                echo $file . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
                //continue;

                //
                $to = $file;
                if ($has_ftp === true) {
                    // nếu trong chuỗi file không có root dir -> báo lỗi
                    if (strpos($to, '/' . $this->file_model->base_dir . '/') !== false) {
                        $to = strstr($to, '/' . $this->file_model->base_dir . '/');
                    }

                    //
                    if (ftp_delete($conn_id, $to)) {
                        echo '<em>' . $to . '</em><br>' . PHP_EOL;
                    } else {
                        echo 'ERROR:' . __LINE__ . ' <strong>' . $to . '</strong><br>' . PHP_EOL;
                    }
                } else {
                    echo 'ERROR:' . __LINE__ . ' <strong>' . $to . '</strong><br>' . PHP_EOL;
                }
            }

            // XÓA thư mục
            foreach ($this->dir_re_cache as $dir) {
                if (!is_dir($dir)) {
                    continue;
                }
                echo $dir . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
                //continue;

                //
                $to = $dir;
                if ($has_ftp === true) {
                    // nếu trong chuỗi dir không có root dir -> báo lỗi
                    if (strpos($to, '/' . $this->file_model->base_dir . '/') !== false) {
                        $to = strstr($to, '/' . $this->file_model->base_dir . '/');
                    }

                    //
                    if (ftp_rmdir($conn_id, $to)) {
                        echo '<em>' . $to . '</em><br>' . PHP_EOL;
                    } else {
                        echo 'ERROR:' . __LINE__ . ' <strong>' . $to . '</strong><br>' . PHP_EOL;
                    }
                } else {
                    echo 'ERROR:' . __LINE__ . ' <strong>' . $to . '</strong><br>' . PHP_EOL;
                }
            }

            // close the connection
            if ($check_dir === true) {
                ftp_close($conn_id);
            }
        }
    }

    public function restore_code()
    {
        if ($this->check_deleted_exist() !== true) {
            $this->base_model->alert('Không tồn tại thư mục deleted', 'error');
        }

        // tìm các thư mục deleted
        foreach ($this->dir_list as $v) {
            $v_deleted = $v . '-deleted';
            // nếu không có thư mục deleted -> bỏ qua
            if (!is_dir($v_deleted)) {
                echo 'DIR NOT EXIST! ' . $v_deleted . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
                continue;
            }

            // xóa code trong thư mục
            if (is_dir($v)) {
                $this->cleanup_deleted_dir([
                    $v,
                ]);
            }

            // đổi tên thư mục delete
            if (!$this->MY_rename($v_deleted, $v)) {
                die('ERROR rename! ' . $v_deleted . ':' . __CLASS__ . ':' . __LINE__);
            }
        }

        //die(__FILE__.':'.__LINE__);
        die('<script>top.done_submit_restore_code();</script>');
    }

    // xóa code trong thư mục deleted nếu chắc chắn không còn lỗi
    public function cleanup_code()
    {
        if ($this->check_deleted_exist() !== true) {
            $this->base_model->alert('Không tồn tại thư mục deleted', 'error');
        }

        // tìm các thư mục deleted
        foreach ($this->dir_deleted_list as $v) {
            // nếu không có thư mục deleted -> bỏ qua
            if (!is_dir($v)) {
                echo 'DIR NOT EXIST! ' . $v . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
                continue;
            }

            // xóa code trong thư mục
            if (is_dir($v)) {
                $this->cleanup_deleted_dir([
                    $v,
                ]);
            }
        }

        //die(__FILE__.':'.__LINE__);
        die('<script>top.done_submit_restore_code();</script>');
    }

    public function cleanup_matching_cache($default_key = '')
    {
        $data = $this->MY_post('data', $default_key);

        if (empty($data) || strlen($data) < 4) {
            $this->base_model->alert('Từ khóa khớp dữ liệu quá ngắn', 'error');
        }

        //
        $this->cleanup_cache($data . '-');
        // xóa thêm đối với term
        if ($data == 'term') {
            $this->cleanup_cache('get_all_taxonomy-');
        }
        //die(__CLASS__ . ':' . __LINE__);

        //
        $this->base_model->alert('Xóa cache theo key thành công. Matching: ' . $data);
    }

    public function unzip_thirdparty()
    {
        $this->vendor_sync(false);
        echo '<script>top.after_unzip_thirdparty();</script>';
        $this->base_model->alert('Đồng bộ lại Code bên thứ 3 và Database thành công!');
    }

    public function reset_term_permalink()
    {
        $space_reset = $this->base_model->scache(__FUNCTION__);
        if ($space_reset != NULL) {
            $this->base_model->alert('Hệ thống đang bận! Vui lòng thử lại sau ' . ($this->space_reset_permalink - (time() - $space_reset)) . ' giây...', 'warning');
        }

        //
        global $arr_custom_taxonomy;
        $allow_taxonomy = [
            TaxonomyType::POSTS,
            TaxonomyType::TAGS,
            //TaxonomyType::BLOGS,
            //TaxonomyType::BLOG_TAGS,
            TaxonomyType::PROD_CATS,
            TaxonomyType::PROD_TAGS,
        ];
        foreach ($arr_custom_taxonomy as $k => $v) {
            if (!in_array($k, $allow_taxonomy)) {
                $allow_taxonomy[] = $k;
            }
        }
        //print_r($allow_taxonomy);

        // xóa hết term permalink trong db đi để nạp lại
        $this->base_model->update_multiple('terms', [
            // SET
            //'term_permalink' => '',
            'updated_permalink' => 0,
        ], [
            // WHERE
            //'term_permalink !=' => '',
            'updated_permalink >' => 0,
            'updated_permalink <' => time() + 3600 - ($this->space_reset_permalink * 2),
        ], [
            /*
            'where_in' => array(
                'taxonomy' => $allow_taxonomy
            ),
            */
            'debug_backtrace' => debug_backtrace()[1]['function'],
            // hiển thị mã SQL để check
            'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            // mặc định sẽ remove các field không có trong bảng, nếu muốn bỏ qua chức năng này thì kích hoạt no_remove_field
            //'no_remove_field' => 1
        ]);

        // cập nhật lại cho 1 ít term
        $data = $this->base_model->select(
            '*',
            WGR_TERM_VIEW,
            array(
                // các kiểu điều kiện where
                'updated_permalink' => 0,
            ),
            array(
                'where_in' => array(
                    'taxonomy' => $allow_taxonomy
                ),
                'order_by' => array(
                    'term_id' => 'DESC'
                ),
                // hiển thị mã SQL để check
                'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                // trả về COUNT(column_name) AS column_name
                //'selectCount' => 'ID',
                // trả về tổng số bản ghi -> tương tự mysql num row
                //'getNumRows' => 1,
                //'offset' => 0,
                'limit' => 500
            )
        );
        foreach ($data as $v) {
            $this->term_model->update_term_permalink($v);
        }
        $this->base_model->scache(__FUNCTION__, time(), $this->space_reset_permalink);

        //
        $this->base_model->alert('Reset Term Permalink thành công!');
    }

    public function reset_post_permalink()
    {
        $space_reset = $this->base_model->scache(__FUNCTION__);
        if ($space_reset != NULL) {
            $this->base_model->alert('Hệ thống đang bận! Vui lòng thử lại sau ' . ($this->space_reset_permalink - (time() - $space_reset)) . ' giây...', 'warning');
        }

        //
        global $arr_custom_post_type;
        $allow_post_type = [
            PostType::PAGE,
            PostType::POST,
            //PostType::BLOG,
            PostType::PROD,
        ];
        foreach ($arr_custom_post_type as $k => $v) {
            if (!in_array($k, $allow_post_type)) {
                $allow_post_type[] = $k;
            }
        }
        //print_r($allow_post_type);

        // xóa hết term permalink trong db đi để nạp lại
        $this->base_model->update_multiple('posts', [
            // SET
            //'post_permalink' => '',
            'updated_permalink' => 0,
        ], [
            // WHERE
            //'post_permalink !=' => '',
            'updated_permalink >' => 0,
            'updated_permalink <' => time() + 3600 - ($this->space_reset_permalink * 2),
        ], [
            'where_in' => array(
                'post_type' => $allow_post_type
            ),
            'debug_backtrace' => debug_backtrace()[1]['function'],
            // hiển thị mã SQL để check
            'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            // mặc định sẽ remove các field không có trong bảng, nếu muốn bỏ qua chức năng này thì kích hoạt no_remove_field
            //'no_remove_field' => 1
        ]);

        // cập nhật lại cho 1 ít post
        $data = $this->base_model->select(
            '*',
            'posts',
            array(
                // các kiểu điều kiện where
                'updated_permalink' => 0,
            ),
            array(
                'where_in' => array(
                    'post_type' => $allow_post_type
                ),
                'order_by' => array(
                    'ID' => 'DESC'
                ),
                // hiển thị mã SQL để check
                'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                // trả về COUNT(column_name) AS column_name
                //'selectCount' => 'ID',
                // trả về tổng số bản ghi -> tương tự mysql num row
                //'getNumRows' => 1,
                //'offset' => 0,
                'limit' => 500
            )
        );
        foreach ($data as $v) {
            $this->post_model->update_post_permalink($v);
        }
        $this->base_model->scache(__FUNCTION__, time(), $this->space_reset_permalink);

        //
        $this->base_model->alert('Reset Post Permalink thành công!');
    }
}
