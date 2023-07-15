<?php

namespace App\Controllers;

// Libraries
use App\Libraries\PostType;
use App\Libraries\TaxonomyType;

//
class Sitemap extends Csrf
{
    // chức năng này không cần nạp header
    public $preload_header = false;

    public function __construct()
    {
        parent::__construct();

        // kiểm tra xem website có đang bật chế độ public không
        //print_r( $this->getconfig );
        if ($this->getconfig->blog_private == 'on') {
            $pcol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
            //$this->response->setStatusCode( 404, $pcol . ' 404 Not Found' );
            header($pcol . ' 404 Not Found');
            die('ERROR ' . __LINE__ . '! Sitemap not available because site not public...');
        }

        // định dạng ngày tháng
        $this->sitemap_date_format = 'c';
        $this->sitemap_current_time = date($this->sitemap_date_format, time());

        // giới hạn số bài viết cho mỗi sitemap map
        $this->limit_post_get = 100;
        //$this->limit_post_get = 2;

        // giới hạn tạo sitemap cho hình ảnh -> google nó limit 1000 ảnh nên chỉ lấy thế thôi
        $this->limit_image_get = $this->limit_post_get;

        // thời gian nạp lại cache cho file, để = 0 -> disable
        //$time_for_relload_sitemap = 0;
        $time_for_relload_sitemap = 3600;
        //$time_for_relload_sitemap = 3 * 3600;

        $this->web_link = DYNAMIC_BASE_URL;

        // các post type sử dụng status inherit
        $this->arr_media_status = [
            PostType::MEDIA,
            PostType::WP_MEDIA,
        ];
    }

    public function index($post_type = '', $page_page = '', $page_num = 1)
    {
        global $arr_custom_post_type;
        //print_r( $arr_custom_post_type );
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;

        //
        $this->WGR_echo_sitemap_css();

        //
        //echo $post_type . '<br>' . PHP_EOL;
        //echo $page_page . '<br>' . PHP_EOL;
        //echo $page_num . '<br>' . PHP_EOL;
        if (!empty($post_type)) {
            // sitemap cho danh mục
            if ($post_type == 'tags') {
                return $this->sitemap_tags();
            }
            // sitemap cho hình ảnh
            else if (in_array($post_type, $this->arr_media_status)) {
                return $this->media_sitemap($post_type, $page_num);
            }
            // sitemap cho phần bài viết
            else {
                return $this->by_post_type($post_type, $page_num);
            }
        }

        //
        $get_list_sitemap = '';

        // manual -> chuẩn hơn trong trường hợp không có bài viết tương ứng thì sitemap không được kích hoạt
        $get_list_sitemap .= $this->WGR_echo_sitemap_node($this->web_link . 'sitemap/tags', $this->sitemap_current_time);


        /*
         * sitemap cho phần bài viết
         */
        $arr_post_type = [
            PostType::POST,
            //PostType::BLOG,
            PostType::PROD,
            PostType::PAGE,
        ];
        //print_r( $arr_post_type );

        // lấy custom post type
        foreach ($arr_custom_post_type as $k => $v) {
            // không tạo sitemap cho các post type được chỉ định không public
            if (isset($v['public']) && $v['public'] != 'on') {
                continue;
            }

            //
            $arr_post_type[] = $k;
        }
        //print_r( $arr_post_type );
        $arr_post_type = array_unique($arr_post_type);
        //print_r( $arr_post_type );

        // ->
        foreach ($arr_post_type as $post_type) {
            $totalThread = $this->get_post_type($post_type, 0, true);
            if ($totalThread <= 0) {
                continue;
            }

            //
            $get_list_sitemap .= $this->WGR_echo_sitemap_node($this->web_link . 'sitemap/' . $post_type, $this->sitemap_current_time);

            // phân trang cho sitemap (lấy từ trang 2 trở đi)
            $get_list_sitemap .= $this->WGR_sitemap_part_page($totalThread, 'sitemap/' . $post_type);
        }


        /*
         * sitemap cho phần media
         */
        $arr_post_type = [
            PostType::MEDIA,
            PostType::WP_MEDIA,
        ];
        //print_r( $arr_post_type );

        // ->
        foreach ($arr_post_type as $post_type) {
            $totalThread = $this->media_total($post_type);
            //echo $totalThread . '<br>' . PHP_EOL;

            //
            if ($totalThread <= 0) {
                continue;
            }

            //
            $get_list_sitemap .= $this->WGR_echo_sitemap_node($this->web_link . 'sitemap/' . $post_type, $this->sitemap_current_time);

            // phân trang cho sitemap (lấy từ trang 2 trở đi)
            $get_list_sitemap .= $this->WGR_sitemap_part_page($totalThread, 'sitemap/' . $post_type);
        }


        //
        echo $this->tmp(file_get_contents(__DIR__ . '/sitemap/sitemapindex.xml', 1), [
            'get_list_sitemap' => $get_list_sitemap,
        ]);
        exit();
    }

    private function sitemap_tags()
    {
        global $arr_custom_taxonomy;
        //print_r( $arr_custom_taxonomy );

        //
        $get_list_sitemap = '';

        // home
        $get_list_sitemap .= $this->WGR_echo_sitemap_url_node(
            $this->web_link,
            1.0,
            $this->sitemap_current_time
        );

        //
        $arr_taxonomy_type = [
            TaxonomyType::POSTS,
            //TaxonomyType::BLOGS,
            TaxonomyType::PROD_CATS,
        ];
        //print_r( $arr_taxonomy_type );

        // lấy custom post type
        foreach ($arr_custom_taxonomy as $k => $v) {
            // không tạo sitemap cho các taxonomy được chỉ định không public
            if (isset($v['public']) && $v['public'] != 'on') {
                continue;
            }
            $arr_taxonomy_type[] = $k;
        }
        //print_r($arr_taxonomy_type);
        $arr_taxonomy_type = array_unique($arr_taxonomy_type);
        //print_r($arr_taxonomy_type);

        // ->
        foreach ($arr_taxonomy_type as $taxonomy_type) {
            $data = $this->term_model->get_all_taxonomy($taxonomy_type, 0, [
                //'or_like' => $where_or_like,
                //'lang_key' => $this->lang_key,
                //'get_meta' => true,
                //'get_child' => true
            ]);
            //print_r( $data );
            foreach ($data as $v) {
                $get_list_sitemap .= $this->WGR_echo_sitemap_url_node(
                    $this->term_model->get_full_permalink($v),
                    1.0,
                    date($this->sitemap_date_format, strtotime($v['last_updated'])),
                );
            }
        }

        //
        return $this->WGR_echo_sitemap_urlset($get_list_sitemap);
    }

    private function get_post_type($post_type, $page_num = 1, $get_count = false)
    {
        // các kiểu điều kiện where
        $where = [
            //'posts.post_status !=' => PostType::DELETED,
            'posts.post_type' => $post_type,
            //'posts.post_status' => in_array( $post_type, $this->arr_media_status ) ? PostType::INHERIT : PostType::PUBLICITY,
            'posts.post_status' => PostType::PUBLICITY,
            //'posts.lang_key' => $this->lang_key
        ];

        // tổng kết filter
        $filter = [
            /*
             'where_in' => array(
             'posts.post_status' => array(
             PostType::DRAFT,
             PostType::PUBLICITY,
             PostType::PENDING,
             )
             ),
             */
            //'or_like' => $where_or_like,
            'order_by' => array(
                // trong sitemap thì order theo ID ASC để hạn chế việc post thì nhảy liên tục trong sitemap
                'posts.ID' => 'ASC',
                //'posts.menu_order' => 'DESC',
                //'posts.post_date' => 'DESC',
                //'post_modified' => 'DESC',
            ),
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 0,
            //'limit' => $post_per_page
        ];

        //
        $totalThread = $this->base_model->select('COUNT(ID) AS c', 'posts', $where, $filter);
        //print_r( $totalThread );
        $totalThread = $totalThread[0]['c'];

        //
        if ($get_count === true) {
            return $totalThread;
        }

        //print_r( $totalThread );
        $totalPage = ceil($totalThread / $this->limit_post_get);
        if ($totalPage < 1) {
            $totalPage = 1;
        }
        //echo $totalPage . '<br>' . PHP_EOL;
        if ($page_num > $totalPage) {
            $page_num = $totalPage;
        } else if ($page_num < 1) {
            $page_num = 1;
        }
        //echo $totalThread . '<br>' . PHP_EOL;
        //echo $totalPage . '<br>' . PHP_EOL;
        $offset = ($page_num - 1) * $this->limit_post_get;

        //
        $filter['offset'] = $offset;
        $filter['limit'] = $this->limit_post_get;
        $data = $this->base_model->select('*', 'posts', $where, $filter);
        //print_r( $data );

        //
        return $data;
    }

    private function by_post_type($post_type, $page_num = 1)
    {
        $data = $this->get_post_type($post_type, $page_num);
        //print_r( $data );

        //
        $get_list_sitemap = '';
        foreach ($data as $v) {
            $get_list_sitemap .= $this->WGR_echo_sitemap_url_node(
                $this->post_model->get_full_permalink($v),
                0.5,
                date($this->sitemap_date_format, strtotime($v['post_modified'])),
                array(
                    'get_images' => $v['ID'],
                )
            );
        }

        //
        return $this->WGR_echo_sitemap_urlset($get_list_sitemap);
    }

    private function WGR_echo_sitemap_urlset($get_list_sitemap)
    {
        echo $this->tmp(file_get_contents(__DIR__ . '/sitemap/urlset.xml', 1), [
            'get_list_sitemap' => $get_list_sitemap,
        ]);
        exit();
    }

    private function WGR_echo_sitemap_url_node($loc, $priority, $lastmod, $op = array())
    {
        return $this->tmp(file_get_contents(__DIR__ . '/sitemap/url.xml', 1), [
            'loc' => $loc,
            'priority' => $priority,
            'lastmod' => $lastmod,
        ]);
    }

    private function WGR_sitemap_part_page($count_post, $file_name = 'sitemap/post')
    {
        $str = '';

        $count_post_post = $count_post;
        //echo $type . ' --> ' . $count_post . '<br>' . PHP_EOL;

        if ($count_post_post > $this->limit_post_get) {
            $j = 0;
            for ($i = 2; $i < 100; $i++) {
                $j += $this->limit_post_get;

                if ($j < $count_post_post) {
                    // cho phần bài viết
                    $str .= $this->WGR_echo_sitemap_node($this->web_link . $file_name . '/page/' . $i, $this->sitemap_current_time);
                }
            }
        }

        // tạm thời ko lấy phần sitemap ảnh ở đây
        return $str;
    }

    //
    private function WGR_echo_sitemap_css()
    {
        header("Content-type: text/xml");
        //die( __CLASS__ . ':' . __LINE__ );

        //
        $main_sitemap_xsl = PUBLIC_HTML_PATH . 'public/css/main-sitemap.xsl';
        $cache_sitemap_xsl = PUBLIC_HTML_PATH . 'public/upload/main-sitemap.xsl';

        // thay thế nội dung trong sitemap thành của partner
        if ($this->base_model->scache(__FUNCTION__) === NULL) {
            $c = file_get_contents($main_sitemap_xsl, 1);
            $arr_replace_xsl = [
                '%partner_website%' => PARTNER_WEBSITE,
                '%partner_brand_name%' => PARTNER_BRAND_NAME,
                '%partner2_website%' => PARTNER2_WEBSITE,
                '%partner2_brand_name%' => PARTNER2_BRAND_NAME,
            ];
            $has_replace = false;
            foreach ($arr_replace_xsl as $k => $v) {
                if (strpos($c, $k) !== false) {
                    $c = str_replace($k, $v, $c);
                    $has_replace = true;
                }
            }
            if (!file_exists($cache_sitemap_xsl) || $has_replace === true) {
                $this->base_model->ftp_create_file($cache_sitemap_xsl, $c);
            }

            //
            $this->base_model->scache(__FUNCTION__, time(), 3600);
        }

        //
        echo $this->tmp(file_get_contents(__DIR__ . '/sitemap/css.xml', 1), [
            'base_url' => DYNAMIC_BASE_URL,
            'filemtime_main_sitemap' => filemtime($main_sitemap_xsl),
        ]);
    }

    private function WGR_echo_sitemap_node($loc, $lastmod)
    {
        return $this->tmp(file_get_contents(__DIR__ . '/sitemap/sitemap_node.xml', 1), [
            'loc' => $loc,
            'lastmod' => $lastmod,
        ]);
    }

    private function tmp($html, $arr)
    {
        foreach ($arr as $k => $v) {
            $html = str_replace('%' . $k . '%', $v, $html);
        }
        return $html;
    }

    private function media_total($post_type)
    {
        return $this->base_model->select('ID', 'posts', [
            'post_type' => $post_type,
            'post_parent >' => 0,
            'post_status' => PostType::INHERIT,
        ], [
            /*
                 'group_by' => array(
                 'post_parent',
                 ),
                 */
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            'getNumRows' => 1,
            //'offset' => 0,
            'limit' => -1
        ]);
    }

    private function media_sitemap($post_type, $page_num)
    {
        $get_list_sitemap = '';

        //
        $totalThread = $this->media_total($post_type);
        //print_r( $totalThread );

        //
        if ($totalThread > 0) {
            $totalPage = ceil($totalThread / $this->limit_post_get);
            if ($totalPage < 1) {
                $totalPage = 1;
            }
            //echo $totalPage . '<br>' . PHP_EOL;
            if ($page_num > $totalPage) {
                $page_num = $totalPage;
            } else if ($page_num < 1) {
                $page_num = 1;
            }
            //echo $totalThread . '<br>' . PHP_EOL;
            //echo $totalPage . '<br>' . PHP_EOL;
            $offset = ($page_num - 1) * $this->limit_post_get;
            //echo $offset . '<br>' . PHP_EOL;

            //
            $data = $this->base_model->select('post_title, post_type, post_parent, guid, post_meta_data', 'posts', [
                'post_type' => $post_type,
                'post_parent >' => 0,
                'post_status' => PostType::INHERIT,
            ], [
                /*
                     'group_by' => array(
                     'post_parent',
                     ),
                     */
                'order_by' => array(
                    'post_parent' => 'ASC',
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'getNumRows' => 1,
                'offset' => $offset,
                'limit' => $this->limit_post_get
                //'limit' => 50
            ]);
            //print_r( $data );

            //
            $parent_data = NULL;
            $parent_id = 0;
            $get_list_img_sitemap = '';
            foreach ($data as $v) {
                //print_r( $v );

                // lấy thông tin bài viết mỗi khi ID có sự thay đổi
                if ($v['post_parent'] != $parent_id) {
                    // gán dữ liệu của vòng lặp cũ nếu có
                    if (!empty($parent_data)) {
                        $get_list_sitemap .= '
<url>
<loc><![CDATA[' . $this->post_model->get_full_permalink($parent_data) . ']]></loc>' . $get_list_img_sitemap . '
</url>';

                        // reset list ảnh
                        $get_list_img_sitemap = '';
                    }

                    //
                    $parent_data = $this->base_model->select('ID, post_name, post_type, post_name', 'posts', [
                        'ID' => $v['post_parent'],
                        'post_status' => PostType::PUBLICITY,
                    ], [
                        /*
                             'group_by' => array(
                             'post_parent',
                             ),
                             */
                        /*
                             'order_by' => array(
                             'post_parent' => 'ASC',
                             ),
                             */
                        // hiển thị mã SQL để check
                        //'show_query' => 1,
                        // trả về câu query để sử dụng cho mục đích khác
                        //'get_query' => 1,
                        //'getNumRows' => 1,
                        //'offset' => $offset,
                        'limit' => 1
                    ]);
                    //print_r( $parent_data );
                }
                $parent_id = $v['post_parent'];

                //
                $post_meta_data = json_decode($v['post_meta_data']);
                //print_r( $post_meta_data );

                // URL ảnh
                if ($v['post_type'] == PostType::WP_MEDIA) {
                    $v['guid'] = PostType::WP_MEDIA_URI . $post_meta_data->_wp_attached_file;
                } else {
                    $v['guid'] = PostType::MEDIA_URI . $post_meta_data->_wp_attached_file;
                }

                // danh sách ảnh
                $get_list_img_sitemap .= '
<image:image>
	<image:loc><![CDATA[' . DYNAMIC_BASE_URL . $v['guid'] . ']]></image:loc>
	<image:title><![CDATA[' . $v['post_title'] . ']]></image:title>
</image:image>';
            }

            // bổ sung dữ liệu của vòng lặp cuối nếu nó chưa được xử lý
            if ($get_list_img_sitemap != '' && !empty($parent_data)) {
                $get_list_sitemap .= '
<url>
<loc><![CDATA[' . $this->post_model->get_full_permalink($parent_data) . ']]></loc>' . $get_list_img_sitemap . '
</url>';
            }
        }

        //
        echo '
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
' . $get_list_sitemap . '
</urlset>';

        //
        exit();
    }
}
