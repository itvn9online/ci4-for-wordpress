<?php
namespace App\ Controllers;

class Ajax extends Layout {
    // chức năng này không cần nạp header
    public $preload_header = false;

    protected $select_term_col = 'term_id, name, slug, term_group, count, parent, taxonomy, child_count, child_last_count';

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
        $this->base_model->set_logged( $this->current_user_id, $this->isMobile );


        // trả về key đã lưu của người dùng trong file
        die( json_encode( [
            //'key' => PATH_LAST_LOGGED . $this->current_user_id,
            't' => time(),
            'hash' => $result
        ] ) );
    }

    public function get_taxonomy_by_ids() {
        header( 'Content-type: application/json; charset=utf-8' );

        //
        $ids = $this->MY_post( 'ids', '' );
        if ( empty( $ids ) ) {
            die( json_encode( [
                'code' => __LINE__,
                'error' => 'EMPTY ids'
            ] ) );
        }

        //
        $data = $this->base_model->select( $this->select_term_col, WGR_TERM_VIEW, array(
            // WHERE AND OR
            //'is_member' => User_type::GUEST,
        ), array(
            'where_in' => array(
                'term_id' => explode( ',', $ids )
            ),
            // trả về COUNT(column_name) AS column_name
            //'selectCount' => 'ID',
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            // trả về tổng số bản ghi -> tương tự mysql num row
            //'getNumRows' => 1,
            //'offset' => 2,
            'limit' => -1
        ) );

        //
        die( json_encode( $data ) );
    }

    public function get_taxonomy_by_taxonomy() {
        header( 'Content-type: application/json; charset=utf-8' );

        //
        $taxonomy = $this->MY_post( 'taxonomy', '' );
        if ( empty( $taxonomy ) ) {
            die( json_encode( [
                'code' => __LINE__,
                'error' => 'EMPTY taxonomy'
            ] ) );
        }

        //
        //die( json_encode( $_POST ) );
        die( $this->term_model->json_taxonomy( $taxonomy, 0, [ 'get_child' => 1 ], $taxonomy . '_get_child' ) );
    }
}