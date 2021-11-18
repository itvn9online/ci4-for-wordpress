<?php
//require_once __DIR__ . '/Layout.php';
namespace App\ Controllers;

// Libraries
use App\ Libraries\ PostType;
use App\ Libraries\ TaxonomyType;

//
class Posts extends Layout {
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
            return $this->page404();
        }
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

                $this->create_term_breadcrumb( $cats );
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

        //
        $this->create_breadcrumb( $data[ 'post_title' ] );
        $seo = $this->base_model->seo( $data, $this->post_model->get_the_permalink( $data ) );

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