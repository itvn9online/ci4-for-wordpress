<?php

namespace App\Controllers\Admin;

// Libraries
use App\Libraries\DeletedStatus;
use App\Libraries\UsersType;
use App\Language\Translate;

//
class Users extends Admin
{
    protected $member_type = '';
    protected $member_name = '';
    protected $arr_members_type = NULL;

    // tham số dùng để thay đổi URL cho controller (nếu muốn)
    protected $controller_slug = 'users';
    // dùng cho trang list -> khi cần phân trang thì cần có cả tên function
    protected $controller_path = '';
    // tham số dùng để đổi file view khi add hoặc edit bài viết (nếu muốn)
    protected $add_view_path = 'users';
    // tham số dùng để thay đổi view của trang danh sách thành viên
    protected $list_view_path = 'users';
    // số bản ghi trên mỗi trang
    protected $post_per_page = 50;

    public function __construct()
    {
        parent::__construct();

        // kiểm tra quyền truy cập của tài khoản hiện tại
        $this->check_permision(__CLASS__);

        //
        if ($this->member_type == '') {
            $this->member_type = $this->MY_get('member_type', '');
        }
        //echo $this->member_type . '<br>' . PHP_EOL;

        //
        if ($this->member_name == '') {
            if ($this->member_type != '') {
                $this->member_name = UsersType::typeList($this->member_type);
            } else {
                $this->member_name = UsersType::ALL;
            }
        }

        //
        //print_r( $this->arr_members_type );
        if ($this->arr_members_type === NULL) {
            $this->arr_members_type = UsersType::typeList();
        }

        //
        $this->validation = \Config\Services::validation();
    }

    public function index()
    {
        return $this->lists();
    }
    /*
     * tham số where: dùng khi muốn thêm điều kiện where từ các controller được extends
     * tham số where_or_like: dùng khi muốn thêm điều kiện tìm kiếm dữ liệu từ các controller được extends
     */
    public function lists($ops = [])
    {
        //print_r( $where );
        //print_r( $where_or_like );

        // URL cho các action dùng chung
        $for_action = '';
        // URL cho phân trang
        $urlPartPage = 'admin/' . $this->controller_slug . $this->controller_path . '?member_type=' . $this->member_type;

        // GET
        $by_is_deleted = $this->MY_get('is_deleted', DeletedStatus::FOR_DEFAULT);
        $by_keyword = trim($this->MY_get('s', ''));
        $by_user_status = $this->MY_get('user_status');
        $order_by = $this->MY_get('order_by');

        //
        if ($by_is_deleted > 0) {
            $urlPartPage .= '&is_deleted=' . $by_is_deleted;
            $for_action .= '&is_deleted=' . $by_is_deleted;
        }

        // các kiểu điều kiện where
        if (!isset($ops['where'])) {
            $where = [];
        }
        if (!isset($ops['where_or_like'])) {
            $where_or_like = [];
        }
        $where['users.is_deleted'] = $by_is_deleted;
        if ($this->member_type != '') {
            $where['users.member_type'] = $this->member_type;
        }

        // nếu không phải admin -> không cho xem danh sách admin luôn
        if ($this->session_data['member_type'] != UsersType::ADMIN) {
            $where['users.member_type !='] = UsersType::ADMIN;
        }

        // tìm kiếm theo từ khóa nhập vào
        if ($by_keyword != '') {
            $urlPartPage .= '&s=' . urlencode($by_keyword);
            $for_action .= '&s=' . urlencode($by_keyword);

            //
            $by_like = $this->base_model->_eb_non_mark_seo($by_keyword);
            // tối thiểu từ 1 ký tự trở lên mới kích hoạt tìm kiếm
            if (strlen($by_like) > 0) {
                //var_dump( strlen( $by_like ) );
                // nếu là số -> chỉ tìm theo ID
                if (is_numeric($by_like) === true) {
                    $where_or_like['ID'] = $by_like * 1;
                    $where_or_like['user_login'] = $by_like;
                    $where_or_like['user_phone'] = $by_like;
                } else {
                    // nếu có @ -> tìm theo email
                    if (strpos($by_keyword, '@') !== false) {
                        $where_or_like['user_phone'] = explode('@', $by_keyword)[0];
                        $where_or_like['user_email'] = $by_keyword;
                    }
                    // còn lại thì có gì tìm hết
                    else {
                        //$where_or_like[ 'user_login' ] = $by_like;
                        $where_or_like['user_email'] = $by_keyword;
                        //$where_or_like[ 'display_name' ] = $by_like;
                        $where_or_like['user_url'] = $by_like;
                        $where_or_like['display_name'] = $by_keyword;
                        $where_or_like['firebase_uid'] = $by_keyword;
                    }
                }
            }
        }

        // lọc theo tên cột truyền vào
        $col_filter = $this->MY_get('col_filter', []);
        //print_r( $col_filter );
        foreach ($col_filter as $k => $v) {
            if ($v != '') {
                $where['users.' . $k] = $v;

                $urlPartPage .= '&col_filter[' . $k . ']=' . $v;
                $for_action .= '&col_filter[' . $k . ']=' . $v;
            }
        }

        // lọc theo trạng thái đăng nhập
        if ($by_user_status != '' && $by_user_status != 'all') {
            $where['users.user_status'] = $by_user_status;

            $urlPartPage .= '&user_status=' . $by_user_status;
            $for_action .= '&user_status=' . $by_user_status;
        }

        //
        if ($order_by == 'last_login') {
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
            //'order_by' => $order_by,
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            'or_like' => $where_or_like,
        ];

        // ghi đè filter nếu có
        if (isset($ops['add_filter'])) {
            foreach ($add_filter as $k => $v) {
                $filter[$k] = $v;
            }
        }
        $filter['offset'] = 0;
        $filter['limit'] = -1;

        /*
         * phân trang
         */
        $totalThread = $this->base_model->select('COUNT(ID) AS c', 'users', $where, $filter);
        //print_r($totalThread);
        $totalThread = $totalThread[0]['c'];
        //print_r($totalThread);
        //die(__CLASS__ . ':' . __LINE__);

        if ($totalThread > 0) {
            $page_num = $this->MY_get('page_num', 1);

            $totalPage = ceil($totalThread / $this->post_per_page);
            if ($totalPage < 1) {
                $totalPage = 1;
            }
            //echo $totalPage . '<br>' . PHP_EOL;
            if ($page_num > $totalPage) {
                $page_num = $totalPage;
            } else if ($page_num < 1) {
                $page_num = 1;
            }
            $for_action .= $page_num > 1 ? '&page_num=' . $page_num : '';
            //echo $totalThread . '<br>' . PHP_EOL;
            //echo $totalPage . '<br>' . PHP_EOL;
            $offset = ($page_num - 1) * $this->post_per_page;

            // chạy vòng lặp gán nốt các thông số khác trên url vào phân trang
            $urlPartPage = $this->base_model->auto_add_params($urlPartPage);

            //
            $pagination = $this->base_model->EBE_pagination($page_num, $totalPage, $urlPartPage, '&page_num=');


            // select dữ liệu từ 1 bảng bất kỳ
            //$filter[ 'show_query' ] = 1;
            $filter['order_by'] = $order_by;
            $filter['offset'] = $offset;
            $filter['limit'] = $this->post_per_page;

            // để tăng độ bảo mật -> không select các cột này
            $arr_deny_col = [
                'user_pass',
                'ci_pass',
                'ci_unpass',
                'user_activation_key',
            ];
            $select_col = $this->MY_get('select_col');
            $selects_col = [];
            if (empty($select_col)) {
                $table_col = $this->base_model->default_data('users');
                //print_r($table_col);
                foreach ($table_col as $k => $v) {
                    if (in_array($k, $arr_deny_col)) {
                        continue;
                    }

                    //
                    $selects_col[] = $k;
                }
            } else {
                $select_col = explode(',', $select_col);
                //print_r($select_col);
                foreach ($select_col as $v) {
                    if (empty($v) || in_array($v, $arr_deny_col)) {
                        continue;
                    }

                    //
                    $selects_col[] = $v;
                }
            }
            //print_r($selects_col);
            //$this->result_json_type($select_col);

            // ghi đè filter nếu có
            if (isset($ops['add_filter'])) {
                foreach ($add_filter as $k => $v) {
                    $filter[$k] = $v;
                }
            }

            //
            $data = $this->base_model->select(implode(',', $selects_col), 'users', $where, $filter);
            //echo implode(',', $selects_col);
            //print_r($data);
            //die(__CLASS__ . ':' . __LINE__);
        } else {
            $data = [];
            $pagination = '';
        }
        //$this->result_json_type($data);

        // trả luôn về data nếu có yêu cầu
        if (isset($ops['get_data']) && $ops['get_data'] === 1) {
            return $data;
        }

        //
        $this->teamplate_admin['content'] = view(
            'admin/' . $this->list_view_path . '/list',
            array(
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
                'controller_path' => $this->controller_path,
                'list_view_path' => $this->list_view_path,
                'member_type' => $this->member_type,
                'member_name' => $this->member_name,
                'arr_members_type' => $this->arr_members_type,
                'DeletedStatus_DELETED' => DeletedStatus::DELETED,
            )
        );
        return view('admin/admin_teamplate', $this->teamplate_admin);
    }

    public function add($ops = [])
    {
        $id = $this->MY_get('id', 0);

        //
        if (!empty($this->MY_post('data'))) {
            //print_r($_POST);
            //die(__CLASS__ . ':' . __LINE__);
            // update
            if ($id > 0) {
                return $this->update($id);
            }
            // insert
            return $this->add_new();
        }
        $data['ci_show_pass'] = '';

        // edit
        if ($id > 0) {
            // select dữ liệu từ 1 bảng bất kỳ
            $data = $this->base_model->select(
                '*',
                'users',
                [
                    'ID' => $id
                ],
                array(
                    // hiển thị mã SQL để check
                    //'show_query' => 1,
                    // trả về câu query để sử dụng cho mục đích khác
                    //'get_query' => 1,
                    //'offset' => 2,
                    'limit' => 1
                )
            );

            if (empty($data)) {
                die('user not found!');
            }

            /*
             * bảo mật quyền cho tài khoản admin cấp cao
             */
            //print_r( $data );
            // nếu tài khoản đang là admin
            if (
                $data['member_type'] == UsersType::ADMIN &&
                // -> chỉ tài khoản admin mới được quyền xem
                $this->session_data['member_type'] != UsersType::ADMIN
            ) {
                die(json_encode(
                    [
                        'code' => __LINE__,
                        'error' => 'ERROR! Permisson deny for view user details!'
                    ]
                ));
            }

            // sửa tài khoản thì không nhập pass
            $data['ci_show_pass'] = $data['ci_pass'];
            $data['ci_pass'] = '';
        }
        // add
        else {
            $data = $this->base_model->default_data('users');
            $data['member_type'] = $this->member_type;

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
            $data['ci_pass'] = $rand_password[rand(0, count($rand_password) - 1)] . '@' . substr(md5(time()), 0, 10);
        }
        //print_r( $data );
        //die( 'dgh dfsfs' );


        //
        if ($this->debug_enable === true) {
            echo '<!-- ';
            print_r($data);
            echo ' -->';
        }

        //
        $this->teamplate_admin['content'] = view(
            'admin/' . $this->add_view_path . '/add',
            array(
                'data' => $data,
                'controller_slug' => $this->controller_slug,
                'member_type' => $this->member_type,
                'member_name' => $this->member_name,
                'arr_members_type' => $this->arr_members_type,
            )
        );
        return view('admin/admin_teamplate', $this->teamplate_admin);
    }

    protected function add_new()
    {
        $data = $this->MY_post('data');
        //echo $this->controller_slug . '<br>' . PHP_EOL;
        //echo $this->member_type . '<br>' . PHP_EOL;
        //print_r( $data );
        if ($data['member_type'] == '') {
            $data['member_type'] = $this->member_type;
        }
        //print_r( $data );
        //die( __CLASS__ . ':' . __LINE__ );

        //
        $result_id = $this->user_model->insert_member($data);
        if ($result_id < 0) {
            $this->base_model->alert('Email đã được sử dụng ' . $data['user_email'], 'error');
        } else if ($result_id !== false) {
            $this->base_model->msg_session('Thêm mới ' . $this->member_name . ' thành công');
            $this->base_model->alert('', base_url('admin/' . $this->controller_slug . '/add') . '?id=' . $result_id);
        }
        $this->base_model->alert('Lỗi thêm mới thành viên', 'error');
    }

    protected function update($id)
    {
        $data = $this->MY_post('data');
        //print_r( $data );
        //die( __CLASS__ . ':' . __LINE__ );

        // nếu có mật khẩu -> đổi riêng mật khẩu
        $is_change_pass = false;
        if (isset($data['ci_pass']) && strlen($data['ci_pass']) >= 6) {
            $is_change_pass = true;
            $data = [
                'ci_pass' => $data['ci_pass']
            ];
            //print_r( $data );
            //die( __CLASS__ . ':' . __LINE__ );
        }
        // các thông tin khác thì cập nhật bình thường
        else {
            if (isset($data['user_email'])) {
                $this->validation->reset();
                $this->validation->setRules(
                    [
                        'user_email' => [
                            'label' => 'Email',
                            'rules' => 'required|min_length[5]|max_length[255]|valid_email',
                            'errors' => [
                                'required' => $this->lang_model->get_the_text('translate_required', Translate::REQUIRED),
                                'min_length' => $this->lang_model->get_the_text('translate_min_len', Translate::MIN_LENGTH),
                                'max_length' => $this->lang_model->get_the_text('translate_max_len', Translate::MAX_LENGTH),
                                'valid_email' => $this->lang_model->get_the_text('translate_valid_email', Translate::VALID_EMAIL),
                            ],
                        ]
                    ]
                );
                if (!$this->validation->run($data)) {
                    $this->set_validation_error($this->validation->getErrors(), 'error');
                }
                /*
                } else {
                $data[ 'user_email' ] = '';
                */
            }
        }

        //
        //print_r( $data );
        $result_id = $this->user_model->update_member($id, $data);

        //
        if ($result_id === true) {
            if ($is_change_pass === true) {
                echo '<script>top.close_input_change_user_password();</script>';
                $this->base_model->alert('Thay đổi mật khẩu cho ' . $this->member_name . ' thành công');
            } else {
                if (!isset($data['user_email'])) {
                    $data['user_email'] = '';
                }
                $msg_session = 'Cập nhật thông tin ' . $this->member_name . ' ' . $data['user_email'] . ' thành công';

                // nếu có tham số này khi submit -> nạp lại trang sau khi update thành công
                if (!empty($this->MY_post('reload_page'))) {
                    $this->base_model->msg_session($msg_session);
                    $this->base_model->alert('', base_url('admin/' . $this->controller_slug . '/add') . '?id=' . $id);
                }
                $this->base_model->alert($msg_session);
            }
        } else {
            $this->base_model->alert($result_id, 'error');
        }
    }

    // chuyển trang sau khi XÓA xong
    protected function after_delete_restore()
    {
        $for_redirect = base_url('admin/' . $this->controller_slug) . '?member_type=' . $this->member_type;

        //
        $page_num = $this->MY_get('page_num');
        if ($page_num != '') {
            $for_redirect .= '&page_num=' . $page_num;
        }

        //
        $is_deleted = $this->MY_get('is_deleted');
        if ($is_deleted != '') {
            $for_redirect .= '&is_deleted=' . $is_deleted;
        }

        //
        $this->base_model->alert('', $for_redirect);
    }
    protected function done_delete_restore($id)
    {
        die('<script>top.done_delete_restore(' . $id . ', "' . base_url('admin/' . $this->controller_slug) . '");</script>');
    }
    protected function before_delete_restore($msg, $is_deleted)
    {
        if ($this->current_user_id <= 0) {
            $this->base_model->alert('Không xác định được ID của bạn!', 'error');
        }

        //
        $id = $this->MY_get('id', 0);

        //
        if ($is_deleted != DeletedStatus::FOR_DEFAULT && $this->current_user_id == $id) {
            $this->base_model->alert($msg, 'warning');
        }

        //
        $update = $this->user_model->update_member(
            $id,
            [
                'is_deleted' => $is_deleted,
            ]
        );

        // nếu update thành công -> gửi lệnh javascript để ẩn bài viết bằng javascript
        if ($update === true) {
            if ($is_deleted == DeletedStatus::REMOVED && ALLOW_USING_MYSQL_DELETE === true) {
                return $update;
            }
            return $this->done_delete_restore($id);
        }
        // không thì nạp lại cả trang để kiểm tra cho chắc chắn
        $this->after_delete_restore();
    }

    public function delete()
    {
        return $this->before_delete_restore('Không thể tự Lưu trữ chính bạn!', DeletedStatus::DELETED);
    }

    public function restore()
    {
        return $this->before_delete_restore('Không thể tự Phục hồi chính bạn!', DeletedStatus::FOR_DEFAULT);
    }

    public function remove()
    {
        // nếu có thuộc tính cho phép xóa hoàn toàn dữ liệu thì tiến hành xóa
        if (ALLOW_USING_MYSQL_DELETE === true) {
            $this->delete_remove($this->MY_get('id', 0));
            return $this->done_delete_restore($this->MY_get('id', 0));
        }
        // mặc định thì chỉ là chuyển về trang thái remove để ẩn khỏi admin
        else {
            $result = $this->before_delete_restore('Không thể tự XÓA chính bạn!', DeletedStatus::REMOVED);
        }

        //
        return $result;
    }

    //
    protected function get_ids()
    {
        $ids = $this->MY_post('ids', '');
        if (empty($ids)) {
            $this->result_json_type(
                [
                    'code' => __LINE__,
                    'error' => 'ids not found!',
                ]
            );
        }

        //
        $ids = explode(',', $ids);
        if (count($ids) <= 0) {
            $this->result_json_type(
                [
                    'code' => __LINE__,
                    'error' => 'ids EMPTY!',
                ]
            );
        }
        //print_r( $ids );

        //
        return $ids;
    }

    // xóa hoàn toàn dữ liệu
    protected function delete_remove($id = 0)
    {
        if ($id > 0) {
            $ids = [$id];
        } else {
            $ids = $this->get_ids();
        }
        //die( __CLASS__ . ':' . __LINE__ );

        // XÓA meta
        $this->base_model->delete_multiple(
            $this->user_model->metaTable,
            [
                // WHERE
                //'t2.is_deleted' => DeletedStatus::REMOVED,
            ],
            [
                /*
                'join' => array(
                $this->user_model->table . ' AS t2' => $this->user_model->metaTable . '.user_id = t2.ID'
                ),
                */
                'where_in' => array(
                    'user_id' => $ids
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
            ]
        );
        //var_dump( $result );
        //die( __CLASS__ . ':' . __LINE__ );

        // XÓA dữ liệu chính
        $this->base_model->delete_multiple(
            $this->user_model->table,
            [
                // WHERE
                //'is_deleted' => DeletedStatus::REMOVED,
            ],
            [
                'where_in' => array(
                    'ID' => $ids
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
            ]
        );
        //die(__CLASS__ . ':' . __LINE__);

        //
        return $result;
    }

    public function before_all_delete_restore($is_deleted, $where = [])
    {
        $ids = $this->get_ids();

        //
        $where['is_deleted !='] = $is_deleted;
        //die( json_encode( $where ) );

        $update = $this->base_model->update_multiple(
            'users',
            [
                // SET
                'is_deleted' => $is_deleted,
            ],
            $where,
            [
                'where_in' => array(
                    'ID' => $ids
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
            ]
        );

        // riêng với lệnh remove -> kiểm tra nếu remove hoàn toàn thì xử lý riêng
        if ($update === true && $is_deleted == DeletedStatus::REMOVED && ALLOW_USING_MYSQL_DELETE === true) {
            return $update;
        }

        //
        $this->result_json_type(
            [
                'code' => __LINE__,
                'result' => $update,
                //'ids' => $ids,
            ]
        );
    }

    // chức năng xóa nhiều bản ghi 1 lúc
    public function delete_all()
    {
        return $this->before_all_delete_restore(
            DeletedStatus::DELETED,
            [
                'ID !=' => $this->current_user_id
            ]
        );
    }

    // chức năng restore nhiều bản ghi 1 lúc
    public function restore_all()
    {
        return $this->before_all_delete_restore(DeletedStatus::FOR_DEFAULT);
    }

    // chức năng remove nhiều bản ghi 1 lúc
    public function remove_all()
    {
        //die(__CLASS__ . ':' . __LINE__);
        // nếu có thuộc tính cho phép xóa hoàn toàn dữ liệu thì tiến hành xóa
        if (ALLOW_USING_MYSQL_DELETE === true) {
            $result = $this->delete_remove();
        } else {
            $result = $this->before_all_delete_restore(DeletedStatus::REMOVED);
        }

        //
        $this->result_json_type(
            [
                'code' => __LINE__,
                'result' => $result,
                //'ids' => $ids,
            ]
        );
    }

    // chức năng đăng nhập vào 1 tài khoản khác
    public function login_as()
    {
        if ($this->current_user_id <= 0) {
            $this->base_model->alert('Không xác định được ID của bạn!', 'error');
        }

        //
        if ($this->session_data['member_type'] != UsersType::ADMIN) {
            $this->base_model->alert('Tài khoản của bạn không có quyền sử dụng chức năng này! ' . __FUNCTION__, 'error');
        }
        $id = $this->MY_get('id', 0);

        //
        if ($this->current_user_id == $id) {
            $this->base_model->alert('Bạn đang đăng nhập vào tài khoản này rồi! ' . __FUNCTION__, 'warning');
        }

        // select dữ liệu từ 1 bảng bất kỳ
        $data = $this->base_model->select(
            '*',
            'users',
            [
                'ID' => $id
            ],
            array(
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 2,
                'limit' => 1
            )
        );
        //print_r( $data );

        //
        if (empty($data)) {
            $this->base_model->alert('Không xác định được tài khoản cần đăng nhập! ' . __FUNCTION__, 'error');
        }

        //
        $data = $this->sync_login_data($data);
        //print_r( $data );

        // lưu thông tin đăng nhập cũ
        $this->MY_session('admin_login_as', $this->session_data);

        // lưu thông tin đăng nhập mới
        $this->base_model->set_ses_login($data);

        //
        $this->base_model->msg_session('Đăng nhập vào tài khoản thành viên thành công: ' . $data['user_email']);

        //
        $this->base_model->alert('', base_url('users/profile'));
    }

    public function quick_status()
    {
        $user_id = $this->MY_post('user_id');
        if (empty($user_id)) {
            $this->result_json_type(
                [
                    'in' => __CLASS__,
                    'code' => __LINE__,
                    'error' => 'EMPTY user id!'
                ]
            );
        }

        //
        $user_status = $this->MY_post('user_status', '');
        if ($user_status == '' || UsersType::typeList($user_status)) {
            $this->result_json_type(
                [
                    'in' => __CLASS__,
                    'code' => __LINE__,
                    'error' => 'EMPTY user status!'
                ]
            );
        }
        if ($user_status * 1 < 0) {
            $user_status = UsersType::FOR_DEFAULT;
        } else {
            // nếu là KHÓA -> không cho KHÓA chính tài khoản hiện tại
            if ($this->current_user_id * 1 === $user_id * 1) {
                $this->result_json_type(
                    [
                        'in' => __CLASS__,
                        'code' => __LINE__,
                        'error' => 'Không thể tự KHÓA chính bạn!'
                    ]
                );
            }
            $user_status = UsersType::NO_LOGIN;
        }
        //$this->result_json_type([$user_status]); // TEST

        //
        $this->base_model->update_multiple(
            'users',
            [
                'user_status' => $user_status
            ],
            [
                // WHERE
                'ID' => $user_id,
                'member_type' => $this->member_type
            ]
        );

        //
        //$this->result_json_type( $_POST ); // TEST
        $this->result_json_type(
            [
                'ok' => $user_id,
                'member_name' => $this->member_name,
                'user_status' => $user_status,
            ]
        );
    }

    // trả về danh sách người dùng dạng json
    public function get_json()
    {
        $data = $this->lists([],  [],  [],  [
            'get_data' => 1
        ]);
        //print_r($data);

        //
        $this->result_json_type($data);
    }
}
