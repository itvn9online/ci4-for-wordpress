<?php

namespace App\Controllers;

// Libraries
use App\Language\Translate;
use App\Libraries\UsersType;

//
class Subscribes extends Csrf
{
    public function __construct()
    {
        parent::__construct();
    }

    // chức năng tạo tài khoản đăng ký theo dõi -> dùng để gửi email marketing
    public function put()
    {
        $e = $this->MY_post('email', '');
        $result = NULL;

        //
        $key_lang = 'subscribe_lang';

        //
        $this->validation = \Config\Services::validation();

        // thực hiện validation
        $this->validation->reset();
        $this->validation->setRules([
            'email' => [
                'label' => 'Email',
                'rules' => 'required|min_length[5]|max_length[255]|valid_email',
                'errors' => [
                    'required' => $this->lang_model->get_the_text('translate_required', Translate::REQUIRED),
                    'min_length' => $this->lang_model->get_the_text('translate_min_len', Translate::MIN_LENGTH),
                    'max_length' => $this->lang_model->get_the_text('translate_max_len', Translate::MAX_LENGTH),
                    'valid_email' => $this->lang_model->get_the_text('translate_valid_email', Translate::VALID_EMAIL),
                ],
            ],
        ]);
        // nếu có lỗi
        if (!$this->validation->run([
            'email' => $e
        ])) {
            // trả về thông báo lỗi
            $result = [
                'code' => __LINE__,
                'error' => $this->validation->getErrors()
            ];
        } else {
            // đến đây là không có lỗi rồi -> tạo data để insert
            $data = [
                'email' => $e,
                'user_email' => $e,
                'member_type' => UsersType::SUB,
            ];

            //
            $result_id = $this->user_model->insert_member($data);
            if ($result_id < 0) {
                $result = [
                    'code' => __LINE__,
                    'error' => $this->lang_model->get_the_text($key_lang . 'email_using', 'Email đã được sử dụng') . $data['user_email'],
                    'ok' => $result_id
                ];
            } else if ($result_id !== false) {
                $result = [
                    'code' => __LINE__,
                    'msg' => $this->lang_model->get_the_text($key_lang . 'sub_ok', 'Đăng ký tài khoản theo dõi thành công'),
                    'ok' => $result_id
                ];
            } else {
                $result = [
                    'code' => __LINE__,
                    'error' => $this->lang_model->get_the_text($key_lang . 'sub_error', 'Lỗi đăng ký tài khoản theo dõi'),
                ];
            }
        }

        //
        $this->result_json_type($result);
    }
}
