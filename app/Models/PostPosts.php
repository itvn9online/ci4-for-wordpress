<?php
namespace App\ Models;

// Libraries
use App\ Libraries\ LanguageCost;
use App\ Libraries\ PostType;
use App\ Libraries\ TaxonomyType;

//
class PostPosts extends PostSlider {
    public function __construct() {
        parent::__construct();
    }

    // trả về khối HTML của từng sản phẩm trong danh mục
    function get_the_node( $data, $ops = [], $default_arr = [] ) {
        //print_r( $data );
        $tmp_html = $this->product_html_node;
        //echo $tmp_html . '<br>' . "\n";

        //
        $data[ 'p_link' ] = $this->get_the_permalink( $data );
        if ( isset( $ops[ 'taxonomy_post_size' ] ) && $ops[ 'taxonomy_post_size' ] != '' ) {
            $data[ 'cf_product_size' ] = $ops[ 'taxonomy_post_size' ];
        } else {
            $data[ 'cf_product_size' ] = $this->getconfig->cf_product_size;
        }
        $data[ 'trv_num_giamoi' ] = 0;
        $data[ 'product_status' ] = 1;
        $data[ 'dynamic_title_tag' ] = 'h3';
        $data[ 'pt' ] = 0;
        $data[ 'trv_img' ] = $this->get_post_thumbnail( $data );
        $data[ 'trv_webp' ] = $this->get_post_image( $data, 'image_webp', '' );
        if ( $data[ 'post_excerpt' ] == '' ) {
            $data[ 'post_excerpt' ] = strip_tags( $data[ 'post_content' ] );
            $data[ 'post_excerpt' ] = $this->base_model->short_string( $data[ 'post_excerpt' ], 168 );
        }
        $data[ 'post_quot_title' ] = str_replace( '"', '', $data[ 'post_title' ] );

        //
        $itemprop_logo = '';
        $itemprop_author = '';
        $itemprop_image = '';
        if ( $data[ 'trv_img' ] != '' && file_exists( PUBLIC_PUBLIC_PATH . $data[ 'trv_img' ] ) ) {
            $itemprop_logo = $this->itempropLogoHtmlNode;
            $itemprop_author = $this->itempropAuthorHtmlNode;
            $itemprop_image = $this->itempropImageHtmlNode;

            //
            $logo_data = getimagesize( PUBLIC_PUBLIC_PATH . $data[ 'trv_img' ] );

            //
            $itemprop_image = str_replace( '{{trv_img}}', DYNAMIC_BASE_URL . $data[ 'trv_img' ], $itemprop_image );
            $itemprop_image = str_replace( '{{trv_width_img}}', $logo_data[ 0 ], $itemprop_image );
            $itemprop_image = str_replace( '{{trv_height_img}}', $logo_data[ 1 ], $itemprop_image );
        }
        $data[ 'itemprop_logo' ] = $itemprop_logo;
        $data[ 'itemprop_author' ] = $itemprop_author;
        $data[ 'itemprop_image' ] = $itemprop_image;

        //
        return $this->base_model->tmp_to_html( $tmp_html, $data, $default_arr );
    }

    function the_node( $data, $ops = [], $default_arr = [] ) {
        echo $this->get_the_node( $data, $ops, $default_arr );
    }

    // post
    public function get_posts_by( $prams, $ops = [] ) {
        $prams = $this->sync_post_parms( $prams );
        $ops = $this->sync_post_ops( $ops );

        // fix cứng tham số
        $prams[ 'post_type' ] = PostType::POST;
        $prams[ 'taxonomy' ] = TaxonomyType::POSTS;

        //
        return $this->get_posts( $prams, $ops );
    }
    public function count_posts_by( $prams, $ops = [] ) {
        $prams = $this->sync_post_parms( $prams );

        // fix cứng tham số
        $prams[ 'post_type' ] = PostType::POST;
        $prams[ 'taxonomy' ] = TaxonomyType::POSTS;

        //
        $ops[ 'count_record' ] = 1;

        //
        return $this->get_posts( $prams, $ops );
    }

    // trả về khối HTML của từng sản phẩm trong danh mục
    function get_the_blog_node( $data, $ops = [] ) {
        //print_r( $data );
        if ( isset( $ops[ 'tmp_html' ] ) && $ops[ 'tmp_html' ] != '' ) {
            $tmp_html = $ops[ 'tmp_html' ];
        } else {
            $tmp_html = $this->blog_html_node;
        }
        //echo $tmp_html . '<br>' . "\n";

        //
        $data[ 'p_link' ] = $this->get_the_permalink( $data );
        if ( isset( $ops[ 'taxonomy_post_size' ] ) && $ops[ 'taxonomy_post_size' ] != '' ) {
            $data[ 'cf_blog_size' ] = $ops[ 'taxonomy_post_size' ];
        } else {
            $data[ 'cf_blog_size' ] = $this->getconfig->cf_blog_size;
        }
        $data[ 'post_category' ] = 0;
        $data[ 'taxonomy_key' ] = '';
        $data[ 'dynamic_post_tag' ] = 'h3';
        $data[ 'blog_link_option' ] = '';
        $data[ 'image' ] = $this->get_post_thumbnail( $data );

        if ( $data[ 'post_excerpt' ] == '' ) {
            $data[ 'post_excerpt' ] = strip_tags( $data[ 'post_content' ] );
            //echo $this->getconfig->cf_blog_description_length . ' aaaaaaaaaa <br>' . "\n";
            $data[ 'post_excerpt' ] = $this->base_model->short_string( $data[ 'post_excerpt' ], $this->getconfig->cf_blog_description_length );
        }

        //
        return $this->base_model->tmp_to_html( $tmp_html, $data );
    }

    function the_blog_node( $data, $ops = [] ) {
        echo $this->get_the_blog_node( $data, $ops );
    }

    // blog
    public function get_blogs_by( $prams, $ops = [] ) {
        $prams = $this->sync_post_parms( $prams );
        $ops = $this->sync_post_ops( $ops );

        // fix cứng tham số
        $prams[ 'post_type' ] = PostType::BLOG;
        $prams[ 'taxonomy' ] = TaxonomyType::BLOGS;

        //
        return $this->get_posts( $prams, $ops );
    }
    public function count_blogs_by( $prams, $ops = [] ) {
        $prams = $this->sync_post_parms( $prams );

        // fix cứng tham số
        $prams[ 'post_type' ] = PostType::BLOG;
        $prams[ 'taxonomy' ] = TaxonomyType::BLOGS;

        //
        $ops[ 'count_record' ] = 1;

        //
        return $this->get_posts( $prams, $ops );
    }

    function get_the_ads( $slug, $limit = 0, $ops = [], $using_cache = true, $time = MEDIUM_CACHE_TIMEOUT ) {
        $in_cache = '';
        if ( $using_cache === true ) {
            $in_cache = __FUNCTION__ . '-' . $slug . '-' . LanguageCost::lang_key();
        }

        //
        if ( $in_cache != '' ) {
            $cache_value = $this->base_model->scache( $in_cache );

            // có cache thì trả về
            if ( $cache_value !== NULL ) {
                //print_r( $cache_value );
                return $cache_value;
            }
        }

        //
        $ops[ 'post_type' ] = PostType::ADS;
        $ops[ 'taxonomy' ] = TaxonomyType::ADS;
        $ops[ 'limit' ] = $limit;
        // nhân bản sang các ngôn ngữ khác
        $ops[ 'auto_clone' ] = 1;

        //
        $data = $this->echbay_blog( $slug, $ops );

        //
        if ( $in_cache != '' ) {
            $this->base_model->scache( $in_cache, $data, $time );
        }
        return $data;
    }

    function the_ads( $slug, $limit = 0, $ops = [], $using_cache = true, $time = MEDIUM_CACHE_TIMEOUT ) {
        echo $this->get_the_ads( $slug, $limit, $ops, $using_cache, $time );
    }

    // ads
    public function get_adss_by( $prams, $ops = [] ) {
        $prams = $this->sync_post_parms( $prams );
        $ops = $this->sync_post_ops( $ops );

        // riêng với mục ads -> ưu tiên sử dụng slug để có thể tạo tụ động nhóm nếu có
        if ( isset( $prams[ 'slug' ] ) && $prams[ 'slug' ] != '' ) {
            if ( !isset( $prams[ 'limit' ] ) ) {
                $prams[ 'limit' ] = isset( $ops[ 'limit' ] ) ? $ops[ 'limit' ] : 0;
            }
            //print_r( $prams );

            // gọi tới hàm với tham số return_object để nó trả về dữ liệu luôn
            $data = $this->get_the_ads( $prams[ 'slug' ], $prams[ 'limit' ], [
                'return_object' => 1
            ] );
            //print_r( $data );

            //
            return $data;
        }

        // fix cứng tham số
        $prams[ 'post_type' ] = PostType::ADS;
        $prams[ 'taxonomy' ] = TaxonomyType::ADS;

        // còn lại sẽ sử dụng term_id
        return $this->get_posts( $prams, $ops );
    }
    public function count_adss_by( $prams, $ops = [] ) {
        $prams = $this->sync_post_parms( $prams );

        // fix cứng tham số
        $prams[ 'post_type' ] = PostType::ADS;
        $prams[ 'taxonomy' ] = TaxonomyType::ADS;

        //
        $ops[ 'count_record' ] = 1;

        //
        return $this->get_posts( $prams, $ops );
    }

    // trả về dữ liệu cho phần post category
    public function post_category( $post_type, $data, $ops = [] ) {
        $where = [
            $this->table . '.post_type' => $post_type,
            $this->table . '.post_status' => PostType::PUBLICITY,
            $this->table . '.lang_key' => LanguageCost::lang_key()
        ];

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
        foreach ( $ops as $k => $v ) {
            $filter[ $k ] = $v;
        }

        // nếu không có cha -> chỉ cần lấy theo ID nhóm hiện tại là được
        if ( empty( $data[ 'child_term' ] ) ) {
            $where[ 'term_taxonomy.term_id' ] = $data[ 'term_id' ];
        }
        // nếu có -> lấy theo cả cha và con
        else {
            $where_in = [];
            foreach ( $data[ 'child_term' ] as $v ) {
                $where_in[] = $v[ 'term_id' ];
            }

            //
            $filter[ 'where_in' ] = [
                'term_taxonomy.term_id' => $where_in
            ];
        }

        //
        $filter[ 'order_by' ] = [
            //$this->table . '.post_modified' => 'DESC',
            $this->table . '.menu_order' => 'DESC',
            $this->table . '.ID' => 'DESC',
        ];

        //
        return $this->base_model->select( '*', $this->table, $where, $filter );
    }
}