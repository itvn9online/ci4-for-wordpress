<?php

namespace App\Controllers\Admin;

// Libraries
use App\Libraries\LanguageCost;
use App\Libraries\ConfigType;
use App\Libraries\DeletedStatus;
use App\Libraries\PHPMaillerSend;
use App\ThirdParty\TelegramBot;

//
class Configs extends Admin
{
    //private $lang_key = '';
    protected $config_type = '';
    protected $view_edit = 'edit';
    // một số kiểu config có sử dụng code và view riêng
    protected $dynamic_config = [
        ConfigType::TRANS => 'translate',
        ConfigType::NUM_MON => 'num_mon',
    ];
    // tham số ví dụ -> mặc định là getconfig truyền từ Layout, 1 số config thi thoảng mới dùng thì truyền tham số riêng
    protected $example_prefix = 'getconfig';
    protected $zalooa_config = NULL;

    public function __construct()
    {
        parent::__construct();
        //$this->load->library( 'LanguageCost' );

        // kiểm tra quyền truy cập của tài khoản hiện tại
        $this->check_permision(__CLASS__);

        //
        //$this->lang_key = $this->lang_key;

        // hỗ trợ lấy theo params truyền vào từ url
        if ($this->config_type == '') {
            $this->config_type = $this->MY_get('config_type', ConfigType::CONFIG);
        }

        //
        $this->payment_model = new \App\Models\Payment();

        //
        $this->zalooa_config = $this->option_model->obj_config(ConfigType::ZALO);
        //print_r($this->zalooa_config);
        //die(__CLASS__ . ':' . __LINE__);
    }

    public function index()
    {
        // khi submit với checkbox, cần xử lý thêm trường hợp không có checkbox nào được chọn
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $this->config_type == ConfigType::CHECKBOX) {
            if (empty($this->MY_post('data'))) {
                return $this->updated($this->config_type);
                //die(__CLASS__ . ':' . __LINE__);
            }
        }
        // update
        if (!empty($this->MY_post('data'))) {
            return $this->updated($this->config_type);
        }

        //
        if (isset($_GET['test_mail'])) {
            return $this->testMail();
        } else if (isset($_GET['get_tele_chat_id'])) {
            return $this->getTeleChatId();
        }

        //
        $meta_default = ConfigType::meta_default($this->config_type);
        //print_r( $meta_default );

        // select dữ liệu từ 1 bảng bất kỳ
        $sql = $this->base_model->select(
            '*',
            $this->option_model->table,
            array(
                // các kiểu điều kiện where
                'is_deleted' => DeletedStatus::FOR_DEFAULT,
                'option_type' => $this->config_type,
                'lang_key' => $this->lang_key
            ),
            array(
                'order_by' => array(
                    'option_id' => 'DESC',
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 2,
                'limit' => -1
            )
        );
        //print_r( $sql );
        $value = [];
        foreach ($sql as $v) {
            $value[$v['option_name']] = $v['option_value'];
        }
        //print_r($value);

        // cố định 1 số view đọng dạng input -> tránh if else nhiều
        $trans_data = $meta_default;
        $trans_custom_type = [];
        // nếu có code riêng thì cho vào đây
        if (isset($this->dynamic_config[$this->config_type])) {
            // với phần dịch -> ghi đè giá trị của default
            foreach ($value as $k => $v) {
                $trans_data[$k] = $v;
            }

            // cố định input type cho các thuộc tính cứng -> ngoài những cái này thì để textarea hết
            foreach ($meta_default as $k => $v) {
                $trans_custom_type[$k] = '';
            }

            // thiết lập file view riêng
            $this->view_edit = $this->dynamic_config[$this->config_type];
        } else {
            // gán giá trị mặc định cho các config khác
            foreach ($meta_default as $k => $v) {
                if (!isset($value[$k])) {
                    $value[$k] = '';
                }
            }

            //
            if ($this->config_type == ConfigType::CHECKBOX) {
                $this->view_edit = 'checkbox';
            }
        }
        //print_r($value);
        //print_r($meta_default);
        //print_r($trans_data);
        //print_r($trans_custom_type);

        //
        $this->teamplate_admin['content'] = view(
            'admin/configs/' . $this->view_edit,
            array(
                'lang_key' => $this->lang_key,
                'config_type' => $this->config_type,
                'zalooa_config' => $this->zalooa_config,
                'meta_default' => $meta_default,
                'trans_data' => $trans_data,
                'trans_custom_type' => $trans_custom_type,
                'data' => $value,
                'vue_data' => [
                    'lang_key' => $this->lang_key,
                    'lang_name' => LanguageCost::typeList($this->lang_key),
                    'config_type' => $this->config_type,
                    'config_name' => ConfigType::typeList($this->config_type),
                ],
                'value' => (object) $value,
                'checkout_config' => $this->payment_model->getCheckoutConfig(false),
                'example_prefix' => $this->example_prefix,
            )
        );
        return view('admin/admin_teamplate', $this->teamplate_admin);
    }

    protected function updated($option_type)
    {
        if (!empty($this->MY_post('data'))) {
            $data = $this->MY_post('data');
        } else {
            $data = $_POST;
        }
        //print_r($data);
        //die(__CLASS__ . ':' . __LINE__);

        //
        $arr_meta_key = [];

        //
        if ($option_type == ConfigType::CONFIG) {
            // chỉ cho phép một số định dạng file được truy cập trong thư mục upload
            echo $this->deny_visit_upload('', true, isset($data['enable_hotlink_protection']) ? true : false) . '<br>' . PHP_EOL;
            $this->auto_create_htaccess_deny(true);

            // chỉ cho phép một số định dạng file được truy cập trong thư mục themes
            echo $this->deny_visit_upload(PUBLIC_PUBLIC_PATH . 'themes', true, false) . '<br>' . PHP_EOL;

            //
            $data['logo_width_img'] = 0;
            $data['logo_height_img'] = 0;
            //echo PUBLIC_PUBLIC_PATH . $data[ 'logo' ] . '<br>' . PHP_EOL;
            if (isset($data['logo']) && $data['logo'] != '' && file_exists(PUBLIC_PUBLIC_PATH . $data['logo'])) {
                $logo_data = getimagesize(PUBLIC_PUBLIC_PATH . $data['logo']);

                //
                $data['logo_width_img'] = ceil($logo_data[0]);
                $data['logo_height_img'] = ceil($logo_data[1]);

                //
                if (!isset($data['logo_main_height']) || empty($data['logo_main_height'])) {
                    $data['logo_main_height'] = $data['logo_height_img'];
                    echo '<script>top.set_configs_value("#data_logo_main_height", ' . $data['logo_main_height'] . ');</script>';
                }
                echo '<script>top.set_configs_value("#data_logo_width_img", ' . $data['logo_width_img'] . ');</script>';
                echo '<script>top.set_configs_value("#data_logo_height_img", ' . $data['logo_height_img'] . ');</script>';
            }
            $arr_meta_key[] = 'logo_width_img';
            $arr_meta_key[] = 'logo_height_img';
            //print_r( $data );

            //
            //die( __CLASS__ . ':' . __LINE__ );
        }

        $list_field_has_change = $this->MY_post('list_field_has_change');
        if (empty($list_field_has_change)) {
            $this->base_model->alert('Không xác định được dữ liệu cần thay đổi #' . $option_type, 'warning');
        }
        //echo $list_field_has_change . '<br>' . PHP_EOL;
        $list_field_has_change = json_decode($list_field_has_change);
        //print_r( $list_field_has_change );
        if (empty($list_field_has_change)) {
            $this->base_model->alert('Không có thay đổi nào được chỉ định #' . $option_type, 'warning');
        }

        foreach ($list_field_has_change as $k => $v) {
            $arr_meta_key[] = $k;
        }
        //print_r( $arr_meta_key );

        //
        if (!empty($data['list_slide'])) {
            $data['list_slide'] = implode(';', $data['list_slide']);
        } else {
            $data['list_slide'] = '';
        }
        //$data[ 'min_price' ] = str_replace( ',', '', $data[ 'min_price' ] );
        //$data[ 'max_price' ] = str_replace( ',', '', $data[ 'max_price' ] );
        //print_r( $data );
        //die( __CLASS__ . ':' . __LINE__ );

        //
        if (isset($data['blog_private']) && $data['blog_private'] == 'on') {
            $this->base_model->ftp_create_file(PUBLIC_PUBLIC_PATH . 'robots.txt', $this->helpersTmpFile('robots_disallow_all'));
        }
        //
        else if (isset($data['robots'])) {
            // cập nhật lại robots.txt khi không có nội dung hoặc sai địa chỉ sitemap
            if ($data['robots'] == '' || strpos($data['robots'], DYNAMIC_BASE_URL) === false) {
                $data['robots'] = $this->helpersTmpFile(
                    'robots_default',
                    [
                        'base_url' => DYNAMIC_BASE_URL,
                    ]
                );
                //echo nl2br( $data[ 'robots' ] );

                //
                $arr_meta_key[] = 'robots';
            }
            //$data[ 'robots' ] = trim( $data[ 'robots' ] );

            //
            //$id = '1';

            /*
            $robot = fopen( PUBLIC_PUBLIC_PATH . 'robots.txt', 'w' )or die( 'Unable to open file!' );
            fwrite( $robot, $data[ 'robots' ] );
            fclose( $robot );
            */

            //
            $this->base_model->ftp_create_file(PUBLIC_PUBLIC_PATH . 'robots.txt', $data['robots']);
        }
        //print_r( $data );

        // backup và xóa các config cũ đã được liệt kê
        $this->option_model->backup_options($option_type, $this->lang_key, $arr_meta_key);

        // sau đó insert cái mới
        $last_updated = date(EBE_DATETIME_FORMAT);
        $insert_time = date('YmdHis');
        foreach ($data as $k => $v) {
            // có tác động thì mới update -> tác động thì sẽ có tên trong danh sách update
            if (!in_array($k, $arr_meta_key)) {
                continue;
            }

            // có giá trị thì mới update
            $v = trim($v);
            if ($v == '') {
                continue;
            }

            //
            echo 'Insert: ' . $k . ' = ' . $v . '<br>' . PHP_EOL;

            //
            $this->option_model->insert_options(
                [
                    'option_name' => $k,
                    'option_value' => $v,
                    'option_type' => $option_type,
                    'lang_key' => $this->lang_key,
                    'last_updated' => $last_updated,
                    'insert_time' => $insert_time,
                ]
            );
        }
        //die(__CLASS__ . ':' . __LINE__);

        // dọn dẹp config dư thừa với các loại không nằm trong danh sách này
        if (!isset($this->dynamic_config[$this->config_type])) {
            // chạy vòng lặp xóa các dữ liệu dư thừa -> không có trong config
            $meta_default = ConfigType::meta_default($this->config_type);
            //print_r($meta_default);
            $remove_not_in = [];
            foreach ($meta_default as $k => $v) {
                $remove_not_in[] = $k;
            }
            //print_r($remove_not_in);
            //die(__CLASS__ . ':' . __LINE__);

            // DELETE dữ liệu
            if (!empty($remove_not_in)) {
                $this->base_model->delete_multiple(
                    $this->option_model->table,
                    [
                        // WHERE
                        'option_type' => $this->config_type,
                    ],
                    [
                        'where_not_in' => array(
                            'option_name' => $remove_not_in
                        ),
                        // hiển thị mã SQL để check
                        'show_query' => 1,
                    ]
                );
            }
        }
        //die(__CLASS__ . ':' . __LINE__);


        // dọn dẹp cache liên quan đến config này -> reset cache
        $this->cleanup_cache($this->option_model->key_cache('list_config'));
        $this->cleanup_cache($this->option_model->key_cache($option_type));

        // xác nhận việc update đã xong
        echo '<script>top.done_field_has_change();</script>';

        //
        $this->base_model->alert('Cập nhật dữ liệu thành công #' . $option_type);
    }

    private function testMail()
    {
        $smtp_config = $this->option_model->get_smtp();
        //print_r( $smtp_config );
        //die( __CLASS__ . ':' . __LINE__ );
        if (!isset($smtp_config->smtp_test_email) || empty($smtp_config->smtp_test_email)) {
            //print_r( $smtp_config );
            die(json_encode(
                [
                    'code' => __LINE__,
                    'error' => 'Test email is NULL or not found!'
                ]
            ));
        }

        //
        $data_send = [
            'to' => $smtp_config->smtp_test_email,
            'to_name' => 'Dao Quoc Dai',
            /*
            'bcc_email' => [
            'v0tjnhlangtu@gmail.com'
            ],
            'cc_email' => [
            'itvn9online@yahoo.com'
            ],
            */
            'subject' => 'Test email ' . date('r'),
            'message' => implode(
                '<br>',
                [
                    'PHPMailer version: ' . file_get_contents(APPPATH . 'ThirdParty/PHPMailer/phpmailer/phpmailer/VERSION', 1),
                    'Domain: ' . $_SERVER['HTTP_HOST'],
                    'Request: ' . $_SERVER['REQUEST_URI'],
                    'Method: ' . $_SERVER['REQUEST_METHOD'],
                    'Time: ' . date('r'),
                    'IP: ' . $this->request->getIPAddress(),
                    'Browser: ' . $_SERVER['HTTP_USER_AGENT'],
                    'Server: ' . $_SERVER['SERVER_ADDR'],
                    'Session: ' . session_id(),
                ]
            ),
        ];
        if (isset($smtp_config->smtp_test_bcc_email) && !empty($smtp_config->smtp_test_bcc_email)) {
            $data_send['bcc_email'] = [
                $smtp_config->smtp_test_bcc_email
            ];
        }
        if (isset($smtp_config->smtp_test_cc_email) && !empty($smtp_config->smtp_test_cc_email)) {
            $data_send['cc_email'] = [
                $smtp_config->smtp_test_cc_email
            ];
        }
        //print_r( $data_send );
        //die( __CLASS__ . ':' . __LINE__ );

        //
        echo 'PHPMailer version: ' . file_get_contents(APPPATH . 'ThirdParty/PHPMailer/phpmailer/phpmailer/VERSION', 1) . '<br>' . PHP_EOL;
        echo 'Username/ Email: ' . $smtp_config->smtp_host_user . '<br>' . PHP_EOL;
        echo 'Password: ' . substr($smtp_config->smtp_host_pass, 0, 6) . '******<br>' . PHP_EOL;
        echo 'Hostname: ' . $smtp_config->smtp_host_name . '<br>' . PHP_EOL;
        echo 'Secure: ' . $smtp_config->smtp_secure . '<br>' . PHP_EOL;
        echo 'Port: ' . $smtp_config->smtp_host_port . '<br>' . PHP_EOL;
        echo '<hr>' . PHP_EOL;

        //
        $result = PHPMaillerSend::the_send($data_send, $smtp_config, PHPMaillerSend::DEBUG_2);
        if ($result === true) {
            echo 'Gửi email thành công! from <strong>' . $smtp_config->smtp_host_user . '</strong> to <strong>' . $data_send['to'] . '</strong> <br>' . PHP_EOL;

            //
            return true;
        } else {
            echo 'Gửi email THẤT BẠI! from <strong>' . $smtp_config->smtp_host_user . '</strong> <br>' . PHP_EOL;
            print_r($result);
        }

        //
        return false;
    }

    // trả về json chứa thông tin của chat ID trên telegram -> dùng để gửi tin nhắn vào nhóm chat
    private function getTeleChatId()
    {
        // lấy ID nhóm chat trên tele
        $this->printTeleChatId(TelegramBot::getUpdates());

        // gửi luôn 1 đoạn test chức năng gửi mess
        TelegramBot::sendMessage(
            implode(
                PHP_EOL,
                [
                    date('r'),
                    __CLASS__ . ': ' . __FUNCTION__,
                    'IP: ' . $this->request->getIPAddress(),
                    'Agent: ' . $_SERVER['HTTP_USER_AGENT'],
                    __CLASS__ . ':' . __LINE__,
                ]
            )
        );

        //
        exit();
    }
    private function printTeleChatId($a, $show = 0)
    {
        //print_r( $a );

        //
        $has_id = false;
        foreach ($a as $k => $v) {
            if ($k == 'chat') {
                echo $k . ': <br>' . PHP_EOL;
                $this->printTeleChatId($v, 1);
                $has_id = true;
            } else if (is_object($v) || is_array($v)) {
                $this->printTeleChatId($v);
            } else if ($show > 0) {
                echo $k . ': ' . $v . '<br>' . PHP_EOL;
                /*
                } else {
                echo $k . ': ' . $v . '<br>' . PHP_EOL;
                */
            }
        }

        //
        if ($has_id === false) {
            echo 'Chat ID not found! <br>' . PHP_EOL;
        }
    }
}
