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
            $this->base_model->alert('dât EMPTY');
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
            if ($v == '' || !isset($meta_default[$k])) {
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
                // với session drive sẽ config riêng
                if ($k == 'MY_SESSION_DRIVE') {
                    if ($v == 'FileHandler') {
                        // mặc định là sử dụng file -> không cần khai báo thêm
                        // $a[] = "define('CUSTOM_SESSION_PATH', WRITEPATH . 'session');";
                    } else if ($v == 'RedisHandler') {
                        if (empty(phpversion('redis'))) {
                            echo 'redis not found! <br>' . PHP_EOL;
                            continue;
                        }
                        $a[] = "define('CUSTOM_SESSION_PATH', 'tcp://localhost:6379');";
                    } else if ($v == 'MemcachedHandler') {
                        if (!class_exists('Memcached')) {
                            echo 'Memcached not found! <br>' . PHP_EOL;
                            continue;
                        }
                        $a[] = "define('CUSTOM_SESSION_PATH', 'localhost:11211');";
                    } else if ($v == 'DatabaseHandler') {
                        $a[] = "define('CUSTOM_SESSION_PATH', 'ci_sessions');";
                    } else {
                        // không nằm trong danh sách kia thì loại bỏ luôn
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

                //$a[] = "defined('$k') || define('$k', $str_quote$v$str_quote);";
                // 1 số trường hợp sẽ thêm lệnh kiểm tra vào trước tham số -> để tránh lỗi nếu trong quá trình hoạt động có sự thay đổi
                if ($k == 'MY_CACHE_HANDLER') {
                    if ($v == 'redis') {
                        if (empty(phpversion('redis'))) {
                            echo 'redis not found! <br>' . PHP_EOL;
                            continue;
                        }
                    } else if ($v == 'memcached') {
                        if (!class_exists('Memcached')) {
                            echo 'Memcached not found! <br>' . PHP_EOL;
                            continue;
                        }
                    }
                }

                //
                $a[] = "define('$k', $str_quote$v$str_quote);";
            }
        }
        //print_r($a);
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
}
