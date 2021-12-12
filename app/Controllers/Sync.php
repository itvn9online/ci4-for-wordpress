<?php
namespace App\ Controllers;

//
use CodeIgniter\ Controller;

// Libraries
//use App\ Libraries\ LanguageCost;
//use App\ Libraries\ PostType;

//
class Sync extends Controller {
    public function __construct() {
        $this->base_model = new\ App\ Models\ Base();
    }

    /*
     * daidq: tự động đồng bộ cấu trúc bảng
     * do cấu trúc bảng của wordpress thiếu 1 số tính năng so với bản CI này nên cần thêm cột để sử dụng
     */

    // tạo bảng để lưu session khi cần có thể sử dụng luôn
    private function tbl_sessions() {
        $sql = "CREATE TABLE IF NOT EXISTS `ci_sessions` (
            `id` varchar(128) NOT null,
            `ip_address` varchar(45) NOT null,
            `timestamp` timestamp DEFAULT CURRENT_TIMESTAMP NOT null,
            `data` blob NOT null,
            KEY `ci_sessions_timestamp` (`timestamp`)
        )";
        echo 'CREATE TABLE IF NOT EXISTS `ci_sessions` <br>' . "\n";

        //
        return $this->base_model->MY_query( $sql );
    }

    // tạo view cho term để select dữ liệu cho tiện
    private function view_terms() {
        // lấy các cột trong bảng term để so sánh cột nào chưa có
        $wp_terms = $this->base_model->default_data( 'wp_terms' );
        //print_r( $wp_terms );

        //
        $wp_term_taxonomy = $this->base_model->default_data( 'wp_term_taxonomy' );
        //print_r( $wp_term_taxonomy );

        //
        $arr_term_taxonomy = [];
        foreach ( $wp_term_taxonomy as $k => $v ) {
            //echo $k . '<br>' . "\n";
            if ( isset( $wp_terms[ $k ] ) ) {
                echo '- - - - - - unset wp_term_taxonomy.' . $k . '<br>' . "\n";
            } else {
                $arr_term_taxonomy[] = 't.' . $k;
            }
        }
        //print_r( $arr_term_taxonomy );

        //
        $sql = $this->base_model->select( 'wp_terms.*,' . implode( ',', $arr_term_taxonomy ), 'wp_terms', array(
            // các kiểu điều kiện where
        ), array(
            'join' => array(
                'wp_term_taxonomy t' => 'wp_terms.term_id = t.term_id'
            ),
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            'get_query' => 1,
            //'offset' => 2,
            //'limit' => 3
        ) );
        //echo $sql . '<br>' . "\n";

        //
        $sql = "CREATE OR REPLACE VIEW v_terms AS " . $sql;
        //echo $sql . '<br>' . "\n";
        echo 'CREATE OR REPLACE VIEW v_terms <br>' . "\n";

        //
        return $this->base_model->MY_query( $sql );
    }

    // tạo view cho post để select dữ liệu cho tiện
    private function view_posts() {
        // lấy các cột trong bảng term để so sánh cột nào chưa có
        $wp_posts = $this->base_model->default_data( 'wp_posts' );
        //print_r( $wp_posts );

        //
        $wp_term_taxonomy = $this->base_model->default_data( 'wp_term_taxonomy' );
        //print_r( $wp_term_taxonomy );

        //
        $arr_term_taxonomy = [];
        foreach ( $wp_term_taxonomy as $k => $v ) {
            //echo $k . '<br>' . "\n";
            if ( isset( $wp_posts[ $k ] ) ) {
                echo '- - - - - - unset wp_term_taxonomy.' . $k . '<br>' . "\n";
            } else {
                $arr_term_taxonomy[] = 't.' . $k;
            }
        }
        //print_r( $arr_term_taxonomy );

        //
        $wp_term_relationships = $this->base_model->default_data( 'wp_term_relationships' );
        //print_r( $wp_term_relationships );

        //
        $arr_term_relationships = [];
        foreach ( $wp_term_relationships as $k => $v ) {
            //echo $k . '<br>' . "\n";
            if ( isset( $wp_term_taxonomy[ $k ] ) ) {
                echo '- - - - - - unset wp_term_relationships.' . $k . '<br>' . "\n";
            } else {
                $arr_term_relationships[] = 'r.' . $k;
            }
        }
        //print_r( $arr_term_relationships );

        //
        $sql = $this->base_model->select( 'wp_posts.*,' . implode( ',', $arr_term_taxonomy ) . ',' . implode( ',', $arr_term_relationships ), 'wp_posts', array(
            // các kiểu điều kiện where
        ), array(
            'join' => array(
                'wp_term_relationships r' => 'r.object_id = wp_posts.ID',
                'wp_term_taxonomy t' => 'r.term_taxonomy_id = t.term_taxonomy_id',
            ),
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            'get_query' => 1,
            //'offset' => 2,
            //'limit' => 3
        ) );
        //echo $sql . '<br>' . "\n";

        //
        //return false;

        //
        $sql = "CREATE OR REPLACE VIEW v_posts AS " . $sql;
        //echo $sql . '<br>' . "\n";
        echo 'CREATE OR REPLACE VIEW v_posts <br>' . "\n";

        //
        return $this->base_model->MY_query( $sql );
    }

    private function auto_sync_table_column() {
        $this->tbl_sessions();
        $this->view_terms();
        $this->view_posts();

        // các cột khi gặp sẽ thêm cả chức năng add index
        $arr_index_cloumn = [
            'lang_key',
            'is_deleted',
            'lang_parent',
            'term_status',
        ];
        //die( __FILE__ . ':' . __LINE__ );

        // tự động fixed các cột của bảng nếu chưa có
        $arr_add_cloumn = [
            'wp_users' => [
                'ci_pass' => 'VARCHAR(255) NULL COMMENT \'Mật khẩu đăng nhập cho phiên bản CI-wordpress\'',
                'member_type' => 'VARCHAR(55) NOT NULL COMMENT \'Phân loại thành viên (role)\'',
                'is_deleted' => 'TINYINT(2) NOT NULL DEFAULT \'0\' COMMENT \'0 = hiển thị, 1 = xóa\'',
                'last_login' => 'DATETIME NOT NULL',
                'last_updated' => 'DATETIME NOT NULL',
            ],
            'wp_posts' => [
                'lang_key' => 'VARCHAR(10) NOT NULL DEFAULT \'vn\' COMMENT \'Phân loại ngôn ngữ theo key quốc gia\'',
                'lang_parent' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'Dùng để xác định với các bản ghi được nhân bản từ ngôn ngữ chính\'',
            ],
            'wp_terms' => [
                'lang_key' => 'VARCHAR(10) NOT NULL DEFAULT \'vn\' COMMENT \'Phân loại ngôn ngữ theo key quốc gia\'',
                'lang_parent' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'Dùng để xác định với các bản ghi được nhân bản từ ngôn ngữ chính\'',
                'last_updated' => 'DATETIME NOT NULL',
                'is_deleted' => 'TINYINT(2) NOT NULL DEFAULT \'0\' COMMENT \'0 = hiển thị, 1 = xóa\'',
                'term_order' => 'INT(10) NOT NULL DEFAULT \'0\' COMMENT \'Sắp xếp vị trí hiển thị, số càng to thì độ ưu tiên càng cao\'',
                'term_status' => 'TINYINT(2) NOT NULL DEFAULT \'0\' COMMENT \'Trạng thái hiển thị của 1 term. 0 = hiển thị, 1 = ẩn\'',
            ],
            'wp_options' => [
                'option_type' => 'VARCHAR(55) NULL DEFAULT NULL COMMENT \'Phân loại option dành cho nhiều việc khác nhau\'',
                'lang_key' => 'VARCHAR(10) NOT NULL DEFAULT \'vn\' COMMENT \'Phân loại ngôn ngữ theo key quốc gia\'',
                'lang_parent' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'Dùng để xác định với các bản ghi được nhân bản từ ngôn ngữ chính\'',
                'last_updated' => 'DATETIME NOT NULL',
                'is_deleted' => 'TINYINT(2) NOT NULL DEFAULT \'0\' COMMENT \'0 = hiển thị, 1 = xóa\'',
                'insert_time' => 'BIGINT(20) NULL DEFAULT NULL COMMENT \'Thời gian insert bản ghi, dùng để lọc các dữ liệu insert cùng thời điểm\'',
            ],
            'wp_comments' => [
                'lang_key' => 'VARCHAR(10) NOT NULL DEFAULT \'vn\' COMMENT \'Phân loại ngôn ngữ theo key quốc gia\'',
                'lang_parent' => 'BIGINT(20) NOT NULL DEFAULT \'0\' COMMENT \'Dùng để xác định với các bản ghi được nhân bản từ ngôn ngữ chính\'',
                'is_deleted' => 'TINYINT(2) NOT NULL DEFAULT \'0\' COMMENT \'0 = hiển thị, 1 = xóa\'',
            ],
        ];
        $arr_add_cloumn[ 'wp_options_deleted' ] = $arr_add_cloumn[ 'wp_options' ];

        foreach ( $arr_add_cloumn as $k => $v ) {
            $check_table_column = $this->base_model->default_data( $k );
            //print_r( $check_table_column );

            //
            $last_key = '';
            foreach ( $check_table_column as $last_k => $last_v ) {
                $last_key = $last_k;
            }
            //echo $last_key . '<br>' . "\n";
            foreach ( $v as $col => $alter ) {
                //echo $col . '<br>' . "\n";
                // nếu chưa có cột này -> thêm mới
                if ( !isset( $check_table_column[ $col ] ) ) {
                    $add_index = '';
                    if ( in_array( $col, $arr_index_cloumn ) ) {
                        $add_index = ", ADD INDEX (`$col`)";
                    }
                    $alter_query = "ALTER TABLE `$k` ADD `$col` $alter AFTER `$last_key`" . $add_index;
                    echo $alter_query . '<br>' . "\n";
                    if ( $this->base_model->MY_query( $alter_query ) ) {
                        echo $col . ' column in database has been sync! <br>' . "\n";
                    } else {
                        echo 'Query failed! Please re-check query <br>' . "\n";
                    }

                    //
                    $last_key = $col;
                }
            }
        }
    }

    /*
     * daidq: chức năng này sẽ giải nén các code trong thư mục vendor dể sử dụng nếu chưa có
     */
    private function action_vendor_sync( $dir ) {
        foreach ( glob( PUBLIC_HTML_PATH . $dir . '/*.zip' ) as $filename ) {
            //echo $filename . '<br>' . "\n";

            //
            $file = basename( $filename, '.zip' );
            $check_dir = PUBLIC_HTML_PATH . $dir . '/' . $file;
            //echo $check_dir . '<br>' . "\n";

            // nếu chưa có thư mục -> giải nén
            if ( !is_dir( $check_dir ) ) {
                $zip = new\ ZipArchive();
                if ( $zip->open( $filename ) === TRUE ) {
                    $zip->extractTo( PUBLIC_HTML_PATH . $dir . '/' );
                    $zip->close();

                    //
                    echo 'DONE! sync code ' . $file . ' <br>' . "\n";
                } else {
                    echo 'ERROR! sync code ' . $file . ' <br>' . "\n";
                }
            } else {
                echo $file . ' has been sync <br>' . "\n";
            }
        }
    }

    public function vendor_sync() {
        // đồng bộ database
        $this->auto_sync_table_column();

        // đồng bộ vendor CSS, JS -> đặt tên là thirdparty để tránh trùng lặp khi load file tĩnh ngoài frontend
        $this->action_vendor_sync( 'public/thirdparty' );
        // đồng bộ vendor php
        $this->action_vendor_sync( 'vendor' );
        // đồng bộ ThirdParty php (code php của bên thứ 3)
        $this->action_vendor_sync( 'app/ThirdParty' );
    }
}