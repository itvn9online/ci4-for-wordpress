<?php
//require_once __DIR__ . '/Admin.php';
namespace App\ Controllers\ Admin;

// Libraries
use App\ Libraries\ PostType;

//
class Uploads extends Admin {
    protected $post_type = PostType::MEDIA;
    protected $name_type = '';
    protected $controller_slug = 'uploads';

    public function __construct() {
        parent::__construct();

        // kiểm tra quyền truy cập của tài khoản hiện tại
        $this->check_permision( __CLASS__ );
    }

    public function index( $url = '' ) {
        //print_r( $_POST );
        //print_r( $this->MY_post( 'data' ) );
        if ( !empty( $this->MY_post( 'data' ) ) ) {
            $this->upload();
        }

        //
        $post_per_page = 50;

        // các kiểu điều kiện where
        $where = [
            'post_status !=' => PostType::DELETED,
        ];

        // tìm kiếm theo từ khóa nhập vào
        $by_keyword = $this->MY_get( 's' );
        $where_or_like = [];
        // URL cho phân trang tìm kiếm
        $urlPartPage = 'admin/' . $this->controller_slug;
        $urlParams = [];

        // loại bớt các tham số trong URL
        //print_r( $_GET );
        $arr_deny_params = [
            'post_type',
            's',
            'page_num',
        ];
        $hiddenSearchForm = [];
        foreach ( $_GET as $k => $v ) {
            if ( in_array( $k, $arr_deny_params ) ) {
                continue;
            }
            $urlParams[] = $k . '=' . $v;
            $hiddenSearchForm[ $k ] = $v;
        }

        //
        if ( $by_keyword != '' ) {
            $urlParams[] = 's=' . $by_keyword;

            //
            $by_like = $this->base_model->_eb_non_mark_seo( $by_keyword );
            // tối thiểu từ 1 ký tự trở lên mới kích hoạt tìm kiếm
            if ( strlen( $by_like ) > 0 ) {
                //var_dump( strlen( $by_like ) );
                $where_or_like = [
                    //'ID' => $by_like,
                    'post_name' => $by_like,
                    'post_title' => $by_keyword,
                ];
            }
        }

        //
        $filter = [
            'where_in' => array(
                'post_type' => array(
                    $this->post_type,
                    PostType::WP_MEDIA,
                )
            ),
            'or_like' => $where_or_like,
            'order_by' => array(
                //'menu_order' => 'DESC',
                'ID' => 'DESC',
                //'post_date' => 'DESC',
                //'post_modified' => 'DESC',
            ),
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 0,
            //'limit' => $post_per_page
        ];


        /*
         * phân trang
         */
        $totalThread = $this->base_model->select( 'COUNT(ID) AS c', 'posts', $where, $filter );
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
        $urlParams[] = 'page_num=';
        $urlPartPage .= '?' . implode( '&', $urlParams );
        $pagination = $this->base_model->EBE_pagination( $page_num, $totalPage, $urlPartPage, '' );


        // select dữ liệu từ 1 bảng bất kỳ
        $filter[ 'offset' ] = $offset;
        $filter[ 'limit' ] = $post_per_page;
        $data = $this->base_model->select( '*', 'posts', $where, $filter );

        //
        $data = $this->post_model->list_meta_post( $data );
        //print_r( $data );

        //
        $this->teamplate_admin[ 'body_class' ] = $this->body_class;

        //
        $this->teamplate_admin[ 'content' ] = view( 'admin/uploads/list', array(
            'by_keyword' => $by_keyword,
            'data' => $data,
            'hiddenSearchForm' => $hiddenSearchForm,
            'pagination' => $pagination,
            'totalThread' => $totalThread,
            //'taxonomy' => $this->taxonomy,
            'post_type' => $this->post_type,
            'controller_slug' => $this->controller_slug,
            'name_type' => PostType::list( $this->post_type ),
        ) );
        return view( 'admin/admin_teamplate', $this->teamplate_admin );
    }

    protected function upload( $key = 'upload_image' ) {
        // gọi tới function upload ảnh thôi
        $list_upload = $this->media_upload( false );
        //die( 'fg dfhdfhfd' );

        // -> gọi hàm này để nó nạp lại trang cha
        $this->alert( '' );
    }

    public function delete() {
        $id = $this->MY_get( 'id', 0 );
        $id *= 1;
        if ( $id <= 0 ) {
            return false;
        }

        //
        $data = $this->post_model->select_post( $id, [
            'post_type' => $this->post_type,
        ] );
        if ( empty( $data ) ) {
            $data = $this->post_model->select_post( $id, [
                'post_type' => PostType::WP_MEDIA,
            ] );
        }
        $update = false;
        if ( !empty( $data ) ) {
            //print_r( $data );

            //
            if ( $data[ 'post_type' ] == PostType::WP_MEDIA ) {
                $secondes_path = PostType::WP_MEDIA_URI;
            } else {
                $secondes_path = PostType::MEDIA_PATH;
            }
            $secondes_path = PUBLIC_HTML_PATH . $secondes_path;
            //echo $secondes_path . '<br>' . "\n";
            //die( __FILE__ . ':' . __LINE__ );

            //
            $delete_file = [];
            // Don't attempt to unserialize data that wasn't serialized going in.
            if ( isset( $data[ 'post_meta' ][ '_wp_attachment_metadata' ] ) && $data[ 'post_meta' ][ '_wp_attachment_metadata' ] != '' ) {
                //if ( is_serialized( $v[ 'post_meta' ][ '_wp_attachment_metadata' ] ) ) {
                $attachment_metadata = unserialize( $data[ 'post_meta' ][ '_wp_attachment_metadata' ] );
                //}

                //print_r( $attachment_metadata );
                if ( empty( $attachment_metadata ) ) {
                    return '';
                }
                //print_r( $attachment_metadata );

                $src = $attachment_metadata[ 'file' ];
                $delete_file[] = $src;
                if ( isset( $attachment_metadata[ 'sizes' ] ) ) {
                    foreach ( $attachment_metadata[ 'sizes' ] as $size_name => $size ) {
                        $delete_file[] = dirname( $src ) . '/' . $size[ 'file' ];
                    }
                }
            } else if ( isset( $data[ 'post_meta' ][ '_wp_attached_file' ] ) && $data[ 'post_meta' ][ '_wp_attached_file' ] != '' ) {
                $delete_file[] = $data[ 'post_meta' ][ '_wp_attached_file' ];
            }
            //print_r( $delete_file );
            foreach ( $delete_file as $v ) {
                $remove_file = $secondes_path . $v;

                //
                if ( file_exists( $remove_file ) ) {
                    //echo $remove_file . '<br>' . "\n";
                    $this->MY_unlink( $remove_file )or die( 'ERROR remove upload file: ' . $v );
                }
            }
            //die( 'delete media' );

            //
            $update = $this->post_model->update_post( $data[ 'ID' ], [
                'post_status' => PostType::DELETED
            ], [
                'post_type' => $data[ 'post_type' ],
            ] );
        }

        //
        if ( $update === true ) {
            $this->done_delete_restore( $id );
        }
        $this->alert( '' );
    }
    protected function done_delete_restore( $id ) {
        die( '<script>top.done_delete_restore(' . $id . ');</script>' );
    }

    protected function alert( $m, $url = '' ) {
        if ( $url == '' ) {
            $url = base_url( 'admin/uploads' );
            $uri_quick_upload = [];
            foreach ( $_GET as $k => $v ) {
                if ( $k != 'id' ) {
                    $uri_quick_upload[] = $k . '=' . $v;
                }
            }
            if ( !empty( $uri_quick_upload ) ) {
                $url .= '?' . implode( '&', $uri_quick_upload );
            }
            //die( $url );
        }

        //
        //$this->base_model->alert( '', $url );
        die( '<script>parent.window.location = "' . $url . '";</script>' );
    }

    // tối ưu hóa ảnh -> nhiều quả ảnh up lên nhưng quá nặng -> cần tối ưu hóa lại chút
    public function optimize() {
        $post_per_page = 50;

        // các kiểu điều kiện where
        $where = [
            //'post_parent' => 688, // TEST
            'post_status !=' => PostType::DELETED,
        ];

        // URL cho phân trang tìm kiếm
        $urlPartPage = 'admin/' . $this->controller_slug . '/optimize';

        //
        $filter = [
            'where_in' => array(
                'post_type' => array(
                    $this->post_type,
                    PostType::WP_MEDIA,
                )
            ),
            'order_by' => array(
                //'menu_order' => 'DESC',
                'ID' => 'DESC',
                //'post_date' => 'DESC',
                //'post_modified' => 'DESC',
            ),
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 0,
            //'limit' => $post_per_page
        ];


        /*
         * phân trang
         */
        $totalThread = $this->base_model->select( 'COUNT(ID) AS c', 'posts', $where, $filter );
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
        $pagination = $this->base_model->EBE_pagination( $page_num, $totalPage, $urlPartPage, '?page_num=' );


        // select dữ liệu từ 1 bảng bất kỳ
        $filter[ 'offset' ] = $offset;
        $filter[ 'limit' ] = $post_per_page;
        $data = $this->base_model->select( '*', 'posts', $where, $filter );

        //
        $data = $this->post_model->list_meta_post( $data );
        //print_r( $data );

        //
        $this->teamplate_admin[ 'body_class' ] = $this->body_class;

        //
        $this->teamplate_admin[ 'content' ] = view( 'admin/uploads/optimize', array(
            'data' => $data,
            'pagination' => $pagination,
            'totalThread' => $totalThread,
            //'taxonomy' => $this->taxonomy,
            'post_type' => $this->post_type,
            'controller_slug' => $this->controller_slug,
            'name_type' => PostType::list( $this->post_type ),
        ) );
        return view( 'admin/admin_teamplate', $this->teamplate_admin );
    }
}