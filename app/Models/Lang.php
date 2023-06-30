<?php

namespace App\Models;

//
use App\Libraries\ConfigType;
use App\Helpers\HtmlTemplate;

//
class Lang extends EbModel
{
    public $option_prefix = 'lang_';

    public function __construct()
    {
        parent::__construct();

        $this->option_model = new \App\Models\Option();
    }

    /*
     * Chức năng này sẽ lấy các bản ghi thuộc dạng bản dịch trong hệ thống để in ra
     * $key: key truyền vào và sẽ trả về dữ liệu tương ứng nếu có
     * $before_text: đoạn chữ đính kèm trước dữ liệu trả về
     * $after_text: đoạn chữ đính kèm phía sau dữ liệu trả về
     */
    public function get_the_text($key, $default_value = '', $before_text = '', $after_text = '')
    {
        global $this_cache_lang;

        //
        if ($this_cache_lang === NULL) {
            $this_cache_lang = $this->option_model->get_lang();
            //echo __CLASS__ . ':' . __LINE__ . PHP_EOL;
        }
        //print_r($this_cache_lang);

        //
        $key = $this->option_prefix . $key;
        //echo $key . '<br>' . PHP_EOL;
        // nếu chưa có
        if (!isset($this_cache_lang[$key])) {
            // gọi đến lệnh tạo lang
            $this_cache_lang[$key] = $this->option_model->create_lang($key, $default_value);
        }
        //print_r($this_cache_lang);
        return $before_text . $this_cache_lang[$key] . $after_text;
    }

    public function the_text($key, $default_value = '', $before_text = '', $after_text = '')
    {
        echo $this->get_the_text($key, $default_value, $before_text, $after_text);
    }

    // trả về thông tin bản quyền phần mềm theo tiêu chuẩn
    public function get_web_license($getconfig)
    {
        return HtmlTemplate::html('footer_copyright.html', [
            'copy_right_first' => $this->get_the_text('copy_right_first', ConfigType::placeholder('copy_right_first')),
            'year' => date('Y'),
            'name' => $getconfig->name,
            'copy_right_last' => $this->get_the_text('copy_right_last', ConfigType::placeholder('copy_right_last')),
            'powered_by_echbay' => $this->get_the_text('powered_by_echbay', ConfigType::placeholder('powered_by_echbay')),
        ]);
    }

    public function the_web_license($getconfig)
    {
        echo $this->get_web_license($getconfig);
    }
}
