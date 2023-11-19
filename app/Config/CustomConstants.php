<?php

/**
 * Constants mặc định của code gốc, file này mà xóa đi thì chỉ có ăn cám -> code này hết sử dụng luôn
 **/

/////////////////////////////////////////////////// DAIDQ CONFIG ////////////////////////////////////////////////////////
/**
 * daidq: 2021-09-14
 * Phần cấu hình thiết lập động để có thể tái sử dụng code cho nhiều website khác nhau mà không cần code lại nhiều
 * Các controller, model... cố gắng viết theo quy tắc extends để có thể tái sử dụng
 **/

/**
 * Các tham số khác, rất ít khi thay đổi
 **/
define('PUBLIC_HTML_PATH', ROOTPATH);
//echo PUBLIC_HTML_PATH . '<br>' . PHP_EOL;
define('PUBLIC_PUBLIC_PATH', PUBLIC_HTML_PATH . 'public/');
//die( PUBLIC_PUBLIC_PATH );

// view mặc định của framework
define('VIEWS_PATH', APPPATH . 'Views/');
// view riêng của từng theme nếu có thì view này sẽ được ưu tiên sử dụng
define('VIEWS_CUSTOM_PATH', ROOTPATH . 'custom/Views/');
// views của admin
define('ADMIN_ROOT_VIEWS', VIEWS_PATH . 'sadmin/');
define('ADMIN_CUSTOM_VIEWS', VIEWS_CUSTOM_PATH . 'sadmin/');
//define('ADMIN_DEFAULT_VIEWS', VIEWS_PATH . 'sadmin/default/');
//die( VIEWS_CUSTOM_PATH );


/**
 * lưu giá trị của config vào biến này, nếu hàm sau có gọi lại thì tái sử dụng luôn
 **/
$this_cache_config = NULL;
$this_cache_lang = NULL;
$this_cache_num = NULL;
$this_cache_checkbox = NULL;


/**
 * Danh sách các custom taxonomy mà người dùng đăng ký sẽ được khai báo thêm ở đây
 **/
$arr_custom_taxonomy = [];
function register_taxonomy($name, $ops = [])
{
    global $arr_custom_taxonomy;
    $arr_custom_taxonomy[$name] = $ops;
}
// hàm đăng ký nhiều taxonomy 1 lúc
function register_taxonomys($arrs = [])
{
    global $arr_custom_taxonomy;
    foreach ($arrs as $k => $v) {
        $arr_custom_taxonomy[$k] = $v;
    }
}


/**
 * Danh sách các custom post type mà người dùng đăng ký sẽ được khai báo thêm ở đây
 **/
$arr_custom_post_type = [];
function register_post_type($name, $ops = [])
{
    global $arr_custom_post_type;
    $arr_custom_post_type[$name] = $ops;
}
// hàm đăng ký nhiều post type 1 lúc
function register_posts_type($arrs = [])
{
    global $arr_custom_post_type;
    foreach ($arrs as $k => $v) {
        $arr_custom_post_type[$k] = $v;
    }
}


/**
 * Danh sách các custom post meta mà người dùng đăng ký sẽ được khai báo thêm ở đây
 * Các meta đã được khai báo sẽ không bị dọn dẹp khi update meta
 **/
$arr_custom_post_meta = [];
function register_post_meta($post_type, $ops = [])
{
    global $arr_custom_post_meta;
    $arr_custom_post_meta[$post_type] = $ops;
}
// hàm đăng ký nhiều post meta 1 lúc
function register_posts_meta($arrs = [])
{
    global $arr_custom_post_meta;
    foreach ($arrs as $k => $v) {
        $arr_custom_post_meta[$k] = $v;
    }
}


/**
 * Danh sách các user type mà người dùng đăng ký sẽ được khai báo thêm ở đây
 * Đầu vào là dạng mảng -> đăng ký nhiều user type 1 lúc
 **/
$arr_custom_user_type = [];
function register_users_type($arrs = [])
{
    global $arr_custom_user_type;
    foreach ($arrs as $k => $v) {
        $arr_custom_user_type[$k] = $v;
    }
}


/**
 * Thư mục chứa theme hiển thị cho website (tùy theo yêu cầu của khách hàng mà thiết lập giao diện khác nhau)
 **/
// nạp file xác định THEMENAME (nếu có)
if (is_file(PUBLIC_PUBLIC_PATH . 'wp-content/themes/actived.php')) {
    include PUBLIC_PUBLIC_PATH . 'wp-content/themes/actived.php';
} else {
    // xác định theme tự động
    foreach (glob(PUBLIC_PUBLIC_PATH . 'wp-content/themes/*.actived-theme') as $filename) {
        // xóa các file theme kiểu cũ này đi -> lát update tự động sang kiểu mới dùng cho gọn
        unlink($filename);

        //
        $filename = basename($filename, '.actived-theme');
        //echo $filename . '<br>' . PHP_EOL;
        if (is_dir(PUBLIC_PUBLIC_PATH . 'wp-content/themes/' . $filename)) {
            define('THEMENAME', $filename);
            break;
        }
    }

    // nếu không có file active theme tự động -> gán mặc định theme echbayfour
    defined('THEMENAME') || define('THEMENAME', 'echbayfour');

    // tạo file khai báo theme theo phiên bản mới
    file_put_contents(PUBLIC_PUBLIC_PATH . 'wp-content/themes/actived.php', '<?php define(\'THEMENAME\', \'' . THEMENAME . '\');');
}
//echo THEMENAME . '<br>' . PHP_EOL;


//
define('THEMEPATH', PUBLIC_PUBLIC_PATH . 'wp-content/themes/' . THEMENAME . '/');
//die( THEMEPATH );


####################################################################
// file Constants động -> file này nhận dữ liệu trong config từ admin theo URL sau: sadmin/constants
define('DYNAMIC_CONSTANTS_PATH', APPPATH . 'Config/DynamicConstants.php');

/**
 * Nạp Constants của từng web -> code động -> độ ưu tiên cao nhất
 **/
if (is_file(DYNAMIC_CONSTANTS_PATH)) {
    include DYNAMIC_CONSTANTS_PATH;
}

/**
 * nạp file function của từng theme -> code cứng -> sẽ bị phủ định bởi code động
 * -> trong functions phải khai báo theo mẫu: defined() || define()
 **/
if (is_file(THEMEPATH . 'functions.php')) {
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

// khi cần thay label cho trang /sadmin/translates để dễ hiểu hơn thì thêm các thông số vào đây
defined('TRANS_TRANS_LABEL') || define(
    'TRANS_TRANS_LABEL',
    [
        'custom_text0' => 'Bản dịch số 0',
        //'custom_textarea0' => 'Bản dịch số 0',
    ]
);

// khi cần thay label cho trang /sadmin/nummons để dễ hiểu hơn thì thêm các thông số vào đây
defined('TRANS_NUMS_LABEL') || define(
    'TRANS_NUMS_LABEL',
    [
        'custom_num_mon0' => 'Tùy chỉnh số 0',
    ]
);

// thời gian hết hạn cho mỗi token trong chức năng anti spam
defined('ANTI_SPAM_EXPIRED') || define('ANTI_SPAM_EXPIRED', 3600);

// Số lượng bản ghi dạng số nguyên -> website nào cần dùng nhiều tăng số lượng trong file functions lên
defined('NUMBER_CHECKBOXS_INPUT') || define('NUMBER_CHECKBOXS_INPUT', 3);
// khi cần thay label cho trang /sadmin/checkboxs để dễ hiểu hơn thì thêm các thông số vào đây
defined('TRANS_CHECKBOXS_LABEL') || define(
    'TRANS_CHECKBOXS_LABEL',
    [
        'custom_checkbox0' => 'Checkbox số 0',
    ]
);


//
defined('EBE_DATE_FORMAT') || define('EBE_DATE_FORMAT', 'Y-m-d');
defined('EBE_DATETIME_FORMAT') || define('EBE_DATETIME_FORMAT', 'Y-m-d H:i:s');

/**
 * Một số thông số sẽ ko lưu vào session đăng nhập
 * Site nào có tùy chỉnh bảng users thì thêm các cột không lưu vào đây
 */
defined('DENY_IN_LOGGED_SES') || define('DENY_IN_LOGGED_SES', []);

/**
 * tạo đường dẫn admin tránh đường dẫn mặc định. Ví dụ : admin -> nhằm tăng cường bảo mật cho website
 **/
defined('CUSTOM_ADMIN_URI') || define('CUSTOM_ADMIN_URI', 'wgr-wp-admin');

/**
 * Khi muốn fake lượt xem cho post, product thì chỉnh tham số này trong functions php, mặc định sẽ update 1 lượt view mỗi lần
 */
defined('CUSTOM_FAKE_POST_VIEW') || define('CUSTOM_FAKE_POST_VIEW', 1);

/**
 * Tạo 1 chuỗi ngẫu nhiên cho URL xác minh đăng nhập trên nhiều thiết bị
 * Tránh việc bị dùng các extension kiểu adblock chặn request
 * Các chuỗi này chỉ dùng sau khi đã đăng nhập -> có thể dùng theo session id do không dình cache
 **/
// tạo hàm ngẫu nhiên theo ngày
$rand_by_ses = md5(session_id());
// echo $rand_by_ses . '<br>' . PHP_EOL;
// khai báo constans để tạo routes
define('RAND_MULTI_LOGOUT', '_' . substr($rand_by_ses, 0, 12));
//echo RAND_MULTI_LOGOUT . '<br>' . PHP_EOL;
define('RAND_MULTI_LOGGED', '_' . substr($rand_by_ses, 6, 12));
//echo RAND_MULTI_LOGGED . '<br>' . PHP_EOL;
define('RAND_CONFIRM_LOGGED', '_' . substr($rand_by_ses, 12, 12));
//echo RAND_CONFIRM_LOGGED . '<br>' . PHP_EOL;

/**
 * Chuỗi dùng để tạo input anti spam -> mỗi trình duyệt có 1 key khác nhau -> không chung đụng
 * Chuỗi này dùng cả khi đăng nhập nên cần truyền qua ajax
 **/
define('RAND_ANTI_SPAM', RAND_MULTI_LOGGED);

/**
 * Tạo phiên bản giả lập wordpress
 **/
define('FAKE_WORDPRESS_VERSION', '6.3.1');

// website của nhà phát triển
defined('PARTNER_WEBSITE') || define('PARTNER_WEBSITE', 'https://echbay.com/');
defined('PARTNER_BRAND_NAME') || define('PARTNER_BRAND_NAME', 'EchBay.com');
//
defined('PARTNER2_WEBSITE') || define('PARTNER2_WEBSITE', 'https://webgiare.org/');
defined('PARTNER2_BRAND_NAME') || define('PARTNER2_BRAND_NAME', 'WebGiaRe.org');

// kiểu kết nối dữ liệu
defined('MY_DB_DRIVER') || define('MY_DB_DRIVER', 'MySQLi');

/**
 * URL động cho website để có thể chạy trên nhiều tên miền khác nhau mà không cần config lại
 **/
// tinh chỉnh protocol theo ý thích -> mặc định là https
defined('BASE_PROTOCOL') || define('BASE_PROTOCOL', 'https');
// -> url động cho website
define('DYNAMIC_BASE_URL', BASE_PROTOCOL . '://' . $_SERVER['HTTP_HOST'] . '/');
//die( DYNAMIC_BASE_URL );

// khi cần chuyển các file tĩnh sang url khác để giảm tải cho server chính thì dùng chức năng này
defined('CDN_BASE_URL') || define('CDN_BASE_URL', '');

// khi cần chuyển các file ảnh trong thư mục upload/ sang url khác để giảm tải cho server chính thì dùng chức năng này
defined('CDN_UPLOADS_URL') || define('CDN_UPLOADS_URL', '');

// permission mặc định khi up file, tạo thư mục
defined('DEFAULT_FILE_PERMISSION') || define('DEFAULT_FILE_PERMISSION', 0777);
defined('DEFAULT_DIR_PERMISSION') || define('DEFAULT_DIR_PERMISSION', 0777);

// kiểu sử dụng cache -> mặc định là file
defined('MY_CACHE_HANDLER') || define('MY_CACHE_HANDLER', 'file');

// đồng bộ http host về 1 chuỗi chung
define('HTTP_SYNC_HOST', str_replace('www.', '', str_replace('.', '', str_replace('-', '_', explode(':', $_SERVER['HTTP_HOST'])[0]))));

// chuỗi sẽ thêm vào khi sử dụng hàm mdnam -> md5
defined('CUSTOM_MD5_HASH_CODE') || define('CUSTOM_MD5_HASH_CODE', HTTP_SYNC_HOST);

/**
 * Chuỗi dùng cho đăng nhập tự động -> mỗi chuỗi sẽ có hạn tầm 1h
 * Do chuỗi này sử dụng lúc chưa đăng nhập nên có cache, cần cố định theo tên miền + ngày giờ để giới hạn thời hạn sử dụng
 **/
$rand_by_date = md5(DYNAMIC_BASE_URL . date('Y-m-d H'));
define('RAND_REMEMBER_LOGIN', '_' . substr($rand_by_date, 0, 12));
//echo RAND_REMEMBER_LOGIN . '<br>' . PHP_EOL;
// chuỗi dùng để tạo url lấy dữ liệu anti spam qua ajax -> tránh cache
define('RAND_GET_ANTI_SPAM', '_' . substr($rand_by_date, 6, 12));
//echo RAND_GET_ANTI_SPAM . '<br>' . PHP_EOL;

// tách riêng cache cho mobile và desktop
// fake function wp_is_mobile of wordpress
$is_mobile = false;
$cache_prefix = HTTP_SYNC_HOST;
if (isset($_SERVER['HTTP_USER_AGENT'])) {
    $a = $_SERVER['HTTP_USER_AGENT'];
    if (!empty($a)) {
        if (
            // Many mobile devices (all iPhone, iPad, etc.)
            strpos($a, 'Mobile') !== false ||
            strpos($a, 'Android') !== false ||
            strpos($a, 'Silk/') !== false ||
            strpos($a, 'Kindle') !== false ||
            strpos($a, 'BlackBerry') !== false ||
            strpos($a, 'Opera Mini') !== false ||
            strpos($a, 'Opera Mobi') !== false
        ) {
            // xác định thiết bị đang là mobile
            $is_mobile = true;
            // thay đổi path hoặc prefix cho cache
            $cache_prefix .= '_m';
        }
    }
}
define('WGR_IS_MOBILE', $is_mobile);

// với cache file -> thư mục lưu cache theo từng tên miền -> code thêm cho các web sử dụng domain pointer
if (MY_CACHE_HANDLER == 'file') {
    define('WRITE_CACHE_PATH', WRITEPATH . 'cache/' . $cache_prefix . '/');

    //
    define('CACHE_HOST_PREFIX', '');
}
// với các thể loại cache khác -> sử dụng prefix -> do nó lưu vào ram thì không phân định theo path được
else {
    define('WRITE_CACHE_PATH', WRITEPATH . 'cache/');

    // tạo key theo host để làm prefix
    define('CACHE_HOST_PREFIX', $cache_prefix);
}
//die(CACHE_HOST_PREFIX);
//die(WRITE_CACHE_PATH);
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

/**
 * Tiền tố cho danh mục sản phẩm
 **/
/*
defined('WGR_CATEGORY_PREFIX') || define('WGR_CATEGORY_PREFIX', 'category');
if (WGR_CATEGORY_PREFIX != '') {
    define('CATEGORY_BASE_URL', WGR_CATEGORY_PREFIX . '/');
} else {
    define('CATEGORY_BASE_URL', '');
}
*/
/**
 * cấu trúc URL cho category
 * ---> làm kiểu này để còn truyền ra cả javascript sử dụng chung
 * ---> tiếp đến là các website khác nhau muốn đổi URL thì có thể đổi Constants
 **/
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

/**
 * Tiền tố cho trang tĩnh
 **/
/*
defined('WGR_PAGES_PREFIX') || define('WGR_PAGES_PREFIX', 'pages');
if (WGR_PAGES_PREFIX != '') {
    define('PAGE_BASE_URL', WGR_PAGES_PREFIX . '/');
} else {
    define('PAGE_BASE_URL', '');
}
*/
/**
 * cấu trúc URL cho post
 * ---> làm kiểu này để còn truyền ra cả javascript sử dụng chung
 * ---> tiếp đến là các website khác nhau muốn đổi URL thì có thể đổi Constants
 **/
defined('WGR_POST_PERMALINK') || define('WGR_POST_PERMALINK', '%ID%/%post_name%');
defined('WGR_PROD_PERMALINK') || define('WGR_PROD_PERMALINK', 'product-%ID%/%post_name%');
defined('WGR_PAGE_PERMALINK') || define('WGR_PAGE_PERMALINK', 'pages/%post_name%');
//defined('WGR_BLOG_PERMALINK') || define('WGR_BLOG_PERMALINK', WGR_PROD_PERMALINK);
defined('WGR_POSTS_PERMALINK') || define('WGR_POSTS_PERMALINK', 'p/%post_type%/%ID%/%post_name%.html');
// URL tùy chỉnh của từng custom post type
defined('WGR_CUS_POST_PERMALINK') || define('WGR_CUS_POST_PERMALINK', []);
//print_r(WGR_CUS_POST_PERMALINK);

/**
 * Với 1 số post type, nếu sử dụng ID làm url chính thì có thể bỏ qua chế độ kiểm tra slug do ID là tham số không thể trùng nhau
 * Khai báo trong file functions.php của theme để phủ định tham số mặc định ở đây
 */
defined('POST_ID_PERMALINK') || define('POST_ID_PERMALINK', []);

/**
 * Tiền tố cho bảng database.
 * Chỉ sử dụng số, ký tự và dấu gạch dưới!
 **/
defined('WGR_TABLE_PREFIX') || define('WGR_TABLE_PREFIX', 'wp_');

define('WGR_TERM_VIEW', WGR_TABLE_PREFIX . 'zzz_v_terms');
define('WGR_POST_VIEW', WGR_TABLE_PREFIX . 'zzz_v_posts');

// Một số thư mục chỉ cho phép 1 số định dạng file được phép truy cập
defined('HTACCESSS_ALLOW') || define('HTACCESSS_ALLOW', 'zip|xlsx|xls|mp3|css|js|map|html?|xml|json|webmanifest|tff|eot|woff?|gif|jpe?g|tiff?|png|webp|bmp|ico|svg|xsl|otf');

// https://scotthelme.co.uk/content-security-policy-an-introduction/
// Content-Security-Policy
defined('WGR_CSP_ENABLE') || define('WGR_CSP_ENABLE', false);
//echo ROOTPATH . '.env';
// nếu chế độ debug được bật -> bắt buộc phải tắt CSP
if (is_file(ROOTPATH . '.env')) {
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
