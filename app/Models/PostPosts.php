<?php

namespace App\ Models;

// Libraries
//use App\ Libraries\ LanguageCost;
use App\ Libraries\ PostType;
use App\ Libraries\ TaxonomyType;
//use App\ Libraries\ DeletedStatus;

//
class PostPosts extends PostSlider {
    public function __construct() {
        parent::__construct();
    }

    // trả về khối HTML của từng sản phẩm trong danh mục
    function get_the_node( $data, $ops = [] ) {
        //print_r( $data );
        $tmp_html = $this->product_html_node;
        //echo $tmp_html . '<br>' . "\n";

        //
        $data[ 'p_link' ] = $this->get_the_permalink( $data );
        if ( isset( $ops[ 'taxonomy_post_size' ] ) && $ops[ 'taxonomy_post_size' ] != '' ) {
            $data[ 'cf_product_size' ] = $ops[ 'taxonomy_post_size' ];
        } else {
            $data[ 'cf_product_size' ] = $this->getconfig->cf_product_size;
        }
        $data[ 'trv_num_giamoi' ] = 0;
        $data[ 'product_status' ] = 1;
        $data[ 'dynamic_title_tag' ] = 'h3';
        $data[ 'pt' ] = 0;
        $data[ 'trv_img' ] = $this->get_post_thumbnail( $data );
        if ( $data[ 'post_excerpt' ] == '' ) {
            $data[ 'post_excerpt' ] = strip_tags( $data[ 'post_content' ] );
            $data[ 'post_excerpt' ] = $this->base_model->short_string( $data[ 'post_excerpt' ], 168 );
        }

        //
        return $this->base_model->tmp_to_html( $tmp_html, $data );
    }

    function the_node( $data, $ops = [] ) {
        echo $this->get_the_node( $data, $ops );
    }

    // post
    public function get_posts_by( $prams, $ops = [] ) {
        $prams = $this->sync_post_parms( $prams );
        $ops = $this->sync_post_ops( $ops );

        // fix cứng tham số
        $prams[ 'post_type' ] = PostType::POST;
        $prams[ 'taxonomy' ] = TaxonomyType::POSTS;

        //
        return $this->get_posts( $prams, $ops );
    }
    public function count_posts_by( $prams, $ops = [] ) {
        $prams = $this->sync_post_parms( $prams );

        // fix cứng tham số
        $prams[ 'post_type' ] = PostType::POST;
        $prams[ 'taxonomy' ] = TaxonomyType::POSTS;

        //
        $ops[ 'count_record' ] = 1;

        //
        return $this->get_posts( $prams, $ops );
    }
}