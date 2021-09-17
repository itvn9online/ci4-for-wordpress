<?php
require_once __DIR__ . '/Layout.php';

class Search extends Layout {

    public function __construct() {
        parent::__construct();
        //$this->load->helper( 'translate' );
        //$this->load->helper( 'form' );
    }

    // trả về json chứa dữ liệu của các bản ghi phục vụ cho tìm kiếm nhanh
    public function index() {
        print_r( $_GET );
    }

    // trả về json chứa dữ liệu của các bản ghi phục vụ cho tìm kiếm nhanh
    public function quick_search() {
        $data = [];

        //
        $post_ops = [
            // không cần lấy post meta
            'no_meta' => 1,
            // chỉ lấy 1 số cột nhất định
            'select' => 'ID, post_title, post_name, post_type',
            // số lượng bản ghi cần lấy
            'limit' => 500,
        ];

        //
        $data[ 'post' ] = $this->post_model->get_posts_by( [], $post_ops );
        $data[ 'blog' ] = $this->post_model->get_blogs_by( [], $post_ops );

        //
        //print_r( $data );


        // định dạng json
        header( 'Content-type: application/json; charset=utf-8' );
        header( 'Access-Control-Allow-Origin: *' );
        header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
        header( 'Cache-Control: post-check=0, pre-check=0', false );
        header( 'Pragma: no-cache' );

        //
        echo json_encode( $data );
    }
}