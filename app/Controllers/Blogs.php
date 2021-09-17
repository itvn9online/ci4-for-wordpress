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
        $data = $this->base_model->select( 'wp_terms.*', 'wp_terms', array(
            // các kiểu điều kiện where
            'wp_terms.slug' => $slug,
            'wp_terms.is_deleted' => DeletedStatus::DEFAULT,
            'wp_terms.lang_key' => $this->lang_key,
            'wp_term_taxonomy.taxonomy' => TaxonomyType::BLOGS,
        ), array(
            'join' => [
                'wp_term_taxonomy' => 'wp_term_taxonomy.term_id = wp_terms.term_id'
            ],
            'order_by' => array(
                'wp_terms.term_id' => 'DESC'
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