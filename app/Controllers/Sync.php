<?php

namespace App\Controllers;

// Libraries
//use App\Libraries\UsersType;
use App\Helpers\HtmlTemplate;
use App\Libraries\LanguageCost;
use App\Libraries\ConfigType;

//
class Sync extends BaseController
{
    public $lang_key = '';
    public $base_model = null;
    public $term_model = null;

    public function __construct()
    {
        // nạp base_model để hàm cdn404 còn dùng
        $this->base_model = new \App\Models\Base();

        // Nếu người dùng truy cập vào link cdn -> báo lỗi luôn
        if (CDN_BASE_URL != '' && strpos(CDN_BASE_URL, '/' . $_SERVER['HTTP_HOST'] . '/') !== false) {
            $this->cdn404();
        } else if (CDN_UPLOADS_URL != '' && strpos(CDN_UPLOADS_URL, '/' . $_SERVER['HTTP_HOST'] . '/') !== false) {
            $this->cdn404();
        }

        // không lỗi lầm gì thì mới nạp mấy cái này
        $this->term_model = new \App\Models\Term();

        //
        //$this->cache = \Config\Services::cache();
        $this->request = \Config\Services::request();

        // test request
        // var_dump($this->request->getVar('ebe_grecaptcha_response'));
        // var_dump($this->base_model->getIPAddress());
        // var_dump($this->request->getFiles());
        // die(__CLASS__ . ':' . __LINE__);


        // Xác định ngôn ngữ dựa theo URL
        $this->prefixLang();


        //
        $this->base_model->lang_key = $this->lang_key;
        //echo $this->base_model->lang_key . PHP_EOL;
    }

    /**
     * Trả về 1 trang 404 trắng nếu người dùng truy cập vào link CDN
     **/
    protected function cdn404()
    {
        //
        ob_end_flush();

        //
        //echo http_response_code() . '<br>' . PHP_EOL;
        // if (function_exists('http_response_code')) {
        //     http_response_code(404);
        // }
        // $pcol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
        // //echo $pcol . '<br>' . PHP_EOL;
        // $response = \Config\Services::response();
        // $response->setStatusCode(404, $pcol . ' 404 Not Found');
        http_response_code(400);

        //
        $this->result_json_type([
            'code' => __LINE__,
            'error' => $_SERVER['HTTP_HOST'],
            'f' => __FUNCTION__,
        ]);
    }

    public function vendor_sync($check_thirdparty_exist = true)
    {
        // đồng bộ database
        $this->auto_sync_table_column($check_thirdparty_exist);

        // đồng bộ vendor CSS, JS -> đặt tên là thirdparty để tránh trùng lặp khi load file tĩnh ngoài frontend
        $this->action_vendor_sync('public/wp-includes/thirdparty', $check_thirdparty_exist);
        // đồng bộ vendor php
        $this->action_vendor_sync('vendor', $check_thirdparty_exist);
        // đồng bộ ThirdParty php (code php của bên thứ 3)
        $this->action_vendor_sync('app/ThirdParty', $check_thirdparty_exist);

        //
        if ($this->base_model->scache(__FUNCTION__ . 'htaccess') === null) {
            // tạo file index cho các thư mục cần bảo mật
            foreach (glob(PUBLIC_PUBLIC_PATH . '*') as $filename) {
                if (is_dir($filename)) {
                    //echo $filename . '<br>' . PHP_EOL;
                    echo basename($filename) . '<br>' . PHP_EOL;
                    $this->action_index_sync('public/' . basename($filename), $check_thirdparty_exist);
                }
            }
            foreach (glob(PUBLIC_PUBLIC_PATH . 'wp-admin/*') as $filename) {
                if (is_dir($filename)) {
                    //echo $filename . '<br>' . PHP_EOL;
                    echo basename($filename) . '<br>' . PHP_EOL;
                    $this->action_index_sync('public/wp-admin/' . basename($filename), $check_thirdparty_exist);
                }
            }

            //
            if (strpos($_SERVER['HTTP_HOST'], 'localhost') === false) {
                $f = APPPATH . 'sync.txt';
                if (is_file($f)) {
                    $this->MY_unlink($f);
                }
            }

            //
            $this->base_model->scache(__FUNCTION__ . 'htaccess', time());
        }
    }

    /**
     * daidq: tự động đồng bộ cấu trúc bảng
     * do cấu trúc bảng của wordpress thiếu 1 số tính năng so với bản CI này nên cần thêm cột để sử dụng
     */
    // tạo bảng để lưu session khi cần có thể sử dụng luôn
    protected function tbl_sessions($tbl = 'ci_sessions')
    {
        $table = WGR_TABLE_PREFIX . $tbl;

        // xem bảng này có chưa -> có rồi thì thôi
        if ($this->base_model->table_exists($table)) {
            echo 'TABLE exist ' . $table . '<br>' . PHP_EOL;
            return false;
        }

        // ENGINE sử dụng InnoDB. MEMORY không dùng được data blob
        $sql = "CREATE TABLE IF NOT EXISTS `$table` (
            `my_id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
            `id` varchar(128) NOT NULL,
            `ip_address` varchar(45) NOT NULL,
            `timestamp` timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL,
            `data` blob NOT NULL,
            PRIMARY KEY (`my_id`) ,
            INDEX (`id`),
            INDEX (`ip_address`),
            KEY (`timestamp`)
        ) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_general_ci";
        echo $sql . '<br>' . PHP_EOL;

        //
        return $this->base_model->MY_query($sql);
    }

    /**
     * Bảng dùng để gửi email định kỳ thay vì gửi luôn và ngay -> giúp tăng tốc cho website
     **/
    public function tbl_mail_queue($tbl = 'mail_queue')
    {
        $table = WGR_TABLE_PREFIX . $tbl;

        // xem bảng này có chưa -> có rồi thì thôi
        if ($this->base_model->table_exists($table)) {
            echo 'TABLE exist ' . $table . '<br>' . PHP_EOL;
            return false;
        }

        // ENGINE sử dụng InnoDB. MEMORY hay bị lỗi 'is full'
        $sql = "CREATE TABLE IF NOT EXISTS `$table` (
            `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
            `ip` VARCHAR(255) NULL,
            `session_id` VARCHAR(255) NOT NULL,
            `agent` VARCHAR(255) NULL,
            `mailto` VARCHAR(255) NULL,
            `title` VARCHAR(255) NULL,
            `content` TEXT NULL,
            `status` VARCHAR(55) NULL,
            `post_id` BIGINT(20) NULL,
            `order_id` BIGINT(20) NULL,
            `comment` VARCHAR(255) NULL,
            `created_at` BIGINT(20) NULL,
            `updated_at` BIGINT(20) NULL,
            `sended_at` BIGINT(20) NULL,
            PRIMARY KEY (`id`) ,
            INDEX (`session_id`) ,
            INDEX (`mailto`) ,
            INDEX (`status`) ,
            INDEX (`post_id`) ,
            INDEX (`order_id`)
            ) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_general_ci";
        echo $sql . '<br>' . PHP_EOL;

        //
        return $this->base_model->MY_query($sql);
    }

    /**
     * Bảng dùng để lưu phiên đăng nhập của người dùng
     * 1 số website không cho đăng nhập nhiều nơi cùng lúc -> dùng bảng này để xác định phiên cuối
     **/
    protected function tbl_logged($tbl = 'ci_logged')
    {
        $table = WGR_TABLE_PREFIX . $tbl;

        // xem bảng này có chưa -> có rồi thì thôi
        if ($this->base_model->table_exists($table)) {
            echo 'TABLE exist ' . $table . '<br>' . PHP_EOL;
            return false;
        }

        // ENGINE sử dụng InnoDB. MEMORY hay bị lỗi 'is full'
        $sql = "CREATE TABLE IF NOT EXISTS `$table` (
            `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
            `ip` VARCHAR(255) NULL,
            `key` VARCHAR(255) NULL,
            `session_id` VARCHAR(255) NOT NULL,
            `agent` VARCHAR(255) NULL,
            `created_at` BIGINT(20) NULL,
            PRIMARY KEY (`id`) ,
            INDEX (`key`) ,
            INDEX (`session_id`)
            ) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_general_ci";
        echo $sql . '<br>' . PHP_EOL;

        //
        return $this->base_model->MY_query($sql);
    }

    /**
     * Bảng để lưu event webhook của Zalo OA
     **/
    protected function tbl_webhook_zalooa($tbl = 'webhook_zalo_oa')
    {
        $table = WGR_TABLE_PREFIX . $tbl;

        // xem bảng này có chưa -> có rồi thì thôi
        if ($this->base_model->table_exists($table)) {
            echo 'TABLE exist ' . $table . '<br>' . PHP_EOL;
            return false;
        }

        //
        $sql = "CREATE TABLE IF NOT EXISTS `$table` (
            `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
            `ip` VARCHAR(255) NULL,
            `event_name` VARCHAR(255) NULL,
            `app_id` VARCHAR(255) NULL,
            `content` TEXT NULL,
            `is_deleted` TINYINT(2) NULL,
            `created_at` BIGINT(20) NULL,
            PRIMARY KEY (`id`) ,
            INDEX (`event_name`) ,
            INDEX (`app_id`) ,
            INDEX (`is_deleted`)
            ) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_general_ci";
        echo $sql . '<br>' . PHP_EOL;

        //
        return $this->base_model->MY_query($sql);
    }

    /**
     * Nhân bản 1 bảng
     **/
    protected function cloneDbTable($from, $to)
    {
        $from = WGR_TABLE_PREFIX . $from;
        $to = WGR_TABLE_PREFIX . $to;

        // xem bảng này có chưa -> có rồi thì thôi
        if ($this->base_model->table_exists($to)) {
            echo 'TABLE exist ' . $to . '<br>' . PHP_EOL;
            return false;
        }

        //
        $sql = "CREATE TABLE IF NOT EXISTS `$to` LIKE `$from`";
        echo $sql . '<br>' . PHP_EOL;

        //
        return $this->base_model->MY_query($sql);
    }

    // tạo view cho term để select dữ liệu cho tiện
    protected function view_terms($has_table_change = false)
    {
        // nếu không xác định được sự thay đổi của bảng
        if ($has_table_change === false) {
            // kiểm tra xem có view này chưa
            if ($this->base_model->table_exists(WGR_TERM_VIEW)) {
                echo 'TABLE exist ' . WGR_TERM_VIEW . '<br>' . PHP_EOL;
                return false;
            }
        }

        // lấy các cột trong bảng term để so sánh cột nào chưa có
        $tbl_terms = $this->base_model->default_data('terms');
        if (empty($tbl_terms)) {
            die(__CLASS__ . ':' . __LINE__);
        }
        //print_r( $tbl_terms );

        //
        $tbl_term_taxonomy = $this->base_model->default_data('term_taxonomy');
        if (empty($tbl_term_taxonomy)) {
            die(__CLASS__ . ':' . __LINE__);
        }
        //print_r( $tbl_term_taxonomy );

        //
        $arr_term_taxonomy = [];
        foreach ($tbl_term_taxonomy as $k => $v) {
            //echo $k . '<br>' . PHP_EOL;
            if (isset($tbl_terms[$k])) {
                echo '- - - - - - unset term_taxonomy.' . $k . '<br>' . PHP_EOL;
            } else {
                $arr_term_taxonomy[] = 't.' . $k;
            }
        }
        //print_r( $arr_term_taxonomy );

        // -> dùng CI query builder để tạo query -> tránh sql injection
        $sql = $this->base_model->select(
            'terms.*,' . implode(',', $arr_term_taxonomy),
            'terms',
            array(
                // các kiểu điều kiện where
            ),
            array(
                'join' => array(
                    'term_taxonomy t' => 'terms.term_id = t.term_id'
                ),
                'group_by' => array(
                    'terms.term_id',
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                'get_query' => 1,
                //'offset' => 2,
                'limit' => -1
            )
        );

        //
        $sql = "CREATE OR REPLACE VIEW " . WGR_TERM_VIEW . " AS " . $sql;
        echo $sql . '<br>' . PHP_EOL;
        echo 'CREATE OR REPLACE VIEW ' . WGR_TERM_VIEW . ' <br>' . PHP_EOL;

        //
        //die( __CLASS__ . ':' . __LINE__ );
        return $this->base_model->MY_query($sql);
    }

    // tạo view cho post để select dữ liệu cho tiện
    protected function view_posts($has_table_change)
    {
        // nếu không xác định được sự thay đổi của bảng
        if ($has_table_change === false) {
            // kiểm tra xem có view này chưa
            if ($this->base_model->table_exists(WGR_POST_VIEW)) {
                echo 'TABLE exist ' . WGR_POST_VIEW . '<br>' . PHP_EOL;
                return false;
            }
        }

        // lấy các cột trong bảng term để so sánh cột nào chưa có
        $tbl_posts = $this->base_model->default_data('posts');
        if (empty($tbl_posts)) {
            die(__CLASS__ . ':' . __LINE__);
        }
        //print_r( $tbl_posts );

        //
        $tbl_term_taxonomy = $this->base_model->default_data('term_taxonomy');
        if (empty($tbl_term_taxonomy)) {
            die(__CLASS__ . ':' . __LINE__);
        }
        //print_r( $tbl_term_taxonomy );

        //
        $arr_term_taxonomy = [];
        foreach ($tbl_term_taxonomy as $k => $v) {
            //echo $k . '<br>' . PHP_EOL;
            if (isset($tbl_posts[$k])) {
                echo '- - - - - - unset term_taxonomy.' . $k . '<br>' . PHP_EOL;
            } else {
                $arr_term_taxonomy[] = 't.' . $k;
            }
        }
        //print_r( $arr_term_taxonomy );

        //
        $tbl_term_relationships = $this->base_model->default_data('term_relationships');
        if (empty($tbl_term_relationships)) {
            die(__CLASS__ . ':' . __LINE__);
        }
        //print_r( $tbl_term_relationships );

        //
        $arr_term_relationships = [];
        foreach ($tbl_term_relationships as $k => $v) {
            //echo $k . '<br>' . PHP_EOL;
            if (isset($tbl_term_taxonomy[$k])) {
                echo '- - - - - - unset term_relationships.' . $k . '<br>' . PHP_EOL;
            } else {
                $arr_term_relationships[] = 'r.' . $k;
            }
        }
        //print_r( $arr_term_relationships );

        // -> dùng CI query builder để tạo query -> tránh sql injection
        $sql = $this->base_model->select(
            'posts.*,' . implode(',', $arr_term_taxonomy) . ',' . implode(',', $arr_term_relationships),
            'posts',
            array(
                // các kiểu điều kiện where
            ),
            array(
                'join' => array(
                    'term_relationships r' => 'r.object_id = posts.ID',
                    'term_taxonomy t' => 'r.term_taxonomy_id = t.term_taxonomy_id',
                ),
                'group_by' => array(
                    'posts.ID',
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                'get_query' => 1,
                //'offset' => 2,
                'limit' => -1
            )
        );
        //echo $sql . '<br>' . PHP_EOL;

        //
        //return false;

        //
        $sql = "CREATE OR REPLACE VIEW " . WGR_POST_VIEW . " AS " . $sql;
        echo $sql . '<br>' . PHP_EOL;
        echo 'CREATE OR REPLACE VIEW ' . WGR_POST_VIEW . ' <br>' . PHP_EOL;

        //
        return $this->base_model->MY_query($sql);
    }

    /**
     * Tạo các thư mục logs nếu chưa có
     **/
    protected function create_sub_writable($root_dir = WRITEPATH)
    {
        $dirs = [
            'logs',
            'logs/zalo-oa',
            'logged',
        ];

        // chạy vòng lặp và tạo các thư mục này nếu chưa có
        foreach ($dirs as $d) {
            $d = $root_dir . $d;
        }
        if (!is_dir($d)) {
            mkdir($d, DEFAULT_DIR_PERMISSION) or die('ERROR create cache dir');
            chmod($d, DEFAULT_DIR_PERMISSION);
        }
    }

    protected function auto_sync_table_column($check_thirdparty_exist = true)
    {
        /**
         * db không cần update liên tục, nếu cần thì clear cache để tái sử dụng
         */
        $last_run = $this->base_model->scache(__FUNCTION__);
        if ($check_thirdparty_exist === true && $last_run !== null) {
            echo __FUNCTION__ . ' RUN ' . (time() - $last_run) . 's ago ---`/ CLEAR cache for continue... ' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
            return false;
        }

        // tạo bảng nếu chưa có
        $this->tbl_webhook_zalooa();
        $this->cloneDbTable('posts', 'orders');
        $this->cloneDbTable('postmeta', 'ordermeta');
        $this->cloneDbTable('options', 'options_deleted');
        $this->tbl_sessions();
        $this->tbl_mail_queue();
        $this->tbl_logged();

        // tạo thư mục nếu chưa có
        $this->create_sub_writable();

        //
        $prefix = WGR_TABLE_PREFIX;

        // các cột khi gặp sẽ thêm cả chức năng add index
        $arr_index_cloumn = [
            'lang_key',
            'is_deleted',
            'lang_parent',
            'term_status',
            'parent_id',
            'parent',
            'district_id',
            'province_id',
            'term_type',
            'term_level',
        ];
        //die( __CLASS__ . ':' . __LINE__ );

        // tự động fixed các cột của bảng nếu chưa có
        $arr_add_cloumn = [
            $prefix . 'users' => [
                'ci_pass' => 'VARCHAR(255) NULL COMMENT \'Mật khẩu đăng nhập cho phiên bản CI-wordpress\'',
                'member_type' => 'VARCHAR(55) NOT NULL COMMENT \'Phân loại thành viên (role)\'',
                'member_verified' => 'TINYINT(2) NOT NULL DEFAULT \'0\' COMMENT \'Kích hoạt qua email hoặc phone. 0 = chưa, 1 = rồi\'',
                'phone_verified' => 'TINYINT(2) NOT NULL DEFAULT \'0\' COMMENT \'Kích hoạt qua phone. 0 = chưa, 1 = rồi\'',
                'is_deleted' => 'TINYINT(2) NOT NULL DEFAULT \'0\' COMMENT \'0 = hiển thị, 1 = xóa\'',
                'parent_id' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'ID của tài khoản cha nếu có\'',
                'district_id' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'ID của quận huyện\'',
                'province_id' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'ID của tỉnh thành phố\'',
                'last_login' => 'DATETIME NOT NULL',
                'login_type' => 'VARCHAR(55) NOT NULL COMMENT \'Phân loại kiểu đăng nhập\'',
                'last_updated' => 'DATETIME NOT NULL',
                'user_birthday' => 'DATE NULL COMMENT \'Sinh nhật của thành viên\'',
                'user_phone' => 'VARCHAR(55) NULL COMMENT \'Điện thoại liên hệ\'',
                'number_phone' => 'BIGINT(20) NULL COMMENT \'Điện thoại liên hệ dạng số nguyên\'',
                'avatar' => 'VARCHAR(255) NOT NULL DEFAULT \'\' COMMENT \'Ảnh đại diện\'',
                'firebase_uid' => 'VARCHAR(255) NOT NULL DEFAULT \'\' COMMENT \'User ID khi đăng nhập qua firebase\'',
                'firebase_source_uid' => 'VARCHAR(255) NOT NULL DEFAULT \'\' COMMENT \'Nguồn cập nhật firebase uid\'',
                'user_fund' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'Số dư tài khoản\'',
                'zalo_oa_id' => 'VARCHAR(255) NOT NULL DEFAULT \'\' COMMENT \'ID của người dùng trên Zalo OA\'',
                'zalo_oa_data' => 'TEXT NULL COMMENT \'Dữ liệu của người dùng được trả về khi kết nối với Zalo OA\'',
                'zalo_oa_last_interact' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'Thời gian tương tác cuối của user, một số tin nhắn qua Zalo OA có giới hạn theo thời gian tương tác cuối\'',
                // rememberme key -> là tham số sẽ thay đổi mỗi lần người dùng đổi pass, dùng tăng bảo mật cho chức năng nhớ mật khẩu đăng nhập
                'rememberme_key' => 'VARCHAR(255) NULL COMMENT \'Mỗi tài khoản sẽ có 1 key dùng để lưu phiên đăng nhập\'',
                'created_source' => 'VARCHAR(255) NULL COMMENT \'Nguồn tạo dữ liệu\'',
            ],
            $prefix . 'posts' => [
                'post_shorttitle' => 'VARCHAR(255) NOT NULL DEFAULT \'\' COMMENT \'Tên rút gọn của post\'',
                'post_shortslug' => 'VARCHAR(255) NOT NULL DEFAULT \'\' COMMENT \'Slug rút gọn của post\'',
                'post_permalink' => 'VARCHAR(255) NOT NULL DEFAULT \'\' COMMENT \'Lưu permalink để cho nhẹ server\'',
                'updated_permalink' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'Lưu thời gian cập nhật permalink\'',
                'post_viewed' => 'BIGINT(20) NOT NULL DEFAULT \'1\' COMMENT \'Đếm số lượt xem bài viết\'',
                'category_id' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'ID của category chính\'',
                'lang_key' => 'VARCHAR(10) NOT NULL DEFAULT \'en\' COMMENT \'Phân loại ngôn ngữ theo key quốc gia\'',
                'lang_parent' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'Dùng để xác định với các bản ghi được nhân bản từ ngôn ngữ chính\'',
                'child_count' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'Đếm tổng số bài viết con của bài này. Thường dùng cho web truyện, chap của truyện\'',
                'child_last_count' => 'BIGINT(20) NULL COMMENT \'Thời gian cập nhật child_count lần trước\'',
                'time_order' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'Sắp xếp độ ưu tiên của post dựa theo thời gian hiện tại\'',
                'category_primary_id' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'ID của danh mục chính, hữu dụng khi 1 post có nhiều danh mục\'',
                'category_primary_slug' => 'VARCHAR(255) NOT NULL DEFAULT \'\' COMMENT \'Slug của danh mục chính, dùng để làm URL nếu muốn\'',
                'category_second_id' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'ID của danh mục phụ, hữu dụng khi 1 post có nhiều danh mục\'',
                'category_second_slug' => 'VARCHAR(255) NOT NULL DEFAULT \'\' COMMENT \'Slug của danh mục phụ, dùng để làm URL nếu muốn\'',
                'post_meta_data' => 'LONGTEXT NULL COMMENT \'Lưu các post meta vào đây để đỡ phải query nhiều\'',
                'time_meta_data' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'Thời gian lưu cache cho post meta\'',
                'media_optimize' => 'TINYINT(2) NOT NULL DEFAULT \'0\' COMMENT \'Optimize hình ảnh. 0 = chưa, 1 = rồi\'',
                'created_source' => 'VARCHAR(255) NULL COMMENT \'Nguồn tạo dữ liệu\'',
            ],
            $prefix . 'terms' => [
                'term_shortname' => 'VARCHAR(255) NOT NULL DEFAULT \'\' COMMENT \'Tên rút gọn của term\'',
                'term_shortslug' => 'VARCHAR(255) NOT NULL DEFAULT \'\' COMMENT \'Slug rút gọn của term\'',
                'term_permalink' => 'VARCHAR(255) NOT NULL DEFAULT \'\' COMMENT \'Lưu permalink để cho nhẹ server\'',
                'updated_permalink' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'Lưu thời gian cập nhật permalink\'',
                'lang_key' => 'VARCHAR(10) NOT NULL DEFAULT \'en\' COMMENT \'Phân loại ngôn ngữ theo key quốc gia\'',
                'lang_parent' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'Dùng để xác định với các bản ghi được nhân bản từ ngôn ngữ chính\'',
                'term_date' => 'DATETIME NOT NULL',
                'last_updated' => 'DATETIME NOT NULL',
                'is_deleted' => 'TINYINT(2) NOT NULL DEFAULT \'0\' COMMENT \'0 = hiển thị, 1 = xóa\'',
                'term_order' => 'INT(10) NOT NULL DEFAULT \'0\' COMMENT \'Sắp xếp vị trí hiển thị, số càng to thì độ ưu tiên càng cao\'',
                'term_status' => 'TINYINT(2) NOT NULL DEFAULT \'0\' COMMENT \'Trạng thái hiển thị của 1 term. 0 = hiển thị, 1 = ẩn\'',
                'term_viewed' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'Đếm số lượt xem danh mục\'',
                'term_meta_data' => 'LONGTEXT NOT NULL DEFAULT \'\' COMMENT \'Lưu các post meta vào đây để đỡ phải query nhiều\'',
                'term_ids' => 'VARCHAR(255) NULL COMMENT \'Danh sách ID của các nhóm con, dùng để tạo query cho nhanh\'',
                'child_count' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'Tính tổng số nhóm con để gọi lệnh lấy nhóm con nếu không NULL\'',
                'child_last_count' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'Thời gian cập nhật child_count lần trước\'',
                'term_type' => 'VARCHAR(55) NULL COMMENT \'Dùng để phân loại term, tương tự category nhưng ít dùng hơn nhiều\'',
                'term_avatar' => 'VARCHAR(255) NOT NULL DEFAULT \'\' COMMENT \'Ảnh đại diện của term\'',
                'term_favicon' => 'VARCHAR(255) NOT NULL DEFAULT \'\' COMMENT \'Hình thu nhỏ của term\'',
                'created_source' => 'VARCHAR(255) NULL COMMENT \'Nguồn tạo dữ liệu\'',
            ],
            $prefix . 'term_taxonomy' => [
                // term level -> dùng để lọc các nhóm theo cấp độ cho nó nhanh -> ví dụ khi cần lấy tất cả các nhóm cấp 1, 2, 3
                'term_level' => 'TINYINT(2) NOT NULL DEFAULT \'0\' COMMENT \'Level của nhóm, tính theo cấp độ của nhóm cha +1\'',
                'source_count' => 'VARCHAR(255) NULL COMMENT \'Lưu trữ nơi update lệnh count cho term\'',
            ],
            $prefix . 'term_relationships' => [
                'is_deleted' => 'TINYINT(2) NOT NULL DEFAULT \'0\' COMMENT \'0 = hiển thị, 1 = xóa. Trạng thái này được lấy định kỳ dựa theo trạng thái của post\'',
            ],
            $prefix . 'options' => [
                'option_type' => 'VARCHAR(55) NULL DEFAULT NULL COMMENT \'Phân loại option dành cho nhiều việc khác nhau\'',
                'lang_key' => 'VARCHAR(10) NOT NULL DEFAULT \'en\' COMMENT \'Phân loại ngôn ngữ theo key quốc gia\'',
                'lang_parent' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'Dùng để xác định với các bản ghi được nhân bản từ ngôn ngữ chính\'',
                'last_updated' => 'DATETIME NOT NULL',
                'is_deleted' => 'TINYINT(2) NOT NULL DEFAULT \'0\' COMMENT \'0 = hiển thị, 1 = xóa\'',
                'insert_time' => 'BIGINT(20) NULL DEFAULT NULL COMMENT \'Thời gian insert bản ghi, dùng để lọc các dữ liệu insert cùng thời điểm\'',
            ],
            $prefix . 'comments' => [
                // thêm tiêu đề cho phần comment -> do bảng mặc định của wp comment không có cột này
                'comment_author_name' => 'VARCHAR(255) NULL COMMENT \'Thêm tên tác giả để tiện cho việc hiển thị\'',
                'comment_title' => 'VARCHAR(255) NULL COMMENT \'Thêm tiêu đề để tiện cho việc hiển thị\'',
                'comment_slug' => 'VARCHAR(255) NULL COMMENT \'Thêm phần slug để tiện cho quá trình tìm kiếm\'',
                'lang_key' => 'VARCHAR(10) NOT NULL DEFAULT \'en\' COMMENT \'Phân loại ngôn ngữ theo key quốc gia\'',
                'lang_parent' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'Dùng để xác định với các bản ghi được nhân bản từ ngôn ngữ chính\'',
                'is_deleted' => 'TINYINT(2) NOT NULL DEFAULT \'0\' COMMENT \'0 = hiển thị, 1 = xóa\'',
                'comment_rate' => 'TINYINT(2) NOT NULL DEFAULT \'5\' COMMENT \'Đánh giá theo thang điểm từ 1-5\'',
                'time_order' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'Sắp xếp độ ưu tiên của post dựa theo thời gian hiện tại\'',
                'created_source' => 'VARCHAR(255) NULL COMMENT \'Nguồn tạo dữ liệu\'',
            ],
            $prefix . 'orders' => [
                'product_ids' => 'VARCHAR(255) NULL COMMENT \'Products ids\'',
                'full_name' => 'VARCHAR(255) NULL',
                'first_name' => 'VARCHAR(55) NULL',
                'last_name' => 'VARCHAR(55) NULL',
                'company' => 'VARCHAR(255) NULL',
                'address' => 'VARCHAR(255) NULL',
                'state' => 'BIGINT(20) NOT NULL DEFAULT \'0\'',
                'state_name' => 'VARCHAR(255) NULL',
                'city' => 'BIGINT(20) NOT NULL DEFAULT \'0\'',
                'city_name' => 'VARCHAR(255) NULL',
                'zip_code' => 'VARCHAR(55) NULL',
                'phone' => 'VARCHAR(55) NULL',
                'email' => 'VARCHAR(255) NULL',
                'comments' => 'TEXT NULL',
                'coupon' => 'VARCHAR(55) NULL',
                'discount_type' => 'VARCHAR(55) NULL COMMENT \'Kiểu khuyến mại\'',
                'payment_method' => 'VARCHAR(55) NULL COMMENT \'Phương thức thanh toán\'',
                // 
                'order_type' => 'VARCHAR(255) NULL COMMENT \'Phân loại đơn hàng\'',
                'order_period' => 'TINYINT(2) NOT NULL DEFAULT \'0\' COMMENT \'Giá trị đơn hàng theo gói định sẵn\'',
                'order_money' => 'FLOAT NOT NULL DEFAULT \'0\' COMMENT \'Giá trị của đơn hàng\'',
                'order_discount' => 'FLOAT NOT NULL DEFAULT \'0\' COMMENT \'Tiền giảm giá cho mỗi đơn hàng\'',
                'order_bonus' => 'FLOAT NOT NULL DEFAULT \'0\' COMMENT \'Tiền cộng thêm cho mỗi đơn hàng\'',
                'shipping_fee' => 'FLOAT NULL',
                'deposit_money' => 'VARCHAR(55) NULL COMMENT \'Số tiền đặt cọc trước, có thể để là % nên định dạng field này là varchar\'',
                'deposit_value' => 'FLOAT NULL COMMENT \'Số tiền đặt cọc sau khi thanh toán online thành công\'',
                // 
                'approve_data' => 'TEXT NULL COMMENT \'Lưu trữ thông tin tóm tắt transaction gửi về từ Paypal...\'',
                'order_capture' => 'TEXT NULL COMMENT \'Lưu trữ thông tin transaction gửi về từ Paypal...\'',
                'order_ip' => 'VARCHAR(255) NULL COMMENT \'IP người đặt hàng\'',
                'order_agent' => 'VARCHAR(255) NULL COMMENT \'User agrent\'',
                'created_source' => 'VARCHAR(255) NULL COMMENT \'Nguồn tạo dữ liệu\'',
            ],
            $prefix . 'mail_queue' => [
                'updated_at' => 'BIGINT(20) NULL',
                'sended_at' => 'BIGINT(20) NULL',
            ],
        ];

        /**
         * bảng dữ liệu riêng của từng theme
         */
        $private_theme_db = THEMEPATH . 'Database_Migrations.php';
        //echo $private_theme_db . '<br>' . PHP_EOL;
        if (is_file($private_theme_db)) {
            include $private_theme_db;

            //
            //print_r( $arr_custom_alter_database );
            foreach ($arr_custom_alter_database as $k => $v) {
                //echo $k . '<br>' . PHP_EOL;
                //print_r( $v );
                if (!isset($arr_add_cloumn[$k])) {
                    $arr_add_cloumn[$k] = [];
                }

                //
                foreach ($v as $k2 => $v2) {
                    //echo $k2 . '<br>' . PHP_EOL;
                    $arr_add_cloumn[$k][$k2] = $v2;
                }
            }
        }

        //
        $arr_add_cloumn[$prefix . 'options_deleted'] = $arr_add_cloumn[$prefix . 'options'];
        //print_r($arr_add_cloumn);
        //die(__CLASS__ . ':' . __LINE__);

        //
        $has_table_change = false;
        foreach ($arr_add_cloumn as $k => $v) {
            $check_table_column = $this->base_model->default_data($k);
            if (empty($check_table_column)) {
                die(__CLASS__ . ':' . __LINE__);
            }
            //print_r( $check_table_column );
            //continue;

            //
            $last_key = '';
            foreach ($check_table_column as $last_k => $last_v) {
                $last_key = $last_k;
            }
            //echo $last_key . '<br>' . PHP_EOL;
            foreach ($v as $col => $alter) {
                //echo $col . '<br>' . PHP_EOL;
                // nếu chưa có cột này -> thêm mới
                if (!isset($check_table_column[$col])) {
                    $add_index = '';
                    if (in_array($col, $arr_index_cloumn)) {
                        $add_index = ", ADD INDEX (`$col`)";
                    } else if (strpos($alter, ', ADD INDEX ()') !== false) {
                        $alter = explode(', ADD INDEX ()', $alter)[0];
                        $add_index = ", ADD INDEX (`$col`)";
                    }
                    $alter_query = "ALTER TABLE `$k` ADD `$col` $alter AFTER `$last_key`" . $add_index;
                    echo $alter_query . '<br>' . PHP_EOL;
                    //continue;
                    //die( __CLASS__ . ':' . __LINE__ );
                    if ($this->base_model->MY_query($alter_query)) {
                        echo $col . ' column in database has been sync! <br>' . PHP_EOL;

                        //
                        $has_table_change = true;
                    } else {
                        echo 'Query failed! Please re-check query <br>' . PHP_EOL;
                    }

                    //
                    $last_key = $col;
                }
            }
        }
        //die( __CLASS__ . ':' . __LINE__ );

        // bảng post_title wordpress mặc định nó là TEXT -> chuyển về VARCHAR
        /*
        $alter_query = "ALTER TABLE `" . $prefix . "posts` CHANGE `post_title` `post_title` VARCHAR(255) NOT NULL;";
        echo $alter_query . '<br>' . PHP_EOL;
        if ( $this->base_model->MY_query( $alter_query ) ) {
        echo $prefix . 'posts - post_title column in database has been sync! <br>' . PHP_EOL;
        } else {
        echo 'Query failed! Please re-check query <br>' . PHP_EOL;
        }
        */

        // kiểm tra và tạo view nếu bảng có sự thay đổi
        $this->view_terms($has_table_change);
        $this->view_posts($has_table_change);
        // cập nhật lại tổng số nhóm con cho phần term
        $last_run = $this->term_model->sync_term_child_count();
        if ($last_run !== true) {
            echo 'sync term child count RUN ' . (time() - $last_run) . 's ago ---`/ CLEAR cache for continue... ' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
        }

        //
        //die( __CLASS__ . ':' . __LINE__ );
        $this->base_model->scache(__FUNCTION__, time());
    }

    /**
     * unzip file
     */
    protected function MY_unzip($file, $dir)
    {
        echo $file . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
        echo $dir . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
        // die(__CLASS__ . ':' . __LINE__);

        //
        $zip = new \ZipArchive();
        if ($zip->open($file) === TRUE) {
            $zip->extractTo(rtrim($dir, '/'));
            $zip->close();
            return TRUE;
        }
        return false;
    }

    /**
     * daidq: chức năng này sẽ giải nén các code trong thư mục vendor dể sử dụng nếu chưa có
     */
    protected function action_vendor_sync($dir, $check_thirdparty_exist = true)
    {
        $upload_via_ftp = false;

        // thử kiểm tra quyền đọc ghi file trong thư mục app, nếu không ghi được -> sẽ sử dụng FTP để xử lý file
        $path = PUBLIC_HTML_PATH . 'app/test_permission.txt';
        if (@file_put_contents($path, time(), LOCK_EX)) {
            chmod($path, DEFAULT_FILE_PERMISSION);
            unlink($path);
        } else {
            $upload_via_ftp = true;

            // daidq (2023-06-22): tạm thời không sync thông qua ftp
            //return false;
        }
        //var_dump( $upload_via_ftp );
        //die( __CLASS__ . ':' . __LINE__ );
        //return false;

        //
        $dir = rtrim($dir, '/');
        // nếu phải xử lý file thông qua ftp
        if ($upload_via_ftp === true) {
            //echo PUBLIC_HTML_PATH . $dir . '<br>' . PHP_EOL;
            //die( __CLASS__ . ':' . __LINE__ );

            // test quyền đọc ghi trong thư mục này để xem có thể giải nén được không
            $path = PUBLIC_HTML_PATH . $dir . '/test_permission.txt';
            if (@file_put_contents($path, time(), LOCK_EX)) {
                chmod($path, DEFAULT_FILE_PERMISSION);
                unlink($path);
            } else {
                // chuyển thư mục về 777 để có thể unzip
                $file_model = new \App\Models\File();
                if (!$file_model->ftp_my_chmod(PUBLIC_HTML_PATH . $dir, DEFAULT_DIR_PERMISSION)) {
                    die(__CLASS__ . ':' . __LINE__);
                }
            }
        }
        //die( __CLASS__ . ':' . __LINE__ );

        //
        foreach (glob(PUBLIC_HTML_PATH . $dir . '/*.zip') as $filename) {
            //echo $filename . '<br>' . PHP_EOL;
            //continue;

            //
            $file = basename($filename, '.zip');
            $check_dir = PUBLIC_HTML_PATH . $dir . '/' . $file;
            //echo $check_dir . '<br>' . PHP_EOL;

            // nếu chưa có thư mục -> giải nén
            if ($check_thirdparty_exist === false || !is_dir($check_dir)) {
                if ($this->MY_unzip($filename, PUBLIC_HTML_PATH . $dir) === TRUE) {
                    echo 'DONE! sync code ' . $file . ' <br>' . PHP_EOL;
                } else {
                    echo 'ERROR! sync code ' . $file . ' <br>' . PHP_EOL;
                }
            } else {
                echo $file . ' has been sync <br>' . PHP_EOL;
            }
        }
    }

    /**
     * Thêm file index cho các thư mục cần bảo mật -> khi truy cập vào đây sẽ không lộ các thư mục trong này
     **/
    protected function action_index_sync($dir, $check_thirdparty_exist = true)
    {
        $dir = PUBLIC_HTML_PATH . rtrim($dir, '/') . '/';
        //echo $dir . '<br>' . PHP_EOL;
        foreach (
            [
                'index.html',
                'index.htm',
                'index.php',
            ] as $v
        ) {
            if (is_file($dir . $v)) {
                return false;
            }
        }
        $dir .= 'index.html';

        //
        echo 'Create file: ' . $dir . '<br>' . PHP_EOL;
        $this->base_model->ftp_create_file(
            $dir,
            'Nice to meet you',
        );
    }

    // tự set session, do session của ci4 nó đứt liên tục
    protected function MY_session($key, $value = null)
    {
        return $this->base_model->MY_session($key, $value);
    }

    protected function set_validation_error($errors, $alert = false)
    {
        //print_r( $errors );
        foreach ($errors as $error) {
            // alert = error || warning
            if ($alert !== false) {
                $this->base_model->alert($error, $alert);
            }
            $this->base_model->msg_error_session($error);
            break;
        }
        //die( __CLASS__ . ':' . __LINE__ );
    }

    // đồng bộ nội dung về 1 kiểu
    protected function replace_content($str)
    {
        $str = str_replace('../../../public/upload/', 'upload/', $str);
        $str = str_replace('../../upload/', 'upload/', $str);
        $str = str_replace('/public/upload/', '/upload/', $str);
        $str = str_replace(DYNAMIC_BASE_URL, '', $str);
        $str = str_replace(base_url() . '/', '', $str);

        //
        return $str;
    }

    /**
     * trả về tên của class và loại bỏ phần namespace thừa
     */
    protected function get_class_name($role)
    {
        return basename(str_replace('\\', '/', $role));
    }
    protected function getClassName($role)
    {
        return strtolower($this->get_class_name($role));
    }

    /**
     * trả về URL của controller theo định dạng của namespace
     * đầu vào là __CLASS__
     * đầu ra sẽ cắt bỏ phần namespace ở đầu, giữ lại phần controller sau -> REUQEST URL
     */
    protected function base_class_url($str)
    {
        // lấy thư mục chứa file hiện tại
        //echo __DIR__ . '<br>' . PHP_EOL;
        $current_dir = basename(__DIR__);
        //echo $current_dir . '<br>' . PHP_EOL;

        //
        //echo $str . '<br>' . PHP_EOL;
        $str = str_replace('\\', '/', $str);
        //echo $str . '<br>' . PHP_EOL;

        // cắt chuỗi
        $str = explode($current_dir . '/', $str);
        //print_r( $str );

        //
        if (isset($str[1])) {
            return strtolower($str[1]);
        }

        //
        return strtolower($str[0]);
    }

    /**
     * Hỗ trợ điều khiển file thông qua FTP account -> do không phải host nào cũng có thể điều khiển file bằng php thuần
     */
    protected function MY_unlink($f)
    {
        if (@!unlink($f)) {
            $file_model = new \App\Models\File();
            return $file_model->FTP_unlink($f);
        }
        return true;
    }

    protected function MY_copy($from, $to, $file_permission = DEFAULT_FILE_PERMISSION)
    {
        //echo $from . '<br>' . PHP_EOL;
        //echo $to . '<br>' . PHP_EOL;
        if (@!copy($from, $to)) {
            $file_model = new \App\Models\File();
            return $file_model->FTP_copy($from, $to);
        }
        if ($file_permission > 0) {
            chmod($to, $file_permission);
        }

        //
        return true;
    }

    protected function MY_rename($from, $to)
    {
        //echo $from . '<br>' . PHP_EOL;
        //echo $to . '<br>' . PHP_EOL;
        if (@!rename($from, $to)) {
            //die( __CLASS__ . ':' . __LINE__ );
            $file_model = new \App\Models\File();
            return $file_model->FTP_rename($from, $to);
        }

        //
        return true;
    }

    // lấy nội dung file mẫu trong /app/Helpers và gắn tham số cho nó để tạo file tương ứng
    protected function helpersTmpFile($fname, $data = [])
    {
        return HtmlTemplate::html($fname . '.txt', $data);
    }

    protected function MY_redirect($to, $status = 200)
    {
        // reset lại view -> tránh in ra phần html nếu lỡ nạp
        ob_end_clean();
        $this->teamplate = [];

        //
        if ($status == 200) {
            die(header('Location: ' . $to));
        }
        http_response_code($status);
        // $pcol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
        // if ($status == 404) {
        //     header($pcol . ' 404 Not Found');
        // } else {
        //     header($pcol . ' ' . $status . ' Not Found');
        // }
        die(header('Location: ' . $to, true, $status));
    }

    protected function result_json_type($arr, $headers = [], $too_headers = [])
    {
        $this->teamplate = [];

        // from function -> dùng để code dễ biết đoạn này gọi từ function nào tới
        // if (!isset($arr['f'])) {
        //     $arr['f'] = debug_backtrace()[1]['function'];
        // }

        //
        return $this->base_model->result_json_type($arr, $headers, $too_headers);
    }

    /**
     * đồng bộ dữ liệu login của thành viên về 1 định dạng chung
     **/
    protected function sync_login_data($result)
    {
        return $this->user_model->sync_login_data($result);
    }

    // kiểm tra segment tại vị trí thứ 1
    protected function prefixLang()
    {
        // lấy segment dựa theo _SERVER REQUEST_URI
        $seg = explode('/', ltrim($_SERVER['REQUEST_URI'], '/'))[0];

        // kiểm tra segment vị trí thứ 1
        // $seg = $this->request->uri->getSegment(1);

        // 
        // print_r($seg);
        // die(__CLASS__ . ':' . __LINE__);


        // 
        $arr_lang_support = LanguageCost::typeList();

        //print_r($arr_lang_support);
        if (
            // nếu nó trùng với ngôn ngữ được hỗ trợ
            isset($arr_lang_support[$seg]) &&
            // nếu khác với ngôn ngữ chính
            $seg != LanguageCost::default_lang() &&
            // và không phải là ngôn ngữ đang hiển thị
            //$seg != $this->lang_key
            $seg != LanguageCost::lang_key()
        ) {
            // gán ngôn ngữ theo segment
            $this->lang_key = $seg;
            //die(__CLASS__ . ':' . __LINE__);
            //echo $this->lang_key . '<br>' . PHP_EOL;

            // thay đổi tham số tạm thời cho lang hiện tại -> để các query lấy đúng bản ghi theo segment đang xem
            LanguageCost::segLang($seg);
            // lưu lang mới vào cookie -> việc lưu kiểu này sẽ ảnh hưởng tới việc khi quay lại ngôn ngữ chính -> không có sub-dir là rụng
            //LanguageCost::saveLang($seg);
        } else {
            // gán ngôn ngữ theo cookies
            $this->lang_key = LanguageCost::lang_key();
        }

        // xác định ngôn ngữ hiện tại
        // echo $this->lang_key . '<br>' . PHP_EOL;
    }

    public function change_lang()
    {
        return LanguageCost::setLang();
    }

    // chức năng kiểm tra sumit form bằng google captcha
    protected function googleCaptachStore()
    {
        // với các trường hợp cần dùng đến captcha nhưng không có trong đầu vào
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            return true;
        }

        // nạp config cho phần đăng nhập
        $firebase_config = $this->option_model->obj_config(ConfigType::FIREBASE);

        // xem có secret không
        $secret = $firebase_config->g_recaptcha_secret_key;
        // không có -> không dùng recaptcha -> trả về true
        if (empty($secret)) {
            return null;
        }

        // nạp dữ liệu để kiểm tra
        $credential = array(
            'secret' => $secret,
            'response' => $this->request->getVar('ebe_grecaptcha_response')
        );
        if (empty($credential['response'])) {
            return 'ebe_grecaptcha_response EMPTY!';
        }

        // lấy dữ liệu từ google
        $verify = curl_init();
        curl_setopt($verify, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
        curl_setopt($verify, CURLOPT_POST, true);
        curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($credential));
        curl_setopt($verify, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($verify);

        // lấy kết quả trả về
        $status = json_decode($response, true);
        //print_r($status);

        // captcha chính xác
        if (isset($status['success']) && !empty($status['success'])) {
            //die('Form has been successfully submitted');
            // trả về true
            return true;
        }
        // còn lại sẽ trả về thông điệp báo lỗi
        else if (isset($status['error-codes']) && !empty($status['error-codes'])) {
            return __FUNCTION__ . ': ' . $status['error-codes'][0];
        }
        //die('Something goes to wrong');
        return __FUNCTION__ . ': ' . 'Something goes to wrong';
    }
}
