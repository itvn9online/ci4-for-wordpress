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
// vadmin -> views của admin
define('ADMIN_ROOT_VIEWS', VIEWS_PATH . 'vadmin/');
define('ADMIN_CUSTOM_VIEWS', VIEWS_CUSTOM_PATH . 'vadmin/');
//define('ADMIN_DEFAULT_VIEWS', VIEWS_PATH . 'vadmin/default/');
//die( VIEWS_CUSTOM_PATH );

/**
 * Tạo 1 chuỗi ngẫu nhiên cho URL xác minh đăng nhập trên nhiều thiết bị
 * Tránh việc bị dùng các extension kiểu adblock chặn request
 * Các chuỗi này chỉ dùng sau khi đã đăng nhập -> có thể dùng theo session id do không dình cache
 **/
// tạo hàm ngẫu nhiên theo ngày giờ
$rand_by_ses = md5($_SERVER['HTTP_HOST'] . date('Y-m-d H'));
// hỗ trợ giao thoa giữa 2 cung giờ trong vòng 30 phút
$rand2_by_ses = md5($_SERVER['HTTP_HOST'] . date('Y-m-d H', time() - 3600));
// $rand_by_ses = md5(session_id());
// echo $rand_by_ses . '<br>' . PHP_EOL;
// khai báo constants để tạo routes
define('RAND_MULTI_LOGOUT', '_' . substr($rand_by_ses, 0, 12));
// die(RAND_MULTI_LOGOUT);
define('RAND2_MULTI_LOGOUT', '_' . substr($rand2_by_ses, 0, 12));
//echo RAND_MULTI_LOGOUT . '<br>' . PHP_EOL;
define('RAND_MULTI_LOGGED', '_' . substr($rand_by_ses, 6, 12));
define('RAND2_MULTI_LOGGED', '_' . substr($rand2_by_ses, 6, 12));
//echo RAND_MULTI_LOGGED . '<br>' . PHP_EOL;
define('RAND_CONFIRM_LOGGED', '_' . substr($rand_by_ses, 12, 12));
define('RAND2_CONFIRM_LOGGED', '_' . substr($rand2_by_ses, 12, 12));
//echo RAND_CONFIRM_LOGGED . '<br>' . PHP_EOL;

/**
 * Chuỗi dùng cho đăng nhập tự động -> mỗi chuỗi sẽ có hạn tầm 1h
 * Do chuỗi này sử dụng lúc chưa đăng nhập nên có cache, cần cố định theo tên miền + ngày giờ để giới hạn thời hạn sử dụng
 **/
// $rand_by_date = md5(DYNAMIC_BASE_URL . date('Y-m-d H'));
$rand_by_date = md5(RAND_MULTI_LOGOUT);
$rand2_by_date = md5(RAND2_MULTI_LOGOUT);
define('RAND_REMEMBER_LOGIN', '_' . substr($rand_by_date, 0, 12));
define('RAND2_REMEMBER_LOGIN', '_' . substr($rand2_by_date, 0, 12));
//echo RAND_REMEMBER_LOGIN . '<br>' . PHP_EOL;
// chuỗi dùng để tạo url lấy dữ liệu anti spam qua ajax -> tránh cache
define('RAND_GET_ANTI_SPAM', '_' . substr($rand_by_date, 6, 12));
define('RAND2_GET_ANTI_SPAM', '_' . substr($rand2_by_date, 6, 12));
//echo RAND_GET_ANTI_SPAM . '<br>' . PHP_EOL;


/**
 * lưu giá trị của config vào biến này, nếu hàm sau có gọi lại thì tái sử dụng luôn
 **/
$GLOBALS['this_cache_config'] = null;
$GLOBALS['this_cache_lang'] = null;
$GLOBALS['this_cache_num'] = null;
$GLOBALS['this_cache_checkbox'] = null;




/**
 * Danh sách các custom taxonomy mà người dùng đăng ký sẽ được khai báo thêm ở đây
 **/
$GLOBALS['arr_custom_taxonomy'] = [];
// hàm đăng ký nhiều taxonomy 1 lúc
/* Code mẫu:
register_taxonomys([
    'custom_taxonomy1' => [
        'name' => 'Custom taxonomy name',
        // cho phép lấy nhóm cha
        'set_parent' => true,
        'slug' => '',
        // mặc định public = on -> sẽ hiển thị ra ngoài
        //'public' => 'off',
    ],
    'custom_taxonomy2' => [
        'name' => 'Custom taxonomy name',
        // cho phép lấy nhóm cha
        'set_parent' => true,
        'slug' => '',
        // mặc định public = on -> sẽ hiển thị ra ngoài
        //'public' => 'off',
    ],
]);
*/
function register_taxonomys($arrs = [])
{
    $a = [];
    foreach ($arrs as $k => $v) {
        $a[$k] = $v;
    }
    $GLOBALS['arr_custom_taxonomy'] = $a;
}

/**
 * Danh sách các custom taxonomy mà người dùng đăng ký sẽ được khai báo thêm ở đây
 * Các meta đã được khai báo sẽ không bị dọn dẹp khi update meta
 **/
$GLOBALS['arr_custom_meta_taxonomy'] = [];
// hàm đăng ký nhiều taxonomy meta 1 lúc
/* Code mẫu:
register_taxonomys_meta([
    'taxonomy' => [
        'meta_key1' => [
            // name: dùng để xác định tên của option
            'name' => 'Meta name',
            // type (không bắt buộc): xác định định dạng của meta, mặc định là text
            'type' => 'text|number|select|textarea',
            // ghi chú nếu có
            'desc' => 'Description',
        ],
        'meta_key2' => [
            'name' => 'Meta name'
        ]
    ],
    'taxonomy2' => [
        'meta_key1' => [
            'name' => 'Meta name'
        ],
        'meta_key2' => [
            'name' => 'Meta name'
        ]
    ],
]);
*/
function register_taxonomys_meta($arrs = [])
{
    $a = [];
    foreach ($arrs as $k => $v) {
        $a[$k] = $v;
    }
    $GLOBALS['arr_custom_meta_taxonomy'] = $a;
}






/**
 * Danh sách các custom post type mà người dùng đăng ký sẽ được khai báo thêm ở đây
 **/
$GLOBALS['arr_custom_post_type'] = [];
/* Code mẫu:
register_posts_type([
    'custom_post_type1' => [
        'name' => 'Custom type name',
        // mặc định public = on -> sẽ hiển thị ra ngoài
        //'public' => 'off',
    ],
    'custom_post_type2' => [
        'name' => 'Custom type name',
        // mặc định public = on -> sẽ hiển thị ra ngoài
        //'public' => 'off',
    ],
]);
*/
function register_posts_type($arrs = [])
{
    $a = [];
    foreach ($arrs as $k => $v) {
        $a[$k] = $v;
    }
    $GLOBALS['arr_custom_post_type'] = $a;
}


/**
 * Danh sách các custom post meta mà người dùng đăng ký sẽ được khai báo thêm ở đây
 * Các meta đã được khai báo sẽ không bị dọn dẹp khi update metalà text
 **/
$GLOBALS['arr_custom_post_meta'] = [];
/* Code mẫu:
register_posts_meta([
    'custom_post_type1' => [
        'meta_key1' => [
            // name: dùng để xác định tên của option
            'name' => 'Meta name',
            // type (không bắt buộc): xác định định dạng của meta, mặc định là text
            'type' => 'text|number|select|textarea',
            // ghi chú nếu có
            'desc' => 'Description',
        ],
        'meta_key2' => [
            'name' => 'Custom meta name',
        ],
    ],
    'custom_post_type2' => [
        'meta_key1' => [
            'name' => 'Danh mục',
        ],
        'meta_key2' => [
            'name' => 'Custom meta name',
        ],
    ],
]);
*/
function register_posts_meta($arrs = [])
{
    $a = [];
    foreach ($arrs as $k => $v) {
        $a[$k] = $v;
    }
    $GLOBALS['arr_custom_post_meta'] = $a;
}





/**
 * Danh sách các user type mà người dùng đăng ký sẽ được khai báo thêm ở đây
 * Đầu vào là dạng mảng -> đăng ký nhiều user type 1 lúc
 **/
$GLOBALS['arr_custom_user_type'] = [];
/* Code mẫu:
register_users_type([
    'custom_user_type1' => [
        'name' => 'Custom type name',
        // Khi có tham số này -> custom type sẽ được thêm vào admin menu
        'controller' => 'admin_controller',
    ],
    'custom_user_type2' => [
        'name' => 'Custom type name',
        // Khi có tham số này -> custom type sẽ được thêm vào admin menu
        'controller' => 'admin_controller',
    ],
]);
*/
function register_users_type($arrs = [])
{
    $a = [];
    foreach ($arrs as $k => $v) {
        $a[$k] = $v;
    }
    $GLOBALS['arr_custom_user_type'] = $a;
}


/**
 * Thư mục chứa theme hiển thị cho website (tùy theo yêu cầu của khách hàng mà thiết lập giao diện khác nhau)
 **/
// nạp file xác định THEMENAME (nếu có)
if (is_file(PUBLIC_PUBLIC_PATH . 'wp-content/themes/actived.php')) {
    include PUBLIC_PUBLIC_PATH . 'wp-content/themes/actived.php';
} else {
    // nếu không có file active theme tự động -> gán mặc định theme echbayfour
    defined('THEMENAME') || define('THEMENAME', 'echbayfour');

    // tạo file khai báo theme theo phiên bản mới
    file_put_contents(PUBLIC_PUBLIC_PATH . 'wp-content/themes/actived.php', '<?php define(\'THEMENAME\', \'' . THEMENAME . '\');', LOCK_EX);
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

/**
 * Ở phiên bản ci-4.50, mấy mảng này trả về null khi gọi qua global
 * Khởi tạo constants cho các mảng động sau khi nạp file functions.php
 */
// print_r($GLOBALS['arr_custom_taxonomy']);
define('ARR_CUSTOM_TAXONOMY', $GLOBALS['arr_custom_taxonomy']);

// print_r($GLOBALS['arr_custom_meta_taxonomy']);
define('ARR_CUSTOM_META_TAXONOMY', $GLOBALS['arr_custom_meta_taxonomy']);

// print_r($GLOBALS['arr_custom_post_type']);
define('ARR_CUSTOM_POST_TYPE', $GLOBALS['arr_custom_post_type']);

// print_r($GLOBALS['arr_custom_post_meta']);
define('ARR_CUSTOM_POST_META', $GLOBALS['arr_custom_post_meta']);

// print_r($GLOBALS['arr_custom_user_type']);
define('ARR_CUSTOM_USER_TYPE', $GLOBALS['arr_custom_user_type']);

// 
// die(__FILE__ . ':' . __LINE__);


// 
defined('MY_APP_TIMEZONE') || define('MY_APP_TIMEZONE', 'UTC');


/**
 * 1 số hosting sử dụng path redis, memcached khác thì cũng cần khai báo lại
 */
defined('WGR_REDIS_HOSTNAME') || define('WGR_REDIS_HOSTNAME', '127.0.0.1');
defined('WGR_REDIS_PORT') || define('WGR_REDIS_PORT', 6379);

// 
defined('WGR_MEMCACHED_HOSTNAME') || define('WGR_MEMCACHED_HOSTNAME', '127.0.0.1');
defined('WGR_MEMCACHED_PORT') || define('WGR_MEMCACHED_PORT', 11211);


/**
 * tùy chỉnh Session driver -> nếu có Redis, Memcached thì nên dùng -> chi tiết xem tại đây:
 * https://codeigniter4.github.io/userguide/libraries/sessions.html?highlight=cache#session-drivers
 */
defined('ROOT_SESSION_DRIVER') || define('ROOT_SESSION_DRIVER', 'CodeIgniter\Session\Handlers\\');
defined('MY_SESSION_DRIVE') || define('MY_SESSION_DRIVE', 'FileHandler');
defined('CUSTOM_SESSION_DRIVER') || define('CUSTOM_SESSION_DRIVER', ROOT_SESSION_DRIVER . MY_SESSION_DRIVE);

/**
 * Session save path
 */
defined('CUSTOM_SESSION_PATH') || define('CUSTOM_SESSION_PATH', WRITEPATH . 'session');


// Trạng thái bình luận mặc định 0|1
defined('DEFAULT_COMMENT_APPROVED') || define('DEFAULT_COMMENT_APPROVED', '1');


// tạo trang amp -> site nào không muốn dùng amp thì tắt nó đi
defined('ENABLE_AMP_VERSION') || define('ENABLE_AMP_VERSION', true);


// khi tạo html nếu meta nào chưa được nhập nó sẽ thừa khối template -> khai báo các template để nó bị xóa đi khi dư thừa
defined('PRODUCT_DEFAULT_META') || define('PRODUCT_DEFAULT_META', []);


/**
 * Cố định danh sách các ngôn ngữ sẽ được hỗ trợ tại đây
 * Sau người dùng muốn chọn cụ thể ngôn ngữ nào thì sẽ chọn lại trong config constants
 */
define(
    'SITE_LANGUAGE_FIXED',
    [
        [
            // Đánh mảng nước ngoài nên vẫn ưu tiên nhất là tiếng Anh
            'value' => 'en',
            'text' => 'English',
            'css_class' => 'text-muted'
        ], [
            // xong đến tiếng Việt
            'value' => 'vn',
            'text' => 'Tiếng Việt',
            'css_class' => 'text-muted'
        ], [
            // tiếng Trung đại lục (giản thể)
            'value' => 'cn',
            // 'text' => '中文(简体)',
            'text' => '中文',
            'css_class' => 'text-muted'
        ], [
            // Hàn
            'value' => 'kr',
            'text' => '한국어',
            'css_class' => 'text-muted'
        ], [
            // Nhật
            'value' => 'jp',
            'text' => '日本語',
            'css_class' => 'text-muted'
        ], [
            // Pháp
            'value' => 'fr',
            'text' => 'Français',
            'css_class' => 'text-muted'
        ], [
            // Thái
            'value' => 'tl',
            'text' => 'ภาษาไทย',
            'css_class' => 'text-muted'
        ], [
            // Tây Ban Nha
            'value' => 'es',
            'text' => 'Español',
            'css_class' => 'text-muted'
        ], [
            // Đức
            'value' => 'de',
            'text' => 'Deutsch',
            'css_class' => 'text-muted'
        ]
    ]
);


/**
 * các ngôn ngữ hiển thị của website
 * mặc định chỉ hiển thị 2 ngôn ngữ
 * muốn hiển thị thêm thì sẽ chọn lại trong config constants
 */
defined('SITE_LANGUAGE_SUPPORT') || define('SITE_LANGUAGE_SUPPORT', [
    SITE_LANGUAGE_FIXED[0],
    SITE_LANGUAGE_FIXED[1],
]);

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
defined('EBE_DATE_TEXT_FORMAT') || define('EBE_DATE_TEXT_FORMAT', EBE_DATE_FORMAT);
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
 * Chuỗi dùng để tạo input anti spam -> mỗi trình duyệt có 1 key khác nhau -> không chung đụng
 * Chuỗi này dùng cả khi đăng nhập nên cần truyền qua ajax
 **/
// define('RAND_ANTI_SPAM', RAND_MULTI_LOGGED);

/**
 * Tạo phiên bản giả lập wordpress
 **/
defined('FAKE_WORDPRESS_VERSION') || define('FAKE_WORDPRESS_VERSION', '6.5');

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

// key dùng để lưu cookie hoặc localStorage của phiên đăng nhập
defined('WGR_COOKIE_LOGIN_KEY') || define('WGR_COOKIE_LOGIN_KEY', '_' . substr(md5(DYNAMIC_BASE_URL), 0, 16));

// khi cần chuyển các file tĩnh sang url khác để giảm tải cho server chính thì dùng chức năng này
defined('CDN_BASE_URL') || define('CDN_BASE_URL', '');

// khi cần chuyển các file ảnh trong thư mục upload/ sang url khác để giảm tải cho server chính thì dùng chức năng này
defined('CDN_UPLOADS_URL') || define('CDN_UPLOADS_URL', '');

// permission mặc định khi up file, tạo thư mục
defined('DEFAULT_FILE_PERMISSION') || define('DEFAULT_FILE_PERMISSION', 0777);
defined('DEFAULT_DIR_PERMISSION') || define('DEFAULT_DIR_PERMISSION', 0777);

// 
define('MY_TOKEN_CSRF_NAME', md5($_SERVER['HTTP_HOST'] . date('Y-m-d')));

// khi cần tối ưu việc select dữ liệu cho bảng post thì khai báo lại tham số này
defined('DEFAULT_SELECT_POST_COL') || define('DEFAULT_SELECT_POST_COL', '*');

// kiểu sử dụng cache -> mặc định là file
defined('MY_CACHE_HANDLER') || define('MY_CACHE_HANDLER', 'file');

// đồng bộ http host về 1 chuỗi chung
// defined('HTTP_SYNC_HOST') || define('HTTP_SYNC_HOST', str_replace('www.', '', str_replace('.', '', str_replace('-', '_', explode(':', $_SERVER['HTTP_HOST'])[0]))));
defined('HTTP_SYNC_HOST') || define('HTTP_SYNC_HOST', THEMENAME);

// chuỗi sẽ thêm vào khi sử dụng hàm mdnam -> md5
defined('CUSTOM_MD5_HASH_CODE') || define('CUSTOM_MD5_HASH_CODE', HTTP_SYNC_HOST);

// token cho chức năng cronjob -> kết nối bên ngoài -> tối thiểu 1 năm đổi 1 lần
defined('CRONJOB_TOKEN') || define('CRONJOB_TOKEN', '_' . substr(md5(date('Y') . CUSTOM_MD5_HASH_CODE), 0, 16));
defined('LOCAL_BAK_PATH') || define('LOCAL_BAK_PATH', '/Volumes/bak');
// Example:
// define('SSH_BAK_PORT', '2233');
defined('SSH_BAK_PORT') || define('SSH_BAK_PORT', '');

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
// var_dump(WGR_IS_MOBILE);

// với cache file -> thư mục lưu cache theo từng tên miền -> code thêm cho các web sử dụng domain pointer
if (MY_CACHE_HANDLER == 'file') {
    define('WRITE_CACHE_PATH', WRITEPATH . 'cache/' . $cache_prefix . '/');
    if (!is_dir(WRITE_CACHE_PATH)) {
        mkdir(WRITE_CACHE_PATH, DEFAULT_DIR_PERMISSION) or die('ERROR create cache dir');
        chmod(WRITE_CACHE_PATH, DEFAULT_DIR_PERMISSION);
    }

    //
    define('CACHE_HOST_PREFIX', '');
}
// với các thể loại cache khác -> sử dụng prefix -> do nó lưu vào ram thì không phân định theo path được
else {
    define('WRITE_CACHE_PATH', WRITEPATH . 'cache/');

    // tạo key theo host để làm prefix
    define('CACHE_HOST_PREFIX', $cache_prefix);
}
// echo CACHE_HOST_PREFIX . '<br>' . PHP_EOL;
// echo WRITE_CACHE_PATH . '<br>' . PHP_EOL;
// die(__FILE__ . ':' . __LINE__);

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
// product_cat -> product-category
defined('WGR_PRODS_PERMALINK') || define('WGR_PRODS_PERMALINK', 'products/%slug%');
// other taxonomy
defined('WGR_TAXONOMY_PERMALINK') || define('WGR_TAXONOMY_PERMALINK', 'c/%taxonomy%/%term_id%/%slug%');
// mấy các tags thì dùng chung mẫu mặc định luôn
defined('WGR_TAGS_PERMALINK') || define('WGR_TAGS_PERMALINK', 'tag/%slug%');
//defined('WGR_OPTIONS_PERMALINK') || define('WGR_OPTIONS_PERMALINK', WGR_TAXONOMY_PERMALINK);
//defined('WGR_BLOG_TAGS_PERMALINK') || define('WGR_BLOG_TAGS_PERMALINK', WGR_TAXONOMY_PERMALINK);
defined('WGR_PROD_TAGS_PERMALINK') || define('WGR_PROD_TAGS_PERMALINK', 'product-tag/%slug%');
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
defined('WGR_CSP_IMG_SRC') || define('WGR_CSP_IMG_SRC', "'self' data: *.google.com *.google.com.vn *.googletagmanager.com *.ytimg.com");
// connect-src
defined('WGR_CSP_CONNECT_SRC') || define('WGR_CSP_CONNECT_SRC', "'self' *.google-analytics.com *.tiktok.com");
// child-src -> for youtube video
defined('WGR_CSP_CHILD_SRC') || define('WGR_CSP_CHILD_SRC', "'self' *.youtube.com");

// Khi cần thay đổi URL cho trang login thì đổi tham số này -> có thể tận dụng HTML của trang login thay vì tự code mới
defined('ACTION_LOGIN_FORM') || define('ACTION_LOGIN_FORM', './guest/login');
