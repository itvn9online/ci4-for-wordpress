<?php

namespace App\ Models;

// Libraries
//use App\ Libraries\ LanguageCost;
use App\ Libraries\ PostType;
use App\ Libraries\ TaxonomyType;
//use App\ Libraries\ DeletedStatus;

//
class PostBlog extends PostAds {
    public function __construct() {
        parent::__construct();
    }

    // trả về khối HTML của từng sản phẩm trong danh mục
    function get_the_blog_node( $data, $ops = [] ) {
        //print_r( $data );
        if ( isset( $ops[ 'tmp_html' ] ) && $ops[ 'tmp_html' ] != '' ) {
            $tmp_html = $ops[ 'tmp_html' ];
        } else {
            $tmp_html = $this->blog_html_node;
        }
        //echo $tmp_html . '<br>' . "\n";

        //
        $data[ 'p_link' ] = $this->get_the_permalink( $data );
        if ( isset( $ops[ 'taxonomy_post_size' ] ) && $ops[ 'taxonomy_post_size' ] != '' ) {
            $data[ 'cf_blog_size' ] = $ops[ 'taxonomy_post_size' ];
        } else {
            $data[ 'cf_blog_size' ] = $this->getconfig->cf_blog_size;
        }
        $data[ 'post_category' ] = 0;
        $data[ 'taxonomy_key' ] = '';
        $data[ 'dynamic_post_tag' ] = 'h3';
        $data[ 'blog_link_option' ] = '';
        $data[ 'image' ] = $this->get_post_thumbnail( $data );

        if ( $data[ 'post_excerpt' ] == '' ) {
            $data[ 'post_excerpt' ] = strip_tags( $data[ 'post_content' ] );
            //echo $this->getconfig->cf_blog_description_length . ' aaaaaaaaaa <br>' . "\n";
            $data[ 'post_excerpt' ] = $this->base_model->short_string( $data[ 'post_excerpt' ], $this->getconfig->cf_blog_description_length );
        }

        //
        return $this->base_model->tmp_to_html( $tmp_html, $data );
    }

    function the_blog_node( $data, $ops = [] ) {
        echo $this->get_the_blog_node( $data, $ops );
    }

    // blog
    public function get_blogs_by( $prams, $ops = [] ) {
        $prams = $this->sync_post_parms( $prams );
        $ops = $this->sync_post_ops( $ops );

        // fix cứng tham số
        $prams[ 'post_type' ] = PostType::BLOG;
        $prams[ 'taxonomy' ] = TaxonomyType::BLOGS;

        //
        return $this->get_posts( $prams, $ops );
    }
    public function count_blogs_by( $prams, $ops = [] ) {
        $prams = $this->sync_post_parms( $prams );

        // fix cứng tham số
        $prams[ 'post_type' ] = PostType::BLOG;
        $prams[ 'taxonomy' ] = TaxonomyType::BLOGS;

        //
        $ops[ 'count_record' ] = 1;

        //
        return $this->get_posts( $prams, $ops );
    }
}