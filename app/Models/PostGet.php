<?php

namespace App\Models;

// Libraries
//use App\Libraries\PostType;

//
class PostGet extends PostQuery
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_auto_post($slug, $post_type = 'post', $taxonomy = 'category', $limit = 0, $ops = [])
    {
        //echo $slug . '<br>' . PHP_EOL;
        if ($slug == '') {
            die('slug for get auto post is NULL!');
        }

        //
        if ($post_type == '') {
            $post_type = $this->base_model->default_post_type;
        }
        if ($taxonomy == '') {
            $taxonomy = $this->base_model->default_taxonomy;
        }

        //
        //echo  __CLASS__ . ':' . __LINE__ . ':' . debug_backtrace()[1]['class'] . ':' . debug_backtrace()[1]['function'] . '<br>' . PHP_EOL;
        $post_cat = $this->term_model->get_cat_post($slug, $post_type, $taxonomy, true, $ops);
        $post_cat = $this->term_model->terms_meta_post([$post_cat]);
        $post_cat = $post_cat[0];
        //print_r($post_cat);
        //echo $post_cat[ 'taxonomy' ] . '<br>' . PHP_EOL;
        //echo $taxonomy . '<br>' . PHP_EOL;

        //
        if ($limit < 1 && isset($post_cat['term_meta']['post_number'])) {
            $limit = $post_cat['term_meta']['post_number'];
        }
        if ($limit < 1) {
            $limit = 1;
        }

        // lấy danh sách bài viết thuộc nhóm này
        $data = $this->select_list_post($post_type, $post_cat, $limit);
        //print_r( $data );

        //
        return [
            'term' => $post_cat,
            'posts' => $data,
        ];
    }

    /*
     * function lấy dữ liệu theo từng post type và taxonomy
     * điều kiện truyền vào có thể là term_id (ưu tiên) hoặc taxonomy slug
     */
    public function get_posts($prams, $ops = [], $in_cache = '', $cache_time = 300)
    {
        if (!isset($prams['limit'])) {
            $prams['limit'] = isset($ops['limit']) ? $ops['limit'] : 0;
        }
        if (!isset($prams['offset'])) {
            $prams['offset'] = isset($ops['offset']) ? $ops['offset'] : 0;
        }
        // ưu tiên lấy trong cache
        if ($in_cache != '') {
            $in_cache .= $prams['limit'] . $prams['offset'];
            $data = $this->base_model->scache($in_cache);
            if ($data !== null) {
                return $data;
            }
        }

        //
        if (!isset($prams['order_by'])) {
            $prams['order_by'] = isset($ops['order_by']) ? $ops['order_by'] : [];
        }
        //print_r( $prams );

        // kiểu dữ liệu trả về
        $ops['offset'] = $prams['offset'];
        // trả về số lượng bản ghi
        if (isset($ops['count'])) {
            $ops['count_record'] = 1;
        }
        //print_r($ops);

        //
        $data = $this->select_list_post($prams['post_type'], $prams, $prams['limit'], $prams['order_by'], $ops);

        //
        if ($in_cache != '') {
            $this->base_model->scache($in_cache, $data, $cache_time);
        }

        //
        return $data;
    }

    // đồng bộ tham số đầu vào
    public function sync_post_parms($prams)
    {
        // nếu đầu vào không phải array
        if (!is_array($prams)) {
            if (empty($prams)) {
                return [];
                //return debug_backtrace()[ 1 ][ 'function' ] . ' $prams is NULL!';
            }

            // tự tạo theo term_id
            if (is_numeric($prams)) {
                $prams = [
                    'term_id' => $prams
                ];
            }
            // hoặc slug
            else if (is_string($prams)) {
                $prams = [
                    'slug' => $prams
                ];
            }
        }
        //print_r( $prams );
        //die( 'dgh dgsda d' );
        return $prams;
    }
    public function sync_post_ops($ops)
    {
        // nếu đầu vào không phải array
        if (!is_array($ops)) {
            // tự tạo limit nếu đầu vào là 1 số
            if (!empty($ops) && is_numeric($ops)) {
                $ops = [
                    'limit' => $ops
                ];
            }
            // còn lại trả về empty do không rõ đầu vào là gì
            else {
                return [];
            }
        }
        //print_r( $ops );
        return $ops;
    }
}
