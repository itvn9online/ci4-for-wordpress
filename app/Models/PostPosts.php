<?php

namespace App\Models;

// Libraries
use App\Libraries\LanguageCost;
use App\Libraries\PostType;
use App\Libraries\TaxonomyType;

//
// class PostPosts extends PostSlider
class PostPosts extends PostGet
{
    protected $currency_sd_format = 'USD';

    public function __construct()
    {
        parent::__construct();

        // 
        if (!empty($this->getconfig->currency_sd_format)) {
            $this->currency_sd_format = $this->getconfig->currency_sd_format;
        }
    }

    // trả về khối HTML của từng post trong danh mục -> dùng để tùy chỉnh khối HTML
    public function build_the_node($data, $tmp_html, $ops = [], $default_arr = [])
    {
        // if (isset($_GET['aaaaaaaaaa'])) {
        //     print_r($data);
        // }
        //echo $tmp_html . '<br>' . PHP_EOL;

        //
        $data['p_link'] = $this->get_full_permalink($data);
        if (isset($ops['taxonomy_post_size']) && $ops['taxonomy_post_size'] != '') {
            $data['cf_posts_size'] = $ops['taxonomy_post_size'];
        } else {
            $data['cf_posts_size'] = $this->getconfig->cf_posts_size;
        }
        $data['cf_products_size'] = $this->getconfig->cf_products_size;
        $data['product_status'] = 1;
        $data['dynamic_title_tag'] = 'h3';
        $data['trv_img'] = $this->get_post_thumbnail($data);
        //$data['trv_webp'] = $this->get_post_image($data, 'image_webp', '');
        if ($data['post_excerpt'] == '' && isset($data['post_content'])) {
            $data['post_excerpt'] = trim(str_replace('&nbsp;', ' ', strip_tags($data['post_content'])));
            $data['post_excerpt'] = $this->base_model->short_string($data['post_excerpt'], 168);
        }
        $data['post_quot_title'] = str_replace('"', '', $data['post_title']);

        //
        $itemprop_logo = '';
        $itemprop_author = '';
        $itemprop_image = '';
        if ($data['trv_img'] != '' && is_file(PUBLIC_PUBLIC_PATH . $data['trv_img'])) {
            $itemprop_logo = $this->itempropLogoHtmlNode;
            $itemprop_author = $this->itempropAuthorHtmlNode;
            $itemprop_image = $this->itempropImageHtmlNode;

            //
            $logo_data = getimagesize(PUBLIC_PUBLIC_PATH . $data['trv_img']);

            //
            $itemprop_image = str_replace('{{image}}', DYNAMIC_BASE_URL . $data['trv_img'], $itemprop_image);
            $itemprop_image = str_replace('{{trv_width_img}}', $logo_data[0], $itemprop_image);
            $itemprop_image = str_replace('{{trv_height_img}}', $logo_data[1], $itemprop_image);
        }
        $data['image'] = $data['trv_img'];
        $data['itemprop_logo'] = $itemprop_logo;
        $data['itemprop_author'] = $itemprop_author;
        $data['itemprop_image'] = $itemprop_image;

        // gán các giá trị mặc định phòng trường hợp không có dữ liệu tương ứng
        $default_arr['price'] = '';
        $default_arr['price_sale'] = '';
        $default_arr['highPrice'] = '0';
        $default_arr['lowPrice'] = '0';
        $default_arr['offerCount'] = '999';
        if (isset($data['post_meta'])) {
            if (isset($data['post_meta']['_regular_price']) && !empty($data['post_meta']['_regular_price'])) {
                $default_arr['highPrice'] = $data['post_meta']['_regular_price'];
                $default_arr['lowPrice'] = $data['post_meta']['_regular_price'];
            }
            if (isset($data['post_meta']['_sale_price']) && !empty($data['post_meta']['_sale_price'])) {
                $default_arr['lowPrice'] = $data['post_meta']['_sale_price'];
            }
        }
        if (isset($data['post_viewed']) && !empty($data['post_viewed'])) {
            $default_arr['offerCount'] = $data['post_viewed'];
        }
        $default_arr['pt'] = '';
        $default_arr['image_medium_large'] = '';
        $default_arr['image_webp'] = '';
        $default_arr['currency_sd_format'] = $this->currency_sd_format;
        $default_arr['current_year'] = date('Y');

        //
        //print_r($data);

        //
        return $this->base_model->tmp_to_html($tmp_html, $data, $default_arr);
    }

    // trả về khối HTML của từng bài viết trong danh mục
    public function get_the_node($data, $ops = [], $default_arr = [])
    {
        // nếu không có col HTML riêng -> dùng col HTML mặc định
        if (!isset($ops['custom_html']) || $ops['custom_html'] == '') {
            $ops['custom_html'] = $this->blog_html_node;
        }

        //
        // $data['dynamic_post_tag'] = 'h3';
        // $data['show_post_content'] = '';
        // $data['blog_link_option'] = '';
        // $data['taxonomy_key'] = '';
        // $data['url_video'] = '';
        if (isset($ops['taxonomy_post_size']) && $ops['taxonomy_post_size'] != '') {
            $data['cf_posts_size'] = $ops['taxonomy_post_size'];
        } else {
            $data['cf_posts_size'] = $this->getconfig->cf_posts_size;
        }
        //echo $data['cf_posts_size'];

        //
        return $this->build_the_node($data, $ops['custom_html'], $ops, $default_arr);
    }
    public function the_node($data, $ops = [], $default_arr = [])
    {
        echo $this->get_the_node($data, $ops, $default_arr);
    }

    // trả về khối HTML của từng bài viết trong danh mục
    function get_the_blog_node($data, $ops = [], $default_arr = [])
    {
        return $this->get_the_node($data, $ops, $default_arr);
    }
    function the_blog_node($data, $ops = [], $default_arr = [])
    {
        echo $this->get_the_node($data, $ops, $default_arr);
    }

    // trả về khối HTML của từng sản phẩm trong danh mục
    public function get_the_product_node($data, $ops = [], $default_arr = [])
    {
        $default_arr['_regular_price'] = '';
        // nếu không có col HTML riêng -> dùng col HTML mặc định
        if (!isset($ops['custom_html']) || $ops['custom_html'] == '') {
            $ops['custom_html'] = $this->product_html_node;
        }

        //
        return $this->build_the_node($data, $ops['custom_html'], $ops, $default_arr);
    }
    public function the_product_node($data, $ops = [], $default_arr = [])
    {
        echo $this->get_the_product_node($data, $ops, $default_arr);
    }

    /**
     * undocumented function summary
     * https://github.com/itvn9online/ci4-for-wordpress#function-l%E1%BA%A5y-danh-s%C3%A1ch-s%E1%BA%A3n-ph%E1%BA%A9m-theo-danh-m%E1%BB%A5c
     **/
    public function get_posts_by($prams, $ops = [], $in_cache = '', $cache_time = 300)
    {
        $prams = $this->sync_post_parms($prams);
        $ops = $this->sync_post_ops($ops);
        //print_r($ops);

        // fix cứng tham số
        $prams['post_type'] = PostType::POST;
        $prams['taxonomy'] = TaxonomyType::POSTS;

        //
        return $this->get_posts($prams, $ops, $in_cache, $cache_time);
    }
    public function count_posts_by($prams, $ops = [])
    {
        $prams = $this->sync_post_parms($prams);
        //print_r($prams);

        // fix cứng tham số
        $prams['post_type'] = PostType::POST;
        $prams['taxonomy'] = TaxonomyType::POSTS;

        //
        $ops['count_record'] = 1;

        //
        return $this->get_posts($prams, $ops);
    }

    /**
     * Thêm tham số session cho việc xác thực quảng cáo nếu có
     **/
    public function the_session_ads($data)
    {
        $data = str_replace('{session_id}', $this->base_model->MY_sessid(), $data);
        $data = str_replace('{csrf_hash}', csrf_hash(), $data);
        return $data;
    }

    function get_the_ads($slug, $limit = 0, $ops = [], $using_cache = true, $time = MEDIUM_CACHE_TIMEOUT)
    {
        $in_cache = '';
        if ($using_cache === true) {
            $in_cache = __FUNCTION__ . '-' . $slug . '-';
            //$in_cache .= LanguageCost::lang_key();
            $in_cache .= $this->base_model->lang_key;
        }
        // $in_cache = '';

        //
        if ($in_cache != '') {
            //echo $in_cache . '<br>' . PHP_EOL;
            $cache_value = $this->base_model->scache($in_cache);

            // có cache thì trả về
            if ($cache_value !== null) {
                // print_r($cache_value);
                if (isset($ops['return_object'])) {
                    return $cache_value;
                }
                return $this->the_session_ads($cache_value);
            }
        }

        //
        $ops['post_type'] = PostType::ADS;
        $ops['taxonomy'] = TaxonomyType::ADS;
        $ops['limit'] = $limit;
        // nhân bản sang các ngôn ngữ khác
        $ops['auto_clone'] = 1;

        //
        //echo  __CLASS__ . ':' . __LINE__ . ':' . debug_backtrace()[1]['class'] . ':' . debug_backtrace()[1]['function'] . '<br>' . PHP_EOL;
        $data = $this->echbay_blog($slug, $ops);

        //
        if ($in_cache != '') {
            $this->base_model->scache($in_cache, $data, $time);
        }
        if (isset($ops['return_object'])) {
            return $data;
        }
        return $this->the_session_ads($data);
    }

    function the_ads($slug, $limit = 0, $ops = [], $using_cache = true, $time = MEDIUM_CACHE_TIMEOUT)
    {
        $data = $this->get_the_ads($slug, $limit, $ops, $using_cache, $time);
        if (isset($ops['return_object'])) {
            return $data;
        }
        echo $data;
    }

    // ads
    public function get_adss_by($prams, $ops = [])
    {
        $prams = $this->sync_post_parms($prams);
        $ops = $this->sync_post_ops($ops);

        // riêng với mục ads -> ưu tiên sử dụng slug để có thể tạo tụ động nhóm nếu có
        if (isset($prams['slug']) && $prams['slug'] != '') {
            if (!isset($prams['limit'])) {
                $prams['limit'] = isset($ops['limit']) ? $ops['limit'] : 0;
            }
            //print_r( $prams );

            // gọi tới hàm với tham số return_object để nó trả về dữ liệu luôn
            $data = $this->get_the_ads($prams['slug'], $prams['limit'], [
                'return_object' => 1
            ]);
            //print_r( $data );

            //
            return $data;
        }

        // fix cứng tham số
        $prams['post_type'] = PostType::ADS;
        $prams['taxonomy'] = TaxonomyType::ADS;

        // còn lại sẽ sử dụng term_id
        return $this->get_posts($prams, $ops);
    }
    public function count_adss_by($prams, $ops = [])
    {
        $prams = $this->sync_post_parms($prams);

        // fix cứng tham số
        $prams['post_type'] = PostType::ADS;
        $prams['taxonomy'] = TaxonomyType::ADS;

        //
        $ops['count_record'] = 1;

        //
        return $this->get_posts($prams, $ops);
    }

    // tính tổng bài viết của các loại taxonomy khác
    public function count_others_by($prams, $post_type, $ops = [])
    {
        $prams = $this->sync_post_parms($prams);
        if (!isset($prams['taxonomy'])) {
            return -1;
        }

        // fix cứng tham số
        $prams['post_type'] = $post_type;

        //
        $ops['count_record'] = 1;

        //
        return $this->get_posts($prams, $ops);
    }

    // tính lại tổng bài viết của các term bị sai thống kê
    public function fix_term_count($prams, $post_type, $ops = [])
    {
        //print_r($prams);
        // ko có tham số này -> LỖI -> báo lỗi
        if (!isset($prams['child_last_count'])) {
            echo 'child_last_count not found in ' . basename(__FILE__) . ':' . __LINE__ . '<br>' . PHP_EOL;
            return 0;
        }
        // nếu mới tổng thì bỏ qua
        else if ($prams['child_last_count'] > time()) {
            return $prams['count'];
        }
        //echo time() - $prams['child_last_count'] . '<br>' . PHP_EOL;

        //
        $count = $this->post_category($post_type, $prams, [
            'selectCount' => 'ID',
            //'show_query' => 1,
            //'offset' => 0,
            'limit' => -1,
            'group_by' => [
                'posts.post_type'
            ]
        ]);
        //print_r($count);
        if (empty($count)) {
            return 0;
        }
        $count = $count[0]['ID'];

        //print_r($prams);
        //echo $post_type . '<br>' . PHP_EOL;
        //print_r($ops);
        //$count = $this->count_others_by($prams, $post_type, $ops);
        //echo $count . '<br>' . PHP_EOL;
        //return $count;
        //die('count: ' . $count);

        //
        $source_count = [];
        $debug_backtrace = debug_backtrace()[1];
        if (isset($debug_backtrace['class'])) {
            $source_count[] = $debug_backtrace['class'];
        }
        if (isset($debug_backtrace['function'])) {
            $source_count[] = $debug_backtrace['function'];
        }
        $source_count[] = __CLASS__ . ':' . __FUNCTION__ . ':' . __LINE__;
        $source_count[] = date('r');

        //
        $this->base_model->update_multiple($this->term_model->taxTable, [
            'count' => $count,
            'source_count' => json_encode($source_count),
        ], [
            'term_taxonomy_id' => $prams['term_id'],
            'term_id' => $prams['term_id'],
        ], [
            'debug_backtrace' => debug_backtrace()[1]['function'],
            // hiển thị mã SQL để check
            //'show_query' => 1,
        ]);

        // dọn dẹp cache
        $this->base_model->dcache($this->key_cache($prams['term_id']));

        //
        return $count;
    }

    // trả về dữ liệu cho phần post category
    public function post_category($post_type, $data, $ops = [])
    {
        $where = [
            // $this->table . '.post_type' => $post_type,
            $this->table . '.post_status' => PostType::PUBLICITY,
            $this->table . '.lang_key' => LanguageCost::lang_key()
        ];
        if ($post_type != '') {
            $where[$this->table . '.post_type'] = $post_type;
        }

        //
        $filter = [
            'join' => [
                'term_relationships' => 'term_relationships.object_id = ' . $this->table . '.ID',
                'term_taxonomy' => 'term_relationships.term_taxonomy_id = term_taxonomy.term_taxonomy_id',
            ],
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            'group_by' => array(
                $this->table . '.ID',
            ),
            //'offset' => $offset,
            //'limit' => $post_per_page
        ];

        //
        foreach ($ops as $k => $v) {
            $filter[$k] = $v;
        }

        // nếu không có cha -> chỉ cần lấy theo ID nhóm hiện tại là được
        if (empty($data['child_term'])) {
            $where['term_taxonomy.term_id'] = $data['term_id'];
        }
        // nếu có -> lấy theo cả cha và con
        else {
            $where_in = [$data['term_id']];
            foreach ($data['child_term'] as $v) {
                $where_in[] = $v['term_id'];
            }

            //
            $filter['where_in'] = [
                'term_taxonomy.term_id' => $where_in
            ];
        }

        //
        $filter['order_by'] = [
            //$this->table . '.post_modified' => 'DESC',
            $this->table . '.menu_order' => 'DESC',
            $this->table . '.time_order' => 'DESC',
            $this->table . '.ID' => 'DESC',
        ];

        //
        return $this->base_model->select('*', $this->table, $where, $filter);
    }
}
