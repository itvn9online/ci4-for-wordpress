<?php

namespace App\Models;

//
use App\Libraries\ConfigType;

//
class Num extends Lang
{
    public $option_prefix = '';

    public function __construct()
    {
        parent::__construct();
    }

    // trả về 1 giá trị số dữ theo key truyền vào
    public function get_the_number($key, $default_value = '')
    {
        if ($GLOBALS['this_cache_num'] === null) {
            // echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
            $GLOBALS['this_cache_num'] = $this->option_model->arr_config(ConfigType::NUM_MON);
        }
        //print_r($GLOBALS['this_cache_num']);

        //
        //echo $key . '<br>' . "\n";
        // nếu chưa có
        if (!isset($GLOBALS['this_cache_num'][$key])) {
            // gọi đến lệnh tạo num
            $GLOBALS['this_cache_num'][$key] = $this->option_model->create_num($key, $default_value);
        }
        //print_r($GLOBALS['this_cache_num']);
        return $GLOBALS['this_cache_num'][$key] * 1;
    }

    public function the_number($key, $default_value = '')
    {
        echo $this->get_the_number($key, $default_value);
    }
}
