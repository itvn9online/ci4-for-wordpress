<?php

namespace App\Controllers;

//
use App\Libraries\PHPMaillerSend;
use App\Language\Translate;

class Contact extends Home
{
    public function __construct()
    {
        parent::__construct();
        //$this->load->helper( 'translate' );
        //$this->load->helper( 'form' );
        $this->validation = \Config\Services::validation();
        $this->comment_model = new \App\Models\Comment();
    }

    public function put()
    {
        $this->wgr_target();

        //
        $data = $this->MY_post('data');
        if (empty($data)) {
            $this->base_model->msg_error_session('Phương thức đầu vào không chính xác', $this->form_target);
            return $this->done_action_login();
        }

        // thực hiện validation
        $this->validation->reset();
        $this->validation->setRules([
            'fullname' => [
                'label' => Translate::FULLNAME,
                'rules' => 'required|min_length[5]|max_length[255]',
                'errors' => [
                    'required' => $this->lang_model->get_the_text('translate_required', Translate::REQUIRED),
                    'min_length' => $this->lang_model->get_the_text('translate_min_len', Translate::MIN_LENGTH),
                    'max_length' => $this->lang_model->get_the_text('translate_max_len', Translate::MAX_LENGTH),
                ],
            ],
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
            'title' => [
                'label' => Translate::TITLE,
                'rules' => 'required|min_length[6]|max_length[255]',
                'errors' => [
                    'required' => $this->lang_model->get_the_text('translate_required', Translate::REQUIRED),
                    'min_length' => $this->lang_model->get_the_text('translate_min_len', Translate::MIN_LENGTH),
                    'max_length' => $this->lang_model->get_the_text('translate_max_len', Translate::MAX_LENGTH),
                ],
            ],
            'content' => [
                'label' => Translate::CONTENT,
                'rules' => 'required|min_length[6]',
                'errors' => [
                    'required' => $this->lang_model->get_the_text('translate_required', Translate::REQUIRED),
                    'min_length' => $this->lang_model->get_the_text('translate_min_len', Translate::MIN_LENGTH),
                ],
            ]
        ]);

        //
        $redirect_to = DYNAMIC_BASE_URL . ltrim($this->MY_post('redirect'), '/');
        if (empty($redirect_to)) {
            $redirect_to = DYNAMIC_BASE_URL;
        }

        //
        /*
         if ( $this->form_validation->run() == FALSE ) {
         $this->base_model->msg_error_session( 'Vui lòng kiểm tra lại! Dữ liệu đầu vào không chính xác', $this->form_target );
         */
        if (!$this->validation->run($data)) {
            $this->set_validation_error($this->validation->getErrors(), $this->form_target);
        } else {
            $smtp_config = $this->option_model->get_smtp();

            $submit = $this->MY_comment([
                'redirect_to' => $redirect_to,
                'comment_type' => $this->getClassName(__CLASS__)
            ]);

            // thiết lập thông tin người nhận
            $data_send = [
                //'to' => $data[ 'email' ],
                'to' => $smtp_config->emailcontact,
                //'to_name' => $data[ 'fullname' ],
                'subject' => $data['title'],
                'message' => $submit['message'],
            ];

            //
            $bcc_email = [];

            //
            $send_my_email = $this->MY_post('send_my_email');
            if ($send_my_email === 'on') {
                //$data_send[ 'to' ] = $data[ 'email' ];
                //$data_send[ 'to_name' ] = $data[ 'fullname' ];

                //
                $bcc_email[] = $data['email'];
            }

            //
            $data_send['bcc_email'] = $bcc_email;

            //
            PHPMaillerSend::the_send($data_send, $smtp_config);
        }

        //
        return $this->done_action_login($redirect_to);
    }

    /*
     * Tạo function dùng chung cho các form thuộc dạng liên hệ
     */
    protected function MY_comment($ops = [])
    {
        // function này chỉ nhận POST
        //print_r( $_SERVER );
        //print_r( $_POST );
        //print_r( $_FILES );

        //
        if (isset($ops['redirect_to']) && $ops['redirect_to'] != '') {
            $redirect_to = $ops['redirect_to'];
        } else {
            $redirect_to = DYNAMIC_BASE_URL . ltrim($this->MY_post('redirect'), '/');
            if (empty($redirect_to)) {
                $redirect_to = DYNAMIC_BASE_URL;
            }
        }
        //die( $redirect_to );

        //
        if (empty($this->MY_post('data'))) {
            $this->base_model->msg_error_session('Lỗi xác định phương thức nhập liệu');
            die(redirect($redirect_to));
        }

        // insert dữ liệu vào bảng
        $data = $this->MY_post('data');
        //$send_my_email = $this->MY_post('send_my_email');
        //print_r( $data );

        // nếu không có thuộc tính phân loại comment -> tạu tạo phân loại dựa theo tên function gửi đến
        if (!isset($ops['comment_type'])) {
            $ops['comment_type'] = debug_backtrace()[1]['function'];
        }

        //
        $data_insert = [
            'comment_author_url' => $redirect_to,
            //'comment_author_IP' => $this->request->getIPAddress(),
            //'comment_date' => date( EBE_DATETIME_FORMAT ),
            'comment_content' => '',
            //'comment_agent' => $_SERVER[ 'HTTP_USER_AGENT' ],
            'comment_type' => $ops['comment_type'],
            //'user_id' => 0,
        ];
        $data_insert['comment_date_gmt'] = $data_insert['comment_date'];
        //print_r( $data_insert );
        //die( 'dgh dhd hdf' );

        //
        if ($this->current_user_id > 0) {
            $data_insert['user_id'] = $this->current_user_id;
        }

        //
        if (isset($data['email']) && !isset($data['comment_author_email'])) {
            $data_insert['comment_author_email'] = $data['email'];
        }
        if (isset($data['title']) && !isset($data['comment_title'])) {
            $data_insert['comment_title'] = $data['title'];
        }

        // -> tạo nội dung theo data truyền vào
        foreach ($data as $k => $v) {
            $k = trim($k);
            $gettype_v = gettype($v);
            if ($gettype_v == 'array' || $gettype_v == 'object') {
                $v = json_encode($v);
            } else {
                $v = trim($v);
            }

            //
            if ($k != '' && !empty($v)) {
                $data_insert['comment_content'] .= $k . ':' . "\n";
                $data_insert['comment_content'] .= $v . "\n";
            }
        }
        //print_r( $data_insert );
        //die( 'j dfs ch dfh ds' );

        //
        $list_upload = $this->media_upload();
        //print_r( $list_upload );
        if (!empty($list_upload)) {
            $data_insert['comment_content'] .= 'File đính kèm: ' . "\n";
            foreach ($list_upload as $arr) {
                foreach ($arr as $v) {
                    $data_insert['comment_content'] .= DYNAMIC_BASE_URL . $v . "\n";
                }
            }
        }
        //print_r( $data_insert );
        //die( 'jh afsdgssf' );

        // insert comment
        $comment_ID = $this->comment_model->insert_comments($data_insert);

        // insert meta comment
        foreach ($data as $k => $v) {
            $k = trim($k);
            $v = trim($v);

            //
            if ($k != '' && $v != '') {
                $this->comment_model->insert_meta_comments([
                    'comment_id' => $comment_ID,
                    'meta_key' => $k,
                    'meta_value' => $v,
                ]);
            }
        }

        //
        if (!empty($list_upload)) {
            $this->comment_model->insert_meta_comments([
                'comment_id' => $comment_ID,
                'meta_key' => 'list_upload',
                'meta_value' => json_encode($list_upload),
            ]);
        }


        //
        $done_message = $this->MY_post('done_message');
        if (empty($done_message)) {
            $done_message = 'Gửi liên hệ thành công. Chúng tôi sẽ liên hệ lại với bạn sớm nhất có thể.';
        }
        $this->base_model->msg_session($done_message);

        //
        $data_send = [
            'message' => $data_insert['comment_content'],
        ];

        //
        return $data_send;
    }
}
