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

    // các thông số dùng để test redis, memcached khi có thiết lập
    protected $redis_hostname = WGR_REDIS_HOSTNAME;
    protected $redis_port = WGR_REDIS_PORT;
    protected $memcached_hostname = WGR_MEMCACHED_HOSTNAME;
    protected $memcached_port = WGR_MEMCACHED_PORT;

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
        // print_r($data);
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
            'WGR_TAGS_PERMALINK',
            'WGR_PRODS_PERMALINK',
            'WGR_PROD_TAGS_PERMALINK',
            'WGR_TAXONOMY_PERMALINK',
            //
            'WGR_POST_PERMALINK',
            'WGR_PROD_PERMALINK',
            'WGR_PAGE_PERMALINK',
            'WGR_POSTS_PERMALINK',
        ];

        // 1 số thông số sẽ thay đổi ngay khi có dữ liệu
        if (!empty($data['WGR_REDIS_HOSTNAME'])) {
            $this->redis_hostname = $data['WGR_REDIS_HOSTNAME'];
        }
        if (trim($data['WGR_REDIS_PORT']) != '') {
            $this->redis_port = $data['WGR_REDIS_PORT'];
        } else if (strpos(basename($this->redis_hostname), '.sock') !== false) {
            $data['WGR_REDIS_PORT'] = '0';
            $this->redis_port = $data['WGR_REDIS_PORT'];
        }
        // 
        if (!empty($data['WGR_MEMCACHED_HOSTNAME'])) {
            $this->memcached_hostname = $data['WGR_MEMCACHED_HOSTNAME'];
        }
        if (trim($data['WGR_MEMCACHED_PORT']) != '') {
            $this->memcached_port = $data['WGR_MEMCACHED_PORT'];
        } else if (strpos(basename($this->memcached_hostname), '.sock') !== false) {
            $data['WGR_MEMCACHED_PORT'] = '0';
            $this->memcached_port = $data['WGR_MEMCACHED_PORT'];
        }

        // xem người dùng có chọn ngôn ngữ hiển thị không
        $arr_language_fixed = $this->MY_post('site_language_fixed');
        // nếu ko có
        if (empty($arr_language_fixed)) {
            // gán mặc định là 2 ngôn ngữ đầu tiên
            $arr_language_fixed = [
                SITE_LANGUAGE_FIXED[0]['value'],
                SITE_LANGUAGE_FIXED[1]['value'],
            ];
        }
        // print_r($arr_language_fixed);

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
                            // nếu không lỗi lầm gì thì thiết lập đường dẫn lưu session bằng redis
                            if (empty($data['CUSTOM_SESSION_PATH'])) {
                                $a[] = "define('CUSTOM_SESSION_PATH', 'tcp://localhost:" . $this->redis_port . "');";
                            }
                        }
                    } else if ($v == 'MemcachedHandler') {
                        if (!class_exists('Memcached') || $this->checkMemcached() !== true) {
                            echo 'Memcached not found! Code #' . __LINE__ . ' <br>' . PHP_EOL;
                            continue;
                        } else {
                            // nếu không lỗi lầm gì thì thiết lập đường dẫn lưu session bằng Memcached
                            if (empty($data['CUSTOM_SESSION_PATH'])) {
                                $a[] = "define('CUSTOM_SESSION_PATH', 'localhost:" . $this->memcached_port . "');";
                            }
                        }
                    } else if ($v == 'DatabaseHandler') {
                        if (empty($data['CUSTOM_SESSION_PATH'])) {
                            $a[] = "define('CUSTOM_SESSION_PATH', 'ci_sessions');";
                        }
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
                    // $i_lang_fixed = 0;
                    foreach (SITE_LANGUAGE_FIXED as $langs_fixed) {
                        // nếu không có trong danh sách đã chọn thì bỏ qua luôn
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
                        // $i_lang_fixed++;
                    }
                    // END array
                    $a_fixed_lang[] = ']';
                    // print_r($a_fixed_lang);

                    // gộp mảng và loại bỏ các ký tự thừa
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
                    // echo $v . '<br>' . PHP_EOL;

                    // ngôn ngữ mặc định phải là 1 trong các ngôn ngữ đã chọn
                    // echo count($arr_language_fixed) . '<br>' . PHP_EOL;

                    // nếu chỉ có 1 thì thiết lập giá trị là mảng đã chọn luôn
                    if (count($arr_language_fixed) === 1) {
                        $v = $arr_language_fixed[0];
                    }
                    // nếu ko có -> bỏ qua thiết lập ngôn ngữ mặc định
                    else if (!in_array($v, $arr_language_fixed)) {
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

        // nếu ngôn ngữ mặc định không được thiết lập
        if (empty($data['SITE_LANGUAGE_DEFAULT'])) {
            // có ngôn ngữ đã chọn -> lấy phần tử đầu tiên
            $a[] = "define('SITE_LANGUAGE_DEFAULT', '" . $arr_language_fixed[0] . "');";
        }

        //
        $f = DYNAMIC_CONSTANTS_PATH;
        echo $f . '<br>' . PHP_EOL;

        //
        if (!empty($a)) {
            //var_dump($a);

            // thêm thông tin người thực hiện lưu trữ
            $a[] = '// ' . $this->base_model->MY_sessid() . ' from ' . $this->request->getIPAddress() . ' in ' . date('r');
            $a[] = '// by ' . $this->session_data['user_email'] . ' (' . md5($this->session_data['user_email']) . ') #' . $this->current_user_id;
            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                $a[] = '// ' . $_SERVER['HTTP_USER_AGENT'];
            }

            //
            foreach ($a as $v) {
                echo $v . '<br>' . PHP_EOL;
            }

            //
            $this->base_model->ftp_create_file($f, str_replace(' ', '', '< ? php') . PHP_EOL . implode(PHP_EOL, $a) . PHP_EOL);

            // gửi 1 lệnh mở link trong tab mới để test code
            echo '<script>top.open_home_for_test_config_constants();</script>';
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
        echo 'Redis hostname: ' . $this->redis_hostname . '<br>' . PHP_EOL;
        echo 'Redis port: ' . $this->redis_port . '<br>' . PHP_EOL;
        // die(__CLASS__ . ':' . __LINE__);

        // connect thử vào redis
        try {
            $rd = new \Redis();
            $rd->connect($this->redis_hostname, $this->redis_port);

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
        echo 'Memcached hostname: ' . $this->memcached_hostname . '<br>' . PHP_EOL;
        echo 'Memcached port: ' . $this->memcached_port . '<br>' . PHP_EOL;
        // die(__CLASS__ . ':' . __LINE__);

        // connect thử vào Memcached
        try {
            // procedural API
            // if (function_exists('memcache_connect') && strpos($this->memcached_hostname, '/') === false) {
            if (function_exists('memcache_connect')) {
                $memcache_obj = memcache_connect($this->memcached_hostname, $this->memcached_port);
            } else {
                // OO API
                $memcache = new \Memcache;
                $memcache->connect($this->memcached_hostname, $this->memcached_port);
            }

            // 
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
