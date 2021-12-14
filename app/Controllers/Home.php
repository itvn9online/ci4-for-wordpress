<?php
//require_once __DIR__ . '/Layout.php';
namespace App\ Controllers;

// Libraries
use App\ Libraries\ DeletedStatus;
use App\ Libraries\ TaxonomyType;
use App\ Libraries\ PostType;
use App\ Libraries\ LanguageCost;

//
class Home extends Layout {

    public function __construct() {
        parent::__construct();
    }

    /*
     * default page
     */
    public function index() {
        // dẫn tới trang post mặc định
        if ( isset( $_GET[ 'p' ], $_GET[ 'post_type' ] ) ) {
            return $this->autoDetails();
        }
        //
        else if ( isset( $_GET[ 'cat' ], $_GET[ 'taxonomy' ] ) ) {
            return $this->autoCategory();
        }
        // dẫn tới trang chủ
        return $this->portal();
    }

    /*
     * home page
     */
    protected function portal() {
        $this->cache_key = 'home';
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
        //print_r( $this->getconfig );
        $getconfig = $this->getconfig;

        //
        $seo = array(
            'index' => '1',
            'title' => $getconfig->title,
            'description' => $getconfig->description,
            'keyword' => $getconfig->keyword,
            'name' => $getconfig->name,
            'canonical' => DYNAMIC_BASE_URL,
            'body_class' => 'home',
            //'google_analytics' => $getconfig->google_analytics,
        );

        $this->teamplate[ 'main' ] = view( 'home_view', array(
            'seo' => $seo,
            'breadcrumb' => '',
            //'cateByLang' => $cateByLang,
            //'serviceByLang' => $serviceByLang,
        ) );
        //print_r( $this->teamplate );
        $cache_value = view( 'layout_view', $this->teamplate );

        // Save into the cache for 5 minutes
        $cache_save = $this->MY_cache( $this->cache_key, $cache_value );
        //var_dump( $cache_save );

        //
        return $cache_value;
    }

    public function checkurl( $slug_1, $set_page = '', $page_num = 1 ) {
        //$slug_1 = $this->uri->segment( 1 );
        //$slug_2 = $this->uri->segment( 2 );

        //echo $slug_1 . '<br>' . "\n";
        //echo $slug_2 . '<br>' . "\n";

        //
        if ( $slug_1 == '' ) {
            die( '404 slug error!' );
        }
        //echo $set_page . ' <br>' . "\n";
        //echo $page_num . ' <br>' . "\n";

        // -> kiểm tra theo category
        $data = $this->term_model->get_taxonomy( array(
            // các kiểu điều kiện where
            'slug' => $slug_1,
            'is_deleted' => DeletedStatus::FOR_DEFAULT,
            'lang_key' => $this->lang_key,
            'taxonomy' => TaxonomyType::POSTS
        ) );
        //print_r( $data );

        // có -> ưu tiên category
        if ( !empty( $data ) ) {
            return $this->category( $data, PostType::POST, TaxonomyType::POSTS, 'category_view', [
                'page_num' => $page_num,
            ] );
        }
        // -> nếu không có -> thử tìm theo trang
        else {
            //echo 'check page <br>' . "\n";
            $data = $this->post_model->select_public_post( 0, [
                'post_name' => $slug_1,
                'post_type' => PostType::PAGE,
            ] );
            if ( !empty( $data ) ) {
                //print_r( $data );
                return $this->pageDetail( $data );
            }
        }

        //
        return $this->page404();
    }

    protected function autoDetails() {
        $id = $this->MY_get( 'p' );
        //echo $id . '<br>' . "\n";

        //
        if ( empty( $id ) ) {
            die( 'ERROR! id? ' . basename( __FILE__ ) . ':' . __LINE__ );
        }

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
        $post_type = $this->MY_get( 'post_type' );

        // lấy post theo ID, không lọc theo post type -> vì nhiều nơi cần dùng đến
        $data = $this->base_model->select( '*', 'wp_posts', array(
            // các kiểu điều kiện where
            'ID' => $id,
            'post_type' => $post_type,
            'post_status' => PostType::PUBLIC
        ), array(
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 2,
            'limit' => 1
        ) );
        //die( __FILE__ . ':' . __LINE__ );

        //
        if ( !empty( $data ) ) {
            // lấy meta của post này
            $data[ 'post_meta' ] = $this->post_model->arr_meta_post( $data[ 'ID' ] );
            //print_r( $data );
            //die( __FILE__ . ':' . __LINE__ );

            // với các post type mặc định -> dùng page view
            if ( in_array( $data[ 'post_type' ], [
                    PostType::POST,
                    PostType::BLOG,
                    PostType::PAGE
                ] ) ) {
                return $this->pageDetail( $data );
            }
            // các custom post type -> dùng view theo post type
            else {
                return $this->pageDetail( $data, $data[ 'post_type' ] . '_view' );
            }
        }

        //
        return $this->page404();
    }

    protected function pageDetail( $data, $file_view = 'page_view' ) {
        $this->cache_key = 'post' . $data[ 'ID' ];
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

        // update lượt xem -> daidq (2021-12-14): chuyển phần update này qua view, ai thích dùng thì kích hoạt cho nó nhẹ
        //$this->post_model->update_views( $data[ $this->post_model->primaryKey ] );

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
                $cats = $this->base_model->select( '*', 'v_terms', [
                    'term_id' => $post_category,
                ], [
                    // hiển thị mã SQL để check
                    //'show_query' => 1,
                    // trả về câu query để sử dụng cho mục đích khác
                    //'get_query' => 1,
                    //'offset' => 0,
                    'limit' => 1
                ] );
                //print_r( $cats );

                //
                if ( !empty( $cats ) ) {
                    $this->create_term_breadcrumb( $cats );
                }
            }
        }

        // page view mặc định
        $page_template = '';
        // nếu có dùng template riêng -> dùng luôn
        if ( isset( $data[ 'post_meta' ][ 'page_template' ] ) && $data[ 'post_meta' ][ 'page_template' ] != '' ) {
            $page_template = $data[ 'post_meta' ][ 'page_template' ];
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
            'seo' => $seo,
            'page_template' => $page_template,
            'data' => $data,
            'parent_data' => $parent_data,
        ) );
        $cache_value = view( 'layout_view', $this->teamplate );

        // chỉ lưu cache nếu không có page template
        //if ( $page_template == '' ) {
        // Save into the cache for 5 minutes
        $cache_save = $this->MY_cache( $this->cache_key, $cache_value );
        //}
        //var_dump( $cache_save );

        //
        return $cache_value;
    }

    protected function autoCategory() {
        $term_id = $this->MY_get( 'cat' );
        //echo $term_id . '<br>' . "\n";

        //
        $taxonomy_type = $this->MY_get( 'taxonomy' );
        //echo $taxonomy_type . '<br>' . "\n";

        //
        $page_num = $this->MY_get( 'page_num', 1 );
        //echo $page_num . '<br>' . "\n";

        //
        $data = $this->term_model->get_taxonomy( array(
            // các kiểu điều kiện where
            'term_id' => $term_id,
            'is_deleted' => DeletedStatus::FOR_DEFAULT,
            'lang_key' => $this->lang_key,
            'taxonomy' => $taxonomy_type
        ) );
        //print_r( $data );

        // có -> ưu tiên category
        if ( !empty( $data ) ) {
            // xác định post type dựa theo taxonomy type
            $get_post_type = $this->base_model->select( 'post_type', 'wp_posts', [
                'wp_posts.post_status' => PostType::PUBLIC,
                'wp_term_taxonomy.term_id' => $data[ 'term_id' ],
                'wp_posts.lang_key' => LanguageCost::lang_key()
            ], [
                'join' => [
                    'wp_term_relationships' => 'wp_term_relationships.object_id = wp_posts.ID',
                    'wp_term_taxonomy' => 'wp_term_relationships.term_taxonomy_id = wp_term_taxonomy.term_taxonomy_id',
                ],
                'order_by' => [
                    'wp_posts.ID' => 'DESC',
                ],
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 0,
                'limit' => 1
            ] );
            //print_r( $get_post_type );

            // tìm được post tương ứng thì mới show category ra
            if ( !empty( $get_post_type ) ) {
                return $this->category( $data, $get_post_type[ 'post_type' ], $taxonomy_type, 'term_view', [
                    'page_num' => $page_num,
                ] );
            }
        }

        //
        return $this->page404();
    }

    /*
     * Tạo function dùng chung cho các form thuộc dạng liên hệ
     */
    protected function MY_comment( $ops = [] ) {
        // function này chỉ nhận POST
        //print_r( $_SERVER );
        //print_r( $_POST );
        //print_r( $_FILES );

        //
        if ( isset( $ops[ 'redirect_to' ] ) && $ops[ 'redirect_to' ] != '' ) {
            $redirect_to = $ops[ 'redirect_to' ];
        } else {
            $redirect_to = DYNAMIC_BASE_URL . ltrim( $this->MY_post( 'redirect' ), '/' );
            if ( empty( $redirect_to ) ) {
                $redirect_to = DYNAMIC_BASE_URL;
            }
        }
        //die( $redirect_to );

        //
        if ( empty( $this->MY_post( 'data' ) ) ) {
            $this->session->setFlashdata( 'msg_error', 'Lỗi xác định phương thức nhập liệu' );
            die( redirect( $redirect_to ) );
        }

        // insert dữ liệu vào bảng
        /*
        $insert_to = $this->MY_post( 'to' );
        if ( empty( $insert_to ) ) {
            $this->session->setFlashdata( 'msg_error', 'Không xác định được đích dữ liệu' );
            die( redirect( $redirect_to ) );
        }
        */
        $data = $this->MY_post( 'data' );
        $send_my_email = $this->MY_post( 'send_my_email' );
        //print_r( $data );

        // nếu không có thuộc tính phân loại comment -> tạu tạo phân loại dựa theo tên function gửi đến
        if ( !isset( $ops[ 'comment_type' ] ) ) {
            $ops[ 'comment_type' ] = debug_backtrace()[ 1 ][ 'function' ];
        }

        //
        $data_insert = [
            'comment_author_url' => $redirect_to,
            //'comment_author_IP' => $this->request->getIPAddress(),
            //'comment_date' => date( 'Y-m-d H:i:s' ),
            'comment_content' => '',
            //'comment_agent' => $_SERVER[ 'HTTP_USER_AGENT' ],
            'comment_type' => $ops[ 'comment_type' ],
            //'user_id' => 0,
        ];
        $data_insert[ 'comment_date_gmt' ] = $data_insert[ 'comment_date' ];
        //print_r( $data_insert );
        //die( 'dgh dhd hdf' );

        //
        if ( !empty( $this->session_data ) && isset( $this->session_data[ 'ID' ] ) ) {
            $data_insert[ 'user_id' ] = $this->session_data[ 'ID' ];
        }

        // -> tạo nội dung theo data truyền vào
        foreach ( $data as $k => $v ) {
            $k = trim( $k );
            $gettype_v = gettype( $v );
            if ( $gettype_v == 'array' || $gettype_v == 'object' ) {
                $v = json_encode( $v );
            } else {
                $v = trim( $v );
            }

            //
            if ( $k != '' && !empty( $v ) ) {
                $data_insert[ 'comment_content' ] .= $k . ':' . "\n";
                $data_insert[ 'comment_content' ] .= $v . "\n";
            }
        }
        //print_r( $data_insert );
        //die( 'j dfs ch dfh ds' );

        //
        $list_upload = $this->media_upload();
        //print_r( $list_upload );
        if ( !empty( $list_upload ) ) {
            $data_insert[ 'comment_content' ] .= 'File đính kèm: ' . "\n";
            foreach ( $list_upload as $arr ) {
                foreach ( $arr as $v ) {
                    $data_insert[ 'comment_content' ] .= DYNAMIC_BASE_URL . $v . "\n";
                }
            }
        }
        //print_r( $data_insert );
        //die( 'jh afsdgssf' );

        // insert comment
        $comment_ID = $this->comment_model->insert_comments( $data_insert );

        // insert meta comment
        foreach ( $data as $k => $v ) {
            $k = trim( $k );
            $v = trim( $v );

            //
            if ( $k != '' && $v != '' ) {
                $meta_id = $this->comment_model->insert_meta_comments( [
                    'comment_id' => $comment_ID,
                    'meta_key' => $k,
                    'meta_value' => $v,
                ] );
            }
        }

        //
        if ( !empty( $list_upload ) ) {
            $meta_id = $this->comment_model->insert_meta_comments( [
                'comment_id' => $comment_ID,
                'meta_key' => 'list_upload',
                'meta_value' => json_encode( $list_upload ),
            ] );
        }


        //
        $done_message = $this->MY_post( 'done_message' );
        if ( empty( $done_message ) ) {
            $done_message = 'Gửi liên hệ thành công. Chúng tôi sẽ liên hệ lại với bạn sớm nhất có thể.';
        }
        $this->session->setFlashdata( 'msg', $done_message );

        //
        $data_send = [
            'message' => $data_insert[ 'comment_content' ],
        ];

        //
        return $data_send;
    }
}