<?php

namespace App\Models;

// Libraries
use App\Libraries\PostType;
use App\Libraries\DeletedStatus;

//
class TermBase extends EbModel
{
    public $table = 'terms';
    public $primaryKey = 'term_id';

    public $metaTable = 'termmeta';
    //public $metaKey = 'meta_id';

    public $taxTable = 'term_taxonomy';
    public $taxKey = 'term_taxonomy_id';

    public $relaTable = 'term_relationships';
    public $relaKey = 'object_id';

    //
    public $time_update_last_count = 6 * 3600;
    public $time_update_count = 3600;

    //protected $primaryTaxonomy = 'category';

    public function __construct()
    {
        parent::__construct();
    }

    // vòng lặp đệ quy -> tạo option cho phần select của term
    public function term_add_child_option($data, $term_id = 0, $gach_ngang = '')
    {
        if (empty($data)) {
            return false;
        }
        //print_r( $data );
        //return false;

        //
        foreach ($data as $v) {
            //print_r( $v );
            //continue;
            if ($v['term_id'] == $term_id || $v['parent'] == $term_id) {
                continue;
            }
            echo '<option value="' . $v['term_id'] . '">' . $gach_ngang . $v['name'] . '</option>';

            //
            $this->term_add_child_option($v['child_term'], $term_id, $gach_ngang . '&#8212; ');
        }
    }

    public function terms_meta_post($data)
    {
        if (!isset($data[0]['term_meta_data'])) {
            return $data;
        }

        //
        //print_r( $data );
        foreach ($data as $k => $v) {
            //print_r( $v );

            // nếu không có dữ liệu của term meta
            if ($v['term_meta_data'] === NULL) {
                $term_meta_data = $this->arr_meta_terms($v['term_id']);
                //print_r( $term_meta_data );
                //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;

                //
                $this->base_model->update_multiple($this->table, [
                    'term_meta_data' => json_encode($term_meta_data),
                ], [
                    'term_id' => $v['term_id'],
                ]);

                // thông báo kiểu dữ liệu trả về
                $data[$k]['term_meta_data'] = 'query';
            } else {
                $term_meta_data = (array) json_decode($v['term_meta_data']);
                //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;

                // thông báo kiểu dữ liệu trả về
                $data[$k]['term_meta_data'] = 'cache';
            }
            $data[$k]['term_meta'] = $term_meta_data;
        }
        //print_r( $data );

        //
        return $data;
    }

    // trả về danh sách meta terms dưới dạng key => value
    public function arr_meta_terms($term_id)
    {
        $data = $this->get_meta_terms($term_id, '', $this->metaTable);

        //
        $meta_data = [];
        foreach ($data as $k => $v) {
            $meta_data[$v['meta_key']] = $v['meta_value'];
        }
        return $meta_data;
    }

    public function get_meta_terms($term_id, $key = '')
    {
        // lấy theo key cụ thể
        if ($key != '') {
            $data = $this->base_model - select('*', $this->metaTable, array(
                // các kiểu điều kiện where
                'term_id' => $term_id,
                'meta_key' => $key,
            ), array(
                'order_by' => array(
                    'meta_id' => 'DESC'
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 2,
                'limit' => 1
            ));

            //
            if (empty($data)) {
                return '';
            }
            return $data['meta_value'];
        }

        // lấy toàn bộ meta
        return $this->base_model->select('*', $this->metaTable, array(
            // các kiểu điều kiện where
            'term_id' => $term_id
        ), array(
            'group_by' => array(
                'meta_key',
            ),
            'order_by' => array(
                'meta_id' => 'DESC'
            ),
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 2,
            //'limit' => 3
        ));
    }

    // hàm này sẽ kiểm tra xem có meta tương ứng của post không, có thì in ra luôn
    function return_meta_term($data, $key, $default_value = '')
    {
        if (isset($data[$key])) {
            return $data[$key];
        } else if (isset($data['term_meta'])) {
            return $this->return_meta_term($data['term_meta'], $key);
        }

        //
        return $default_value;
    }

    //
    function echo_meta_term($data, $key, $default_value = '')
    {
        echo $this->return_meta_term($data['term_meta'], $key, $default_value);
    }

    // đồng bộ tham số đầu vào
    public function sync_term_parms($prams, $ops)
    {
        // nếu đầu vào không phải array
        if (!is_array($prams)) {
            if (empty($prams)) {
                $prams = [];
                //return debug_backtrace()[ 1 ][ 'function' ] . ' $prams is NULL!';
            }
            // tự tạo theo term id
            else if (is_numeric($prams)) {
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

        if (!isset($prams['term_id'])) {
            $prams['term_id'] = isset($ops['term_id']) ? $ops['term_id'] : 0;
        }
        if (!isset($prams['limit'])) {
            $prams['limit'] = isset($ops['limit']) ? $ops['limit'] : 0;
        }
        if (!isset($prams['offset'])) {
            $prams['offset'] = isset($ops['offset']) ? $ops['offset'] : 0;
        }
        if (!isset($prams['order_by'])) {
            $prams['order_by'] = isset($ops['order_by']) ? $ops['order_by'] : [];
        }
        //print_r( $prams );
        //die( 'hkj dfsdfgsdgsdgs' );

        //
        return $prams;
    }

    public function select_term($term_id, $where = [], $filter = [], $select_col = '*')
    {
        if ($term_id > 0) {
            $where['term_id'] = $term_id;
        }
        if (empty($where)) {
            return [];
        }

        //
        $default_filter = [
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 2,
            'limit' => 1
        ];
        foreach ($filter as $k => $v) {
            $default_filter[$k] = $v;
        }

        // select dữ liệu từ 1 bảng bất kỳ
        $data = $this->base_model->select($select_col, WGR_TERM_VIEW, $where, $default_filter);
        //print_r($data);
        //die(__CLASS__ . ':' . __LINE__);

        // lấy meta của post này
        if (!empty($data)) {
            if ($default_filter['limit'] > 1) {
                $data = $this->terms_meta_post($data);
            } else {
                $data = $this->terms_meta_post([$data]);
                $data = $data[0];
            }
            //print_r($data);
            return $data;
        }

        //
        return [];
    }

    /*
     * đồng bộ các tổng số nhóm con cho các danh mục
     */
    public function sync_v1_term_child_count($run_h_only = true)
    {
        // chức năng này chỉ hoạt động vào khung giờ thấp điểm
        if ($run_h_only === true && date('H') % 6 != 0) {
            return 'Run in 0, 6, 12 or 24h only';
        }

        //
        $prefix = WGR_TABLE_PREFIX;

        //echo __FUNCTION__ . '<br>' . PHP_EOL;
        $last_run = $this->base_model->scache(__FUNCTION__);
        if ($last_run !== NULL) {
            //print_r( $last_run );
            return $last_run;
        }
        // lúc cần xem lỗi trên html thì mở dòng này để còn hiển thị html
        //echo ' -->';

        /*
         * chức năng này chạy lâu hơn bình thường -> tạo cache luôn và ngay để tránh việc người sau vào lại thực thi cùng
         * cái này cứ để giãn cách xa 1 chút, tầm nửa ngày đến vài ngày làm 1 lần cũng được
         */
        $this->base_model->scache(__FUNCTION__, time(), $this->time_update_last_count - rand(333, 666));

        //
        $the_view = $prefix . 'zzz_update_count';

        //
        $current_time = time();

        /*
         * tính tổng số nhóm con trong 1 nhóm
         */
        // reset tất cả về 0 đã
        $this->base_model->update_multiple(
            'terms',
            [
                'child_count' => 0,
                'child_last_count' => $current_time,
            ],
            [
                'is_deleted' => DeletedStatus::FOR_DEFAULT,
            ],
            [
                // hiển thị mã SQL để check
                //'show_query' => 1,
            ]
        );
        //return false;

        /*
         * tạo 1 view trung gian để update tính tổng số nhóm con trong 1 nhóm
         */
        // -> dùng CI query builder để tạo query -> tránh sql injection
        $sql = $this->base_model->select(
            'parent, COUNT(term_id) AS c',
            'term_taxonomy',
            array(
                // các kiểu điều kiện where
                'parent > ' => 0,
            ),
            array(
                'group_by' => array(
                    'parent',
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                'get_query' => 1,
                // trả về COUNT(column_name) AS column_name
                //'selectCount' => 'ID',
                // trả về tổng số bản ghi -> tương tự mysql num row
                //'getNumRows' => 1,
                //'offset' => 0,
                'limit' => -1
            )
        );
        $sql = "CREATE OR REPLACE VIEW $the_view AS $sql";
        //echo $sql . '<br>' . PHP_EOL;
        //die( __CLASS__ . ':' . __LINE__ );
        $this->base_model->MY_query($sql);

        // update count cho các parent trong view
        $sql = "UPDATE " . $prefix . "terms
        INNER JOIN
            $the_view ON $the_view.parent = " . $prefix . "terms.term_id
        SET
            " . $prefix . "terms.child_count = $the_view.c
        WHERE
            is_deleted = ?";
        //echo $sql . '<br>' . PHP_EOL;
        $this->base_model->MY_query(
            $sql,
            [
                DeletedStatus::FOR_DEFAULT
            ]
        );


        /*
         * tính tổng số bài viết trong 1 nhóm
         */
        // reset tất cả về 0 đã
        $this->base_model->update_multiple(
            'term_taxonomy',
            [
                'count' => 0
            ],
            [
                'count >' => 0
            ]
        );

        // đặt các relationships về XÓA
        $sql = "UPDATE " . $prefix . "term_relationships
        SET
            is_deleted = ?";
        //echo $sql . '<br>' . PHP_EOL;
        $this->base_model->MY_query(
            $sql,
            [
                DeletedStatus::DELETED
            ]
        );

        // đặt trạng thái public các relationships của post đang public
        $params = [];
        $sql = "UPDATE " . $prefix . "term_relationships
        INNER JOIN
            " . $prefix . "posts ON  " . $prefix . "posts.ID = " . $prefix . "term_relationships.object_id
        SET
            " . $prefix . "term_relationships.is_deleted = ?";
        $params[] = DeletedStatus::FOR_DEFAULT;

        //
        $sql .= "WHERE " . $prefix . "posts.post_status = ?";
        $params[] = PostType::PUBLICITY;

        //
        //echo $sql . '<br>' . PHP_EOL;
        $this->base_model->MY_query($sql, $params);
        //return false;

        /*
         * tạo 1 view trung gian để update tính tổng số bài viết trong 1 nhóm
         */
        // -> dùng CI query builder để tạo query -> tránh sql injection
        $sql = $this->base_model->select(
            'term_taxonomy_id, COUNT(object_id) AS c',
            'term_relationships',
            array(
                // các kiểu điều kiện where
                'is_deleted' => DeletedStatus::FOR_DEFAULT,
            ),
            array(
                'group_by' => array(
                    'term_taxonomy_id',
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                'get_query' => 1,
                // trả về COUNT(column_name) AS column_name
                //'selectCount' => 'ID',
                // trả về tổng số bản ghi -> tương tự mysql num row
                //'getNumRows' => 1,
                //'offset' => 0,
                'limit' => -1
            )
        );
        $sql = "CREATE OR REPLACE VIEW $the_view AS $sql";
        //echo $sql . '<br>' . PHP_EOL;
        $this->base_model->MY_query($sql);

        // update count cho các parent trong view
        $sql = "UPDATE " . $prefix . "term_taxonomy
        INNER JOIN
            $the_view ON $the_view.term_taxonomy_id = " . $prefix . "term_taxonomy.term_id
        SET
            " . $prefix . "term_taxonomy.count = $the_view.c";
        //echo $sql . '<br>' . PHP_EOL;
        $this->base_model->MY_query($sql);


        /*
         * tạo view để tính tổng số bài trong nhóm con, sau đó update cho nhóm cha -> tăng xác suất xuất hiện của nhóm cha
         */
        // -> dùng CI query builder để tạo query -> tránh sql injection
        $sql = $this->base_model->select(
            'parent, SUM(count) AS t',
            'term_taxonomy',
            array(
                // các kiểu điều kiện where
                'parent > ' => 0,
            ),
            array(
                'group_by' => array(
                    'parent',
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                'get_query' => 1,
                // trả về COUNT(column_name) AS column_name
                //'selectCount' => 'ID',
                // trả về tổng số bản ghi -> tương tự mysql num row
                //'getNumRows' => 1,
                //'offset' => 0,
                'limit' => -1
            )
        );
        $sql = "CREATE OR REPLACE VIEW $the_view AS $sql";
        //echo $sql . '<br>' . PHP_EOL;
        $this->base_model->MY_query($sql);

        // update count cho các parent trong view
        $sql = "UPDATE " . $prefix . "term_taxonomy
        INNER JOIN
            $the_view ON $the_view.parent = " . $prefix . "term_taxonomy.term_id
        SET
            " . $prefix . "term_taxonomy.count = " . $prefix . "term_taxonomy.count+$the_view.t";
        //echo $sql . '<br>' . PHP_EOL;
        $this->base_model->MY_query($sql);

        // TEST
        //return false;


        /*
         * xong thì xóa luôn view này đi
         */
        $sql = "DROP VIEW IF EXISTS $the_view";
        //echo $sql . '<br>' . PHP_EOL;
        $this->base_model->MY_query($sql);


        /*
         * dọn dẹp các cache liên quan đến term để nếu trước đó nó dính thì sau nó còn nạp lại luôn
         */
        $this->base_model->dcache('get_all_taxonomy-');
        $this->base_model->dcache('term-');

        //
        return true;
    }

    /**
     * đồng bộ các tổng số nhóm con cho các danh mục
     **/
    public function sync_term_child_count($run_h_only = true, $limit = 20)
    {
        if ($run_h_only === true) {
            $last_run = $this->base_model->scache(__FUNCTION__);
            if ($last_run !== NULL) {
                //print_r( $last_run );
                return $last_run;
            }
        }

        // lấy ít nhóm đã quá hạn đồng bộ để chạy vòng lặp đồng bộ lại
        $data = $this->base_model->select(
            'term_id, child_last_count',
            'terms',
            array(
                // các kiểu điều kiện where
                'is_deleted' => DeletedStatus::FOR_DEFAULT,
                'child_last_count <' => time(),
            ),
            array(
                'order_by' => array(
                    'child_last_count' => 'ASC',
                    //'term_id' => 'DESC',
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                // trả về COUNT(column_name) AS column_name
                //'selectCount' => 'ID',
                // trả về tổng số bản ghi -> tương tự mysql num row
                //'getNumRows' => 1,
                //'offset' => 0,
                'limit' => $limit
            )
        );
        //print_r($data);
        if (empty($data)) {
            $this->base_model->scache(__FUNCTION__, time(), $this->time_update_last_count - rand(333, 666));
            return true;
        }

        //
        foreach ($data as $v) {
            $this->update_count_post_in_term($v);
        }

        //
        return true;
    }

    /**
     * Update tổng số bài viết trong 1 nhóm
     **/
    public function update_count_post_in_term($data)
    {
        //print_r($data);
        if (!isset($data['child_last_count']) || !isset($data['term_id'])) {
            return false;
        }
        // giãn cách giữa các lần cập nhật count
        if ($data['child_last_count'] > time()) {
            return false;
        }

        /*
        * child_count: tính tổng số nhóm con của nhóm này
        */
        $child_count = $this->base_model->select(
            'term_id',
            'term_taxonomy',
            array(
                // các kiểu điều kiện where
                'parent' => $data['term_id'],
            ),
            array(
                // hiển thị mã SQL để check
                //'show_query' => 1,
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
        //print_r($child_count);

        //
        $this->base_model->update_multiple('terms', [
            'child_count' => count($child_count),
            'child_last_count' => time() + $this->time_update_count,
        ], [
            'term_id' => $data['term_id'],
        ], [
            // hiển thị mã SQL để check
            //'show_query' => 1,
        ]);

        //
        $where_in = [$data['term_id']];
        foreach ($child_count as $v) {
            $where_in[] = $v['term_id'];
        }
        //print_r($where_in);

        /*
        * count: tính tổng số bài viết của nhóm này và nhóm con
        */
        $post_count = $this->base_model->select(
            'COUNT(' . WGR_TABLE_PREFIX . 'term_relationships.object_id) AS c',
            'term_relationships',
            array(
                // các kiểu điều kiện where
                //'term_relationships.term_taxonomy_id' => $data['term_id'],
                'term_relationships.is_deleted' => DeletedStatus::FOR_DEFAULT,
                'posts.post_status' => PostType::PUBLICITY,
            ),
            array(
                'where_in' => array(
                    'term_relationships.term_taxonomy_id' => $where_in
                ),
                'join' => array(
                    'posts' => 'posts.ID = term_relationships.object_id'
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
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
        //print_r($post_count);

        //
        $this->base_model->update_multiple('term_taxonomy', [
            'count' => $post_count[0]['c'],
        ], [
            'term_id' => $data['term_id'],
        ], [
            // hiển thị mã SQL để check
            //'show_query' => 1,
        ]);

        //
        return true;
    }

    // trả về key cho term cache
    public function key_cache($id)
    {
        return 'term-' . $id . '-';
    }
    // cache cho phần term -> gán key theo mẫu thống nhất để sau còn xóa cache cho dễ
    public function the_cache($id, $key, $value = '', $time = MEDIUM_CACHE_TIMEOUT)
    {
        return $this->base_model->scache($this->key_cache($id) . $key, $value, $time);
    }
}
