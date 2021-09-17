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
	private function auto_sync_table_column() {
		// các cột khi gặp sẽ thêm cả chức năng add index
		$arr_index_cloumn = [
			'lang_key',
			'is_deleted',
			'lang_parent',
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

		// đồng bộ vendor CSS, JS -> đặt tên là outsource để tránh trùng lặp khi load file tĩnh ngoài frontend
		$this->action_vendor_sync( 'public/outsource' );
		// đồng bộ vendor php
		$this->action_vendor_sync( 'vendor' );
		// đồng bộ ThirdParty php (code php của bên thứ 3)
		$this->action_vendor_sync( 'app/ThirdParty' );
	}
}