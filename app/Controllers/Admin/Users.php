<?php
//require_once __DIR__ . '/Admin.php';
namespace App\ Controllers\ Admin;

// Libraries
use App\ Libraries\ DeletedStatus;

//
class Users extends Admin {
    private $member_type = '';

    public function __construct() {
        parent::__construct();

        // kiểm tra quyền truy cập của tài khoản hiện tại
        $this->check_permision( __CLASS__ );

        $this->member_type = $this->MY_get( 'member_type' );
    }

    public function index( $url = '' ) {
        $post_per_page = 50;

        // các kiểu điều kiện where
        $where = [
            'wp_users.is_deleted' => DeletedStatus::DEFAULT,
        ];
        if ( $this->member_type != '' ) {
            $where[ 'wp_users.member_type' ] = $this->member_type;
        }

        // tìm kiếm theo từ khóa nhập vào
        $by_keyword = $this->MY_get( 's', '' );
        $where_or_like = [];
        // URL cho phân trang tìm kiếm
        $urlPartPage = 'admin/users?member_type=' . $this->member_type;
        if ( $by_keyword != '' ) {
            $urlPartPage .= '&s=' . $by_keyword;

            //
            $by_like = $this->base_model->_eb_non_mark_seo( $by_keyword );
            // tối thiểu từ 3 ký tự trở lên mới kích hoạt tìm kiếm
            if ( strlen( $by_like ) > 2 ) {
                $is_number = is_numeric( $by_like );
                // nếu là số -> chỉ tìm theo ID
                if ( $is_number === true ) {
                    $where_or_like = [
                        'ID' => $by_like,
                    ];
                } else {
                    $is_email = strpos( $by_keyword, '@' );
                    // nếu có @ -> tìm theo email
                    if ( $is_email !== false ) {
                        $where_or_like = [
                            'user_email' => explode( '@', $by_keyword )[ 0 ],
                        ];
                    }
                    // còn lại thì có gì tìm hết
                    else {
                        $where_or_like = [
                            'ID' => $by_like,
                            'user_login' => $by_like,
                            'user_email' => $by_keyword,
                            //'display_name' => $by_like,
                            'user_url' => $by_like,
                            'display_name' => $by_keyword,
                        ];
                    }
                }
            }
        }

        //
        $filter = [
            'or_like' => $where_or_like,
            'order_by' => array(
                'wp_users.ID' => 'DESC',
            ),
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 0,
            //'limit' => $post_per_page;
        ];


        /*
         * phân trang
         */
        $totalThread = $this->base_model->select( 'COUNT(ID) AS c', 'wp_users', $where, $filter );
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
        $data = $this->base_model->select( '*', 'wp_users', $where, $filter );
        //print_r( $data );

        //
        $this->teamplate_admin[ 'content' ] = view( 'admin/users/list', array(
            'pagination' => $pagination,
            'totalThread' => $totalThread,
            'by_keyword' => $by_keyword,
            'data' => $data,
            'member_type' => $this->member_type,
        ) );
        return view( 'admin/admin_teamplate', $this->teamplate_admin );
    }

    public function add() {
        $id = $this->MY_get( 'id', 0 );

        //
        if ( !empty( $this->MY_post( 'data' ) ) ) {
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
            $data = $this->base_model->select( '*', 'wp_users', [
                'ID' => $id
            ], array(
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 2,
                'limit' => 1
            ) );

            if ( empty( $data ) ) {
                die( 'user not found!' );
            }
        }
        // add
        else {
            $data = $this->base_model->default_data( 'wp_users' );
        }
        //print_r( $data );
        //die( 'dgh dfsfs' );

        //
        $this->teamplate_admin[ 'content' ] = view( 'admin/users/add', array(
            'data' => $data,
            'member_type' => $this->member_type,
        ) );
        return view( 'admin/admin_teamplate', $this->teamplate_admin );
    }

    public function add_new() {
        $data = $this->MY_post( 'data' );
        //print_r( $data );
        //die( __FILE__ . ':' . __LINE__ );

        //
        $result_id = $this->user_model->insert_member( $data );
        if ( $result_id > 0 ) {
            $this->base_model->alert( '', base_url( 'admin/users/add' ) . '?id=' . $result_id );
        } else if ( $insert === -1 ) {
            $this->base_model->alert( 'Email đã được sử dụng', 'error' );
        }
        $this->base_model->alert( 'Lỗi thêm mới thành viên', 'error' );
    }

    public function update( $id ) {
        $data = $this->MY_post( 'data' );
        //print_r( $data );
        //die( __LINE__ );

        //
        $result_id = $this->user_model->update_member( $id, $data );

        $this->base_model->alert( 'Cập nhật thông tin thành viên ' . $data[ 'user_email' ] . ' thành công' );
    }

    public function delete() {
        $current_user_id = $this->session_data[ 'userID' ];
        if ( empty( $current_user_id ) ) {
            $this->base_model->alert( 'Không xác định được ID của bạn!', 'error' );
        }

        //
        $id = $this->MY_get( 'id', 0 );

        //
        if ( $current_user_id == $id ) {
            $this->base_model->alert( 'Không thể tự xóa chính bạn!', 'warning' );
        }

        //
        $this->user_model->update_member( $id, [
            'is_deleted' => DeletedStatus::DELETED,
        ] );

        $this->base_model->alert( '', base_url( 'admin/users' ) );
    }

}