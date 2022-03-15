<?php

namespace App\ Models;

class Lang extends EbModel {
    public function __construct() {
        parent::__construct();

        $this->option_model = new\ App\ Models\ Option();
    }

    /*
     * Chức năng này sẽ lấy các bản ghi thuộc dạng bản dịch trong hệ thống để in ra
     * $key: key truyền vào và sẽ trả về dữ liệu tương ứng nếu có
     * $before_text: đoạn chữ đính kèm trước dữ liệu trả về
     * $after_text: đoạn chữ đính kèm phía sau dữ liệu trả về
     */
    public function get_the_text( $key, $before_text = '', $after_text = '' ) {
        global $this_cache_lang;

        //
        if ( $this_cache_lang === NULL ) {
            $this_cache_lang = $this->option_model->get_lang();
        }
        //print_r( $this_cache_lang );

        //
        $key = 'lang_' . $key;
        //echo $key . '<br>' . "\n";
        if ( isset( $this_cache_lang[ $key ] ) ) {
            return $before_text . $this_cache_lang[ $key ] . $after_text;
        }
        return '';
    }

    public function the_text( $key, $before_text = '', $after_text = '' ) {
        echo $this->get_the_text( $key, $before_text, $after_text );
    }
}