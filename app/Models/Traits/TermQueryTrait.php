<?php

namespace App\Models\Traits;

use App\Libraries\LanguageCost;
use App\Libraries\DeletedStatus;
use App\Libraries\TaxonomyType;
use App\Libraries\PostType;

//
trait TermQueryTrait
{
    protected function sync_term_data($data)
    {
        // tạo short shortslug nếu có -> dùng để order khi select
        if (isset($data['term_shortname'])) {
            if ($data['term_shortname'] != '') {
                $data['term_shortslug'] = $this->base_model->slug_non_mark_seo($data['term_shortname']);
            } else {
                $data['term_shortslug'] = '';
            }
        }
        // đặt giá trị này để khởi tạo lại permalink
        //$data['term_permalink'] = '';
        //$data['updated_permalink'] = 0;

        //
        foreach ($data as $k => $v) {
            //echo gettype($v) . "\n";

            //
            if (in_array(gettype($v), [
                'string'
            ])) {
                $data[$k] = trim($v);
            }
        }
        return $data;
    }

    // lấy post theo dạng tương tự wordpress -> nếu không có -> tự động tạo mới
    public function get_cat_post($slug, $post_type = 'post', $taxonomy = 'category', $auto_insert = true, $ops = [])
    {
        if (!isset($ops['lang_key']) || $ops['lang_key'] == '') {
            $ops['lang_key'] = LanguageCost::lang_key();
            //$ops['lang_key'] = $this->base_model->lang_key;
        }

        //
        $where = [
            // các kiểu điều kiện where
            'lang_key' => $ops['lang_key'],
            'is_deleted' => DeletedStatus::FOR_DEFAULT,
            'taxonomy' => $taxonomy
        ];
        if (!empty($slug)) {
            if (is_numeric($slug)) {
                $where['term_id'] = $slug;
                // ko tự động khởi tạo khi select theo term_id
                $auto_insert = false;
            } else {
                $where['slug'] = $slug;
            }
        }
        // print_r($where);

        //
        $post_cat = $this->get_taxonomy($where);
        // print_r($post_cat);

        // nếu không có -> insert luôn 1 nhóm mới
        if (empty($post_cat)) {
            if ($auto_insert === true) {
                // lấy thêm các tham số của nhóm ngôn ngữ chính nếu có
                if ($ops['lang_key'] != LanguageCost::default_lang()) {
                    $cat_primary_data = $this->get_cat_post(
                        $slug,
                        $post_type,
                        $taxonomy,
                        false,
                        [
                            'lang_key' => LanguageCost::default_lang(),
                        ]
                    );
                    //print_r( $cat_primary_data );
                    if (!empty($cat_primary_data)) {
                        $cat_primary_data = $this->terms_meta_post([$cat_primary_data]);
                        $cat_primary_data = $cat_primary_data[0];
                        //print_r( $cat_primary_data );

                        // nếu có -> lấy meta của nó để nhân bản
                        if (isset($cat_primary_data['term_meta'])) {
                            $_POST['term_meta'] = $cat_primary_data['term_meta'];
                        }
                    }
                    //die( 'hjdgdgd gd' );
                }

                //
                //echo  __CLASS__ . ':' . __LINE__ . ':' . debug_backtrace()[1]['class'] . ':' . debug_backtrace()[1]['function'] . '<br>' . "\n";
                echo 'Auto create taxonomy: ' . $slug . ' (' . $taxonomy . ') <br>' . "\n";
                $result_id = $this->insert_terms(
                    [
                        'name' => str_replace('-', ' ', $slug),
                        'slug' => $slug,
                        'lang_key' => $ops['lang_key'],
                    ],
                    $taxonomy
                );

                //
                if ($result_id > 0) {
                    //echo  __CLASS__ . ':' . __LINE__ . ':' . debug_backtrace()[1]['class'] . ':' . debug_backtrace()[1]['function'] . '<br>' . "\n";
                    return $this->get_cat_post($slug, $post_type, $taxonomy, false);
                }
                // nếu tồn tại rồi thì báo đã tồn tại
                else if ($result_id < 0) {
                    //echo  __CLASS__ . ':' . __LINE__ . ':' . debug_backtrace()[1]['class'] . ':' . debug_backtrace()[1]['function'] . '<br>' . "\n";
                    die('EXIST auto create new terms #' . $taxonomy . ':' . __CLASS__ . ':' . __LINE__);
                }
                die('ERROR auto create new terms #' . $taxonomy . ':' . __CLASS__ . ':' . __LINE__);
            } else {
                //echo  __CLASS__ . ':' . __LINE__ . ':' . debug_backtrace()[1]['class'] . ':' . debug_backtrace()[1]['function'] . '<br>' . "\n";
                die('AUTO INSERT new terms has DISABLE #' . $taxonomy . ':' . __CLASS__ . ':' . __LINE__);
            }
        }
        //print_r( $post_cat );

        //
        return $post_cat;
    }

    /**
     * return_exist -> trả về ID của term khi gặp trùng lặp slug
     */
    public function insert_terms($data, $taxonomy, $return_exist = false, $data_meta = [], $check_exist = true)
    {
        // các dữ liệu mặc định
        $default_data = [
            'term_date' => date(EBE_DATETIME_FORMAT),
            'lang_key' => LanguageCost::lang_key(),
            'created_source' => implode(' - ', [
                // isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null,
                isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null,
                isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null,
                // isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null,
            ]),
        ];
        $default_data['last_updated'] = $default_data['term_date'];
        //print_r( $default_data );

        //
        if (!isset($data['slug']) || $data['slug'] == '') {
            $data['slug'] = $data['name'];
        }
        if ($data['slug'] != '') {
            $data['slug'] = $this->base_model->slug_non_mark_seo($data['slug']);
            //print_r( $data );
            //die( __CLASS__ . ':' . __LINE__ );

            //
            // $check_term_exist = $this->get_term_by_id(1, $taxonomy, false);
            //print_r( $check_term_exist );
            /**
             * xem term này đã có chưa
             */
            if ($check_exist === true) {
                // mặc định là có rồi
                $has_slug = true;
                // chạy vòng lặp để kiểm tra, nếu có rồi thì thêm số vào sau để tránh trùng lặp
                for ($i = 0; $i < 10; $i++) {
                    $by_slug = $data['slug'];
                    if ($i > 0) {
                        $by_slug .= $i;
                    }
                    //echo 'by_slug: ' . $by_slug . '<br>' . "\n";
                    $check_term_exist = $this->get_term_by_slug(
                        $by_slug,
                        $taxonomy,
                        // get_meta
                        false,
                        // limit
                        1,
                        // select_col
                        'term_id',
                        isset($data['lang_key']) ? $data['lang_key'] : ''
                    );
                    //print_r( $check_term_exist );
                    //die( __CLASS__ . ':' . __LINE__ );

                    // chưa có thì bỏ qua việc kiểm tra
                    if (empty($check_term_exist)) {
                        $data['slug'] = $by_slug;

                        // xác nhận slug này chưa được sử dụng
                        $has_slug = false;

                        break;
                    }
                    // nếu có rồi mà có kèm lệnh hủy thì trả về data luôn
                    else if ($return_exist === true) {
                        return $check_term_exist['term_id'];
                    }
                    // không thì for tiếp để thêm số vào slug -> tránh trùng lặp
                }
                //var_dump( $has_slug );
                //print_r( $data );
                if ($has_slug === true) {
                    return -1;
                }
            }
            //return false;
            //die( __CLASS__ . ':' . __LINE__ );
        }
        foreach ($default_data as $k => $v) {
            if (!isset($data[$k])) {
                $data[$k] = $v;
            }
        }
        // đồng bộ dữ liệu trước khi insert
        $data = $this->sync_term_data($data);
        //print_r( $data );
        //die( __CLASS__ . ':' . __LINE__ );

        //
        $result_id = $this->base_model->insert($this->table, $data, true);
        //echo $result_id . '<br>' . "\n";

        if ($result_id !== false) {
            $data_insert = $data;
            $data_insert['term_taxonomy_id'] = $result_id;
            $data_insert['term_id'] = $result_id;
            $data_insert['taxonomy'] = $taxonomy;
            $data_insert['source_count'] = 'Auto create taxonomy ' . $taxonomy . ' in ' . $_SERVER['REQUEST_URI'];
            // if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
            //     $data_insert['source_count'] .= ' form ' . $_SERVER['HTTP_REFERER'];
            // }
            // if (isset($_SERVER['HTTP_USER_AGENT'])) {
            //     $data_insert['source_count'] .= ' with ' . $_SERVER['HTTP_USER_AGENT'];
            // }
            // $data_insert['description'] = $data_insert['source_count'];

            //
            $this->base_model->insert($this->taxTable, $data_insert, true);

            //
            if (is_numeric($result_id) && $result_id > 0) {
                $this->before_term_permalink($this->select_term($result_id, [
                    //'taxonomy' => $taxonomy,
                ]));
            }

            // insert/ update meta term
            if (!empty($data_meta)) {
                $this->insert_meta_term($data_meta, $result_id);
            }
            //
            else if (isset($_POST['term_meta'])) {
                $this->insert_meta_term($_POST['term_meta'], $result_id);
            }

            // xóa cache
            $this->delete_cache_taxonomy($taxonomy);
        }
        return $result_id;
    }

    function update_terms($term_id, $data, $taxonomy = '', $ops = [])
    {
        // var_dump($term_id);
        // print_r($data);
        // die(__CLASS__ . ':' . __LINE__);

        // tiêu đề gắn thêm khi post bị xóa
        $str_trash = DeletedStatus::FOR_TRASH;
        $str_time = date('dHis');

        //
        if (isset($data['is_deleted']) && in_array($data['is_deleted'], [
            DeletedStatus::DELETED,
            DeletedStatus::REMOVED
        ])) {
            // print_r($data);

            //
            if (!isset($data['slug']) || $data['slug'] == '') {
                $the_slug = $this->base_model->select(
                    'slug',
                    $this->table,
                    array(
                        // các kiểu điều kiện where
                        'term_id' => $term_id,
                    ),
                    array(
                        // hiển thị mã SQL để check
                        // 'show_query' => 1,
                        // trả về câu query để sử dụng cho mục đích khác
                        //'get_query' => 1,
                        // trả về COUNT(column_name) AS column_name
                        //'selectCount' => 'ID',
                        // trả về tổng số bản ghi -> tương tự mysql num row
                        //'getNumRows' => 1,
                        //'offset' => 0,
                        'limit' => 1
                    )
                );
                // print_r($slug);
                if (empty($the_slug)) {
                    return -1;
                }
                $data['slug'] = $the_slug['slug'];
            }

            // xóa trash đi trước khi non-mark
            $data['slug'] = explode($str_trash, $data['slug'])[0];
            // thêm tham số trash vào slug
            $data['slug'] = $this->base_model->slug_non_mark_seo($data['slug'], $str_trash . $str_time);
            // print_r($data);
        } else if (isset($data['slug']) && strpos($data['slug'], $str_trash) === false) {
            // print_r($data);
            if ($data['slug'] == '') {
                $data['slug'] = $data['name'];
            }
            if ($data['slug'] != '') {
                // bỏ tham số trash khỏi slug
                // $data['slug'] = explode($str_trash, $data['slug'])[0];
                $data['slug'] = $this->base_model->slug_non_mark_seo($data['slug']);
                //print_r( $data );

                // kiểm tra lại slug trước khi update
                $check_term_exist = $this->get_taxonomy(
                    [
                        'term_id !=' => $term_id,
                        'slug' => $data['slug'],
                        'taxonomy' => $taxonomy,
                        'is_deleted' => DeletedStatus::FOR_DEFAULT,
                        'lang_key' => isset($data['lang_key']) ? $data['lang_key'] : LanguageCost::lang_key(),
                    ]
                );
                //print_r( $check_term_exist );
                //die( __CLASS__ . ':' . __LINE__ );

                //
                if (!empty($check_term_exist)) {
                    //print_r($check_term_exist);

                    // đưa ra cảnh báo ngay nếu có kiêu cầu
                    if (isset($ops['alert'])) {
                        //print_r( $check_term_exist );

                        //
                        $this->base_model->alert('ERROR! lỗi cập nhật danh mục... Có thể slug đã được sử dụng', 'error');
                    }

                    //
                    return -1;
                }
            }
        }
        if (!isset($data['last_updated']) || $data['last_updated'] == '') {
            $data['last_updated'] = date(EBE_DATETIME_FORMAT);
        }

        // tính tổng số term con của term đang được cập nhật
        /*
        $child_term = $this->base_model->select(
            'COUNT(term_id) AS c',
            WGR_TERM_VIEW,
            array(
                'parent' => $term_id,
                //'taxonomy' => $taxonomy,
                'is_deleted' => DeletedStatus::FOR_DEFAULT,
            ),
            array(
                //'selectCount' => 'term_id',
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 2,
                //'limit' => 3
            )
        );
        //print_r($child_term);

        //
        $data['child_count'] = $child_term[0]['c'];
        //$data['child_count'] = $child_term[0]['term_id'];
        $data['child_last_count'] = time() + $this->time_update_count;
        */
        $data['child_last_count'] = 0;

        //
        //print_r( $data );
        // cập nhật quan hệ cha con
        if (isset($data['parent'])) {
            // mặc định level của nó là 0
            $data['term_level'] = 0;

            // nếu nhóm này có cha
            if ($data['parent'] * 1 > 0) {
                // -> lấy level của cha
                $parent_level = $this->base_model->select(
                    'term_level',
                    $this->taxTable,
                    array(
                        'term_id' => $term_id,
                    ),
                    array(
                        // hiển thị mã SQL để check
                        //'show_query' => 1,
                        // trả về câu query để sử dụng cho mục đích khác
                        //'get_query' => 1,
                        //'offset' => 2,
                        'limit' => 1
                    )
                );
                //print_r( $parent_level );

                //
                if (!empty($parent_level)) {
                    $data['term_level'] = $parent_level['term_level'];
                }
            }

            // đặt các nhóm con của nó lên 1 level
            $this->base_model->update_multiple(
                $this->taxTable,
                [
                    'term_level' => $data['term_level'] + 1
                ],
                [
                    'parent' => $term_id,
                ],
                [
                    'debug_backtrace' => debug_backtrace()[1]['function']
                ]
            );
        }
        // print_r($data);
        // die(__CLASS__ . ':' . __LINE__);


        //
        /*
        $count_post_term = $this->base_model->select(
            'COUNT(object_id) AS c',
            $this->relaTable,
            array(
                // WHERE AND OR
                'term_taxonomy_id' => $term_id,
            ),
            array(
                // 'selectCount' => 'object_id',
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 2,
                //'limit' => 3
            )
        );
        print_r($count_post_term);
        $data['count'] = $count_post_term[0]['object_id'];
        */

        //
        $where = [
            'term_id' => $term_id,
        ];
        // print_r($where);

        // đồng bộ dữ liệu trước khi update
        $data = $this->sync_term_data($data);
        // print_r($data);
        // die(__CLASS__ . ':' . __LINE__);

        //
        $result_update = $this->base_model->update_multiple(
            $this->table,
            $data,
            $where,
            [
                'debug_backtrace' => debug_backtrace()[1]['function']
            ]
        );

        // nếu có taxonomy -> update luôn cho bảng term_taxonomy
        if ($taxonomy != '') {
            $where = [
                'term_id' => $term_id,
                'taxonomy' => $taxonomy,
            ];
            //print_r( $where );
            $this->base_model->update_multiple(
                $this->taxTable,
                $data,
                $where,
                [
                    'debug_backtrace' => debug_backtrace()[1]['function']
                ]
            );

            // xóa cache
            $this->delete_cache_taxonomy($taxonomy);
        }
        //die( __CLASS__ . ':' . __LINE__ );

        //
        if (isset($_POST['term_meta'])) {
            $this->insert_meta_term($_POST['term_meta'], $term_id);
        }

        //
        if ($result_update === true) {
            $data['term_id'] = $term_id;
            $data['term_permalink'] = $this->before_term_permalink($data);
            //print_r($data);
        }

        //
        return $result_update;
    }

    // phiên bản xóa xong thêm -> không tối ứu
    function insert_v2_meta_term($meta_data, $term_id)
    {
        $this->base_model->delete($this->metaTable, 'term_id', $term_id);

        // add lại
        foreach ($meta_data as $k => $v) {
            if ($v != '') {
                $this->base_model->insert(
                    $this->metaTable,
                    [
                        'term_id' => $term_id,
                        'meta_key' => $k,
                        'meta_value' => $v,
                    ]
                );
            }
        }

        // done
        return true;
    }

    // thêm post meta
    function insert_meta_term($meta_data, $term_id, $clear_meta = true)
    {
        //print_r( $meta_data );
        if (!is_array($meta_data) || empty($meta_data)) {
            return false;
        }

        /**
         * v2 -> Xóa hết đi add lại
         */
        //return $this->insert_v2_meta_term( $meta_data, $term_id );

        /**
         * v1 -> chưa xử lý được các checkbox sau khi bị hủy
         * daidq (2021-12-14): đã xử lý được phần checkbox
         */
        // lấy toàn bộ meta của post này
        $meta_exist = $this->arr_meta_terms($term_id);
        //print_r( $meta_exist );
        //die( __CLASS__ . ':' . __LINE__ );

        // xem các meta nào không có trong lần update này -> XÓA
        if ($clear_meta === true) {
            foreach ($meta_exist as $k => $v) {
                if (!isset($meta_data[$k])) {
                    //echo 'DELETE ' . $k . ' ' . $v . '<br>' . "\n";

                    //
                    $this->base_model->delete_multiple(
                        $this->metaTable,
                        [
                            'term_id' => $term_id,
                            'meta_key' => $k,
                        ]
                    );
                }
            }
        }

        //
        $insert_meta = [];
        $update_meta = [];
        foreach ($meta_data as $k => $v) {
            // thêm vào mảng update nếu có rồi
            if (isset($meta_exist[$k])) {
                $update_meta[$k] = $v;
            }
            // thêm vào mảng insert nếu chưa có
            else if ($v != '') {
                $insert_meta[$k] = $v;
            }
        }

        // các meta chưa có thì insert
        //print_r( $insert_meta );
        foreach ($insert_meta as $k => $v) {
            $this->base_model->insert(
                $this->metaTable,
                [
                    'term_id' => $term_id,
                    'meta_key' => $k,
                    'meta_value' => $v,
                ]
            );
        }

        // các meta có rồi thì update
        //print_r( $update_meta );
        foreach ($update_meta as $k => $v) {
            $this->base_model->update_multiple(
                $this->metaTable,
                [
                    'meta_value' => $v,
                ],
                [
                    'term_id' => $term_id,
                    'meta_key' => $k,
                ]
            );
        }

        // cập nhật post meta vào cột của post để đỡ phải query nhiều
        $this->base_model->update_multiple(
            $this->table,
            [
                'term_meta_data' => json_encode($meta_data),
            ],
            [
                'term_id' => $term_id,
            ]
        );

        //
        //die( __CLASS__ . ':' . __LINE__ );
        return true;
    }


    function insert_term_relationships($post_id, $list, $term_order = 0)
    {
        $list = explode(',', $list);
        // print_r($list);

        // lấy các id đã có
        $exist_data = $this->base_model->select(
            'term_taxonomy_id',
            $this->relaTable,
            array(
                // các kiểu điều kiện where
                'object_id' => $post_id,
            ),
            array(
                // hiển thị mã SQL để check
                // 'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                // trả về COUNT(column_name) AS column_name
                //'selectCount' => 'ID',
                // trả về tổng số bản ghi -> tương tự mysql num row
                //'getNumRows' => 1,
                //'offset' => 0,
                'limit' => -1
            )
        );
        // print_r($exist_data);

        //
        $exist_ids = [];
        foreach ($exist_data as $v) {
            $exist_ids[] = $v['term_taxonomy_id'];
        }
        // print_r($exist_ids);

        // die(__CLASS__ . ':' . __LINE__);

        // xóa các term_relationships ko còn sử dụng
        $rm_ids = [];
        foreach ($exist_ids as $term_id) {
            // ko có trong loạt id mới thì xóa đi
            if (!in_array($term_id, $list)) {
                $rm_ids[] = $term_id;
            }
        }
        if (!empty($rm_ids)) {
            $this->base_model->delete_multiple($this->relaTable, [
                // WHERE
                'object_id' => $post_id,
            ], [
                'where_in' => array(
                    'term_taxonomy_id' => $rm_ids
                ),
                // hiển thị mã SQL để check
                // 'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                // 'get_query' => 1,
            ]);
        }

        // insert cái mới
        foreach ($list as $term_id) {
            $term_id = trim($term_id);
            if (empty($term_id) || $term_id < 1) {
                continue;
            }

            // nếu có rồi thì bỏ qua
            if (in_array($term_id, $exist_ids)) {
                // echo $term_id . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
                continue;
            }

            // chưa có thì insert
            $this->base_model->insert(
                $this->relaTable,
                [
                    'object_id' => $post_id,
                    'term_taxonomy_id' => $term_id,
                    'term_order' => $term_order,
                ]
            );

            // tính tổng bài viết theo từng term
            $data = $this->get_taxonomy(
                array(
                    // các kiểu điều kiện where
                    'term_id' => $term_id,
                    // 'is_deleted' => DeletedStatus::FOR_DEFAULT,
                    // 'lang_key' => $this->lang_key,
                    // 'taxonomy' => $taxonomy_type
                ),
                [
                    // 'show_query' => 1,
                    'limit' => 1,
                ]
            );
            // print_r($data);
            // continue;

            //
            if (!empty($data)) {
                $data['child_last_count'] = 0;
                $this->update_count_post_in_term($data);
            }

            // daidq: sử dụng hàm update_count_post_in_term cho thống nhất dữ liệu
            if (1 > 2) {
                $count_post_term = $this->base_model->select_count(
                    'object_id',
                    $this->relaTable,
                    array(
                        // WHERE AND OR
                        'term_taxonomy_id' => $term_id,
                    )
                );

                // cập nhật lại tổng số bài viết cho term
                $this->base_model->update_multiple(
                    $this->taxTable,
                    [
                        'count' => $count_post_term,
                        'source_count' => __CLASS__ . ':' . __FUNCTION__ . ':' . __LINE__,
                    ],
                    [
                        'term_taxonomy_id' => $term_id,
                        'term_id' => $term_id,
                    ],
                    [
                        'debug_backtrace' => debug_backtrace()[1]['function']
                    ]
                );
            }
        }
    }

}
