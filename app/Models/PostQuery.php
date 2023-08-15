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
                $data['post_shortslug'] = $this->base_model->_eb_non_mark_seo($data['post_shorttitle']);
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
            if (in_array(gettype($v), [
                'string'
            ])) {
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
            $data['post_name'] = $this->base_model->_eb_non_mark_seo($data['post_name']);
            $data['post_name'] = str_replace('.', '-', $data['post_name']);
            $data['post_name'] = str_replace('--', '-', $data['post_name']);

            //
            if ($check_slug === true) {
                $check_slug = $this->base_model->select(
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
                //print_r( $check_slug );
                if (!empty($check_slug)) {
                    return [
                        'code' => __LINE__,
                        'error' => __FUNCTION__ . ' Slug đã được sử dụng ở ' . $check_slug['post_type'] . ' #' . $check_slug['ID'] . ' (' . $data['post_name'] . ')',
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
        //print_r($data);
        //print_r($data_meta);
        //die(__CLASS__ . ':' . __LINE__);

        // insert post
        $result_id = $this->base_model->insert($this->table, $data, true);
        //var_dump( $result_id );
        //print_r( $result_id );

        if ($result_id !== false) {
            //print_r($data_meta);
            //print_r($_POST);
            //die( __CLASS__ . ':' . __LINE__ );

            //
            if (is_numeric($result_id) && $result_id > 0) {
                $this->before_post_permalink($this->select_post($result_id, [
                    //'post_type' => $data['post_type'],
                ]));
                //die(__CLASS__ . ':' . __LINE__);
            }

            // insert/ update meta post
            if (!empty($data_meta)) {
                $this->insert_meta_post($data_meta, $result_id);
            }
            //
            else if (isset($_POST['post_meta'])) {
                $this->insert_meta_post($_POST['post_meta'], $result_id);
            }
            /*
            } else {
            $this->base_model->insert( $this->table, $data, true, 'getQuery' );
            */
        }
        return $result_id;
    }

    public function update_post($post_id, $data, $where = [], $data_meta = [])
    {
        // tiêu đề gắn thêm khi post bị xóa
        $post_trash_title = '___' . PostType::DELETED;

        //
        if (isset($data['post_name'])) {
            if ($data['post_name'] == '') {
                $data['post_name'] = $data['post_title'];
            }
            if ($data['post_name'] != '') {
                if (isset($data['post_status']) && $data['post_status'] != PostType::DELETED) {
                    $data['post_name'] = str_replace($post_trash_title, '', $data['post_name']);
                    $data['post_name'] = $this->base_model->_eb_non_mark_seo($data['post_name']);
                }
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
        //print_r( $data );
        //print_r( $where );

        // nếu đang là xóa bài viết thì bỏ qua việc kiểm tra slug
        if (isset($data['post_status']) && $data['post_status'] == PostType::DELETED) {
            if (isset($data['post_name']) && $data['post_name'] != '') {
                if (strpos($data['post_name'], $post_trash_title) === false) {
                    $data['post_name'] = $this->base_model->_eb_non_mark_seo($data['post_name']);
                    $data['post_name'] .= $post_trash_title;
                }
            }
        }
        // kiểm tra xem có trùng slug không
        else if (isset($data['post_name']) && $data['post_name'] != '') {
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
                $check_slug = $this->base_model->select(
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
                //print_r( $check_slug );
                if (!empty($check_slug)) {
                    //print_r($check_slug);

                    //
                    return [
                        'code' => __LINE__,
                        'error' => __FUNCTION__ . ' Slug đã được sử dụng ở ' . $check_slug['post_type'] . ' #' . $check_slug['ID'] . ' (' . $data['post_name'] . ')',
                    ];
                }
                //die( __CLASS__ . ':' . __LINE__ );
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
        //print_r( $_POST );
        //print_r($data_meta);
        if (!empty($data_meta)) {
            //print_r($data_meta);
            $this->insert_meta_post($data_meta, $post_id);
        } else if (isset($_POST['post_meta'])) {
            $this->insert_meta_post($_POST['post_meta'], $post_id);
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
            //$data[ 'post_meta_data' ] = NULL;
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
            //$where[ '(term_id = ' . $post_cat[ 'term_id' ] . ' OR parent = ' . $post_cat[ 'term_id' ] . ')' ] = NULL;
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
            //$where[ '(term_id = ' . $get_term_id[ 'term_id' ] . ' OR parent = ' . $get_term_id[ 'term_id' ] . ')' ] = NULL;
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
            $data = $this->base_model->select(
                'COUNT(ID) AS c',
                WGR_POST_VIEW,
                $where,
                [
                    'selectCount' => 'ID',
                    'where_or' => $arr_where_or,
                    'where_not_in' => $where_not_in,
                    'group_by' => $ops['group_by'],
                    //'order_by' => $order_by,
                    //'get_sql' => 1,
                    //'show_query' => 1,
                    //'debug_only' => 1,
                    //'offset' => $ops[ 'offset' ],
                    //'limit' => $limit
                ]
            );
            //print_r($data);
            //die(__CLASS__ . ':' . __LINE__);

            //
            if (empty($data)) {
                return 0;
            }
            //return $data[ 0 ][ 'c' ];
            return $data[0]['ID'];
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
                        //print_r( $data_insert );

                        //
                        $_POST['post_meta'] = $data_insert['post_meta'];
                        echo 'Auto create post: ' . $data_insert['post_title'] . ' (' . $ops['post_type'] . ') <br>' . PHP_EOL;
                        $this->insert_post($data_insert, $_POST['post_meta']);
                    }
                }
                //die( 'fjg dghsd sgsd' );
            }
            //return '<a href="./admin/posts/add?post_type=' . $ops[ 'post_type' ] . '">Please add post to category slug #' . $slug . '</a>';
            return '<p class="show-if-admin">Please add post to category slug #' . $slug . '</p>';
        }

        //
        $data = $get_data['posts'];
        //print_r( $data );
        if (isset($ops['return_object'])) {
            // trả về dữ liệu ngay sau khi select xong -> bỏ qua đoạn builder HTML
            return $data;
        }
        $post_cat = $get_data['term'];
        //print_r( $post_cat );
        $instance = $post_cat['term_meta'];
        //print_r( $instance );

        // lấy các giá trị placeholder mặc định -> cho thành trống hết
        $meta_detault = TaxonomyType::meta_default($post_cat['taxonomy']);
        //print_r( $meta_detault );
        foreach ($meta_detault as $k => $v) {
            if (!isset($instance[$k])) {
                $instance[$k] = '';
            }
        }

        //
        //if ($instance['custom_cat_link'] == '#') {
        if ($instance['custom_cat_link'] == '') {
            $instance['custom_cat_link'] = 'javascript:;';
        }
        if ($instance['widget_description'] != '') {
            $instance['widget_description'] = '<div class="eb-widget-blogs-desc">' . nl2br($instance['widget_description']) . '</div>';
        }
        if ($instance['dynamic_tag'] == '') {
            $instance['dynamic_tag'] = 'div';
        }
        if ($instance['dynamic_post_tag'] == '') {
            $instance['dynamic_post_tag'] = 'div';
        }
        if ($instance['custom_size'] == '') {
            $instance['custom_size'] = $this->getconfig->cf_posts_size;
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
            $html_widget_title = '<div data-type="' . $post_cat['taxonomy'] . '" data-id="' . $post_cat['term_id'] . '"
                class="eb-widget-hide-title"></div>';
        } else {
            $html_widget_title = '<{{dynamic_tag}} data-type="' . $post_cat['taxonomy'] . '" data-id="' . $post_cat['term_id'] . '" class="eb-widget-title"><a href="{{custom_cat_link}}">' . $post_cat['name'] . '</a></{{dynamic_tag}}>{{widget_description}}';
        }
        if ($instance['hide_title'] == 'on') {
            $custom_style[] = 'hide-blogs-title';
        }
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
            $show_short_title = '{{post_shorttitle}}';
        }
        // hiển thị nội dung của bài viết
        $show_post_content = '';
        if ($instance['show_post_content'] == 'on') {
            $show_post_content = '{{post_content}}';
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
        //print_r( $instance );
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
            //print_r( $v );
            //continue;
            //die( __CLASS__ . ':' . __LINE__ );

            //
            $p_link = 'javascript:;';
            if (isset($v['post_meta']['url_redirect']) && $v['post_meta']['url_redirect'] != '') {
                //print_r( $v );
                $p_link = $v['post_meta']['url_redirect'];
                //echo $p_link . '<br>' . PHP_EOL;
            }

            // đặt chế độ xuống dòng cho phần tóm tắt khi không có mã HTML trong đấy
            if ($v['post_excerpt'] != '' && strpos($v['post_excerpt'], '</') === false && strpos($v['post_excerpt'], '>') === false) {
                $v['post_excerpt'] = nl2br($v['post_excerpt']);
            }
            $widget_blog_more = '';
            if ($instance['text_view_details'] != '') {
                $widget_blog_more = '<div class="widget-blog-more details-blog-more"><a href="{{p_link}}">' . $instance['text_view_details'] . '</a></div>';
            }

            //
            $a_class = '';
            $a_link = $p_link;
            if (isset($v['post_meta']['url_video']) && $v['post_meta']['url_video'] != '') {
                $a_class = 'open-video';
                $a_link = $v['post_meta']['url_video'];
            }

            // tạo html cho từng node
            //echo $tmp_html;
            $str_node = $this->base_model->tmp_to_html(
                $tmp_html,
                [
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
                ]
            );
            //echo $str_node;
            $str_node = HtmlTemplate::render($str_node, $v['post_meta']);
            //echo $str_node;

            //
            $html .= $str_node;
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
                'more_link' => $instance['text_view_more'] != '' ? '<div class="widget-blog-more"><a href="{{custom_cat_link}}">' . $instance['text_view_more'] . '</a></div>' : '',
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
}
