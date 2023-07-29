<?php

namespace App\Controllers;

//
use App\Libraries\UsersType;
use App\Libraries\DeletedStatus;
use App\Libraries\PHPMaillerSend;
use App\Language\Translate;
use App\Helpers\HtmlTemplate;
use App\Libraries\ConfigType;

//
class Guest extends Csrf
{
    // dùng để các class extends có thể thay đổi slug theo ý muốn
    public $controller_slug = 'guest';

    public $member_type = UsersType::GUEST;
    protected $firebase_config;
    protected $zalooa_config;

    /*
    * file JS khi sử dụng chức năng đăng nhập qua firebase
    * phone_auth: phần đăng nhập qua điện thoại sẽ được ưu tiên code riêng do nó có mất phí
    * dynamic_auth: các chức năng đăng nhập khác sẽ được viết riêng vào đây
    */
    public $file_auth = 'dynamic_auth';
    // thời gian hết hạn của mỗi token login -> để thấp cho nó bảo mật
    public $expires_time = 600;
    public $expires_reverify_time = 1800;

    public function __construct()
    {
        parent::__construct();

        //
        $this->validation = \Config\Services::validation();

        //
        //echo $_SERVER[ 'REQUEST_METHOD' ] . '<br>' . PHP_EOL;
        // quá trình submit bắt buộc phải có các tham số sau -> chống tắt javascript
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
            if (!isset($_POST['__wgr_request_from']) || !isset($_POST['__wgr_nonce'])) {
                //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
                // chuyển tham số này thành true -> dùng chung với captcha
                $this->has_captcha = true;
            }
        }

        // nạp config cho phần đăng nhập
        $this->firebase_config = $this->option_model->obj_config(ConfigType::FIREBASE);
        //print_r($this->firebase_config);
        $this->zalooa_config = $this->option_model->obj_config(ConfigType::ZALO);
        //print_r($this->zalooa_config);
        //die(__CLASS__ . ':' . __LINE__);
    }

    public function index()
    {
        return $this->login();
    }
    public function login()
    {
        $login_redirect = DYNAMIC_BASE_URL;
        //die( $login_redirect );

        if ($this->current_user_id > 0) {
            //var_dump( $this->form_target );
            //die( __CLASS__ . ':' . __LINE__ );
            if (!empty($this->MY_post('username'))) {
                $this->wgr_target();
                $this->base_model->alert('Bạn đang đăng nhập bằng tài khoản khác rồi!', $this->form_target);
            }
            return $this->done_action_login($login_redirect);
        }

        //
        if (!empty($this->MY_post('username'))) {
            $this->wgr_target();
            //die(__CLASS__ . ':' . __LINE__);

            //
            if ($this->firebase_config->disable_local_login == 'on') {
                $this->base_model->alert('Chức năng đăng nhập đang tạm ngưng!', $this->form_target);
            }

            // xem có phải nhập mã captcha không -> khi đăng nhập sai quá nhiều lần -> bắt buộc phải nhập captcha
            if ($this->base_model->check_faild_login() > 0) {
                //die( __CLASS__ . ':' . __LINE__ );
                $this->check_required_captcha();
            }
            //var_dump($this->has_captcha);
            //die(__CLASS__ . ':' . __LINE__);

            //
            if ($this->has_captcha === false) {
                //print_r( $_POST );
                $this->validation->reset();
                $this->validation->setRules([
                    'username' => [
                        'label' => Translate::USERNAME,
                        'rules' => 'required',
                        'errors' => [
                            'required' => $this->lang_model->get_the_text('translate_required', Translate::REQUIRED),
                        ],
                    ],
                    'password' => [
                        'label' => Translate::PASSWORD,
                        'rules' => 'required|min_length[6]',
                        'errors' => [
                            'required' => $this->lang_model->get_the_text('translate_required', Translate::REQUIRED),
                            'min_length' => $this->lang_model->get_the_text('translate_min_len', Translate::MIN_LENGTH),
                        ],
                    ]
                ]);
                //die(__CLASS__ . ':' . __LINE__);

                //
                if (!$this->validation->run($_POST)) {
                    $this->set_validation_error($this->validation->getErrors(), $this->form_target);
                } else {
                    //die(__CLASS__ . ':' . __LINE__);

                    //
                    if ($this->checkaccount() === true) {
                        // lấy dữ liệu đăng nhập của người dùng
                        $session_data = $this->base_model->get_ses_login();

                        //
                        /*
                        if ( function_exists( 'set_cookie' ) ) {
                        //die( $this->wrg_cookie_login_key );
                        //set_cookie( $this->wrg_cookie_login_key, $this->session_data[ 'ID' ] . '|' . time() . '|' . md5( $this->wrg_cookie_login_key . $this->session_data[ 'ID' ] ), 3600, '.' . $_SERVER[ 'HTTP_HOST' ], '/' );
                        }
                        */

                        //
                        if (isset($_REQUEST['login_redirect']) && $_REQUEST['login_redirect'] != '') {
                            $login_redirect = $_REQUEST['login_redirect'];
                        } else if (!empty($session_data) && isset($session_data['userLevel']) && $session_data['userLevel'] > 0) {
                            $login_redirect = base_url(CUSTOM_ADMIN_URI);
                        }
                        //die( $login_redirect );

                        //
                        return $this->done_action_login($login_redirect);
                    }
                }
            } else {
                $this->base_model->alert('Không xác định được nonce', 'error');
                //die(__CLASS__ . ':' . __LINE__);
            }
        }

        //
        $this->teamplate['main'] = view(
            'login_view',
            array(
                //'option_model' => $this->option_model,
                'seo' => $this->guest_seo('Đăng nhập', __FUNCTION__),
                'breadcrumb' => '',
                'login_redirect' => $this->loginRedirect(),
                //'cateByLang' => $cateByLang,
                //'serviceByLang' => $serviceByLang,
                'set_login' => $this->MY_get('set_login', ''),
                // tạo chuỗi dùng để xác thực url sau khi đăng nhập thành công
                'sign_in_success_params' => $this->firebaseSignInSuccessParams(),
                'expires_time' => $this->expires_time,
                'file_auth' => $this->file_auth,
                'firebase_config' => $this->firebase_config,
                'zalooa_config' => $this->zalooa_config,
            )
        );
        //print_r( $this->teamplate );
        return view('layout_view', $this->teamplate);
    }

    protected function checkaccount()
    {
        $username = $this->MY_post('username');
        $user_pass = md5($this->MY_post('password'));
        $ci_pass = $this->base_model->mdnam($this->MY_post('password'));

        //$result = $this->user_model->login( $username, $user_pass );
        $result = $this->user_model->login($username, $user_pass, $ci_pass);
        if (empty($result)) {
            $sql = $this->base_model->select(
                'ID',
                'users',
                array(
                    // các kiểu điều kiện where
                    //'user_email' => $username,
                ),
                array(
                    'where_or' => array(
                        'user_email' => $username,
                        'user_login' => $username,
                        'user_phone' => $username,
                    ),
                    // hiển thị mã SQL để check
                    //'show_query' => 1,
                    // trả về câu query để sử dụng cho mục đích khác
                    //'get_query' => 1,
                    //'offset' => 0,
                    'limit' => 1
                )
            );

            // thêm số lần đăng nhập sai
            $this->base_model->push_faild_login();

            //
            if (empty($sql)) {
                if (strpos($username, '@') !== false) {
                    $this->base_model->msg_error_session('Email đăng nhập không chính xác', $this->form_target);
                } else {
                    $this->base_model->msg_error_session('Tài khoản đăng nhập không chính xác', $this->form_target);
                }
            } else {
                $this->base_model->msg_error_session('Mật khẩu đăng nhập không chính xác', $this->form_target);
            }

            //
            return false;
        }

        // reset lại captcha login
        $this->base_model->reset_faild_login();

        // tài khoản bị KHÓA
        $result['user_status'] *= 1;
        if ($result['user_status'] != UsersType::FOR_DEFAULT * 1) {
            // kiểm tra xem đã đến hạn mở khóa chưa
            if ($result['user_status'] > 0) {
                $auto_unlock = strtotime($result['last_login']) + ($result['user_status'] * 3600);

                // nếu đã hết hạn bị KHÓA -> tự động mở khóa cho tài khoản
                if ($auto_unlock < time()) {
                    $this->user_model->update_member($result['ID'], [
                        'user_status' => UsersType::FOR_DEFAULT,
                    ]);

                    //
                    $this->base_model->msg_session('Mở khóa tài khoản thành công! Vui lòng đăng nhập lại.');

                    return $this->done_action_login();
                }
                $auto_unlock = date(EBE_DATETIME_FORMAT, $auto_unlock);
            } else {
                $auto_unlock = '<strong>Không xác định</strong>. Vui lòng liên hệ admin.';
            }
            $this->base_model->msg_error_session('Tài khoản đang bị <strong>' . UsersType::statusList($result['user_status']) . '</strong>! Thời gian mở khóa: ' . $auto_unlock, $this->form_target);
            return false;
        }
        // tài khoản bị XÓA
        else if ($result['is_deleted'] * 1 != DeletedStatus::FOR_DEFAULT * 1) {
            $this->base_model->msg_error_session('Tài khoản không tồn tại trong hệ thống! Vui lòng liên hệ admin.', $this->form_target);
            return false;
        }

        //
        //print_r( $result );
        //die( __CLASS__ . ':' . __LINE__ );
        $result = $this->sync_login_data($result);
        $result['user_activation_key'] = session_id();

        //
        $this->user_model->update_member($result['ID'], [
            'last_login' => date(EBE_DATETIME_FORMAT),
            'login_type' => UsersType::LOCAL,
            'user_activation_key' => $result['user_activation_key'],
        ]);

        //
        $this->base_model->set_ses_login($result);

        //
        return true;
    }

    public function register()
    {
        //print_r( $this->getconfig );
        // nếu website không cho đăng ký thành viên thì hiển thị view thông báo tương ứng
        if (isset($this->getconfig->disable_register_member) && $this->getconfig->disable_register_member == 'on') {
            $this->teamplate['main'] = view(
                'register_disable_view',
                array(
                    'seo' => $this->guest_seo('Website tạm dừng việc đăng ký tài khoản mới', __FUNCTION__),
                    'breadcrumb' => '',
                    //'cateByLang' => $cateByLang,
                    //'serviceByLang' => $serviceByLang,

                )
            );
            return view('layout_view', $this->teamplate);
        }

        //
        $login_redirect = DYNAMIC_BASE_URL;

        if ($this->current_user_id > 0) {
            return $this->done_action_login($login_redirect);
        }

        //
        $data = $this->MY_post('data');
        if (!empty($data) && isset($data['email'])) {
            $this->wgr_target();

            // đăng ký tài khoản bắt buộc phải có captcha
            $this->check_required_captcha();

            //
            if ($this->has_captcha === false) {
                $this->validation->reset();
                $this->validation->setRules([
                    'email' => [
                        'label' => 'Email',
                        //'rules' => 'required|min_length[5]|max_length[255]|valid_email|is_unique[users.user_email]',
                        'rules' => 'required|min_length[5]|max_length[255]|valid_email',
                        'errors' => [
                            'required' => $this->lang_model->get_the_text('translate_required', Translate::REQUIRED),
                            'min_length' => $this->lang_model->get_the_text('translate_min_len', Translate::MIN_LENGTH),
                            'max_length' => $this->lang_model->get_the_text('translate_max_len', Translate::MAX_LENGTH),
                            'valid_email' => $this->lang_model->get_the_text('translate_valid_email', Translate::VALID_EMAIL),
                        ],
                    ],
                    'password' => [
                        'label' => 'Mật khẩu',
                        'rules' => 'required|min_length[5]|max_length[255]',
                        'errors' => [
                            'required' => $this->lang_model->get_the_text('translate_required', Translate::REQUIRED),
                            'min_length' => $this->lang_model->get_the_text('translate_min_len', Translate::MIN_LENGTH),
                            'max_length' => $this->lang_model->get_the_text('translate_max_len', Translate::MAX_LENGTH),
                        ],
                    ]
                ]);

                // mật khẩu xác nhận
                if ($data['password'] != $data['password2']) {
                    $this->base_model->msg_error_session('Mật khẩu xác nhận không chính xác', $this->form_target);
                }
                // lấy lỗi trả về nếu có
                else if (!$this->validation->run($data)) {
                    $this->set_validation_error($this->validation->getErrors(), $this->form_target);
                }
                // tiến hành đăng ký tài khoản
                else {
                    $data['email'] = $data['email'];
                    $data['user_email'] = $data['email'];
                    $data['ci_pass'] = $data['password'];
                    $data['member_type'] = $this->member_type;
                    //$data[ 'username' ] = str_replace( '.', '', str_replace( '@', '', $data[ 'email' ] ) );
                    //$data[ 'password' ] = md5( $data[ 'password' ] );
                    //$data[ 'level' ] = '0';
                    //$data[ 'status' ] = '1';
                    //$data[ 'accept_mail' ] = 0;
                    //$data[ 'avatar' ] = base_url( 'frontend/images/icon-user-not-login.png' );
                    //$data[ 'created' ] = date( EBE_DATETIME_FORMAT );
                    //print_r( $data );
                    //die( 'register' );

                    //
                    //$insert = $this->base_model->insert( 'tbl_user', $data, true );
                    $insert = $this->user_model->insert_member($data);
                    if ($insert < 0) {
                        $this->base_model->msg_error_session('Email đã được sử dụng', $this->form_target);
                    } else if ($insert !== false) {
                        $this->base_model->msg_session('Đăng ký tài khoản thành công!');

                        return $this->done_action_login(base_url('guest/login'));
                    } else {
                        $this->base_model->msg_error_session('Lỗi đăng ký tài khoản', $this->form_target);
                    }
                }
            }
        }

        //
        //echo $this->controller_slug;
        //echo $this->member_type;

        //
        $this->teamplate['main'] = view(
            'register_view',
            array(
                'seo' => $this->guest_seo('Đăng ký tài khoản mới', __FUNCTION__),
                'breadcrumb' => '',
                //'cateByLang' => $cateByLang,
                //'serviceByLang' => $serviceByLang,
                'member_type' => $this->member_type,
                'form_action' => $this->controller_slug . '/' . __FUNCTION__,
            )
        );
        return view('layout_view', $this->teamplate);
    }

    protected function check_email_exist()
    {
        $data = $this->MY_post('data');
        return $this->user_model->check_user_exist($data['email']);
    }

    public function resetpass()
    {
        $data = $this->MY_post('data');
        if (!empty($data) && isset($data['email'])) {
            $this->wgr_target();
            $this->checking_captcha();

            //
            if ($this->has_captcha === false) {
                //print_r( $data );
                //die( __CLASS__ . ':' . __LINE__ );
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
                    ]
                ]);

                //
                if (!$this->validation->run($data)) {
                    $this->set_validation_error($this->validation->getErrors(), $this->form_target);
                } else {
                    if ($this->check_resetpass() === true) {
                        // sử dụng cache để không cho người dùng gửi email liên tục
                        $in_cache = __FUNCTION__ . $this->base_model->_eb_non_mark_seo($data['email']);
                        //die( $in_cache );
                        if ($this->base_model->scache($in_cache) === NULL) {
                            /*
                             * link reset pass
                             */
                            $data_reset = [
                                'e' => $data['email'],
                                // hạn sử dụng của link
                                't' => time() + 600,
                            ];

                            // token
                            $data_reset['token'] = $this->base_model->mdnam($data_reset['e'] . $data_reset['t'], CUSTOM_MD5_HASH_CODE);

                            //
                            //print_r( $data_reset );
                            $link_reset_pass = [];
                            foreach ($data_reset as $k => $v) {
                                $link_reset_pass[] = $k . '=' . $v;
                            }
                            $link_reset_pass = base_url('guest/confirm_reset_password') . '?' . implode('&', $link_reset_pass);
                            //echo $link_reset_pass . '<br>' . PHP_EOL;
                            //echo base_url() . '<br>' . PHP_EOL;


                            // thiết lập thông tin người nhận
                            $data_send = [
                                'to' => $data['email'],
                                'subject' => 'Khởi tạo lại mật khẩu đăng nhập',
                                'message' => HtmlTemplate::render(
                                    $this->base_model->get_html_tmp('reset_password_confirm', '', 'html/mail-template/'),
                                    [
                                        'base_url' => base_url(),
                                        'email' => $data['email'],
                                        'link_reset_pass' => $link_reset_pass,
                                    ]
                                ),
                            ];
                            //print_r( $data_send );
                            //die( __CLASS__ . ':' . __LINE__ );

                            //
                            if (PHPMaillerSend::the_send($data_send, $this->option_model->get_smtp()) === true) {
                                $this->base_model->msg_session('Gửi email lấy lại mật khẩu thành công! Vui lòng kiểm tra email và làm theo hướng dẫn để tiếp tục.');

                                //
                                $this->base_model->scache($in_cache, time(), 60);
                            } else {
                                $this->base_model->msg_error_session('Gửi email lấy lại mật khẩu THẤT BẠI! Vui lòng liên hệ với quản trị website.');
                            }
                        } else {
                            $this->base_model->msg_session('Vui lòng kiểm tra email và làm theo hướng dẫn để tiếp tục.');
                        }

                        return $this->done_action_login();
                    }
                }
            }
        }

        //
        $this->teamplate['main'] = view(
            'resetpass_view',
            array(
                'seo' => $this->guest_seo('Lấy lại mật khẩu', __FUNCTION__),
                'breadcrumb' => '',
                //'cateByLang' => $cateByLang,
                //'serviceByLang' => $serviceByLang,

            )
        );
        return view('layout_view', $this->teamplate);
    }

    protected function check_resetpass()
    {
        $data = $this->MY_post('data');
        return $this->user_model->check_resetpass($data['email']);
    }

    protected function guest_seo($name, $uri)
    {
        //echo $uri . '<br>' . PHP_EOL;
        return $this->base_model->default_seo($name, $this->getClassName(__CLASS__) . '/' . $uri);
    }

    public function confirm_reset_password()
    {
        //print_r( $_GET );

        //
        $email = $this->MY_get('e', '');
        $expire = $this->MY_get('t', 0);
        $token = $this->MY_get('token', '');

        // các tham số không thể thiếu -> thiếu là bỏ qua luôn
        if ($email == '' || $expire <= 0 || $token == '') {
            $this->base_model->msg_error_session('Dữ liệu đầu vào không chính xác!');
        } else {
            $user_id = $this->user_model->check_user_exist($email);

            //
            if ($user_id === false) {
                $this->base_model->msg_error_session('Email ' . $email . ' không tồn tại trong hệ thống!');
            } else {
                // sử dụng cache để kiểm soát không cho dùng link liên tục
                $in_cache = __FUNCTION__;
                //die( $in_cache );
                if ($this->user_model->the_cache($user_id, $in_cache) === NULL) {
                    // kiểm tra độ khớp của dữ liệu
                    if ($expire < time()) {
                        $this->base_model->msg_error_session('Liên kết đã hết hạn sử dụng! Hãy gửi yêu cầu cung cấp liên kết mới.');
                    }
                    // mã xác nhận -> token
                    else if ($this->base_model->mdnam($email . $expire, CUSTOM_MD5_HASH_CODE) != $token) {
                        $this->base_model->msg_error_session('Mã xác nhận không chính xác!');
                    }
                    // đúng thì tiến hành reset password
                    else {
                        /*
                         * v2: đăng nhập và chuyển đến form đổi pass
                         */
                        $data = $this->base_model->select(
                            '*',
                            $this->user_model->table,
                            array(
                                // các kiểu điều kiện where
                                // mặc định
                                'ID' => $user_id,
                                // kiểm tra email đã được sử dụng rồi hay chưa thì không cần kiểm tra trạng thái XÓA -> vì có thể user này đã bị xóa vĩnh viễn
                                'is_deleted' => DeletedStatus::FOR_DEFAULT,
                            ),
                            array(
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
                        //print_r( $data );

                        //
                        if (empty($data)) {
                            $this->base_model->msg_error_session('Không xác định được thông tin tài khoản của bạn!');
                        } else {
                            $data = $this->sync_login_data($data);
                            //print_r( $data );

                            // lưu thông tin đăng nhập tự động cho khách
                            $this->base_model->set_ses_login($data);

                            //
                            $this->base_model->msg_session('Xác thực và đăng nhập tự động thành công. Hãy cập nhật mật khẩu mới để sử dụng.');

                            //
                            return $this->done_action_login(base_url('users/profile#data_ci_pass'));
                        }


                        /*
                         * v1: tự đổi sang pass mới
                         */
                        //$this->change_random_password();
                    }
                } else {
                    $this->base_model->msg_session('Vui lòng kiểm tra email ' . $email . ' để liên kết khởi tạo mật khẩu mới.');
                }
            }
        }

        // dùng chung view với trang đăng nhập
        $this->teamplate['main'] = view(
            'login_view',
            array(
                'seo' => $this->guest_seo('Khởi tạo lại mật khẩu', __FUNCTION__),
                'breadcrumb' => '',
                'login_redirect' => $this->loginRedirect(),
                'set_login' => $this->MY_get('set_login', ''),
                'firebase_config' => $this->firebase_config,
                'zalooa_config' => $this->zalooa_config,
            )
        );
        //print_r( $this->teamplate );
        return view('layout_view', $this->teamplate);
    }

    private function change_random_password()
    {
        $random_password = substr($this->base_model->mdnam(time()), 0, 12);
        //echo $random_password . '<br>' . PHP_EOL;

        // thiết lập thông tin người nhận
        $data_send = [
            'to' => $email,
            'subject' => 'Mật khẩu đăng nhập mới',
            'message' => HtmlTemplate::render(
                $this->base_model->get_html_tmp('reset_password', '', 'html/mail-template/'),
                [
                    'base_url' => base_url('guest/login'),
                    'email' => $email,
                    'ip' => $this->request->getIPAddress(),
                    'random_password' => $random_password,
                    'agent' => $_SERVER['HTTP_USER_AGENT'],
                    'date_send' => date('r'),
                ]
            ),
        ];
        //print_r( $data_send );
        //die( __CLASS__ . ':' . __LINE__ );

        // gửi email thông báo
        if (PHPMaillerSend::the_send($data_send, $this->option_model->get_smtp()) === true) {
            $this->base_model->msg_session('Mật khẩu mới đã được thiết lập! Vui lòng kiểm tra email ' . $email . ' để lấy mật khẩu đăng nhập mới.');

            // cập nhật mật khẩu mới cho user
            $this->user_model->update_member($user_id, [
                'ci_pass' => $random_password,
            ]);

            // không cho thao tác liên tục
            $this->user_model->the_cache($user_id, $in_cache, time());
        } else {
            $this->base_model->msg_error_session('Gửi email cung cấp mật khẩu mới THẤT BẠI! Vui lòng liên hệ với quản trị website.');
        }
    }

    public function confirm_change_email()
    {
        //print_r( $_GET );
        //die( __CLASS__ . ':' . __LINE__ );

        //
        $email = $this->MY_get('e', '');
        $old_email = $this->MY_get('oe', '');
        $expire = $this->MY_get('t', 0);
        $token = $this->MY_get('token', '');

        // các tham số không thể thiếu -> thiếu là bỏ qua luôn
        if ($email == '' || $old_email == '' || $expire <= 0 || $token == '') {
            $this->base_model->msg_error_session('Dữ liệu đầu vào không chính xác!');
        } else {
            $user_id = $this->user_model->check_user_exist($old_email);

            //
            if ($user_id === false) {
                $this->base_model->msg_error_session('Email ' . $old_email . ' không tồn tại trong hệ thống!');
            } else {
                // sử dụng cache để kiểm soát không cho dùng link liên tục
                $in_cache = __FUNCTION__;
                //die( $in_cache );
                if ($this->user_model->the_cache($user_id, $in_cache) === NULL) {
                    // kiểm tra độ khớp của dữ liệu
                    if ($expire < time()) {
                        $this->base_model->msg_error_session('Liên kết đã hết hạn sử dụng! Hãy gửi yêu cầu cung cấp liên kết mới.');
                    }
                    // mã xác nhận -> token
                    else if ($this->base_model->mdnam($email . $old_email . $expire, CUSTOM_MD5_HASH_CODE) != $token) {
                        $this->base_model->msg_error_session('Mã xác nhận không chính xác!');
                    }
                    // đúng thì tiến hành thay đổi email
                    else {
                        //print_r( $_GET );

                        // cập nhật email mới
                        $this->user_model->update_member($user_id, [
                            'user_email' => $email,
                        ]);

                        // select lại dữ liệu
                        $data = $this->base_model->select(
                            '*',
                            $this->user_model->table,
                            array(
                                // các kiểu điều kiện where
                                // mặc định
                                'ID' => $user_id,
                                // kiểm tra email đã được sử dụng rồi hay chưa thì không cần kiểm tra trạng thái XÓA -> vì có thể user này đã bị xóa vĩnh viễn
                                'is_deleted' => DeletedStatus::FOR_DEFAULT,
                            ),
                            array(
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
                        //print_r( $data );

                        //
                        if (empty($data)) {
                            $this->base_model->msg_error_session('Không xác định được thông tin tài khoản của bạn!');
                        } else {
                            $data = $this->sync_login_data($data);
                            //print_r( $data );

                            // lưu thông tin đăng nhập tự động cho khách
                            $this->base_model->set_ses_login($data);

                            //
                            $this->base_model->msg_session('Thay đổi email đăng nhập thành công.');

                            //
                            return $this->done_action_login(base_url('users/profile'));
                        }

                        //
                        //die( __CLASS__ . ':' . __LINE__ );
                    }
                } else {
                    $this->base_model->msg_session('Vui lòng kiểm tra email ' . $email . ' để lấy mật khẩu đăng nhập mới.');
                }
            }
        }
        if ($this->current_user_id > 0) {
            return $this->done_action_login(base_url('users/profile'));
        }

        // dùng chung view với trang đăng nhập
        $this->teamplate['main'] = view(
            'login_view',
            array(
                'seo' => $this->guest_seo('Thay đổi email đăng nhập', __FUNCTION__),
                'breadcrumb' => '',
                'login_redirect' => $this->loginRedirect(),
                'set_login' => $this->MY_get('set_login', ''),
                'firebase_config' => $this->firebase_config,
                'zalooa_config' => $this->zalooa_config,
            )
        );
        //print_r( $this->teamplate );
        return view('layout_view', $this->teamplate);
    }

    protected function loginRedirect()
    {
        $login_redirect = '';
        if (isset($_REQUEST['login_redirect'])) {
            $login_redirect = urldecode($_REQUEST['login_redirect']);
        } else if (
            isset($_SERVER['HTTP_REFERER']) &&
            $_SERVER['HTTP_REFERER'] &&
            strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) !== false
        ) {
            $login_redirect = urldecode($_SERVER['HTTP_REFERER']);
        }
        return $login_redirect;
    }

    protected function myAccessToken($str = '')
    {
        if ($str != '') {
            $str = $this->base_model->mdnam($str);
            return $this->base_model->MY_session('access_token' . session_id(), $str);
        }
        return $this->base_model->MY_session('access_token' . session_id());
    }

    protected function firebaseSignInSuccessParams($hash_code = '')
    {
        $current_time = time();
        // tạo hash code mặc định nếu chưa có
        if (empty($hash_code)) {
            $hash_code = session_id();
        }

        // lưu access_token vào cache -> lúc cần kiểm tra sẽ lấy từ cache để kiểm tra -> tăng bảo mật
        $this->myAccessToken($current_time . $hash_code);

        // trả về thông số tăng cường bảo mật cho quá trình đăng nhập qua firebase
        return [
            'success_url' => base_url('firebase2s/sign_in_success'),
            //'token_url' => base_url('firebase2s/sign_in_token'),
            //'firebase_config' => base_url('firebase2s/firebase_config'),
            // thêm 3 ký tự ngẫu nhiên vào expires_token -> để tăng bảo mật
            'expires_token' => $current_time . rand(100, 999),
            //'user_token' => $this->base_model->mdnam($current_time . session_id()),
        ];
    }
}
