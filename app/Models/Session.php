<?php
/*
 * file này chủ yếu xử lý các vấn đề liên quan đến session
 */

namespace App\Models;

// Libraries
//use CodeIgniter\Model;
use App\Helpers\HtmlTemplate;
//use App\Libraries\ConfigType;

class Session
{
    // key dùng lưu session cho các phiên kiểm tra csrf
    private $key_csrf_hash = '_wgr_csrf_hash';
    // key lưu phiên đăng nhập của khách
    private $key_member_login = '_wgr_logged';
    // khi số lần đăng nhập sai vượt qua con số này thì sẽ kích hoạt captcha
    private $max_login_faild = 3;

    public $cache = NULL;
    public $session = NULL;
    public $rand_anti_spam = '';

    // danh sách name và type của các input dùng để tạo anti spam
    protected $input_anti_spam = [
        // tránh các tên input phổ biến như email, phone -> ví dễ dính quả tự động điền của trình duyệt nên đứt
        'dfjk4dffd' => 'text',
        'nvbsi4fdgh' => 'text',
        'hgj4dfhdf' => 'text',
        'xcgdfdty' => 'text',
        'ghszdvsdfg' => 'text',
        'captcha' => 'text',
    ];
    // độ dài của chuỗi ngẫu nhiên tạo ra bởi session id
    public $rand_len_code = 6;

    public function __construct()
    {
        // var_dump(session_id());
        $this->cache = \Config\Services::cache();
        $this->session = \Config\Services::session();
        // var_dump(session_id());
        // var_dump(debug_backtrace()[1]['class']);
        // var_dump(debug_backtrace()[1]['function']);

        //
        $this->rand_anti_spam = '_' . substr(md5(session_id()), 0, 12);
    }

    /**
     * https://codeigniter.com/user_guide/libraries/sessions.html
     */
    public function MY_session($key, $value = NULL)
    {
        if ($value !== null) {
            // $_SESSION[$key] = $value;
            $this->session->set($key, $value);
            return true;
        }
        // return isset($_SESSION[$key]) ? $_SESSION[$key] : '';
        return $this->session->get($key);
    }

    /**
     * Trả về session id do kiểu session của ci4 không dùng trực tiếp hàm session_id() được
     **/
    public function MY_sessid()
    {
        // return session_id();

        //
        $my_ssid = $this->session->get('mysessid');
        if (empty($my_ssid)) {
            $my_ssid = session_id();
            // $my_ssid = file_get_contents(DYNAMIC_BASE_URL . 'session_id.php');
            $this->session->set('mysessid', $my_ssid);
        }
        return $my_ssid;
    }

    // trả về input chứa csrf và lưu vào session để nếu submit thì còn kiểm tra được
    public function csrf_field()
    {
        // mỗi phiên -> lưu lại csrf token dưới dạng session
        $this->MY_session($this->key_csrf_hash, csrf_hash());

        // in html
        echo csrf_field();

        //
        return true;
    }

    /**
     * trả về input chứa các hidden input để sau đó sẽ check các input này và đánh giá có phải bot hay ko
     * time_expired -> thêm thời gian hết hạn cho hide-captcha -> mặc định 5m -> trường hợp nào cần lâu hơn thì truyền vào theo tham số
     * hide_captcha: khi bật chế độ này, input chỉ định trả về alert sẽ không được in ra -> lệnh sẽ trả về mã json
     **/
    public function anti_spam_field($ops = [])
    {
        // thời gian hết hạn mặc định
        if (!isset($ops['time_expired'])) {
            $ops['time_expired'] = ANTI_SPAM_EXPIRED;
        }
        // khi có tham số này -> input sẽ được in ra thay vì chờ nạp ajax (thường chỉ dùng cho popup, còn lại nạp qua ajax hết)
        if (!isset($ops['show_now'])) {
            $ops['show_now'] = 0;
        }

        //
        if ($ops['show_now'] > 0) {
            include VIEWS_PATH . 'includes/anti_spam.php';
        } else {
            // trả về đoạn html, cache sau đó sẽ nạp qua ajax
            echo '<div class="ebe-recaptcha d-none"></div>';
        }
        return true;
    }

    /**
     * Trả về input nhưng fill sẵn dữ liệu cho input fjs -> dùng cho popup
     **/
    public function anti_spam_popup($ops = [])
    {
        $ops['show_now'] = 1;
        return $this->anti_spam_field($ops);
    }
    // các function bỏ
    // hide_captcha_field
    // anti_spam_ajax
    // hide_captcha_ajax

    /**
     * Kiểm tra đầu vào của dữ liệu xem chuẩn không
     **/
    public function check_csrf()
    {
        // bỏ qua phương thức get
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            return true;
        }

        $csrf_name = csrf_token();
        //echo $csrf_name . '<br>' . PHP_EOL;
        // nếu tồn tại hash
        if (isset($_REQUEST[$csrf_name])) {
            $hash = $this->MY_session($this->key_csrf_hash);
            // -> kiểm tra khớp dữ liệu
            if ($hash != '' && $_REQUEST[$csrf_name] != $hash) {
                //print_r($_SESSION);
                die(json_encode([
                    'code' => __LINE__,
                    'in' => $_REQUEST[$csrf_name],
                    'out' => $this->MY_session($this->key_csrf_hash),
                    'error' => 'CSRF Invalid token from your request!'
                ]));
            }
        }
        //die( __CLASS__ . ':' . __LINE__ );
    }

    /**
     * Một số phương thức cần dộ bảo mật cao thì sẽ bắt buộc check spam -> nghĩa là các input được định nghĩa bắt buộc phải có
     **/
    public function antiRequiredSpam()
    {
        // không phải POST -> bỏ
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return false;
        }
        //print_r($_POST);

        // chạy 1 vòng -> kiểm tra các input của anti spam có tồn tại không
        $has_value = 0;
        $no_value = 0;
        $i = 0;
        $this_spam = false;
        $msg = '';
        foreach ($this->input_anti_spam as $k => $v) {
            $k = $this->rand_anti_spam . '_' . $k;
            // không tồn tại 1 input -> bỏ luôn
            if (!isset($_REQUEST[$k])) {
                break;
            }

            // nếu có dữ liệu
            if (!empty($_REQUEST[$k])) {
                // đánh dấu có dữ liệu
                $has_value++;
                // so khớp dữ liệu nếu khác nhau -> báo lỗi
                if (md5($k) != explode('@', $_REQUEST[$k])[0]) {
                    //echo $k . '<br>' . PHP_EOL;
                    $this_spam = $k;
                    $msg = 'require field ' . $k;
                    // $msg = md5($k) . ' !=  ' . explode('@', $_REQUEST[$k])[0];
                    break;
                }
            } else {
                // không có thì đánh dấu ko có
                $no_value++;
            }
            $i++;
        }

        // các tham số chỉ cần sai 1 cái là bỏ qua kiểm tra cái sau luôn
        $by_token = 0;
        $by_code = 0;
        // $by_jsf = 0;

        // kiểm tra thời gian hết hạn của token -> bắt buộc
        $time_out = isset($_POST[$this->rand_anti_spam . '_to']) ? $_POST[$this->rand_anti_spam . '_to'] : 0;
        $time_token = isset($_POST[$this->rand_anti_spam . '_token']) ? $_POST[$this->rand_anti_spam . '_token'] : '';
        // nếu không có thời gian hết hạn hoặc hết hạn hoặc token không khớp -> lỗi
        if (empty($time_out) || empty($time_token)) {
            $by_token = 1;
            $msg = 'token EMPTY';
        } else if (!is_numeric($time_out) || $time_out < time()) {
            $by_token = 1;
            $msg = 'request timeout';
        } else if (md5($this->rand_anti_spam . $time_out) != $time_token) {
            $by_token = 1;
            $msg = 'token mismatch';
        } else {
            // kiểm tra mã session có khớp không
            $rand_code = isset($_POST[$this->rand_anti_spam . '_code']) ? $_POST[$this->rand_anti_spam . '_code'] : '';
            if (empty($rand_code)) {
                $by_code = 1;
                $msg = 'captcha EMPTY';
            } else if (strlen($rand_code) != $this->rand_len_code || strpos($this->MY_sessid(), $rand_code) === false) {
                // var_dump($this->MY_sessid());
                // var_dump($rand_code);
                $msg = 'captcha mismatch';
            }
        }

        //
        return $this->afterCheckSpam([
            'msg' => $msg,
            'by_token' => $by_token,
            'by_code' => $by_code,
            // 'by_jsf' => $by_jsf,
            // gán giá trị mặc định nếu ko tìm thấy -> vì những cái này bắt buộc phải có
            'has_value' => $has_value > 0 ? $has_value : __LINE__,
            'no_value' => $no_value > 0 ? $no_value : __LINE__,
            'i' => $i > 0 ? $i : __LINE__,
            'this_spam' => $this_spam,
            'f' => __FUNCTION__,
        ]);
    }

    /**
     * Sau khi check spam thì in ra kết quả nếu phát hiện có vấn đề
     **/
    protected function afterCheckSpam($ops)
    {
        //
        if (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === false) {
            return $this->dieIfSpam([
                'code' => __LINE__,
                'msg' => $ops['msg'],
                'rq' => 'Bad request!',
                'f' => strtolower($ops['f']),
                // 'context' => $ops['context'],
            ]);
        }

        // tổng số input
        $count_anti = count($this->input_anti_spam);
        //echo $count_anti . '<br>' . PHP_EOL;
        //print_r($ops);
        //echo $ops['has_value'] . '<br>' . PHP_EOL;
        //echo $ops['no_value'] . '<br>' . PHP_EOL;
        //echo $ops['i'] . '<br>' . PHP_EOL;
        //echo $ops['this_spam'] . '<br>' . PHP_EOL;

        // lỗi khớp token -> báo lỗi luôn
        if ($ops['by_token'] > 0) {
            $this->dieIfSpam([
                'code' => __LINE__,
                'msg' => $ops['msg'],
                'token' => $ops['by_token'],
                'f' => strtolower($ops['f']),
            ]);
        }
        // lỗi khớp session id -> báo lỗi luôn
        else if ($ops['by_code'] > 0) {
            $this->dieIfSpam([
                'code' => __LINE__,
                'msg' => $ops['msg'],
                'sid' => $ops['by_code'],
                'f' => strtolower($ops['f']),
            ]);
        }
        // lỗi khớp js-fill -> javascript không hoạt động hoặc push dữ liệu không khớp -> báo lỗi luôn
        // else if ($ops['by_jsf'] > 0) {
        //     $this->dieIfSpam([
        //         'code' => __LINE__,
        // 'msg' => $ops['msg'],
        //         'jsf' => $ops['by_jsf'],
        //         'f' => strtolower($ops['f']),
        //     ]);
        // }

        //
        $ops['has_value'] *= 1;
        $ops['no_value'] *= 1;
        $by_value = false;
        // chỉ kiểm tra theo giá trị khi có ghi nhận
        if ($ops['has_value'] > 0 || $ops['no_value'] > 0) {
            // Nếu chỉ duy nhất 1 input được phép chứa dữ liệu, còn lại không được chứa dữ liệu
            if ($ops['has_value'] !== 1 || $ops['no_value'] !== ($count_anti - 1)) {
                $by_value = true;
            }
        }

        // ải giá trị không qua -> cho đứt
        if ($by_value !== false) {
            $this->dieIfSpam([
                'code' => __LINE__,
                'msg' => $ops['msg'],
                'has' => $ops['has_value'],
                'no' => $ops['no_value'],
                'f' => strtolower($ops['f']),
            ]);
        }
        // không có thì thôi, đã có thì tổng số input nhận được phải bằng tổng số input được khai báo
        else if ($ops['i'] > 0 && $ops['i'] != $count_anti) {
            $this->dieIfSpam([
                'code' => __LINE__,
                'msg' => $ops['msg'],
                'count' => $ops['i'],
                // 'error' => '',
                'f' => strtolower($ops['f']),
            ]);
        }
        // Bị gắn cờ spam
        else if ($ops['this_spam'] !== false) {
            $this->dieIfSpam([
                'code' => __LINE__,
                'msg' => $ops['msg'],
                'spamer' => $ops['this_spam'],
                'f' => strtolower($ops['f']),
            ]);
        }
        //die(__CLASS__ . ':' . __LINE__);

        //
        return true;
    }

    /**
     * dừng lại mọi hoạt động nếu phát hiện spam
     **/
    protected function dieIfSpam($d)
    {
        print_r($d);
        // cố định thông điệp báo lỗi
        if (!isset($d['error']) || empty($d['error'])) {
            // $d['error'] = 'Anti-spam activated in your request! Please reload this page and try again.';
            if (!isset($d['msg']) || empty($d['msg'])) {
                $d['msg'] = 'spamer detected';
            }
            $d['error'] = '(Anti-spam) your request blocked by: ' . $d['msg'] . '! Please reload this page and try again.';
        }

        // nếu là truyền qua ajax -> trả về json
        if (isset($_POST['doing_ajax'])) {
            header('Content-Type: text/plain; charset=UTF-8');
            //header('Content-type: application/json; charset=utf-8');
            die(json_encode($d));
        }

        // còn lại sẽ alert cho người dùng biết
        $this->alert($d['error'] . ' (' . $d['code'] . ')', 'error');
    }

    // -> trả về alert của javascript
    public function alert($m, $lnk = '')
    {
        if (ENVIRONMENT !== 'production') {
            $arr_debug = debug_backtrace();
            //print_r($arr_debug);

            //
            $alert_data = [
                'file' => basename($arr_debug[1]['file']),
                'line' => $arr_debug[1]['line'],
                'function' => $arr_debug[1]['function'],
                'class' => basename(str_replace('\\', '/', $arr_debug[1]['class'])),
            ];
        } else {
            $alert_data = [
                'file' => '',
                'line' => '',
                'function' => '',
                'class' => '',
            ];
        }
        $alert_data['m'] = $m;
        $alert_data['lnk'] = $lnk;

        //
        die(HtmlTemplate::html('wgr_alert.html', $alert_data));
    }

    // set session login -> lưu phiên đăng nhập của người dùng
    public function set_ses_login($data, $save_data = [])
    {
        foreach ($data as $k => $v) {
            $save_data[$k] = $v;
        }

        // một số dữ liệu không lưu vào session
        $save_data['user_pass'] = '';
        $save_data['ci_pass'] = '';
        $save_data['rememberme_key'] = '';
        //$save_data['user_activation_key'] = '';

        // dữ liệu tùy chỉnh (khai báo trong functions.php của theme)
        foreach (DENY_IN_LOGGED_SES as $v) {
            $save_data[$v] = '';
        }

        //
        return $this->MY_session($this->key_member_login, $save_data);
    }
    // get session login -> trả về dữ liệu đăng nhập của người dùng
    public function get_ses_login()
    {
        return $this->MY_session($this->key_member_login);
    }

    /*
     * Chức năng captcha khi đăng nhập sai nhiều lần
     */
    // lấy tổng số lần đăng nhập sai
    public function get_faild_login()
    {
        $a = $this->MY_session('count_faild_login');
        if ($a == '') {
            $a = 0;
        } else {
            $a *= 1;
        }

        //
        return $a;
    }
    // thêm số lần đăng nhập sai -> mỗi lần đăng nhập sai thì thêm 1 đơn vị
    public function push_faild_login()
    {
        $this->MY_session('count_faild_login', $this->get_faild_login() + 1);
    }
    // reset lại số lần nhập sai pass nếu trước đó đã nhập đúng captcha
    public function reset_faild_login()
    {
        if ($this->MY_session('count_faild_login') != '') {
            $this->MY_session('count_faild_login', '');
        }
    }
    // kiểm tra nếu vượt số lần đăng nhập sai thì trả về 1 số > 0
    public function check_faild_login()
    {
        if ($this->get_faild_login() >= $this->max_login_faild) {
            return 1;
        }
        return 0;
    }

    public function mdnam($str, $hash = '')
    {
        $str .= $hash;
        $str = md5($str);
        $str = substr($str, 2, 6);
        return md5($str);
    }

    public function mdhash($str)
    {
        return $this->mdnam($str, CUSTOM_MD5_HASH_CODE);
    }

    // cache bên model là cache select database -> chỉ kiểm tra theo key truyền vào -> không kiểm tra theo session login
    public function scache($key, $value = '', $time = MINI_CACHE_TIMEOUT)
    {
        if (MY_CACHE_HANDLER == 'disable') {
            return NULL;
        }

        // lưu cache nếu có nội dung
        if ($value != '') {
            return $this->cache->save($key, $value, $time);
        }

        // trả về cache nếu có
        return $this->cache->get($key);
    }

    /*
     * delete cache
     * chức năng xóa cache theo key truyền vào
     * clean_all: một số phương thức không áp dụng được kiểu xóa theo key -> admin có thể xóa all
     */
    public function dcache($for = '', $clean_all = false)
    {
        // disable -> không có cache để mà xóa
        if (MY_CACHE_HANDLER == 'disable') {
            return false;
        }

        // lưu có key -> xóa theo key truyền vào
        if ($for != '') {
            // 1 số phương thức không áp dụng được kiểu xóa này do không có key 
            if (
                in_array(MY_CACHE_HANDLER, [
                    'memcached',
                    'wincache',
                ])
            ) {
                // có thể cân đối giữa việc XÓA toàn bộ hoặc return false luôn
                if ($clean_all !== true) {
                    return NULL;
                }
            } else {
                return $this->cache->deleteMatching('*' . $for . '*');
            }
        }

        // nếu cache và session sử dụng chung 1 drive thì không cho xóa toàn bộ
        if (MY_CACHE_HANDLER == 'redis') {
            if (MY_SESSION_DRIVE == 'RedisHandler') {
                return NULL;
            }
        } else if (MY_CACHE_HANDLER == 'memcached') {
            if (MY_SESSION_DRIVE == 'MemcachedHandler') {
                return NULL;
            }
        }

        // mặc định là xóa hết
        return $this->cache->clean();
    }

    // chạy vòng lặp gán nốt các thông số khác trên url vào phân trang
    public function auto_add_params($uri)
    {
        foreach ($_GET as $k => $v) {
            // tham số phân trang thì bỏ qua -> sẽ được add lại trong hàm phân trang
            if ($k == 'page_num') {
                continue;
            }
            if (strpos($uri, $k . '=') === false) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        $uri .= '&' . $k . '[' . $k2 . ']=' . $v2;
                    }
                } else {
                    $uri .= '&' . $k . '=' . $v;
                }
            }
        }
        return $uri;
    }

    /**
     * kiểm tra tính hợp lệ của 1 post request
     * 1 số trường hợp chỉ chấp nhận method post và HTTP_REFERER cũng phải trong whitelist mới có thể submit
     **/
    public function checkPostReferer($line, $check_referer = true, $whitelist = [])
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return [
                'code' => __LINE__ . ':' . $line,
                'error' => 'Bad request!',
            ];
        }

        //
        if ($check_referer === true) {
            if (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === false) {
                return [
                    'code' => __LINE__ . ':' . $line,
                    'error' => 'Blocked request!',
                ];
            }
        }

        //
        return true;
    }
}
