<?php
//require_once __DIR__ . '/Admin.php';
namespace App\ Controllers\ Admin;

// Libraries
use App\ Libraries\ TaxonomyType;
use App\ Libraries\ LanguageCost;
use App\ Libraries\ DeletedStatus;

//
class Terms extends Admin {
    protected $taxonomy = '';
    protected $name_type = '';
    private $default_taxonomy = '';

    // tham số dùng để thay đổi URL cho controller nếu muốn
    protected $controller_slug = 'terms';
    // tham số dùng để đổi file view khi add hoặc edit bài viết nếu muốn
    protected $add_edit_view = 'terms';

    public function __construct( $for_extends = false ) {
        parent::__construct();

        // kiểm tra quyền truy cập của tài khoản hiện tại
        $this->check_permision( __CLASS__ );

        // chỉ kiểm tra các điều kiện này nếu không được chỉ định là extends
        if ( $for_extends === false ) {
            // lọc term dựa theo taxonomy
            $this->default_taxonomy = TaxonomyType::POSTS;
            $this->taxonomy = $this->MY_get( 'taxonomy', $this->default_taxonomy );
            $this->name_type = TaxonomyType::list( $this->taxonomy, true );

            // báo lỗi nếu không xác định được taxonomy
            if ( $this->taxonomy == '' || TaxonomyType::list( $this->taxonomy ) == '' ) {
                die( 'Taxonomy not register in system!' );
            }
        }
    }

    public function index() {
        $post_per_page = 20;

        // tìm kiếm theo từ khóa nhập vào
        $by_keyword = $this->MY_get( 's' );
        $where_or_like = [];
        // URL cho phân trang tìm kiếm
        $urlPartPage = 'admin/terms?taxonomy=' . $this->taxonomy;
        if ( $by_keyword != '' ) {
            $urlPartPage .= '&s=' . $by_keyword;

            //
            $by_like = $this->base_model->_eb_non_mark_seo( $by_keyword );
            // tối thiểu từ 1 ký tự trở lên mới kích hoạt tìm kiếm
            if ( strlen( $by_like ) > 0 ) {
                //var_dump( strlen( $by_like ) );
                $is_number = is_numeric( $by_like );
                // nếu là số -> chỉ tìm theo ID
                if ( $is_number === true ) {
                    $where_or_like = [
                        'term_id' => $by_like,
                    ];
                } else {
                    $where_or_like = [
                        //'term_id' => $by_like,
                        'slug' => $by_like,
                        'name' => $by_keyword,
                    ];
                }
            }
        }

        //
        $by_is_deleted = $this->MY_get( 'is_deleted', DeletedStatus::FOR_DEFAULT );


        //
        $filter = [
            'or_like' => $where_or_like,
            'by_is_deleted' => $by_is_deleted,
            'lang_key' => LanguageCost::lang_key(),
            'limit' => -1,
        ];


        /*
         * phân trang
         */
        $count_filter = $filter;
        $count_filter[ 'select_col' ] = 'COUNT(term_id) AS c';
        $totalThread = $this->term_model->get_all_taxonomy( $this->taxonomy, 0, $count_filter );
        //print_r( $totalThread );
        //die( __FILE__ . ':' . __LINE__ );
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
        //echo $offset . '<br>' . "\n";
        //die( __FILE__ . ':' . __LINE__ );

        //
        $pagination = $this->base_model->EBE_pagination( $page_num, $totalPage, $urlPartPage, '&page_num=' );

        //
        $filter[ 'offset' ] = $offset;
        $filter[ 'limit' ] = $post_per_page;
        $filter[ 'get_meta' ] = true;
        $filter[ 'get_child' ] = true;

        //
        $data = $this->term_model->get_all_taxonomy( $this->taxonomy, 0, $filter );
        //print_r( $data );
        //$data = $this->term_model->terms_meta_post( $data );
        //print_r( $data );

        //
        $this->teamplate_admin[ 'content' ] = view( 'admin/terms/list', array(
            'by_is_deleted' => $by_is_deleted,
            'by_keyword' => $by_keyword,
            'data' => $data,
            'pagination' => $pagination,
            'totalThread' => $totalThread,
            'taxonomy' => $this->taxonomy,
            'name_type' => $this->name_type,
            'controller_slug' => $this->controller_slug,
        ) );
        return view( 'admin/admin_teamplate', $this->teamplate_admin );
    }

    public function add() {
        $id = $this->MY_get( 'id', 0 );

        //
        if ( !empty( $this->MY_post( 'data' ) ) ) {
            // nếu là nhân bản
            if ( $this->MY_post( 'is_duplicate', 0 ) * 1 === 1 ) {
                //print_r( $_POST );
                //die( 'fghs ffs' );
                // đổi lại tiêu đề để tránh trùng lặp
                if ( isset( $_POST[ 'data' ][ 'name' ] ) ) {
                    $duplicate_title = explode( '- Duplicate', $_POST[ 'data' ][ 'name' ] );
                    $_POST[ 'data' ][ 'name' ] = trim( $duplicate_title[ 0 ] ) . ' - Duplicate ' . date( 'Ymd-His' );

                    // tạo lại slug
                    $_POST[ 'data' ][ 'slug' ] = '';
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
            $data = $this->term_model->get_all_taxonomy( $this->taxonomy, $id );

            if ( empty( $data ) ) {
                die( 'term not found!' );
            }

            // tự động cập nhật lại slug khi nhân bản
            if ( strstr( $data[ 'slug' ], '-duplicate-' ) == true && strstr( $data[ 'name' ], ' - Duplicate ' ) == false ) {
                //die( DYNAMIC_BASE_URL . ltrim( $_SERVER[ 'REQUEST_URI' ], '/' ) );
                //echo 'bbbbbbbbbbbbb';
                $this->term_model->update_terms( $data[ 'term_id' ], [
                    'name' => $data[ 'name' ],
                    'slug' => '',
                ] );

                //
                die( header( 'location:' . DYNAMIC_BASE_URL . ltrim( $_SERVER[ 'REQUEST_URI' ], '/' ) ) );
            }
        }
        // add
        else {
            $data = $this->base_model->default_data( 'v_terms' );
            /*
            $data = $this->base_model->default_data( 'wp_terms', [
                'wp_term_taxonomy'
            ] );
            */
            $data[ 'term_meta' ] = [];
        }
        //print_r( $data );


        // lấy danh sách các nhóm để add cho post
        $post_cat = $this->term_model->get_all_taxonomy( $this->taxonomy );


        //
        if ( $this->debug_enable === true ) {
            echo '<!-- ';
            print_r( $data );
            echo ' -->';
        }


        //
        $this->teamplate_admin[ 'content' ] = view( 'admin/terms/add', array(
            'lang_key' => LanguageCost::lang_key(),
            'post_cat' => $post_cat,
            'data' => $data,
            'taxonomy' => $this->taxonomy,
            'name_type' => $this->name_type,
            'meta_detault' => TaxonomyType::meta_default( $this->taxonomy ),
        ) );
        return view( 'admin/admin_teamplate', $this->teamplate_admin );
    }

    protected function add_new() {
        $data = $this->MY_post( 'data' );
        //print_r( $data );
        //die( __FILE__ . ':' . __LINE__ );

        //
        $result_id = $this->term_model->insert_terms( $data, $this->taxonomy );

        //
        if ( $result_id > 0 ) {
            //$this->base_model->alert( '', base_url( 'admin/terms/add' ) . '?id=' . $result_id );
            $this->base_model->alert( '', $this->term_model->get_admin_permalink( $this->taxonomy, $result_id, $this->controller_slug ) );
        }
        // nếu tồn tại rồi thì báo đã tồn tại
        else if ( $result_id < 0 ) {
            $this->base_model->alert( 'Danh mục đã tồn tại trong hệ thống (' . $this->taxonomy . ')', 'error' );
        }
        $this->base_model->alert( 'Lỗi tạo ' . TaxonomyType::list( $this->taxonomy, true ) . ' mới', 'error' );
    }

    protected function update( $id ) {
        $data = $this->MY_post( 'data' );
        //print_r( $data );
        //die( __LINE__ );

        //
        $result_id = $this->term_model->update_terms( $id, $data, $this->taxonomy );
        if ( $result_id < 0 ) {
            $this->base_model->alert( 'ERROR! lỗi cập nhật danh mục... Có thể slug đã được sử dụng', 'error' );
        }
        $this->base_model->alert( 'Cập nhật ' . TaxonomyType::list( $this->taxonomy, true ) . ' thành công' );
    }

    // chuyển trang sau khi XÓA xong
    protected function after_delete_restore() {
        $for_redirect = base_url( 'admin/' . $this->controller_slug ) . '?taxonomy=' . $this->taxonomy;

        //
        $is_deleted = $this->MY_get( 'is_deleted' );
        if ( $is_deleted != '' ) {
            $for_redirect .= '&is_deleted=' . $is_deleted;
        }

        //
        $this->base_model->alert( '', $for_redirect );
    }
    protected function before_delete_restore( $is_deleted ) {
        $id = $this->MY_get( 'id', 0 );

        $this->term_model->update_terms( $id, [
            'is_deleted' => $is_deleted,
        ] );

        //
        $this->after_delete_restore();
    }

    public function delete() {
        return $this->before_delete_restore( DeletedStatus::DELETED );
    }

    public function restore() {
        return $this->before_delete_restore( DeletedStatus::FOR_DEFAULT );
    }

    public function term_status() {
        $this->base_model->alert( 'Warning! tính năng chờ cập nhật...', 'warning' );
    }

}