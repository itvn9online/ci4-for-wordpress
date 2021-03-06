<?php
namespace App\ Controllers;

// Libraries
use App\ Libraries\ PostType;
use App\ Libraries\ TaxonomyType;

//
class Posts extends Csrf {
    protected $post_type = PostType::POST;
    protected $taxonomy = TaxonomyType::POSTS;
    protected $file_view = 'post_view';

    public function __construct() {
        parent::__construct();
    }

    public function index( $id, $slug = '' ) {
        return $this->post_details( $id, $slug );
    }

    public function post_details( $id, $slug = '' ) {
        //echo $id . ' <br>' . "\n";
        //echo $slug . ' <br>' . "\n";
        //echo 'post details <br>' . "\n";

        //
        $this->cache_key = $this->post_model->key_cache( $id );
        $cache_value = $this->MY_cache( $this->cache_key );
        // Will get the cache entry named 'my_foo'
        //var_dump( $cache_value );
        // có thì in ra cache là được
        //if ( $_SERVER[ 'REQUEST_METHOD' ] == 'GET' && $cache_value !== NULL ) {
        if ( $this->hasFlashSession() === false && $cache_value !== NULL ) {
            return $this->show_cache( $cache_value );
        }

        //
        $data = $this->post_model->select_public_post( $id, [
            //'post_name' => $slug_1,
            'post_type' => $this->post_type,
        ] );
        if ( empty( $data ) ) {
            //print_r( $data );
            return $this->page404( 'ERROR ' . strtolower( __FUNCTION__ ) . ':' . __LINE__ . '! Không xác định được dữ liệu bài viết...' );
        }

        // kiểm tra quyền truy cập chi tiết 1 post
        if ( $this->post_permission( $data ) !== true ) {
            return $this->page404( $this->post_permission( $data ) );
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
                $cats = $this->term_model->get_all_taxonomy( $this->taxonomy, $post_category );
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
        if ( $this->post_type == PostType::BLOG ) {
            $config_key = 'eb_blog_per_page';
        }
        $post_per_page = $this->base_model->get_config( $this->getconfig, $config_key, 5 );
        if ( $post_per_page > 0 &&
            isset( $data[ 'post_meta' ][ 'post_category' ] ) &&
            !empty( $data[ 'post_meta' ][ 'post_category' ] ) ) {
            //print_r( $data[ 'post_meta' ][ 'post_category' ] );

            //
            /*
            $same_cat_data = $this->post_model->select_list_post( $this->post_type, [
                'term_id' => $data[ 'post_meta' ][ 'post_category' ],
                'taxonomy' => $this->taxonomy,
            ], $post_per_page );
            */

            //
            $arr_where_in = [
                'term_id' => explode( ',', $data[ 'post_meta' ][ 'post_category' ] )
            ];

            // lấy 1 bài phía trước
            $same_cat_data = $this->base_model->select( '*', WGR_POST_VIEW, [
                'ID >' => $data[ 'ID' ],
            ], [
                'where_in' => $arr_where_in,
                'order_by' => [
                    'menu_order' => 'ASC',
                    'ID' => 'ASC',
                ],
                //'get_sql' => 1,
                //'show_query' => 1,
                //'debug_only' => 1,
                //'offset' => 0,
                'limit' => 1
            ] );
            if ( !empty( $same_cat_data ) ) {
                $same_cat_data = $this->post_model->list_meta_post( [ $same_cat_data ] );
                //print_r( $same_cat_data );

                $post_per_page -= 1;
            }

            // sau đó là các bài phía sau
            if ( $post_per_page > 0 ) {
                $after_cat_data = $this->base_model->select( '*', WGR_POST_VIEW, [
                    'ID <' => $data[ 'ID' ],
                ], [
                    'where_in' => $arr_where_in,
                    'order_by' => [
                        'menu_order' => 'DESC',
                        'ID' => 'DESC',
                    ],
                    //'get_sql' => 1,
                    //'show_query' => 1,
                    //'debug_only' => 1,
                    //'offset' => 0,
                    'limit' => $post_per_page
                ] );
                if ( !empty( $after_cat_data ) ) {
                    $after_cat_data = $this->post_model->list_meta_post( $after_cat_data );

                    // gộp lại
                    foreach ( $after_cat_data as $after_data ) {
                        $same_cat_data[] = $after_data;
                    }
                }
            }
            //print_r( $same_cat_data );
        }

        // nếu có post cha -> lấy cả thông tin post cha
        $parent_data = [];
        if ( $data[ 'post_parent' ] > 0 ) {
            $parent_data = $this->base_model->select( '*', 'posts', array(
                // các kiểu điều kiện where
                'ID' => $data[ 'post_parent' ],
                'post_status' => PostType::PUBLICITY
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

        //
        $structured_data = $this->structuredData( $data, 'Article.html' );

        // -> views
        $this->teamplate[ 'breadcrumb' ] = view( 'breadcrumb_view', array(
            'breadcrumb' => $this->breadcrumb
        ) ) . $structured_data;

        $this->teamplate[ 'main' ] = view( $this->file_view, array(
            'taxonomy_slider' => $this->taxonomy_slider,
            'taxonomy_post_size' => $this->taxonomy_post_size,
            'same_cat_data' => $same_cat_data,
            'seo' => $seo,
            'data' => $data,
        ) );

        // nếu có flash session -> trả về view luôn
        if ( $this->hasFlashSession() === true ) {
            return view( 'layout_view', $this->teamplate );
        }
        // còn không sẽ tiến hành lưu cache
        $cache_value = view( 'layout_view', $this->teamplate );

        $cache_save = $this->MY_cache( $this->cache_key, $cache_value . '<!-- Served from: ' . __FUNCTION__ . ' -->' );
        //var_dump( $cache_save );

        //
        return $cache_value;
    }
}