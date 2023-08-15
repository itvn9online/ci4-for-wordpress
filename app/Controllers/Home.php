<?php

namespace App\Controllers;

// Libraries
use App\Libraries\DeletedStatus;
use App\Libraries\TaxonomyType;
use App\Libraries\PostType;

//
class Home extends Posts
{
    public function __construct()
    {
        parent::__construct();
    }

    /*
     * default page
     */
    public function index($id = 0, $slug = '')
    {
        // thử xem có tham số p không -> có thì có thể là shortlink
        $post_id = $this->MY_get('p', 0);
        // dẫn tới trang post mặc định
        //if ( isset( $_GET[ 'p' ], $_GET[ 'post_type' ] ) ) {
        if ($post_id > 0) {
            //die(__CLASS__ . ':' . __LINE__);
            return $this->showPostDetails($post_id, $this->MY_get('post_type', ''));
        }
        //
        else if (isset($_GET['cat'], $_GET['taxonomy'])) {
            return $this->autoCategory();
        }
        // dẫn tới trang chủ
        return $this->portal();
    }

    /*
     * home page
     */
    protected function portal($custom_data = [])
    {
        $cache_key = 'home';
        $cache_value = $this->MY_cache($cache_key);
        // Will get the cache entry named 'my_foo'
        //var_dump( $cache_value );
        // có thì in ra cache là được
        //if ( $_SERVER[ 'REQUEST_METHOD' ] == 'GET' && $cache_value !== NULL ) {
        if ($this->hasFlashSession() === false && $cache_value !== NULL) {
            return $this->show_cache($cache_value);
        }
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;

        //
        //print_r( $this->getconfig );
        $getconfig = $this->getconfig;

        // dữ liệu có cấu trúc cho trang chủ
        $schema_person = [
            '@context' => 'http://schema.org',
            '@type' => 'Person',
            "name" => $this->getconfig->name,
            'alternateName' => $_SERVER['HTTP_HOST'],
            "url" => base_url(),
            "sameAs" => $this->base_model->sameAsSchema([
                $this->getconfig->facebook,
                $this->getconfig->google,
                $this->getconfig->linkin,
                $this->getconfig->youtube,
                $this->getconfig->tiktok,
                (!empty($this->getconfig->zalo) ? 'https://zalo.me/' . $this->getconfig->zalo : ''),
                $this->getconfig->registeronline,
                $this->getconfig->notificationbct
            ]),
            "contactPoint" => [
                '@type' => 'ContactPoint',
                'telephone' => $this->getconfig->phone,
                'contactType' => 'Customer support',
                'areaServed' => strtoupper($this->lang_key),
                'availableLanguage' => [[
                    "@type" => "Language",
                    "name" => "Vietnamese"
                ], [
                    "@type" => "Language",
                    "name" => "English"
                ], [
                    "@type" => "Language",
                    "name" => "German"
                ], [
                    "@type" => "Language",
                    "name" => "Chinese"
                ], [
                    "@type" => "Language",
                    "name" => "Japanese"
                ]],
            ],
        ];
        if (!empty($this->getconfig->company_name)) {
            $schema_person['@type'] = 'Organization';
            $schema_person['name'] = $this->getconfig->company_name;
            $schema_person['logo'] = strpos($this->getconfig->logo, '//') === false ? base_url() . ltrim($this->getconfig->logo, '/') : $this->getconfig->logo;
        }

        //
        $dynamic_schema = [
            $schema_person
        ];

        // nếu có phần fake review
        //if ($this->getconfig->home_rating_value > 0 && $this->getconfig->home_rating_count > 0 && $this->getconfig->home_review_count) {
        //if (!empty($this->getconfig->home_rating_value) && $this->getconfig->home_rating_value > 0) {
        if (!empty($this->getconfig->home_rating_value)) {
            $dynamic_schema[] = [
                '@context' => 'http://schema.org',
                '@type' => 'CreativeWorkSeries',
                'aggregateRating' => [
                    "@type" => "AggregateRating",
                    "ratingValue" => $this->getconfig->home_rating_value,
                    "bestRating" => 5,
                    "ratingCount" => $this->getconfig->home_rating_count,
                    "reviewCount" => $this->getconfig->home_review_count
                ]
            ];
        }

        //
        $seo = $this->base_model->default_seo($getconfig->title, $this->getClassName(__CLASS__), [
            'index' => 'on',
            'canonical' => DYNAMIC_BASE_URL,
            'description' => $getconfig->description,
            'keyword' => $getconfig->keyword,
            'name' => $getconfig->name,
            'dynamic_schema' => $this->base_model->dynamicSchema($dynamic_schema),
        ]);

        //
        $this->teamplate['main'] = view(
            'home_view',
            array(
                'seo' => $seo,
                'breadcrumb' => '',
                //'cateByLang' => $cateByLang,
                //'serviceByLang' => $serviceByLang,
                'custom_data' => $custom_data,
            )
        );
        //print_r( $this->teamplate );

        // nếu có flash session -> trả về view luôn
        if ($this->hasFlashSession() === true) {
            return view('layout_view', $this->teamplate);
        }
        // còn không sẽ tiến hành lưu cache
        $cache_value = view('layout_view', $this->teamplate);

        $cache_save = $this->MY_cache($cache_key, $cache_value . '<!-- Served from: ' . __FUNCTION__ . ' -->');
        //var_dump( $cache_save );

        //
        return $cache_value;
    }

    public function checkurl($slug, $set_page = '', $page_num = 1)
    {
        $result = $this->before_checkurl($slug, $set_page, $page_num);
        if ($result !== false) {
            return $result;
        }
        return $this->page404('ERROR ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! Không xác định được danh mục bài viết...');
    }

    // hàm này sẽ tìm danh mục hoặc page theo url (tìm khi web không sử dụng prefix cho danh mục hoặc page)
    protected function before_checkurl($slug, $set_page = '', $page_num = 1)
    {
        if ($slug == '') {
            die('404 slug error!');
        }
        //echo $set_page . ' <br>' . PHP_EOL;
        //echo $page_num . ' <br>' . PHP_EOL;

        //
        //echo WGR_CATEGORY_PREFIX . ' <br>' . PHP_EOL;
        //echo CATEGORY_BASE_URL . ' <br>' . PHP_EOL;
        //echo WGR_PAGES_PREFIX . ' <br>' . PHP_EOL;
        //echo WGR_CATEGORY_PERMALINK . ' <br>' . PHP_EOL;
        //echo WGR_TAXONOMY_PERMALINK . ' <br>' . PHP_EOL;
        //echo WGR_POST_PERMALINK . ' <br>' . PHP_EOL;
        //echo WGR_PRODS_PERMALINK . ' <br>' . PHP_EOL;
        //echo WGR_PROD_PERMALINK . ' <br>' . PHP_EOL;
        //echo WGR_PAGE_PERMALINK . ' <br>' . PHP_EOL;
        //echo WGR_POSTS_PERMALINK . ' <br>' . PHP_EOL;

        // nếu taxonomy nào sử dụng slug thì cho vào danh sách tìm URL trong trang chủ
        $where_taxonomy_in = [];
        if (WGR_CATEGORY_PERMALINK == '%slug%') {
            $where_taxonomy_in[] = TaxonomyType::POSTS;
        }
        if (WGR_PRODS_PERMALINK == '%slug%') {
            $where_taxonomy_in[] = TaxonomyType::PROD_CATS;
        }

        // -> kiểm tra theo category
        if (!empty($where_taxonomy_in)) {
            $data = $this->term_model->get_taxonomy(
                [
                    // các kiểu điều kiện where
                    'slug' => $slug,
                    'is_deleted' => DeletedStatus::FOR_DEFAULT,
                    'lang_key' => $this->lang_key,
                    //'taxonomy' => TaxonomyType::POSTS
                ],
                [
                    'where_in' => [
                        'taxonomy' => $where_taxonomy_in,
                    ]
                ]
            );
            //print_r($data);
        } else {
            $data = [];
        }
        //die(__CLASS__ . ':' . __LINE__);

        // có -> ưu tiên category
        if (!empty($data)) {
            // vào đây thì bắt buộc phải không có category prefix
            /*
            if (WGR_CATEGORY_PREFIX != '') {
                // -> có thì chuyển hướng tới link chính ngay
                $this->MY_redirect($this->term_model->get_full_permalink($data), 301);
            }
            */

            //
            $tax_to_post_type = [
                TaxonomyType::POSTS => PostType::POST,
                TaxonomyType::PROD_CATS => PostType::PROD,
            ];
            //print_r($tax_to_post_type);

            //
            return $this->category($data, isset($tax_to_post_type[$data['taxonomy']]) ? $tax_to_post_type[$data['taxonomy']] : PostType::POST, $data['taxonomy'], $data['taxonomy'] . '_view', [
                'page_num' => $page_num,
            ]);
        }
        // -> nếu không có -> thử tìm theo trang
        else {
            $where_post_type_in = [];
            if (WGR_POST_PERMALINK == '%post_name%') {
                $where_post_type_in[] = PostType::POST;
            }
            if (WGR_PROD_PERMALINK == '%post_name%') {
                $where_post_type_in[] = PostType::PROD;
            }
            if (WGR_PAGE_PERMALINK == '%post_name%') {
                $where_post_type_in[] = PostType::PAGE;
            }
            //echo 'check page <br>' . PHP_EOL;

            //
            if (!empty($where_post_type_in)) {
                $data = $this->post_model->select_public_post(0, [
                    'post_name' => $slug,
                    //'post_type' => PostType::PAGE,
                ], '*', [
                    'where_in' => [
                        'post_type' => $where_post_type_in
                    ],
                ]);
                if (!empty($data)) {
                    //print_r( $data );

                    // vào đây thì bắt buộc phải không có page prefix
                    /*
                if (WGR_PAGES_PREFIX != '') {
                    // -> có thì chuyển hướng tới link chính ngay
                    $this->MY_redirect($this->post_model->get_full_permalink($data), 301);
                }
                */

                    // phân bổ view dựa theo post type
                    if ($data['post_type'] == PostType::PAGE) {
                        return $this->pageDetail($data);
                    } else {
                        if ($data['post_type'] == PostType::PROD) {
                            //print_r( $data );

                            // chỉnh lại thông số 1 chút -> do mặc định nó đang kết nối tới post
                            //$this->post_type = PostType::BLOG;
                            $this->post_type = PostType::PROD;
                            //$this->taxonomy = TaxonomyType::BLOGS;
                            $this->taxonomy = TaxonomyType::PROD_CATS;
                            $this->file_view = $this->post_type . '_view';
                        }

                        //
                        return $this->post_details($data['ID'], $data['post_name'], $data);
                    }
                }
            }
        }

        // đến đây mà không có dữ liệu thì trả về false -> các function kế thừa sẽ tùy chỉnh trang 404
        return false;
    }

    /*
    protected function autoDetails() {
    return $this->showPostDetails( $this->MY_get( 'p', 0 ), $this->MY_get( 'post_type', '' ) );
    }
    */
    protected function showPostDetails($id, $post_type = '', $slug = '')
    {
        //echo $id . '<br>' . PHP_EOL;
        //echo $post_type . '<br>' . PHP_EOL;

        //
        if (!is_numeric($id) || $id <= 0) {
            die('ERROR! id? ' . __CLASS__ . ':' . __LINE__);
        }

        //
        $cache_key = $this->post_model->key_cache($id);
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
        $cache_value = $this->MY_cache($cache_key);
        // Will get the cache entry named 'my_foo'
        //var_dump( $cache_value );
        // có thì in ra cache là được
        if ($cache_value !== NULL) {
            return $this->show_cache($cache_value);
        }
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;

        //
        $in_cache = __FUNCTION__;
        $data = $this->post_model->the_cache($id, $in_cache);
        //var_dump($data);
        if ($data === NULL) {
            // lấy post theo ID, không lọc theo post type -> vì nhiều nơi cần dùng đến
            $data = $this->base_model->select(
                '*',
                'posts',
                array(
                    // các kiểu điều kiện where
                    'ID' => $id,
                    // bỏ qua where post_type để còn tạo shortlink
                    //'post_type' => $post_type,
                    //'post_status' => PostType::PUBLICITY
                ),
                array(
                    // hiển thị mã SQL để check
                    //'show_query' => 1,
                    // trả về câu query để sử dụng cho mục đích khác
                    //'get_query' => 1,
                    //'offset' => 2,
                    'limit' => 1
                )
            );
            //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
            //die(__CLASS__ . ':' . __LINE__);

            //
            if (!empty($data)) {
                if ($data['post_status'] != PostType::PUBLICITY) {
                    return $this->page404('ERROR ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! Bạn không có quyền xem bài viết này...');
                    //die(__CLASS__ . ':' . __LINE__);
                }

                // lấy meta của post này
                //$data[ 'post_meta' ] = $this->post_model->arr_meta_post( $data[ 'ID' ] );
                $data = $this->post_model->the_meta_post($data);
                //print_r( $data );
                //die( __CLASS__ . ':' . __LINE__ );
            }

            //
            $this->post_model->the_cache($id, $in_cache, $data);
        }
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
        //print_r($data);
        //die(__CLASS__ . ':' . __LINE__);

        //
        if (!empty($data)) {
            //die(__CLASS__ . ':' . __LINE__);
            // kiểm tra lại slug -> nếu sai thì redirect 301 qua url mới
            $this->post_model->check_canonical($slug, $data);

            // nếu đây là shortlink
            if ($post_type == '') {
                $this->MY_redirect($this->post_model->get_full_permalink($data), 301);
            }

            // dùng view theo post type (ngoại trừ post type ADS)
            if ($data['post_type'] != PostType::ADS) {
                return $this->pageDetail($data, $data['post_type'] . '_view');
            }
        }
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;

        //
        return $this->page404('ERROR ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! Không xác định được dữ liệu bài viết...');
    }

    protected function pageDetail($data, $file_view = 'page_view')
    {
        // kiểm tra quyền truy cập chi tiết 1 post
        if ($this->post_permission($data) !== true) {
            return $this->page404($this->post_permission($data));
        }

        // xem có file view tương ứng không
        if (!file_exists(VIEWS_PATH . $file_view . '.php')) {
            // không có thì hiển thị lỗi luôn
            return $this->page404('ERROR (' . $file_view . ') ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! Bạn không có quyền xem thông tin này...');
        }

        //
        $cache_key = $this->post_model->key_cache($data['ID']);
        $cache_value = $this->MY_cache($cache_key);
        // Will get the cache entry named 'my_foo'
        //var_dump($cache_value);
        // có thì in ra cache là được
        //if ( $_SERVER[ 'REQUEST_METHOD' ] == 'GET' && $cache_value !== NULL ) {
        if ($this->hasFlashSession() === false && $cache_value !== NULL) {
            return $this->show_cache($cache_value);
        }

        // update lượt xem -> daidq (2021-12-14): chuyển phần update này qua view, ai thích dùng thì kích hoạt cho nó nhẹ
        //$this->post_model->update_views( $data[ 'ID' ] );

        //
        $data['post_content'] = $this->replace_content($data['post_content']);
        //print_r($data);
        $data = $this->post_model->metaTitleDescription($data);

        // lấy thông tin danh mục để tạo breadcrumb
        $cats = [];
        if (isset($data['post_meta']['post_category'])) {
            $post_category = explode(',', $data['post_meta']['post_category']);
            $post_category = $post_category[0];

            //
            if ($post_category > 0) {
                $in_cache = __FUNCTION__;
                $cats = $this->term_model->the_cache($post_category, $in_cache);
                if ($cats === NULL) {
                    $cats = $this->base_model->select('*', WGR_TERM_VIEW, [
                        'term_id' => $post_category,
                    ], [
                        // hiển thị mã SQL để check
                        //'show_query' => 1,
                        // trả về câu query để sử dụng cho mục đích khác
                        //'get_query' => 1,
                        //'offset' => 0,
                        'limit' => 1
                    ]);

                    //
                    $this->term_model->the_cache($post_category, $in_cache, $cats);
                }
                //print_r( $cats );

                //
                if (!empty($cats)) {
                    $this->create_term_breadcrumb($cats);
                }
            }
        }

        // page view mặc định
        $page_template = '';
        // nếu có dùng template riêng -> dùng luôn
        if (isset($data['post_meta']['page_template']) && $data['post_meta']['page_template'] != '') {
            $page_template = $data['post_meta']['page_template'];
        }

        // nếu có post cha -> lấy cả thông tin post cha
        $parent_data = [];
        if ($data['post_parent'] > 0) {
            $parent_data = $this->base_model->select(
                '*',
                'posts',
                array(
                    // các kiểu điều kiện where
                    'ID' => $data['post_parent'],
                    'post_status' => PostType::PUBLICITY
                ),
                array(
                    // hiển thị mã SQL để check
                    //'show_query' => 1,
                    // trả về câu query để sử dụng cho mục đích khác
                    //'get_query' => 1,
                    //'offset' => 2,
                    'limit' => 1
                )
            );
            //print_r( $parent_data );

            //
            if (!empty($parent_data)) {
                $this->create_breadcrumb($parent_data['post_title'], $this->post_model->get_full_permalink($parent_data));
            }
            // cha bị khóa thì cũng trả về 404 luôn
            else {
                return $this->page404('ERROR ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! Bài viết bị KHÓA do đang liên kết tới một bài viết khác đã bị KHÓA...');
            }
        }

        // nếu có lệnh redirect do sai URL
        if (isset($_GET['canonical'])) {
            //echo __CLASS__ . ':' . __LINE__;
            // xóa permalink để URL được update lại
            //$data['post_permalink'] = '';
            //$data['updated_permalink'] = 0;
            $full_link = $this->post_model->update_post_permalink($data, DYNAMIC_BASE_URL);
        } else {
            $full_link = $this->post_model->get_full_permalink($data);
        }
        //print_r($data);
        //echo $full_link;

        //
        $this->create_breadcrumb($data['post_title'], $full_link);
        $seo = $this->base_model->post_seo($data, $full_link);

        //
        $structured_data = $this->structuredGetData($data);
        //print_r($structured_data);
        $structured_data = $this->post_model->structuredArticleData($data, $structured_data);
        if (!isset($seo['dynamic_schema'])) {
            $seo['dynamic_schema'] = '';
        }
        $seo['dynamic_schema'] .= $structured_data;

        //
        $this->current_pid = $data['ID'];

        // -> views
        $this->teamplate['breadcrumb'] = view(
            'breadcrumb_view',
            array(
                'breadcrumb' => $this->breadcrumb
            )
        );

        $this->teamplate['main'] = view(
            $file_view,
            array(
                'seo' => $seo,
                'page_template' => $page_template,
                'data' => $data,
                'current_pid' => $this->current_pid,
                'parent_data' => $parent_data,
            )
        );

        // nếu có flash session -> trả về view luôn
        if ($this->hasFlashSession() === true) {
            return view('layout_view', $this->teamplate);
        }
        // còn không sẽ tiến hành lưu cache
        $cache_value = view('layout_view', $this->teamplate);

        // chỉ lưu cache nếu không có page template
        //if ( $page_template == '' ) {
        $cache_save = $this->MY_cache($cache_key, $cache_value . '<!-- Served from: ' . __FUNCTION__ . ' -->');
        //var_dump( $cache_save );
        //}

        //
        return $cache_value;
    }

    protected function autoCategory()
    {
        $term_id = $this->MY_get('cat', 0);
        //echo $term_id . '<br>' . PHP_EOL;

        //
        $taxonomy_type = $this->MY_get('taxonomy', '');
        //echo $taxonomy_type . '<br>' . PHP_EOL;

        //
        $page_num = $this->MY_get('page_num', 1);
        //echo $page_num . '<br>' . PHP_EOL;

        //
        return $this->showCategory($term_id, $taxonomy_type, $page_num);
    }

    //
    protected function showCategory($term_id, $taxonomy_type, $page_num = 1, $slug = '')
    {
        $cache_key = $this->term_model->key_cache($term_id) . 'page' . $page_num;
        $cache_value = $this->MY_cache($cache_key);
        // có thì in ra cache là được
        if ($cache_value !== NULL) {
            return $this->show_cache($cache_value);
        }

        //
        $in_cache = __FUNCTION__;
        $data = $this->term_model->the_cache($term_id, $in_cache);
        if ($data === NULL) {
            $data = $this->term_model->get_taxonomy(
                array(
                    // các kiểu điều kiện where
                    'term_id' => $term_id,
                    'is_deleted' => DeletedStatus::FOR_DEFAULT,
                    'lang_key' => $this->lang_key,
                    'taxonomy' => $taxonomy_type
                )
            );

            //
            $this->term_model->update_count_post_in_term($data);

            //
            $this->term_model->the_cache($term_id, $in_cache, $data);
        }
        //print_r($data);
        //die(__CLASS__ . ':' . __LINE__);

        // không có dữ liệu -> báo 404 luôn
        if (empty($data)) {
            return $this->page404('ERROR ' . __FUNCTION__ . ':' . __LINE__ . '! Không xác định được danh mục bài viết...', $cache_key);
        } else if ($data['count'] <= 0) {
            return $this->page404('ERROR ' . __FUNCTION__ . ':' . __LINE__ . '! Không tìm thấy bài viết trong danh mục...', $cache_key);
        }

        // kiểm tra lại slug -> nếu sai thì redirect 301 qua url mới
        $this->term_model->check_canonical($slug, $data);

        // có -> lấy bài viết trong nhóm
        // xem nhóm này có nhóm con không
        $in_cache = __FUNCTION__ . '-parent';
        $child_data = $this->term_model->the_cache($term_id, $in_cache);
        if ($child_data === NULL) {
            $child_data = $this->term_model->get_taxonomy(
                [
                    // các kiểu điều kiện where
                    'parent' => $term_id,
                    'is_deleted' => DeletedStatus::FOR_DEFAULT,
                    'lang_key' => $this->lang_key,
                    'taxonomy' =>
                    $taxonomy_type
                ],
                [
                    'limit' => 10,
                    'select_col' => 'term_id',
                ]
            );

            //
            $this->term_model->the_cache($term_id, $in_cache, $child_data);
        }
        //print_r($child_data);

        //
        $where = [
            'posts.post_status' => PostType::PUBLICITY,
            'posts.lang_key' => $this->lang_key
        ];

        //
        $filter = [
            'join' => [
                'term_relationships' => 'term_relationships.object_id = posts.ID',
                'term_taxonomy' => 'term_relationships.term_taxonomy_id = term_taxonomy.term_taxonomy_id',
            ],
            'order_by' => [
                'posts.ID' => 'DESC',
            ],
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 0,
            'limit' => 1
        ];

        // nếu không có cha -> chỉ cần lấy theo ID nhóm hiện tại là được
        if (empty($child_data)) {
            $where['term_taxonomy.term_id'] = $data['term_id'];
        }
        // nếu có -> lấy theo cả cha và con
        else {
            $where_in = [];
            foreach ($child_data as $v) {
                $where_in[] = $v['term_id'];
            }

            //
            $filter['where_in'] = [
                'term_taxonomy.term_id' => $where_in
            ];

            //
            $data['where_in'] = $where_in;
        }
        //print_r( $data );

        // xác định post type dựa theo taxonomy type
        $get_post_type = $this->base_model->select('post_type', 'posts', $where, $filter);
        //print_r($get_post_type);

        // tìm được post tương ứng thì mới show category ra
        if (!empty($get_post_type)) {
            //die(__CLASS__ . ':' . __LINE__);
            return $this->category($data, $get_post_type['post_type'], $taxonomy_type, $taxonomy_type . '_view', [
                'page_num' => $page_num,
                'cache_key' => $cache_key,
            ]);
        }

        //
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
        // cập nhật lại tổng số bài viết cho term - để sau nếu có tính năng lấy theo nhóm thì nó sẽ không xuất hiện nữa
        $this->base_model->update_multiple($this->term_model->taxTable, [
            'count' => 0
        ], [
            'term_taxonomy_id' => $term_id,
            'term_id' => $term_id,
        ], [
            'debug_backtrace' => debug_backtrace()[1]['function']
        ]);
    }
}
