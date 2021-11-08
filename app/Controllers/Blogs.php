<?php
//require_once __DIR__ . '/Posts.php';
namespace App\ Controllers;

// Libraries
use App\ Libraries\ DeletedStatus;
use App\ Libraries\ TaxonomyType;
use App\ Libraries\ PostType;

//
class Blogs extends Posts {
    public function __construct() {
        parent::__construct();
    }

    public function blog_details( $id, $slug ) {
        return $this->index( $id, $slug, PostType::BLOG, TaxonomyType::BLOGS, 'blog_view' );
    }

    public function blogs_list( $slug, $set_page = '', $page_num = 1 ) {
        //echo $slug . ' <br>' . "\n";
        //echo $set_page . ' <br>' . "\n";
        //echo $page_num . ' <br>' . "\n";
        //echo 'blog list <br>' . "\n";

        //
        if ( $slug == '' ) {
            die( '404 slug error!' );
        }

        // -> kiểm tra theo category
        $data = $this->base_model->select( '*', 'v_terms', array(
            // các kiểu điều kiện where
            'slug' => $slug,
            'is_deleted' => DeletedStatus::DEFAULT,
            'lang_key' => $this->lang_key,
            'taxonomy' => TaxonomyType::BLOGS,
        ), array(
            'order_by' => array(
                'term_id' => 'DESC'
            ),
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 2,
            'limit' => 1
        ) );
        //print_r( $data );

        // có -> ưu tiên category
        if ( !empty( $data ) ) {
            return $this->category( $data, PostType::BLOG, TaxonomyType::BLOGS, 'blogs_view', [
                'page_num' => $page_num,
            ] );
        }

        //
        return $this->page404();
    }
}