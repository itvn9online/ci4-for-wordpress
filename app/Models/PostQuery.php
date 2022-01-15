<?php

namespace App\ Models;

// Libraries
use App\ Libraries\ LanguageCost;
use App\ Libraries\ PostType;
use App\ Libraries\ TaxonomyType;
use App\ Libraries\ DeletedStatus;

//
class PostQuery extends PostMeta {
    public function __construct() {
        parent::__construct();
    }

    function insert_post( $data, $data_meta = [] ) {
        //$session_data = $this->session->get( 'admin' );
        $session_data = $this->base_model->get_ses_login();
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
        $default_data[ 'time_order' ] = time();

        //
        if ( !isset( $data[ 'post_name' ] ) || $data[ 'post_name' ] == '' ) {
            $data[ 'post_name' ] = $data[ 'post_title' ];
        }
        if ( $data[ 'post_name' ] != '' ) {
            $data[ 'post_name' ] = $this->base_model->_eb_non_mark_seo( $data[ 'post_name' ] );

            //
            $check_slug = $this->base_model->select( 'ID', 'wp_posts', [
                'post_name' => $data[ 'post_name' ],
                'post_type' => $data[ 'post_type' ],
                'post_status !=' => PostType::DELETED,
            ], [
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 2,
                'limit' => 1
            ] );
            //print_r( $check_slug );
            if ( !empty( $check_slug ) ) {
                return [
                    'code' => __LINE__,
                    'error' => 'Slug đã được sử dụng ở post #' . $check_slug[ 'ID' ] . ' (' . $data[ 'post_name' ] . ')',
                ];
            }
            //die( __FILE__ . ':' . __LINE__ );
        }
        foreach ( $default_data as $k => $v ) {
            if ( !isset( $data[ $k ] ) ) {
                $data[ $k ] = $v;
            }
        }

        // insert post
        //print_r( $data );
        $result_id = $this->base_model->insert( $this->table, $data, true );
        //var_dump( $result_id );
        //print_r( $result_id );

        if ( $result_id > 0 ) {
            //print_r( $data_meta );
            //print_r( $_POST );
            //die( __FILE__ . ':' . __LINE__ );

            // insert/ update meta post
            if ( !empty( $data_meta ) ) {
                $this->insert_meta_post( $data_meta, $result_id );
            }
            //
            else if ( isset( $_POST[ 'post_meta' ] ) ) {
                $this->insert_meta_post( $_POST[ 'post_meta' ], $result_id );
            }

            //
            return $result_id;
        }
        return false;
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
        $where[ 'ID' ] = $post_id;
        //print_r( $data );
        //print_r( $where );

        // nếu đang là xóa bài viết thì bỏ qua việc kiểm tra slug
        if ( isset( $data[ 'post_status' ] ) && $data[ 'post_status' ] == PostType::DELETED ) {
            //
        }
        // kiểm tra xem có trùng slug không
        else if ( isset( $data[ 'post_name' ] ) && $data[ 'post_name' ] != '' ) {
            // post đang cần update
            $current_slug = $this->base_model->select( '*', 'wp_posts', $where, [
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 2,
                'limit' => 1
            ] );
            //print_r( $current_slug );

            //
            if ( !empty( $current_slug ) ) {
                $check_slug = $this->base_model->select( 'ID', 'wp_posts', [
                    'post_name' => $data[ 'post_name' ],
                    'ID !=' => $current_slug[ 'ID' ],
                    'post_type' => $current_slug[ 'post_type' ],
                    'post_status !=' => PostType::DELETED,
                    //'post_status' => $current_slug[ 'post_status' ],
                ], [
                    // hiển thị mã SQL để check
                    //'show_query' => 1,
                    // trả về câu query để sử dụng cho mục đích khác
                    //'get_query' => 1,
                    //'offset' => 2,
                    'limit' => 1
                ] );
                //print_r( $check_slug );
                if ( !empty( $check_slug ) ) {
                    return [
                        'code' => __LINE__,
                        'error' => 'Slug đã được sử dụng ở post #' . $check_slug[ 'ID' ] . ' (' . $data[ 'post_name' ] . ')',
                    ];
                }
                //die( __FILE__ . ':' . __LINE__ );
            }
        }

        //
        $this->base_model->update_multiple( $this->table, $data, $where, [
            'debug_backtrace' => debug_backtrace()[ 1 ][ 'function' ]
        ] );

        //
        //print_r( $_POST );
        if ( isset( $_POST[ 'post_meta' ] ) ) {
            $this->insert_meta_post( $_POST[ 'post_meta' ], $post_id );
        }

        //
        return true;
    }

    function select_post( $post_id, $where = [] ) {
        if ( $post_id > 0 ) {
            $where[ 'ID' ] = $post_id;
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
            $data[ 'post_meta' ] = $this->arr_meta_post( $data[ 'ID' ] );
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
                'ID' => 'DESC',
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
                'is_deleted' => DeletedStatus::FOR_DEFAULT,
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
                        echo 'Auto create post: ' . $data_insert[ 'post_title' ] . ' (' . $ops[ 'post_type' ] . ') <br>' . PHP_EOL;
                        $this->insert_post( $data_insert, $_POST[ 'post_meta' ] );
                    }
                }
                //die( 'fjg dghsd sgsd' );
            }
            //return '<a href="./admin/posts/add?post_type=' . $ops[ 'post_type' ] . '">Please add post to category slug #' . $slug . '</a>';
            return 'Please add post to category slug #' . $slug;
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
        echo '<!-- ' . $html_node . ' --> ' . PHP_EOL;
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
}