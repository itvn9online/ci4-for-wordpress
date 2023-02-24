<?php

/*
 * Tài khoản FTP -> dùng để điều khiển file trong trường hợp bị lỗi permission
 */
//define( 'FTP_HOST', $_SERVER[ 'SERVER_ADDR' ] );
//define( 'FTP_HOST', '127.0.0.1' );
//define( 'FTP_USER', '' );
//define( 'FTP_PASS', '' );

//
//define( 'PARTNER_WEBSITE', 'https://' . $_SERVER[ 'HTTP_HOST' ] . '/' );
//define( 'PARTNER_BRAND_NAME', strtoupper( $_SERVER[ 'HTTP_HOST' ] ) );

//
//define( 'MY_DB_DRIVER', 'MySQLi|Postgre|PDO|Oracle' );

// tinh chỉnh protocol theo ý thích -> mặc định là https
//define( 'BASE_PROTOCOL', 'http' );

// chuỗi sẽ thêm vào khi sử dụng hàm mdnam -> md5 -> tăng độ bảo mật cho chuỗi
//define( 'CUSTOM_MD5_HASH_CODE', '' );

// kiểu sử dụng cache, độ ưu tiên: redis -> memcached -> file
//define( 'MY_CACHE_HANDLER', 'redis|memcached|file' );

// khi cần chuyển các file tĩnh sang url khác để giảm tải cho server chính thì dùng chức năng này
//define( 'CDN_BASE_URL', '' );

// Mặc định không cho xóa hoàn toàn dữ liệu trong mysql, nếu bạn muốn xóa hẳn thì có thể kích hoạt tính năng này.
//define( 'ALLOW_USING_MYSQL_DELETE', true );


/*
* ngôn ngữ hiển thị của website
*/
// các ngôn ngữ được hỗ trợ
/*
define(
    'SITE_LANGUAGE_SUPPORT',
    [
        [
            'value' => 'vn',
            'text' => 'Tiếng Việt',
            'css_class' => 'text-muted'
        ],
        // muốn chạy bao nhiêu ngôn ngữ thì thêm bằng đấy mảng vào đây
        [
            'value' => 'en',
            'text' => 'English',
            'css_class' => 'text-success'
        ],
    ]
);
*/

// ngôn ngữ mặc định
// -> đặt là mảng số 0
//defined('SITE_LANGUAGE_DEFAULT') || define('SITE_LANGUAGE_DEFAULT', SITE_LANGUAGE_SUPPORT[0]['value']);
// hoặc định nghĩa cụ thể 1 ngôn ngữ
//define('SITE_LANGUAGE_DEFAULT', 'vn');


// Số lượng bản dịch dạng input -> website nào cần dùng nhiều tăng số lượng trong file functions lên
//define( 'NUMBER_TRANS_INPUT', 30 );
// Số lượng bản dịch dạng textarea -> website nào cần dùng nhiều tăng số lượng trong file functions lên
//define( 'NUMBER_TRANS_TEXTAREA', 20 );
/*
// khi cần thay label cho trang /admin/translates để dễ hiểu hơn thì thêm các thông số vào đây
define( 'TRANS_TRANS_LABEL', [
    'custom_text0' => 'Bản dịch số 0',
    //'custom_textarea0' => 'Bản dịch số 0',
] );
*/

// Số lượng bản ghi dạng số nguyên -> website nào cần dùng nhiều tăng số lượng trong file functions lên
//defined('NUMBER_NUMS_INPUT') || define('NUMBER_NUMS_INPUT', 3);
// khi cần thay label cho trang /admin/nummons để dễ hiểu hơn thì thêm các thông số vào đây
/*
defined('TRANS_NUMS_LABEL') || define(
    'TRANS_NUMS_LABEL',
    [
        'custom_num_mon0' => 'Tùy chỉnh số 0',
    ]
);
*/

// Số lượng bản ghi dạng số nguyên -> website nào cần dùng nhiều tăng số lượng trong file functions lên
//defined('NUMBER_CHECKBOXS_INPUT') || define('NUMBER_CHECKBOXS_INPUT', 3);
// khi cần thay label cho trang /admin/checkboxs để dễ hiểu hơn thì thêm các thông số vào đây
/*
defined('TRANS_CHECKBOXS_LABEL') || define(
    'TRANS_CHECKBOXS_LABEL',
    [
        'custom_checkbox0' => 'Checkbox số 0',
    ]
);
*/


/*
 * Tiền tố cho danh mục sản phẩm
 */
//define( 'WGR_CATEGORY_PREFIX', 'category' );

/*
 * Tiền tố cho trang tĩnh
 */
//define( 'WGR_PAGES_PREFIX', 'pages' );

//
//define('WGR_POST_PERMALINK', '%ID%/%post_name%');
//define('WGR_BLOG_PERMALINK', '%post_type%-%ID%/%post_name%');
//define('WGR_PAGE_PERMALINK', '%page_base%%post_name%');
//define('WGR_POSTS_PERMALINK', 'p/%post_type%/%ID%/%post_name%.html');

/*
 * Thêm menu cho admin
 * Ngoài các menu mặc định, với mỗi website có thể thêm các menu tùy chỉnh khác nhau vào đây theo công thức mẫu
 */
/*
function register_admin_menu() {
return [
'admin/controller' => [
// nếu có phân quyền thì nhập phân quyền vào đây, không thì xóa nó đi -> quyền admin
'role' => [
UsersType::ADMIN
],
'name' => 'Custom admin menu',
'icon' => 'fa fa-bug',
'arr' => [
'admin/sub_controller' => [
'name' => 'Sub menu',
'icon' => 'fa fa-plus',
//'target' => '_blank',
],
],
'order' => 95,
]
];
}
*/

/*
 * đăng ký taxonomy riêng nếu muốn taxonomy này được public ra ngoài
 */
/*
register_taxonomy('custom_taxonomy', [
    'name' => 'Custom name',
    'set_parent' => true,
    'slug' => '',
    //'public' => 'off',
]);
*/

/*
 * đăng ký taxonomy riêng nếu muốn post type này được public ra ngoài
 */
/*
register_post_type('custom_post_type', [
    'name' => 'Custom name',
    //'public' => 'off',
]);
*/