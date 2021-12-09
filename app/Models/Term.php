<?php

namespace App\ Models;

// Libraries
use App\ Libraries\ LanguageCost;
use App\ Libraries\ DeletedStatus;
use App\ Libraries\ TaxonomyType;

//
class Term extends EB_Model {
    protected $table = 'wp_terms';
    protected $primaryKey = 'term_id';

    protected $metaTable = 'wp_termmeta';
    protected $metaKey = 'meta_id';

    protected $taxTable = 'wp_term_taxonomy';
    protected $taxKey = 'term_taxonomy_id';

    protected $relaTable = 'wp_term_relationships';
    protected $relaKey = 'object_id';

    //protected $primaryTaxonomy = 'category';

    public function __construct() {
        parent::__construct();
    }

    // lấy post theo dạng tương tự wordpress -> nếu không có -> tự động tạo mới
    function get_cat_post( $slug, $post_type = 'post', $taxonomy = 'category', $auto_insert = true, $ops = [] ) {
        if ( !isset( $ops[ 'lang_key' ] ) || $ops[ 'lang_key' ] == '' ) {
            $ops[ 'lang_key' ] = LanguageCost::lang_key();
        }

        //
        $where = [
            // các kiểu điều kiện where
            'lang_key' => $ops[ 'lang_key' ],
            'is_deleted' => DeletedStatus::DEFAULT,
            'taxonomy' => $taxonomy
        ];
        if ( $slug != '' ) {
            $where[ 'slug' ] = $slug;
        }

        //
        $post_cat = $this->get_taxonomy( $where );
        //print_r( $post_cat );

        // nếu không có -> insert luôn 1 nhóm mới
        if ( empty( $post_cat ) ) {
            if ( $auto_insert === true ) {
                // lấy thêm các tham số của nhóm ngôn ngữ chính nếu có
                if ( $ops[ 'lang_key' ] != LanguageCost::default_lang() ) {
                    $cat_primary_data = $this->get_cat_post( $slug, $post_type, $taxonomy, false, [
                        'lang_key' => LanguageCost::default_lang(),
                    ] );
                    //print_r( $cat_primary_data );
                    if ( !empty( $cat_primary_data ) ) {
                        $cat_primary_data = $this->terms_meta_post( [ $cat_primary_data ] );
                        $cat_primary_data = $cat_primary_data[ 0 ];
                        //print_r( $cat_primary_data );

                        // nếu có -> lấy meta của nó để nhân bản
                        if ( isset( $cat_primary_data[ 'term_meta' ] ) ) {
                            $_POST[ 'term_meta' ] = $cat_primary_data[ 'term_meta' ];
                        }
                    }
                    //die( 'hjdgdgd gd' );
                }

                //
                echo 'Auto create taxonomy: ' . $slug . ' (' . $taxonomy . ') <br>' . "\n";
                $result_id = $this->insert_terms( [
                    'name' => str_replace( '-', ' ', $slug ),
                    'slug' => $slug,
                    'lang_key' => $ops[ 'lang_key' ],
                ], $taxonomy );

                //
                if ( $result_id > 0 ) {
                    return $this->get_cat_post( $slug, $post_type, $taxonomy, false );
                }
                // nếu tồn tại rồi thì báo đã tồn tại
                else if ( $result_id < 0 ) {
                    die( 'EXIST auto create new terms #' . $taxonomy . ':' . basename( __FILE__ ) . ':' . __LINE__ );
                }
                die( 'ERROR auto create new terms #' . $taxonomy . ':' . basename( __FILE__ ) . ':' . __LINE__ );
            } else {
                die( 'AUTO INSERT new terms has DISABLE #' . $taxonomy . ':' . basename( __FILE__ ) . ':' . __LINE__ );
            }
        }
        //print_r( $post_cat );

        //
        return $post_cat;
    }

    function insert_terms( $data, $taxonomy ) {
        // các dữ liệu mặc định
        $default_data = [
            'last_updated' => date( 'Y-m-d H:i:s' ),
            'lang_key' => LanguageCost::lang_key(),
        ];
        //print_r( $default_data );

        //
        if ( $data[ 'slug' ] == '' ) {
            $data[ 'slug' ] = $data[ 'name' ];
        }
        if ( $data[ 'slug' ] != '' ) {
            $data[ 'slug' ] = $this->base_model->_eb_non_mark_seo( $data[ 'slug' ] );
            //print_r( $data );
            //die( __FILE__ . ':' . __LINE__ );

            //
            //$check_term_exist = $this->get_term_by_id( 1, $taxonomy, false );
            //print_r( $check_term_exist );
            /*
             * xem term này đã có chưa
             */
            // mặc định là có rồi
            $has_slug = true;
            // chạy vòng lặp để kiểm tra, nếu có rồi thì thêm số vào sau để tránh trùng lặp
            for ( $i = 0; $i < 10; $i++ ) {
                $by_slug = $data[ 'slug' ];
                if ( $i > 0 ) {
                    $by_slug .= $i;
                }
                //echo 'by_slug: ' . $by_slug . '<br>' . "\n";
                $check_term_exist = $this->get_term_by_slug( $by_slug, $taxonomy, false );
                //print_r( $check_term_exist );
                //die( __FILE__ . ':' . __LINE__ );

                // nếu có rồi thì trả về false lỗi
                if ( empty( $check_term_exist ) ) {
                    $data[ 'slug' ] = $by_slug;

                    // xác nhận slug này chưa được sử dụng
                    $has_slug = false;

                    break;
                }
            }
            //var_dump( $has_slug );
            //print_r( $data );
            if ( $has_slug === true ) {
                return -1;
            }
            //return false;
            //die( __FILE__ . ':' . __LINE__ );
        }
        foreach ( $default_data as $k => $v ) {
            if ( !isset( $data[ $k ] ) ) {
                $data[ $k ] = $v;
            }
        }
        //print_r( $data );
        //die( __FILE__ . ':' . __LINE__ );

        //
        $result_id = $this->base_model->insert( $this->table, $data, true );
        //echo $result_id . '<br>' . "\n";

        if ( $result_id > 0 ) {
            $this->base_model->insert( $this->taxTable, [
                $this->taxKey => $result_id,
                $this->primaryKey => $result_id,
                'taxonomy' => $taxonomy,
                'description' => 'Auto create nav menu taxonomy',
            ] );

            // insert/ update meta post
            if ( isset( $_POST[ 'term_meta' ] ) ) {
                $this->insert_meta_term( $_POST[ 'term_meta' ], $result_id );
            }

            //
            return $result_id;
        }
        return false;
    }

    function update_terms( $term_id, $data, $taxonomy = '' ) {
        if ( isset( $data[ 'slug' ] ) ) {
            if ( $data[ 'slug' ] == '' ) {
                $data[ 'slug' ] = $data[ 'name' ];
            }
            if ( $data[ 'slug' ] != '' ) {
                $data[ 'slug' ] = $this->base_model->_eb_non_mark_seo( $data[ 'slug' ] );
                //print_r( $data );

                // kiểm tra lại slug trước khi update
                $check_term_exist = $this->get_taxonomy( [
                    $this->primaryKey . ' !=' => $term_id,
                    'slug' => $data[ 'slug' ],
                    'taxonomy' => $taxonomy,
                ] );
                //print_r( $check_term_exist );
                //die( __FILE__ . ':' . __LINE__ );

                //
                if ( !empty( $check_term_exist ) ) {
                    return -1;
                }
            }
        }
        if ( !isset( $data[ 'last_updated' ] ) || $data[ 'last_updated' ] == '' ) {
            $data[ 'last_updated' ] = date( 'Y-m-d H:i:s' );
        }

        //
        //print_r( $data );

        //
        $where = [
            $this->primaryKey => $term_id,
        ];
        //print_r( $where );

        //
        $this->base_model->update_multiple( $this->table, $data, $where, [
            'debug_backtrace' => debug_backtrace()[ 1 ][ 'function' ]
        ] );

        // nếu có taxonomy -> update luôn cho bảng term_taxonomy
        if ( $taxonomy != '' ) {
            $where = [
                $this->primaryKey => $term_id,
                'taxonomy' => $taxonomy,
            ];
            //print_r( $where );
            $this->base_model->update_multiple( $this->taxTable, $data, $where, [
                'debug_backtrace' => debug_backtrace()[ 1 ][ 'function' ]
            ] );
        }
        //die( __FILE__ . ':' . __LINE__ );

        //
        if ( isset( $_POST[ 'term_meta' ] ) ) {
            $this->insert_meta_term( $_POST[ 'term_meta' ], $term_id );
        }
    }

    // thêm post meta
    function insert_meta_term( $meta_data, $term_id ) {
        //print_r( $meta_data );
        if ( !is_array( $meta_data ) || empty( $meta_data ) ) {
            return false;
        }

        /*
         * v2 -> Xóa hết đi add lại
         */
        $this->base_model->delete( $this->metaTable, $this->primaryKey, $term_id );

        // add lại
        foreach ( $meta_data as $k => $v ) {
            if ( $v != '' ) {
                $this->base_model->insert( $this->metaTable, [
                    $this->primaryKey => $term_id,
                    'meta_key' => $k,
                    'meta_value' => $v,
                ] );
            }
        }

        // done
        return true;

        /*
         * v1 -> chưa xử lý được các checkbox sau khi bị hủy
         */
        // lấy toàn bộ meta của post này
        $meta_exist = $this->arr_meta_terms( $term_id );
        //print_r( $meta_exist );
        //die( __FILE__ . ':' . __LINE__ );

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
                $this->primaryKey => $term_id,
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
                $this->primaryKey => $term_id,
                'meta_key' => $k,
            ] );
        }

        //
        //die( __FILE__ . ':' . __LINE__ );
        return true;
    }

    // trả về mảng dữ liệu để json data -> auto select category bằng js cho nhẹ -> lấy quá nhiều dữ liệu dễ bị json lỗi
    function get_json_taxonomy( $taxonomy = 'category', $term_id = 0, $ops = [] ) {
        // cố định loại cột cần lấy
        $ops[ 'select_col' ] = $this->primaryKey . ', name';

        //
        return json_encode( $this->get_all_taxonomy( $taxonomy, $term_id, $ops ) );
    }

    function json_taxonomy( $taxonomy = 'category', $term_id = 0, $ops = [] ) {
        echo $this->get_json_taxonomy( $taxonomy, $term_id, $ops );
    }

    function get_all_taxonomy( $taxonomy = 'category', $term_id = 0, $ops = [] ) {
        //print_r( $ops );

        // các kiểu điều kiện where
        $where = [
            'taxonomy' => $taxonomy,
            'lang_key' => LanguageCost::lang_key()
        ];
        $where_or_like = [];
        if ( $term_id > 0 ) {
            $where[ $this->primaryKey ] = $term_id;
            $ops[ 'limit' ] = 1;
        } else if ( isset( $ops[ 'slug' ] ) && !empty( $ops[ 'slug' ] ) ) {
            $where[ 'slug' ] = $ops[ 'slug' ];
            $ops[ 'limit' ] = 1;
            $ops[ 'slug_get_child' ] = 1;
        } else {
            if ( isset( $ops[ 'by_is_deleted' ] ) ) {
                $where[ 'is_deleted' ] = $ops[ 'by_is_deleted' ];
            } else {
                $where[ 'is_deleted' ] = DeletedStatus::DEFAULT;
            }

            // tìm kiếm
            if ( isset( $ops[ 'or_like' ] ) && !empty( $ops[ 'or_like' ] ) ) {
                $where_or_like = $ops[ 'or_like' ];
                $ops[ 'get_child' ] = 0;
                unset( $ops[ 'get_child' ] );
            }
            //
            else {
                if ( $where[ 'is_deleted' ] != DeletedStatus::DELETED ) {
                    if ( isset( $ops[ 'get_child' ] ) ) {
                        if ( !isset( $ops[ 'parent' ] ) ) {
                            $ops[ 'parent' ] = 0;
                        }
                    }
                    if ( isset( $ops[ 'parent' ] ) ) {
                        $where[ 'parent' ] = $ops[ 'parent' ];
                    }
                }
            }
            if ( isset( $ops[ 'lang_key' ] ) ) {
                $where[ 'lang_key' ] = $ops[ 'lang_key' ];
            }
        }

        //
        if ( !isset( $ops[ 'limit' ] ) ) {
            $ops[ 'limit' ] = 500;
        } else if ( $ops[ 'limit' ] < 0 ) {
            $ops[ 'limit' ] = 0;
        }

        //
        if ( !isset( $ops[ 'select_col' ] ) ) {
            $ops[ 'select_col' ] = '*';
        }
        //print_r( $where );
        //print_r( $ops );
        //print_r( $where_or_like );
        //die( __FILE__ . ':' . __LINE__ );

        //
        $post_cat = $this->base_model->select( $ops[ 'select_col' ], 'v_terms', $where, array(
            'or_like' => $where_or_like,
            'order_by' => array(
                'term_order' => 'DESC',
                $this->primaryKey => 'DESC',
            ),
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 2,
            'limit' => $ops[ 'limit' ]
        ) );
        //print_r( $post_cat );
        //die( __FILE__ . ':' . __LINE__ );
        //return $post_cat;

        // daidq (2021-12-01): khi có thêm tham số by_is_deleted mà vẫn lấy term meta thì bị lỗi query -> tạm bỏ
        if ( !empty( $post_cat ) && !isset( $ops[ 'by_is_deleted' ] ) ) {
            //print_r( $ops );

            // lấy meta
            if ( $term_id > 0 || isset( $ops[ 'slug_get_child' ] ) ) {
                $post_cat = $this->terms_meta_post( [ $post_cat ] );

                // lấy các nhóm con
                if ( isset( $ops[ 'get_child' ] ) && $ops[ 'get_child' ] > 0 ) {
                    $post_cat = $this->get_child_terms( $post_cat, $ops );
                }
                $post_cat = $post_cat[ 0 ];
            } else if ( isset( $ops[ 'get_meta' ] ) ) {
                $post_cat = $this->terms_meta_post( $post_cat );

                // lấy các nhóm con
                if ( isset( $ops[ 'get_child' ] ) && $ops[ 'get_child' ] > 0 ) {
                    $post_cat = $this->get_child_terms( $post_cat, $ops );
                }
            }
        }

        //
        return $post_cat;
    }

    public function get_taxonomy( $where, $limit = 1, $select_col = '*' ) {
        return $this->base_model->select( $select_col, 'v_terms', $where, array(
            'order_by' => array(
                $this->primaryKey => 'DESC'
            ),
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 2,
            'limit' => $limit
        ) );
    }

    function get_child_terms( $data, $ops = [] ) {
        //print_r( $data );
        foreach ( $data as $k => $v ) {
            $ops[ 'parent' ] = $v[ $this->primaryKey ];
            //print_r( $ops );

            //
            $data[ $k ][ 'child_term' ] = [];
            $data[ $k ][ 'child_term' ] = $this->get_all_taxonomy( $v[ 'taxonomy' ], 0, $ops );
        }
        return $data;
    }

    // hàm này để giả lập dữ liệu theo kiểu wordpress
    function insert_term_relationships( $post_id, $list, $term_order = 0 ) {
        $list = explode( ',', $list );

        // xóa các term_relationships cũ
        $this->base_model->delete( $this->relaTable, $this->relaKey, $post_id );

        // insert cái mới
        foreach ( $list as $term_id ) {
            $term_id = trim( $term_id );

            if ( $term_id != '' && $term_id > 0 ) {
                $this->base_model->insert( $this->relaTable, [
                    $this->relaKey => $post_id,
                    $this->taxKey => $term_id,
                    'term_order' => $term_order,
                ] );
            }
        }
    }

    function get_meta_terms( $term_id, $key = '' ) {
        // lấy theo key cụ thể
        if ( $key != '' ) {
            $data = $this->base_model - select( '*', $this->metaTable, array(
                // các kiểu điều kiện where
                $this->primaryKey => $term_id,
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
            $this->primaryKey => $term_id
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

    // trả về danh sách meta terms dưới dạng key => value
    function arr_meta_terms( $term_id ) {
        $data = $this->get_meta_terms( $term_id, '', $this->metaTable );

        //
        $meta_data = [];
        foreach ( $data as $k => $v ) {
            $meta_data[ $v[ 'meta_key' ] ] = $v[ 'meta_value' ];
        }
        return $meta_data;
    }

    function terms_meta_post( $data ) {
        //print_r( $data );
        foreach ( $data as $k => $v ) {
            //print_r( $v );
            $data[ $k ][ 'term_meta' ] = $this->arr_meta_terms( $v[ $this->primaryKey ] );
        }
        //print_r( $data );

        //
        return $data;
    }

    // chỉ trả về link admin của 1 term
    function get_admin_permalink( $taxonomy = '', $id = 0, $controller_slug = 'terms' ) {
        $url = base_url( 'admin/' . $controller_slug . '/add' ) . '?taxonomy=' . $taxonomy;
        if ( $id > 0 ) {
            $url .= '&id=' . $id;
        }
        return $url;
    }

    // thường dùng trong view -> in ra link admin của 1 term
    function admin_permalink( $taxonomy = '', $id = 0, $controller_slug = 'terms' ) {
        echo $this->get_admin_permalink( $taxonomy, $id, $controller_slug );
    }

    // trả về url của 1 term
    function get_the_permalink( $data ) {
        //print_r( $data );

        $allow_taxonomy = [
            TaxonomyType::TAGS,
            TaxonomyType::BLOGS,
            TaxonomyType::BLOG_TAGS,
        ];
        if ( $data[ 'taxonomy' ] == TaxonomyType::POSTS ) {
            return DYNAMIC_BASE_URL . $data[ 'slug' ];
        } else if ( in_array( $data[ 'taxonomy' ], $allow_taxonomy ) ) {
            return DYNAMIC_BASE_URL . $data[ 'taxonomy' ] . '/' . $data[ 'slug' ];
        }
        return '#';
    }
    // thường dùng trong view -> in ra link admin của 1 term
    function the_permalink( $data ) {
        echo $this->get_the_permalink( $data );
    }

    // hàm này sẽ kiểm tra xem có meta tương ứng của post không, có thì in ra luôn
    function return_meta_term( $data, $key, $default_value = '' ) {
        if ( isset( $data[ $key ] ) ) {
            return $data[ $key ];
        } else if ( isset( $data[ 'term_meta' ] ) ) {
            return $this->return_meta_term( $data[ 'term_meta' ], $key );
        }

        //
        return $default_value;
    }

    //
    function echo_meta_term( $data, $key, $default_value = '' ) {
        echo $this->return_meta_term( $data[ 'term_meta' ], $key, $default_value );
    }

    // tạo html trong này -> do trong view không viết được tham số $this để tạo vòng lặp đệ quy
    function list_html_view( $data, $gach_ngang = '', $is_deleted = '', $controller_slug = 'terms' ) {
        $tmp = '<tr>
            <td>&nbsp;</td>
            <td><a href="%get_admin_permalink%">' . $gach_ngang . ' %name% <i class="fa fa-edit"></i></a></td>
            <td>%slug%</td>
            <td class="d-none show-if-ads-type">%custom_size%</td>
            <td>&nbsp;</td>
            <td>%lang_key%</td>
            <td>%count%</td>
            <td>%action_link%</td>
        </tr>';

        //
        $for_redirect = '';
        if ( $is_deleted != '' ) {
            $for_redirect .= '&is_deleted=' . $is_deleted;
        }

        //
        $str = '';
        foreach ( $data as $k => $v ) {
            //print_r( $v );

            //
            $node = $tmp;

            //
            $action_link = '<a href="admin/' . $controller_slug . '/delete?taxonomy=%taxonomy%&id=%term_id%' . $for_redirect . '" onClick="return click_a_delete_record();" target="target_eb_iframe" class="redcolor"><i class="fa fa-trash"></i></a>';
            if ( $v[ 'is_deleted' ] == DeletedStatus::DELETED ) {
                $action_link = '<a href="admin/' . $controller_slug . '/restore?taxonomy=%taxonomy%&id=%term_id%' . $for_redirect . '" onClick="return click_a_restore_record();" target="target_eb_iframe" class="bluecolor"><i class="fa fa-undo"></i></a>';
            }
            $node = str_replace( '%action_link%', $action_link, $node );

            //
            foreach ( $v as $key => $val ) {
                if ( $key == 'term_meta' ) {
                    //print_r( $val );
                    foreach ( $val as $key_val => $val_val ) {
                        $node = str_replace( '%' . $key_val . '%', $val_val, $node );
                    }
                } else if ( !is_array( $val ) ) {
                    $node = str_replace( '%' . $key . '%', $val, $node );
                }
            }
            $node = str_replace( '%get_admin_permalink%', $this->get_admin_permalink( $v[ 'taxonomy' ], $v[ $this->primaryKey ], $controller_slug ), $node );

            //
            $str .= $node;

            //
            if ( isset( $v[ 'child_term' ] ) ) {
                $str .= $this->list_html_view( $v[ 'child_term' ], $gach_ngang . ' &#8212;', $is_deleted );
            }
        }

        //
        return $str;
    }

    // tự động tạo slider nếu có
    public function get_the_slider( $taxonomy_slider, $second_slider = '' ) {
        //print_r( $taxonomy_slider );
        if ( empty( $taxonomy_slider ) ) {
            return '';
        }

        // -> chạy vòng lặp để tìm slider theo danh mục gần nhất -> con không có thì tìm cha
        foreach ( $taxonomy_slider as $slider ) {
            if ( isset( $slider[ 'term_meta' ][ 'taxonomy_auto_slider' ] ) && $slider[ 'term_meta' ][ 'taxonomy_auto_slider' ] == 'on' ) {
                //echo 'taxonomy_auto_slider';

                $slug = $slider[ 'slug' ] . '-' . $slider[ 'taxonomy' ] . '-' . $slider[ $this->primaryKey ];
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
    public function the_slider( $data, $second_slider = '' ) {
        echo $this->get_the_slider( $data, $second_slider );
    }


    /*
     *
     */
    // đồng bộ tham số đầu vào
    public function sync_term_parms( $prams, $ops ) {
        // nếu đầu vào không phải array
        if ( !is_array( $prams ) ) {
            if ( empty( $prams ) ) {
                $prams = [];
                //return debug_backtrace()[ 1 ][ 'function' ] . ' $prams is NULL!';
            }
            // tự tạo theo term id
            else if ( is_numeric( $prams ) ) {
                $prams = [
                    $this->primaryKey => $prams
                ];
            }
            // hoặc slug
            else if ( is_string( $prams ) ) {
                $prams = [
                    'slug' => $prams
                ];
            }
        }

        if ( !isset( $prams[ $this->primaryKey ] ) ) {
            $prams[ $this->primaryKey ] = isset( $ops[ $this->primaryKey ] ) ? $ops[ $this->primaryKey ] : 0;
        }
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
        //die( 'hkj dfsdfgsdgsdgs' );

        //
        return $prams;
    }

    // category -> categories -> categorys
    public function get_categorys_by( $prams = [], $ops = [] ) {
        $prams = $this->sync_term_parms( $prams, $ops );
        //print_r( $prams );

        //
        return $this->get_all_taxonomy( TaxonomyType::POSTS, $prams[ $this->primaryKey ], $prams );
    }

    // lấy chi tiết 1 term theo ID
    public function get_term_by_id( $id, $taxonomy = 'category', $get_meta = true ) {
        $data = $this->get_taxonomy( [
            $this->primaryKey => $id,
            'taxonomy' => $taxonomy,
        ] );

        //
        if ( $get_meta === true && !empty( $data ) ) {
            $data = $this->terms_meta_post( [ $data ] );
            return $data[ 0 ];
        }

        //
        return $data;
    }

    // lấy chi tiết 1 term theo slug
    public function get_term_by_slug( $slug, $taxonomy = 'category', $get_meta = true ) {
        $data = $this->get_taxonomy( [
            'slug' => $slug,
            'taxonomy' => $taxonomy,
        ] );

        //
        if ( $get_meta === true && !empty( $data ) ) {
            $data = $this->terms_meta_post( [ $data ] );
            return $data[ 0 ];
        }

        //
        return $data;
    }
}