<?php
/*
 * Routes mặc định của code gốc, nó bao gốm những gì cần thiết nhất cho một website cơ bản
 */
// We get a performance increase by specifying the default
// route since we don't have to scan directories.

/*
 * các routes sẽ được khai báo dạng mảng như này, mục đích là để dễ custom routes cho từng website hơn
 */
$arrCustomRoutes = [
    'get' => [
        // install
        '/install' => 'Installs::index',
        // tạo đường dẫn admin tránh đường dẫn mặc định: VD /admin
        CUSTOM_ADMIN_URI => 'Admin\Dashboard::index',
        '/' => 'Home::index',
        //'users' => 'Users::index',
        //'guest' => 'Guest::index',
        'search' => 'Search::index',
        // sitemap
        'sitemap' => 'Sitemap::index',
        'sitemap.xml' => 'Sitemap::index',
        'sitemap/(:segment)' => 'Sitemap::index/$1',
        'sitemap/(:segment)/page/(:num)' => 'Sitemap::index/$1/page/$2',
        // blog
        'blogs/(:segment)' => 'Blogs::blogs_list/$1',
        'blogs/(:segment)/page/(:num)' => 'Blogs::blogs_list/$1/page/$2',
        'blog-(:num)/(:segment)' => 'Blogs::blog_details/$1/$2',
        // post
        '(:num)/(:segment)' => 'Posts::post_details/$1/$2',
        // custom taxonomy
        'c/(:segment)/(:num)/(:segment)' => 'C::custom_taxonomy/$1/$2/$3',
        'c/(:segment)/(:num)/(:segment)/page/(:num)' => 'C::custom_taxonomy/$1/$2/$3/page/$4',
    ],
    'add' => [],
    'match' => [
        // custom post type
        'p/(:segment)/(:num)/(:segment)' => 'P::custom_post_type/$1/$2/$3',
    ]
];

// category -> có base slug cho category thì dùng loại base fix cứng này
if (WGR_CATEGORY_PREFIX != '') {
    $arrCustomRoutes['get'][WGR_CATEGORY_PREFIX . '/(:segment)'] = 'Category::category_list/$1';
    /*
    $arrCustomRoutes['get'][WGR_CATEGORY_PREFIX . '/(:segment)/page/(:num)'] = 'Category::category_list/$1/page/$2';
    }
    // không có thì mới sử dụng loại auto category -> hỗ trợ phân trang
    else {
    $arrCustomRoutes['get']['(:segment)/page/(:num)'] = 'Category::category_list/$1/page/$2';
    */
}
$arrCustomRoutes['get'][CATEGORY_BASE_URL . '(:segment)/page/(:num)'] = 'Category::category_list/$1/page/$2';

// auto category -> dùng nặng -> hạn chế sử dụng
//$arrCustomRoutes['get']['(:segment)/page/(:num)'] = 'Home::checkurl/$1/page/$2';

// hỗ trợ auto category và page -> sau đó sẽ so sánh URL, nếu khác biệt thì sẽ chuyển về link gốc
$arrCustomRoutes['get']['(:segment)'] = 'Home::checkurl/$1';

// page -> có base slug cho page thì dùng loại base fix cứng này
if (WGR_PAGES_PREFIX != '') {
    //$arrCustomRoutes['add'][WGR_PAGES_PREFIX . '/(:segment)'] = 'Pages::get_page/$1';
    $arrCustomRoutes['add'][PAGE_BASE_URL . '(:segment)'] = 'Pages::get_page/$1';
}


/*
 * Gọi tới file chứa routes tùy chỉnh theo mỗi website (nếu có)
 */
include __DIR__ . '/CustomRoutes.php';


/*
 * Chạy vòng lặp để add routes vào hệ thống
 */
//print_r($arrCustomRoutes);
foreach ($arrCustomRoutes as $k => $v) {
    //print_r($v);
    if ($k == 'add') {
        foreach ($v as $k2 => $v2) {
            $routes->add($k2, $v2);
        }
    } else if ($k == 'match') {
        foreach ($v as $k2 => $v2) {
            $routes->match(['get', 'post'], $k2, $v2);
        }
    } else {
        foreach ($v as $k2 => $v2) {
            $routes->get($k2, $v2);
        }
    }
}

//
//$routes->addPlaceholder( 'checkurl', '[0-9a-z]{1}-(:segment)' );