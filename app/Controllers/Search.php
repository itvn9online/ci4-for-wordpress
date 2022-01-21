<?php
//require_once __DIR__ . '/Layout.php';
namespace App\ Controllers;

// Libraries
//use App\ Libraries\ DeletedStatus;
//use App\ Libraries\ TaxonomyType;
use App\ Libraries\ PostType;
//use App\ Libraries\ LanguageCost;

class Search extends Csrf {
    protected $post_type = PostType::POST;

    public function __construct() {
        parent::__construct();

        //
        $this->post_type = $this->MY_get( 'post_type', $this->post_type );
    }

    // tìm kiếm
    public function index() {
        //print_r( $_GET );

        //
        $this->create_breadcrumb( 'Tìm kiếm' );
        $pagination = '';
        $data = [];
        $totalThread = 0;

        //
        $by_keyword = trim( $this->MY_get( 's' ) );
        if ( !empty( $by_keyword ) ) {
            $this->create_breadcrumb( $by_keyword );

            //
            //$post_per_page = 20;
            $post_per_page = $this->base_model->get_config( $this->getconfig, 'eb_blogs_per_page', 10 );

            // các kiểu điều kiện where
            $where = [
                'posts.post_type' => $this->post_type,
                'posts.post_status' => PostType::PUBLIC,
                'posts.lang_key' => $this->lang_key
            ];
            //print_r( $where );

            // tìm kiếm theo từ khóa nhập vào
            $where_or_like = [];
            // URL cho phân trang tìm kiếm
            $urlPartPage = $this->base_class_url( __CLASS__ );
            $urlParams = [];
            $urlParams[] = 's=' . $by_keyword;

            //
            $by_like = $this->base_model->_eb_non_mark_seo( $by_keyword );
            // tối thiểu từ 3 ký tự trở lên mới kích hoạt tìm kiếm
            if ( strlen( $by_like ) > 0 ) {
                //var_dump( strlen( $by_like ) );
                $is_number = is_numeric( $by_like );
                // nếu là số -> chỉ tìm theo ID
                if ( $is_number === true ) {
                    $where_or_like = [
                        'ID' => $by_like,
                    ];
                } else {
                    $where_or_like = [
                        //'ID' => $by_like,
                        'post_name' => $by_like,
                        'post_title' => $by_keyword,
                    ];
                }
            }
            //print_r( $where_or_like );


            // tổng kết filter
            $filter = [
                /*
                'where_in' => array(
                    'posts.post_type' => array(
                        PostType::POST,
                        PostType::BLOG,
                        PostType::PAGE,
                    )
                ),
                */
                'or_like' => $where_or_like,
                'order_by' => array(
                    'posts.menu_order' => 'DESC',
                    'posts.post_date' => 'DESC',
                    //'post_modified' => 'DESC',
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 0,
                //'limit' => $post_per_page
            ];
            //print_r( $filter );


            /*
             * phân trang
             */
            $totalThread = $this->base_model->select( 'COUNT(ID) AS c', 'posts', $where, $filter );
            //print_r( $totalThread );
            $totalThread = $totalThread[ 0 ][ 'c' ];
            //echo $totalThread . '<br>' . "\n";
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
            //echo $pagination . '<br>' . "\n";


            // select dữ liệu từ 1 bảng bất kỳ
            $filter[ 'offset' ] = $offset;
            $filter[ 'limit' ] = $post_per_page;
            //print_r( $filter );

            //
            $data = $this->base_model->select( '*', 'posts', $where, $filter );

            //
            $data = $this->post_model->list_meta_post( $data );
            //print_r( $data );
        }
        //die( __FILE__ . ':' . __LINE__ );

        //
        $seo = $this->base_model->default_seo( trim( 'Tìm kiếm ' . $by_keyword ), base_url() );

        // -> views
        $this->teamplate[ 'breadcrumb' ] = view( 'breadcrumb_view', array(
            'breadcrumb' => $this->breadcrumb
        ) );

        //
        $this->teamplate[ 'main' ] = view( 'search_view', array(
            'totalThread' => $totalThread,
            'by_keyword' => $by_keyword,
            'seo' => $seo,
            'post_type' => $this->post_type,
            'public_part_page' => $pagination,
            'data' => $data,
        ) );
        return view( 'layout_view', $this->teamplate );
    }

    // trả về json chứa dữ liệu của các bản ghi phục vụ cho tìm kiếm nhanh
    public function quick_search() {
        $data = [];

        //
        $post_ops = [
            // không cần lấy post meta
            'no_meta' => 1,
            // chỉ lấy 1 số cột nhất định
            'select' => 'ID, post_title, post_name, post_type',
            // số lượng bản ghi cần lấy
            'limit' => 500,
        ];

        //
        $data[ 'post' ] = $this->post_model->get_posts_by( [], $post_ops );
        $data[ 'blog' ] = $this->post_model->get_blogs_by( [], $post_ops );

        //
        //print_r( $data );


        // định dạng json
        header( 'Content-type: application/json; charset=utf-8' );
        header( 'Access-Control-Allow-Origin: *' );
        header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
        header( 'Cache-Control: post-check=0, pre-check=0', false );
        header( 'Pragma: no-cache' );

        //
        echo json_encode( $data );
    }
}