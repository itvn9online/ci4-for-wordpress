<?php

namespace App\Models;

//
use App\Libraries\ConfigType;
use App\Libraries\LanguageCost;

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
        global $this_cache_checkbox;

        //
        if ($this_cache_checkbox === NULL) {
            $this_cache_checkbox = $this->option_model->gets_config(ConfigType::CHECKBOX, LanguageCost::lang_key());
        }
        //print_r($this_cache_checkbox);

        //
        //echo $key . '<br>' . "\n";
        // nếu chưa có
        if (!isset($this_cache_checkbox[$key])) {
            // gọi đến lệnh tạo num
            $this_cache_checkbox[$key] = 'off';
        }
        //print_r($this_cache_checkbox);
        return $this_cache_checkbox[$key];
    }

    public function the_checkbox($key, $default_value = '')
    {
        echo $this->get_the_checkbox($key, $default_value);
    }
}
