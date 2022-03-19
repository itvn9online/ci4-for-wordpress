<?php
namespace App\ Controllers\ Admin;

// Libraries
use App\ Libraries\ DeletedStatus;
use App\ Libraries\ UsersType;

//
class Users extends Admin {
    protected $member_type = '';
    protected $arr_members_type = NULL;
    protected $member_name = '';

    // tham số dùng để thay đổi URL cho controller nếu muốn
    protected $controller_slug = 'users';
    // tham số dùng để đổi file view khi add hoặc edit bài viết nếu muốn
    protected $add_edit_view = 'users';
    // tham số dùng để thay đổi view của trang danh sách thành viên
    protected $custom_list_view = 'users';

    public function __construct() {
        parent::__construct();

        // kiểm tra quyền truy cập của tài khoản hiện tại
        $this->check_permision( __CLASS__ );

        $this->member_type = $this->MY_get( 'member_type', $this->member_type );
        //echo $this->member_type . '<br>' . "\n";
        if ( $this->member_type == '' ) {
            $this->member_name = UsersType::ALL;
        } else if ( $this->member_name == '' ) {
            $this->member_name = UsersType::list( $this->member_type );
            if ( $this->member_name == '' ) {
                $this->member_name = UsersType::ALL;
            }
        }

        //
        //print_r( $this->arr_members_type );
        if ( $this->arr_members_type === NULL ) {
            $this->arr_members_type = UsersType::list();
        }

        //
        $this->validation = \Config\ Services::validation();
    }

    public function index() {
        $post_per_page = 50;
        // URL cho các action dùng chung
        $for_action = '';
        // URL cho phân trang
        $urlPartPage = 'admin/' . $this->controller_slug . '?member_type=' . $this->member_type;

        // GET
        $by_is_deleted = $this->MY_get( 'is_deleted', DeletedStatus::FOR_DEFAULT );
        $by_keyword = $this->MY_get( 's' );
        $by_user_status = $this->MY_get( 'user_status' );
        $order_by = $this->MY_get( 'order_by' );

        //
        if ( $by_is_deleted > 0 ) {
            $urlPartPage .= '&is_deleted=' . $by_is_deleted;
            $for_action .= '&is_deleted=' . $by_is_deleted;
        }

        // các kiểu điều kiện where
        $where = [
            'users.is_deleted' => $by_is_deleted,
        ];
        if ( $this->member_type != '' ) {
            $where[ 'users.member_type' ] = $this->member_type;
        }

        // nếu không phải admin -> không cho xem danh sách admin luôn
        if ( $this->session_data[ 'member_type' ] != UsersType::ADMIN ) {
            $where[ 'users.member_type !=' ] = UsersType::ADMIN;
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
                        'ID' => $by_like,
                    ];
                } else {
                    // nếu có @ -> tìm theo email
                    if ( strpos( $by_keyword, '@' ) !== false ) {
                        $where_or_like = [
                            'user_email' => explode( '@', $by_keyword )[ 0 ],
                        ];
                    }
                    // còn lại thì có gì tìm hết
                    else {
                        $where_or_like = [
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

        // lọc theo tên cột truyền vào
        $col_filter = $this->MY_get( 'col_filter', [] );
        //print_r( $col_filter );
        foreach ( $col_filter as $k => $v ) {
            if ( !empty( $v ) ) {
                $where[ 'users.' . $k ] = $v;

                $urlPartPage .= '&col_filter[' . $k . ']=' . $v;
                $for_action .= '&col_filter[' . $k . ']=' . $v;
            }
        }

        // lọc theo trạng thái đăng nhập
        if ( $by_user_status != '' && $by_user_status != 'all' ) {
            $where[ 'users.user_status' ] = $by_user_status;

            $urlPartPage .= '&user_status=' . $by_user_status;
            $for_action .= '&user_status=' . $by_user_status;
        }

        //
        if ( $order_by == 'last_login' ) {
            $urlPartPage .= '&order_by=' . $order_by;
            $for_action .= '&order_by=' . $order_by;

            //
            $order_by = [
                'users.last_login' => 'DESC',
            ];
        } else {
            $order_by = [
                'users.ID' => 'DESC',
            ];
        }

        //
        $filter = [
            'or_like' => $where_or_like,
            //'order_by' => $order_by,
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 0,
            'limit' => -1
        ];


        /*
         * phân trang
         */
        $totalThread = $this->base_model->select( 'COUNT(ID) AS c', 'users', $where, $filter );
        //print_r( $totalThread );
        $totalThread = $totalThread[ 0 ][ 'c' ];
        //print_r( $totalThread );

        if ( $totalThread > 0 ) {
            $page_num = $this->MY_get( 'page_num', 1 );

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

            //
            $pagination = $this->base_model->EBE_pagination( $page_num, $totalPage, $urlPartPage, '&page_num=' );


            // select dữ liệu từ 1 bảng bất kỳ
            $filter[ 'order_by' ] = $order_by;
            $filter[ 'offset' ] = $offset;
            $filter[ 'limit' ] = $post_per_page;
            $data = $this->base_model->select( '*', 'users', $where, $filter );

            //
            //print_r( $data );
        } else {
            $data = [];
            $pagination = '';
        }

        //
        $this->teamplate_admin[ 'content' ] = view( 'admin/' . $this->custom_list_view . '/list', array(
            'pagination' => $pagination,
            'by_is_deleted' => $by_is_deleted,
            'for_action' => $for_action,
            'by_user_status' => $by_user_status,
            //'page_num' => $page_num,
            'totalThread' => $totalThread,
            'by_keyword' => $by_keyword,
            'data' => $data,
            'col_filter' => $col_filter,
            'controller_slug' => $this->controller_slug,
            'member_type' => $this->member_type,
            'member_name' => $this->member_name,
            'arr_members_type' => $this->arr_members_type,
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
            $data = $this->base_model->select( '*', 'users', [
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

            /*
             * bảo mật quyền cho tài khoản admin cấp cao
             */
            //print_r( $data );
            // nếu tài khoản đang là admin
            if ( $data[ 'member_type' ] == UsersType::ADMIN &&
                // -> chỉ tài khoản admin mới được quyền xem
                $this->session_data[ 'member_type' ] != UsersType::ADMIN
            ) {
                die( json_encode( [
                    'code' => __LINE__,
                    'error' => 'ERROR! Permisson deny for view user details!'
                ] ) );
            }

            // sửa tài khoản thì không nhập pass
            $data[ 'ci_pass' ] = '';
        }
        // add
        else {
            $data = $this->base_model->default_data( 'users' );

            // tạo mật khẩu ngẫu nhiên cho user
            $rand_password = [
                'A',
                'B',
                'C',
                'D',
                'E',
                'F',
                'G',
                'H',
                'I',
                'J',
                'K',
                'L',
                'M',
                'N',
                'O',
                'U',
                'P',
                'Q',
            ];
            $data[ 'ci_pass' ] = $rand_password[ rand( 0, count( $rand_password ) - 1 ) ] . '@' . substr( md5( time() ), 0, 10 );
        }
        //print_r( $data );
        //die( 'dgh dfsfs' );


        //
        if ( $this->debug_enable === true ) {
            echo '<!-- ';
            print_r( $data );
            echo ' -->';
        }

        //
        $this->teamplate_admin[ 'content' ] = view( 'admin/' . $this->add_edit_view . '/add', array(
            'data' => $data,
            'controller_slug' => $this->controller_slug,
            'member_type' => $this->member_type,
            'member_name' => $this->member_name,
            'arr_members_type' => $this->arr_members_type,
        ) );
        return view( 'admin/admin_teamplate', $this->teamplate_admin );
    }

    protected function add_new() {
        $data = $this->MY_post( 'data' );
        //print_r( $data );
        //die( __CLASS__ . ':' . __LINE__ );

        //
        $result_id = $this->user_model->insert_member( $data );
        if ( $result_id < 0 ) {
            $this->base_model->alert( 'Email đã được sử dụng', 'error' );
        } else if ( $result_id !== false ) {
            $this->base_model->alert( '', base_url( 'admin/' . $this->add_edit_view . '/add' ) . '?id=' . $result_id );
        }
        $this->base_model->alert( 'Lỗi thêm mới thành viên', 'error' );
    }

    protected function update( $id ) {
        $data = $this->MY_post( 'data' );
        //print_r( $data );
        //die( __LINE__ );

        //
        $this->validation->reset();
        $this->validation->setRule( 'user_email', 'Email', 'required|min_length[5]|max_length[255]|valid_email' );
        if ( !$this->validation->run( $data ) ) {
            $this->base_model->alert( 'Email không đúng định dạng được hỗ trợ', 'error' );
        }

        //
        $result_id = $this->user_model->update_member( $id, $data );

        //
        if ( $result_id === true ) {
            $this->base_model->alert( 'Cập nhật thông tin thành viên ' . $data[ 'user_email' ] . ' thành công' );
        } else {
            $this->base_model->alert( $result_id, 'error' );
        }
    }

    // chuyển trang sau khi XÓA xong
    protected function after_delete_restore() {
        $for_redirect = base_url( 'admin/' . $this->controller_slug ) . '?member_type=' . $this->member_type;

        //
        $page_num = $this->MY_get( 'page_num' );
        if ( $page_num != '' ) {
            $for_redirect .= '&page_num=' . $page_num;
        }

        //
        $is_deleted = $this->MY_get( 'is_deleted' );
        if ( $is_deleted != '' ) {
            $for_redirect .= '&is_deleted=' . $is_deleted;
        }

        //
        $this->base_model->alert( '', $for_redirect );
    }
    protected function done_delete_restore( $id ) {
        die( '<script>top.done_delete_restore(' . $id . ');</script>' );
    }
    protected function before_delete_restore( $msg, $is_deleted ) {
        if ( $this->current_user_id <= 0 ) {
            $this->base_model->alert( 'Không xác định được ID của bạn!', 'error' );
        }

        //
        $id = $this->MY_get( 'id', 0 );

        //
        if ( $this->current_user_id == $id ) {
            $this->base_model->alert( $msg, 'warning' );
        }

        //
        $update = $this->user_model->update_member( $id, [
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
        return $this->before_delete_restore( 'Không thể tự xóa chính bạn!', DeletedStatus::DELETED );
    }

    public function restore() {
        return $this->before_delete_restore( 'Không thể tự phục hồi chính bạn!', DeletedStatus::FOR_DEFAULT );
    }

    //
    public function before_all_delete_restore( $is_deleted, $where = [] ) {
        $ids = $this->MY_post( 'ids', '' );
        if ( empty( $ids ) ) {
            $this->result_json_type( [
                'code' => __LINE__,
                'error' => 'ids not found!',
            ] );
        }

        //
        $ids = explode( ',', $ids );
        if ( count( $ids ) <= 0 ) {
            $this->result_json_type( [
                'code' => __LINE__,
                'error' => 'ids EMPTY!',
            ] );
        }

        //
        $where[ 'is_deleted !=' ] = $is_deleted;
        //die( json_encode( $where ) );

        //
        $result = $this->base_model->update_multiple( 'users', [
            // SET
            'is_deleted' => $is_deleted,
        ], $where, [
            'where_in' => array(
                'ID' => $ids
            ),
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
        ] );
        $this->result_json_type( [
            'code' => __LINE__,
            'result' => $result,
        ] );
    }

    // chức năng xóa nhiều tài khoản 1 lúc
    public function delete_all() {
        return $this->before_all_delete_restore( DeletedStatus::DELETED, [
            'ID !=' => $this->current_user_id
        ] );
    }

    // chức năng xóa nhiều tài khoản 1 lúc
    public function restore_all() {
        return $this->before_all_delete_restore( DeletedStatus::FOR_DEFAULT );
    }

    // chức năng đăng nhập vào 1 tài khoản khác
    public function login_as() {
        if ( $this->current_user_id <= 0 ) {
            $this->base_model->alert( 'Không xác định được ID của bạn!', 'error' );
        }

        //
        if ( $this->session_data[ 'member_type' ] != UsersType::ADMIN ) {
            $this->base_model->alert( 'Tài khoản của bạn không có quyền sử dụng chức năng này! ' . __FUNCTION__, 'error' );
        }
        $id = $this->MY_get( 'id', 0 );

        //
        if ( $this->current_user_id == $id ) {
            $this->base_model->alert( 'Bạn đang đăng nhập vào tài khoản này rồi! ' . __FUNCTION__, 'warning' );
        }

        // select dữ liệu từ 1 bảng bất kỳ
        $data = $this->base_model->select( '*', 'users', [
            'ID' => $id
        ], array(
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 2,
            'limit' => 1
        ) );
        //print_r( $data );

        //
        if ( empty( $data ) ) {
            $this->base_model->alert( 'Không xác định được tài khoản cần đăng nhập! ' . __FUNCTION__, 'error' );
        }

        //
        $data = $this->sync_login_data( $data );
        //print_r( $data );

        // lưu thông tin đăng nhập cũ
        $this->MY_session( 'admin_login_as', $this->session_data );

        // lưu thông tin đăng nhập mới
        $this->base_model->set_ses_login( $data );

        //
        $this->base_model->msg_session( 'Đăng nhập vào tài khoản thành viên thành công: ' . $data[ 'user_email' ] );

        //
        $this->base_model->alert( '', base_url( 'users/profile' ) );
    }

}