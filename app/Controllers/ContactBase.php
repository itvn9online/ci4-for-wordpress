<?php

namespace App\Controllers;

//
use App\Libraries\PHPMaillerSend;
use App\Language\Translate;
use App\Libraries\CommentType;

class ContactBase extends Home
{
    protected $comment_type = CommentType::CONTACT;
    protected $allow_upload = [];
    // mặc định sẽ không cho 1 ip gửi quá số lượng bản ghi này trong 1 giờ -> cần tắt thì chuyển nó về 0
    public $check_spam_ip = 5;

    // các dữ liệu đầu thuộc dạng bắt buộc -> form nào cần tùy chỉnh thì extends ra xong khai báo lại trong class đã được extends
    protected $aria_required = [
        'fullname' => null,
        'email' => null,
        'title' => null,
        'content' => null,
    ];

    // mảng chứa rules mặc định dùng để chạy validation
    protected $default_rules = null;
    // mảng chứa rules cusstom dùng để chạy validation -> cái này dùng cho class extends tùy biến
    protected $custom_rules = [];
    public $comment_model = null;

    public function __construct()
    {
        parent::__construct();
        $this->validation = \Config\Services::validation();
        $this->comment_model = new \App\Models\Comment();
    }

    // khởi tạo giá trị cho default rules nếu chưa có
    protected function getDefaultRules()
    {
        if ($this->default_rules === null) {
            $this->default_rules = [
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
                ],
            ];
        }

        //
        return $this->default_rules;
    }

    public function put()
    {
        //ini_set('display_errors', 1);
        //error_reporting(E_ALL);

        // chỉ chấp nhận phương thức post
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return $this->done_action_login(base_url());
        }

        //
        /*
        $redirect_default = base_url();
        if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) !== false) {
            //die($_SERVER['HTTP_REFERER']);
            $redirect_default = $_SERVER['HTTP_REFERER'];
        }
        */

        //
        $this->wgr_target();

        // khởi tạo default rules để check form
        $this->getDefaultRules();

        // kiểm tra rule -> cái nào có thì mới kiểm tra
        $check_rules = [];
        foreach ($this->aria_required as $k => $v) {
            if ($v === null) {
                // ưu tiên custom rules
                if (isset($this->custom_rules[$k])) {
                    $v = $this->custom_rules[$k];
                }
                // sau đó mới đến default rules
                else if (isset($this->default_rules[$k])) {
                    $v = $this->default_rules[$k];
                }

                // gán giá trị để chạy validation nếu có
                if ($v !== null) {
                    $check_rules[$k] = $v;
                }
            } else {
                $check_rules[$k] = $v;
            }
        }
        // print_r($check_rules);
        // die(__CLASS__ . ':' . __LINE__);

        // daidq (2023-10-04): bỏ gcaptcha -> test tính năng chống spamer -> antiRequiredSpam
        // kiểm tra recaptcha (nếu có)
        $check_recaptcha = $this->googleCaptachStore();
        // var_dump($check_recaptcha);
        // === null -> ko sử dụng grecaptcha
        if ($check_recaptcha === null) {
            // kiểm tra spam bot
            if ($this->current_user_id < 1) {
                $this->base_model->antiRequiredSpam();
            }
        }
        // != true -> có lỗi -> in ra lỗi
        else if ($check_recaptcha !== true) {
            //$redirect_to = base_url($_SERVER['REQUEST_URI']);
            //die($redirect_to);
            $this->base_model->msg_error_session($check_recaptcha, $this->form_target);
            return $this->done_action_login($redirect_default);
        }
        // die(__CLASS__ . ':' . __LINE__);

        //
        $data = $this->MY_post('data');
        if (empty($data)) {
            $this->base_model->msg_error_session('Incorrect input method', $this->form_target);
            return $this->done_action_login();
        }
        // print_r($_POST);
        // die(__CLASS__ . ':' . __LINE__);

        // thực hiện validation
        if (!empty($check_rules)) {
            $this->validation->reset();
            $this->validation->setRules($check_rules);
        } else {
            // kiểm tra có dữ liệu gửi đi hay không
            $has_data = false;
            foreach ($data as $v) {
                if (!empty(trim($v))) {
                    $has_data = true;
                }
            }

            //
            if ($has_data === false) {
                $this->base_model->msg_error_session('Cannot be determined data', $this->form_target);
                return $this->done_action_login();
            }
        }

        //
        $redirect_to = $this->MY_post('redirect');
        if (empty($redirect_to)) {
            $redirect_to = $this->MY_post('__wgr_request_from', DYNAMIC_BASE_URL);
        }

        //
        /*
         if ( $this->form_validation->run() == FALSE ) {
         $this->base_model->msg_error_session( 'Vui lòng kiểm tra lại! Dữ liệu đầu vào không chính xác', $this->form_target );
         */
        if (!empty($check_rules) && !$this->validation->run($data)) {
            $this->set_validation_error($this->validation->getErrors(), $this->form_target);
        } else {
            $smtp_config = $this->option_model->get_smtp();
            //print_r($smtp_config);
            //die(__CLASS__ . ':' . __LINE__);

            $submit = $this->MY_comment([
                'redirect_to' => $redirect_to,
                'comment_type' => $this->comment_type == '' ? $this->getClassName(__CLASS__) : $this->comment_type,
            ]);

            // thiết lập thông tin người nhận
            $data_send = [
                // 'to' => $data['email'],
                'to' => $this->getconfig->emailcontact,
                // 'to_name' => $data['fullname'],
                'subject' => $data['title'],
                'message' => $submit['message'] . PHP_EOL . $redirect_to,
            ];

            //
            $bcc_email = [];

            //
            if ($this->MY_post('send_my_email') === 'on') {
                // $data_send['to'] = $data['email'];
                // $data_send['to_name'] = $data['fullname'];

                //
                if (isset($data['email'])) {
                    $data['email'] = trim($data['email']);
                    if ($data['email'] != '' && strpos($data['email'], '@') !== false) {
                        $bcc_email[] = $data['email'];
                    }
                }
            }

            // 
            $emailnotice = str_replace(';', ',', $this->getconfig->emailnotice);
            $emailnotice = explode(',', $emailnotice);
            foreach ($emailnotice as $v) {
                $v = trim($v);
                if ($v == '' || strpos($v, '@') === false) {
                    continue;
                }

                // 
                $bcc_email[] = $v;
            }

            //
            $data_send['bcc_email'] = $bcc_email;

            //
            PHPMaillerSend::the_send($data_send, $smtp_config);
        }
        //die($redirect_to);

        //
        return $this->done_action_login($redirect_to);
    }

    /**
     * Tạo function dùng chung cho các form thuộc dạng liên hệ
     */
    protected function MY_comment($ops = [])
    {
        // function này chỉ nhận POST
        // print_r($_SERVER);
        // print_r($_POST);
        // print_r($_FILES);

        //
        $comment_author_url = $this->MY_post('__wgr_request_from', DYNAMIC_BASE_URL);
        if (isset($ops['redirect_to']) && $ops['redirect_to'] != '') {
            $redirect_to = $ops['redirect_to'];
        } else {
            $redirect_to = $comment_author_url;
        }
        // die($redirect_to);

        //
        if (empty($this->MY_post('data'))) {
            $this->base_model->msg_error_session('Lỗi xác định phương thức nhập liệu');
            die(redirect($redirect_to));
        }

        // insert dữ liệu vào bảng
        $data = $this->MY_post('data');
        //$send_my_email = $this->MY_post('send_my_email');
        // print_r($data);
        // die(__CLASS__ . ':' . __LINE__);

        // nếu không có thuộc tính phân loại comment -> tạu tạo phân loại dựa theo tên function gửi đến
        if (!isset($ops['comment_type'])) {
            if ($this->comment_type == '') {
                $ops['comment_type'] = debug_backtrace()[1]['function'];
            } else {
                $ops['comment_type'] = $this->comment_type;
            }
        }

        //
        $data_insert = [
            'comment_author_url' => $comment_author_url,
            //'comment_author_IP' => $this->base_model->getIPAddress(),
            //'comment_date' => date( EBE_DATETIME_FORMAT ),
            'comment_content' => '',
            //'comment_agent' => $_SERVER[ 'HTTP_USER_AGENT' ],
            'comment_type' => $ops['comment_type'],
            //'user_id' => 0,
        ];
        if (!isset($data_insert['comment_date'])) {
            $data_insert['comment_date'] = date(EBE_DATETIME_FORMAT);
        }
        $data_insert['comment_date_gmt'] = $data_insert['comment_date'];
        // print_r($data_insert);
        // die(__CLASS__ . ':' . __LINE__);

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
            if ($gettype_v == 'array') {
                $v = implode(', ', $v);
            } else if ($gettype_v == 'object') {
                $v = json_encode($v);
            } else {
                $v = trim($v);
            }

            //
            if ($k != '' && !empty($v)) {
                $data_insert['comment_content'] .= $k . ':' . PHP_EOL;
                $data_insert['comment_content'] .= $v . PHP_EOL;
            }
        }
        // print_r($data_insert);
        // die(__CLASS__ . ':' . __LINE__);

        // daidq (2023-03-31): tạm thời không cho upload file qua form liên hệ
        // if (1 > 2) {
        $list_upload = $this->media_upload($this->allow_upload, true);
        //print_r( $list_upload );
        if (!empty($list_upload)) {
            $data_insert['comment_content'] .= 'File đính kèm: ' . PHP_EOL;
            foreach ($list_upload as $arr) {
                foreach ($arr as $v) {
                    $data_insert['comment_content'] .= '<a href="' . DYNAMIC_BASE_URL . $v . '">' . DYNAMIC_BASE_URL . $v . '</a>' . PHP_EOL;
                }
            }
        }
        // }
        // print_r($data_insert);
        // die(__CLASS__ . ':' . __LINE__);

        // insert comment
        $comment_ID = $this->comment_model->insert_comments($data_insert, [
            'check_spam_ip' => $this->check_spam_ip,
        ]);
        // var_dump($comment_ID);
        // die(__CLASS__ . ':' . __LINE__);

        // insert meta comment
        // print_r($data);
        // die(__CLASS__ . ':' . __LINE__);
        foreach ($data as $k => $v) {
            $k = trim($k);
            $gettype_v = gettype($v);
            if ($gettype_v == 'array') {
                $v = implode(', ', $v);
            } else if ($gettype_v == 'object') {
                $v = json_encode($v);
            } else {
                $v = trim($v);
            }

            //
            if ($k != '' && !empty($v)) {
                $this->comment_model->insert_meta_comments([
                    'comment_id' => $comment_ID,
                    'meta_key' => $k,
                    'meta_value' => $v,
                ]);
            }
        }
        // die(__CLASS__ . ':' . __LINE__);

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
            // $done_message = 'Gửi liên hệ thành công. Chúng tôi sẽ liên hệ lại với bạn sớm nhất có thể.';
            $done_message = $this->lang_model->get_the_text('ContactBase-done_message', 'Contact sent successfully. We will contact you as soon as possible..');
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
