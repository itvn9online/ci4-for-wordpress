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
        global $this_cache_num;

        //
        if ($this_cache_num === NULL) {
            $this_cache_num = $this->option_model->arr_config(ConfigType::NUM_MON);
        }
        //print_r($this_cache_num);

        //
        //echo $key . '<br>' . PHP_EOL;
        // nếu chưa có
        if (!isset($this_cache_num[$key])) {
            // gọi đến lệnh tạo num
            $this_cache_num[$key] = $this->option_model->create_num($key, $default_value);
        }
        //print_r($this_cache_num);
        return $this_cache_num[$key];
    }

    public function the_number($key, $default_value = '')
    {
        echo $this->get_the_number($key, $default_value);
    }
}
