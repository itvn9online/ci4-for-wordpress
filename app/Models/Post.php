<?php

namespace App\ Models;

// Libraries
use App\ Libraries\ LanguageCost;
use App\ Libraries\ PostType;
use App\ Libraries\ TaxonomyType;

//
class Post extends EB_Model {
    protected $table = 'wp_posts';
    protected $primaryKey = 'ID';

    protected $metaTable = 'wp_postmeta';
    protected $metaKey = 'meta_id';

    public $product_html_node = '';
    public $blog_html_node = '';
    public $getconfig = NULL;

    function __construct() {
        parent::__construct();

        //
        $this->option_model = new\ App\ Models\ Option();
        $this->term_model = new\ App\ Models\ Term();

        // tạo block html cho phần sản phẩm
        //echo THEMEPATH . '<br>' . "\n";
        $this->product_html_node = $this->base_model->get_html_tmp( 'thread_node' );
        $this->product_html_node = '<li data-id="{tmp.ID}" data-type="{tmp.post_type}" data-price="{tmp.trv_num_giamoi}" data-per="{tmp.pt}" data-link="{tmp.p_link}" data-status="{tmp.product_status}" class="hide-if-gia-zero">' . $this->product_html_node . '</li>';

        //
        $this->blog_html_node = $this->base_model->get_html_tmp( 'blogs_node' );


        //
        $getconfig = $this->option_model->list_config();
        //print_r( $getconfig );
        $getconfig = ( object )$getconfig;
        $getconfig->cf_product_size = $this->base_model->get_config( $getconfig, 'cf_product_size', 1 );
        $getconfig->cf_blog_size = $this->base_model->get_config( $getconfig, 'cf_blog_size', '2/3' );
        if ( empty( $getconfig->cf_blog_description_length ) ) {
            $getconfig->cf_blog_description_length = 250;
        }
        //print_r( $getconfig );
        $this->getconfig = $getconfig;

        //
        $this->session = \Config\ Services::session();
    }

    function insert_post( $data ) {
        $session_data = $this->session->get( 'admin' );
        if ( empty( $session_data ) ) {
            $post_author = 0;
        } else {
            $post_author = $session_data[ 'userID' ];
        }

        // các dữ liệu mặc định
        $default_data = [
            'post_author' => $post_author,
            'post_date' => date( 'Y-m-d H:i:s' ),
            'lang_key' => LanguageCost::lang_key(),
        ];
        if ( empty( $default_data[ 'post_author' ] ) ) {
            $default_data[ 'post_author' ] = 1;
        }
        $default_data[ 'post_date_gmt' ] = $default_data[ 'post_date' ];
        $default_data[ 'post_modified' ] = $default_data[ 'post_date' ];
        $default_data[ 'post_modified_gmt' ] = $default_data[ 'post_date' ];

        //
        if ( $data[ 'post_name' ] == '' ) {
            $data[ 'post_name' ] = $data[ 'post_title' ];
        }
        if ( $data[ 'post_name' ] != '' ) {
            $data[ 'post_name' ] = $this->base_model->_eb_non_mark_seo( $data[ 'post_name' ] );
        }
        foreach ( $default_data as $k => $v ) {
            if ( !isset( $data[ $k ] ) ) {
                $data[ $k ] = $v;
            }
        }

        // insert post
        //print_r( $data );
        $result_id = $this->base_model->insert( $this->table, $data, true );

        if ( $result_id > 0 ) {
            // insert/ update meta post
            if ( isset( $_POST[ 'post_meta' ] ) ) {
                $this->insert_meta_post( $_POST[ 'post_meta' ], $result_id );
            }

            //
            return $result_id;
        }
        return false;
    }

    function select_post( $post_id, $where = [] ) {
        if ( $post_id > 0 ) {
            $where[ $this->primaryKey ] = $post_id;
        }
        if ( empty( $where ) ) {
            return [];
        }

        // select dữ liệu từ 1 bảng bất kỳ
        $data = $this->base_model->select( '*', $this->table, $where, array(
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 2,
            'limit' => 1
        ) );

        // lấy meta của post này
        if ( !empty( $data ) ) {
            $data[ 'post_meta' ] = $this->arr_meta_post( $data[ $this->primaryKey ] );
        }
        //print_r( $data );

        //
        return $data;
    }

    function select_public_post( $post_id, $where = [] ) {
        // các tham số bắt buộc
        $where[ 'post_status' ] = PostType::PUBLIC;
        $where[ 'lang_key' ] = LanguageCost::lang_key();

        //
        return $this->select_post( $post_id, $where );
    }

    // trả về danh sách post theo term_id
    function select_list_post( $post_type, $post_cat = [], $limit = 1, $order_by = [], $ops = [] ) {
        //print_r( $post_cat );
        //print_r( $ops );
        if ( !isset( $ops[ 'offset' ] ) ) {
            $ops[ 'offset' ] = 0;
        } else if ( $ops[ 'offset' ] < 0 ) {
            $ops[ 'offset' ] = 0;
        }

        //
        if ( empty( $order_by ) ) {
            $order_by = [
                'menu_order' => 'DESC',
                $this->primaryKey => 'DESC',
            ];
        }


        //
        $where = [
            'post_type' => $post_type,
            'post_status' => 'publish',
            'taxonomy' => $post_cat[ 'taxonomy' ],
            //'(wp_term_taxonomy.term_id = ' . $post_cat[ 'term_id' ] . ' OR wp_term_taxonomy.parent = ' . $post_cat[ 'term_id' ] . ')' => NULL,
        ];
        /*
        if ( isset( $post_cat[ 'taxonomy' ] ) && $post_cat[ 'taxonomy' ] != '' ) {
            $where[ 'wp_term_taxonomy.taxonomy' ] = $post_cat[ 'taxonomy' ];
        }
        */

        //
        $arr_or_where = [];
        // tìm theo ID truyền vào
        if ( isset( $post_cat[ 'term_id' ] ) && $post_cat[ 'term_id' ] > 0 ) {
            //$where[ '(term_id = ' . $post_cat[ 'term_id' ] . ' OR parent = ' . $post_cat[ 'term_id' ] . ')' ] = NULL;
            $arr_or_where = [
                'term_id' => $post_cat[ 'term_id' ],
                'parent' => $post_cat[ 'term_id' ],
            ];
        }
        // tìm theo slug truyền vào
        else if ( isset( $post_cat[ 'slug' ] ) && $post_cat[ 'slug' ] != '' ) {
            // lấy term_id theo slug truyền vào
            $get_term_id = $this->term_model->get_taxonomy( [
                'slug' => $post_cat[ 'slug' ],
                'is_deleted' => DeletedStatus::DEFAULT,
                'taxonomy' => $post_cat[ 'taxonomy' ],
            ], 1, 'term_id' );
            //print_r( $get_term_id );

            // không có thì trả về lỗi
            if ( empty( $get_term_id ) ) {
                return [];
            }
            // có thì gán lấy theo term_id
            //$where[ '(term_id = ' . $get_term_id[ 'term_id' ] . ' OR parent = ' . $get_term_id[ 'term_id' ] . ')' ] = NULL;
            $arr_or_where = [
                'term_id' => $get_term_id[ 'term_id' ],
                'parent' => $get_term_id[ 'term_id' ],
            ];
        }

        //
        if ( isset( $ops[ 'count_record' ] ) ) {
            $data = $this->base_model->select( 'COUNT(ID) AS c', 'v_posts', $where, [
                'or_where' => $arr_or_where,
                //'order_by' => $order_by,
                //'get_sql' => 1,
                //'show_query' => 1,
                //'debug_only' => 1,
                //'offset' => $ops[ 'offset' ],
                //'limit' => $limit
            ] );
            //print_r( $data );

            return $data[ 0 ][ 'c' ];
        } else {
            // nếu có chỉ định chỉ lấy các cột cần thiết
            if ( isset( $ops[ 'select' ] ) ) {
                // chuẩn hóa đầu vào
                $ops[ 'select' ] = str_replace( ' ', '', $ops[ 'select' ] );
                /*
                $ops[ 'select' ] = explode( ',', $ops[ 'select' ] );
                //print_r( $ops[ 'select' ] );
                $ops[ 'select' ] = implode( ',', $ops[ 'select' ] );
                */
                //$ops[ 'select' ] = $ops[ 'select' ];
                //echo $ops[ 'select' ] . '<br>' . "\n";
            } else {
                $ops[ 'select' ] = '*';
            }

            // lấy danh sách bài viết thuộc nhóm này
            $data = $this->base_model->select( $ops[ 'select' ], 'v_posts', $where, [
                'or_where' => $arr_or_where,
                'order_by' => $order_by,
                //'get_sql' => 1,
                //'show_query' => 1,
                //'debug_only' => 1,
                'offset' => $ops[ 'offset' ],
                'limit' => $limit
            ] );
            //print_r( $data );
        }

        //
        if ( !empty( $data ) && !isset( $ops[ 'no_meta' ] ) ) {
            if ( $limit === 1 ) {
                $data = [ $data ];
            }

            //
            $data = $this->list_meta_post( $data );
        }
        //print_r( $data );

        //
        return $data;
    }

    function update_post( $post_id, $data, $where = [] ) {
        if ( isset( $data[ 'post_name' ] ) ) {
            if ( $data[ 'post_name' ] == '' ) {
                $data[ 'post_name' ] = $data[ 'post_title' ];
            }
            if ( $data[ 'post_name' ] != '' ) {
                $data[ 'post_name' ] = $this->base_model->_eb_non_mark_seo( $data[ 'post_name' ] );
            }
        }
        if ( !isset( $data[ 'post_modified' ] ) || $data[ 'post_modified' ] == '' ) {
            $data[ 'post_modified' ] = date( 'Y-m-d H:i:s' );
            $data[ 'post_modified_gmt' ] = $data[ 'post_modified' ];
        }

        //
        $where[ $this->primaryKey ] = $post_id;
        //print_r( $data );
        //print_r( $where );

        //
        $this->base_model->update_multiple( $this->table, $data, $where, [
            'debug_backtrace' => debug_backtrace()[ 1 ][ 'function' ]
        ] );

        //
        if ( isset( $_POST[ 'post_meta' ] ) ) {
            $this->insert_meta_post( $_POST[ 'post_meta' ], $post_id );
        }
    }

    // thêm post meta
    function insert_meta_post( $meta_data, $post_id ) {
        //print_r( $meta_data );
        if ( !is_array( $meta_data ) || empty( $meta_data ) ) {
            return false;
        }

        // lấy toàn bộ meta của post này
        $meta_exist = $this->arr_meta_post( $post_id );
        //print_r( $meta_exist );

        // xử lý riêng đối với post_category
        if ( isset( $meta_data[ 'post_category' ] ) && gettype( $meta_data[ 'post_category' ] ) == 'array' ) {
            $meta_data[ 'post_category' ] = implode( ',', $meta_data[ 'post_category' ] );
        }
        if ( isset( $meta_data[ 'post_category' ] ) ) {
            $this->term_model->insert_term_relationships( $post_id, $meta_data[ 'post_category' ] );
        }

        //
        $insert_meta = [];
        $update_meta = [];
        foreach ( $meta_data as $k => $v ) {
            if ( isset( $meta_exist[ $k ] ) ) {
                $update_meta[ $k ] = $v;
            } else if ( $v != '' ) {
                $insert_meta[ $k ] = $v;
            }
        }

        // các meta chưa có thì insert
        //print_r( $insert_meta );
        foreach ( $insert_meta as $k => $v ) {
            $this->base_model->insert( $this->metaTable, [
                'post_id' => $post_id,
                'meta_key' => $k,
                'meta_value' => $v,
            ] );
        }

        // các meta có rồi thì update
        //print_r( $update_meta );
        foreach ( $update_meta as $k => $v ) {
            $this->base_model->update_multiple( $this->metaTable, [
                'meta_value' => $v,
            ], [
                'post_id' => $post_id,
                'meta_key' => $k,
            ] );
        }

        //
        //die( __FILE__ . ':' . __LINE__ );
        return true;
    }

    function get_meta_post( $post_id, $key = '' ) {
        // lấy theo key cụ thể
        if ( $key != '' ) {
            $data = $this->base_model - select( '*', $this->metaTable, array(
                // các kiểu điều kiện where
                'post_id' => $post_id,
                'meta_key' => $key,
            ), array(
                'order_by' => array(
                    $this->metaKey => 'DESC'
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 2,
                'limit' => 1
            ) );

            //
            if ( empty( $data ) ) {
                return '';
            }
            return $data[ 'meta_value' ];
        }

        // lấy toàn bộ meta
        return $this->base_model->select( '*', $this->metaTable, array(
            // các kiểu điều kiện where
            'post_id' => $post_id
        ), array(
            'group_by' => array(
                'meta_key',
            ),
            'order_by' => array(
                $this->metaKey => 'DESC'
            ),
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 2,
            //'limit' => 3
        ) );
    }

    // trả về danh sách meta post dưới dạng key => value
    function arr_meta_post( $post_id ) {
        $data = $this->get_meta_post( $post_id, '', $this->metaTable );
        //print_r( $data );

        //
        $meta_data = [];
        foreach ( $data as $k => $v ) {
            $meta_data[ $v[ 'meta_key' ] ] = $v[ 'meta_value' ];
        }

        // hỗ trợ kiểu danh mục từ echbaydotcom
        if ( !isset( $meta_data[ 'post_category' ] ) || $meta_data[ 'post_category' ] == '' ) {
            $sql = $this->base_model->select( 'term_taxonomy_id', 'wp_term_relationships', array(
                // các kiểu điều kiện where
                'object_id' => $post_id
            ), array(
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 2,
                //'limit' => 3
            ) );
            //print_r( $sql );
            $term_relationships = [];
            foreach ( $sql as $k => $v ) {
                $term_relationships[] = $v[ 'term_taxonomy_id' ];
            }
            $meta_data[ 'post_category' ] = implode( ',', $term_relationships );
        }

        //
        return $meta_data;
    }

    // hàm này sẽ kiểm tra xem có meta tương ứng của post không, có thì in ra luôn
    function return_meta_post( $data, $key, $default_value = '' ) {
        if ( isset( $data[ $key ] ) ) {
            return $data[ $key ];
        } else if ( isset( $data[ 'post_meta' ] ) ) {
            return $this->return_meta_post( $data[ 'post_meta' ], $key );
        }

        //
        return $default_value;
    }

    function show_meta_post( $data, $key, $default_value = '' ) {
        echo $this->return_meta_post( $data, $key, $default_value );
    }

    // tương tự show_meta_post -> chỉ khác là sẽ truyền thẳng data post_meta vào luôn
    function echo_meta_post( $data, $key, $default_value = '' ) {
        echo $this->return_meta_post( $data[ 'post_meta' ], $key, $default_value );
    }

    function get_post_thumbnail( $data, $key = 'image', $default_value = 'images/noavatar.png' ) {
        $result = $this->return_meta_post( $data, $key );
        //echo $result . '<br>' . "\n";

        // hỗ trợ dữ liệu từ echbaydotcom
        if ( $result == '' ) {
            $result = $this->return_meta_post( $data, '_eb_product_avatar' );
            //echo $result . '<br>' . "\n";
        }
        if ( $result == '' ) {
            $result = $default_value;
        }

        //
        return $result;
    }

    // function giả lập widget echbay blog trong wordpress
    function echbay_blog( $slug, $ops = [] ) {
        // đầu vào mặc định
        if ( !isset( $ops[ 'post_type' ] ) || $ops[ 'post_type' ] == '' ) {
            $ops[ 'post_type' ] = $this->base_model->default_post_type;
        }
        if ( !isset( $ops[ 'taxonomy' ] ) || $ops[ 'taxonomy' ] == '' ) {
            $ops[ 'taxonomy' ] = $this->base_model->default_taxonomy;
        }
        if ( !isset( $ops[ 'limit' ] ) || $ops[ 'limit' ] < 1 ) {
            $ops[ 'limit' ] = 0;
        }

        // trả về dữ liệu
        $get_data = $this->get_auto_post( $slug, $ops[ 'post_type' ], $ops[ 'taxonomy' ], $ops[ 'limit' ] );
        //print_r( $get_data );
        //die( __FILE__ . ':' . __LINE__ );
        if ( !isset( $get_data[ 'posts' ] ) || empty( $get_data[ 'posts' ] ) ) {
            // nếu có tham số cho phép nhân bản dữ liệu cho các ngôn ngữ khác
            if ( isset( $ops[ 'auto_clone' ] ) && $ops[ 'auto_clone' ] === 1 && LanguageCost::lang_key() != LanguageCost::default_lang() ) {
                //print_r( $get_data );
                // tiến hành lấy dữ liệu mẫu để nhân
                $clone_data = $this->get_auto_post( $slug, $ops[ 'post_type' ], $ops[ 'taxonomy' ], $ops[ 'limit' ], [
                    'lang_key' => LanguageCost::default_lang()
                ] );
                //print_r( $clone_data );

                // bắt đầu nhân bản
                if ( isset( $clone_data[ 'posts' ] ) && !empty( $clone_data[ 'posts' ] ) ) {
                    foreach ( $clone_data[ 'posts' ] as $k => $v ) {
                        //print_r( $v );

                        //
                        $data_insert = $v;
                        $data_insert[ 'ID' ] = 0;
                        unset( $data_insert[ 'ID' ] );
                        $data_insert[ 'lang_key' ] = LanguageCost::lang_key();
                        $data_insert[ 'lang_parent' ] = $v[ 'ID' ];
                        $data_insert[ 'post_meta' ][ 'post_category' ] = $get_data[ 'term' ][ 'term_id' ];
                        //print_r( $data_insert );

                        //
                        $_POST[ 'post_meta' ] = $data_insert[ 'post_meta' ];
                        echo 'Auto create post: ' . $data_insert[ 'post_title' ] . ' (' . $ops[ 'post_type' ] . ') <br>' . "\n";
                        $this->insert_post( $data_insert );
                    }
                }
                //die( 'fjg dghsd sgsd' );
            }
            return 'Please add post to category slug#' . $slug;
        }

        //
        $data = $get_data[ 'posts' ];
        //print_r( $data );
        if ( isset( $ops[ 'return_object' ] ) ) {
            return $data;
        }
        $post_cat = $get_data[ 'term' ];
        //print_r( $post_cat );
        $instance = $post_cat[ 'term_meta' ];
        //print_r( $instance );

        // lấy các giá trị placeholder mặc định -> cho thành trống hết
        $meta_detault = TaxonomyType::meta_default( $post_cat[ 'taxonomy' ] );
        //print_r( $meta_detault );
        foreach ( $meta_detault as $k => $v ) {
            if ( !isset( $instance[ $k ] ) ) {
                $instance[ $k ] = '';
            }
        }

        //
        if ( $instance[ 'custom_cat_link' ] == '#' ) {
            $instance[ 'custom_cat_link' ] = 'javascript:;';
        }
        if ( $instance[ 'widget_description' ] != '' ) {
            $instance[ 'widget_description' ] = '<div class="echbay-widget-blogs-desc">' . nl2br( $instance[ 'widget_description' ] ) . '</div>';
        }
        if ( $instance[ 'dynamic_tag' ] == '' ) {
            $instance[ 'dynamic_tag' ] = 'div';
        }
        if ( $instance[ 'dynamic_post_tag' ] == '' ) {
            $instance[ 'dynamic_post_tag' ] = 'div';
        }
        if ( $instance[ 'custom_size' ] == '' ) {
            $instance[ 'custom_size' ] = $this->getconfig->cf_blog_size;
        }
        if ( $instance[ 'custom_id' ] != '' ) {
            $instance[ 'custom_id' ] = ' id="' . $instance[ 'custom_id' ] . '"';
        }
        $instance[ 'max_width' ] = str_replace( '  ', ' ', trim( $instance[ 'max_width' ] . ' ' . $instance[ 'custom_style' ] ) );
        if ( $instance[ 'hide_widget_title' ] == 'on' ) {
            $instance[ 'max_width' ] .= ' hide-widget-title';
        }
        if ( $instance[ 'hide_title' ] == 'on' ) {
            $instance[ 'max_width' ] .= ' hide-blogs-title';
        }
        if ( $instance[ 'hide_description' ] == 'on' ) {
            $instance[ 'max_width' ] .= ' hide-blogs-description';
        }
        if ( $instance[ 'hide_info' ] == 'on' ) {
            $instance[ 'max_width' ] .= ' hide-blogs-info';
        }
        if ( $instance[ 'run_slider' ] == 'on' ) {
            $instance[ 'max_width' ] .= ' ebwidget-run-slider';
        }
        /*
        if ( $instance[ 'open_youtube' ] == 'on' ) {
            $instance[ 'max_width' ] .= ' youtube-quick-view';
        }
        */
        if ( $instance[ 'text_view_more' ] != '' || $instance[ 'text_view_details' ] != 'text_view_details' ) {
            $instance[ 'max_width' ] .= ' show-view-more';
        }
        // thêm class theo slug
        $instance[ 'max_width' ] .= ' ' . str_replace( '-', '', $slug );
        // hiển thị nội dung của bài viết
        if ( $instance[ 'show_post_content' ] == 'on' ) {
            //
        }
        //print_r( $instance );
        if ( isset( $ops[ 'add_class' ] ) ) {
            $instance[ 'max_width' ] .= ' ' . $ops[ 'add_class' ];
        }
        $instance[ 'max_width' ] .= ' blog-section';

        // cố định file HTML để tối ưu với SEO
        $html_node = 'blogs_node';
        if ( $instance[ 'post_cloumn' ] == 'chi_anh' ) {
            $html_node = 'blogs_node_chi_anh';
        } else if ( $instance[ 'post_cloumn' ] == 'chi_chu' ) {
            $html_node = 'blogs_node_chi_chu';
        } else if ( $instance[ 'post_cloumn' ] == 'text_only' ) {
            $html_node = 'blogs_node_text_only';
        } else if ( $instance[ 'hide_description' ] == 'on' && $instance[ 'hide_info' ] == 'on' ) {
            $html_node = 'blogs_node_avt_title';
        }
        echo '<!-- ' . $html_node . ' --> ' . "\n";
        $tmp_html = $this->base_model->parent_html_tmp( $html_node );
        //echo $tmp_html . '<br>' . "\n";

        // tạo css chỉnh cột
        if ( $instance[ 'post_cloumn' ] != '' ) {
            $instance[ 'post_cloumn' ] = 'blogs_node_' . $instance[ 'post_cloumn' ];
        }

        // do hàm select có chỉnh sửa với limit -> ở đây phải thao tác ngược lại
        if ( $ops[ 'limit' ] == 1 ) {
            $data = [ $data ];
        }
        $html = '';
        foreach ( $data as $v ) {
            //print_r( $v );

            //
            $p_link = 'javascript:;';
            if ( isset( $v[ 'post_meta' ][ 'url_redirect' ] ) && $v[ 'post_meta' ][ 'url_redirect' ] != '' ) {
                $p_link = $v[ 'post_meta' ][ 'url_redirect' ];
            }

            //
            if ( $v[ 'post_excerpt' ] != '' ) {
                $v[ 'post_excerpt' ] = nl2br( $v[ 'post_excerpt' ] );
            }
            if ( $instance[ 'text_view_details' ] != '' ) {
                $v[ 'post_excerpt' ] .= '<div class="widget-blog-more details-blog-more"><a href="%p_link%">' . $instance[ 'text_view_details' ] . '</a></div>';
            }

            // tạo html cho từng node
            $str_node = $this->base_model->tmp_to_html( $tmp_html, $v, [
                'taxonomy_key' => $ops[ 'taxonomy' ],
                'p_link' => $p_link,
            ] );
            $str_node = $this->base_model->tmp_to_html( $str_node, $v[ 'post_meta' ] );

            //
            $html .= $str_node;
        }

        // các thuộc tính của URL target, rel...
        $blog_link_option = '';
        if ( $instance[ 'rel_xfn' ] != '' ) {
            $blog_link_option .= ' rel="' . $instance[ 'rel_xfn' ] . '"';
            //$widget_title_option .= ' rel="' . $rel_xfn . '"';
        }
        if ( $instance[ 'open_target' ] == 'on' ) {
            $blog_link_option .= ' target="_blank"';
            //$widget_title_option .= ' target="_blank"';
        }
        //$max_width = '';
        //$num_line = '';
        //$post_cloumn = '';

        // thay thế HTML cho khối term
        $tmp_html = $this->base_model->parent_html_tmp( 'widget_echbay_blog' );
        $html = $this->base_model->tmp_to_html( $tmp_html, $post_cat, [
            'content' => $html,
            'cf_blog_size' => '%custom_size%',
            'blog_link_option' => $blog_link_option,
            'widget_title' => $instance[ 'hide_widget_title' ] == 'on' ? '' : '<%dynamic_tag% data-type="' . $post_cat[ 'taxonomy' ] . '" data-id="' . $post_cat[ 'term_id' ] . '" class="echbay-widget-title"><a href="%custom_cat_link%">' . $post_cat[ 'name' ] . '</a></%dynamic_tag%> %widget_description%',
            /*
            'max_width' => $max_width,
            'num_line' => $num_line,
            'post_cloumn' => $post_cloumn,
            */
            'more_link' => $instance[ 'text_view_more' ] != '' ? '<div class="widget-blog-more"><a href="%custom_cat_link%">' . $instance[ 'text_view_more' ] . '</a></div>' : '',
            'str_sub_cat' => '',
        ] );

        //
        $html = $this->base_model->tmp_to_html( $html, $instance );
        // thay các size dùng chung
        $html = $this->base_model->tmp_to_html( $html, [
            'main_banner_size' => $this->base_model->get_config( $this->getconfig, 'main_banner_size' ),
            'second_banner_size' => $this->base_model->get_config( $this->getconfig, 'second_banner_size' ),
        ] );

        //
        return $html;
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

    // lấy về danh sách meta post cho toàn bộ data được truyền vào
    function list_meta_post( $data ) {
        foreach ( $data as $k => $v ) {
            //print_r( $v );
            $data[ $k ][ 'post_meta' ] = $this->arr_meta_post( $v[ 'ID' ] );
        }
        //print_r( $data );

        //
        return $data;
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

    // trả về khối HTML của từng sản phẩm trong danh mục
    function get_the_blog_node( $data, $ops = [] ) {
        //print_r( $data );
        if ( isset( $ops[ 'tmp_html' ] ) && $ops[ 'tmp_html' ] != '' ) {
            $tmp_html = $ops[ 'tmp_html' ];
        } else {
            $tmp_html = $this->blog_html_node;
        }
        //echo $tmp_html . '<br>' . "\n";

        //
        $data[ 'p_link' ] = $this->get_the_permalink( $data );
        if ( isset( $ops[ 'taxonomy_post_size' ] ) && $ops[ 'taxonomy_post_size' ] != '' ) {
            $data[ 'cf_blog_size' ] = $ops[ 'taxonomy_post_size' ];
        } else {
            $data[ 'cf_blog_size' ] = $this->getconfig->cf_blog_size;
        }
        $data[ 'post_category' ] = 0;
        $data[ 'taxonomy_key' ] = '';
        $data[ 'dynamic_post_tag' ] = 'h3';
        $data[ 'blog_link_option' ] = '';
        $data[ 'image' ] = $this->get_post_thumbnail( $data );

        if ( $data[ 'post_excerpt' ] == '' ) {
            $data[ 'post_excerpt' ] = strip_tags( $data[ 'post_content' ] );
            //echo $this->getconfig->cf_blog_description_length . ' aaaaaaaaaa <br>' . "\n";
            $data[ 'post_excerpt' ] = $this->base_model->short_string( $data[ 'post_excerpt' ], $this->getconfig->cf_blog_description_length );
        }

        //
        return $this->base_model->tmp_to_html( $tmp_html, $data );
    }

    function the_blog_node( $data, $ops = [] ) {
        echo $this->get_the_blog_node( $data, $ops );
    }

    // chỉ trả về link admin của 1 post
    function get_admin_permalink( $post_type = '', $id = 0 ) {
        $controller_slug = 'posts';
        if ( $post_type == PostType::MENU ) {
            $controller_slug = 'menus';
        }
        $url = base_url( 'admin/' . $controller_slug . '/add' ) . '?post_type=' . $post_type;
        if ( $id > 0 ) {
            $url .= '&id=' . $id;
        }
        return $url;
    }

    // thường dùng trong view -> in ra link admin của 1 post
    function admin_permalink( $post_type = '', $id = 0 ) {
        echo $this->get_admin_permalink( $post_type, $id );
    }

    // trả về url của 1 post
    function get_the_permalink( $data ) {
        //print_r( $data );

        //
        if ( $data[ 'post_type' ] == PostType::POST ) {
            return DYNAMIC_BASE_URL . $data[ 'ID' ] . '/' . $data[ 'post_name' ];
        } else if ( $data[ 'post_type' ] == PostType::BLOG ) {
            return DYNAMIC_BASE_URL . PostType::BLOG . '-' . $data[ 'ID' ] . '/' . $data[ 'post_name' ];
        } else if ( $data[ 'post_type' ] == PostType::PAGE ) {
            return DYNAMIC_BASE_URL . $data[ 'post_name' ];
        }
        return DYNAMIC_BASE_URL . 'p=' . $data[ 'ID' ];
    }

    // thường dùng trong view -> in ra link admin của 1 post
    function the_permalink( $data ) {
        echo $this->get_the_permalink( $data );
    }

    function quick_add_menu() {
        $arr_result = [];

        $allow_taxonomy = [
            TaxonomyType::POSTS,
            TaxonomyType::TAGS,
            TaxonomyType::BLOGS,
            TaxonomyType::BLOG_TAGS,
        ];

        foreach ( $allow_taxonomy as $allow ) {
            $category_list = $this->term_model->get_all_taxonomy( $allow, 0, [
                //'get_meta' => true,
                //'get_child' => true
            ] );
            //print_r( $category_list );
            if ( empty( $category_list ) ) {
                continue;
            }
            $arr_result[] = '<option class="bold" disabled>' . TaxonomyType::list( $allow ) . '</option>';

            //
            foreach ( $category_list as $cat_key => $cat_val ) {
                $arr_result[] = '<option value="' . $this->term_model->get_the_permalink( $cat_val ) . '">' . $cat_val[ 'name' ] . '</option>';
            }
        }


        //
        $allow_post_type = [
            PostType::PAGE,
            PostType::POST,
            PostType::BLOG,
        ];

        foreach ( $allow_post_type as $allow ) {
            // các kiểu điều kiện where
            $where = [
                //'post_status !=' => PostType::DELETED,
                'post_type' => $allow,
                'lang_key' => LanguageCost::lang_key()
            ];

            $filter = [
                'where_in' => array(
                    'post_status' => array(
                        PostType::DRAFT,
                        PostType::PUBLIC,
                        PostType::PENDING,
                    )
                ),
                'order_by' => array(
                    'menu_order' => 'DESC',
                    'post_date' => 'DESC',
                    //'post_modified' => 'DESC',
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                'offset' => 0,
                'limit' => 50
            ];
            $page_list = $this->base_model->select( '*', $this->table, $where, $filter );
            //print_r( $page_list );

            //
            if ( empty( $page_list ) ) {
                continue;
            }
            $arr_result[] = '<option class="bold" disabled>' . PostType::list( $allow ) . '</option>';

            //
            foreach ( $page_list as $post_key => $post_val ) {
                $arr_result[] = '<option value="' . $this->get_the_permalink( $post_val ) . '">' . $post_val[ 'post_title' ] . '</option>';
            }
        }

        //
        $arr_result[] = '<option class="bold" disabled>Menu hệ thống</option>';
        $arr_result[] = '<option value="./' . CUSTOM_ADMIN_URI . '">Quản trị hệ thống</option>';
        $arr_result[] = '<option value="./guest/login">Đăng nhập</option>';
        $arr_result[] = '<option value="./guest/register">Đăng ký</option>';
        $arr_result[] = '<option value="./guest/resetpass">Quên mật khẩu</option>';
        $arr_result[] = '<option value="./users/profile">Tài khoản</option>';
        $arr_result[] = '<option value="./users/logout">Đăng xuất</option>';
        //$arr_result[] = '<option value="./users/changepass">Đổi mật khẩu</option>';

        //
        return $arr_result;
    }

    // tự động tạo slider nếu có
    public function get_the_slider( $data, $taxonomy_slider = [], $second_slider = '' ) {
        //print_r( $data );
        //print_r( $taxonomy_slider );
        if ( empty( $data ) ) {
            return '';
        }

        // ưu tiên tìm trong 
        if ( isset( $data[ 'post_meta' ][ 'post_auto_slider' ] ) && $data[ 'post_meta' ][ 'post_auto_slider' ] == 'on' ) {
            //echo 'post_auto_slider';
            //print_r( $data );
            return $this->get_the_ads( $data[ 'post_name' ] . '-' . $data[ 'post_type' ] . '-' . $data[ $this->primaryKey ], 0, [
                'add_class' => 'taxonomy-auto-slider'
            ] );
        } else {
            // thử tìm của bài cha nếu có
            if ( $data[ 'post_parent' ] > 0 ) {
                $parent_data = $this->select_post( $data[ 'post_parent' ] );
                //print_r( $parent_data );

                // thử tìm slider của bài cha -> có thì trả về luôn
                $parent_slider = $this->get_the_slider( $parent_data );
                if ( $parent_slider != '' ) {
                    return $parent_slider;
                }
            }

            // không có -> sử dụng của taxonomy
            $tax_slider = $this->term_model->get_the_slider( $taxonomy_slider, $second_slider );
            if ( !empty( $tax_slider ) ) {
                $tax_slider = $this->get_the_ads( $tax_slider, 0, [
                    'add_class' => 'taxonomy-auto-slider'
                ] );
                if ( !empty( $tax_slider ) ) {
                    return $tax_slider;
                }
            }

            // đến đây vẫn không có -> tìm slider thứ cấp (slider dùng chung cho cả website)
            //$second_slider = 'top-main-slider'; // main_slider_slug
            if ( $second_slider != '' ) {
                return $this->get_the_ads( $second_slider, 0, [
                    'add_class' => 'taxonomy-auto-slider'
                ] );
            }
        }

        //
        return '';
    }
    public function the_slider( $data, $taxonomy_slider = [], $second_slider = '' ) {
        echo $this->get_the_slider( $data, $taxonomy_slider, $second_slider );
    }

    function get_auto_post( $slug, $post_type = 'post', $taxonomy = 'category', $limit = 0, $ops = [] ) {
        //echo $slug . '<br>' . "\n";
        if ( $slug == '' ) {
            die( 'slug for get_auto_post is NULL!' );
        }

        //
        if ( $post_type == '' ) {
            $post_type = $this->base_model->default_post_type;
        }
        if ( $taxonomy == '' ) {
            $taxonomy = $this->base_model->default_taxonomy;
        }

        //
        $post_cat = $this->term_model->get_cat_post( $slug, $post_type, $taxonomy, true, $ops );
        $post_cat = $this->term_model->terms_meta_post( [ $post_cat ] );
        $post_cat = $post_cat[ 0 ];
        //print_r( $post_cat );
        //echo $post_cat[ 'taxonomy' ] . '<br>' . "\n";
        //echo $taxonomy . '<br>' . "\n";

        //
        if ( $limit < 1 && isset( $post_cat[ 'term_meta' ][ 'post_number' ] ) ) {
            $limit = $post_cat[ 'term_meta' ][ 'post_number' ];
        }
        if ( $limit < 1 ) {
            $limit = 1;
        }

        // lấy danh sách bài viết thuộc nhóm này
        $data = $this->select_list_post( $post_type, $post_cat, $limit );
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
    public function get_posts( $prams, $ops = [] ) {
        if ( !isset( $prams[ 'limit' ] ) ) {
            $prams[ 'limit' ] = isset( $ops[ 'limit' ] ) ? $ops[ 'limit' ] : 0;
        }
        if ( !isset( $prams[ 'offset' ] ) ) {
            $prams[ 'offset' ] = isset( $ops[ 'offset' ] ) ? $ops[ 'offset' ] : 0;
        }
        if ( !isset( $prams[ 'order_by' ] ) ) {
            $prams[ 'order_by' ] = isset( $ops[ 'order_by' ] ) ? $ops[ 'order_by' ] : [];
        }
        //print_r( $prams );

        // kiểu dữ liệu trả về
        $ops[ 'offset' ] = $prams[ 'offset' ];
        // trả về số lượng bản ghi
        if ( isset( $ops[ 'count' ] ) ) {
            $ops[ 'count_record' ] = 1;
        }
        //print_r( $ops );

        //
        $data = $this->select_list_post( $prams[ 'post_type' ], $prams, $prams[ 'limit' ], $prams[ 'order_by' ], $ops );

        //
        return $data;
    }

    // đồng bộ tham số đầu vào
    public function sync_post_parms( $prams ) {
        // nếu đầu vào không phải array
        if ( !is_array( $prams ) ) {
            if ( empty( $prams ) ) {
                return [];
                //return debug_backtrace()[ 1 ][ 'function' ] . ' $prams is NULL!';
            }

            // tự tạo theo term_id
            if ( is_numeric( $prams ) ) {
                $prams = [
                    'term_id' => $prams
                ];
            }
            // hoặc slug
            else if ( is_string( $prams ) ) {
                $prams = [
                    'slug' => $prams
                ];
            }
        }
        //print_r( $prams );
        //die( 'dgh dgsda d' );
        return $prams;
    }
    public function sync_post_ops( $ops ) {
        // nếu đầu vào không phải array
        if ( !is_array( $ops ) ) {
            // tự tạo limit nếu đầu vào là 1 số
            if ( !empty( $ops ) && is_numeric( $ops ) ) {
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

    // blog
    public function get_blogs_by( $prams, $ops = [] ) {
        $prams = $this->sync_post_parms( $prams );
        $ops = $this->sync_post_ops( $ops );

        // fix cứng tham số
        $prams[ 'post_type' ] = PostType::BLOG;
        $prams[ 'taxonomy' ] = TaxonomyType::BLOGS;

        //
        return $this->get_posts( $prams, $ops );
    }
    public function count_blogs_by( $prams, $ops = [] ) {
        $prams = $this->sync_post_parms( $prams );

        // fix cứng tham số
        $prams[ 'post_type' ] = PostType::BLOG;
        $prams[ 'taxonomy' ] = TaxonomyType::BLOGS;

        //
        $ops[ 'count_record' ] = 1;

        //
        return $this->get_posts( $prams, $ops );
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