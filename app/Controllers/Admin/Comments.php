<?php
//require_once __DIR__ . '/Admin.php';
namespace App\ Controllers\ Admin;

// Libraries
use App\ Libraries\ CommentType;
use App\ Libraries\ DeletedStatus;
use App\ Libraries\ LanguageCost;

//
class Comments extends Admin {
    public function __construct() {
        parent::__construct();

        // kiểm tra quyền truy cập của tài khoản hiện tại
        $this->check_permision( __CLASS__ );

        $this->comment_type = $this->MY_get( 'comment_type', CommentType::CONTACT );

        // báo lỗi nếu không xác định được taxonomy
        if ( $this->comment_type == '' || CommentType::list( $this->comment_type ) == '' ) {
            die( 'comment_type not register in system!' );
        }
    }

    public function index() {
        $comment_id = $this->MY_get( 'comment_id' );
        if ( $comment_id > 0 ) {
            return $this->details( $comment_id );
        }

        //
        $post_per_page = 20;

        // các kiểu điều kiện where
        $where = [
            'wp_comments.is_deleted' => DeletedStatus::FOR_DEFAULT,
            'wp_comments.comment_type' => $this->comment_type,
            'wp_comments.lang_key' => LanguageCost::lang_key()
        ];

        $filter = [
            'order_by' => array(
                'wp_comments.comment_ID' => 'DESC',
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
        $totalThread = $this->base_model->select( 'COUNT(comment_ID) AS c', 'wp_comments', $where, $filter );
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
        $pagination = $this->base_model->EBE_pagination( $page_num, $totalPage, 'admin/comments?comment_type=' . $this->comment_type, '&page_num=' );


        // select dữ liệu từ 1 bảng bất kỳ
        $filter[ 'offset' ] = $offset;
        $filter[ 'limit' ] = $post_per_page;
        $data = $this->base_model->select( '*', 'wp_comments', $where, $filter );
        //print_r( $data );
        //die('fj gd sdgsd');

        //
        //$data = $this->post_model->list_meta_post( $data );
        //print_r( $data );

        //
        $this->teamplate_admin[ 'content' ] = view( 'admin/comments/list', array(
            'pagination' => $pagination,
            'totalThread' => $totalThread,
            'data' => $data,
            'comment_type' => $this->comment_type,
        ) );
        return view( 'admin/admin_teamplate', $this->teamplate_admin );
    }

    // hiển thị chi tiết 1 comment/ liên hệ
    protected function details( $comment_id ) {
        //echo $comment_id . '<br>' . "\n";

        //
        $data = $this->base_model->select( '*', 'wp_comments', [
            //'is_deleted' => DeletedStatus::DEFAULT,
            'comment_ID' => $comment_id,
            'comment_type' => $this->comment_type,
            //'lang_key' => LanguageCost::lang_key()
        ], [
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 0,
            'limit' => 1
        ] );
        //print_r( $data );

        //
        $this->teamplate_admin[ 'content' ] = view( 'admin/comments/details', array(
            'data' => $data,
            'comment_type' => $this->comment_type,
        ) );
        return view( 'admin/admin_teamplate', $this->teamplate_admin );
    }
}