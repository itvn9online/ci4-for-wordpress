<?php

/////////////////////////////////////////////////// DAIDQ CONFIG ////////////////////////////////////////////////////////
/*
 * daidq: 2021-09-14
 * Phần cấu hình thiết lập động để có thể tái sử dụng code cho nhiều website khác nhau mà không cần code lại nhiều
 * Các controller, model... cố gắng viết theo quy tắc exten để có thể tái sử dụng
 */

defined( 'EBE_DATE_FORMAT' ) || define( 'EBE_DATE_FORMAT', 'Y-m-d' );
defined( 'EBE_DATETIME_FORMAT' ) || define( 'EBE_DATETIME_FORMAT', 'Y-m-d H:i:s' );

/*
 * tạo đường dẫn admin tránh đường dẫn mặc định. Ví dụ : admin -> nhằm tăng cường bảo mật cho website
 */
defined( 'CUSTOM_ADMIN_URI' ) || define( 'CUSTOM_ADMIN_URI', 'wgr-wp-admin' );

/*
 * Các tham số khác, rất ít khi thay đổi
 */
define( 'PUBLIC_HTML_PATH', ROOTPATH );
//echo PUBLIC_HTML_PATH . '<br>' . "\n";
define( 'PUBLIC_PUBLIC_PATH', PUBLIC_HTML_PATH . 'public/' );
//die( PUBLIC_PUBLIC_PATH );


/*
 * lưu giá trị của config vào biến này, nếu hàm sau có gọi lại thì tái sử dụng luôn
 */
$this_cache_config = NULL;
$this_cache_lang = NULL;


/*
 * Danh sách các custom taxonomy mà người dùng đăng ký sẽ được khai báo thêm ở đây
 */
$arr_custom_taxonomy = [];

function register_taxonomy( $name, $ops = [] ) {
    global $arr_custom_taxonomy;

    $arr_custom_taxonomy[ $name ] = $ops;
}


/*
 * Danh sách các custom post type mà người dùng đăng ký sẽ được khai báo thêm ở đây
 */
$arr_custom_post_type = [];

function register_post_type( $name, $ops = [] ) {
    global $arr_custom_post_type;

    $arr_custom_post_type[ $name ] = $ops;
}


/*
 * Thư mục chứa theme hiển thị cho website (tùy theo yêu cầu của khách hàng mà thiết lập giao diện khác nhau)
 */
// xác định theme tự động
foreach ( glob( PUBLIC_PUBLIC_PATH . 'themes/*.actived-theme' ) as $filename ) {
    $filename = basename( $filename, '.actived-theme' );
    //echo $filename . '<br>' . "\n";
    if ( is_dir( PUBLIC_PUBLIC_PATH . 'themes/' . $filename ) ) {
        define( 'THEMENAME', $filename );
        break;
    }
}

// nếu không có file active theme tự động -> gán mặc định theme echbayfour
defined( 'THEMENAME' ) || define( 'THEMENAME', 'echbayfour' );
//echo THEMENAME . '<br>' . "\n";


//
define( 'THEMEPATH', PUBLIC_PUBLIC_PATH . 'themes/' . THEMENAME . '/' );
//die( THEMEPATH );

/*
 * nạp file function của từng theme
 */
if ( file_exists( THEMEPATH . 'functions.php' ) ) {
    include THEMEPATH . 'functions.php';
}

// website của nhà phát triển
defined( 'PARTNER_WEBSITE' ) || define( 'PARTNER_WEBSITE', 'https://echbay.com/' );
defined( 'PARTNER_BRAND_NAME' ) || define( 'PARTNER_BRAND_NAME', 'EchBay.com' );

/*
 * URL động cho website để có thể chạy trên nhiều tên miền khác nhau mà không cần config lại
 */
// tinh chỉnh protocol theo ý thích -> mặc định là https
defined( 'BASE_PROTOCOL' ) || define( 'BASE_PROTOCOL', 'https' );
// -> url động cho website
define( 'DYNAMIC_BASE_URL', BASE_PROTOCOL . '://' . $_SERVER[ 'HTTP_HOST' ] . '/' );
//die( DYNAMIC_BASE_URL );

// khi cần chuyển các file tĩnh sang url khác để giảm tải cho server chính thì dùng chức năng này
defined( 'CDN_BASE_URL' ) || define( 'CDN_BASE_URL', '' );

// kiểu sử dụng cache
defined( 'MY_CACHE_HANDLER' ) || define( 'MY_CACHE_HANDLER', 'file' );

// chuỗi sẽ thêm vào khi sử dụng hàm mdnam -> md5
defined( 'CUSTOM_MD5_HASH_CODE' ) || define( 'CUSTOM_MD5_HASH_CODE', $_SERVER[ 'HTTP_HOST' ] );

// permission mặc định khi up file, tạo thư mục
defined( 'DEFAULT_FILE_PERMISSION' ) || define( 'DEFAULT_FILE_PERMISSION', 0777 );
defined( 'DEFAULT_DIR_PERMISSION' ) || define( 'DEFAULT_DIR_PERMISSION', 0777 );

// thư mục lưu cache theo từng tên miền -> code thêm cho các web sử dụng domain pointer
define( 'WRITE_CACHE_PATH', WRITEPATH . 'cache/' . explode( ':', $_SERVER[ 'HTTP_HOST' ] )[ 0 ] . '/' );
//die( WRITE_CACHE_PATH );
if ( !is_dir( WRITE_CACHE_PATH ) ) {
    mkdir( WRITE_CACHE_PATH, DEFAULT_DIR_PERMISSION )or die( 'ERROR create cache dir' );
    chmod( WRITE_CACHE_PATH, DEFAULT_DIR_PERMISSION );
}

// thời gian cache mặc định
defined( 'MINI_CACHE_TIMEOUT' ) || define( 'MINI_CACHE_TIMEOUT', 300 );
defined( 'MEDIUM_CACHE_TIMEOUT' ) || define( 'MEDIUM_CACHE_TIMEOUT', HOUR );
defined( 'BIG_CACHE_TIMEOUT' ) || define( 'BIG_CACHE_TIMEOUT', 21600 ); // 6 giờ

// cho phép sử dụng lệnh DELETE trong mysql
defined( 'ALLOW_USING_MYSQL_DELETE' ) || define( 'ALLOW_USING_MYSQL_DELETE', false );

/*
 * Tiền tố cho danh mục sản phẩm
 */
defined( 'WGR_CATEGORY_PREFIX' ) || define( 'WGR_CATEGORY_PREFIX', 'category' );
if ( WGR_CATEGORY_PREFIX != '' ) {
    define( 'CATEGORY_BASE_URL', WGR_CATEGORY_PREFIX . '/' );
} else {
    define( 'CATEGORY_BASE_URL', '' );
}

/*
 * Tiền tố cho trang tĩnh
 */
defined( 'WGR_PAGES_PREFIX' ) || define( 'WGR_PAGES_PREFIX', 'pages' );
if ( WGR_PAGES_PREFIX != '' ) {
    define( 'PAGE_BASE_URL', WGR_PAGES_PREFIX . '/' );
} else {
    define( 'PAGE_BASE_URL', '' );
}

/**
 * Tiền tố cho bảng database.
 * Chỉ sử dụng số, ký tự và dấu gạch dưới!
 */
defined( 'WGR_TABLE_PREFIX' ) || define( 'WGR_TABLE_PREFIX', 'wp_' );

define( 'WGR_TERM_VIEW', WGR_TABLE_PREFIX . 'zzz_v_terms' );
define( 'WGR_POST_VIEW', WGR_TABLE_PREFIX . 'zzz_v_posts' );