<?php
//require_once __DIR__ . '/Admin.php';
namespace App\ Controllers\ Admin;

// Libraries
use App\ Libraries\ TaxonomyType;
use App\ Libraries\ LanguageCost;
use App\ Libraries\ DeletedStatus;

//
class Terms extends Admin {
    private $taxonomy = '';
    private $default_taxonomy = '';

    public function __construct() {
        parent::__construct();

        // kiểm tra quyền truy cập của tài khoản hiện tại
        $this->check_permision( __CLASS__ );

        // lọc term dựa theo taxonomy
        $this->default_taxonomy = TaxonomyType::POSTS;
        $this->taxonomy = $this->MY_get( 'taxonomy', $this->default_taxonomy );

        // báo lỗi nếu không xác định được taxonomy
        if ( $this->taxonomy == '' || TaxonomyType::list( $this->taxonomy ) == '' ) {
            die( 'Taxonomy not register in system!' );
        }
    }

    public function index() {
        // tìm kiếm theo từ khóa nhập vào
        $by_keyword = $this->MY_get( 's', '' );
        $where_or_like = [];
        if ( $by_keyword != '' ) {
            $by_like = $this->base_model->_eb_non_mark_seo( $by_keyword );
            // tối thiểu từ 3 ký tự trở lên mới kích hoạt tìm kiếm
            if ( strlen( $by_like ) > 2 ) {
                $is_number = is_numeric( $by_like );
                // nếu là số -> chỉ tìm theo ID
                if ( $is_number === true ) {
                    $where_or_like = [
                        'wp_terms.term_id' => $by_like,
                    ];
                } else {
                    $where_or_like = [
                        'wp_terms.term_id' => $by_like,
                        'wp_terms.slug' => $by_like,
                        'wp_terms.name' => $by_keyword,
                    ];
                }
            }
        }

        $data = $this->term_model->get_all_taxonomy( $this->taxonomy, 0, [
            'or_like' => $where_or_like,
            'lang_key' => LanguageCost::lang_key(),
            'get_meta' => true,
            'get_child' => true
        ] );
        //print_r( $data );
        //$data = $this->term_model->terms_meta_post( $data );
        //print_r( $data );

        //
        $this->teamplate_admin[ 'content' ] = view( 'admin/terms/list', array(
            'by_keyword' => $by_keyword,
            'data' => $data,
            'pagination' => '',
            'taxonomy' => $this->taxonomy,
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
            $data = $this->base_model->default_data( 'wp_terms', [
                'wp_term_taxonomy'
            ] );
            $data[ 'term_meta' ] = [];
        }
        //print_r( $data );


        // lấy danh sách các nhóm để add cho post
        $post_cat = $this->term_model->get_all_taxonomy( $this->taxonomy );

        //
        $this->teamplate_admin[ 'content' ] = view( 'admin/terms/add', array(
            'lang_key' => LanguageCost::lang_key(),
            'post_cat' => $post_cat,
            'data' => $data,
            'taxonomy' => $this->taxonomy,
            'meta_detault' => TaxonomyType::meta_default( $this->taxonomy ),
        ) );
        return view( 'admin/admin_teamplate', $this->teamplate_admin );
    }

    public function add_new() {
        $data = $this->MY_post( 'data' );
        //print_r( $data );
        //die( __FILE__ . ':' . __LINE__ );

        //
        $result_id = $this->term_model->insert_terms( $data, $this->taxonomy );

        if ( $result_id > 0 ) {
            //$this->base_model->alert( '', base_url( 'admin/terms/add' ) . '?id=' . $result_id );
            $this->base_model->alert( '', $this->term_model->get_admin_permalink( $this->taxonomy, $result_id ) );
        }
        $this->base_model->alert( 'Lỗi tạo ' . TaxonomyType::list( $this->taxonomy, true ) . ' mới', 'error' );
    }


    public function update( $id ) {
        $data = $this->MY_post( 'data' );
        //print_r( $data );
        //die( __LINE__ );

        //
        $result_id = $this->term_model->update_terms( $id, $data, $this->taxonomy );

        $this->base_model->alert( 'Cập nhật ' . TaxonomyType::list( $this->taxonomy, true ) . ' thành công' );
    }

    public function delete() {
        $id = $this->MY_get( 'id', 0 );

        $this->term_model->update_terms( $id, [
            'is_deleted' => DeletedStatus::DELETED,
        ] );

        $this->base_model->alert( '', base_url( 'admin/terms' ) . '?taxonomy=' . $this->taxonomy );
    }

}