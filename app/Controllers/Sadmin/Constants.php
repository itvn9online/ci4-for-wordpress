<?php

namespace App\Controllers\Sadmin;

// Libraries
use App\Libraries\ConfigType;
//use App\Libraries\LanguageCost;

//
class Constants extends Configs
{
    protected $config_type = ConfigType::CONSTANTS;
    protected $view_edit = 'constants';

    public function __construct()
    {
        parent::__construct();

        // chức năng này sẽ cố định cho 1 ngôn ngữ thôi
        //$this->lang_key = LanguageCost::default_lang();
        $this->lang_key = SITE_LANGUAGE_DEFAULT;
    }

    public function update_constants()
    {
        //
        //print_r($_POST);

        //
        $data = $this->MY_post('data');
        //print_r($data);
        if (empty($data)) {
            $this->base_model->alert('data EMPTY');
        }

        // chạy vòng lặp và add constants vào file tĩnh
        $meta_default = ConfigType::meta_default($this->config_type);
        //print_r($meta_default);

        // mảng các giá trị sẽ được chuyển đổi thành true|false nếu đầu vào là 1|0
        $arr_true_false = [
            'ALLOW_USING_MYSQL_DELETE',
            'WGR_CSP_ENABLE',
            'SITE_LANGUAGE_SUB_FOLDER',
            'ENABLE_AMP_VERSION',
        ];

        // mảng các giá trị sẽ bỏ dấu / ở đầu để tránh xung đột -> trim directory separator
        $arr_trim_ds = [
            'WGR_CATEGORY_PERMALINK',
            'WGR_PRODS_PERMALINK',
            'WGR_TAXONOMY_PERMALINK',
            //
            'WGR_POST_PERMALINK',
            'WGR_PROD_PERMALINK',
            'WGR_PAGE_PERMALINK',
            'WGR_POSTS_PERMALINK',
        ];

        //
        $a = [];
        foreach ($data as $k => $v) {
            if ($v == '') {
                continue;
            } else if (!isset($meta_default[$k])) {
                echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
                echo $k . ' not found from meta_default!' . '<br>' . PHP_EOL;
                continue;
            }

            //
            if ($v == 'IS_EMPTY') {
                //$a[] = "defined('$k') || define('$k', '');";
                $a[] = "define('$k', '');";
            } else if (in_array($k, $arr_true_false)) {
                if ($v > 0) {
                    $v = 'true';
                } else {
                    $v = 'false';
                }
                //$a[] = "defined('$k') || define('$k', $v);";
                $a[] = "define('$k', $v);";
            } else {
                // với session driver sẽ config riêng
                if ($k == 'MY_SESSION_DRIVE') {
                    if ($v == 'FileHandler') {
                        // mặc định là sử dụng file -> không cần khai báo thêm
                    } else if ($v == 'RedisHandler') {
                        if (empty(phpversion('redis')) || $this->checkRedis() !== true) {
                            echo 'redis not found! Code #' . __LINE__ . ' <br>' . PHP_EOL;
                            continue;
                        } else {
                            // nếu không lỗi lầm gì thì thiết lập redis
                            $a[] = "define('CUSTOM_SESSION_PATH', 'tcp://localhost:" . WGR_REDIS_PORT . "');";
                        }
                    } else if ($v == 'MemcachedHandler') {
                        if (!class_exists('Memcached') || $this->checkMemcached() !== true) {
                            echo 'Memcached not found! Code #' . __LINE__ . ' <br>' . PHP_EOL;
                            continue;
                        } else {
                            // nếu không lỗi lầm gì thì thiết lập Memcached
                            $a[] = "define('CUSTOM_SESSION_PATH', 'localhost:" . WGR_MEMCACHED_PORT . "');";
                        }
                    } else if ($v == 'DatabaseHandler') {
                        $a[] = "define('CUSTOM_SESSION_PATH', 'ci_sessions');";
                    } else {
                        // không nằm trong danh sách kia thì loại bỏ luôn
                        continue;
                    }
                }
                // 1 số trường hợp sẽ thêm lệnh kiểm tra vào trước tham số -> để tránh lỗi nếu trong quá trình hoạt động có sự thay đổi
                else if ($k == 'MY_CACHE_HANDLER') {
                    if ($v == 'redis') {
                        if (empty(phpversion('redis')) || $this->checkRedis() !== true) {
                            echo 'redis not found! Code #' . __LINE__ . ' <br>' . PHP_EOL;
                            continue;
                        }
                    } else if ($v == 'memcached') {
                        if (!class_exists('Memcached') || $this->checkMemcached() !== true) {
                            echo 'Memcached not found! Code #' . __LINE__ . ' <br>' . PHP_EOL;
                            continue;
                        }
                    }
                }
                // với phần ngôn ngữ cũng sẽ thiết lập riêng
                else if ($k == 'SITE_LANGUAGE_SUPPORT') {
                    // echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
                    // echo $v . '<br>' . PHP_EOL;

                    //
                    $arr = explode(',', $v);
                    // print_r($arr);

                    // Tạo 1 mảng để khai báo dữ liệu thành từng dòng -> tránh lỗi nếu có
                    $a_fixed_lang = [];

                    // BEGIN array
                    $a_fixed_lang[] = '[';

                    // chạy 1 vòng lấy các ngôn ngữ khả dụng trong config
                    $i_lang_fixed = 0;
                    foreach (SITE_LANGUAGE_FIXED as $langs_fixed) {
                        if (!in_array($langs_fixed['value'], $arr)) {
                            continue;
                        }
                        // print_r($langs_fixed);

                        // 
                        $a_fixed_lang[] = '[';
                        foreach ($langs_fixed as $k_lang_fixed => $v_lang_fixed) {
                            $a_fixed_lang[] = '\'' . $k_lang_fixed . '\' => \'' . $v_lang_fixed . '\',';
                        }
                        $a_fixed_lang[] = '],';

                        //
                        $i_lang_fixed++;
                    }
                    // END array
                    $a_fixed_lang[] = ']';
                    // print_r($a_fixed_lang);

                    // loại bỏ các ký tự thừa
                    $a_fixed_lang = str_replace(',]', ']', implode('', $a_fixed_lang));
                    // print_r($a_fixed_lang);
                    // die(__CLASS__ . ':' . __LINE__);

                    // gán tham số riêng cho Constants
                    $a[] = "define('$k', $a_fixed_lang);";

                    // 
                    // print_r($a);
                    // die(__CLASS__ . ':' . __LINE__);

                    // 
                    continue;
                }
                // với phần ngôn ngữ mặc định
                else if ($k == 'SITE_LANGUAGE_DEFAULT') {
                    $site_language_fixed = $this->MY_post('site_language_fixed');
                    // print_r($site_language_fixed);
                    // echo $v . '<br>' . PHP_EOL;

                    // nếu có thiết lập ngôn ngữ hiển thị thì ngôn ngữ mặc định phải là 1 trong các ngôn ngữ đã chọn
                    if (!empty($site_language_fixed)) {
                        // nếu ko có -> bỏ qua thiết lập ngôn ngữ mặc định
                        if (!in_array($v, $site_language_fixed)) {
                            continue;
                        }
                    }
                    // nếu không thiết lập ngôn ngữ hiển thị thì kiểm tra theo 2 ngôn ngữ đầu tiên
                    else if (!in_array($v, [
                        SITE_LANGUAGE_FIXED[0]['value'],
                        SITE_LANGUAGE_FIXED[1]['value'],
                    ])) {
                        // ko có -> bỏ qua
                        continue;
                    }
                }
                // bỏ dấu / ở 2 đầu nếu có
                else if (in_array($k, $arr_trim_ds) && !empty($v)) {
                    $v = ltrim($v, '/');
                    $v = rtrim($v, '/');
                }

                //
                $str_quote = "'";
                if (strpos($v, "'") !== false) {
                    $str_quote = '"';
                    if (strpos($v, '"') !== false) {
                        $v = str_replace('"', '\"', $v);
                    }
                }

                // 
                //$a[] = "defined('$k') || define('$k', $str_quote$v$str_quote);";
                $a[] = "define('$k', $str_quote$v$str_quote);";
            }
        }
        // print_r($a);
        //echo implode(PHP_EOL, $a) . PHP_EOL;

        //
        $f = DYNAMIC_CONSTANTS_PATH;
        echo $f . '<br>' . PHP_EOL;

        //
        if (!empty($a)) {
            //var_dump($a);

            //
            foreach ($a as $v) {
                echo $v . '<br>' . PHP_EOL;
            }

            //
            $this->base_model->ftp_create_file($f, str_replace(' ', '', '< ? php') . PHP_EOL . implode(PHP_EOL, $a) . PHP_EOL);
        } else if (is_file($f)) {
            unlink($f);
        }

        //
        parent::updated($this->config_type);
    }

    /**
     * kiểm tra redis khi lưu config
     **/
    protected function checkRedis()
    {
        // connect thử vào redis
        try {
            $rd = new \Redis();
            $rd->connect(WGR_REDIS_HOSTNAME, WGR_REDIS_PORT);

            // 
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * kiểm tra Memcached khi lưu config
     **/
    protected function checkMemcached()
    {
        // connect thử vào Memcached
        try {
            // procedural API
            if (function_exists('memcache_connect')) {
                $memcache_obj = memcache_connect('localhost', WGR_MEMCACHED_PORT);
            } else {
                // OO API
                $memcache = new \Memcache;
                $memcache->connect('localhost', WGR_MEMCACHED_PORT);
            }

            // 
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
