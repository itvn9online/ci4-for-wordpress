<?php

namespace App\Models;

// Libraries
use App\Libraries\LanguageCost;
use App\Libraries\DeletedStatus;
use App\Libraries\TaxonomyType;

//
class Term extends TermBase
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function sync_term_data($data)
    {
        // tạo short shortslug nếu có -> dùng để order khi select
        if (isset($data['term_shortname'])) {
            if ($data['term_shortname'] != '') {
                $data['term_shortslug'] = $this->base_model->_eb_non_mark_seo($data['term_shortname']);
            } else {
                $data['term_shortslug'] = '';
            }
        }
        // đặt giá trị này để khởi tạo lại permalink
        //$data['term_permalink'] = '';
        //$data['updated_permalink'] = 0;

        //
        foreach ($data as $k => $v) {
            //echo gettype($v) . PHP_EOL;

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
        if ($slug != '') {
            $where['slug'] = $slug;
        }

        //
        $post_cat = $this->get_taxonomy($where);
        //print_r( $post_cat );

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
                //echo  __CLASS__ . ':' . __LINE__ . ':' . debug_backtrace()[1]['class'] . ':' . debug_backtrace()[1]['function'] . '<br>' . PHP_EOL;
                echo 'Auto create taxonomy: ' . $slug . ' (' . $taxonomy . ') <br>' . PHP_EOL;
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
                    //echo  __CLASS__ . ':' . __LINE__ . ':' . debug_backtrace()[1]['class'] . ':' . debug_backtrace()[1]['function'] . '<br>' . PHP_EOL;
                    return $this->get_cat_post($slug, $post_type, $taxonomy, false);
                }
                // nếu tồn tại rồi thì báo đã tồn tại
                else if ($result_id < 0) {
                    //echo  __CLASS__ . ':' . __LINE__ . ':' . debug_backtrace()[1]['class'] . ':' . debug_backtrace()[1]['function'] . '<br>' . PHP_EOL;
                    die('EXIST auto create new terms #' . $taxonomy . ':' . __CLASS__ . ':' . __LINE__);
                }
                die('ERROR auto create new terms #' . $taxonomy . ':' . __CLASS__ . ':' . __LINE__);
            } else {
                //echo  __CLASS__ . ':' . __LINE__ . ':' . debug_backtrace()[1]['class'] . ':' . debug_backtrace()[1]['function'] . '<br>' . PHP_EOL;
                die('AUTO INSERT new terms has DISABLE #' . $taxonomy . ':' . __CLASS__ . ':' . __LINE__);
            }
        }
        //print_r( $post_cat );

        //
        return $post_cat;
    }

    /*
     * return_exist -> trả về ID của term khi gặp trùng lặp slug
     */
    public function insert_terms($data, $taxonomy, $return_exist = false, $data_meta = [])
    {
        // các dữ liệu mặc định
        $default_data = [
            'last_updated' => date(EBE_DATETIME_FORMAT),
            'lang_key' => LanguageCost::lang_key(),
        ];
        //print_r( $default_data );

        //
        if (!isset($data['slug']) || $data['slug'] == '') {
            $data['slug'] = $data['name'];
        }
        if ($data['slug'] != '') {
            $data['slug'] = $this->base_model->_eb_non_mark_seo($data['slug']);
            $data['slug'] = str_replace('.', '-', $data['slug']);
            $data['slug'] = str_replace('--', '-', $data['slug']);
            //print_r( $data );
            //die( __CLASS__ . ':' . __LINE__ );

            //
            //$check_term_exist = $this->get_term_by_id( 1, $taxonomy, false );
            //print_r( $check_term_exist );
            /*
             * xem term này đã có chưa
             */
            // mặc định là có rồi
            $has_slug = true;
            // chạy vòng lặp để kiểm tra, nếu có rồi thì thêm số vào sau để tránh trùng lặp
            for ($i = 0; $i < 10; $i++) {
                $by_slug = $data['slug'];
                if ($i > 0) {
                    $by_slug .= $i;
                }
                //echo 'by_slug: ' . $by_slug . '<br>' . PHP_EOL;
                $check_term_exist = $this->get_term_by_slug($by_slug, $taxonomy, false, 1, 'term_id', isset($data['lang_key']) ? $data['lang_key'] : '');
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
        //echo $result_id . '<br>' . PHP_EOL;

        if ($result_id !== false) {
            $data_insert = $data;
            $data_insert['term_taxonomy_id'] = $result_id;
            $data_insert['term_id'] = $result_id;
            $data_insert['taxonomy'] = $taxonomy;
            $data_insert['description'] = 'Auto create nav menu taxonomy';

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
        if (isset($data['slug'])) {
            if ($data['slug'] == '') {
                $data['slug'] = $data['name'];
            }
            if ($data['slug'] != '') {
                $data['slug'] = $this->base_model->_eb_non_mark_seo($data['slug']);
                $data['slug'] = str_replace('.', '-', $data['slug']);
                $data['slug'] = str_replace('--', '-', $data['slug']);
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
        //print_r( $data );
        //die( __CLASS__ . ':' . __LINE__ );


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
                'selectCount' => 'object_id',
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
        //print_r( $where );

        // đồng bộ dữ liệu trước khi update
        $data = $this->sync_term_data($data);

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

        /*
         * v2 -> Xóa hết đi add lại
         */
        //return $this->insert_v2_meta_term( $meta_data, $term_id );

        /*
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
                    //echo 'DELETE ' . $k . ' ' . $v . '<br>' . PHP_EOL;

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

    // trả về mảng dữ liệu để json data -> auto select category bằng js cho nhẹ -> lấy quá nhiều dữ liệu dễ bị json lỗi
    public function get_json_taxonomy($taxonomy = 'category', $term_id = 0, $ops = [], $in_cache = '')
    {
        // nếu không có cache key -> kiểm tra điều kiện tạo key
        if ($in_cache == '') {
            if ($term_id === 0 && empty($ops)) {
                $in_cache = $taxonomy;
            }
        }
        //echo 'in_cache: ' . $in_cache . '<br>' . PHP_EOL;

        // cố định loại cột cần lấy
        $ops['select_col'] = 'term_id, name, term_shortname, slug, term_group, count, parent, taxonomy, child_count, child_last_count, term_permalink, term_avatar, term_favicon, term_status, lang_key';
        //$ops[ 'select_col' ] = '*';

        //
        return str_replace('\'', '\\\'', json_encode($this->get_all_taxonomy($taxonomy, $term_id, $ops, $in_cache)));
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

    /*
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
            if ($term_id === 0 && empty($ops)) {
                $in_cache = $taxonomy;
            }
        }

        //
        if (!isset($ops['lang_key']) || $ops['lang_key'] == '') {
            $ops['lang_key'] = LanguageCost::lang_key();
        }

        //
        if ($in_cache != '') {
            $in_cache = __FUNCTION__ . '-' . $in_cache . '-' . $ops['lang_key'];
            //echo $in_cache . '<br>' . PHP_EOL;

            // xóa cache nếu có yêu cầu
            if ($clear_cache === true) {
                return $this->base_model->cache->delete($in_cache);
            }

            //
            $cache_value = $this->base_model->scache($in_cache);
            //print_r( $cache_value );
            //var_dump( $cache_value );

            // có cache thì trả về
            if ($cache_value !== NULL) {
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
                //echo $ops[ 'by_is_deleted' ] . '<br>' . PHP_EOL;
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
        $post_cat = $this->base_model->select(
            $ops['select_col'],
            WGR_TERM_VIEW,
            $where,
            array(
                //'where_in' => isset( $ops[ 'where_in' ] ) ? $ops[ 'where_in' ] : [],
                'or_like' => $where_or_like,
                'order_by' => $ops['order_by'],
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                'offset' => $ops['offset'],
                'limit' => $ops['limit']
            )
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
                //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
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
                //die( __CLASS__ . ':' . __LINE__ );
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

        //
        return $this->base_model->select(
            $ops['select_col'],
            WGR_TERM_VIEW,
            $where,
            array(
                'where_in' => $ops['where_in'],
                'order_by' => array(
                    'term_id' => 'DESC'
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 2,
                'limit' => $ops['limit']
            )
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
            if ($v['child_count'] === NULL) {
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
                        //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;

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
    function insert_term_relationships($post_id, $list, $term_order = 0)
    {
        $list = explode(',', $list);

        // xóa các term_relationships cũ
        $this->base_model->delete($this->relaTable, 'object_id', $post_id);

        // insert cái mới
        foreach ($list as $term_id) {
            $term_id = trim($term_id);

            if ($term_id != '' && $term_id > 0) {
                $this->base_model->insert(
                    $this->relaTable,
                    [
                        'object_id' => $post_id,
                        'term_taxonomy_id' => $term_id,
                        'term_order' => $term_order,
                    ]
                );

                // tính tổng bài viết theo từng term
                $count_post_term = $this->base_model->select(
                    'COUNT(object_id) AS c',
                    $this->relaTable,
                    array(
                        // WHERE AND OR
                        'term_taxonomy_id' => $term_id,
                    ),
                    array(
                        'selectCount' => 'object_id',
                        // hiển thị mã SQL để check
                        //'show_query' => 1,
                        // trả về câu query để sử dụng cho mục đích khác
                        //'get_query' => 1,
                        //'offset' => 2,
                        //'limit' => 3

                    )
                );
                //print_r( $count_post_term );

                // cập nhật lại tổng số bài viết cho term
                $this->base_model->update_multiple(
                    $this->taxTable,
                    [
                        //'count' => $count_post_term[ 0 ][ 'c' ]
                        'count' => $count_post_term[0]['object_id']
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

    // chỉ trả về link admin của 1 term
    function get_admin_permalink($taxonomy = '', $id = 0, $controller_slug = 'terms')
    {
        //$url = base_url( 'admin/' . $controller_slug . '/add' ) . '?taxonomy=' . $taxonomy;
        $url = base_url('admin/' . $controller_slug . '/add');
        if ($id > 0) {
            //$url .= '&id=' . $id;
            $url .= '?id=' . $id;
        }
        return $url;
    }

    // thường dùng trong view -> in ra link admin của 1 term
    public function admin_permalink($taxonomy = '', $id = 0, $controller_slug = 'terms')
    {
        echo $this->get_admin_permalink($taxonomy, $id, $controller_slug);
    }

    // kiểm tra url đã chuẩn chưa, chưa thì redirect về url chuẩn
    public function check_canonical($slug, $data)
    {
        // nếu slug trống
        if (
            $slug == '' ||
            // hoặc đúng là slug
            $slug == $data['slug'] ||
            // hoặc kiểu URL có .html, .html, .etc...
            strpos($slug, $data['slug'] . '.') !== false
        ) {
            // thì cho qua
            return true;
        }
        // không thì redirect về URL chuẩn
        $redirect_to = $this->get_full_permalink($data);
        //die( $redirect_to );
        if (strpos($redirect_to, '?') === false) {
            $redirect_to .= '?';
        } else {
            $redirect_to .= '&';
        }
        $redirect_to .= 'canonical=server&uri=' . urlencode($_SERVER['REQUEST_URI']);

        //
        header('HTTP/1.1 301 Moved Permanently');
        die(header('Location: ' . $redirect_to, TRUE, 301));
        //die( __CLASS__ . ':' . __LINE__ );
    }

    /**
     * Kiểm tra dữ liệu đầu vào trước khi update post permalink -> tránh lỗi
     **/
    public function before_term_permalink($data)
    {
        // nếu có đủ các thông số còn thiếu thì tiến hành cập nhật permalink
        foreach ([
            'term_id',
            'slug',
            'taxonomy',
            'lang_key',
        ] as $k) {
            if (!isset($data[$k])) {
                return false;
            }
        }
        return $this->update_term_permalink($data);
    }
    /**
     * Update update permalink định kỳ
     **/
    public function update_term_permalink($data, $base_url = '')
    {
        // không có thì mới tạo và update vào db
        if ($data['taxonomy'] == TaxonomyType::POSTS) {
            $url = WGR_CATEGORY_PERMALINK;
            /*
        } else if ($data['taxonomy'] == TaxonomyType::BLOGS) {
            $url = WGR_BLOGS_PERMALINK;
            */
        } else if ($data['taxonomy'] == TaxonomyType::PROD_CATS) {
            $url = WGR_PRODS_PERMALINK;
            /*
        } else if ($data['taxonomy'] == TaxonomyType::TAGS) {
            $url = WGR_TAGS_PERMALINK;
        } else if ($data['taxonomy'] == TaxonomyType::OPTIONS) {
            $url = WGR_OPTIONS_PERMALINK;
        } else if ($data['taxonomy'] == TaxonomyType::BLOG_TAGS) {
            $url = WGR_BLOG_TAGS_PERMALINK;
        } else if ($data['taxonomy'] == TaxonomyType::PROD_TAGS) {
            $url = WGR_PROD_TAGS_PERMALINK;
            */
        } else if (isset(WGR_CUS_TAX_PERMALINK[$data['taxonomy']])) {
            $url = WGR_CUS_TAX_PERMALINK[$data['taxonomy']];
        } else {
            $url = WGR_TAXONOMY_PERMALINK;
        }

        // thêm prefix cho url -> hỗ trợ đa ngôn ngữ sub-folder
        if (SITE_LANGUAGE_SUB_FOLDER == true && $data['lang_key'] != SITE_LANGUAGE_DEFAULT) {
            $url = $data['lang_key'] . '/' . $url;
        }

        //
        foreach ([
            //'category_base' => CATEGORY_BASE_URL,
            'term_id' => $data['term_id'],
            'slug' => $data['slug'],
            'taxonomy' => $data['taxonomy'],
        ] as $k => $v) {
            $url = str_replace('%' . $k . '%', $v, $url);
        }

        // update vào db để sau còn tái sử dụng -> nhẹ server
        $this->base_model->update_multiple(
            'terms',
            [
                // xóa cắp dấu // để tránh trường hợp gặp segment trống
                'term_permalink' => str_replace('//', '/', $url),
                // cập nhật giãn cách update lại permalink -> khi quá thời gian này sẽ tiến hành cập nhật permalink mới
                'updated_permalink' => time() + 3600,
            ],
            [
                'term_id' => $data['term_id'],
            ],
            [
                // hiển thị mã SQL để check
                //'show_query' => 1,
            ]
        );

        //
        return $base_url . $url;
    }
    // trả về url với đầy đủ tên miền
    public function get_full_permalink($data)
    {
        return $this->get_term_permalink($data, DYNAMIC_BASE_URL);
    }
    // trả về url của 1 term
    public function get_the_permalink($data, $base_url = '')
    {
        return $this->get_term_permalink($data, $base_url);
    }
    public function get_term_permalink($data, $base_url = '')
    {
        //print_r( $data );

        // chức năng này sẽ để 1 thời gian sau đó comment lại
        /*
        if (!isset($data['updated_permalink'])) {
            //print_r($data);
            die(__FUNCTION__ . ' Updated permalink! ' . __CLASS__ . ':' . __LINE__);
        }
        */

        // sử dụng permalink có sẵn trong data
        /*
        if ($data['updated_permalink'] > time() && $data['term_permalink'] != '') {
            return $base_url . $data['term_permalink'];
        }
        */
        return $base_url . $data['term_permalink'];

        //
        //return $base_url . '?cat=' . $data[ 'term_id' ] . '&taxonomy=' . $data[ 'taxonomy' ] . '&slug=' . $data[ 'slug' ];
        //return $base_url . 'c/' . $data['taxonomy'] . '/' . $data['term_id'] . '/' . $data['slug'];
    }
    // thường dùng trong view -> in ra link admin của 1 term
    public function the_term_permalink($data)
    {
        echo $this->get_term_permalink($data);
    }
    public function the_permalink($data)
    {
        // gọi tên đầy đủ để dễ lọc function giữa post với term
        $this->the_term_permalink($data);
    }

    // tạo html trong này -> do trong view không viết được tham số $this để tạo vòng lặp đệ quy
    function list_html_view($data, $gach_ngang = '', $is_deleted = '', $controller_slug = 'terms')
    {
        $tmp = '<tr>
            <td>&nbsp;</td>
            <td><a href="%get_admin_permalink%">' . $gach_ngang . ' %name% <i class="fa fa-edit"></i></a></td>
            <td><a href="%view_url%" target="_blank">%slug% <i class="fa fa-eye"></i></a></td>
            <td class="d-none show-if-ads-type">%custom_size%</td>
            <td>&nbsp;</td>
            <td>%lang_key%</td>
            <td>%count%</td>
            <td class="text-center">%action_link%</td>
        </tr>';

        //
        $for_redirect = '';
        if ($is_deleted != '') {
            $for_redirect .= '&is_deleted=' . $is_deleted;
        }

        //
        $str = '';
        foreach ($data as $k => $v) {
            print_r($v);

            //
            $node = $tmp;

            //
            if ($v['is_deleted'] == DeletedStatus::DELETED) {
                $action_link = '<a href="admin/' . $controller_slug . '/restore?id=%term_id%' . $for_redirect . '" onClick="return click_a_restore_record();" target="target_eb_iframe" class="bluecolor"><i class="fa fa-undo"></i></a>';
            } else {
                $action_link = '<a href="admin/' . $controller_slug . '/term_status?id=%term_id%' . $for_redirect . '" target="target_eb_iframe" data-id="%term_id%" data-status="%term_status%" class="record-status-color"><i class="fa fa-eye"></i></a> &nbsp; ';

                $action_link .= '<a href="admin/' . $controller_slug . '/delete?id=%term_id%' . $for_redirect . '" onClick="return click_a_delete_record();" target="target_eb_iframe" class="redcolor"><i class="fa fa-trash"></i></a>';
            }
            $node = str_replace('%action_link%', $action_link, $node);

            //
            foreach ($v as $key => $val) {
                if ($key == 'term_meta') {
                    //print_r( $val );
                    foreach ($val as $key_val => $val_val) {
                        $node = str_replace('%' . $key_val . '%', $val_val, $node);
                    }
                } else if (!is_array($val)) {
                    $node = str_replace('%' . $key . '%', $val, $node);
                }
            }
            $node = str_replace('%get_admin_permalink%', $this->get_admin_permalink($v['taxonomy'], $v['term_id'], $controller_slug), $node);
            $node = str_replace('%view_url%', $this->get_term_permalink($v), $node);

            //
            $str .= $node;

            //
            if (isset($v['child_term'])) {
                $str .= $this->list_html_view($v['child_term'], $gach_ngang . ' &#8212;', $is_deleted);
            }
        }

        //
        return $str;
    }

    // tự động tạo slider nếu có
    public function get_the_slider($taxonomy_slider, $second_slider = '')
    {
        //print_r( $taxonomy_slider );
        if (empty($taxonomy_slider)) {
            return '';
        }

        // -> chạy vòng lặp để tìm slider theo danh mục gần nhất -> con không có thì tìm cha
        foreach ($taxonomy_slider as $slider) {
            if (isset($slider['term_meta']['taxonomy_auto_slider']) && $slider['term_meta']['taxonomy_auto_slider'] == 'on') {
                //echo 'taxonomy_auto_slider';

                $slug = $slider['slug'] . '-' . $slider['taxonomy'] . '-' . $slider['term_id'];
                return $slug;

                /*
                return $this->post_model->get_the_ads( $slug, 0, [
                'add_class' => 'taxonomy-auto-slider'
                ] );
                */
                break;
            }
        }

        // đến đây vẫn không có -> tìm slider thứ cấp (slider dùng chung cho cả website)
        /*
        if ( $second_slider != '' ) {
        return $this->post_model->get_the_ads( $second_slider, 0, [
        'add_class' => 'taxonomy-auto-slider'
        ] );
        }
        */

        //
        return '';
    }
    public function the_slider($data, $second_slider = '')
    {
        echo $this->get_the_slider($data, $second_slider);
    }

    // category -> categories -> categorys
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
    public function get_term_by_slug($slug, $taxonomy = 'category', $get_meta = true, $limit = 1, $select_col = '*', $lang_key = '')
    {
        $data = $this->get_taxonomy(
            [
                'slug' => $slug,
                'taxonomy' => $taxonomy,
                'is_deleted' => DeletedStatus::FOR_DEFAULT,
                'lang_key' => $lang_key != '' ? $lang_key : LanguageCost::lang_key(),
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
}
