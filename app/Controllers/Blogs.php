<?php
namespace App\ Controllers;

// Libraries
use App\ Libraries\ DeletedStatus;
use App\ Libraries\ TaxonomyType;
use App\ Libraries\ PostType;

//
class Blogs extends Posts {
    protected $post_type = PostType::BLOG;
    protected $taxonomy = TaxonomyType::BLOGS;
    protected $file_view = 'blog_view';

    public function __construct() {
        parent::__construct();
    }

    public function blog_details( $id, $slug ) {
        return $this->index( $id, $slug );
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
        $data = $this->term_model->get_taxonomy( array(
            // các kiểu điều kiện where
            'slug' => $slug,
            'is_deleted' => DeletedStatus::FOR_DEFAULT,
            'lang_key' => $this->lang_key,
            'taxonomy' => $this->taxonomy,
        ) );
        //print_r( $data );

        // có -> ưu tiên category
        if ( !empty( $data ) ) {
            return $this->category( $data, PostType::BLOG, $this->taxonomy, 'blogs_view', [
                'page_num' => $page_num,
            ] );
        }

        //
        return $this->page404( 'ERROR ' . strtolower( __FUNCTION__ ) . ':' . __LINE__ . '! Không xác định được danh mục tin tức...' );
    }
}