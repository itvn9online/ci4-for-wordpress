<?php

namespace App\ Models;

class Lang extends EbModel {
    public $list = NULL;

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
        if ( $this->list === NULL ) {
            $data = $this->option_model->get_lang();

            //
            $this->list = [];
            foreach ( $data as $v ) {
                $this->list[ $v[ 'option_name' ] ] = $v[ 'option_value' ];
            }
        }
        //print_r( $this->list );

        //
        $key = 'lang_' . $key;
        //echo $key . '<br>' . "\n";
        if ( isset( $this->list[ $key ] ) ) {
            return $before_text . $this->list[ $key ] . $after_text;
        }
        return '';
    }

    public function the_text( $key, $before_text = '', $after_text = '' ) {
        echo $this->get_the_text( $key, $before_text, $after_text );
    }
}