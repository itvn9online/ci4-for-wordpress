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

    // danh sách name và type của các input dùng để tạo anti spam
    protected $input_anti_spam = [
        'email' => 'email',
        'phone' => 'text',
        'fname' => 'text',
        'lname' => 'text',
        'address' => 'text',
        'captcha' => 'text',
    ];
    // độ dài của chuỗi ngẫu nhiên tạo ra bởi session id
    public $rand_len_code = 6;

    public function __construct()
    {
        $this->cache = \Config\Services::cache();
    }

    public function MY_session($key, $value = NULL)
    {
        if ($value !== NULL) {
            $_SESSION[$key] = $value;
            return true;
        }
        return isset($_SESSION[$key]) ? $_SESSION[$key] : '';
    }

    // trả về input chứa csrf và lưu vào session để nếu submit thì còn kiểm tra được
    public function csrf_field($anti_spam = true, $time_expired = ANTI_SPAM_EXPIRED)
    {
        // mỗi phiên -> lưu lại csrf token dưới dạng session
        $this->MY_session($this->key_csrf_hash, csrf_hash());

        // in html
        echo csrf_field();

        // nạp thêm input ẩn -> chống spam
        if ($anti_spam === true) {
            $this->anti_spam_field($time_expired);
        }

        //
        return true;
    }

    /**
     * trả về input chứa các hidden input để sau đó sẽ check các input này và đánh giá có phải bot hay ko
     * time_expired -> thêm thời gian hết hạn cho hide-captcha -> mặc định 5m -> trường hợp nào cần lâu hơn thì truyền vào theo tham số
     * hide_captcha: khi bật chế độ này, input chỉ định trả về alert sẽ không được in ra -> lệnh sẽ trả về mã json
     **/
    public function anti_spam_field($time_expired = ANTI_SPAM_EXPIRED, $hide_captcha = 0)
    {
        include VIEWS_PATH . 'includes/anti_spam.php';
        return true;
    }

    /**
     * Trả về hidden input không bao gồm input alert -> dùng cho các lệnh js có sử dụng hide-captcha
     **/
    public function hide_captcha_field($time_expired = ANTI_SPAM_EXPIRED)
    {
        return $this->anti_spam_field($time_expired, 1);
    }

    /**
     * Trả về đoạn mã HTML chứa DIV và class css, sau đó mã captcha sẽ được nạp qua ajax và push vào đấy
     * Đoạn này thường dùng cho các page có cache -> ko thể include trực tiếp captcha vào cache được do khác session
     **/
    public function anti_spam_ajax($user_id, $time_expired = ANTI_SPAM_EXPIRED)
    {
        // nếu user đang đăng nhập thì trả thẳng mã captcha luôn -> do ko bị cache
        if ($user_id > 0) {
            return $this->anti_spam_field($time_expired);
        }

        // chưa đăng nhập thì có cache nên sẽ trả về đoạn html, cache sau đó sẽ nạp qua ajax
        echo '<div class="ebe-recaptcha"></div>';
        return true;
    }

    /**
     * Trả về đoạn mã HTML chứa DIV và class css, sau đó mã captcha sẽ được nạp qua ajax và push vào đấy
     * Đoạn này thường dùng cho các page có cache -> ko thể include trực tiếp captcha vào cache được do khác session
     * Trả về hidden input không bao gồm input alert -> dùng cho các lệnh js có sử dụng hide-captcha
     **/
    public function hide_captcha_ajax($user_id, $time_expired = ANTI_SPAM_EXPIRED)
    {
        // nếu user đang đăng nhập thì trả thẳng mã captcha luôn -> do ko bị cache
        if ($user_id > 0) {
            return $this->anti_spam_field($time_expired, 1);
        }

        // chưa đăng nhập thì có cache nên sẽ trả về đoạn html, cache sau đó sẽ nạp qua ajax
        echo '<div class="ebe-rehidecaptcha"></div>';
        return true;
    }

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

        // chạy 1 vòng -> kiểm tra các input của anti spam có tồn tại không
        $has_value = 0;
        $no_value = 0;
        $i = 0;
        $this_spam = false;
        foreach ($this->input_anti_spam as $k => $v) {
            $k = RAND_ANTI_SPAM . '_' . $k;
            // nếu tồn tại request này
            if (isset($_REQUEST[$k])) {
                // nếu có dữ liệu
                if (!empty($_REQUEST[$k])) {
                    // đánh dấu có dữ liệu
                    $has_value++;
                    // so khớp dữ liệu nếu khác nhau -> báo lỗi
                    if (md5($k) != explode('@', $_REQUEST[$k])[0]) {
                        //echo $k . '<br>' . PHP_EOL;
                        $this_spam = $k;
                        break;
                    }
                } else {
                    // không có thì đánh dấu ko có
                    $no_value++;
                }
                $i++;
            }
        }

        // kiểm tra thời gian hết hạn của token -> nếu có
        $by_token = 0;
        $time_out = isset($_POST[RAND_ANTI_SPAM . '_to']) ? $_POST[RAND_ANTI_SPAM . '_to'] : 0;
        $time_token = isset($_POST[RAND_ANTI_SPAM . '_token']) ? $_POST[RAND_ANTI_SPAM . '_token'] : '';
        // nếu có thời gian hết hạn hoặc có token thì mới kiểm tra
        if (!empty($time_out) && !empty($time_token)) {
            if (!is_numeric($time_out) || $time_out < time() || md5(RAND_ANTI_SPAM . $time_out) != $time_token) {
                $by_token = 1;
            }
        }

        // kiểm tra mã session có khớp không
        $by_code = 0;
        $rand_code = isset($_POST[RAND_ANTI_SPAM . '_code']) ? $_POST[RAND_ANTI_SPAM . '_code'] : '';
        if (!empty($rand_code)) {
            if (strlen($rand_code) != $this->rand_len_code || strpos(session_id(), $rand_code) === false) {
                $by_code = 1;
            }
        }

        //
        return $this->afterCheckSpam([
            'by_token' => $by_token,
            'by_code' => $by_code,
            'has_value' => $has_value,
            'no_value' => $no_value,
            'i' => $i,
            'this_spam' => $this_spam,
            'f' => __FUNCTION__,
        ]);
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
        foreach ($this->input_anti_spam as $k => $v) {
            $k = RAND_ANTI_SPAM . '_' . $k;
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
                    break;
                }
            } else {
                // không có thì đánh dấu ko có
                $no_value++;
            }
            $i++;
        }

        // kiểm tra thời gian hết hạn của token -> bắt buộc
        $by_token = 0;
        $time_out = isset($_POST[RAND_ANTI_SPAM . '_to']) ? $_POST[RAND_ANTI_SPAM . '_to'] : 0;
        $time_token = isset($_POST[RAND_ANTI_SPAM . '_token']) ? $_POST[RAND_ANTI_SPAM . '_token'] : '';
        // nếu không có thời gian hết hạn hoặc hết hạn hoặc token không khớp -> lỗi
        if (empty($time_out) || !is_numeric($time_out) || $time_out < time() || empty($time_token) || md5(RAND_ANTI_SPAM . $time_out) != $time_token) {
            $by_token = 1;
        }

        // kiểm tra mã session có khớp không
        $by_code = 0;
        $rand_code = isset($_POST[RAND_ANTI_SPAM . '_code']) ? $_POST[RAND_ANTI_SPAM . '_code'] : '';
        if (empty($rand_code) || strlen($rand_code) != $this->rand_len_code || strpos(session_id(), $rand_code) === false) {
            $by_code = 1;
        }

        //
        return $this->afterCheckSpam([
            'by_token' => $by_token,
            'by_code' => $by_code,
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
                'token' => $ops['by_token'],
                'f' => strtolower($ops['f']),
            ]);
        }

        // lỗi khớp session id -> báo lỗi luôn
        if ($ops['by_code'] > 0) {
            $this->dieIfSpam([
                'code' => __LINE__,
                'sid' => $ops['by_code'],
                'f' => strtolower($ops['f']),
            ]);
        }

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
                'has' => $ops['has_value'],
                'no' => $ops['no_value'],
                'f' => strtolower($ops['f']),
            ]);
        }
        // không có thì thôi, đã có thì tổng số input nhận được phải bằng tổng số input được khai báo
        else if ($ops['i'] > 0 && $ops['i'] != $count_anti) {
            $this->dieIfSpam([
                'code' => __LINE__,
                'count' => $ops['i'],
                'f' => strtolower($ops['f']),
            ]);
        }
        // Bị gắn cờ spam
        else if ($ops['this_spam'] !== false) {
            $this->dieIfSpam([
                'code' => __LINE__,
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
        // cố định thông điệp báo lỗi
        $d['error'] = 'Anti-spam activated in your request!';

        // nếu là từ view -> alert cho người dùng biết
        if (isset($_POST[RAND_ANTI_SPAM . '_alert'])) {
            $this->alert($d['error'] . ' (' . $d['code'] . ')', 'error');
        }

        // còn lại sẽ trả về mã json
        header('Content-Type:text/plain; charset=UTF-8');
        //header('Content-type: application/json; charset=utf-8');
        die(json_encode($d));
    }

    // -> trả về alert của javascript
    public function alert($m, $lnk = '')
    {
        $arr_debug = debug_backtrace();
        //print_r($arr_debug);

        //
        die(HtmlTemplate::html(
            'wgr_alert.html',
            [
                'file' => basename($arr_debug[1]['file']),
                'line' => $arr_debug[1]['line'],
                'function' => $arr_debug[1]['function'],
                'class' => basename(str_replace('\\', '/', $arr_debug[1]['class'])),
                'm' => $m,
                'lnk' => $lnk,
            ]
        ));
    }

    // set session login -> lưu phiên đăng nhập của người dùng
    public function set_ses_login($data, $save_data = [])
    {
        foreach ($data as $k => $v) {
            $save_data[$k] = $v;
        }
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
}
