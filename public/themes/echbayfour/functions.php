<?php

/*
 * Tài khoản FTP -> dùng để điều khiển file trong trường hợp bị lỗi permission
 */
//define( 'FTP_HOST', $_SERVER[ 'SERVER_ADDR' ] );
//define( 'FTP_HOST', '127.0.0.1' );
//define( 'FTP_USER', '' );
//define( 'FTP_PASS', '' );

// tinh chỉnh protocol theo ý thích -> mặc định là https
//define( 'BASE_PROTOCOL', 'http' );

// chuỗi sẽ thêm vào khi sử dụng hàm mdnam -> md5 -> tăng độ bảo mật cho chuỗi
//define( 'CUSTOM_MD5_HASH_CODE', '' );

// kiểu sử dụng cache, độ ưu tiên: redis -> memcached -> file
//define( 'MY_CACHE_HANDLER', 'redis|memcached|file' );

// khi cần chuyển các file tĩnh sang url khác để giảm tải cho server chính thì dùng chức năng này
//define( 'CDN_BASE_URL', '' );

/*
 * Tiền tố cho danh mục sản phẩm
 */
//define( 'WGR_CATEGORY_PREFIX', 'category' );

/*
 * Tiền tố cho trang tĩnh
 */
//define( 'WGR_PAGES_PREFIX', 'pages' );

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
                ],
            ],
            'order' => 95,
        ]
    ];
}
*/

/*
 * đăng ký taxonomy riêng
 */
/*
register_taxonomy( 'custom_taxonomy', [
    'name' => 'Custom name',
    'set_parent' => true,
    'slug' => '',
] );
*/

/*
 * đăng ký post type riêng
 */
/*
register_post_type( 'custom_post_type', [
    'name' => 'Custom name'
] );
*/