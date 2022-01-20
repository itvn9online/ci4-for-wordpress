<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if ( file_exists( SYSTEMPATH . 'Config/Routes.php' ) ) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace( 'App\Controllers' );
$routes->setDefaultController( 'Home' );
$routes->setDefaultMethod( 'index' );
$routes->setTranslateURIDashes( false );
$routes->set404Override();
$routes->setAutoRoute( true );

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */


//
$routes->setPrioritize();

// admin
// tạo đường dẫn admin tránh đường dẫn mặc định
$routes->get( CUSTOM_ADMIN_URI, 'Admin/Dashboard::index' );

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get( '/', 'Home::index' );

//$routes->get( 'users', 'Users::index' );
//$routes->get( 'guest', 'Guest::index' );

//
$routes->get( 'search', 'Search::index' );


// sitemap
$routes->get( 'sitemap', 'Sitemap::index' );
$routes->get( 'sitemap.xml', 'Sitemap::index' );
$routes->get( 'sitemap/(:segment)', 'Sitemap::index/$1' );
$routes->get( 'sitemap/(:segment)/page/(:num)', 'Sitemap::index/$1/page/$2' );


// blog
$routes->get( 'blogs/(:segment)', 'Blogs::blogs_list/$1' );
$routes->get( 'blogs/(:segment)/page/(:num)', 'Blogs::blogs_list/$1/page/$2' );
$routes->get( 'blog-(:num)/(:segment)', 'Blogs::blog_details/$1/$2' );

// post
$routes->get( '(:num)/(:segment)', 'Posts::post_details/$1/$2' );

// category -> có base slug cho category thì dùng loại base fix cứng này
if ( WGR_CATEGORY_PREFIX != '' ) {
    $routes->get( WGR_CATEGORY_PREFIX . '/(:segment)', 'Category::category_list/$1' );
    $routes->get( WGR_CATEGORY_PREFIX . '/(:segment)/page/(:num)', 'Category::category_list/$1/page/$2' );
}
// không có thì mới sử dụng loại auto category -> hỗ trợ phân trang
else {
    $routes->get( '(:segment)/page/(:num)', 'Category::category_list/$1/page/$2' );
}

// auto category
//$routes->get( '(:segment)/page/(:num)', 'Home::checkurl/$1/page/$2' );

// category -> có base slug cho category thì dùng loại base fix cứng này
if ( WGR_PAGES_PREFIX != '' ) {
    $routes->add( WGR_PAGES_PREFIX . '/(:segment)', 'Pages::get_page/$1' );
}
// auto page
else {
    $routes->add( '(:segment)', 'Home::checkurl/$1' );
}

//
//$routes->addPlaceholder( 'checkurl', '[0-9a-z]{1}-(:segment)' );
//$routes->add( '(:checkurl)', 'Home::checkurl/$1' );

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if ( file_exists( APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php' ) ) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}