<?php
namespace App\ Controllers\ Admin;

// Libraries
use App\ Libraries\ TaxonomyType;
use App\ Libraries\ LanguageCost;
use App\ Libraries\ DeletedStatus;

//
class Terms extends Admin {
    protected $taxonomy = TaxonomyType::POSTS;
    protected $name_type = '';
    //private $default_taxonomy = '';

    // tham số dùng để thay đổi URL cho controller nếu muốn
    protected $controller_slug = 'terms';
    // tham số dùng để đổi file view khi add hoặc edit bài viết nếu muốn
    protected $add_edit_view = 'terms';

    public function __construct( $for_extends = false ) {
        parent::__construct();

        // kiểm tra quyền truy cập của tài khoản hiện tại
        $this->check_permision( __CLASS__ );

        // hỗ trợ lấy theo params truyền vào từ url
        $this->taxonomy = $this->MY_get( 'taxonomy', $this->taxonomy );

        // chỉ kiểm tra các điều kiện này nếu không được chỉ định là extends
        if ( $for_extends === false ) {
            // lọc term dựa theo taxonomy
            //$this->default_taxonomy = TaxonomyType::POSTS;
            $this->name_type = TaxonomyType::list( $this->taxonomy, true );

            // báo lỗi nếu không xác định được taxonomy
            if ( $this->name_type == '' ) {
                die( 'Taxonomy not register in system!' );
            }
        }
    }

    public function index() {
        $post_per_page = 25;
        // URL cho các action dùng chung
        $for_action = '';
        // URL cho phân trang
        $urlPartPage = 'admin/' . $this->controller_slug . '?part_type=' . $this->taxonomy;

        //
        $by_keyword = $this->MY_get( 's' );
        $by_is_deleted = $this->MY_get( 'is_deleted', DeletedStatus::FOR_DEFAULT );
        $page_num = $this->MY_get( 'page_num', 1 );

        //
        if ( $by_is_deleted > 0 ) {
            $urlPartPage .= '&is_deleted=' . $by_is_deleted;
            $for_action .= '&is_deleted=' . $by_is_deleted;
        }

        // tìm kiếm theo từ khóa nhập vào
        $where_or_like = [];
        if ( $by_keyword != '' ) {
            $urlPartPage .= '&s=' . $by_keyword;
            $for_action .= '&s=' . $by_keyword;

            //
            $by_like = $this->base_model->_eb_non_mark_seo( $by_keyword );
            // tối thiểu từ 1 ký tự trở lên mới kích hoạt tìm kiếm
            if ( strlen( $by_like ) > 0 ) {
                //var_dump( strlen( $by_like ) );
                // nếu là số -> chỉ tìm theo ID
                if ( is_numeric( $by_like ) === true ) {
                    $where_or_like = [
                        'term_id' => $by_like,
                        //'parent' => $by_like,
                    ];
                } else {
                    $where_or_like = [
                        'slug' => $by_like,
                        'name' => $by_keyword,
                    ];
                }
            }
        }

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
        //echo $totalPage . '<br>' . "\n";
        if ( $page_num > $totalPage ) {
            $page_num = $totalPage;
        } else if ( $page_num < 1 ) {
            $page_num = 1;
        }
        $for_action .= $page_num > 1 ? '&page_num=' . $page_num : '';
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
        // daidq (2021-01-24): tạm thời không cần lấy nhóm cấp 1
        if ( $this->taxonomy == TaxonomyType::ADS ) {
            $filter[ 'get_meta' ] = true;
        }
        //$filter[ 'get_child' ] = 1;

        //
        $data = $this->term_model->get_all_taxonomy( $this->taxonomy, 0, $filter );
        //print_r( $data );
        //$data = $this->term_model->terms_meta_post( $data );
        //print_r( $data );

        //
        $data = $this->term_treeview_data( $data );
        //echo __FILE__ . ':' . __LINE__ . '<br>' . "\n";
        //print_r( $data );

        //
        $this->teamplate_admin[ 'content' ] = view( 'admin/terms/list', array(
            'for_action' => $for_action,
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

    private function term_treeview_data( $data ) {
        foreach ( $data as $k => $v ) {
            $v[ 'get_admin_permalink' ] = $this->term_model->get_admin_permalink( $v[ 'taxonomy' ], $v[ 'term_id' ], $this->controller_slug );
            $v[ 'view_url' ] = $this->term_model->get_the_permalink( $v );
            $v[ 'gach_ngang' ] = '';

            // phiên bản dùng angular js -> có sử dụng child_term
            /*
            if ( count( $v[ 'child_term' ] ) > 0 ) {
                $v[ 'child_term' ] = $this->term_treeview_data( $v[ 'child_term' ] );
            }
            */

            //
            $data[ $k ] = $v;
        }
        return $data;
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
                die( header( 'Location:' . DYNAMIC_BASE_URL . ltrim( $_SERVER[ 'REQUEST_URI' ], '/' ) ) );
            }
        }
        // add
        else {
            $data = $this->base_model->default_data( WGR_TERM_VIEW );
            /*
            $data = $this->base_model->default_data( 'terms', [
                'term_taxonomy'
            ] );
            */
            $data[ 'term_meta' ] = [];
        }
        //print_r( $data );


        // lấy danh sách các nhóm để tạo cha con
        $set_parent = '';
        if ( in_array( $this->taxonomy, [
                TaxonomyType::POSTS,
                TaxonomyType::BLOGS,
            ] ) ) {
            $set_parent = $this->taxonomy;
        }
        // với custom taxonomy -> kiểm tra xem có tham số set cha con không
        else {
            global $arr_custom_taxonomy;
            //print_r( $arr_custom_taxonomy );

            //
            if ( isset( $arr_custom_taxonomy[ $this->taxonomy ] ) && isset( $arr_custom_taxonomy[ $this->taxonomy ][ 'set_parent' ] ) ) {
                $set_parent = $this->taxonomy;
            }
        }


        //
        if ( $this->debug_enable === true ) {
            echo '<!-- ';
            print_r( $data );
            echo ' -->';
        }


        //
        $this->teamplate_admin[ 'content' ] = view( 'admin/' . $this->add_edit_view . '/add', array(
            'lang_key' => LanguageCost::lang_key(),
            'set_parent' => $set_parent,
            'data' => $data,
            'taxonomy' => $this->taxonomy,
            'name_type' => $this->name_type,
            'meta_detault' => TaxonomyType::meta_default( $this->taxonomy ),
            'controller_slug' => $this->controller_slug,
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
        $for_redirect = base_url( 'admin/' . $this->controller_slug );
        $urlParams = [];

        //
        $is_deleted = $this->MY_get( 'is_deleted' );
        if ( $is_deleted != '' ) {
            $urlParams[] = 'is_deleted=' . $is_deleted;
        }

        //
        if ( count( $urlParams ) > 0 ) {
            $for_redirect .= '?' . implode( '&', $urlParams );
        }
        $this->base_model->alert( '', $for_redirect );
    }
    protected function done_delete_restore( $id ) {
        die( '<script>top.done_delete_restore(' . $id . ');</script>' );
    }
    protected function before_delete_restore( $is_deleted ) {
        $id = $this->MY_get( 'id', 0 );

        $update = $this->term_model->update_terms( $id, [
            'is_deleted' => $is_deleted,
        ] );

        // nếu update thành công -> gửi lệnh javascript để ẩn bài viết bằng javascript
        if ( $update === true ) {
            return $this->done_delete_restore( $id );
        }
        // không thì nạp lại cả trang để kiểm tra cho chắc chắn
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