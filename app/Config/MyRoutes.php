<?php
/*
 * Routes mặc định của code gốc, nó bao gốm những gì cần thiết nhất cho một website cơ bản
 */


/*
* Các routes không hỗ trợ đa ngôn ngữ
*/
// install
$routes->get('/install', 'Installs::index');

// tạo đường dẫn admin tránh đường dẫn mặc định
$routes->get('/' . CUSTOM_ADMIN_URI, 'Admin\Dashboard::index');


/*
* Các routes này là của trang khách -> có hỗ trợ dùng prefix để xác định ngôn ngữ hiển thị
* xác định prefix cho routes
*/
//print_r(SITE_LANGUAGE_SUPPORT);
//echo $_SERVER['REQUEST_URI'];

// nếu web này có dùng đa ngôn ngữ kiểu sub-folder thì sẽ tiến hành tạo prefix
if (SITE_LANGUAGE_SUB_FOLDER === true) {
    $arr_prefix_routes = SITE_LANGUAGE_SUPPORT;
}
// nếu không -> giả lập 1 mảng để làm sao cho foreach chạy 1 lần rồi thôi, và giá trị prefix được thiết lập sẽ là /
else {
    $arr_prefix_routes = [
        [
            'value' => '/',
        ]
    ];
}
//print_r($arr_prefix_routes);

// chạy vòng lặp để add routes cho từng ngôn ngữ
foreach ($arr_prefix_routes as $v) {
    // với ngôn ngữ mặc định
    if ($v['value'] == SITE_LANGUAGE_DEFAULT) {
        $routes_prefix = '/';
    }
    // với các ngôn ngữ khác
    else {
        $routes_prefix = $v['value'];
    }
    //echo $routes_prefix;

    // dùng group để nhóm các routes lại với nhau
    $routes->group($routes_prefix, static function ($routes) {
        // Routes tùy chỉnh từ các theme con
        include __DIR__ . '/CustomRoutes.php';


        // We get a performance increase by specifying the default
        // route since we don't have to scan directories.
        $routes->get('', 'Home::index');

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
        /*
        $routes->get('blogs/(:segment)', 'Blogs::blogs_list/$1');
        $routes->get('blogs/(:segment)/page/(:num)', 'Blogs::blogs_list/$1/page/$2');
        //
        //$routes->get('blog_tags/(:segment)', 'Blogtags::blogs_list/$1');
        //$routes->get('blog_tags/(:segment)/page/(:num)', 'Blogtags::blogs_list/$1/page/$2');
        //
        $routes->get('blog-(:num)/(:segment)', 'Blogs::blog_details/$1/$2');
*/

        // product
        //echo WGR_PRODS_PERMALINK . ' <br>' . PHP_EOL;
        if (WGR_PRODS_PERMALINK != '%slug%') {
            $a = str_replace('%slug%', '(:segment)', WGR_PRODS_PERMALINK);
            $routes->get($a, 'Products::products_list/$1');
            $routes->get($a . '/page/(:num)', 'Products::products_list/$1/page/$2');
        }
        //
        //$routes->get('product_tag/(:segment)', 'Producttags::products_list/$1');
        //$routes->get('product_tag/(:segment)/page/(:num)', 'Producttags::products_list/$1/page/$2');
        // product
        //echo WGR_PROD_PERMALINK . ' <br>' . PHP_EOL;
        if (WGR_PROD_PERMALINK != '%post_name%') {
            $a = str_replace('%post_name%', '(:segment)', WGR_PROD_PERMALINK);
            $a = str_replace('%ID%', '(:num)', $a);
            $routes->get($a, 'Products::product_details/$1/$2');
        }

        // post
        //echo WGR_POST_PERMALINK . ' <br>' . PHP_EOL;
        if (WGR_POST_PERMALINK != '%post_name%') {
            $a = str_replace('%post_name%', '(:segment)', WGR_POST_PERMALINK);
            $a = str_replace('%ID%', '(:num)', $a);
            $routes->get($a, 'Posts::post_details/$1/$2');
        }

        // page
        //echo WGR_PAGE_PERMALINK . ' <br>' . PHP_EOL;
        if (WGR_PAGE_PERMALINK != '%post_name%') {
            $a = str_replace('%post_name%', '(:segment)', WGR_PAGE_PERMALINK);
            //$a = str_replace('%ID%', '(:num)', $a);
            $routes->get($a, 'Pages::get_page/$1');
        }

        // custom post type
        $routes->match(['get', 'post'], 'p/(:segment)/(:num)/(:segment)', 'P::custom_post_type/$1/$2/$3');

        // custom taxonomy
        $routes->get('c/(:segment)/(:num)/(:segment)', 'C::custom_taxonomy/$1/$2/$3');
        $routes->get('c/(:segment)/(:num)/(:segment)/page/(:num)', 'C::custom_taxonomy/$1/$2/$3/page/$4');

        // Category
        //echo WGR_CATEGORY_PERMALINK . ' <br>' . PHP_EOL;
        if (WGR_CATEGORY_PERMALINK != '%slug%') {
            $a = str_replace('%slug%', '(:segment)', WGR_CATEGORY_PERMALINK);
            $routes->get($a, 'Category::category_list/$1');
            $routes->get($a . '/page/(:num)', 'Category::category_list/$1/page/$2');
        }

        // category -> có base slug cho category thì dùng loại base fix cứng này
        //if (WGR_CATEGORY_PREFIX != '') {
        //$routes->get(WGR_CATEGORY_PREFIX . '/(:segment)', 'Category::category_list/$1');
        /*
            $routes->get(WGR_CATEGORY_PREFIX . '/(:segment)/page/(:num)', 'Category::category_list/$1/page/$2');
        }
        // không có thì mới sử dụng loại auto category -> hỗ trợ phân trang
        else {
            $routes->get('(:segment)/page/(:num)', 'Category::category_list/$1/page/$2');
            */
        //}
        //$routes->get(CATEGORY_BASE_URL . '(:segment)/page/(:num)', 'Category::category_list/$1/page/$2');

        // auto category -> dùng nặng -> hạn chế sử dụng
        //$routes->get( '(:segment)/page/(:num)', 'Home::checkurl/$1/page/$2' );

        // hỗ trợ auto category và page -> sau đó sẽ so sánh URL, nếu khác biệt thì sẽ chuyển về link gốc
        $routes->add('(:segment)', 'Home::checkurl/$1');
        $routes->add('(:segment)/page/(:num)', 'Home::checkurl/$1/page/$2');

        //
        //$routes->addPlaceholder( 'checkurl', '[0-9a-z]{1}-(:segment)' );
    });
}
