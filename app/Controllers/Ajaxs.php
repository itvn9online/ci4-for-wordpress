<?php

namespace App\Controllers;

//
use App\Libraries\UsersType;

//
class Ajaxs extends Layout
{
    // chức năng này không cần nạp header
    public $preload_header = false;

    protected $select_term_col = 'term_id, name, slug, term_shortname, term_group, count, parent, taxonomy, child_count, child_last_count';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Một số phương thức ajax trong này sẽ kiểm tra thêm đoạn nguồn truy cập -> không cho truy cập từ bên ngoài domain
     **/
    protected function checkReferer($line)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->result_json_type(
                [
                    'code' => $line,
                    'error' => 'Bad request!',
                ]
            );
        }

        //
        if (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === false) {
            $this->result_json_type(
                [
                    'code' => __LINE__,
                    'error' => 'Blocked request!',
                ]
            );
        }

        //
        return true;
    }

    public function multi_logged()
    {
        $this->checkReferer(__LINE__);

        // trả về khối HTML để nạp Modal cảnh báo đăng nhập trên nhiều thiết bị
        if (!empty($this->MY_post('the_modal'))) {
            ob_end_clean();
            ob_start();
            // ưu tiên nạp view trong custom trước
            if (file_exists(VIEWS_CUSTOM_PATH . 'default/device_protection_modal.php')) {
                include VIEWS_CUSTOM_PATH . 'default/device_protection_modal.php';
            } else {
                // không có thì nạp view mặc định
                include VIEWS_PATH . 'default/device_protection_modal.php';
            }
            $html = ob_get_contents();
            ob_end_clean();

            //
            echo $html;
            exit();
        }

        //
        if ($this->current_user_id < 1) {
            $this->result_json_type(
                [
                    'code' => __LINE__,
                    'msg' => 'User has been logout...',
                ]
            );
        }

        //
        //die(json_encode($_GET));

        // lấy nội dung đăng nhập cũ trước khi lưu phiên mới
        $result = $this->user_model->getLogged($this->current_user_id);
        //$cresult = $this->user_model->getCLogged($this->current_user_id);

        // lưu session id của người dùng vào file
        $this->user_model->setLogged($this->current_user_id);
        //$this->user_model->setCLogged($this->current_user_id);

        // trả về key đã lưu của người dùng trong file
        $this->result_json_type(
            [
                't' => time(),
                // nếu có thông số tự logout phiên của người dùng thì tiến hành logout luôn
                'logout' => $this->getconfig->logout_device_protection,
                'hash' => $result,
                // hash lấy từ cache ra
                //'chash' => $cresult,
                //'rmlogout' => RAND_MULTI_LOGOUT,
                //'rmlogged' => RAND_MULTI_LOGGED,
            ]
        );
    }

    /**
     * Khi phát hiện người dùng đăng nhập trên nhiều thiết bị, mà chức năng auto logout được kích hoạt -> tiến hành logout tk của người dùng vào nạp lại trang
     **/
    public function multi_logout()
    {
        $this->checkReferer(__LINE__);

        //
        $msg = 'Device protection destroy';
        $blocked = NULL;
        if ($this->getconfig->block_device_protection == 'on' && $this->current_user_id > 0) {
            // nếu chưa có cache này -> lần đầu bị cảnh báo -> tạm tha
            if ($this->user_model->getCLogged($this->current_user_id) === NULL) {
                // lưu cache -> lần tới có cache này -> khóa
                //$this->user_model->setCLogged($this->current_user_id);
                $this->user_model->setCBlocked($this->current_user_id);

                //
                $blocked = 'skip';
            } else {
                // lần này có cache rồi vẫn dính -> khóa
                $blocked = $this->user_model->update_member($this->current_user_id, [
                    // khóa tk user
                    'user_status' => UsersType::NO_LOGIN,
                ], [
                    // bỏ qua admin
                    'member_type !=' => UsersType::ADMIN,
                ]);
                $msg = 'Device protection blocked';
            }
        }

        //
        session_destroy();
        //$this->base_model->MY_session('admin', []);

        // trả về key đã lưu của người dùng trong file
        $this->result_json_type(
            [
                'code' => __LINE__,
                'error' => $msg,
                'blocked' => $blocked,
                //'redirect_to' => base_url('guest/login'),
            ]
        );
    }

    /**
     * Khi người dùng bấm xác nhận không đăng nhập nhiều thiết bị -> don dẹp luôn dữ liệu
     **/
    public function confirm_logged()
    {
        $this->checkReferer(__LINE__);

        //
        $user_id = $this->MY_post('user_id');
        if (empty($user_id) || $user_id < 0) {
            $this->result_json_type(
                [
                    'code' => __LINE__,
                    'error' => 'user_id not found!',
                ]
            );
        }

        //
        $this->result_json_type(
            [
                't' => time(),
                'code' => __LINE__,
                'result' => $this->user_model->confirmLogged($user_id),
            ]
        );
    }

    public function get_taxonomy_by_ids()
    {
        header('Content-type: application/json; charset=utf-8');

        //
        $ids = $this->MY_post('ids', '');
        if (empty($ids)) {
            die(json_encode(
                [
                    'code' => __LINE__,
                    'error' => 'EMPTY ids'
                ]
            ));
        }

        //
        $data = $this->base_model->select(
            $this->select_term_col,
            WGR_TERM_VIEW,
            array(
                // WHERE AND OR
                //'is_member' => User_type::GUEST,
            ),
            array(
                'where_in' => array(
                    'term_id' => explode(',', $ids)
                ),
                // trả về COUNT(column_name) AS column_name
                //'selectCount' => 'ID',
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                // trả về tổng số bản ghi -> tương tự mysql num row
                //'getNumRows' => 1,
                //'offset' => 2,
                'limit' => -1
            )
        );

        //
        die(json_encode($data));
    }

    public function the_base_url()
    {
        die(DYNAMIC_BASE_URL);
    }

    public function sync_ajax_post_term()
    {
        // đồng bộ lại tổng số nhóm con cho các danh mục trước đã
        $this->result_json_type(
            [
                'term' => $this->term_model->sync_term_child_count(),
                'post' => $this->post_model->sync_post_term_permalink(),
            ]
        );
    }

    public function update_post_viewed()
    {
        $require_data = [
            //'current_user_id',
            'pid',
            //'post_author',
        ];
        foreach ($require_data as $v) {
            if (!isset($_POST[$v]) || empty($_POST[$v])) {
                $this->result_json_type([
                    'msg' => $v,
                    'error' => __LINE__
                ]);
            }
        }

        // nếu là tác giả đang xem thì chỉ tăng 1 lượt xem thôi
        $current_user_id = $this->MY_post('current_user_id', 0);
        $post_author = $this->MY_post('post_author', 0);
        if ($current_user_id > 0 && $current_user_id == $post_author) {
            $val = 1;
        } else {
            // người khác vào xem thì tăng mạnh hơn -> fview = fake view -> ngoài FE viết tắt tí cho kỳ bí
            $fake_view = $this->MY_post('fview', 1);
            if ($fake_view > 10) {
                $val = rand(5, $fake_view);
            } else {
                $val = 1;
            }
        }
        $this->post_model->update_views($this->MY_post('pid', 0), $val);

        //
        $this->result_json_type(
            [
                'ok' => __LINE__,
                'data' => $_POST,
                'val' => $val,
            ]
        );
        return true;
    }

    /**
     * Trả về khối HTML chứa mã captcha để thực hiện cho các phi vụ cần dùng đến hide-captcha
     **/
    public function get_anti_spam()
    {
        $this->checkReferer(__LINE__);

        //
        /*
        die(json_encode([
            'code' => __LINE__,
            //'test' => $_SERVER,
            'data' => $_POST,
        ]));
        */

        //
        ob_end_clean();
        ob_start();

        //
        $hide_captcha = $this->MY_post('hide_captcha', 0);
        $hide_captcha *= 1;
        // truyền giả lập user_id = 1 -> sẽ lấy mã html để trả về
        $user_id = 1;
        // trả về hide-captcha
        if ($hide_captcha > 0) {
            $this->base_model->hide_captcha_ajax($user_id);
        } else {
            // trả về captcha
            $this->base_model->anti_spam_ajax($user_id);
        }
        $html = ob_get_contents();

        //
        ob_end_clean();

        //
        echo $html;
        exit();
    }
}
