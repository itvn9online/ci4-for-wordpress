<?php

namespace App\Models;

// Libraries
use App\Libraries\LanguageCost;
use App\Libraries\PostType;
use App\Libraries\TaxonomyType;
use App\Libraries\DeletedStatus;
use App\Helpers\HtmlTemplate;

//
class PostQuery extends PostMeta
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function sync_post_data($data)
    {
        // tạo short shortslug nếu có -> dùng để order khi select
        if (isset($data['post_shorttitle'])) {
            if ($data['post_shorttitle'] != '') {
                $data['post_shortslug'] = $this->base_model->slug_non_mark_seo($data['post_shorttitle']);
            } else {
                $data['post_shortslug'] = '';
            }
        }
        // đặt giá trị này để khởi tạo lại permalink
        //$data['post_permalink'] = '';
        //$data['updated_permalink'] = 0;

        //
        foreach ($data as $k => $v) {
            //echo gettype($v) . PHP_EOL;

            //
            if (gettype($v) == 'string') {
                /*
            if (in_array(gettype($v), [
                'string'
            ])) {
                */
                $data[$k] = trim($v);
            }
        }
        return $data;
    }

    public function insert_post($data, $data_meta = [], $check_slug = true)
    {
        //print_r($data);

        // các dữ liệu mặc định
        $default_data = [
            'post_date' => date(EBE_DATETIME_FORMAT),
        ];
        if (!isset($data['lang_key']) || $data['lang_key'] == '') {
            $data['lang_key'] = LanguageCost::lang_key();
        }
        // gán thông ID tác giả nếu chưa có
        if (!isset($data['post_author']) || empty($data['post_author'])) {
            //$session_data = $this->session->get( 'admin' );
            $session_data = $this->base_model->get_ses_login();
            if (empty($session_data) || empty($session_data['userID'])) {
                $data['post_author'] = 1;
            } else {
                $data['post_author'] = $session_data['userID'];
            }
        }
        $default_data['post_date_gmt'] = $default_data['post_date'];
        $default_data['post_modified'] = $default_data['post_date'];
        $default_data['post_modified_gmt'] = $default_data['post_date'];
        $default_data['time_order'] = time();

        //
        if (!isset($data['post_name']) || $data['post_name'] == '') {
            $data['post_name'] = $data['post_title'];
        }
        if ($data['post_name'] != '') {
            $data['post_name'] = $this->base_model->slug_non_mark_seo($data['post_name']);

            //
            if ($check_slug === true) {
                $checking_slug = $this->base_model->select(
                    'ID, post_type',
                    $this->table,
                    [
                        'post_name' => $data['post_name'],
                        'post_type' => $data['post_type'],
                        //'post_status !=' => PostType::DELETED,
                        'lang_key' => $data['lang_key'],
                    ],
                    [
                        'where_not_in' => array(
                            'post_status' => array(
                                PostType::DELETED,
                                PostType::REMOVED,
                            )
                        ),
                        // hiển thị mã SQL để check
                        //'show_query' => 1,
                        // trả về câu query để sử dụng cho mục đích khác
                        //'get_query' => 1,
                        //'offset' => 2,
                        'limit' => 1
                    ]
                );
                //print_r( $checking_slug );
                if (!empty($checking_slug)) {
                    return [
                        'code' => __LINE__,
                        'error' => __FUNCTION__ . ' Slug đã được sử dụng ở ' . $checking_slug['post_type'] . ' #' . $checking_slug['ID'] . ' (' . $data['post_name'] . ')',
                    ];
                }
                //die( __CLASS__ . ':' . __LINE__ );
            }
        }
        foreach ($default_data as $k => $v) {
            if (!isset($data[$k])) {
                $data[$k] = $v;
            }
        }

        //
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $v = implode(',', $v);
                $v = ltrim($v, ',');
                $v = ltrim($v, '0,');
                $data[$k] = $v;
            }
        }
        // đồng bộ dữ liệu trước khi insert
        $data = $this->sync_post_data($data);
        // print_r($data);
        // print_r($data_meta);
        // die(__CLASS__ . ':' . __LINE__);

        // insert post
        $result_id = $this->base_model->insert($this->table, $data, true);
        // var_dump($result_id);
        // print_r($result_id);

        // 
        if ($result_id !== false) {
            //print_r($data_meta);
            //print_r($_POST);
            //die( __CLASS__ . ':' . __LINE__ );

            //
            if (is_numeric($result_id) && $result_id > 0) {
                $this->update_post_permalink($this->select_post($result_id, [
                    //'post_type' => $data['post_type'],
                ]));
                //die(__CLASS__ . ':' . __LINE__);
            }

            //
            $post_type = '';
            if (isset($data['post_type'])) {
                $post_type = $data['post_type'];
            }

            // insert/ update meta post
            if (!empty($data_meta)) {
                $this->insert_meta_post($data_meta, $result_id, false, $post_type);
            } else if (isset($_POST['post_meta'])) {
                $this->insert_meta_post($_POST['post_meta'], $result_id, false, $post_type);
            }
        } else {
            // $this->base_model->insert($this->table, $data, true, 'getQuery');
        }
        return $result_id;
    }

    public function update_post($post_id, $data, $where = [], $data_meta = [], $clear_meta = true, $post_type = '', $check_slug = true)
    {
        if (isset($data['post_name'])) {
            if ($data['post_name'] == '') {
                $data['post_name'] = $data['post_title'];
            }
            if ($data['post_name'] != '') {
                $data['post_name'] = str_replace('.', '-', $data['post_name']);
                $data['post_name'] = str_replace('--', '-', $data['post_name']);
            }
        }
        if (!isset($data['post_modified']) || $data['post_modified'] == '') {
            $data['post_modified'] = date(EBE_DATETIME_FORMAT);
            $data['post_modified_gmt'] = $data['post_modified'];
        }
        //$data[ 'time_order' ] = time();

        //
        $where['ID'] = $post_id;
        // print_r($data);
        // print_r($where);

        //
        if (isset($data['post_type'])) {
            $post_type = $data['post_type'];
        } else if (isset($where['post_type'])) {
            $post_type = $where['post_type'];
        }

        // tiêu đề gắn thêm khi post bị xóa
        $str_trash = DeletedStatus::FOR_TRASH;
        $str_time = date('dHis');

        // nếu đang là xóa bài viết thì bỏ qua việc kiểm tra slug
        if (isset($data['post_status']) && in_array($data['post_status'], [
            PostType::DELETED,
            PostType::REMOVED
        ])) {

            //
            if (!isset($data['post_name']) || $data['post_name'] == '') {
                $the_slug = $this->base_model->select(
                    'post_name',
                    $this->table,
                    array(
                        // các kiểu điều kiện where
                        'ID' => $post_id,
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
                // print_r($the_slug);
                if (empty($the_slug)) {
                    return -1;
                }
                $data['post_name'] = $the_slug['post_name'];
            }

            // xóa trash đi trước khi non-mark
            $data['post_name'] = explode($str_trash, $data['post_name'])[0];
            // thêm tham số trash vào slug
            $data['post_name'] = $this->base_model->slug_non_mark_seo($data['post_name'], $str_trash . $str_time);
        } else {
            if (isset($data['post_name']) && $data['post_name'] != '') {
                // bỏ tham số trash khỏi slug
                // $data['post_name'] = explode($str_trash, $data['post_name'])[0];
                $data['post_name'] = $this->base_model->slug_non_mark_seo($data['post_name']);
            }

            // Nếu post type này sử ID làm URL chính thì bỏ qua chế độ check slug
            //print_r(POST_ID_PERMALINK);
            if ($post_type == PostType::ADS || in_array($post_type, POST_ID_PERMALINK)) {
                $check_slug = false;
            }

            // kiểm tra xem có trùng slug không
            if ($check_slug === true && isset($data['post_name']) && $data['post_name'] != '' && strpos($data['post_name'], $str_trash) === false) {
                // post đang cần update
                $current_slug = $this->base_model->select(
                    '*',
                    $this->table,
                    $where,
                    [
                        // hiển thị mã SQL để check
                        //'show_query' => 1,
                        // trả về câu query để sử dụng cho mục đích khác
                        //'get_query' => 1,
                        //'offset' => 2,
                        'limit' => 1
                    ]
                );
                //print_r( $current_slug );

                //
                if (!empty($current_slug) && $current_slug['post_type'] != PostType::ADS) {
                    if (isset($data['lang_key']) && $data['lang_key'] != '') {
                        $lang_key = $data['lang_key'];
                    } else {
                        $lang_key = LanguageCost::lang_key();
                    }

                    //
                    $checking_slug = $this->base_model->select(
                        '*',
                        //'ID, post_type',
                        $this->table,
                        [
                            'post_name' => $data['post_name'],
                            'ID !=' => $current_slug['ID'],
                            'post_type' => $current_slug['post_type'],
                            //'post_status !=' => PostType::DELETED,
                            //'post_status' => $current_slug[ 'post_status' ],
                            'lang_key' => $lang_key,
                        ],
                        [
                            'where_not_in' => array(
                                'post_status' => array(
                                    PostType::DELETED,
                                    PostType::REMOVED,
                                )
                            ),
                            // hiển thị mã SQL để check
                            //'show_query' => 1,
                            // trả về câu query để sử dụng cho mục đích khác
                            //'get_query' => 1,
                            //'offset' => 2,
                            'limit' => 1
                        ]
                    );
                    //print_r( $checking_slug );
                    if (!empty($checking_slug)) {
                        //print_r($checking_slug);

                        //
                        return [
                            'code' => __LINE__,
                            'error' => __FUNCTION__ . ' Slug đã được sử dụng ở ' . $checking_slug['post_type'] . ' #' . $checking_slug['ID'] . ' (' . $data['post_name'] . ')',
                        ];
                    }
                    //die( __CLASS__ . ':' . __LINE__ );
                }
            }
        }

        //
        //print_r( $data );
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $v = implode(',', $v);
                $v = ltrim($v, ',');
                $v = ltrim($v, '0,');
                $data[$k] = $v;
            }
        }
        // đồng bộ dữ liệu trước khi update
        $data = $this->sync_post_data($data);
        //print_r( $data );
        //die( __CLASS__ . ':' . __LINE__ );

        //
        $result_update = $this->base_model->update_multiple(
            $this->table,
            $data,
            $where,
            [
                'debug_backtrace' => debug_backtrace()[1]['function']
            ]
        );

        //
        //print_r($_POST);
        //print_r($data_meta);
        if (!empty($data_meta)) {
            //print_r($data_meta);
            $this->insert_meta_post($data_meta, $post_id, $clear_meta, $post_type);
        } else if (isset($_POST['post_meta'])) {
            //print_r($_POST);
            $this->insert_meta_post($_POST['post_meta'], $post_id, $clear_meta, $post_type);
        }

        //
        if ($result_update === true) {
            $data['ID'] = $post_id;
            $data['post_permalink'] = $this->before_post_permalink($data);
            //print_r($data);
        }

        //
        return $result_update;
    }

    public function select_post($post_id, $where = [], $filter = [], $select_col = '*')
    {
        if ($post_id > 0) {
            $where['ID'] = $post_id;
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
        $data = $this->base_model->select($select_col, $this->table, $where, $default_filter);

        // lấy meta của post này -> chỉ lấy khi có post ID
        if (!empty($data) && $post_id > 0) {
            //$data[ 'post_meta' ] = $this->arr_meta_post( $data[ 'ID' ] );
            // trong trang chi tiết -> lấy trực tiếp từ meta -> đỡ lỗi
            //$data[ 'post_meta_data' ] = null;
            $data = $this->the_meta_post($data);
        } else if ($default_filter['limit'] === 1) {
            $data = $this->the_meta_post($data);
        }
        //print_r( $data );

        //
        return $data;
    }

    public function select_public_post($post_id, $where = [], $select_col = '*', $ops = [])
    {
        // các tham số bắt buộc
        //$where[ 'post_status' ] = PostType::PUBLICITY;
        $where['lang_key'] = LanguageCost::lang_key();

        //
        if (!isset($ops['where_in'])) {
            $ops['where_in'] = [];
        }
        if (!isset($ops['order_by'])) {
            $ops['order_by'] = [
                'ID' => 'ASC'
            ];
        }
        $ops['where_in']['post_status'] = [
            // ai cũng có thể xem
            PostType::PUBLICITY,
            // người dùng đã đăng nhập
            PostType::PRIVATELY,
            // cho admin xem trước
            PostType::DRAFT,
        ];
        //print_r($ops);
        //die(__CLASS__ . ':' . __LINE__);

        //
        return $this->select_post(
            $post_id,
            $where,
            [
                'where_in' => $ops['where_in'],
                // hiển thị mã SQL để check
                //'show_query' => 1,
                //
                'order_by' => $ops['order_by'],
            ],
            $select_col
        );
    }

    // trả về danh sách post theo term_id
    public function select_list_post($post_type, $post_cat = [], $limit = 1, $order_by = [], $ops = [])
    {
        //print_r( $post_cat );
        //print_r($ops);
        if (!isset($ops['offset']) || $ops['offset'] < 0) {
            $ops['offset'] = 0;
        }

        //
        $where_not_in = [];
        if (isset($ops['where_not_in'])) {
            $where_not_in = $ops['where_not_in'];
        }

        //
        $where_in = [];
        if (isset($ops['where_in'])) {
            $where_in = $ops['where_in'];
        }

        //
        if (empty($order_by)) {
            $order_by = [
                'menu_order' => 'DESC',
                'time_order' => 'DESC',
                'ID' => 'DESC',
            ];
        }

        //
        if (!isset($post_cat['lang_key']) || $post_cat['lang_key'] == '') {
            $post_cat['lang_key'] = LanguageCost::lang_key();
        }

        //
        $where = [
            'post_type' => $post_type,
            'post_status' => PostType::PUBLICITY,
            'taxonomy' => $post_cat['taxonomy'],
            'lang_key' => $post_cat['lang_key'],
            //'(term_taxonomy.term_id = ' . $post_cat[ 'term_id' ] . ' OR term_taxonomy.parent = ' . $post_cat[ 'term_id' ] . ')' => NULL,
        ];
        /*
        if ( isset( $post_cat[ 'taxonomy' ] ) && $post_cat[ 'taxonomy' ] != '' ) {
        $where[ 'term_taxonomy.taxonomy' ] = $post_cat[ 'taxonomy' ];
        }
        */

        //
        if (isset($ops['where'])) {
            foreach ($ops['where'] as $k => $v) {
                $where[$k] = $v;
            }
        }

        //
        $arr_where_or = [];
        // tìm theo ID truyền vào
        if (isset($post_cat['term_id']) && $post_cat['term_id'] > 0) {
            //$where[ '(term_id = ' . $post_cat[ 'term_id' ] . ' OR parent = ' . $post_cat[ 'term_id' ] . ')' ] = null;
            $arr_where_or = [
                'term_id' => $post_cat['term_id'],
                'parent' => $post_cat['term_id'],
            ];
        }
        // tìm theo slug truyền vào
        else if (isset($post_cat['slug']) && $post_cat['slug'] != '') {
            // lấy term_id theo slug truyền vào
            $get_term_id = $this->term_model->get_taxonomy(
                [
                    'slug' => $post_cat['slug'],
                    'is_deleted' => DeletedStatus::FOR_DEFAULT,
                    'taxonomy' => $post_cat['taxonomy'],
                ],
                [
                    'limit' => 1,
                    'select_col' => 'term_id',
                ]
            );
            //print_r( $get_term_id );

            // không có thì trả về lỗi
            if (empty($get_term_id)) {
                return [];
            }
            // có thì gán lấy theo term_id
            //$where[ '(term_id = ' . $get_term_id[ 'term_id' ] . ' OR parent = ' . $get_term_id[ 'term_id' ] . ')' ] = null;
            $arr_where_or = [
                'term_id' => $get_term_id['term_id'],
                'parent' => $get_term_id['term_id'],
            ];
        }

        //
        if (isset($ops['where_or'])) {
            foreach ($ops['where_or'] as $k => $v) {
                $arr_where_or[$k] = $v;
            }
        }

        //
        if (!isset($ops['group_by'])) {
            $ops['group_by'] = [
                'ID',
            ];
        }

        //
        if (isset($ops['count_record'])) {
            return $this->base_model->select_count(
                'ID',
                WGR_POST_VIEW,
                $where,
                [
                    'where_or' => $arr_where_or,
                    'where_not_in' => $where_not_in,
                    // 'group_by' => $ops['group_by'],
                    'group_by' => [
                        'ID'
                    ],
                    //'order_by' => $order_by,
                    //'get_sql' => 1,
                    //'show_query' => 1,
                    //'debug_only' => 1,
                    //'offset' => $ops[ 'offset' ],
                    //'limit' => $limit
                ]
            );
        } else {
            // nếu có chỉ định chỉ lấy các cột cần thiết
            if (!isset($ops['select'])) {
                $ops['select'] = '*';
                //} else {
                // chuẩn hóa đầu vào
                //$ops['select'] = str_replace(' ', '', $ops['select']);
                /*
                $ops[ 'select' ] = explode( ',', $ops[ 'select' ] );
                //print_r( $ops[ 'select' ] );
                $ops[ 'select' ] = implode( ',', $ops[ 'select' ] );
                */
                //$ops[ 'select' ] = $ops[ 'select' ];
                //echo $ops[ 'select' ] . '<br>' . PHP_EOL;
            }

            // lấy danh sách bài viết thuộc nhóm này
            $data = $this->base_model->select(
                $ops['select'],
                WGR_POST_VIEW,
                $where,
                [
                    'where_or' => $arr_where_or,
                    'where_not_in' => $where_not_in,
                    'where_in' => $where_in,
                    'group_by' => $ops['group_by'],
                    'order_by' => $order_by,
                    //'get_sql' => 1,
                    //'show_query' => 1,
                    //'debug_only' => 1,
                    'offset' => $ops['offset'],
                    'limit' => $limit
                ]
            );
            //print_r($data);
        }

        //
        if (!empty($data) && !isset($ops['no_meta'])) {
            if ($limit === 1) {
                $data = [$data];
            }

            //
            $data = $this->list_meta_post($data);
        }
        //print_r( $data );

        //
        return $data;
    }

    // function giả lập widget echbay blog trong wordpress
    function echbay_blog($slug, $ops = [])
    {
        // đầu vào mặc định
        if (!isset($ops['post_type']) || $ops['post_type'] == '') {
            $ops['post_type'] = $this->base_model->default_post_type;
        }
        if (!isset($ops['taxonomy']) || $ops['taxonomy'] == '') {
            $ops['taxonomy'] = $this->base_model->default_taxonomy;
        }
        if (!isset($ops['limit']) || $ops['limit'] < 1) {
            $ops['limit'] = 0;
        }
        // print_r($ops);

        // trả về dữ liệu
        $get_data = $this->get_auto_post($slug, $ops['post_type'], $ops['taxonomy'], $ops['limit']);
        //print_r($get_data);
        //die(__CLASS__ . ':' . __LINE__);
        if (!isset($get_data['posts']) || empty($get_data['posts'])) {
            // nếu có tham số auto clone -> cho phép nhân bản dữ liệu cho các ngôn ngữ khác
            if (
                isset($ops['auto_clone']) &&
                $ops['auto_clone'] === 1 &&
                LanguageCost::lang_key() != LanguageCost::default_lang()
            ) {
                //print_r( $get_data );
                // tiến hành lấy dữ liệu mẫu để nhân
                $clone_data = $this->get_auto_post(
                    $slug,
                    $ops['post_type'],
                    $ops['taxonomy'],
                    $ops['limit'],
                    [
                        'lang_key' => LanguageCost::default_lang()
                    ]
                );
                //print_r( $clone_data );

                // bắt đầu nhân bản
                if (isset($clone_data['posts']) && !empty($clone_data['posts'])) {
                    foreach ($clone_data['posts'] as $k => $v) {
                        //print_r( $v );

                        //
                        $data_insert = $v;
                        $data_insert['ID'] = 0;
                        unset($data_insert['ID']);
                        $data_insert['lang_key'] = LanguageCost::lang_key();
                        $data_insert['lang_parent'] = $v['ID'];
                        $data_insert['post_meta']['post_category'] = $get_data['term']['term_id'];
                        //
                        $data_insert['post_date'] = date(EBE_DATETIME_FORMAT);
                        $data_insert['post_date_gmt'] = $data_insert['post_date'];
                        $data_insert['post_modified'] = $data_insert['post_date'];
                        $data_insert['post_modified_gmt'] = $data_insert['post_date'];
                        //print_r( $data_insert );

                        //
                        $_POST['post_meta'] = $data_insert['post_meta'];
                        echo 'Auto create post: ' . $data_insert['post_title'] . ' (' . $ops['post_type'] . ') <br>' . PHP_EOL;
                        $this->insert_post($data_insert, $_POST['post_meta']);
                    }
                }
                //die( 'fjg dghsd sgsd' );
            }
            //return '<a href="sadmin/posts/add?post_type=' . $ops[ 'post_type' ] . '">Please add post to category slug #' . $slug . '</a>';
            return '<p class="show-if-admin">Please add post to category slug #' . $slug . '</p>';
        }

        //
        $data = $get_data['posts'];
        //print_r($data);
        if (isset($ops['return_object'])) {
            // trả về dữ liệu ngay sau khi select xong -> bỏ qua đoạn builder HTML
            return $data;
        }
        $post_cat = $get_data['term'];
        //print_r( $post_cat );
        if (!isset($post_cat['term_meta'])) {
            return 'term_meta not found! #' . $slug;
        }
        $instance = $post_cat['term_meta'];
        //print_r($instance);

        // lấy các giá trị placeholder mặc định -> cho thành trống hết
        // $meta_default = TaxonomyType::meta_default($post_cat['taxonomy']);
        $meta_default = $this->term_model->taxonomy_meta_default($post_cat['taxonomy']);
        //print_r( $meta_default );
        foreach ($meta_default as $k => $v) {
            if (!isset($instance[$k])) {
                $instance[$k] = '';
            }
        }

        //
        $link_view_more = '';
        if ($instance['custom_cat_link'] == '#' || $instance['custom_cat_link'] == '') {
            // $instance['custom_cat_link'] = 'javascript:;';
            $custom_cat_link = '<span>' . $post_cat['name'] . '</span>';
        } else {
            $custom_cat_link = '<a href="' . $instance['custom_cat_link'] . '">' . $post_cat['name'] . '</a>';

            //
            if ($instance['text_view_more'] != '') {
                $link_view_more = '<div class="widget-blog-more"><a href="' . $instance['custom_cat_link'] . '">' . $instance['text_view_more'] . '</a></div>';
            }
        }
        if ($instance['widget_description'] != '') {
            $instance['widget_description'] = '<div class="w90 ' . $instance['max_width'] . ' eb-widget-blogs-desc by-widget_description">' . nl2br($instance['widget_description']) . '</div>';
        } else {
            // nếu nội dung không phải nội dung mẫu
            $post_cat['description'] = str_replace('Auto create nav menu taxonomy', '', $post_cat['description']);
            if (trim(strip_tags($post_cat['description'])) != '' && strpos($post_cat['description'], 'Auto create taxonomy') === false) {
                $instance['widget_description'] = '<div class="w90 ' . $instance['max_width'] . ' eb-widget-blogs-desc by-description">' . $post_cat['description'] . '</div>';
            }
        }
        if ($instance['dynamic_tag'] == '') {
            $instance['dynamic_tag'] = 'div';
        }
        if ($instance['dynamic_post_tag'] == '') {
            $instance['dynamic_post_tag'] = 'div';
        }
        if ($instance['custom_size'] == '') {
            // nếu là q.cáo -> tự động gán size theo cỡ ảnh của bản ghi đầu tiên
            if ($data[0]['post_type'] == PostType::ADS || $this->getconfig->cf_posts_size == '') {
                $instance['custom_size'] = $this->getBlogsSize($data);
            } else {
                // còn lại sẽ lấy mặc định theo cf_posts_size
                $instance['custom_size'] = $this->getconfig->cf_posts_size;
            }
        }
        if ($instance['custom_id'] != '') {
            $instance['custom_id'] = ' id="' . $instance['custom_id'] . '"';
        }
        $custom_style = explode(' ', $instance['custom_style']);
        $custom_style[] = str_replace('-', '', $slug);

        //
        $html_widget_title = '';
        if ($instance['hide_widget_title'] == 'on') {
            $custom_style[] = 'hide-widget-title';

            // với widget ẩn title -> vẫn cho thẻ hiển thị nút sửa để admin tiện điều khiển
            $html_widget_title = '<div data-type="' . $post_cat['taxonomy'] . '" data-id="' . $post_cat['term_id'] . '" class="eb-widget-hide-title"></div>';
        } else {
            $html_widget_title = '<{{dynamic_tag}} data-type="' . $post_cat['taxonomy'] . '" data-id="' . $post_cat['term_id'] . '" class="eb-widget-title">' . $custom_cat_link . '</{{dynamic_tag}}>';
        }
        $html_widget_title .= $instance['widget_description'];

        // để tối ưu SEO -> chỉ lấy tiêu đề khi có yêu cầu -> khi hide-title thì bỏ khối html title luôn
        if (
            $instance['hide_title'] == 'on'
        ) {
            $custom_style[] = 'hide-blogs-title';
            $the_title = '';
            $the_span_title = '';
            $the_avt_link = '';
        } else {
            // html cho link
            $the_title = file_get_contents(VIEWS_PATH . 'html/the_title.html');
            // html khi ko có link
            $the_span_title = file_get_contents(VIEWS_PATH . 'html/the_span_title.html');
            // html cho link ảnh đại diện
            $the_avt_link = file_get_contents(VIEWS_PATH . 'html/the_avt_link.html');
        }

        //
        if ($instance['hide_description'] == 'on') {
            $custom_style[] = 'hide-blogs-description';
        }
        if ($instance['hide_info'] == 'on') {
            $custom_style[] = 'hide-blogs-info';
        }
        if ($instance['run_slider'] == 'on') {
            $custom_style[] = 'ebwidget-run-slider';
        }
        /*
        if ( $instance[ 'open_youtube' ] == 'on' ) {
            $custom_style[] = 'youtube-quick-view';
        }
        */
        if ($instance['text_view_more'] != '' || $instance['text_view_details'] != 'text_view_details') {
            $custom_style[] = 'show-view-more';
        }
        // hiển thị tiêu đề ngắn bài viết
        $show_short_title = '';
        if ($instance['show_short_title'] == 'on') {
            $show_short_title = '<div class="eb-blog-short_title">{{post_shorttitle}}</div>';
        }
        // hiển thị nội dung của bài viết
        $show_post_content = '';
        if ($instance['show_post_content'] == 'on') {
            $show_post_content = '<div class="eb-blog-content">{{post_content}}</div>';
        }
        //print_r( $instance );
        if (isset($ops['add_class'])) {
            // thêm class css tùy chỉnh vào
            $custom_style[] = $ops['add_class'];
        }
        $custom_style[] = 'blog-section';

        // thêm class theo slug
        $instance['custom_style'] =  implode(' ', $custom_style);

        // nếu có file custom HTML -> ưu tiên dùng
        if ($instance['post_custom_cloumn'] != '') {
            echo '<!-- ' . $instance['post_custom_cloumn'] . ' --> ' . PHP_EOL;
            $tmp_html = $this->base_model->get_html_tmp($instance['post_custom_cloumn'], VIEWS_CUSTOM_PATH . 'ads_node/', '');
        }
        // cố định file HTML để tối ưu với SEO
        else {
            $html_node = 'ads_node';
            if ($instance['post_cloumn'] == 'chi_anh') {
                $html_node = 'ads_node_chi_anh';
            } elseif ($instance['post_cloumn'] == 'chi_chu') {
                $html_node = 'ads_node_chi_chu';
            } elseif ($instance['post_cloumn'] == 'text_only') {
                $html_node = 'ads_node_text_only';
            } elseif ($instance['hide_description'] == 'on' && $instance['hide_info'] == 'on') {
                $html_node = 'ads_node_avt_title';
            }
            echo '<!-- ' . $html_node . ' --> ' . PHP_EOL;
            $tmp_html = $this->base_model->parent_html_tmp($html_node);
        }
        //echo $tmp_html . '<br>' . PHP_EOL;

        // tạo css chỉnh cột
        //print_r($instance);
        if ($instance['post_cloumn'] != '') {
            $instance['post_cloumn'] = 'blogs_node_' . $instance['post_cloumn'];
        }
        //print_r( $instance );
        $instance['post_cloumn'] = implode(' ', [
            $instance['num_line'],
            $instance['num_medium_line'],
            $instance['num_small_line'],
            $instance['post_cloumn'],
            $instance['column_spacing'],
            $instance['row_align'],
            $instance['max_width'],
        ]);

        // do hàm select có chỉnh sửa với limit -> ở đây phải thao tác ngược lại
        if ($ops['limit'] == 1) {
            $data = [$data];
        }
        //print_r( $data );
        $html = '';
        foreach ($data as $v) {
            // print_r($v);
            //continue;
            //die( __CLASS__ . ':' . __LINE__ );

            //
            $url_video = '';
            $a_class = '';
            $p_link = 'javascript:;';
            $dynamic_a_tag = $the_title;
            $dynamic_avt_link = $the_avt_link;

            //
            if (isset($v['post_meta']['url_video']) && $v['post_meta']['url_video'] != '') {
                $a_class = 'open-video';
                $p_link = $v['post_meta']['url_video'];
                $url_video = $p_link;
            } else if (isset($v['post_meta']['url_redirect']) && $v['post_meta']['url_redirect'] != '') {
                //print_r( $v );
                $p_link = $v['post_meta']['url_redirect'];
                //echo $p_link . '<br>' . PHP_EOL;
            } else {
                $a_class = 'is-empty-url';
                $dynamic_a_tag = $the_span_title;
                $dynamic_avt_link = '';
            }
            $a_link = $p_link;

            // đặt chế độ xuống dòng cho phần tóm tắt khi không có mã HTML trong đấy
            if ($v['post_excerpt'] != '' && strpos($v['post_excerpt'], '</') === false && strpos($v['post_excerpt'], '>') === false) {
                $v['post_excerpt'] = nl2br($v['post_excerpt']);
            }
            $widget_blog_more = '';
            if ($instance['text_view_details'] != '') {
                $widget_blog_more = '<div class="widget-blog-more-xoa details-blog-more"><a href="{{p_link}}">' . $instance['text_view_details'] . '</a></div>';
            }

            // nếu người dùng chọn ảnh hiển thị thì đặt webp là ảnh đó
            if (isset($v['post_meta']['image_size']) && $v['post_meta']['image_size'] != 'image_medium') {
                if (isset($v['post_meta']['image'])) {
                    $v['post_meta']['image_webp'] = $v['post_meta']['image'];
                }
            }

            // tạo html cho từng node
            //echo $tmp_html;
            $str_node = $this->base_model->tmp_to_html(
                $tmp_html,
                [
                    'dynamic_a_tag' => $dynamic_a_tag,
                    'dynamic_avt_link' => $dynamic_avt_link,
                    'p_link' => $p_link,
                    'a_class' => $a_class,
                    'a_link' => $a_link,
                    'show_short_title' => $show_short_title,
                    'show_post_content' => $show_post_content . $widget_blog_more,
                    'post_permalink' => $p_link,
                ]
            );
            $str_node = $this->base_model->tmp_to_html(
                $str_node,
                $v,
                [
                    'taxonomy_key' => $ops['taxonomy'],
                    'p_link' => $p_link,
                    'url_video' => $url_video,
                ]
            );
            //echo $str_node;
            $str_node = HtmlTemplate::render($str_node, $v['post_meta']);
            //echo $str_node;

            //
            $html .= $str_node;
        }

        // xóa các trường dữ liệu trống
        foreach ([
            '<div class="eb-blog-short_title"></div>' => '',
            '<div class="eb-blog-gioithieu"></div>' => '',
            '<div class="eb-blog-content"></div>' => '',
        ] as $k => $v) {
            $html = str_replace($k, $v, $html);
        }

        // các thuộc tính của URL target, rel...
        $blog_link_option = '';
        if ($instance['rel_xfn'] != '') {
            $blog_link_option .= ' rel="' . $instance['rel_xfn'] . '"';
            //$widget_title_option .= ' rel="' . $rel_xfn . '"';
        }
        if ($instance['open_target'] == 'on') {
            $blog_link_option .= ' target="_blank"';
            //$widget_title_option .= ' target="_blank"';
        }
        //print_r( $instance );
        //print_r( $post_cat );

        // thay thế HTML cho khối term
        $tmp_html = $this->base_model->parent_html_tmp('widget_eb_blog');
        // ưu tiên các giá trị trong instance
        $tmp_html = HtmlTemplate::render($tmp_html, $instance);
        // sau đó mới đến custom
        $html = $this->base_model->tmp_to_html(
            $tmp_html,
            $post_cat,
            [
                'content' => $html,
                'cf_posts_size' => '{{custom_size}}',
                'blog_link_option' => $blog_link_option,
                'widget_title' => $html_widget_title,
                'more_link' => $link_view_more,
                'str_sub_cat' => '',
            ]
        );

        //
        $html = HtmlTemplate::render($html, $instance);
        // thay các size dùng chung
        $html = HtmlTemplate::render(
            $html,
            [
                'main_banner_size' => $this->base_model->get_config($this->getconfig, 'main_banner_size'),
                'second_banner_size' => $this->base_model->get_config($this->getconfig, 'second_banner_size'),
            ]
        );

        //
        return $html;
    }

    /**
     * Lấy size ảnh tự động cho echbay blog khi người dùng không thiết lập size
     **/
    public function getBlogsSize($data, $term_id = 0, $meta_key = 'custom_size')
    {
        $custom_size = '';
        foreach ($data as $v) {
            //print_r($v);
            if (!isset($v['post_meta']) || !isset($v['post_meta']['image']) || empty($v['post_meta']['image'])) {
                continue;
            }

            //
            $file_path = $v['post_meta']['image'];
            // TEST
            //$file_path = 'https://google.com/upload/nofile-nolove.jpg';
            // nếu không phải full URL
            if (strpos($file_path, '//') === false) {
                // gán full path luôn
                $file_path = PUBLIC_PUBLIC_PATH . ltrim($file_path, '/');
            } else {
                // cắt lấy phần upload
                $file_path = PUBLIC_PUBLIC_PATH . ltrim(strstr($file_path, '/upload/'), '/');
            }
            //echo $file_path . '<br>' . PHP_EOL;

            //
            if (!is_file($file_path)) {
                continue;
                /*
            } else if (!is_file($file_path)) {
                echo $v['ID'] . '<br>' . PHP_EOL;
                echo $file_path . '<br>' . PHP_EOL;
                continue;
                */
            }

            //
            $get_file_info = getimagesize($file_path);
            //print_r($get_file_info);

            //
            $custom_size = $get_file_info[1] . '/' . $get_file_info[0];

            // Cập nhật cho database nếu có yêu cầu
            /*
            // có liên quan đến xử lý cache nên tạm thời bỏ
            if ($term_id > 0) {
            }
            */

            //
            break;
        }

        //
        return $custom_size;
    }
}
