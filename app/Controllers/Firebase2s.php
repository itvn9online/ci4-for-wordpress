<?php

namespace App\Controllers;

// Libraries
use App\Libraries\UsersType;
use App\Libraries\DeletedStatus;
use App\Libraries\PHPMaillerSend;
use App\Helpers\HtmlTemplate;

//
class Firebase2s extends Firebases
{
    // với 1 số controller, sẽ không nạp cái HTML header vào, nên có thêm tham số này để không nạp header nữa
    public $preload_header = false;

    public function __construct()
    {
        parent::__construct();
    }

    // trả về bản dịch của chức năng đăng nhâp qua firebase
    protected function firebaseLang($key, $default_value)
    {
        return $this->lang_model->get_the_text('firebase_' . $key, $default_value);
    }

    // kiểm tra dữ liệu trống
    protected function checkEmptyParams($str, $err)
    {
        if (empty($str)) {
            $this->result_json_type($err);
        }
        return $str;
    }

    // kiểm tra dữ liệu trống hoặc không có trong config
    protected function checkConfigParams($str, $err)
    {
        if (empty($str) || strpos($this->firebase_config->g_firebase_config, $str) === false) {
            $this->result_json_type($err);
        }
        return $str;
    }

    public function sign_in_success($callBack = true)
    {
        // kiểm tra url submit tới xem có khớp nhau không
        if (!isset($_SERVER['HTTP_REFERER']) || empty($_SERVER['HTTP_REFERER'])) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => $this->firebaseLang('referer', 'Không xác định được referer!'),
            ]);
        }
        $referer = explode('//', $_SERVER['HTTP_REFERER']);
        if (!isset($referer[1])) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => $this->firebaseLang('referer_https', 'Referer không hợp lệ'),
            ]);
        }
        //$this->result_json_type([$_SERVER['HTTP_REFERER']]);
        $referer = explode('/', $referer[1]);
        $referer = $referer[0];
        if ($referer != $_SERVER['HTTP_HOST']) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => $this->firebaseLang('referer_host', 'Referer không phù hợp'),
            ]);
        }
        //$this->result_json_type([$referer]);

        // xem có config cho chức năng đăng nhập qua firebase không
        $this->checkEmptyParams(trim($this->firebase_config->g_firebase_config), [
            'code' => __LINE__,
            'error' => $this->firebaseLang('firebase_config', 'firebase_config chưa được thiết lập'),
        ]);

        // chỉ nhận method post
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->result_json_type($_GET);
        }

        // kiểm tra tính hợp lệ của url
        $this->firebaseUrlExpires($this->MY_post('expires_token'), $this->expires_time);

        //
        //$this->firebaseSessionToken($expires_token, $this->MY_post('user_token'));

        //
        $id_token = $this->checkEmptyParams($this->MY_post('id_token'), [
            'code' => __LINE__,
            'error' => $this->firebaseLang('id_token', 'id_token trống'),
        ]);

        // lấy uid
        $firebase_uid = $this->checkEmptyParams($this->MY_post('uid', ''), [
            'code' => __LINE__,
            'error' => $this->firebaseLang('uid', 'user_id trống'),
        ]);

        // kiểm tra tính xác thực của token -> so khớp token sau khi biên dịch với uid xem có giống nhau không
        $this->phpJwt($id_token, $firebase_uid);

        // nếu là request lưu uid để lát so khớp thì xử lý riêng tại đây -> tận dụng các hàm kiểm tra bảo mật ở trên
        if (!empty($this->MY_post('token_url'))) {
            return $this->sign_in_token($firebase_uid);
        }

        // so khớp trong cache -> trước đó sẽ có 1 request update cache này -> nếu không có nghĩa là lỗi
        if ($this->id_cache_token() != $firebase_uid) {
            $this->result_json_type([
                'code' => __LINE__,
                'auto_logout' => __LINE__,
                'error' => $this->firebaseLang('cache_token', 'Lỗi xác minh danh tính! Vui lòng thử lại...'),
            ]);
        }

        //
        $this->checkConfigParams($this->MY_post('project_id'), [
            'code' => __LINE__,
            'error' => $this->firebaseLang('project_id', 'project_id không phù hợp!'),
        ]);

        // apikey phải tồn tại trong config thì mới cho tiếp tục
        $this->checkConfigParams($this->MY_post('apikey'), [
            'code' => __LINE__,
            'error' => $this->firebaseLang('apikey', 'apikey không phù hợp'),
        ]);
        // apiurl phải tồn tại trong config thì mới cho tiếp tục
        $this->checkConfigParams($this->MY_post('apiurl'), [
            'code' => __LINE__,
            'error' => $this->firebaseLang('apiurl', 'apiurl không phù hợp'),
        ]);

        //$this->result_json_type($_POST);

        //
        $name = $this->MY_post('name');
        $email = trim($this->MY_post('email'));
        //$email = 'itvn9online@yahoo.com';
        $phone = trim($this->MY_post('phone'));
        //$phone = '+84984533228';
        $photo = $this->MY_post('photo', '');

        //
        $where = [
            'is_deleted' => DeletedStatus::FOR_DEFAULT,
        ];
        $where_like = [];
        if (!empty($email) && strpos($email, '@') !== false) {
            $where['user_email'] = $email;
        } else if (!empty($phone)) {
            $where_like['user_phone'] = substr($phone, -9);
        } else {
            $this->result_json_type([
                'code' => __LINE__,
                'auto_logout' => __LINE__,
                'error' => $this->firebaseLang('email_or_phone', 'Không xác định được Email hoặc Số điện thoại'),
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
            // cập nhật firebase_uid nếu chưa có
            if (empty($data['firebase_uid'])) {
                //
                $this->checkUid($firebase_uid);

                // verify email khi chưa có firebase_uid
                if ($this->firebase_config->skipverify_firebase_email != 'on') {
                    // muốn an toàn hơn thì nên làm chức năng gửi email xác thực xong mới cập nhật firebase_uid
                    if (!empty($email) && strpos($email, '@') !== false) {
                        $this->reVerifyFirebaseEmail($data, [
                            'uid' => $firebase_uid
                        ]);
                    } else if (strlen($phone) > 9) {
                        //$this->result_json_type($data);
                        $this->user_model->update_member($data['ID'], [
                            // cập nhật firebase_uid
                            'firebase_uid' => $this->base_model->mdnam($firebase_uid),
                        ]);
                    } else {
                        $this->result_json_type([
                            'code' => __LINE__,
                            'error' => $this->firebaseLang('empty_email', 'Tài khoản không thể kích hoạt vì thiếu email'),
                        ]);
                    }
                } else {
                    $this->user_model->update_member($data['ID'], [
                        // cập nhật firebase_uid
                        'firebase_uid' => $this->base_model->mdnam($firebase_uid),
                    ]);
                }
            }
            // nếu có firebase_uid -> so khớp phần dữ liệu này -> coi như đây là password
            else if ($data['firebase_uid'] != $this->base_model->mdnam($firebase_uid)) {
                if ($this->firebase_config->skipverify_firebase_email != 'on') {
                    $this->reVerifyFirebaseEmail($data, [
                        'uid' => $firebase_uid
                    ]);
                }

                //
                $this->result_json_type([
                    'code' => __LINE__,
                    'error' => $this->firebaseLang('user_id_mismatched', 'user_id không đúng'),
                ]);
            }

            // tạo session login
            $data = $this->sync_login_data($data);
            //$data['user_activation_key'] = session_id();

            // cập nhật 1 số thông tin kiểu cố định
            $this->user_model->update_member($data['ID'], [
                'last_login' => date(EBE_DATETIME_FORMAT),
                'login_type' => UsersType::FIREBASE,
                //'user_activation_key' => $data['user_activation_key'],
                'member_verified' => UsersType::VERIFIED,
            ]);

            // cho phép đăng nhập nếu tài khoản chưa bị đánh dấu xóa
            $this->user_model->update_member($data['ID'], [
                'user_status' => UsersType::FOR_DEFAULT,
            ], [
                // nếu tài khoản không bị khóa vĩnh viễn
                'user_status !=' => UsersType::NO_LOGIN,
                // và chưa bị đánh dấu xóa
                'is_deleted' => DeletedStatus::FOR_DEFAULT,
            ]);

            // cập nhật avt nếu chưa có
            if (empty($data['avatar']) && $photo != '') {
                $this->user_model->update_member($data['ID'], [
                    'avatar' => $photo,
                ]);
            }

            //
            $this->base_model->set_ses_login($data);
        } else {
            //
            $this->checkUid($firebase_uid);

            //
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
                'avatar' => $photo,
                'firebase_uid' => $this->base_model->mdnam($firebase_uid),
                'member_verified' => UsersType::VERIFIED,
            ];
            //$this->result_json_type($data);
            $insert = $this->user_model->insert_member($data);
            if ($insert < 0) {
                $this->result_json_type([
                    'code' => __LINE__,
                    'error' => $this->firebaseLang('email_used', 'Email đã được sử dụng'),
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
                    'error' => $this->firebaseLang('error_create', 'Lỗi đăng ký tài khoản'),
                ]);
            }
        }
        //$this->result_json_type($data);
        //$this->result_json_type($_POST);
        $this->result_json_type([
            'ok' => __LINE__,
        ]);
    }

    // kiểm tra uid đã được sử dụng chưa
    protected function checkUid($firebase_uid)
    {
        // xem đã có tài khoản nào sử dụng firebase_uid này chưa
        $check_uid = $this->base_model->select(
            'ID',
            $this->user_model->table,
            [
                'firebase_uid' => $this->base_model->mdnam($firebase_uid),
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
        if (!empty($check_uid)) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => $this->firebaseLang('check_uid', 'user_id đã được sử dụng bởi một tài khoản khác'),
            ]);
        }
    }

    // gửi email xác thực lại thông tin đăng nhập qua firebase
    protected function reVerifyFirebaseEmail($data, $ops = [])
    {
        //$this->result_json_type($data);

        // chuẩn bị gửi mail báo xác thực email
        $smtp_config = $this->option_model->get_smtp();

        //
        if (isset($ops['uid']) && !empty($ops['uid'])) {
            $verify_params = $this->firebaseSignInSuccessParams($ops['uid']);
            $verify_params['uid'] = $ops['uid'];
            $verify_params['verify_key'] = $this->base_model->mdnam($data['ID'] . $ops['uid']);
        } else {
            $verify_params = $this->firebaseSignInSuccessParams();
            $verify_params['verify_key'] = $this->base_model->mdnam($data['ID'] . session_id());
        }
        //print_r($verify_params);

        //
        $verify_url = base_url('firebase2s/verify_email') . '?nse=' . $data['ID'];
        foreach ($verify_params as $k => $v) {
            if (in_array($k, [
                'success_url',
                //'token_url',
                //'user_token',
            ])) {
                continue;
            }
            $verify_url .= '&' . $k . '=' . $v;
        }

        // thiết lập thông tin người nhận
        $data_send = [
            'to' => $data['user_email'],
            'subject' => $this->firebaseLang('reverify_subject', 'Xac minh dia chi email'),
            'message' => HtmlTemplate::render(
                // mẫu HTML
                $this->base_model->get_html_tmp('firebase_verify_email'),
                [
                    // các tham số
                    'verify_url' => $verify_url,
                    'base_url' => base_url(),
                    'email' => $data['user_email'],
                    'ip' => $this->request->getIPAddress(),
                    'agent' => $_SERVER['HTTP_USER_AGENT'],
                    'date_send' => date('r'),
                ]
            ),
        ];

        // Nếu mail mới được gửi trong thời gian còn cache thì nhắc lại thông báo
        if ($this->user_model->the_cache($data['ID'], __FUNCTION__) !== NULL) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => $this->firebaseLang('check_email', 'Vui lòng kiểm tra email và kích hoạt tài khoản của bạn: ') . $data['user_email'],
            ]);
        }

        // Nếu gửi mail thành công
        if (PHPMaillerSend::the_send($data_send, $smtp_config) === true) {
            // không cho gửi mail liên tục
            $this->user_model->the_cache($data['ID'], __FUNCTION__, time(), MINI_CACHE_TIMEOUT);

            // cập nhật verify_key -> nếu người dùng bấm vào đây thì sẽ tiến hành cập nhật firebase_uid
            $this->user_model->update_member($data['ID'], [
                'user_activation_key' => $verify_params['verify_key'],
            ]);

            // báo người dùng check email
            $this->result_json_type([
                'code' => __LINE__,
                'error' => $this->firebaseLang('check_email', 'Vui lòng kiểm tra email và kích hoạt tài khoản của bạn: ') . $data['user_email'],
            ]);
        } else {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => $this->firebaseLang('error_email', 'Hệ thống email đang lỗi, vui lòng báo với quản trị viên'),
            ]);
        }
    }

    public function verify_email()
    {
        $this->firebaseUrlExpires($this->MY_get('expires_token'), $this->expires_reverify_time, $this->MY_get('uid'));
        //$this->result_json_type($_GET);

        //
        $user_id = $this->checkEmptyParams($this->MY_get('nse'), [
            'code' => __LINE__,
            'error' => $this->firebaseLang('verify_nse', 'nse trống'),
        ]);
        $verify_key = $this->checkEmptyParams($this->MY_get('verify_key'), [
            'code' => __LINE__,
            'error' => $this->firebaseLang('verify_key', 'verify_key trống'),
        ]);
        $uid = $this->checkEmptyParams($this->MY_get('uid'), [
            'code' => __LINE__,
            'error' => $this->firebaseLang('uid', 'user_id trống'),
        ]);

        // so khới với verify_key
        if ($this->base_model->mdnam($user_id . $uid) == $verify_key) {
            // khớp thông tin thì cập nhật lại firebase_uid
            $this->user_model->update_member($user_id, [
                //'user_status' => UsersType::FOR_DEFAULT,
                'firebase_uid' => $this->base_model->mdnam($uid),
                'user_activation_key' => '',
            ], [
                // where
                'user_activation_key' => $verify_key,
            ]);

            //
            $this->base_model->msg_session($this->firebaseLang('verify_complete', 'Kích hoạt lại tài khoản thành công! Cảm ơn bạn.'));
        } else {
            $this->base_model->msg_error_session($this->firebaseLang('verify_error_params', 'Thông số trên URL không hợp lệ!'));
        }

        // chuyển đến trang đăng nhập
        $this->MY_redirect(base_url('guest/login'));
    }

    // kiểm tra URL có hợp lệ hay không
    protected function firebaseUrlExpires($expires_token, $expires_time, $hash_code = '')
    {
        // bắt đầu kiểm tra
        if (!empty($expires_token)) {
            // cắt bỏ 3 ký tự cuối của expires_token
            $expires_token = substr($expires_token, 0, -3);
        }
        if (empty($expires_token) || time() - $expires_token > $expires_time) {
            // URL hết hạn
            $this->result_json_type([
                'code' => __LINE__,
                'reload' => __LINE__,
                'auto_logout' => __LINE__,
                'error' => $this->firebaseLang('expires_expires', 'expires_token đã hết hạn sử dụng. Vui lòng thử lại sau giây lát...'),
            ]);
        }
        // tạo hash code mặc định nếu chưa có
        if (empty($hash_code)) {
            $hash_code = session_id();
        }

        //
        $at = $this->myAccessToken();
        if (empty($at) || $this->base_model->mdnam($expires_token . $hash_code) != $at) {
            $this->result_json_type([
                'code' => __LINE__,
                'reload' => __LINE__,
                'auto_logout' => __LINE__,
                'error' => $this->firebaseLang('access_token', 'access_token không phù hợp'),
            ]);
        }

        //
        return $expires_token;
    }

    // kiểm tra xem token có đúng theo session không
    protected function firebaseSessionToken($expires_token, $ut)
    {
        if (empty($ut) || $this->base_model->mdnam($expires_token . session_id()) != $ut) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => $this->firebaseLang('user_token', 'user_token không phù hợp'),
            ]);
        }
    }

    protected function sign_in_token($uid)
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // lưu cache để đến phiên đăng nhập thì nạp lại -> không cho 1 phiên lưu quá lâu
            $this->id_cache_token($uid);

            //
            $this->result_json_type($_POST);
        }

        //
        $this->result_json_type([
            'code' => __LINE__,
            'error' => 'No money no love!'
        ]);
    }

    protected function id_cache_token($str = '')
    {
        if ($str != '') {
            // nếu có lệnh giữ lại session -> dùng cache -> session bị xóa khi bấm logout -> không giữ được
            if ($this->firebase_config->save_firebase_session == 'on') {
                return $this->base_model->scache(__FUNCTION__ . session_id(), $str, DAY);
            }
            return $this->base_model->MY_session(__FUNCTION__ . session_id(), $str);
        }
        if ($this->firebase_config->save_firebase_session == 'on') {
            return $this->base_model->scache(__FUNCTION__ . session_id());
        }
        return $this->base_model->MY_session(__FUNCTION__ . session_id());
    }

    public function firebase_config()
    {
        if (!empty($this->firebase_config->firebase_json_config)) {
            $this->result_json_type([
                'ok' => __LINE__,
                'data' => json_decode($this->firebase_config->firebase_json_config)
            ]);
        }

        //
        $this->result_json_type([
            'code' => __LINE__,
            'error' => 'No money no love!'
        ]);
    }

    // biên dịch mã jwt để so sánh giá trị truyền vào
    protected function phpJwt($jwt, $uid = '')
    {
        list($headersB64, $payloadB64, $sig) = explode('.', $jwt);
        //echo $headersB64 . PHP_EOL;
        //echo $payloadB64 . PHP_EOL;
        //echo $sig . PHP_EOL;

        //
        //$decoded = json_decode(base64_decode($headersB64), true);
        //print_r($decoded);

        //
        //$decoded = json_decode(base64_decode($sig), true);
        //print_r($decoded);

        //
        $payloadB64 = str_replace('-', '+', $payloadB64);
        $payloadB64 = str_replace('_', '/', $payloadB64);
        $decoded = json_decode(base64_decode($payloadB64), true);
        //print_r($decoded);

        //
        if (!is_array($decoded)) {
            $this->result_json_type([
                'code' => __LINE__,
                //'jwt' => $jwt,
                //'headersB64' => $headersB64,
                //'payloadB64' => $payloadB64,
                //'payloadB64' => base64_decode($payloadB64),
                //'sig' => $sig,
                //'decoded' => $decoded,
                'error' => $this->firebaseLang('decoded_array', 'Định dạng decoded không đúng'),
            ]);
        } else if (!isset($decoded['user_id'])) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => $this->firebaseLang('user_id_isset', 'Không xác định được user_id'),
            ]);
        }

        //
        //echo date('Y-m-d H:i:s', $decoded['exp']) . PHP_EOL;
        //echo date('Y-m-d H:i:s', $decoded['auth_time']) . PHP_EOL;

        // nếu có uid -> so khớp
        if ($uid != '') {
            if ($uid != $decoded['user_id']) {
                $this->result_json_type([
                    'code' => __LINE__,
                    'error' => $this->firebaseLang('user_id_uid', 'uid không hợp lệ'),
                ]);
            }
        }

        //
        return $decoded;
    }
}
