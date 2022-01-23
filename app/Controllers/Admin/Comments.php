<?php
namespace App\ Controllers\ Admin;

// Libraries
use App\ Libraries\ CommentType;
use App\ Libraries\ DeletedStatus;
use App\ Libraries\ LanguageCost;

//
class Comments extends Admin {
    protected $comment_type = CommentType::COMMENT;

    // tham số dùng để thay đổi URL cho controller nếu muốn
    protected $controller_slug = 'comments';

    public function __construct( $for_extends = false ) {
        parent::__construct();

        // kiểm tra quyền truy cập của tài khoản hiện tại
        $this->check_permision( __CLASS__ );

        // hỗ trợ lấy theo params truyền vào từ url
        $this->comment_type = $this->MY_get( 'comment_type', $this->comment_type );

        // báo lỗi nếu không xác định được taxonomy
        // chỉ kiểm tra các điều kiện này nếu không được chỉ định là extends
        if ( $for_extends === false ) {
            //if ( $this->comment_type == '' || CommentType::list( $this->comment_type ) == '' ) {
            if ( CommentType::list( $this->comment_type ) == '' ) {
                die( 'comment type not register in system!' );
            }
        }
    }

    public function index() {
        $comment_id = $this->MY_get( 'comment_id' );
        if ( $comment_id > 0 ) {
            return $this->details( $comment_id );
        }

        //
        $post_per_page = 50;

        // URL cho phân trang tìm kiếm
        $urlPartPage = 'admin/' . $this->controller_slug;
        $urlParams = [];

        // các kiểu điều kiện where
        $where = [
            'comments.is_deleted' => DeletedStatus::FOR_DEFAULT,
            'comments.comment_type' => $this->comment_type,
            'comments.lang_key' => LanguageCost::lang_key()
        ];

        $filter = [
            'order_by' => array(
                'comments.comment_ID' => 'DESC',
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
        $totalThread = $this->base_model->select( 'COUNT(comment_ID) AS c', 'comments', $where, $filter );
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
        $data = $this->base_model->select( '*', 'comments', $where, $filter );
        //print_r( $data );
        //die('fj gd sdgsd');

        //
        //$data = $this->post_model->list_meta_post( $data );
        foreach ( $data as $k => $v ) {
            if ( $v[ 'comment_title' ] == '' ) {
                $v[ 'comment_title' ] = strip_tags( $v[ 'comment_content' ] );
                $v[ 'comment_title' ] = explode( "\n", $v[ 'comment_title' ] );
                $v[ 'comment_title' ] = $v[ 'comment_title' ][ 0 ];
            }

            //
            $v[ 'comment_content' ] = '';
            $data[ $k ] = $v;
        }
        //print_r( $data );

        //
        $this->teamplate_admin[ 'content' ] = view( 'admin/comments/list', array(
            'pagination' => $pagination,
            'totalThread' => $totalThread,
            'data' => $data,
            'comment_type' => $this->comment_type,
            'controller_slug' => $this->controller_slug,
        ) );
        return view( 'admin/admin_teamplate', $this->teamplate_admin );
    }

    // hiển thị chi tiết 1 comment/ liên hệ
    protected function details( $comment_id ) {
        //echo $comment_id . '<br>' . "\n";

        //
        $data = $this->base_model->select( '*', 'comments', [
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
            'controller_slug' => $this->controller_slug,
        ) );
        return view( 'admin/admin_teamplate', $this->teamplate_admin );
    }
}