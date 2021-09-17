<?php
//require_once __DIR__ . '/Admin.php';
namespace App\ Controllers\ Admin;

// Libraries
use App\ Libraries\ PostType;
use App\ Libraries\ TaxonomyType;
use App\ Libraries\ LanguageCost;

//
class Posts extends Admin {
    private $post_type = '';
    private $detault_type = '';

    // các taxonomy được hỗ trợ -> cái nào trống nghĩa là không hỗ trợ theo post_type tương ứng
    private $taxonomy = '';
    private $tags = '';
    private $options = '';
    private $controller_slug = 'posts';

    public function __construct() {
        parent::__construct();

        // kiểm tra quyền truy cập của tài khoản hiện tại
        $this->check_permision( __CLASS__ );

        // lọc bài viết dựa theo post type
        $this->detault_type = PostType::POST;
        $this->post_type = $this->MY_get( 'post_type', $this->detault_type );

        // báo lỗi nếu không xác định được post_type
        if ( $this->post_type == '' || PostType::list( $this->post_type ) == '' ) {
            die( 'post_type not register in system!' );
        }

        // -> category tương ứng
        if ( $this->post_type == PostType::ADS ) {
            $this->taxonomy = TaxonomyType::ADS;
        } else if ( $this->post_type == PostType::BLOG ) {
            $this->taxonomy = TaxonomyType::BLOGS;
            $this->tags = TaxonomyType::BLOG_TAGS;
        } else if ( $this->post_type == PostType::MENU ) {
            // riêng với phần menu, do dùng chung controller với post -> kiểm tra lại permission lần nữa cho chắc
            $this->check_permision( 'Menus' );

            //$this->taxonomy = TaxonomyType::MENU;
            $this->controller_slug = 'menus';
        } else if ( $this->post_type == PostType::PAGE ) {
            //$this->taxonomy = TaxonomyType::PAGE;
        } else {
            $this->taxonomy = TaxonomyType::POSTS;
            $this->tags = TaxonomyType::TAGS;
        }
        $this->options = TaxonomyType::OPTIONS;
    }

    public function index() {
        $post_per_page = 20;

        // các kiểu điều kiện where
        $where = [
            'wp_posts.post_status !=' => PostType::DELETED,
            'wp_posts.post_type' => $this->post_type,
            'wp_posts.lang_key' => LanguageCost::lang_key()
        ];

        // tìm kiếm theo từ khóa nhập vào
        $by_keyword = $this->MY_get( 's', '' );
        $where_or_like = [];
        // URL cho phân trang tìm kiếm
        $urlPartPage = 'admin/posts?post_type=' . $this->post_type;
        if ( $by_keyword != '' ) {
            $urlPartPage .= '&s=' . $by_keyword;

            //
            $by_like = $this->base_model->_eb_non_mark_seo( $by_keyword );
            // tối thiểu từ 3 ký tự trở lên mới kích hoạt tìm kiếm
            if ( strlen( $by_like ) > 2 ) {
                $where_or_like = [
                    'ID' => $by_like,
                    'post_name' => $by_like,
                    'post_title' => $by_keyword,
                ];
            }
        }

        // tổng kết filter
        $filter = [
            'where_in' => array(
                'wp_posts.post_status' => array(
                    PostType::DRAFT,
                    PostType::PUBLIC,
                    PostType::PENDING,
                )
            ),
            'or_like' => $where_or_like,
            'order_by' => array(
                'wp_posts.menu_order' => 'DESC',
                'wp_posts.post_date' => 'DESC',
                //'post_modified' => 'DESC',
            ),
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 0,
            //'limit' => $post_per_page;
        ];

        // nếu có lọc theo term_id -> thêm câu lệnh để lọc
        $by_term_id = $this->MY_get( 'term_id', 0 );
        if ( $by_term_id > 0 ) {
            $where[ 'wp_term_taxonomy.term_id' ] = $_GET[ 'term_id' ];
            $filter[ 'join' ] = [
                'wp_term_relationships' => 'wp_term_relationships.object_id = wp_posts.ID',
                'wp_term_taxonomy' => 'wp_term_relationships.term_taxonomy_id = wp_term_taxonomy.term_taxonomy_id',
            ];
        }


        /*
         * phân trang
         */
        $totalThread = $this->base_model->select( 'COUNT(ID) AS c', 'wp_posts', $where, $filter );
        //print_r( $totalThread );
        $totalThread = $totalThread[ 0 ][ 'c' ];
        //print_r( $totalThread );
        $totalPage = ceil( $totalThread / $post_per_page );
        if ( $totalPage < 1 ) {
            $totalPage = 1;
        }
        $page_num = $this->MY_get( 'page_num', 1 );
        //echo $totalPage . '<br>' . "\n";
        if ( $page_num > $totalPage ) {
            $page_num = $totalPage;
        } else if ( $page_num < 1 ) {
            $page_num = 1;
        }
        //echo $totalThread . '<br>' . "\n";
        //echo $totalPage . '<br>' . "\n";
        $offset = ( $page_num - 1 ) * $post_per_page;

        //
        $pagination = $this->base_model->EBE_pagination( $page_num, $totalPage, $urlPartPage, '&page_num=' );


        // select dữ liệu từ 1 bảng bất kỳ
        $filter[ 'offset' ] = $offset;
        $filter[ 'limit' ] = $post_per_page;
        $data = $this->base_model->select( '*', 'wp_posts', $where, $filter );

        //
        $data = $this->post_model->list_meta_post( $data );
        //print_r( $data );

        //
        $this->teamplate_admin[ 'content' ] = view( 'admin/posts/list', array(
            'by_keyword' => $by_keyword,
            'by_term_id' => $by_term_id,
            'controller_slug' => $this->controller_slug,
            'pagination' => $pagination,
            'totalThread' => $totalThread,
            'data' => $data,
            'taxonomy' => $this->taxonomy,
            'post_type' => $this->post_type,
        ) );
        return view( 'admin/admin_teamplate', $this->teamplate_admin );
    }

    public function add() {
        $id = $this->MY_get( 'id', 0 );

        //
        if ( !empty( $this->MY_post( 'data' ) ) ) {
            // nếu là nhân bản
            if ( isset( $_POST[ 'is_duplicate' ] ) && $_POST[ 'is_duplicate' ] * 1 === 1 ) {
                // đổi lại tiêu đề để tránh trùng lặp
                if ( isset( $_POST[ 'data' ][ 'post_title' ] ) ) {
                    $duplicate_title = explode( '- Duplicate', $_POST[ 'data' ][ 'post_title' ] );
                    $_POST[ 'data' ][ 'post_title' ] = trim( $duplicate_title[ 0 ] ) . ' - Duplicate ' . date( 'Ymd-His' );

                    // tạo lại slug
                    $_POST[ 'data' ][ 'post_name' ] = '';
                }

                // -> bỏ ID đi
                $id = 0;
            }

            // update
            if ( $id > 0 ) {
                return $this->update( $id );
            }
            // insert
            return $this->add_new();
        }

        // edit
        if ( $id != '' ) {
            // select dữ liệu từ 1 bảng bất kỳ
            $data = $this->post_model->select_post( $id, [
                'post_type' => $this->post_type,
            ] );

            if ( empty( $data ) ) {
                die( 'post not found!' );
            }

            // tự động cập nhật lại slug khi nhân bản
            if ( strstr( $data[ 'post_name' ], '-duplicate-' ) == true && strstr( $data[ 'post_title' ], ' - Duplicate ' ) == false ) {
                //echo 'bbbbbbbbbbbbb';
                $this->post_model->update_post( $id, [
                    'post_title' => $data[ 'post_title' ],
                    'post_name' => '',
                ], [
                    'post_type' => $this->post_type,
                ] );

                //
                die( header( 'location:' . DYNAMIC_BASE_URL . ltrim( $_SERVER[ 'REQUEST_URI' ], '/' ) ) );
            }
        }
        // add
        else {
            $data = $this->base_model->default_data( 'wp_posts' );
            $data[ 'post_meta' ] = [];
        }
        //print_r( $this->session_data );
        //print_r( $data );


        //
        $post_cat = [];
        $parent_post = [];
        // lấy danh sách các trang để chọn bài cha
        if ( $this->post_type == PostType::PAGE ) {
            // các kiểu điều kiện where
            $where = [
                'wp_posts.post_status !=' => PostType::DELETED,
                'wp_posts.post_type' => $this->post_type,
                'wp_posts.lang_key' => LanguageCost::lang_key()
            ];

            $filter = [
                'where_in' => array(
                    'wp_posts.post_status' => array(
                        PostType::DRAFT,
                        PostType::PUBLIC,
                        PostType::PENDING,
                    )
                ),
                'where_not_in' => array(
                    'wp_posts.ID' => array(
                        $id
                    )
                ),
                'order_by' => array(
                    'wp_posts.menu_order' => 'DESC',
                    'wp_posts.post_date' => 'DESC',
                    //'post_modified' => 'DESC',
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 0,
                //'limit' => $post_per_page;
            ];
            $parent_post = $this->base_model->select( 'wp_posts.ID, wp_posts.post_title', 'wp_posts', $where, $filter );
            //print_r( $parent_post );
        }
        // lấy danh sách các nhóm để add cho post
        else {
            $post_cat = $this->term_model->get_all_taxonomy( $this->taxonomy );
        }


        //
        $this->teamplate_admin[ 'content' ] = view( 'admin/posts/add', array(
            'controller_slug' => $this->controller_slug,
            'lang_key' => LanguageCost::lang_key(),
            'post_cat' => $post_cat,
            'parent_post' => $parent_post,
            'data' => $data,
            'meta_detault' => PostType::meta_default( $this->post_type ),
            'taxonomy' => $this->taxonomy,
            'post_type' => $this->post_type,
        ) );
        return view( 'admin/admin_teamplate', $this->teamplate_admin );
    }

    public function add_new() {
        $data = $_POST[ 'data' ];
        $data[ 'post_type' ] = $this->post_type;

        //
        $result_id = $this->post_model->insert_post( $data );

        if ( $result_id > 0 ) {
            $this->base_model->alert( '', $this->post_model->get_admin_permalink( $this->post_type, $result_id ) );
        }
        $this->base_model->alert( 'Lỗi tạo ' . PostType::list( $this->post_type ) . ' mới', 'error' );
    }


    public function update( $id ) {
        //print_r( $_POST );
        if ( isset( $_POST[ 'is_deleted' ] ) && $_POST[ 'is_deleted' ] * 1 === 1 ) {
            $this->delete( $id );
        }

        //
        $data = $_POST[ 'data' ];
        //print_r( $data );

        //
        $result_id = $this->post_model->update_post( $id, $data, [
            'post_type' => $this->post_type,
        ] );

        $this->base_model->alert( 'Cập nhật ' . PostType::list( $this->post_type ) . ' thành công' );
    }

    public function delete( $id ) {
        $result_id = $this->post_model->update_post( $id, [
            'post_status' => PostType::DELETED
        ], [
            'post_type' => $this->post_type,
        ] );

        $this->base_model->alert( '', base_url( 'admin/posts' ) . '?post_type=' . $this->post_type );
    }


    private function createdThumbnail( $imagePath ) {

        $listSizeThumb = $this->config->item( 'list_thumbnail' );
        $listThumbFolder = $this->config->item( 'thumbnail_folder' );

        for ( $i = 0; $i < count( $listThumbFolder ); $i++ ) {

            $pimageFullPath = $imagePath; // đg dẫn full path

            $config_manip = array(
                'image_library' => 'gd2',
                'source_image' => $pimageFullPath,
                'new_image' => $this->config->item( 'base_path' ) . '/Images/' . $listThumbFolder[ $i ],
                'maintain_ratio' => TRUE,
                'width' => $listSizeThumb[ $i ][ 'width' ],
                'height' => $listSizeThumb[ $i ][ 'height' ],
            );

            $this->load->library( 'image_lib' );
            $this->image_lib->initialize( $config_manip );

            if ( !$this->image_lib->resize() ) {
                echo $this->image_lib->display_errors();
                die( ' lỗi resize' );
            }
            $this->image_lib->clear();
        }
    }

}