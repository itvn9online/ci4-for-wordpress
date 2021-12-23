<?php
namespace App\ Controllers;

//
class Csrf extends Layout {
    public function __construct() {
        parent::__construct();

        /*
         * Kiểm tra đầu vào của dữ liệu xem chuẩn không
         */
        $csrf_name = csrf_token();
        //echo $csrf_name . '<br>' . "\n";
        if ( isset( $_REQUEST[ $csrf_name ] ) ) {
            //print_r( $_REQUEST );
            if ( $_REQUEST[ $csrf_name ] != csrf_hash() ) {
                /*
                die( json_encode( [
                    'code' => __LINE__,
                    'in' => $_REQUEST[ $csrf_name ],
                    'out' => csrf_hash(),
                    'error' => 'Invalid token from your request!'
                ] ) );
                */
                //die( __FILE__ . ':' . __LINE__ );
            }
        }
        //die( __FILE__ . ':' . __LINE__ );
    }
}