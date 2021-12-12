<?php

/*
 | --------------------------------------------------------------------
 | App Namespace
 | --------------------------------------------------------------------
 |
 | This defines the default Namespace that is used throughout
 | CodeIgniter to refer to the Application directory. Change
 | this constant to change the namespace that all application
 | classes should use.
 |
 | NOTE: changing this will require manually modifying the
 | existing namespaces of App\* namespaced-classes.
 */
defined( 'APP_NAMESPACE' ) || define( 'APP_NAMESPACE', 'App' );

/*
 | --------------------------------------------------------------------------
 | Composer Path
 | --------------------------------------------------------------------------
 |
 | The path that Composer's autoload file is expected to live. By default,
 | the vendor folder is in the Root directory, but you can customize that here.
 */
defined( 'COMPOSER_PATH' ) || define( 'COMPOSER_PATH', ROOTPATH . 'vendor/autoload.php' );

/*
 |--------------------------------------------------------------------------
 | Timing Constants
 |--------------------------------------------------------------------------
 |
 | Provide simple ways to work with the myriad of PHP functions that
 | require information to be in seconds.
 */
defined( 'SECOND' ) || define( 'SECOND', 1 );
defined( 'MINUTE' ) || define( 'MINUTE', 60 );
defined( 'HOUR' ) || define( 'HOUR', 3600 );
defined( 'DAY' ) || define( 'DAY', 86400 );
defined( 'WEEK' ) || define( 'WEEK', 604800 );
defined( 'MONTH' ) || define( 'MONTH', 2592000 );
defined( 'YEAR' ) || define( 'YEAR', 31536000 );
defined( 'DECADE' ) || define( 'DECADE', 315360000 );

/*
 | --------------------------------------------------------------------------
 | Exit Status Codes
 | --------------------------------------------------------------------------
 |
 | Used to indicate the conditions under which the script is exit()ing.
 | While there is no universal standard for error codes, there are some
 | broad conventions.  Three such conventions are mentioned below, for
 | those who wish to make use of them.  The CodeIgniter defaults were
 | chosen for the least overlap with these conventions, while still
 | leaving room for others to be defined in future versions and user
 | applications.
 |
 | The three main conventions used for determining exit status codes
 | are as follows:
 |
 |    Standard C/C++ Library (stdlibc):
 |       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
 |       (This link also contains other GNU-specific conventions)
 |    BSD sysexits.h:
 |       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
 |    Bash scripting:
 |       http://tldp.org/LDP/abs/html/exitcodes.html
 |
 */
defined( 'EXIT_SUCCESS' ) || define( 'EXIT_SUCCESS', 0 ); // no errors
defined( 'EXIT_ERROR' ) || define( 'EXIT_ERROR', 1 ); // generic error
defined( 'EXIT_CONFIG' ) || define( 'EXIT_CONFIG', 3 ); // configuration error
defined( 'EXIT_UNKNOWN_FILE' ) || define( 'EXIT_UNKNOWN_FILE', 4 ); // file not found
defined( 'EXIT_UNKNOWN_CLASS' ) || define( 'EXIT_UNKNOWN_CLASS', 5 ); // unknown class
defined( 'EXIT_UNKNOWN_METHOD' ) || define( 'EXIT_UNKNOWN_METHOD', 6 ); // unknown class member
defined( 'EXIT_USER_INPUT' ) || define( 'EXIT_USER_INPUT', 7 ); // invalid user input
defined( 'EXIT_DATABASE' ) || define( 'EXIT_DATABASE', 8 ); // database error
defined( 'EXIT__AUTO_MIN' ) || define( 'EXIT__AUTO_MIN', 9 ); // lowest automatically-assigned error code
defined( 'EXIT__AUTO_MAX' ) || define( 'EXIT__AUTO_MAX', 125 ); // highest automatically-assigned error code


/////////////////////////////////////////////////// DAIDQ CONFIG ////////////////////////////////////////////////////////
/*
 * daidq: 2021-09-14
 * Phần cấu hình thiết lập động để có thể tái sử dụng code cho nhiều website khác nhau mà không cần code lại nhiều
 * Các controller, model... cố gắng viết theo quy tắc exten để có thể tái sử dụng
 */

// website của nhà phát triển
define( 'PARTNER_WEBSITE', 'https://echbay.com/' );

define( 'EBE_DATE_FORMAT', 'Y-m-d' );
define( 'EBE_DATETIME_FORMAT', 'Y-m-d H:i:s' );

/*
 * tạo đường dẫn admin tránh đường dẫn mặc định. Ví dụ : admin -> nhằm tăng cường bảo mật cho website
 */
define( 'CUSTOM_ADMIN_URI', 'ci4-wp-admin' );

/*
 * URL động cho website để có thể chạy trên nhiều tên miền khác nhau mà không cần config lại
 */
$web_protocol = 'http';
if ( $_SERVER[ 'SERVER_PORT' ] == 443 ||
    ( isset( $_SERVER[ 'HTTPS' ] ) && $_SERVER[ 'HTTPS' ] == 'on' ) ||
    ( isset( $_SERVER[ 'HTTP_X_FORWARDED_PROTO' ] ) && $_SERVER[ 'HTTP_X_FORWARDED_PROTO' ] == 'https' ) ) {
    $web_protocol = 'https';
}
//die( $web_protocol );
define( 'DYNAMIC_BASE_URL', $web_protocol . '://' . $_SERVER[ 'HTTP_HOST' ] . '/' );

/*
 * Các tham số khác, rất ít khi thay đổi
 */
define( 'PUBLIC_HTML_PATH', ROOTPATH );
//echo PUBLIC_HTML_PATH . '<br>' . "\n";
define( 'PUBLIC_PUBLIC_PATH', PUBLIC_HTML_PATH . 'public/' );
//die( PUBLIC_PUBLIC_PATH );


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
foreach ( glob( PUBLIC_PUBLIC_PATH . 'themes/*.theme' ) as $filename ) {
    $filename = basename( $filename, '.theme' );
    //echo $filename . '<br>' . "\n";
    if ( is_dir( PUBLIC_PUBLIC_PATH . 'themes/' . $filename ) ) {
        define( 'THEMENAME', $filename );
        break;
    }
}

// nếu không có file active theme tự động -> gán mặc định theme echbayfour
if ( !defined( 'THEMENAME' ) ) {
    define( 'THEMENAME', 'echbayfour' );
}
//echo THEMENAME . '<br>' . "\n";


//
define( 'THEMEPATH', PUBLIC_PUBLIC_PATH . 'themes/' . THEMENAME . '/' );
//die( THEMEPATH );

//
if ( file_exists( THEMEPATH . 'functions.php' ) ) {
    include THEMEPATH . 'functions.php';
}


/*
 * Tài khoản FTP -> dùng để điều khiển file trong trường hợp bị lỗi permission
 */
//define( 'FTP_HOST', $_SERVER['SERVER_ADDR'] );
//define( 'FTP_USER', '' );
//define( 'FTP_PASS', '' );