<?php

namespace App\Controllers;

//
use App\Language\Translate;
use App\Libraries\PHPMaillerSend;
use App\Libraries\PostType;
use App\Helpers\HtmlTemplate;

//
class Users extends Csrf
{
    protected $controller_name = 'Cá nhân';

    // danh sách các cột user được phép update
    protected $allow_update = [
        'user_email',
        'display_name',
        'user_nicename',
        'user_birthday',
        'user_phone',
        'avatar',
    ];

    //
    public function __construct()
    {
        parent::__construct();

        //
        if ($this->current_user_id <= 0) {
            // tạo url sau khi đăng nhập xong sẽ trỏ tới
            $login_redirect = DYNAMIC_BASE_URL . ltrim($_SERVER['REQUEST_URI'], '/');
            //die($login_redirect);

            //
            $login_url = base_url('guest/login') . '?login_redirect=' . urlencode($login_redirect) . '&msg=' . urlencode('Permission deny! ' . basename(__FILE__, '.php') . ':' . __LINE__);
            //die( $login_url );

            //
            die(header('Location: ' . $login_url));
            //die( 'Permission deny! ' . basename( __FILE__, '.php' ) . ':' . __LINE__ );
        }

        //
        $this->validation = \Config\Services::validation();

        //
        $this->breadcrumb[] = '<li><a href="users/profile">' . $this->controller_name . '</a></li>';
    }

    public function index()
    {
        return $this->profile();
    }
    public function profile()
    {
        $id = $this->current_user_id;

        //
        if (!empty($this->MY_post('data'))) {
            //print_r( $this->MY_post( 'data' ) );
            //die( __CLASS__ . ':' . __LINE__ );
            return $this->update($id);
        }

        // edit
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
            return $this->page404('ERROR ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! Không xác định được thông tin thành viên...');
        }

        //
        $this->teamplate['breadcrumb'] = view(
            'breadcrumb_view',
            array(
                'breadcrumb' => $this->breadcrumb
            )
        );

        //
        $this->teamplate['main'] = view(
            'profile_view',
            array(
                'seo' => $this->base_model->default_seo('Thông tin tài khoản', $this->getClassName(__CLASS__) . '/' . __FUNCTION__),
                'breadcrumb' => '',
                'data' => $data,
                'session_data' => $this->session_data,
            )
        );
        return view('users_view', $this->teamplate);
    }

    private function update($id)
    {
        $data = $this->MY_post('data');
        //print_r( $data );
        //die( __CLASS__ . ':' . __LINE__ );

        //
        if (isset($data['ci_pass'])) {
            if (empty($data['ci_pass'])) {
                $this->base_model->alert('Không xác định được mật khẩu cần thay đổi', 'warning');
            }
            $this->wgr_target();

            //
            $this->validation->reset();
            $this->validation->setRules([
                'ci_pass' => [
                    'label' => Translate::PASSWORD,
                    'rules' => 'required|min_length[5]|max_length[255]',
                    'errors' => [
                        'required' => $this->lang_model->get_the_text('translate_required', Translate::REQUIRED),
                        'min_length' => $this->lang_model->get_the_text('translate_min_len', Translate::MIN_LENGTH),
                        'max_length' => $this->lang_model->get_the_text('translate_max_len', Translate::MAX_LENGTH),
                    ],
                ]
            ]);
            if (!$this->validation->run($data)) {
                $this->set_validation_error($this->validation->getErrors(), $this->form_target);
                //die( __CLASS__ . ':' . __LINE__ );
            }

            // cập nhật mật khẩu mới cho user
            $this->user_model->update_member($id, [
                'ci_pass' => $data['ci_pass'],
            ]);

            //
            echo '<script>top.$(\'#data_ci_pass\').val(\'\');</script>';
            $this->base_model->alert('Cập nhật mật khẩu mới thành công');
        }


        /*
         * upload ảnh đại diện nếu có
         */
        if (!empty($_FILES) && isset($_FILES['avatar'])) {
            //print_r( $_FILES );
            $upload_files = $this->request->getFiles();
            //print_r( $upload_files );
            $file = $upload_files['avatar'];
            //print_r( $file );
            if ($file->isValid() && !$file->hasMoved()) {
                // 1 số định dạng file không cho phép upload trực tiếp
                $allow_upload = [
                    'jpg',
                    'jpeg',
                    'png'
                ];

                $file_ext = $file->guessExtension();
                //echo $file_ext . '<br>' . PHP_EOL;
                $file_ext = strtolower($file_ext);
                //echo $file_ext . '<br>' . PHP_EOL;

                // nếu có kiểm duyệt định dạng file -> chỉ các file trong này mới được upload
                if (!in_array($file_ext, $allow_upload)) {
                    $this->base_model->alert('Định dạng ' . strtoupper($file_ext) . ' chưa được hỗ trợ! Hiện chỉ hỗ trợ định dạng JPG hoặc PNG', 'error');
                }

                //
                $upload_path = $this->get_path_upload($this->current_user_id);
                //echo $upload_path . '<br>' . PHP_EOL;

                //
                $file_name = 'avatar' . '.' . $file_ext;
                //echo $file_name . '<br>' . PHP_EOL;

                //
                $file_path = $upload_path . $file_name;
                //echo $file_path . '<br>' . PHP_EOL;

                //
                $file->move($upload_path, $file_name, true);

                //
                if (!file_exists($file_path)) {
                    $this->base_model->alert('Upload thất bại! Không xác định được file sau khi upload', 'error');
                }
                chmod($file_path, DEFAULT_FILE_PERMISSION);

                //
                $data['avatar'] = str_replace(PUBLIC_PUBLIC_PATH, '', $file_path) . '?v=' . time();
                //print_r( $data );

                //
                //die( __CLASS__ . ':' . __LINE__ );
            }
            //die( __CLASS__ . ':' . __LINE__ );
        }


        // kiểm tra xem người dùng có thay đổi email không
        if (isset($data['user_email'])) {
            $change_email = false;

            //
            $this->validation->reset();
            $this->validation->setRules([
                'user_email' => [
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
            //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
            //print_r( $this->session_data );

            // kiểm tra định dạng email
            if ($this->validation->run($data)) {
                //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;

                // nếu email có sự thay đổi thì mới update
                if ($this->session_data['user_email'] != $data['user_email']) {
                    //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;

                    // xem email mới đã được sử dụng chưa
                    $user_id = $this->user_model->check_user_exist($data['user_email']);
                    //var_dump( $this->current_user_id );
                    //var_dump( $user_id );

                    // được sử dụng thì báo lỗi luôn
                    if ($user_id !== false) {
                        // ID tài khoản giống nhau
                        if ($user_id != $this->current_user_id) {
                            $this->base_model->alert('Email ' . $data['user_email'] . ' đã được sử dụng!', 'error');
                        }
                    }
                    // chưa sử dụng thì mới cho đổi
                    else {
                        $change_email = true;
                    }
                    //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;

                    // nếu email cũ là dạng email tự động tạo -> trùng với tên miền hiện tại thì cho đổi luôn
                    // còn không -> sẽ tiến hành gửi email xác thực
                    if ($change_email === true && strpos($this->session_data['user_email'], '@' . $_SERVER['HTTP_HOST']) === false) {
                        // tạo link để xác thực việc thay đổi email
                        $data_reset = [
                            'e' => $data['user_email'],
                            'oe' => $this->session_data['user_email'],
                            // hạn sử dụng của link
                            't' => time() + 3600,
                        ];

                        // token
                        $data_reset['token'] = $this->base_model->mdnam($data_reset['e'] . $data_reset['oe'] . $data_reset['t'], CUSTOM_MD5_HASH_CODE);

                        //
                        //print_r( $data_reset );
                        $link_change_email = [];
                        foreach ($data_reset as $k => $v) {
                            $link_change_email[] = $k . '=' . $v;
                        }
                        $link_change_email = base_url('guest/confirm_change_email') . '?' . implode('&', $link_change_email);
                        //echo $link_change_email . '<br>' . PHP_EOL;

                        //
                        //print_r( $this->session_data );
                        //echo $this->session_data[ 'user_email' ];


                        // thiết lập thông tin người nhận
                        $data_send = [
                            'to' => $this->session_data['user_email'],
                            'subject' => 'Xác nhận thay đổi email',
                            'message' => HtmlTemplate::render(
                                $this->base_model->get_html_tmp('change_email_confirm', '', 'html/mail-template/'),
                                [
                                    'base_url' => base_url(),
                                    'email' => $data['user_email'],
                                    'old_email' => $this->session_data['user_email'],
                                    'link_change_email' => $link_change_email,
                                ]
                            ),
                        ];
                        //print_r( $data_send );
                        //die( __CLASS__ . ':' . __LINE__ );

                        //
                        if (PHPMaillerSend::the_send($data_send, $this->option_model->get_smtp()) === true) {
                            $this->base_model->alert('Vui lòng kiểm tra email ' . $data_send['to'] . ' và làm theo hướng dẫn để xác thực việc thay đổi email.');
                        }
                        $this->base_model->alert('Gửi email xác thực THẤT BẠI! Vui lòng liên hệ với quản trị website.', 'error');
                    }
                }
            }

            // bỏ qua việc cập nhật email nếu không đạt các điều kiện
            //var_dump( $change_email );
            if ($change_email === false) {
                unset($data['user_email']);
            }
            //print_r( $data );
            //die( __CLASS__ . ':' . __LINE__ );
        }

        //
        $data_update = [];
        foreach ($data as $k => $v) {
            if (!in_array($k, $this->allow_update)) {
                echo $k . ' not in allow_update... <br>' . "'n'";
                continue;
            }
            $data_update[$k] = $v;
        }
        //print_r( $data_update );

        //
        if (empty($data_update)) {
            $this->base_model->alert('Không xác định được thông tin cần thay đổi', 'warning');
        }

        // cập nhật thông tin mới cho user
        $this->user_model->update_member($id, $data_update);

        /*
         * lưu thông tin đăng nhập mới vào session
         */
        //print_r( $this->session_data );
        foreach ($data as $k => $v) {
            $this->session_data[$k] = $v;
        }
        //print_r( $this->session_data );
        $data = $this->sync_login_data($this->session_data);
        //print_r( $data );
        $this->base_model->set_ses_login($data);

        //
        $this->base_model->alert('Cập nhật thông tin tài khoản thành công');
    }

    public function logout()
    {
        // xóa cache theo user để các chức năng liên quan đến user có thể tái sử dụng
        $has_cache = $this->base_model->dcache($this->user_model->key_cache($this->current_user_id));
        //echo 'Using cache delete Matching Total clear: ' . $has_cache . '<br>' . PHP_EOL;
        //die( __CLASS__ . ':' . __LINE__ );

        // nếu có session login từ admin vào 1 user nào đó -> quay lại session của admin
        $admin_login_as = $this->MY_session('admin_login_as');
        if (!empty($admin_login_as)) {
            $this->base_model->set_ses_login($admin_login_as);

            // xóa session login as
            $this->MY_session('admin_login_as', '');

            //
            //$this->MY_redirect(base_url('users/profile'), 301);
            $this->MY_redirect(base_url('admin/users/add') . '?id=' . $this->current_user_id, 301);
        }
        // còn không thì logout thôi
        else {
            session_destroy();
            //$this->session->destroy();

            // xóa cookie lưu ID đăng nhập
            //delete_cookie( $this->wrg_cookie_login_key );

            //
            if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
                $this->MY_redirect($_SERVER['HTTP_REFERER'], 301);
            } else {
                $this->MY_redirect(base_url('guest/login'), 301);
            }
        }
    }

    protected function get_path_upload($id, $dir = 'profile')
    {
        $upload_root = PUBLIC_HTML_PATH . PostType::MEDIA_PATH;
        //echo $upload_root . '<br>' . PHP_EOL;

        //
        $this->deny_visit_upload($upload_root);

        //
        if ($dir == '') {
            $dir = 'users';
        }

        //
        $upload_path = $this->media_path([
            $dir,
            $id,
        ], $upload_root);
        //echo $upload_path . '<br>' . PHP_EOL;
        //die( __CLASS__ . ':' . __LINE__ );

        //
        return $upload_path;
    }
}
