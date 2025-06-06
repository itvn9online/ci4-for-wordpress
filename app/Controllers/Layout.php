<?php

namespace App\Controllers;

// Libraries
use App\Libraries\PostType;
use App\Libraries\TaxonomyType;

//
class Layout extends Sync
{
    //public $CI = null;

    //
    public $breadcrumb = [];
    public $getconfig = null;
    public $taxonomy_post_size = '';
    // danh sách các nhóm cha của nhóm hiện tại đang được xem
    public $taxonomy_slider = [];
    // danh sách ID nhóm của sản phẩm đang xem -> dùng để tìm các bài cùng nhóm khi xem chi tiết bài viết
    public $posts_parent_list = [];

    // với 1 số controller, sẽ không nạp cái HTML header vào, nên có thêm tham số này để không nạp header nữa
    public $preload_header = true;

    // ID của user đang đang nhập
    public $current_user_id = 0;
    // phân loại user đang đăng nhập
    public $current_user_type = 'nologin';
    // kiểu login -> dùng để css cho frontend nó dễ hơn
    public $current_user_logged = 'free-viewer';
    //
    public $current_pid = 0;
    public $current_cid = 0;
    public $current_sid = 0;
    public $breadcrumb_position = 1;
    public $session_data = null;
    public $isMobile = '';
    public $cache_key = '';
    public $cache_mobile_key = '';
    public $teamplate = [];
    public $debug_enable = false;
    // 
    public $option_model = null;
    public $lang_model = null;
    public $num_model = null;
    public $checkbox_model = null;
    public $post_model = null;
    public $menu_model = null;
    public $htmlmenu_model = null;
    public $user_model = null;

    public function __construct()
    {
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        // 
        parent::__construct();

        //echo base_url('/') . '<br>' . PHP_EOL;

        $this->option_model = new \App\Models\Option();
        $this->lang_model = new \App\Models\Lang();
        $this->num_model = new \App\Models\Num();
        $this->checkbox_model = new \App\Models\Checkbox();
        $this->post_model = new \App\Models\Post();
        $this->menu_model = new \App\Models\Menu();
        $this->htmlmenu_model = new \App\Models\Htmlmenu();
        $this->user_model = new \App\Models\User();

        //
        //$this->session = \Config\Services::session();

        //
        /*
        helper( [
        //'cookie',
        'url',
        'form',
        'security'
        ] );
        */

        /**
         * bắt đầu code
         */

        // $allurl = 'https://' . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'REQUEST_URI' ];

        // 
        $this->getconfig = $this->option_model->list_config();
        // print_r($this->getconfig);
        $this->getconfig = (object) $this->getconfig;
        // print_r($this->getconfig);
        // die(__CLASS__ . ':' . __LINE__);

        // tạo thông tin nhà xuất bản (publisher) cho phần dữ liệu có cấu trúc
        $itemprop_cache_logo = $this->base_model->scache('itemprop_logo');
        //$itemprop_cache_logo = WRITEPATH . 'itemprop-logo.txt';
        //$itemprop_cache_author = WRITEPATH . 'itemprop-author.txt';
        //if (!is_file($itemprop_cache_logo) || time() - filemtime($itemprop_cache_logo) > HOUR) {
        if ($itemprop_cache_logo === null) {
            // logo
            $structured_data = file_get_contents(VIEWS_PATH . 'html/structured-data/itemprop-logo.html');
            foreach (
                [
                    '{{web_quot_title}}' => str_replace('"', '', $this->getconfig->name),
                    '{{image}}' => DYNAMIC_BASE_URL . $this->getconfig->logo,
                    '{{trv_width_img}}' => $this->getconfig->logo_height_img,
                    '{{trv_height_img}}' => $this->getconfig->logo_width_img,
                ] as $k => $v
            ) {
                $structured_data = str_replace($k, $v, $structured_data);
            }

            //
            //$this->base_model->eb_create_file($itemprop_cache_logo, $structured_data);
            //touch($itemprop_cache_logo, time());
            $this->base_model->scache('itemprop_logo', $structured_data, HOUR);

            // author
            $structured_data = file_get_contents(VIEWS_PATH . 'html/structured-data/itemprop-author.html');
            $structured_data = str_replace('{{author_quot_title}}', str_replace('"', '', $this->getconfig->name), $structured_data);

            //
            //$this->base_model->eb_create_file($itemprop_cache_author, $structured_data);
            $this->base_model->scache('itemprop_author', $structured_data, HOUR);
        }

        //
        $this->session_data = $this->base_model->get_ses_login();
        //print_r( $this->session_data );

        // key lưu ID hiện tại của user
        if (!empty($this->session_data) && isset($this->session_data['userID']) && $this->session_data['userID'] > 0) {
            $this->current_user_id = $this->session_data['userID'];
            $this->current_user_type = $this->session_data['member_type'];
            $this->current_user_logged = 'logged-in';
        }

        //
        $this->debug_enable = (ENVIRONMENT !== 'production');
        //var_dump( $this->debug_enable );

        //
        // $this->cache_key = '';
        // $this->cache_mobile_key = '';

        //
        // $this->isMobile = '';
        // $this->teamplate = [];
        if ($this->preload_header === true) {
            //echo 'preload header <br>' . PHP_EOL;
            //$this->isMobile = $this->checkDevice( $_SERVER[ 'HTTP_USER_AGENT' ] );
            $this->isMobile = WGR_IS_MOBILE;
            //var_dump( $this->isMobile );

            //
            $this->global_header_footer();
        }
    }

    // trả về nội dung từ cache hoặc lưu cache nếu có
    protected function global_cache($key, $value = '', $time = MINI_CACHE_TIMEOUT)
    {
        $key .= $this->cache_mobile_key . '-' . $this->lang_key;

        //
        return $this->base_model->scache($key, $value, $time);
    }

    // kiểm tra session của user, nếu đang đăng nhập thì bỏ qua chế độ cache
    protected function MY_cache($key, $value = '', $time = MINI_CACHE_TIMEOUT)
    {
        // không thực thi cache đối với tài khoản đang đăng nhập
        if (MY_CACHE_HANDLER == 'disable' || $this->current_user_id > 0 || isset($_GET['set_lang'])) {
            return null;
        }

        //
        return $this->global_cache($key, $value, $time);
    }

    // hiển thị nội dung từ cache -> thêm 1 số đoạn comment HTML vào
    protected function show_cache($content, $key = '')
    {
        echo $content;

        //
        if (MY_CACHE_HANDLER == 'disable') {
            echo '<!-- Cached is disabled -->' . PHP_EOL;
        } else {
            echo '<!-- Cached by ebcache with key ' . CACHE_HOST_PREFIX . ':' . $key . PHP_EOL;
            if (MY_CACHE_HANDLER == 'file') {
                echo 'Caching using hard disk drive. Recommendations using SSD drive for your website.' . PHP_EOL;
            } else {
                echo 'How wonderful! Caching using ' . MY_CACHE_HANDLER . ' handler.' . PHP_EOL;
            }
            echo 'Compression = gzip -->';
        }

        //
        return true;
    }

    // chỉ gọi đến chức năng nạp header, footer khi cần hiển thị
    protected function global_header_footer()
    {
        //
        //$response = \Config\Services::response();
        //$response->removeHeader('Cache-Control');
        //$response->setHeader('Cache-Control', 'max-age=120')->appendHeader('Cache-Control', 'must-revalidate');
        //$response->setHeader('Cache-Control', 'max-age=120');

        //print_r($this->getconfig);
        $this->teamplate['header'] = view(
            'header_view',
            array(
                // các model dùng chung thì cho vào header để sau sử dụng luôn
                'base_model' => $this->base_model,
                'menu_model' => $this->menu_model,
                'htmlmenu_model' => $this->htmlmenu_model,
                'option_model' => $this->option_model,
                'post_model' => $this->post_model,
                'term_model' => $this->term_model,
                'lang_model' => $this->lang_model,
                'num_model' => $this->num_model,
                'checkbox_model' => $this->checkbox_model,
                'user_model' => $this->user_model,
                //
                //'session' => $this->session,
                'getconfig' => $this->getconfig,
                'session_data' => $this->session_data,
                'current_user_id' => $this->current_user_id,
                'current_user_type' => $this->current_user_type,
                'current_user_logged' => $this->current_user_logged,
                'current_cid' => $this->current_cid,
                'current_sid' => $this->current_sid,
                'current_pid' => $this->current_pid,
                'debug_enable' => $this->debug_enable,
                //'menu' => $menu,
                //'allurl' => $allurl,
                'isMobile' => $this->isMobile,
                'html_lang' => $this->lang_key,
                'current_search_key' => $this->MY_get('s'),
            )
        );

        //
        $this->teamplate['footer'] = view('footer_view');
        $this->teamplate['html_lang'] = $this->lang_key;

        //
        return true;
    }

    protected function create_breadcrumb($text, $url = '')
    {
        if ($url != '') {
            $this->breadcrumb_position++;

            //
            $this->breadcrumb[] = '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a href="' . $url . '" itemprop="item" title="' . str_replace('"', '', $text) . '"><span itemprop="name">' . $text . '</span></a><meta itemprop="position" content="' . $this->breadcrumb_position . '"></li>';
        } else {
            $this->breadcrumb[] = '<li>' . $text . '</li>';
        }
        //print_r( $this->breadcrumb );

        //
        return false;
    }

    protected function create_term_breadcrumb($cats)
    {
        if (empty($cats)) {
            return false;
        }
        // print_r($cats);

        // 
        $this->taxonomy_slider[] = $cats;
        $this->posts_parent_list[] = $cats['term_id'];

        //
        if ($this->taxonomy_post_size == '' && isset($cats['term_meta']['taxonomy_custom_post_size'])) {
            $this->taxonomy_post_size = $cats['term_meta']['taxonomy_custom_post_size'];
        }

        //
        if ($cats['parent'] > 0) {
            $in_cache = __FUNCTION__;
            $parent_cats = $this->term_model->the_cache($cats['parent'], $in_cache);
            if ($parent_cats === null) {
                $parent_cats = $this->term_model->get_all_taxonomy($cats['taxonomy'], $cats['parent']);

                //
                $this->term_model->the_cache($cats['parent'], $in_cache, $parent_cats);
            }
            //print_r( $parent_cats );

            $this->create_term_breadcrumb($parent_cats);
        }

        //
        return $this->create_breadcrumb($cats['name'], $this->term_model->get_full_permalink($cats));
    }

    /**
     * Kiểm tra các bản ghi trong RewriteRule để redirect cho trang 404 nếu có
     **/
    protected function rewriteRule($rules_content = '')
    {
        if ($rules_content == '') {
            // xem có file RewriteRule ko
            $rules_path = WRITEPATH . 'RewriteRule.txt';
            // ko có thì trả về false luôn
            if (!is_file($rules_path)) {
                return false;
            }
            $rules_content = file_get_contents($rules_path);
            // } else {
            //     die($rules_content);
        }

        // xác định url hiện tại
        $current_uri = $_SERVER['REQUEST_URI'];

        // xem url này có trong RewriteRule ko
        foreach (
            [
                $current_uri,
                ltrim($current_uri, '/'),
            ] as $v
        ) {
            $v = '^' . $v . '$';
            //echo $v . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;

            //
            if (strpos($rules_content, $v) === false) {
                continue;
            }
            // tách thành mảng để kiểm tra điều kiện URL -> loại bỏ phần có dấu #
            $rules_content = explode("\n", $rules_content);

            //
            foreach ($rules_content as $url) {
                $url = trim($url);

                // Không phải rewriterule -> bỏ
                if (strpos(strtolower($url), 'rewriterule') === false) {
                    //echo $url . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
                    continue;
                }

                // trống và # -> bỏ
                if (empty($url) || substr($url, 0, 1) == '#') {
                    continue;
                }
                //echo $url . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;

                // có trong chuỗi -> cắt chuỗi
                if (strpos($url, $v) !== false) {
                    //echo $url . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;

                    // xác định kiểu redirect
                    $redirect_type = 301;
                    if (strpos($url, 'R=302') !== false) {
                        $redirect_type = 302;
                    }
                    //echo $redirect_type . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;

                    //
                    $url = trim(explode($v, $url)[1]);
                    //echo $url . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
                    $url = trim(explode("[", $url)[0]);
                    //echo $url . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
                    if (strpos($url, '//') === false) {
                        $url = DYNAMIC_BASE_URL . ltrim($url, '/');
                        //echo $url . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
                    }
                    //echo $url . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;

                    // -> thực hiện redirect
                    $this->MY_redirect($url, $redirect_type);

                    //
                    break;
                }
            }
        }
        //die($current_uri . ':' . __CLASS__ . ':' . __LINE__);

        //
        return true;
    }

    public function page404($msg_404 = '', $in_cache = '')
    {
        // kiểm tra có trong RewriteRule không đã
        $this->rewriteRule();

        // 
        $link_name = $_SERVER['REQUEST_URI'];
        if (strpos($link_name, '/apple-touch-icon.') !== false || strpos($link_name, '/apple-touch-icon-') !== false) {
            // với file ảnh thì bỏ mọi dấu ? ở sau luôn
            $link_name = explode('?', $link_name)[0];

            // copy file nếu chưa có
            if (strpos($link_name, '.png') !== false) {
                // echo PUBLIC_PUBLIC_PATH . 'favicon-full.png' . '<br>' . PHP_EOL;
                // echo PUBLIC_PUBLIC_PATH . ltrim($link_name, '/') . '<br>' . PHP_EOL;
                // die(__CLASS__ . ':' . __LINE__);

                // 
                $this->MY_copy(PUBLIC_PUBLIC_PATH . 'favicon-full.png', PUBLIC_PUBLIC_PATH . ltrim($link_name, '/'));
            }

            // chuyển hướng đến URL ảnh tương ứng
            $this->MY_redirect(DYNAMIC_BASE_URL . '/favicon-full.png', 301);
        }
        // với các file tĩnh thì bỏ mọi dấu ? ở sau luôn
        else if (
            strpos($link_name, '.php?') !== false ||
            strpos($link_name, '.aspx?') !== false ||
            strpos($link_name, '.asp?') !== false ||
            strpos($link_name, '.png?') !== false ||
            strpos($link_name, '.jpg?') !== false ||
            strpos($link_name, '.jpeg?') !== false ||
            strpos($link_name, '.gif?') !== false ||
            strpos($link_name, '.js?') !== false ||
            strpos($link_name, '.css?') !== false ||
            strpos($link_name, '.pdf?') !== false ||
            strpos($link_name, '.json?') !== false ||
            strpos($link_name, '.txt?') !== false ||
            strpos($link_name, '.html?') !== false ||
            strpos($link_name, '.htm?') !== false ||
            strpos($link_name, '.zip?') !== false ||
            strpos($link_name, '.sql?') !== false ||
            strpos($link_name, '.lock?') !== false ||
            strpos($link_name, '.env?') !== false ||
            strpos($link_name, '.ini?') !== false ||
            strpos($link_name, '.bak?') !== false ||
            strpos($link_name, '.yaml?') !== false ||
            strpos($link_name, '.yml?') !== false ||
            strpos($link_name, '.log?') !== false ||
            strpos($link_name, '.run?') !== false ||
            strpos($link_name, '.conf?') !== false ||
            strpos($link_name, '.gz?') !== false ||
            strpos($link_name, '.rar?') !== false ||
            strpos($link_name, '.tgz?') !== false ||
            strpos($link_name, '.7z?') !== false ||
            strpos($link_name, '.py?') !== false ||
            strpos($link_name, '.xml?') !== false
        ) {
            $link_name = explode('?', $link_name)[0];
        } else {
            // xóa bỏ các tham số không cần thiết trong URL
            $remove_params = array(
                'fbclid=',
                'gclid=',
                'fb_comment_id=',
                // 'add_to_wishlist=',
                '_wpnonce=',
                'utm_',
                'v',
                'nse',
            );
            foreach ($remove_params as $v) {
                $link_name = explode('?' . $v, $link_name)[0];
                $link_name = explode('&' . $v, $link_name)[0];
            }
        }

        // xóa đoạn /public/ ở đầu link nếu có
        if (strpos($link_name, '/public/') !== false) {
            // $link_name = explode('/public/', $link_name)[1];
            $link_name = str_replace('/public/', '/', $link_name, 1);
        }

        // 
        if (!empty($link_name)) {
            $data = $this->base_model->select(
                [
                    // các trường cần lấy ra
                    'link_image',
                ],
                'links',
                array(
                    // các kiểu điều kiện where
                    // 'link_image !=' => '',
                ),
                array(
                    'like_before' => array(
                        'link_name' => $link_name,
                    ),
                    'order_by' => array(
                        'link_id' => 'DESC',
                    ),
                    // hiển thị mã SQL để check
                    // 'show_query' => 1,
                    // trả về câu query để sử dụng cho mục đích khác
                    // 'get_query' => 1,
                    // trả về COUNT(column_name) AS column_name
                    // 'selectCount' => 'ID',
                    // trả về tổng số bản ghi -> tương tự mysql num row
                    // 'getNumRows' => 1,
                    // 'offset' => 0,
                    'limit' => 10,
                )
            );
            // print_r($data);
            if (!empty($data)) {
                foreach ($data as $v) {
                    if (!empty($v['link_image'])) {
                        $this->rewriteRule('RewriteRule ^' . $_SERVER['REQUEST_URI'] . '$ ' . $v['link_image'] . ' [R=301,L]');
                        break;
                    }
                }
                // return false;
            } else if (strpos($link_name, '.php') === false) {
                // } else {
                // các link kiểu php thì không cần lưu lại
                // lưu các URL 404 này vào bảng links để tiện theo dõi
                $result_id = $this->base_model->insert('links', [
                    'link_url' => $_SERVER['HTTP_HOST'],
                    'link_name' => $link_name,
                    'link_image' => '',
                    'link_target' => $in_cache == '' ? debug_backtrace()[1]['function'] : $in_cache,
                    'link_description' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
                    'link_visible' => 'N',
                    'link_owner' => $this->current_user_id,
                    'link_updated' => date(EBE_DATETIME_FORMAT),
                    'link_rel' => $this->base_model->getIPAddress(),
                    'link_notes' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
                    'link_rss' => '',
                ]);
            }
        }


        /**
         * trả về lỗi 404
         **/
        // echo __CLASS__ . ':' . __LINE__;
        if (function_exists('http_response_code')) {
            http_response_code(404);
        }
        $pcol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
        $response = \Config\Services::response();
        $response->setStatusCode(404, $pcol . ' 404 Not Found');
        // http_response_code(404);

        //
        $this->teamplate['main'] = view(
            '404',
            array(
                'seo' => $this->base_model->default_seo(
                    '404 not found',
                    __FUNCTION__,
                    [
                        'canonical' => base_url('404'),
                    ]
                ),
                'breadcrumb' => '',
                // thông điệp của việc xuất hiện lỗi 404
                'msg_404' => $msg_404,
            )
        );
        return view('layout_view', $this->teamplate);
    }

    protected function category($input, $post_type, $taxonomy, $file_view = 'category_view', $ops = [])
    {
        // xem có file view tương ứng không
        if ($file_view == '' || !is_file(VIEWS_PATH . $file_view . '.php')) {
            // không có thì hiển thị lỗi luôn
            // return $this->page404('ERROR (' . $file_view . ') ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! Bạn không có quyền xem thông tin này...');
            // $file_view = 'category_auto_view';
            $file_view = 'term_view';
        }

        //echo debug_backtrace()[1]['class'] . ':' . debug_backtrace()[1]['function'] . '<br>' . PHP_EOL;
        //$config['base_url'] = $this->term_model->get_term_permalink();
        //$config['per_page'] = 50;
        //$config['uri_segment'] = 3;

        //
        if (!isset($ops['page_num'])) {
            $ops['page_num'] = 1;
        }

        //
        if (!isset($ops['cache_key'])) {
            $this->cache_key = $this->term_model->key_cache($input['term_id']) . 'page' . $ops['page_num'];
        } else {
            $this->cache_key = $ops['cache_key'];
        }
        $cache_value = $this->MY_cache($this->cache_key);
        // Will get the cache entry named 'my_foo'
        //var_dump($cache_value);
        // có thì in ra cache là được
        //if ( $_SERVER[ 'REQUEST_METHOD' ] == 'GET' && $cache_value !== null ) {
        if ($this->hasFlashSession() === false && $cache_value !== null) {
            return $this->show_cache($cache_value, $this->cache_key);
        }

        //
        //echo 'this category <br>' . PHP_EOL;

        //
        //print_r( $input );
        $data = $input;
        $data = $this->term_model->terms_meta_post([$data]);
        $data = $data[0];

        // đầu vào (input) chính là data rồi -> không cần gọi lại
        /*
        $data = $this->term_model->get_all_taxonomy( $taxonomy, $input[ 'term_id' ], [
        'parent' => $input[ 'term_id' ],
        //'where_in' => isset( $input[ 'where_in' ] ) ? $input[ 'where_in' ] : NULL
        ] );
        */

        //
        //print_r($data);
        $data = $this->term_model->get_child_terms([$data], []);
        //print_r($data);

        $data = $data[0];
        //print_r($data);

        // nếu có lệnh redirect do sai URL
        if (isset($_GET['canonical'])) {
            //echo __CLASS__ . ':' . __LINE__;
            // xóa permalink để URL được update lại
            //$data['term_permalink'] = '';
            //$data['updated_permalink'] = 0;
            $full_link = $this->term_model->update_term_permalink($data, DYNAMIC_BASE_URL);
        } else {
            $full_link = $this->term_model->get_full_permalink($data);
        }
        //echo $full_link;

        //
        $this->term_model->update_count_post_in_term($data);

        // hiện tại chỉ hỗ trợ bản amp cho tin tức
        $amp_link = '';
        if (ENABLE_AMP_VERSION === true && $data['taxonomy'] == TaxonomyType::POSTS) {
            $amp_link = $this->base_model->amp_term_link($data, $ops['page_num']);
        }

        //
        //$this->create_breadcrumb( $data[ 'name' ] );
        $this->create_term_breadcrumb($data);
        //print_r( $this->taxonomy_slider );
        $seo = $this->base_model->term_seo($data, $full_link, $amp_link);

        // chỉnh lại thông số cho canonical
        if ($ops['page_num'] > 1) {
            // $seo['canonical'] = rtrim($seo['canonical'], '/') . '/page/' . $ops['page_num'];
            $seo['shortlink'] = rtrim($seo['shortlink'], '/') . '&page_num=' . $ops['page_num'];
            $seo['title'] .= ' - Page ' . $ops['page_num'];
        }
        //print_r($seo);

        // lấy danh sách nhóm con xem có không
        //$child_cat = $this->term_model->get_all_taxonomy( $data[ 'taxonomy' ] );
        //print_r( $child_cat );

        // lấy banner quảng cáo theo taxonomy nếu có
        $taxonomy_slider = '';
        /*
        $taxonomy_slider = $this->term_model->get_the_slider($this->taxonomy_slider);
        //echo $taxonomy_slider . '<br>' . PHP_EOL;
        if ($taxonomy_slider == '') {
            $taxonomy_slider = $this->lang_model->get_the_text('main_slider_slug', '');
        }
        //echo $taxonomy_slider . '<br>' . PHP_EOL;
        if ($taxonomy_slider != '') {
            $taxonomy_slider = $this->post_model->get_the_ads(
                $taxonomy_slider,
                0,
                [
                    'add_class' => 'taxonomy-auto-slider'
                ]
            );
        }
        */

        // -> views
        $this->teamplate['breadcrumb'] = view(
            'breadcrumb_view',
            array(
                'breadcrumb' => $this->breadcrumb
            )
        );
        if ($data['parent'] > 0) {
            $this->current_cid = $data['parent'];
            $this->current_sid = $data['term_id'];
        } else {
            $this->current_cid = $data['term_id'];
        }

        //
        //echo $file_view . '<br>' . PHP_EOL;
        $this->teamplate['main'] = view(
            $file_view,
            array(
                //'post_per_page' => $post_per_page,
                'taxonomy_post_size' => $this->taxonomy_post_size,
                //'taxonomy_slider' => $this->taxonomy_slider,
                'taxonomy_slider' => $taxonomy_slider,
                'taxonomy' => $taxonomy,
                'ops' => $ops,
                'seo' => $seo,
                'post_type' => $post_type,
                'getconfig' => $this->getconfig,
                'data' => $data,
                'current_cid' => $this->current_cid,
                'current_sid' => $this->current_sid,
            )
        );

        // nếu có flash session -> trả về view luôn
        if ($this->hasFlashSession() === true) {
            return view('layout_view', $this->teamplate);
        }
        // còn không sẽ tiến hành lưu cache
        $cache_value = view('layout_view', $this->teamplate);

        // 
        $this->MY_cache($this->cache_key, $cache_value . '<!-- Served from: ' . __FUNCTION__ . ' -->');

        //
        return $cache_value;
    }

    // hàm lấy dữ liệu đầu vào và xử lý các vấn đề bảo mật nếu có
    protected function MY_data($a, $default_value = '', $xss_clean = true)
    {
        // với kiểu chuỗi -> so sánh lấy chuỗi trống
        if (is_string($a) && $a == '') {
            return $default_value;
        } else if (is_numeric($a)) {
            return $a;
        } else if (empty($a)) {
            return $default_value;
        }

        //
        return $a;
    }
    protected function MY_get($key, $default_value = '', $xss_clean = true)
    {
        return $this->MY_data($this->request->getGet($key), $default_value, $xss_clean);
    }
    protected function MY_post($key, $default_value = '', $xss_clean = true)
    {
        return $this->MY_data($this->request->getPost($key), $default_value, $xss_clean);
    }

    /**
     * Upload giả lập wordpress
     */
    protected function deny_visit_upload($upload_root = '', $remove_file = false, $hotlink_protection = false)
    {
        if ($upload_root == '') {
            $upload_root = PUBLIC_HTML_PATH . PostType::MEDIA_PATH;
        }
        $upload_root = rtrim($upload_root, '/') . '/';

        //
        $htaccess_file = $upload_root . '.htaccess';
        //die($htaccess_file);
        //echo $htaccess_file . '<br>' . PHP_EOL;

        // cập nhật lại nội dung file htaccess
        if ($remove_file === true && is_file($htaccess_file)) {
            $this->MY_unlink($htaccess_file);
        }

        //
        if (!is_file($htaccess_file)) {
            // tạo hotlink protection nếu có yêu cầu
            $hotlink_protection = '';
            if ($hotlink_protection === true) {
                $hotlink_protection = $this->helpersTmpFile(
                    'hotlink_protection',
                    [
                        'http_host' => $_SERVER['HTTP_HOST'],
                        'htaccess_allow' => HTACCESSS_ALLOW,
                    ]
                );
            }

            // nội dung chặn mọi truy cập tới các file trong này
            $this->base_model->ftp_create_file(
                $htaccess_file,
                // tạo file htaccess chỉ cho phép truy cập tới 1 số file được chỉ định
                $this->helpersTmpFile(
                    'htaccess_allow_deny',
                    [
                        'htaccess_allow' => HTACCESSS_ALLOW,
                        'created_from' => __CLASS__ . ':' . __LINE__,
                        'base_url' => DYNAMIC_BASE_URL,
                        'hotlink_protection' => $hotlink_protection,
                    ]
                ),
            );
        }
        //die( __CLASS__ . ':' . __LINE__ );

        //
        return $htaccess_file;
    }

    protected function media_upload($allow_upload = [], $md5 = false)
    {
        // print_r($_POST);
        // print_r($_FILES);
        // die(__CLASS__ . ':' . __LINE__);

        //
        $upload_root = PUBLIC_HTML_PATH . PostType::MEDIA_PATH;
        // echo $upload_root . '<br>' . PHP_EOL;

        //
        $this->deny_visit_upload($upload_root);

        //
        $upload_path = $this->media_path(
            [
                date('Y'),
                date('m'),
            ],
            $upload_root
        );
        // echo $upload_path . '<br>' . PHP_EOL;

        // mảng trả về danh sách file đã upload
        $arr_result = [];

        // 1 số định dạng file không cho phép upload trực tiếp
        $arr_block_upload = [
            'php',
            'exe',
            'py',
            'sh'
        ];

        //
        if ($upload_files = $this->request->getFiles()) {
            // print_r($upload_files);
            // die(__CLASS__ . ':' . __LINE__);

            // chạy vòng lặp để lấy các key upload -> xác định tên input tự động
            foreach ($_FILES as $key => $upload_image) {
                // echo $key . '<br>' . PHP_EOL;
                // print_r($upload_image);

                //
                // $multi_up = true;
                // không có size -> bỏ
                if (!isset($upload_image['size'])) {
                    continue;
                } else {
                    // size là dạng mảng -> multi upload
                    if (is_array($upload_image['size'])) {
                        // size quá nhỏ -> bỏ
                        if (empty($upload_image['size']) || $upload_image['size'][0] < 1) {
                            continue;
                        }
                    } else {
                        // $multi_up = false;
                        // giả lập muti up -> để lệnh sau có thể hoạt động được
                        $upload_files[$key] = [$upload_files[$key]];

                        //
                        if ($upload_image['size'] < 1) {
                            // size quá nhỏ -> bỏ
                            continue;
                        }
                    }
                }
                // print_r($upload_files[$key]);
                // die(__CLASS__ . ':' . __LINE__);

                //
                foreach ($upload_files[$key] as $file) {
                    // print_r($file);
                    // die(__CLASS__ . ':' . __LINE__);
                    if ($file->isValid() && !$file->hasMoved()) {
                        $file_name = $this->MY_get('set_filename');
                        if (empty($file_name)) {
                            $file_name = $file->getName();
                        } else {
                            // lấy phần mơng của file
                            $file_ext = $file->getClientExtension();
                            // return [$file_ext];
                            $file_name = $file_name . '.' . $file_ext;
                        }
                        // return [$file_name];
                        // echo $file_name . '<br>' . PHP_EOL;
                        $file_name = $this->base_model->_eb_non_mark_seo($file_name);
                        $file_name = sanitize_filename($file_name);
                        // khi cần bảo mật tên file thì thực hiện md5 cho nó
                        if ($md5 !== false) {
                            $file_name = md5($file_name) . '-' . md5(time()) . '-' . $file_name;
                        }
                        // echo $file_name . '<br>' . PHP_EOL;

                        // kiểm tra định dạng file
                        $mime_type = $file->getMimeType();
                        // echo $mime_type . '<br>' . PHP_EOL;
                        // continue;

                        //
                        $file_ext = $file->guessExtension();
                        // echo $file_ext . '<br>' . PHP_EOL;
                        if (empty($file_ext)) {
                            $this->result_json_type(
                                [
                                    'code' => __LINE__,
                                    'error' => 'Định dạng file chưa được hỗ trợ ' . $mime_type
                                ]
                            );
                            // $file_ext = basename($mime_type);
                        }
                        $file_ext = strtolower($file_ext);

                        //
                        $file_path = $upload_path . $file_name;
                        // echo $file_path . '<br>' . PHP_EOL;

                        // kiểm tra lại ext -> vì có 1 trường hợp mime type khác với ext truyền vào
                        $check_ext = pathinfo($file_path, PATHINFO_EXTENSION);
                        // echo $check_ext . '<br>' . PHP_EOL;

                        //
                        if ($check_ext != $file_ext) {
                            // $this->base_model->alert('Định dạng file không khớp nhau! ' . $check_ext . ' != ' . $file_ext, 'error');
                            $this->base_model->msg_error_session('Định dạng file không khớp nhau! ' . $check_ext . ' != ' . $file_ext);
                            continue;
                        }

                        // đổi tên file nếu file đã tồn tại
                        if (is_file($file_path)) {
                            for ($i = 1; $i < 100; $i++) {
                                $file_new_name = basename($file_name, '.' . $file_ext) . '_' . $i . '.' . $file_ext;
                                $file_path = $upload_path . $file_new_name;
                                // echo $file_path . '<br>' . PHP_EOL;
                                if (!is_file($file_path)) {
                                    $file_name = basename($file_path);
                                    break;
                                }
                            }
                        }
                        // echo $file_path . '<br>' . PHP_EOL;

                        // nếu không phải file ảnh
                        $check_mime_type = strtolower(explode('/', $mime_type)[0]);
                        $is_image = true;
                        if ($check_mime_type != 'image') {
                            $is_image = false;
                            $media_mime_type = [
                                'audio',
                                'video',
                            ];
                            // hỗ trợ up video, audio
                            if (in_array($check_mime_type, $media_mime_type)) {
                                //
                            }
                            // các file khác chưa xác định thì cứ gọi là bỏ qua đã
                            else {
                                // thêm vào tệp mở rộng để không cho truy cập file trực tiếp
                                $file_other_ext = 'daidq-ext';
                                $file_new_path = $file_path . '.' . $file_other_ext;
                                // echo $file_new_path . '<br>' . PHP_EOL;
                                if (is_file($file_new_path)) {
                                    for ($i = 1; $i < 100; $i++) {
                                        $file_new_path = $file_path . '.' . $file_other_ext . '_' . $i;
                                        // echo $file_new_path . '<br>' . PHP_EOL;
                                        if (!is_file($file_new_path)) {
                                            $file_path = $file_new_path;
                                            break;
                                        }
                                    }
                                } else {
                                    $file_path = $file_new_path;
                                }
                                // echo $file_path . '<br>' . PHP_EOL;
                                $file_name = basename($file_path);
                                // echo $file_name . '<br>' . PHP_EOL;
                                // die( __CLASS__ . ':' . __LINE__ );
                            }
                        }
                        // echo $file_path . '<br>' . PHP_EOL;

                        // nếu có kiểm duyệt định dạng file -> chỉ các file trong này mới được upload
                        if (!empty($allow_upload) && !in_array($file_ext, $allow_upload)) {
                            continue;
                        }
                        // nếu không, sẽ chặn các định dạng file có khả năng thực thi lệnh từ server
                        else if (in_array($file_ext, $arr_block_upload)) {
                            continue;
                        }

                        //
                        $file->move($upload_path, $file_name, true);

                        //
                        if (!is_file($file_path)) {
                            continue;
                        }
                        chmod($file_path, DEFAULT_FILE_PERMISSION);

                        //
                        if (!isset($arr_result[$key])) {
                            $arr_result[$key] = [];
                        }

                        //
                        $metadata = $this->media_attachment_metadata($file_path, $file_ext, $upload_path, $mime_type, $upload_root);

                        // optimize file gốc
                        if ($is_image === true) {
                            $new_quality = \App\Libraries\MyImage::quality($file_path);
                        }

                        //
                        if ($metadata !== false) {
                            $arr_result[$key][] = $metadata['file_uri'];
                        }
                    } else {
                        throw new \RuntimeException($file->getErrorString() . '(' . $file->getError() . ')');
                    }
                }
            }
            // die(__CLASS__ . ':' . __LINE__);
        }
        // print_r($arr_result);
        // die(__CLASS__ . ':' . __LINE__);

        //
        return $arr_result;
    }

    // tạo thumbnail cho hình ảnh dựa theo path
    protected function media_attachment_metadata($file_path, $file_ext = '', $upload_path = '', $mime_type = '', $upload_root = '', $post_parent = 0)
    {
        if (!is_file($file_path)) {
            return false;
        }
        //echo $file_path . '<br>' . PHP_EOL;

        // bảo mật file, lỗi thì xóa luôn file này đi
        /*
        if ( $this->security->xss_clean( $file_path, TRUE ) === FALSE ) {
        unlink( $file_path );
        die( 'ERROR! xss file upload' );
        }
        */
        //unlink( $file_path );
        //continue;

        //
        //echo 'upload ok: ' . $v . '<br>' . PHP_EOL;

        //
        if ($upload_root == '') {
            $upload_root = PUBLIC_HTML_PATH . PostType::MEDIA_PATH;
        }
        //echo $upload_root . '<br>' . PHP_EOL;
        if ($upload_path == '') {
            $upload_path = dirname($file_path) . '/';
        }
        //echo $upload_path . '<br>' . PHP_EOL;

        //
        $file_uri = str_replace($upload_root, '', $file_path);
        //echo $file_uri . '<br>' . PHP_EOL;

        //
        //echo $file_ext . '<br>' . PHP_EOL;
        if ($file_ext == '') {
            $file_ext = pathinfo($file_path, PATHINFO_EXTENSION);
        }
        $file_ext = strtolower($file_ext);
        //die($file_ext);
        //echo $file_ext . '<br>' . PHP_EOL;

        //
        if ($mime_type == '') {
            $mime_type = mime_content_type($file_path);
        }
        //echo $mime_type . '<br>' . PHP_EOL;

        // nếu không phải file ảnh
        $is_image = true;
        if (strtolower(explode('/', $mime_type)[0]) != 'image') {
            $is_image = false;
        }

        //
        $post_title = basename($file_path, '.' . $file_ext);
        //echo $post_title . '<br>' . PHP_EOL;

        //
        $arr_list_size = PostType::media_size();
        // chỉ resize file ảnh
        $arr_allow_resize = [
            'bmp',
            'png',
            'jpg',
            'jpeg'
        ];

        // giả lập dữ liệu giống wordpress
        $arr_after_sizes = [];
        if ($is_image == true) {
            $get_file_info = getimagesize($file_path);
        } else {
            $get_file_info = [
                0,
                0
            ];
        }
        //print_r( $get_file_info );
        $file_size = filesize($file_path);
        //echo $file_size . '<br>' . PHP_EOL;
        //die( __CLASS__ . ':' . __LINE__ );
        foreach ($arr_list_size as $size_name => $size) {
            $resize_path = $upload_path . $post_title . '-' . $size_name . '.' . $file_ext;
            //echo $resize_path . '<br>' . PHP_EOL;
            //die( __CLASS__ . ':' . __LINE__ );
            //continue;

            /**
             * Sử dụng class tự viết hoặc tham kháo thư viện của CI3
             * https://codeigniter.com/userguide3/libraries/image_lib.html
             */
            // chỉ resize với các file được chỉ định (thường là file ảnh)
            if (in_array($file_ext, $arr_allow_resize)) {
                $resize_img = \App\Libraries\MyImage::resize($file_path, $resize_path, $size);
            }
            // các file khác không cần resize
            else {
                if (empty($get_file_info)) {
                    $get_file_info = [
                        0,
                        0
                    ];
                }
                //var_dump($is_image);
                //print_r($get_file_info);
                //die(__CLASS__ . ':' . __LINE__);
                $resize_img = [
                    'width' => $get_file_info[0],
                    'height' => $get_file_info[1],
                    'file_size' => $file_size,
                    'file' => basename($file_path),
                ];
            }
            $resize_img['mime-type'] = $mime_type;
            //print_r( $resize_img );

            //
            $arr_after_sizes[$size_name] = $resize_img;
        }
        //print_r( $arr_after_sizes );
        //die( __CLASS__ . ':' . __LINE__ );

        //
        //print_r( $get_file_info );
        $arr_metadata = [
            'width' => $get_file_info[0],
            'height' => $get_file_info[1],
            'file_size' => $file_size,
            'file' => $file_uri,
            'sizes' => $arr_after_sizes,
            'image_meta' => [
                'aperture' => 0,
                'credit' => '',
                'camera' => '',
                'caption' => '',
                'created_timestamp' => time(),
                'copyright' => '',
                'focal_length' => 0,
                'iso' => 0,
                'shutter_speed' => 0,
                'title' => '',
                'orientation' => 0,
                'keywords' => [],
            ]
        ];
        // print_r($arr_metadata);
        $str_metadata = serialize($arr_metadata);
        // echo $str_metadata . '<br>' . PHP_EOL;
        // $test = unserialize($str_metadata);
        // print_r($test);

        //
        $data_insert = [
            'post_title' => $post_title,
            'post_status' => PostType::INHERIT,
            //'post_name' => $post_title,
            'post_name' => str_replace('.', '-', PostType::MEDIA_URI . $file_uri),
            'guid' => DYNAMIC_BASE_URL . PostType::MEDIA_URI . $file_uri,
            'post_type' => PostType::MEDIA,
            'post_mime_type' => $mime_type,
            'post_parent' => $post_parent,
        ];
        // print_r($data_insert);
        $_POST['post_meta'] = [
            '_wp_attachment_metadata' => $str_metadata,
            '_wp_attached_file' => $file_uri,
        ];
        // print_r($_POST);
        // die(__CLASS__ . ':' . __LINE__);
        $result_id = $this->post_model->insert_post($data_insert, $_POST['post_meta']);
        // print_r($result_id);
        if (is_array($result_id) && isset($result_id['error'])) {
            $this->base_model->alert($result_id['error'], 'error');
        }
        // die(__CLASS__ . ':' . __LINE__);
        // echo 'Result id: ' . $result_id . '<br>' . PHP_EOL;

        //
        return [
            'file_uri' => PostType::MEDIA_URI . $file_uri,
            'is_image' => $is_image,
            'metadata' => $arr_metadata,
            'data' => $data_insert,
            'meta' => $_POST['post_meta'],
        ];
    }

    // tạo path upload
    protected function media_path($data = [], $path = '')
    {
        if ($path == '') {
            //$path = PUBLIC_HTML_PATH . PostType::MEDIA_URI;
            $path = PUBLIC_HTML_PATH . PostType::MEDIA_PATH;
        }
        foreach ($data as $v) {
            $path .= $v . '/';
            //echo $path . '<br>' . PHP_EOL;

            if (!is_dir($path)) {
                mkdir($path, DEFAULT_DIR_PERMISSION) or die('ERROR create dir (' . __CLASS__ . ':' . __LINE__ . ')! ' . $path);
                chmod($path, DEFAULT_DIR_PERMISSION);
            }
        }

        //
        return $path;
    }

    // kiểm tra quyền truy cập chi tiết 1 post
    protected function post_permission($data)
    {
        // print_r($this->session_data);

        // nếu bài viết ở chế độ riêng tư
        if ($data['post_status'] == PostType::PRIVATELY) {
            // -> chỉ đăng nhập mới có thể xem
            if ($this->current_user_id < 1) {
                return 'WARNING ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! Bạn không có quyền xem nội dung này...';
            }
        }
        // nếu bài này không phải dạng public
        else if ($data['post_status'] != PostType::PUBLICITY) {
            // kiểm tra xem nếu không phải admin thì không cho xem
            if (empty($this->session_data) || !isset($this->session_data['userLevel']) || $this->session_data['userLevel'] < 1) {
                return 'ERROR ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! Cannot be determined post data...';
            }
        }

        //
        return true;
    }

    protected function hasFlashSession()
    {
        // không cache nếu phương thức tuyền vào không phải là get
        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
            return true;
        }
        // Save cache -> không lưu cache khi có session thông báo riêng
        else if ($this->base_model->msg_session() != '' || $this->base_model->msg_error_session() != '') {
            return true;
        }
        return false;
    }

    /**
     * Tạo dữ liệu để tạo schema cho thống nhất
     **/
    protected function structuredData($data, $f, $html = '', $get_data = false)
    {
        //print_r( $data );
        $data['name'] = $this->getconfig->name;
        $data['logo'] = DYNAMIC_BASE_URL . $this->getconfig->logo;
        $data['logo_height_img'] = $this->getconfig->logo_height_img;
        $data['logo_width_img'] = $this->getconfig->logo_width_img;
        $data['currency_sd_format'] = empty($this->getconfig->currency_sd_format) ? 'USD' : $this->getconfig->currency_sd_format;

        //
        $data['post_img'] = '';
        $data['trv_width_img'] = 0;
        $data['trv_height_img'] = 0;
        $data['trv_img'] = $this->post_model->get_list_thumbnail($data, 'large');
        //$data['trv_img'] = $this->post_model->get_post_thumbnail($data);
        if ($data['trv_img'] != '') {
            $data['trv_img'] = explode('?', $data['trv_img'])[0];
            // nếu file tồn tại trong host -> xác định size của file
            //echo PUBLIC_PUBLIC_PATH . $data['trv_img'];
            if (is_file(PUBLIC_PUBLIC_PATH . $data['trv_img'])) {
                $logo_data = getimagesize(PUBLIC_PUBLIC_PATH . $data['trv_img']);

                //
                $data['post_img'] = DYNAMIC_BASE_URL . $data['trv_img'];
                $data['trv_width_img'] = $logo_data[0];
                $data['trv_height_img'] = $logo_data[1];
            } else if (strpos($data['trv_img'], '//') !== false) {
                $data['post_img'] = $data['trv_img'];
                $data['trv_width_img'] = 280;
                $data['trv_height_img'] = 280;
            }
        }
        $data['image'] = $data['trv_img'];

        //
        $data['p_link'] = $this->post_model->get_full_permalink($data);

        //
        //print_r( $data );
        if ($get_data === true) {
            return $data;
        }

        //
        if ($html == '') {
            $html = file_get_contents(VIEWS_PATH . 'html/structured-data/' . $f);
        }
        // thay data chính
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                continue;
            }
            $html = str_replace('{{' . $k . '}}', str_replace('"', '', $v), $html);
        }
        // sau đó là meta
        if (isset($data['post_meta'])) {
            foreach ($data['post_meta'] as $k => $v) {
                if (is_array($v)) {
                    continue;
                }
                $html = str_replace('{{' . $k . '}}', str_replace('"', '', $v), $html);
            }
        }

        // thay 1 số template đề phòng không có dữ liệu tương ứng
        foreach (
            [
                'meta_description' => $data['post_title'],
            ] as $k => $v
        ) {
            $html = str_replace('{{' . $k . '}}', $v, $html);
        }

        //
        return $html;
    }

    /**
     * Trả về mảng dữ liệu đã được build để tạo cấu trúc
     **/
    public function structuredGetData($data)
    {
        return $this->structuredData($data, '', '', true);
    }

    /**
     * 1 số controller bắt buộc phải đăng nhập mới cho tiếp tục
     **/
    protected function required_logged($add_params = '')
    {
        if ($this->current_user_id < 1) {
            // tạo url sau khi đăng nhập xong sẽ trỏ tới
            $login_redirect = DYNAMIC_BASE_URL . ltrim($_SERVER['REQUEST_URI'], '/');
            //die($login_redirect);

            //
            $login_url = base_url('guest/login') . '?login_redirect=' . urlencode($login_redirect);
            //$login_url .= '&msg=' . urlencode('Permission deny! ' . basename(__FILE__, '.php') . ':' . __LINE__);
            $login_url .= '&reauth=1';
            $login_url .= $add_params;
            //die( $login_url );

            //
            die(header('Location: ' . $login_url));
            //die( 'Permission deny! ' . basename( __FILE__, '.php' ) . ':' . __LINE__ );
        }
    }
}
