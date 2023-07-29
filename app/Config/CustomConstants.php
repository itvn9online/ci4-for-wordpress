<?php
/*
 * Constants mặc định của code gốc, file này mà xóa đi thì chỉ có ăn cám -> code này hết sử dụng luôn
 */

/////////////////////////////////////////////////// DAIDQ CONFIG ////////////////////////////////////////////////////////
/*
 * daidq: 2021-09-14
 * Phần cấu hình thiết lập động để có thể tái sử dụng code cho nhiều website khác nhau mà không cần code lại nhiều
 * Các controller, model... cố gắng viết theo quy tắc exten để có thể tái sử dụng
 */

/*
 * Các tham số khác, rất ít khi thay đổi
 */
define('PUBLIC_HTML_PATH', ROOTPATH);
//echo PUBLIC_HTML_PATH . '<br>' . PHP_EOL;
define('PUBLIC_PUBLIC_PATH', PUBLIC_HTML_PATH . 'public/');
//die( PUBLIC_PUBLIC_PATH );

// view mặc định của framework
define('VIEWS_PATH', APPPATH . 'Views/');
// view riêng của từng theme nếu có thì view này sẽ được ưu tiên sử dụng
define('VIEWS_CUSTOM_PATH', ROOTPATH . 'custom/Views/');
// views của admin
define('ADMIN_ROOT_VIEWS', VIEWS_PATH . 'admin/');
define('ADMIN_CUSTOM_VIEWS', VIEWS_CUSTOM_PATH . 'admin/');
//define('ADMIN_DEFAULT_VIEWS', VIEWS_PATH . 'admin/default/');
//die( VIEWS_CUSTOM_PATH );


/*
 * lưu giá trị của config vào biến này, nếu hàm sau có gọi lại thì tái sử dụng luôn
 */
$this_cache_config = NULL;
$this_cache_lang = NULL;
$this_cache_num = NULL;
$this_cache_checkbox = NULL;


/*
 * Danh sách các custom taxonomy mà người dùng đăng ký sẽ được khai báo thêm ở đây
 */
$arr_custom_taxonomy = [];

function register_taxonomy($name, $ops = [])
{
    global $arr_custom_taxonomy;

    $arr_custom_taxonomy[$name] = $ops;
}


/*
 * Danh sách các custom post type mà người dùng đăng ký sẽ được khai báo thêm ở đây
 */
$arr_custom_post_type = [];

function register_post_type($name, $ops = [])
{
    global $arr_custom_post_type;

    $arr_custom_post_type[$name] = $ops;
}


/*
 * Thư mục chứa theme hiển thị cho website (tùy theo yêu cầu của khách hàng mà thiết lập giao diện khác nhau)
 */
// xác định theme tự động
foreach (glob(PUBLIC_PUBLIC_PATH . 'themes/*.actived-theme') as $filename) {
    $filename = basename($filename, '.actived-theme');
    //echo $filename . '<br>' . PHP_EOL;
    if (is_dir(PUBLIC_PUBLIC_PATH . 'themes/' . $filename)) {
        define('THEMENAME', $filename);
        break;
    }
}

// nếu không có file active theme tự động -> gán mặc định theme echbayfour
defined('THEMENAME') || define('THEMENAME', 'echbayfour');
//echo THEMENAME . '<br>' . PHP_EOL;


//
define('THEMEPATH', PUBLIC_PUBLIC_PATH . 'themes/' . THEMENAME . '/');
//die( THEMEPATH );


####################################################################
// file Constants động
define('DYNAMIC_CONSTANTS_PATH', APPPATH . 'Config/DynamicConstants.php');

/*
* Nạp Constants của từng web -> code động -> độ ưu tiên cao nhất
*/
if (file_exists(DYNAMIC_CONSTANTS_PATH)) {
    include DYNAMIC_CONSTANTS_PATH;
}

/*
 * nạp file function của từng theme -> code cứng -> sẽ bị phủ định bởi code động
 * -> trong functions phải khai báo theo mẫu: defined() || define()
 */
if (file_exists(THEMEPATH . 'functions.php')) {
    include THEMEPATH . 'functions.php';
}
####################################################################


// ngôn ngữ của website -> mặc định chỉ chạy 1 ngôn ngữ, nếu muốn chạy nhiều ngôn ngữ thì tăng số mảng lên
defined('SITE_LANGUAGE_SUPPORT') || define(
    'SITE_LANGUAGE_SUPPORT',
    [
        [
            'value' => 'vn',
            'text' => 'Tiếng Việt',
            'css_class' => 'text-muted'
        ]
    ]
);
// ngôn ngữ mặc định -> đặt là mảng số 0
defined('SITE_LANGUAGE_DEFAULT') || define('SITE_LANGUAGE_DEFAULT', SITE_LANGUAGE_SUPPORT[0]['value']);
// kiểu hiển thị đa ngôn ngữ (true:có hỗ trợ|false: không hỗ trợ), nếu là sub-folder thì sẽ hỗ trợ prefix cho routes, url cũng sẽ thêm prefix vào trước (nếu tắt đi thì kiểu đa ngôn ngữ sẽ là sub-domain)
defined('SITE_LANGUAGE_SUB_FOLDER') || define('SITE_LANGUAGE_SUB_FOLDER', true);

// khi cần thay label cho trang /admin/translates để dễ hiểu hơn thì thêm các thông số vào đây
defined('TRANS_TRANS_LABEL') || define(
    'TRANS_TRANS_LABEL',
    [
        'custom_text0' => 'Bản dịch số 0',
        //'custom_textarea0' => 'Bản dịch số 0',
    ]
);

// khi cần thay label cho trang /admin/nummons để dễ hiểu hơn thì thêm các thông số vào đây
defined('TRANS_NUMS_LABEL') || define(
    'TRANS_NUMS_LABEL',
    [
        'custom_num_mon0' => 'Tùy chỉnh số 0',
    ]
);

// Số lượng bản ghi dạng số nguyên -> website nào cần dùng nhiều tăng số lượng trong file functions lên
defined('NUMBER_CHECKBOXS_INPUT') || define('NUMBER_CHECKBOXS_INPUT', 3);
// khi cần thay label cho trang /admin/checkboxs để dễ hiểu hơn thì thêm các thông số vào đây
defined('TRANS_CHECKBOXS_LABEL') || define(
    'TRANS_CHECKBOXS_LABEL',
    [
        'custom_checkbox0' => 'Checkbox số 0',
    ]
);


//
defined('EBE_DATE_FORMAT') || define('EBE_DATE_FORMAT', 'Y-m-d');
defined('EBE_DATETIME_FORMAT') || define('EBE_DATETIME_FORMAT', 'Y-m-d H:i:s');

/*
 * tạo đường dẫn admin tránh đường dẫn mặc định. Ví dụ : admin -> nhằm tăng cường bảo mật cho website
 */
defined('CUSTOM_ADMIN_URI') || define('CUSTOM_ADMIN_URI', 'wgr-wp-admin');

// website của nhà phát triển
defined('PARTNER_WEBSITE') || define('PARTNER_WEBSITE', 'https://echbay.com/');
defined('PARTNER_BRAND_NAME') || define('PARTNER_BRAND_NAME', 'EchBay.com');
//
defined('PARTNER2_WEBSITE') || define('PARTNER2_WEBSITE', 'https://webgiare.org/');
defined('PARTNER2_BRAND_NAME') || define('PARTNER2_BRAND_NAME', 'WebGiaRe.org');

// kiểu kết nối dữ liệu
defined('MY_DB_DRIVER') || define('MY_DB_DRIVER', 'MySQLi');

/*
 * URL động cho website để có thể chạy trên nhiều tên miền khác nhau mà không cần config lại
 */
// tinh chỉnh protocol theo ý thích -> mặc định là https
defined('BASE_PROTOCOL') || define('BASE_PROTOCOL', 'https');
// -> url động cho website
define('DYNAMIC_BASE_URL', BASE_PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . '/');
//die( DYNAMIC_BASE_URL );

// khi cần chuyển các file tĩnh sang url khác để giảm tải cho server chính thì dùng chức năng này
defined('CDN_BASE_URL') || define('CDN_BASE_URL', '');

// permission mặc định khi up file, tạo thư mục
defined('DEFAULT_FILE_PERMISSION') || define('DEFAULT_FILE_PERMISSION', 0777);
defined('DEFAULT_DIR_PERMISSION') || define('DEFAULT_DIR_PERMISSION', 0777);

// kiểu sử dụng cache -> mặc định là file
defined('MY_CACHE_HANDLER') || define('MY_CACHE_HANDLER', 'file');

// đồng bộ http host về 1 chuỗi chung
define('HTTP_SYNC_HOST', str_replace('www.', '', str_replace('.', '', explode(':', $_SERVER['HTTP_HOST'])[0])));

// chuỗi sẽ thêm vào khi sử dụng hàm mdnam -> md5
defined('CUSTOM_MD5_HASH_CODE') || define('CUSTOM_MD5_HASH_CODE', HTTP_SYNC_HOST);

// với cache file -> thư mục lưu cache theo từng tên miền -> code thêm cho các web sử dụng domain pointer
if (MY_CACHE_HANDLER == 'file') {
    define('WRITE_CACHE_PATH', WRITEPATH . 'cache/' . HTTP_SYNC_HOST . '/');

    //
    define('CACHE_HOST_PREFIX', '');
}
// với các thể loại cache khác -> sử dụng prefix -> do nó lưu vào ram thì không phân định theo path được
else {
    define('WRITE_CACHE_PATH', WRITEPATH . 'cache/');

    // tạo key theo host để làm prefix
    define('CACHE_HOST_PREFIX', HTTP_SYNC_HOST);
}
//die( CACHE_HOST_PREFIX );
//die( WRITE_CACHE_PATH );
if (!is_dir(WRITE_CACHE_PATH)) {
    mkdir(WRITE_CACHE_PATH, DEFAULT_DIR_PERMISSION) or die('ERROR create cache dir');
    chmod(WRITE_CACHE_PATH, DEFAULT_DIR_PERMISSION);
}

// thời gian cache mặc định
defined('MINI_CACHE_TIMEOUT') || define('MINI_CACHE_TIMEOUT', 300);
defined('MEDIUM_CACHE_TIMEOUT') || define('MEDIUM_CACHE_TIMEOUT', HOUR);
defined('BIG_CACHE_TIMEOUT') || define('BIG_CACHE_TIMEOUT', 21600); // 6 giờ

// cho phép sử dụng lệnh DELETE trong mysql
defined('ALLOW_USING_MYSQL_DELETE') || define('ALLOW_USING_MYSQL_DELETE', false);

/*
 * Tiền tố cho danh mục sản phẩm
 */
/*
defined('WGR_CATEGORY_PREFIX') || define('WGR_CATEGORY_PREFIX', 'category');
if (WGR_CATEGORY_PREFIX != '') {
    define('CATEGORY_BASE_URL', WGR_CATEGORY_PREFIX . '/');
} else {
    define('CATEGORY_BASE_URL', '');
}
*/
/*
 * cấu trúc URL cho category
 * ---> làm kiểu này để còn truyền ra cả javascript sử dụng chung
 * ---> tiếp đến là các website khác nhau muốn đổi URL thì có thể đổi Constants
 */
// category
defined('WGR_CATEGORY_PERMALINK') || define('WGR_CATEGORY_PERMALINK', 'category/%slug%');
// blogs
//defined('WGR_BLOGS_PERMALINK') || define('WGR_BLOGS_PERMALINK', '%taxonomy%/%slug%');
// product_cat
defined('WGR_PRODS_PERMALINK') || define('WGR_PRODS_PERMALINK', 'products/%slug%');
// other taxonomy
defined('WGR_TAXONOMY_PERMALINK') || define('WGR_TAXONOMY_PERMALINK', 'c/%taxonomy%/%term_id%/%slug%');
// mấy các tags thì dùng chung mẫu mặc định luôn
//defined('WGR_TAGS_PERMALINK') || define('WGR_TAGS_PERMALINK', WGR_TAXONOMY_PERMALINK);
//defined('WGR_OPTIONS_PERMALINK') || define('WGR_OPTIONS_PERMALINK', WGR_TAXONOMY_PERMALINK);
//defined('WGR_BLOG_TAGS_PERMALINK') || define('WGR_BLOG_TAGS_PERMALINK', WGR_TAXONOMY_PERMALINK);
//defined('WGR_PROD_TAGS_PERMALINK') || define('WGR_PROD_TAGS_PERMALINK', WGR_TAXONOMY_PERMALINK);
// URL tùy chỉnh của từng tags hoặc custom taxonomy
defined('WGR_CUS_TAX_PERMALINK') || define('WGR_CUS_TAX_PERMALINK', []);
//print_r(WGR_CUS_TAX_PERMALINK);

/*
 * Tiền tố cho trang tĩnh
 */
/*
defined('WGR_PAGES_PREFIX') || define('WGR_PAGES_PREFIX', 'pages');
if (WGR_PAGES_PREFIX != '') {
    define('PAGE_BASE_URL', WGR_PAGES_PREFIX . '/');
} else {
    define('PAGE_BASE_URL', '');
}
*/
/*
 * cấu trúc URL cho post
 * ---> làm kiểu này để còn truyền ra cả javascript sử dụng chung
 * ---> tiếp đến là các website khác nhau muốn đổi URL thì có thể đổi Constants
 */
defined('WGR_POST_PERMALINK') || define('WGR_POST_PERMALINK', '%ID%/%post_name%');
defined('WGR_PROD_PERMALINK') || define('WGR_PROD_PERMALINK', '%post_type%-%ID%/%post_name%');
defined('WGR_PAGE_PERMALINK') || define('WGR_PAGE_PERMALINK', 'pages/%post_name%');
//defined('WGR_BLOG_PERMALINK') || define('WGR_BLOG_PERMALINK', WGR_PROD_PERMALINK);
defined('WGR_POSTS_PERMALINK') || define('WGR_POSTS_PERMALINK', 'p/%post_type%/%ID%/%post_name%.html');
// URL tùy chỉnh của từng custom post type
defined('WGR_CUS_POST_PERMALINK') || define('WGR_CUS_POST_PERMALINK', []);
//print_r(WGR_CUS_POST_PERMALINK);

/**
 * Tiền tố cho bảng database.
 * Chỉ sử dụng số, ký tự và dấu gạch dưới!
 */
defined('WGR_TABLE_PREFIX') || define('WGR_TABLE_PREFIX', 'wp_');

define('WGR_TERM_VIEW', WGR_TABLE_PREFIX . 'zzz_v_terms');
define('WGR_POST_VIEW', WGR_TABLE_PREFIX . 'zzz_v_posts');

// Một số thư mục chỉ cho phép 1 số định dạng file được phép truy cập
defined('HTACCESSS_ALLOW') || define('HTACCESSS_ALLOW', 'zip|xlsx|xls|mp3|css|js|map|htm?l|xml|json|webmanifest|tff|eot|woff?|gif|jpe?g|tiff?|png|webp|bmp|ico|svg');

// https://scotthelme.co.uk/content-security-policy-an-introduction/
// Content-Security-Policy
defined('WGR_CSP_ENABLE') || define('WGR_CSP_ENABLE', false);
//echo ROOTPATH . '.env';
// nếu chế độ debug được bật -> bắt buộc phải tắt CSP
if (file_exists(ROOTPATH . '.env')) {
    define('WGR_CSP_FOR_DEBUG', false);
} else {
    // còn lại sẽ lấy theo config động
    define('WGR_CSP_FOR_DEBUG', WGR_CSP_ENABLE);
}
//var_dump(WGR_CSP_FOR_DEBUG);

// nội dung cho phần src của CSP
// tham khảo của mấy web lớn: https://securityheaders.com/?q=https%3A%2F%2Fwww.facebook.com%2F&followRedirects=on
// default-src
defined('WGR_CSP_DEFAULT_SRC') || define('WGR_CSP_DEFAULT_SRC', "'self' 'unsafe-inline' 'unsafe-eval' data: blob: *.fbsbx.com 'unsafe-inline' *.facebook.com *.fbcdn.net *.gstatic.com");
// script-src
defined('WGR_CSP_SCRIPT_SRC') || define('WGR_CSP_SCRIPT_SRC', "'self' 'unsafe-inline' 'unsafe-eval' data: *.googleapis.com *.fbcdn.net *.facebook.com *.googletagmanager.com *.tiktok.com *.doubleclick.net");
// style-src
defined('WGR_CSP_STYLE_SRC') || define('WGR_CSP_STYLE_SRC', "'self' 'unsafe-inline' 'unsafe-eval' blob: data: *.facebook.com *.fbcdn.net *.facebook.net *.google-analytics.com *.google.com *.facebook.net *.googleapis.com");
// img-src
defined('WGR_CSP_IMG_SRC') || define('WGR_CSP_IMG_SRC', "'self' data: *.google.com *.google.com.vn *.googletagmanager.com");
// connect-src
defined('WGR_CSP_CONNECT_SRC') || define('WGR_CSP_CONNECT_SRC', "'self' *.google-analytics.com *.tiktok.com");
// child-src -> for youtube video
defined('WGR_CSP_CHILD_SRC') || define('WGR_CSP_CHILD_SRC', "'self' *.youtube.com");

// Khi cần thay đổi URL cho trang login thì đổi tham số này -> có thể tận dụng HTML của trang login thay vì tự code mới
defined('ACTION_LOGIN_FORM') || define('ACTION_LOGIN_FORM', './guest/login');
