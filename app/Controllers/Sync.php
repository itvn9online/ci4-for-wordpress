<?php
namespace App\Controllers;

// Libraries
use App\Libraries\DeletedStatus;
use App\Helpers\HtmlTemplate;

//
class Sync extends BaseController
{
    public function __construct()
    {
        $this->base_model = new \App\Models\Base();
        $this->term_model = new \App\Models\Term();

        //
        //$this->cache = \Config\ Services::cache();
    }

    public function vendor_sync($check_thirdparty_exist = true)
    {
        // đồng bộ database
        $this->auto_sync_table_column($check_thirdparty_exist);

        // đồng bộ vendor CSS, JS -> đặt tên là thirdparty để tránh trùng lặp khi load file tĩnh ngoài frontend
        $this->action_vendor_sync('public/thirdparty', $check_thirdparty_exist);
        // đồng bộ vendor php
        $this->action_vendor_sync('vendor', $check_thirdparty_exist);
        // đồng bộ ThirdParty php (code php của bên thứ 3)
        $this->action_vendor_sync('app/ThirdParty', $check_thirdparty_exist);

        //
        if (strpos($_SERVER['HTTP_HOST'], 'localhost') === false) {
            $f = APPPATH . 'sync.txt';
            if (file_exists($f)) {
                $this->MY_unlink($f);
            }
        }
    }

    /*
     * daidq: tự động đồng bộ cấu trúc bảng
     * do cấu trúc bảng của wordpress thiếu 1 số tính năng so với bản CI này nên cần thêm cột để sử dụng
     */

    // tạo bảng để lưu session khi cần có thể sử dụng luôn
    private function tbl_sessions($tbl = 'ci_sessions')
    {
        $table = WGR_TABLE_PREFIX . $tbl;

        // xem bảng này có chưa -> có rồi thì thôi
        if ($this->base_model->table_exists($table)) {
            echo 'TABLE exist ' . $table . '<br>' . "\n";
            return false;
        }

        //
        $sql = "CREATE TABLE IF NOT EXISTS `$table` (
            `id` varchar(128) NOT null,
            `ip_address` varchar(45) NOT null,
            `timestamp` timestamp DEFAULT CURRENT_TIMESTAMP NOT null,
            `data` blob NOT null,
            KEY `ci_sessions_timestamp` (`timestamp`)
        )";
        echo 'CREATE TABLE IF NOT EXISTS `' . $table . '` <br>' . "\n\n";

        //
        return $this->base_model->MY_query($sql);
    }

    // tạo view cho term để select dữ liệu cho tiện
    private function view_terms($has_table_change = false)
    {
        // nếu không xác định được sự thay đổi của bảng
        if ($has_table_change === false) {
            // kiểm tra xem có view này chưa
            if ($this->base_model->table_exists(WGR_TERM_VIEW)) {
                echo 'TABLE exist ' . WGR_TERM_VIEW . '<br>' . "\n";
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
            //echo $k . '<br>' . "\n";
            if (isset($tbl_terms[$k])) {
                echo '- - - - - - unset term_taxonomy.' . $k . '<br>' . "\n";
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
        echo $sql . '<br>' . "\n";
        echo 'CREATE OR REPLACE VIEW ' . WGR_TERM_VIEW . ' <br>' . "\n\n";

        //
        //die( __CLASS__ . ':' . __LINE__ );
        return $this->base_model->MY_query($sql);
    }

    // tạo view cho post để select dữ liệu cho tiện
    private function view_posts($has_table_change)
    {
        // nếu không xác định được sự thay đổi của bảng
        if ($has_table_change === false) {
            // kiểm tra xem có view này chưa
            if ($this->base_model->table_exists(WGR_POST_VIEW)) {
                echo 'TABLE exist ' . WGR_POST_VIEW . '<br>' . "\n";
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
            //echo $k . '<br>' . "\n";
            if (isset($tbl_posts[$k])) {
                echo '- - - - - - unset term_taxonomy.' . $k . '<br>' . "\n";
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
            //echo $k . '<br>' . "\n";
            if (isset($tbl_term_taxonomy[$k])) {
                echo '- - - - - - unset term_relationships.' . $k . '<br>' . "\n";
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
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                'get_query' => 1,
                //'offset' => 2,
                'limit' => -1
            )
        );
        //echo $sql . '<br>' . "\n";

        //
        //return false;

        //
        $sql = "CREATE OR REPLACE VIEW " . WGR_POST_VIEW . " AS " . $sql;
        echo $sql . '<br>' . "\n";
        echo 'CREATE OR REPLACE VIEW ' . WGR_POST_VIEW . ' <br>' . "\n\n";

        //
        return $this->base_model->MY_query($sql);
    }

    private function auto_sync_table_column($check_thirdparty_exist = true)
    {
        /*
         * db không cần update liên tục, nếu cần thì clear cache để tái sử dụng
         */
        $last_run = $this->base_model->scache(__FUNCTION__);
        if ($check_thirdparty_exist === true && $last_run !== NULL) {
            echo __FUNCTION__ . ' RUN ' . (time() - $last_run) . 's ago ---`/ CLEAR cache for continue... ' . __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
            return false;
        }

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
                'is_deleted' => 'TINYINT(2) NOT NULL DEFAULT \'0\' COMMENT \'0 = hiển thị, 1 = xóa\'',
                'parent_id' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'ID của tài khoản cha nếu có\'',
                'district_id' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'ID của quận huyện\'',
                'province_id' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'ID của tỉnh thành phố\'',
                'last_login' => 'DATETIME NOT NULL',
                'last_updated' => 'DATETIME NOT NULL',
                'user_birthday' => 'DATE NULL COMMENT \'Sinh nhật của thành viên\'',
                'user_phone' => 'VARCHAR(55) NULL COMMENT \'Điện thoại liên hệ\'',
                'avatar' => 'VARCHAR(255) NOT NULL DEFAULT \'\' COMMENT \'Ảnh đại diện\'',
                'user_fund' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'Số dư tài khoản\'',
            ],
            $prefix . 'posts' => [
                'post_shorttitle' => 'VARCHAR(255) NOT NULL DEFAULT \'\' COMMENT \'Tên rút gọn của post\'',
                'post_shortslug' => 'VARCHAR(255) NOT NULL DEFAULT \'\' COMMENT \'Slug rút gọn của post\'',
                'post_permalink' => 'VARCHAR(255) NOT NULL DEFAULT \'\' COMMENT \'Lưu permalink để cho nhẹ server\'',
                'lang_key' => 'VARCHAR(10) NOT NULL DEFAULT \'vn\' COMMENT \'Phân loại ngôn ngữ theo key quốc gia\'',
                'lang_parent' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'Dùng để xác định với các bản ghi được nhân bản từ ngôn ngữ chính\'',
                'post_viewed' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'Đếm số lượt xem bài viết\'',
                'child_count' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'Đếm tổng số bài viết con của bài này. Thường dùng cho web truyện, chap của truyện\'',
                'child_last_count' => 'BIGINT(20) NULL COMMENT \'Thời gian cập nhật child_count lần trước\'',
                'time_order' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'Sắp xếp độ ưu tiên của post dựa theo thời gian hiện tại\'',
                'post_meta_data' => 'LONGTEXT NULL COMMENT \'Lưu các post meta vào đây để đỡ phải query nhiều\'',
            ],
            $prefix . 'terms' => [
                'term_shortname' => 'VARCHAR(255) NOT NULL DEFAULT \'\' COMMENT \'Tên rút gọn của term\'',
                'term_shortslug' => 'VARCHAR(255) NOT NULL DEFAULT \'\' COMMENT \'Slug rút gọn của term\'',
                'term_permalink' => 'VARCHAR(255) NOT NULL DEFAULT \'\' COMMENT \'Lưu permalink để cho nhẹ server\'',
                'lang_key' => 'VARCHAR(10) NOT NULL DEFAULT \'vn\' COMMENT \'Phân loại ngôn ngữ theo key quốc gia\'',
                'lang_parent' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'Dùng để xác định với các bản ghi được nhân bản từ ngôn ngữ chính\'',
                'last_updated' => 'DATETIME NOT NULL',
                'is_deleted' => 'TINYINT(2) NOT NULL DEFAULT \'0\' COMMENT \'0 = hiển thị, 1 = xóa\'',
                'term_order' => 'INT(10) NOT NULL DEFAULT \'0\' COMMENT \'Sắp xếp vị trí hiển thị, số càng to thì độ ưu tiên càng cao\'',
                'term_status' => 'TINYINT(2) NOT NULL DEFAULT \'0\' COMMENT \'Trạng thái hiển thị của 1 term. 0 = hiển thị, 1 = ẩn\'',
                'term_viewed' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'Đếm số lượt xem danh mục\'',
                'term_meta_data' => 'LONGTEXT NULL COMMENT \'Lưu các post meta vào đây để đỡ phải query nhiều\'',
                'term_ids' => 'VARCHAR(255) NULL COMMENT \'Danh sách ID của các nhóm con, dùng để tạo query cho nhanh\'',
                'child_count' => 'BIGINT(20) NULL COMMENT \'Tính tổng số nhóm con để gọi lệnh lấy nhóm con nếu không NULL\'',
                'child_last_count' => 'BIGINT(20) NULL COMMENT \'Thời gian cập nhật child_count lần trước\'',
                'term_type' => 'VARCHAR(55) NULL COMMENT \'Dùng để phân loại term, tương tự category nhưng ít dùng hơn nhiều\'',
                'term_avatar' => 'VARCHAR(255) NOT NULL DEFAULT \'\' COMMENT \'Ảnh đại diện của term\'',
                'term_favicon' => 'VARCHAR(255) NOT NULL DEFAULT \'\' COMMENT \'Hình thu nhỏ của term\'',
            ],
            $prefix . 'term_taxonomy' => [
                // term level -> dùng để lọc các nhóm theo cấp độ cho nó nhanh -> ví dụ khi cần lấy tất cả các nhóm cấp 1, 2, 3
                'term_level' => 'TINYINT(2) NOT NULL DEFAULT \'0\' COMMENT \'Level của nhóm, tính theo cấp độ của nhóm cha +1\'',
            ],
            $prefix . 'term_relationships' => [
                'is_deleted' => 'TINYINT(2) NOT NULL DEFAULT \'0\' COMMENT \'0 = hiển thị, 1 = xóa. Trạng thái này được lấy định kỳ dựa theo trạng thái của post\'',
            ],
            $prefix . 'options' => [
                'option_type' => 'VARCHAR(55) NULL DEFAULT NULL COMMENT \'Phân loại option dành cho nhiều việc khác nhau\'',
                'lang_key' => 'VARCHAR(10) NOT NULL DEFAULT \'vn\' COMMENT \'Phân loại ngôn ngữ theo key quốc gia\'',
                'lang_parent' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'Dùng để xác định với các bản ghi được nhân bản từ ngôn ngữ chính\'',
                'last_updated' => 'DATETIME NOT NULL',
                'is_deleted' => 'TINYINT(2) NOT NULL DEFAULT \'0\' COMMENT \'0 = hiển thị, 1 = xóa\'',
                'insert_time' => 'BIGINT(20) NULL DEFAULT NULL COMMENT \'Thời gian insert bản ghi, dùng để lọc các dữ liệu insert cùng thời điểm\'',
            ],
            $prefix . 'comments' => [
                // thêm tiêu đề cho phần comment -> do bảng mặc định của wp comment không có cột này
                'comment_title' => 'VARCHAR(255) NOT NULL DEFAULT \'Thêm tiêu đề để tiện cho việc hiển thị\'',
                'comment_slug' => 'VARCHAR(255) NOT NULL DEFAULT \'Thêm phần slug để tiện cho quá trình tìm kiếm\'',
                'lang_key' => 'VARCHAR(10) NOT NULL DEFAULT \'vn\' COMMENT \'Phân loại ngôn ngữ theo key quốc gia\'',
                'lang_parent' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'Dùng để xác định với các bản ghi được nhân bản từ ngôn ngữ chính\'',
                'is_deleted' => 'TINYINT(2) NOT NULL DEFAULT \'0\' COMMENT \'0 = hiển thị, 1 = xóa\'',
                'time_order' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'Sắp xếp độ ưu tiên của post dựa theo thời gian hiện tại\'',
            ],
            $prefix . 'orders' => [
                'order_money' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'Giá trị của đơn hàng\'',
            ],
        ];

        /*
         * bảng dữ liệu riêng của từng theme
         */
        $private_theme_db = THEMEPATH . 'Database_Migrations.php';
        //echo $private_theme_db . '<br>' . "\n";
        if (file_exists($private_theme_db)) {
            include $private_theme_db;

            //
            //print_r( $arr_custom_alter_database );
            foreach ($arr_custom_alter_database as $k => $v) {
                //echo $k . '<br>' . "\n";
                //print_r( $v );
                if (!isset($arr_add_cloumn[$k])) {
                    $arr_add_cloumn[$k] = [];
                }

                //
                foreach ($v as $k2 => $v2) {
                    //echo $k2 . '<br>' . "\n";
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
            //echo $last_key . '<br>' . "\n";
            foreach ($v as $col => $alter) {
                //echo $col . '<br>' . "\n";
                // nếu chưa có cột này -> thêm mới
                if (!isset($check_table_column[$col])) {
                    $add_index = '';
                    if (in_array($col, $arr_index_cloumn)) {
                        $add_index = ", ADD INDEX (`$col`)";
                    }
                    $alter_query = "ALTER TABLE `$k` ADD `$col` $alter AFTER `$last_key`" . $add_index;
                    echo $alter_query . '<br>' . "\n";
                    //continue;
                    //die( __CLASS__ . ':' . __LINE__ );
                    if ($this->base_model->MY_query($alter_query)) {
                        echo $col . ' column in database has been sync! <br>' . "\n";

                        //
                        $has_table_change = true;
                    } else {
                        echo 'Query failed! Please re-check query <br>' . "\n";
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
        echo $alter_query . '<br>' . "\n";
        if ( $this->base_model->MY_query( $alter_query ) ) {
        echo $prefix . 'posts - post_title column in database has been sync! <br>' . "\n";
        } else {
        echo 'Query failed! Please re-check query <br>' . "\n";
        }
        */

        //
        $this->tbl_sessions();
        // kiểm tra và tạo view nếu bảng có sự thay đổi
        $this->view_terms($has_table_change);
        $this->view_posts($has_table_change);
        // cập nhật lại tổng số nhóm con cho phân term
        /*
        $last_run = $this->term_model->sync_term_child_count();
        if ($last_run !== true) {
        echo 'sync term child count RUN ' . (time() - $last_run) . 's ago ---`/ CLEAR cache for continue... ' . __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
        }
        */

        //
        //die( __CLASS__ . ':' . __LINE__ );
        $this->base_model->scache(__FUNCTION__, time(), MEDIUM_CACHE_TIMEOUT);
    }

    /*
     * unzip file
     */
    protected function MY_unzip($file, $dir)
    {
        echo $file . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
        echo $dir . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . "\n";

        //
        $zip = new \ZipArchive();
        if ($zip->open($file) === TRUE) {
            $zip->extractTo(rtrim($dir, '/'));
            $zip->close();
            return TRUE;
        }
        return false;
    }

    /*
     * daidq: chức năng này sẽ giải nén các code trong thư mục vendor dể sử dụng nếu chưa có
     */
    private function action_vendor_sync($dir, $check_thirdparty_exist = true)
    {
        $upload_via_ftp = false;
        if (@!file_put_contents(PUBLIC_HTML_PATH . 'test_permission.txt', time())) {
            $upload_via_ftp = true;
        } else {
            unlink(PUBLIC_HTML_PATH . 'test_permission.txt');
        }
        //var_dump( $upload_via_ftp );
        //die( __CLASS__ . ':' . __LINE__ );
        //return false;

        //
        $dir = rtrim($dir, '/');
        // nếu phải xử lý file thông qua ftp
        if ($upload_via_ftp === true) {
            //echo PUBLIC_HTML_PATH . $dir . '<br>' . "\n";
            //die( __CLASS__ . ':' . __LINE__ );

            // chuyển thư mục về 777 để có thể unzip
            $file_model = new \App\Models\File();
            $file_model->FTP_chmod(PUBLIC_HTML_PATH . $dir, 0777);
        }
        //die( __CLASS__ . ':' . __LINE__ );

        //
        foreach (glob(PUBLIC_HTML_PATH . $dir . '/*.zip') as $filename) {
            //echo $filename . '<br>' . "\n";
            //continue;

            //
            $file = basename($filename, '.zip');
            $check_dir = PUBLIC_HTML_PATH . $dir . '/' . $file;
            //echo $check_dir . '<br>' . "\n";

            // nếu chưa có thư mục -> giải nén
            if ($check_thirdparty_exist === false || !is_dir($check_dir)) {
                if ($this->MY_unzip($filename, PUBLIC_HTML_PATH . $dir) === TRUE) {
                    echo 'DONE! sync code ' . $file . ' <br>' . "\n";
                } else {
                    echo 'ERROR! sync code ' . $file . ' <br>' . "\n";
                }
            } else {
                echo $file . ' has been sync <br>' . "\n";
            }
        }
    }

    // tự set session, do session của ci4 nó đứt liên tục
    protected function MY_session($key, $value = NULL)
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
        $str = str_replace('/public/upload/', '/upload/', $str);
        $str = str_replace(base_url() . '/', '', $str);

        //
        return $str;
    }

    /*
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

    /*
     * trả về URL của controller theo định dạng của namespace
     * đầu vào là __CLASS__
     * đầu ra sẽ cắt bỏ phần namespace ở đầu, giữ lại phần controller sau -> REUQEST URL
     */
    protected function base_class_url($str)
    {
        // lấy thư mục chứa file hiện tại
        //echo __DIR__ . '<br>' . "\n";
        $current_dir = basename(__DIR__);
        //echo $current_dir . '<br>' . "\n";

        //
        //echo $str . '<br>' . "\n";
        $str = str_replace('\\', '/', $str);
        //echo $str . '<br>' . "\n";

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

    /*
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
        //echo $from . '<br>' . "\n";
        //echo $to . '<br>' . "\n";
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
        //echo $from . '<br>' . "\n";
        //echo $to . '<br>' . "\n";
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
        if ($status == 200) {
            die(header('Location: ' . $to));
        }
        $pcol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
        if ($status == 404) {
            header($pcol . ' 404 Not Found');
        } else {
            header($pcol . ' ' . $status . ' Not Found');
        }
        die(header('Location: ' . $to, TRUE, $status));
    }
}