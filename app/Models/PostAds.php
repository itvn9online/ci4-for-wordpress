<?php

namespace App\ Models;

// Libraries
//use App\ Libraries\ LanguageCost;
use App\ Libraries\ PostType;
use App\ Libraries\ TaxonomyType;
//use App\ Libraries\ DeletedStatus;

//
class PostAds extends PostPosts {
    public function __construct() {
        parent::__construct();
    }

    function get_the_ads( $slug, $limit = 0, $ops = [] ) {
        $ops[ 'post_type' ] = PostType::ADS;
        $ops[ 'taxonomy' ] = TaxonomyType::ADS;
        $ops[ 'limit' ] = $limit;
        // nhân bản sang các ngôn ngữ khác
        $ops[ 'auto_clone' ] = 1;

        //
        return $this->echbay_blog( $slug, $ops );
    }

    function the_ads( $slug, $limit = 0, $ops = [] ) {
        echo $this->get_the_ads( $slug, $limit, $ops );
    }

    // ads
    public function get_adss_by( $prams, $ops = [] ) {
        $prams = $this->sync_post_parms( $prams );
        $ops = $this->sync_post_ops( $ops );

        // riêng với mục ads -> ưu tiên sử dụng slug để có thể tạo tụ động nhóm nếu có
        if ( isset( $prams[ 'slug' ] ) && $prams[ 'slug' ] != '' ) {
            if ( !isset( $prams[ 'limit' ] ) ) {
                $prams[ 'limit' ] = isset( $ops[ 'limit' ] ) ? $ops[ 'limit' ] : 0;
            }
            //print_r( $prams );

            // gọi tới hàm với tham số return_object để nó trả về dữ liệu luôn
            $data = $this->get_the_ads( $prams[ 'slug' ], $prams[ 'limit' ], [
                'return_object' => 1
            ] );
            //print_r( $data );

            //
            return $data;
        }

        // fix cứng tham số
        $prams[ 'post_type' ] = PostType::ADS;
        $prams[ 'taxonomy' ] = TaxonomyType::ADS;

        // còn lại sẽ sử dụng term_id
        return $this->get_posts( $prams, $ops );
    }
    public function count_adss_by( $prams, $ops = [] ) {
        $prams = $this->sync_post_parms( $prams );

        // fix cứng tham số
        $prams[ 'post_type' ] = PostType::ADS;
        $prams[ 'taxonomy' ] = TaxonomyType::ADS;

        //
        $ops[ 'count_record' ] = 1;

        //
        return $this->get_posts( $prams, $ops );
    }
}