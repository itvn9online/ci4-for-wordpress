<?php
//require_once __DIR__ . '/Layout.php';
namespace App\ Controllers;

// Libraries
use App\ Libraries\ PostType;
use App\ Libraries\ TaxonomyType;

//
class Posts extends Csrf {
    public function __construct() {
        parent::__construct();
    }

    public function index( $id, $slug, $post_type, $taxonomy, $file_view = 'post_view' ) {
        //echo $id . ' <br>' . "\n";
        //echo $slug . ' <br>' . "\n";
        //echo 'post details <br>' . "\n";

        //
        $this->cache_key = 'post' . $id;
        $cache_value = $this->MY_cache( $this->cache_key );
        // Will get the cache entry named 'my_foo'
        //var_dump( $cache_value );
        // không có cache thì tiếp tục
        if ( !$cache_value ) {
            //echo '<!-- no cache -->';
        }
        // có thì in ra cache là được
        else {
            return $this->show_cache( $cache_value );
        }

        //
        $data = $this->post_model->select_public_post( $id, [
            //'post_name' => $slug_1,
            'post_type' => $post_type,
        ] );
        if ( empty( $data ) ) {
            //print_r( $data );
            return $this->page404('ERROR ' . strtolower( __FUNCTION__ ) . ':' . __LINE__ . '! Không xác định được dữ liệu bài viết...');
        }

        // update lượt xem -> daidq (2021-12-14): chuyển phần update này qua view, ai thích dùng thì kích hoạt cho nó nhẹ
        //$this->post_model->update_views( $data[ 'ID' ] );

        //
        $data[ 'post_content' ] = $this->replace_content( $data[ 'post_content' ] );
        //print_r( $data );

        // lấy thông tin danh mục để tạo breadcrumb
        $cats = [];
        if ( isset( $data[ 'post_meta' ][ 'post_category' ] ) ) {
            $post_category = explode( ',', $data[ 'post_meta' ][ 'post_category' ] );
            $post_category = $post_category[ 0 ];

            //
            if ( $post_category > 0 ) {
                $cats = $this->term_model->get_all_taxonomy( $taxonomy, $post_category );
                //print_r( $cats );

                if ( !empty( $cats ) ) {
                    $this->create_term_breadcrumb( $cats );
                }
            }
        }
        //print_r( $this->taxonomy_slider );
        //print_r( $this->posts_parent_list );
        //print_r( $data );

        // tìm các bài cùng nhóm
        $same_cat_data = [];
        $config_key = 'eb_post_per_page';
        if ( $post_type == PostType::BLOG ) {
            $config_key = 'eb_blog_per_page';
        }
        $post_per_page = $this->base_model->get_config( $this->getconfig, $config_key, 5 );
        if ( $post_per_page > 0 && isset( $data[ 'post_meta' ][ 'post_category' ] ) && $data[ 'post_meta' ][ 'post_category' ] > 0 ) {
            $same_cat_data = $this->post_model->select_list_post( $post_type, [
                'term_id' => $data[ 'post_meta' ][ 'post_category' ],
                'taxonomy' => $taxonomy,
            ], $post_per_page );
            //print_r( $same_cat_data );
        }

        // nếu có post cha -> lấy cả thông tin post cha
        $parent_data = [];
        if ( $data[ 'post_parent' ] > 0 ) {
            $parent_data = $this->base_model->select( '*', 'wp_posts', array(
                // các kiểu điều kiện where
                'ID' => $data[ 'post_parent' ],
                'post_status' => PostType::PUBLIC
            ), array(
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 2,
                'limit' => 1
            ) );
            //print_r( $parent_data );
            $this->create_breadcrumb( $parent_data[ 'post_title' ], $this->post_model->get_the_permalink( $parent_data ) );
        }

        //
        $post_permalink = $this->post_model->get_the_permalink( $data );
        $this->create_breadcrumb( $data[ 'post_title' ], $post_permalink );
        $seo = $this->base_model->seo( $data, $post_permalink );

        // -> views
        $this->teamplate[ 'breadcrumb' ] = view( 'breadcrumb_view', array(
            'breadcrumb' => $this->breadcrumb
        ) );

        $this->teamplate[ 'main' ] = view( $file_view, array(
            'taxonomy_slider' => $this->taxonomy_slider,
            'taxonomy_post_size' => $this->taxonomy_post_size,
            'same_cat_data' => $same_cat_data,
            'seo' => $seo,
            'data' => $data,
        ) );
        $cache_value = view( 'layout_view', $this->teamplate );

        // Save into the cache for 5 minutes
        $cache_save = $this->MY_cache( $this->cache_key, $cache_value );
        //var_dump( $cache_save );

        //
        return $cache_value;
    }

    public function post_details( $id, $slug ) {
        return $this->index( $id, $slug, PostType::POST, TaxonomyType::POSTS );
    }
}