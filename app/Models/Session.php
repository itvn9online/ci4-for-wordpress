<?php
/*
 * file này chủ yếu xử lý các vấn đề liên quan đến session
 */

namespace App\Models;

// Libraries
//use CodeIgniter\Model;

class Session
{
    // key dùng lưu session cho các phiên kiểm tra csrf
    private $key_csrf_hash = '_wgr_csrf_hash';
    // key lưu phiên đăng nhập của khách
    private $key_member_login = '_wgr_logged';
    // khi số lần đăng nhập sai vượt qua con số này thì sẽ kích hoạt captcha
    private $max_login_faild = 3;

    public $cache = NULL;

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

    /*
     * Kiểm tra đầu vào của dữ liệu xem chuẩn không
     */
    public function check_csrf()
    {
        $csrf_name = csrf_token();
        //echo $csrf_name . '<br>' . PHP_EOL;
        // nếu tồn tại hash
        if (isset($_REQUEST[$csrf_name])) {
            $hash = $this->MY_session($this->key_csrf_hash);
            // -> kiểm tra khớp dữ liệu
            if ($hash != '' && $_REQUEST[$csrf_name] != $hash) {
                print_r($_SESSION);
                die(json_encode([
                    'code' => __LINE__,
                    'in' => $_REQUEST[$csrf_name],
                    'out' => $this->MY_session($this->key_csrf_hash),
                    'error' => 'CSRF Invalid token from your request!'
                ]));
            }
        }
        //die( __CLASS__ . ':' . __LINE__ );

        //
        return true;
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
