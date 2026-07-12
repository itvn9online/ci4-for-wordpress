<?php

namespace App\Models\Traits;

use App\Libraries\LanguageCost;
use App\Libraries\DeletedStatus;
use App\Libraries\TaxonomyType;

//
trait TermGetTrait
{
    public function get_json_taxonomy($taxonomy = 'category', $term_id = 0, $ops = [], $in_cache = '')
    {
        // nếu không có cache key -> kiểm tra điều kiện tạo key
        if ($in_cache == '') {
            if ($term_id < 1 && empty($ops)) {
                $in_cache = $taxonomy;
            }
        }
        //echo 'in_cache: ' . $in_cache . '<br>' . "\n";

        // cố định loại cột cần lấy
        $ops['select_col'] = 'term_id, name, term_shortname, slug, term_group, count, parent, taxonomy, child_count, child_last_count, term_permalink, term_avatar, term_favicon, term_status, lang_key';
        //$ops[ 'select_col' ] = '*';

        //
        return json_encode($this->get_all_taxonomy($taxonomy, $term_id, $ops, $in_cache));
        // return str_replace('\'', '\\\'', json_encode($this->get_all_taxonomy($taxonomy, $term_id, $ops, $in_cache)));
    }

    public function json_taxonomy($taxonomy = 'category', $term_id = 0, $ops = [], $in_cache = '')
    {
        echo $this->get_json_taxonomy($taxonomy, $term_id, $ops, $in_cache);
    }

    public function delete_cache_taxonomy($taxonomy, $in_cache = '')
    {
        if ($in_cache == '') {
            $in_cache = $taxonomy;
        }
        return $this->get_all_taxonomy($taxonomy, 0, NULL, $in_cache, true);
    }

    /**
     * trả về tổng số bản ghi của term theo điều kiện truyền vào
     */
    public function count_all_taxonomy($taxonomy = 'category', $term_id = 0, $ops = [])
    {
        // cố định tham số count data
        $ops['select_col'] = 'COUNT(term_id) AS c';
        // đặt tham số như này để bỏ qua chế độ order by
        $ops['order_by'] = [];
        $ops['result_count'] = __FUNCTION__;

        //
        $result = $this->get_all_taxonomy($taxonomy, $term_id, $ops);
        //print_r( $result );

        //
        return $result[0]['c'];
    }

    public function get_all_taxonomy($taxonomy = 'category', $term_id = 0, $ops = [], $in_cache = '', $clear_cache = false, $time = MINI_CACHE_TIMEOUT)
    {
        //print_r($ops);

        // nếu không có cache key -> kiểm tra điều kiện tạo key
        if ($in_cache == '') {
            if ($term_id < 1 && empty($ops)) {
                $in_cache = $taxonomy;
            }
        }

        //
        if (!isset($ops['lang_key']) || $ops['lang_key'] == '') {
            $ops['lang_key'] = LanguageCost::lang_key();
        }

        //
        if ($in_cache != '') {
            $in_cache = $in_cache . '-' . __FUNCTION__ . '-' . $ops['lang_key'];
            //echo $in_cache . '<br>' . "\n";

            // xóa cache nếu có yêu cầu
            if ($clear_cache === true) {
                return $this->base_model->cache->delete($in_cache);
            }

            //
            $cache_value = $this->base_model->scache($in_cache);
            //print_r( $cache_value );
            //var_dump( $cache_value );

            // có cache thì trả về
            if ($cache_value !== null) {
                //print_r( $cache_value );
                return $cache_value;
                /*
                } else {
                print_r( $cache_value );
                var_dump( $cache_value );
                */
            }
        }

        // các kiểu điều kiện where
        $where = [
            'taxonomy' => $taxonomy,
            //'term_status' => DeletedStatus::TERM_SHOW,
            'lang_key' => $ops['lang_key']
        ];

        // 
        if (isset($ops['parent'])) {
            $where['parent'] = $ops['parent'];
        }

        // 
        $where_or_like = [];
        if ($term_id > 0) {
            $where['term_id'] = $term_id;
            $ops['limit'] = 1;
        } else if (isset($ops['slug']) && !empty($ops['slug'])) {
            $where['slug'] = $ops['slug'];
            $ops['limit'] = 1;
            $ops['slug_get_child'] = 1;
        } else {
            if (isset($ops['by_is_deleted'])) {
                //echo $ops[ 'by_is_deleted' ] . '<br>' . "\n";
                $where['is_deleted'] = $ops['by_is_deleted'];
            } else {
                $where['is_deleted'] = DeletedStatus::FOR_DEFAULT;
            }

            // tìm kiếm
            if (isset($ops['or_like']) && !empty($ops['or_like'])) {
                $where_or_like = $ops['or_like'];
                $ops['get_child'] = 0;
                unset($ops['get_child']);
            }
            //
            else {
                //if ( $where[ 'is_deleted' ] != DeletedStatus::DELETED ) {
                if (isset($ops['get_child'])) {
                    if (!isset($ops['parent'])) {
                        $ops['parent'] = 0;
                    }
                }
                if (isset($ops['parent'])) {
                    $where['parent'] = $ops['parent'];
                }
                //}
            }
        }

        //
        if (!isset($ops['limit'])) {
            $ops['limit'] = 500;
            /*
            } else if ( $ops[ 'limit' ] < 0 ) {
            $ops[ 'limit' ] = 0;
            */
        }
        if (!isset($ops['offset'])) {
            $ops['offset'] = 0;
        }

        //
        if (!isset($ops['select_col'])) {
            $ops['select_col'] = '*';
        }
        if (!isset($ops['result_count']) && !isset($ops['order_by'])) {
            $ops['order_by'] = [
                'term_order' => 'DESC',
                'term_shortslug' => 'ASC',
                'slug' => 'ASC',
                'term_id' => 'DESC',
            ];
        }
        //print_r( $where );
        //print_r( $ops );
        //print_r( $where_or_like );
        //die( __CLASS__ . ':' . __LINE__ );

        //
        $filter = [
            //'where_in' => isset( $ops[ 'where_in' ] ) ? $ops[ 'where_in' ] : [],
            'or_like' => $where_or_like,
            'order_by' => $ops['order_by'],
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            'offset' => $ops['offset'],
            'limit' => $ops['limit']
        ];

        //
        if (isset($ops['show_query'])) {
            $filter['show_query'] = 1;
        }

        //
        $post_cat = $this->base_model->select(
            $ops['select_col'],
            WGR_TERM_VIEW,
            $where,
            $filter
        );
        //print_r( $post_cat );
        //die( __CLASS__ . ':' . __LINE__ );

        // có result count -> lấy tổng số bản ghi -> trả về luôn -> không cần chạy đoạn code sau
        if (isset($ops['result_count'])) {
            return $post_cat;
        }

        // daidq (2021-12-01): khi có thêm tham số by_is_deleted mà vẫn lấy term meta thì bị lỗi query -> tạm bỏ
        if (!empty($post_cat)) {
            //if ( !isset( $ops[ 'by_is_deleted' ] ) ) {
            //print_r( $ops );

            // lấy meta
            if ($term_id > 0 || isset($ops['slug_get_child'])) {
                //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
                //print_r( $post_cat );
                $post_cat = $this->terms_meta_post([$post_cat]);
                //print_r( $post_cat );

                // lấy các nhóm con
                if (isset($ops['get_child']) && $ops['get_child'] > 0) {
                    die(__CLASS__ . ':' . __LINE__);
                    $post_cat = $this->get_child_terms($post_cat, $ops);
                }
                $post_cat = $post_cat[0];
            } else if (isset($ops['get_meta'])) {
                // die(__CLASS__ . ':' . __LINE__);
                $post_cat = $this->terms_meta_post($post_cat);

                // lấy các nhóm con
                if (isset($ops['get_child']) && $ops['get_child'] > 0) {
                    //die( __CLASS__ . ':' . __LINE__ );
                    $post_cat = $this->get_child_terms($post_cat, $ops);
                }
            } else if (isset($ops['get_child']) && $ops['get_child'] > 0) {
                //die( __CLASS__ . ':' . __LINE__ );
                $post_cat = $this->get_child_terms($post_cat, $ops);
            }
            //}

            //print_r($post_cat);
            //die(__CLASS__ . ':' . __LINE__);
        }

        //
        if ($in_cache != '') {
            $this->base_model->scache($in_cache, $post_cat, $time);
        }

        //
        return $post_cat;
    }

    public function get_taxonomy($where, $ops = [])
    {
        if (!isset($ops['where_in'])) {
            $ops['where_in'] = [];
        }
        if (!isset($ops['limit'])) {
            $ops['limit'] = 1;
        }
        if (!isset($ops['select_col'])) {
            $ops['select_col'] = '*';
        }
        if (!isset($ops['order_by'])) {
            $ops['order_by'] = [
                'term_order' => 'DESC',
                'term_id' => 'DESC',
            ];
        }

        //
        $filter = [
            'where_in' => $ops['where_in'],
            'order_by' => $ops['order_by'],
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 2,
            'limit' => $ops['limit']
        ];
        if (isset($ops['show_query'])) {
            $filter['show_query'] = $ops['show_query'];
        }

        //
        return $this->base_model->select(
            $ops['select_col'],
            WGR_TERM_VIEW,
            $where,
            $filter
        );
    }

    function get_child_terms($data, $ops = [])
    {
        $current_time = time();

        //print_r( $data );
        foreach ($data as $k => $v) {
            $ops['parent'] = $v['term_id'];
            //print_r( $ops );
            //print_r( $v );
            //continue;

            //
            $child_term = [];
            $child_update_count = false;
            // nếu tham số child count chưa được cập nhật -> lấy từ database và cập nhật lại
            if ($v['child_count'] === null) {
                $child_update_count = 'query';
            }
            // hoặc lần cuối cập nhật cách đây đủ lâu
            //else if ($current_time - $v['child_last_count'] > $this->time_update_last_count) {
            else if ($v['child_last_count'] < $current_time) {
                $child_update_count = 'timeout';
            }

            //
            if ($child_update_count !== false) {
                $child_count = $this->count_all_taxonomy($v['taxonomy'], 0, $ops);
                if ($child_count > 0) {
                    $child_term = $this->get_all_taxonomy($v['taxonomy'], 0, $ops);
                }

                //
                $this->base_model->update_multiple(
                    $this->table,
                    [
                        'child_count' => $child_count,
                        'child_last_count' => $current_time + $this->time_update_count,
                    ],
                    [
                        'term_id' => $v['term_id'],
                    ]
                );

                // thông báo kiểu dữ liệu trả về
                $data[$k]['child_count'] = $child_update_count;
            } else {
                // nếu có nhóm con -> mới gọi lệnh lấy nhóm con
                if ($v['child_count'] > 0) {
                    $child_count = $this->count_all_taxonomy($v['taxonomy'], 0, $ops);
                    if ($child_count > 0) {
                        $child_term = $this->get_all_taxonomy($v['taxonomy'], 0, $ops);
                    }

                    // cập nhật lại tổng số nhóm nếu có sai số
                    if ($child_count != $v['child_count']) {
                        //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";

                        //
                        $this->base_model->update_multiple(
                            $this->table,
                            [
                                'child_count' => $child_count,
                                'child_last_count' => $current_time + $this->time_update_count,
                            ],
                            [
                                'term_id' => $v['term_id'],
                            ]
                        );
                    }
                }

                // thông báo kiểu dữ liệu trả về
                $data[$k]['child_count'] = 'cache';
            }
            $data[$k]['child_term'] = $child_term;
        }
        //print_r( $data );
        return $data;
    }

    // hàm này để giả lập dữ liệu theo kiểu wordpress

    public function get_categorys_by($prams = [], $ops = [])
    {
        $prams = $this->sync_term_parms($prams, $ops);
        //print_r( $prams );

        //
        return $this->get_all_taxonomy(TaxonomyType::POSTS, $prams['term_id'], $prams);
    }

    // lấy chi tiết 1 term theo ID
    public function get_term_by_id($id, $taxonomy = 'category', $get_meta = true, $limit = 1, $select_col = '*')
    {
        $data = $this->get_taxonomy(
            [
                'term_id' => $id,
                'taxonomy' => $taxonomy,
            ],
            [
                'limit' => $limit,
                'select_col' => $select_col,
            ]
        );

        //
        if ($get_meta === true && !empty($data)) {
            $data = $this->terms_meta_post([$data]);
            return $data[0];
        }

        //
        return $data;
    }

    // lấy chi tiết 1 term theo slug
    public function get_term_by_slug($slug, $taxonomy = 'category', $get_meta = true, $limit = 1, $select_col = '*', $lang_key = '', $ops = [])
    {
        //
        $where = [
            // 'slug' => $slug,
            'taxonomy' => $taxonomy,
            'is_deleted' => DeletedStatus::FOR_DEFAULT,
            'lang_key' => $lang_key != '' ? $lang_key : LanguageCost::lang_key(),
        ];
        if ($slug != '') {
            $where['slug'] = $slug;
        }
        $filter = [
            'limit' => $limit,
            'select_col' => $select_col,
        ];
        if (isset($ops['where'])) {
            foreach ($ops['where'] as $k => $v) {
                $where[$k] = $v;
            }
        }
        if (isset($ops['filter'])) {
            foreach ($ops['filter'] as $k => $v) {
                $filter[$k] = $v;
            }
        }

        //
        $data = $this->get_taxonomy($where, $filter);

        //
        if ($get_meta === true && !empty($data)) {
            $data = $this->terms_meta_post([$data]);
            return $data[0];
        }

        //
        return $data;
    }

    /**
     * Trả về danh sách controller theo từng taxonomy
     **/
    public function controllerByType()
    {
        return TaxonomyType::controllerList();
    }
}
