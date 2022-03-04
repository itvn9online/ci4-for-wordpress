<?php
namespace App\ Controllers;

class Ajax extends Layout {
    // chức năng này không cần nạp header
    public $preload_header = false;

    public function __construct() {
        parent::__construct();
    }

    // trả về URL hiện tại của website -> dùng để kiểm tra URL có https với www không
    protected function check_via_curl( $test_url, $session_id = 'b822aeffbdeab9bbf59bd885bbd06e02' ) {
        $curl = curl_init();
        curl_setopt_array( $curl, array(
            CURLOPT_URL => $test_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Cookie: PHPSESSID=' . $session_id
            ),
        ) );
        $http_response = curl_exec( $curl );
        curl_close( $curl );

        //
        return $http_response;
    }
    public function check_ssl() {
        header( 'Content-type: application/json; charset=utf-8' );

        //
        $test_url = DYNAMIC_BASE_URL . 'ajax/the_base_url';
        // bỏ https
        $test_url = str_replace( 'https://', 'http://', $test_url );
        // bỏ www.
        $test_url = str_replace( 'http://www.', 'http://', $test_url );
        //die( $test_url );

        // thêm lại www.
        $www_url = str_replace( 'http://', 'http://www.', $test_url );
        //die( $www_url );

        //
        //$client = \Config\ Services::curlrequest();

        // rồi lấy url xem nó có tự redirect về url chuẩn mình mong muốn không
        die( json_encode( [
            'http_url' => $test_url,
            'http_response' => $this->check_via_curl( $test_url ),
            'www_url' => $www_url,
            'www_response' => $this->check_via_curl( $www_url ),
            //'base_url' => file_get_contents( $test_url . 'ajax/the_base_url', 1 ),
            /*
            'base_url' => $client->request( 'GET', $test_url, [
                'allow_redirects' => true,
                // Sets the following defaults:
                'max' => 5, // Maximum number of redirects to follow before stopping
                'strict' => true, // Ensure POST requests stay POST requests through redirects
                'protocols' => [ 'http', 'https' ] // Restrict redirects to one or more protocols
            ] ),
            */
        ] ) );
    }
    public function the_base_url() {
        die( DYNAMIC_BASE_URL );
    }

    public function multi_login() {
        header( 'Content-type: application/json; charset=utf-8' );

        //
        //die( json_encode( $_GET ) );
        //echo PATH_LAST_LOGGED . '<br>' . "\n";

        // lấy nội dung file cũ
        $result = $this->current_user_id > 0 ? file_get_contents( PATH_LAST_LOGGED . $this->current_user_id, 1 ) : '';

        // lưu session id của người dùng vào file
        $this->base_model->set_logged( $this->current_user_id );


        // trả về key đã lưu của người dùng trong file
        die( json_encode( [
            //'key' => PATH_LAST_LOGGED . $this->current_user_id,
            't' => time(),
            'hash' => $result
        ] ) );
    }
}