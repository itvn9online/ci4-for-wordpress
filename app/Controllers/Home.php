<?php
namespace App\ Controllers;

// Libraries
use App\ Libraries\ DeletedStatus;
use App\ Libraries\ TaxonomyType;
use App\ Libraries\ PostType;
use App\ Libraries\ LanguageCost;

//
class Home extends Csrf {

    public function __construct() {
        parent::__construct();
    }

    /*
     * default page
     */
    public function index() {
        // đồng bộ lại tổng số nhóm con cho các danh mục trước đã
        $this->term_model->sync_term_child_count();

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
        $cache_key = 'home';
        $cache_value = $this->MY_cache( $cache_key );
        // Will get the cache entry named 'my_foo'
        //var_dump( $cache_value );
        // có thì in ra cache là được
        //if ( $_SERVER[ 'REQUEST_METHOD' ] == 'GET' && $cache_value !== NULL ) {
        if ( $this->hasFlashSession() === false && $cache_value !== NULL ) {
            return $this->show_cache( $cache_value );
        }
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";

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

        // nếu có flash session -> trả về view luôn
        if ( $this->hasFlashSession() === true ) {
            return view( 'layout_view', $this->teamplate );
        }
        // còn không sẽ tiến hành lưu cache
        $cache_value = view( 'layout_view', $this->teamplate );

        $cache_save = $this->MY_cache( $cache_key, $cache_value . '<!-- Served from: ' . __FUNCTION__ . ' -->' );
        //var_dump( $cache_save );

        //
        return $cache_value;
    }

    public function checkurl( $slug, $set_page = '', $page_num = 1 ) {
        if ( $slug == '' ) {
            die( '404 slug error!' );
        }
        //echo $set_page . ' <br>' . "\n";
        //echo $page_num . ' <br>' . "\n";

        // -> kiểm tra theo category
        $data = $this->term_model->get_taxonomy( array(
            // các kiểu điều kiện where
            'slug' => $slug,
            'is_deleted' => DeletedStatus::FOR_DEFAULT,
            'lang_key' => $this->lang_key,
            'taxonomy' => TaxonomyType::POSTS
        ) );
        //print_r( $data );

        // có -> ưu tiên category
        if ( !empty( $data ) ) {
            // vào đây thì bắt buộc phải không có category prefix
            if ( WGR_CATEGORY_PREFIX != '' ) {
                // -> có thì chuyển hướng tới link chính ngay
                return redirect()->to( $this->term_model->get_the_permalink( $data ) );
                //die( __CLASS__ . ':' . __LINE__ );
            }

            //
            return $this->category( $data, PostType::POST, TaxonomyType::POSTS, 'category_view', [
                'page_num' => $page_num,
            ] );
        }
        // -> nếu không có -> thử tìm theo trang
        else {
            //echo 'check page <br>' . "\n";
            $data = $this->post_model->select_public_post( 0, [
                'post_name' => $slug,
                'post_type' => PostType::PAGE,
            ] );
            if ( !empty( $data ) ) {
                //print_r( $data );

                // vào đây thì bắt buộc phải không có page prefix
                if ( WGR_PAGES_PREFIX != '' ) {
                    // -> có thì chuyển hướng tới link chính ngay
                    return redirect()->to( $this->post_model->get_the_permalink( $data ) );
                    //die( __CLASS__ . ':' . __LINE__ );
                }

                //
                return $this->pageDetail( $data );
            }
        }

        //
        return $this->page404( 'ERROR ' . strtolower( __FUNCTION__ ) . ':' . __LINE__ . '! Không xác định được danh mục bài viết...' );
    }

    protected function autoDetails() {
        return $this->showPostDetails( $this->MY_get( 'p', 0 ), $this->MY_get( 'post_type', '' ) );
    }
    protected function showPostDetails( $id, $post_type ) {
        //echo $id . '<br>' . "\n";
        //echo $post_type . '<br>' . "\n";

        //
        if ( !is_numeric( $id ) || $id <= 0 ) {
            die( 'ERROR! id? ' . basename( __FILE__ ) . ':' . __LINE__ );
        }

        //
        $cache_key = $this->post_model->key_cache( $id );
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
        $cache_value = $this->MY_cache( $cache_key );
        // Will get the cache entry named 'my_foo'
        //var_dump( $cache_value );
        // có thì in ra cache là được
        if ( $cache_value !== NULL ) {
            return $this->show_cache( $cache_value );
        }
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";

        //
        $in_cache = __FUNCTION__;
        $data = $this->post_model->the_cache( $id, $in_cache );
        if ( $data === NULL ) {
            // lấy post theo ID, không lọc theo post type -> vì nhiều nơi cần dùng đến
            $data = $this->base_model->select( '*', 'posts', array(
                // các kiểu điều kiện where
                'ID' => $id,
                'post_type' => $post_type,
                'post_status' => PostType::PUBLICITY
            ), array(
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 2,
                'limit' => 1
            ) );
            //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
            //die( __CLASS__ . ':' . __LINE__ );

            //
            if ( !empty( $data ) ) {
                // lấy meta của post này
                //$data[ 'post_meta' ] = $this->post_model->arr_meta_post( $data[ 'ID' ] );
                $data = $this->post_model->the_meta_post( $data );
                //print_r( $data );
                //die( __CLASS__ . ':' . __LINE__ );
            }

            //
            $this->post_model->the_cache( $id, $in_cache, $data );
        }
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";

        //
        if ( !empty( $data ) ) {
            // với các post type mặc định -> dùng page view
            if ( in_array( $data[ 'post_type' ], [
                    PostType::POST,
                    PostType::BLOG,
                    PostType::PAGE
                ] ) ) {
                return $this->pageDetail( $data );
            }
            // các custom post type -> dùng view theo post type (ngoại trừ post type ADS)
            else if ( !in_array( $data[ 'post_type' ], [
                    PostType::ADS
                ] ) ) {
                return $this->pageDetail( $data, $data[ 'post_type' ] . '_view' );
            }
        }
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";

        //
        return $this->page404( 'ERROR ' . strtolower( __FUNCTION__ ) . ':' . __LINE__ . '! Không xác định được dữ liệu bài viết...' );
    }

    protected function pageDetail( $data, $file_view = 'page_view' ) {
        // kiểm tra quyền truy cập chi tiết 1 post
        if ( $this->post_permission( $data ) !== true ) {
            return $this->page404( $this->post_permission( $data ) );
        }

        //
        $cache_key = $this->post_model->key_cache( $data[ 'ID' ] );
        $cache_value = $this->MY_cache( $cache_key );
        // Will get the cache entry named 'my_foo'
        //var_dump( $cache_value );
        // có thì in ra cache là được
        //if ( $_SERVER[ 'REQUEST_METHOD' ] == 'GET' && $cache_value !== NULL ) {
        if ( $this->hasFlashSession() === false && $cache_value !== NULL ) {
            return $this->show_cache( $cache_value );
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
                $in_cache = __FUNCTION__;
                $cats = $this->term_model->the_cache( $post_category, $in_cache );
                if ( $cats === NULL ) {
                    $cats = $this->base_model->select( '*', WGR_TERM_VIEW, [
                        'term_id' => $post_category,
                    ], [
                        // hiển thị mã SQL để check
                        //'show_query' => 1,
                        // trả về câu query để sử dụng cho mục đích khác
                        //'get_query' => 1,
                        //'offset' => 0,
                        'limit' => 1
                    ] );

                    //
                    $this->term_model->the_cache( $post_category, $in_cache, $cats );
                }
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

            //
            if ( !empty( $parent_data ) ) {
                $this->create_breadcrumb( $parent_data[ 'post_title' ], $this->post_model->get_the_permalink( $parent_data ) );
            }
            // cha bị khóa thì cũng trả về 404 luôn
            else {
                return $this->page404( 'ERROR ' . strtolower( __FUNCTION__ ) . ':' . __LINE__ . '! Bài viết bị KHÓA do đang liên kết tới một bài viết khác đã bị KHÓA...' );
            }
        }

        //
        $post_permalink = $this->post_model->get_the_permalink( $data );
        $this->create_breadcrumb( $data[ 'post_title' ], $post_permalink );
        $seo = $this->base_model->seo( $data, $post_permalink );
        $this->current_pid = $data[ 'ID' ];

        // -> views
        $this->teamplate[ 'breadcrumb' ] = view( 'breadcrumb_view', array(
            'breadcrumb' => $this->breadcrumb
        ) );

        $this->teamplate[ 'main' ] = view( $file_view, array(
            'seo' => $seo,
            'page_template' => $page_template,
            'data' => $data,
            'current_pid' => $this->current_pid,
            'parent_data' => $parent_data,
        ) );

        // nếu có flash session -> trả về view luôn
        if ( $this->hasFlashSession() === true ) {
            return view( 'layout_view', $this->teamplate );
        }
        // còn không sẽ tiến hành lưu cache
        $cache_value = view( 'layout_view', $this->teamplate );

        // chỉ lưu cache nếu không có page template
        //if ( $page_template == '' ) {
        $cache_save = $this->MY_cache( $cache_key, $cache_value . '<!-- Served from: ' . __FUNCTION__ . ' -->' );
        //var_dump( $cache_save );
        //}

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
        return $this->showCategory( $term_id, $taxonomy_type, $page_num );
    }

    //
    protected function showCategory( $term_id, $taxonomy_type, $page_num = 1 ) {
        $cache_key = $this->term_model->key_cache( $term_id ) . 'page' . $page_num;
        $cache_value = $this->MY_cache( $cache_key );
        // có thì in ra cache là được
        if ( $cache_value !== NULL ) {
            return $this->show_cache( $cache_value );
        }

        //
        $in_cache = __FUNCTION__;
        $data = $this->term_model->the_cache( $term_id, $in_cache );
        if ( $data === NULL ) {
            $data = $this->term_model->get_taxonomy( array(
                // các kiểu điều kiện where
                'term_id' => $term_id,
                'is_deleted' => DeletedStatus::FOR_DEFAULT,
                'lang_key' => $this->lang_key,
                'taxonomy' => $taxonomy_type
            ) );

            //
            $this->term_model->the_cache( $term_id, $in_cache, $data );
        }
        //print_r( $data );

        // có -> lấy bài viết trong nhóm
        if ( !empty( $data ) && $data[ 'count' ] > 0 ) {
            // xem nhóm này có nhóm con không
            $in_cache = __FUNCTION__ . '-parent';
            $child_data = $this->term_model->the_cache( $term_id, $in_cache );
            if ( $child_data === NULL ) {
                $child_data = $this->term_model->get_taxonomy( array(
                    // các kiểu điều kiện where
                    'parent' => $term_id,
                    'is_deleted' => DeletedStatus::FOR_DEFAULT,
                    'lang_key' => $this->lang_key,
                    'taxonomy' => $taxonomy_type
                ), 10, 'term_id' );

                //
                $this->term_model->the_cache( $term_id, $in_cache, $child_data );
            }
            //print_r( $child_data );

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
            if ( empty( $child_data ) ) {
                $where[ 'term_taxonomy.term_id' ] = $data[ 'term_id' ];
            }
            // nếu có -> lấy theo cả cha và con
            else {
                $where_in = [];
                foreach ( $child_data as $v ) {
                    $where_in[] = $v[ 'term_id' ];
                }

                //
                $filter[ 'where_in' ] = [
                    'term_taxonomy.term_id' => $where_in
                ];

                //
                $data[ 'where_in' ] = $where_in;
            }
            //print_r( $data );

            // xác định post type dựa theo taxonomy type
            $get_post_type = $this->base_model->select( 'post_type', 'posts', $where, $filter );
            //print_r( $get_post_type );

            // tìm được post tương ứng thì mới show category ra
            if ( !empty( $get_post_type ) ) {
                return $this->category( $data, $get_post_type[ 'post_type' ], $taxonomy_type, 'term_view', [
                    'page_num' => $page_num,
                    'cache_key' => $cache_key,
                ] );
            }

            // cập nhật lại tổng số bài viết cho term - để sau nếu có tính năng lấy theo nhóm thì nó sẽ không xuất hiện nữa
            $this->base_model->update_multiple( $this->term_model->taxTable, [
                'count' => 0
            ], [
                'term_taxonomy_id' => $term_id,
                'term_id' => $term_id,
            ], [
                'debug_backtrace' => debug_backtrace()[ 1 ][ 'function' ]
            ] );
        }

        //
        return $this->page404( 'ERROR ' . strtolower( __FUNCTION__ ) . ':' . __LINE__ . '! Không xác định được danh mục bài viết...', $cache_key );
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
            $this->base_model->msg_error_session( 'Lỗi xác định phương thức nhập liệu' );
            die( redirect( $redirect_to ) );
        }

        // insert dữ liệu vào bảng
        /*
        $insert_to = $this->MY_post( 'to' );
        if ( empty( $insert_to ) ) {
            $this->base_model->msg_error_session( 'Không xác định được đích dữ liệu' );
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
            //'comment_date' => date( EBE_DATETIME_FORMAT ),
            'comment_content' => '',
            //'comment_agent' => $_SERVER[ 'HTTP_USER_AGENT' ],
            'comment_type' => $ops[ 'comment_type' ],
            //'user_id' => 0,
        ];
        $data_insert[ 'comment_date_gmt' ] = $data_insert[ 'comment_date' ];
        //print_r( $data_insert );
        //die( 'dgh dhd hdf' );

        //
        if ( $this->current_user_id > 0 ) {
            $data_insert[ 'user_id' ] = $this->current_user_id;
        }

        //
        if ( isset( $data[ 'email' ] ) && !isset( $data[ 'comment_author_email' ] ) ) {
            $data_insert[ 'comment_author_email' ] = $data[ 'email' ];
        }
        if ( isset( $data[ 'title' ] ) && !isset( $data[ 'comment_title' ] ) ) {
            $data_insert[ 'comment_title' ] = $data[ 'title' ];
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
        $this->base_model->msg_session( $done_message );

        //
        $data_send = [
            'message' => $data_insert[ 'comment_content' ],
        ];

        //
        return $data_send;
    }
}