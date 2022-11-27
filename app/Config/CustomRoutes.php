<?php
/*
 * Routes mặc định của code gốc, nó bao gốm những gì cần thiết nhất cho một website cơ bản
 */

// install
$routes->get('/install', 'Installs::index');

// admin
// tạo đường dẫn admin tránh đường dẫn mặc định
$routes->get(CUSTOM_ADMIN_URI, 'Admin\Dashboard::index');

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index');

//$routes->get( 'users', 'Users::index' );
//$routes->get( 'guest', 'Guest::index' );

//
$routes->get('search', 'Search::index');


// sitemap
$routes->get('sitemap', 'Sitemap::index');
$routes->get('sitemap.xml', 'Sitemap::index');
$routes->get('sitemap/(:segment)', 'Sitemap::index/$1');
$routes->get('sitemap/(:segment)/page/(:num)', 'Sitemap::index/$1/page/$2');


// blog
$routes->get('blogs/(:segment)', 'Blogs::blogs_list/$1');
$routes->get('blogs/(:segment)/page/(:num)', 'Blogs::blogs_list/$1/page/$2');
$routes->get('blog-(:num)/(:segment)', 'Blogs::blog_details/$1/$2');

// post
$routes->get('(:num)/(:segment)', 'Posts::post_details/$1/$2');

// page -> có base slug cho page thì dùng loại base fix cứng này
if (WGR_PAGES_PREFIX != '') {
    //$routes->add( WGR_PAGES_PREFIX . '/(:segment)', 'Pages::get_page/$1' );
    $routes->add(PAGE_BASE_URL . '(:segment)', 'Pages::get_page/$1');
}

// custom post type
$routes->match(['get', 'post'], 'p/(:segment)/(:num)/(:segment)', 'P::custom_post_type/$1/$2/$3');

// custom taxonomy
$routes->get('c/(:segment)/(:num)/(:segment)', 'C::custom_taxonomy/$1/$2/$3');
$routes->get('c/(:segment)/(:num)/(:segment)/page/(:num)', 'C::custom_taxonomy/$1/$2/$3/page/$4');

// category -> có base slug cho category thì dùng loại base fix cứng này
if (WGR_CATEGORY_PREFIX != '') {
    $routes->get(WGR_CATEGORY_PREFIX . '/(:segment)', 'Category::category_list/$1');
    /*
    $routes->get( WGR_CATEGORY_PREFIX . '/(:segment)/page/(:num)', 'Category::category_list/$1/page/$2' );
    }
    // không có thì mới sử dụng loại auto category -> hỗ trợ phân trang
    else {
    $routes->get( '(:segment)/page/(:num)', 'Category::category_list/$1/page/$2' );
    */
}
$routes->get(CATEGORY_BASE_URL . '(:segment)/page/(:num)', 'Category::category_list/$1/page/$2');

// auto category -> dùng nặng -> hạn chế sử dụng
//$routes->get( '(:segment)/page/(:num)', 'Home::checkurl/$1/page/$2' );

// hỗ trợ auto category và page -> sau đó sẽ so sánh URL, nếu khác biệt thì sẽ chuyển về link gốc
$routes->add('(:segment)', 'Home::checkurl/$1');

//
//$routes->addPlaceholder( 'checkurl', '[0-9a-z]{1}-(:segment)' );