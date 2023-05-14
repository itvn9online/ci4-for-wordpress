<?php

namespace App\Controllers;

// Libraries
use App\Libraries\UsersType;
use App\Libraries\DeletedStatus;

//
class Firebase2s extends Firebases
{
    // với 1 số controller, sẽ không nạp cái HTML header vào, nên có thêm tham số này để không nạp header nữa
    public $preload_header = false;
    // thời gian hết hạn của mỗi token -> để thấp cho nó bảo mật
    public $expires_time = 120;

    public function __construct()
    {
        parent::__construct();
    }

    public function sign_in_success($callBack = true)
    {
        // kiểm tra tính hợp lệ của url
        $expires_token = $this->MY_get('expires_token');
        if (empty($expires_token)) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => $this->lang_model->get_the_text(__FUNCTION__ . 'expires_token', 'expires_token is EMPTY!'),
            ]);
        } else if (time() - $expires_token > $this->expires_time) {
            // URL hết hạn
            $this->result_json_type([
                'code' => __LINE__,
                'error' => $this->lang_model->get_the_text(__FUNCTION__ . 'expires_expires', 'expires_token is expires!'),
            ]);
        }

        //
        $access_token = $this->MY_get('access_token');
        if (empty($access_token)) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => $this->lang_model->get_the_text(__FUNCTION__ . 'access_token', 'access_token is EMPTY!'),
            ]);
        } else if ($this->base_model->mdnam($expires_token . session_id()) != $access_token) {
            // URL hết hạn
            $this->result_json_type([
                'code' => __LINE__,
                'error' => $this->lang_model->get_the_text(__FUNCTION__ . 'access_token', 'access_token mismatched!'),
            ]);
        }

        //
        $user_token = $this->MY_get('user_token');
        if (empty($user_token)) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => $this->lang_model->get_the_text(__FUNCTION__ . 'user_token', 'user_token is EMPTY!'),
            ]);
        } else if ($this->base_model->mdnam($expires_token . $this->current_user_id) != $user_token) {
            // URL hết hạn
            $this->result_json_type([
                'code' => __LINE__,
                'error' => $this->lang_model->get_the_text(__FUNCTION__ . 'user_token', 'user_token mismatched!'),
            ]);
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            //
            $name = $this->MY_post('name');
            $email = trim($this->MY_post('email'));
            //$email = 'itvn9online@yahoo.com';
            $phone = trim($this->MY_post('phone'));
            //$phone = '+84984533228';

            //
            $where = [
                'is_deleted' => DeletedStatus::FOR_DEFAULT,
            ];
            $where_like = [];
            if (!empty($email)) {
                $where['user_email'] = $email;
            } else if (!empty($phone)) {
                $where_like['user_phone'] = substr($phone, -9);
            } else {
                $this->result_json_type([
                    'code' => __LINE__,
                    'error' => 'email or phone?',
                ]);
            }

            // select lại dữ liệu
            $data = $this->base_model->select(
                '*',
                $this->user_model->table,
                $where,
                array(
                    'like_before' => $where_like,
                    'order_by' => [
                        'ID' => 'ASC'
                    ],
                    // hiển thị mã SQL để check
                    //'show_query' => 1,
                    // trả về câu query để sử dụng cho mục đích khác
                    //'get_query' => 1,
                    //'offset' => 2,
                    'limit' => 1
                )
            );
            //print_r($data);
            // nếu có dữ liệu trả về -> thiết lập session đăng nhập
            if (!empty($data)) {
                $data = $this->sync_login_data($data);
                $data['user_activation_key'] = session_id();

                //
                $this->user_model->update_member($data['ID'], [
                    'last_login' => date(EBE_DATETIME_FORMAT),
                    'user_activation_key' => $data['user_activation_key'],
                ]);

                //
                $this->base_model->set_ses_login($data);
            } else {
                if (empty($email)) {
                    $email = substr($phone, -9) . '@' . $_SERVER['HTTP_HOST'];
                }

                // chưa có -> tiến hành đăng ký tài khoản mới
                $data = [
                    'email' => $email,
                    'user_email' => $email,
                    'display_name' => $name,
                    'user_phone' => $phone,
                    'member_type' => UsersType::GUEST,
                ];
                //$this->result_json_type($data);
                $insert = $this->user_model->insert_member($data);
                if ($insert < 0) {
                    $this->result_json_type([
                        'code' => __LINE__,
                        'error' => 'Email đã được sử dụng',
                    ]);
                } else if ($insert !== false) {
                    // đăng ký thành công thì gọi lại chính function này để nó trả về dữ liệu
                    if ($callBack === true) {
                        return $this->sign_in_success(false);
                    } else {
                        $this->result_json_type([
                            'ok' => 0,
                        ]);
                    }
                } else {
                    $this->result_json_type([
                        'code' => __LINE__,
                        'error' => 'Lỗi đăng ký tài khoản',
                    ]);
                }
            }
            //$this->result_json_type($data);
            //$this->result_json_type($_POST);
            $this->result_json_type([
                'ok' => __LINE__,
            ]);
        }

        //
        $this->result_json_type($_GET);
    }
}
