<?php

namespace App\Models;

//
use App\Libraries\ConfigType;

//
class Checkbox extends Lang
{
    public $option_prefix = '';

    public function __construct()
    {
        parent::__construct();
    }

    // trả về giá trị on/off của 1 ản ghi dạng checkbox
    public function get_the_checkbox($key, $default_value = '')
    {
        if ($GLOBALS['this_cache_checkbox'] === null) {
            // echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
            $GLOBALS['this_cache_checkbox'] = $this->option_model->arr_config(ConfigType::CHECKBOX);
        }
        //print_r($GLOBALS['this_cache_checkbox']);

        //
        //echo $key . '<br>' . "\n";
        // nếu chưa có
        if (!isset($GLOBALS['this_cache_checkbox'][$key])) {
            // gọi đến lệnh tạo num
            $GLOBALS['this_cache_checkbox'][$key] = 'off';
        }
        //print_r($GLOBALS['this_cache_checkbox']);
        return $GLOBALS['this_cache_checkbox'][$key];
    }

    public function the_checkbox($key, $default_value = '')
    {
        echo $this->get_the_checkbox($key, $default_value);
    }
}
