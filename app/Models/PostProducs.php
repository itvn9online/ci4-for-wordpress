<?php

namespace App\Models;

// Libraries
use App\Libraries\PostType;

//
class PostProducs extends PostPages
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Trả về danh sách sản phẩm
     **/
    public function get_products_by($prams, $ops = [], $in_cache = '', $cache_time = 300)
    {
        $prams = $this->sync_post_parms($prams);
        $ops = $this->sync_post_ops($ops);
        //print_r($ops);

        // fix cứng tham số
        $prams['post_type'] = PostType::PROD;
        $prams['taxonomy'] = TaxonomyType::PROD_CATS;

        //
        return $this->get_posts($prams, $ops, $in_cache, $cache_time);
    }
    public function count_products_by($prams, $ops = [])
    {
        $prams = $this->sync_post_parms($prams);
        //print_r($prams);

        // fix cứng tham số
        $prams['post_type'] = PostType::PROD;
        $prams['taxonomy'] = TaxonomyType::PROD_CATS;

        //
        $ops['count_record'] = 1;

        //
        return $this->get_posts($prams, $ops);
    }
}
