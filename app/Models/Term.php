<?php

namespace App\ Models;

// Libraries
use App\ Libraries\ LanguageCost;
use App\ Libraries\ DeletedStatus;
use App\ Libraries\ TaxonomyType;

//
class Term extends EbModel {
    public $table = 'terms';
    public $primaryKey = 'term_id';

    public $metaTable = 'termmeta';
    //public $metaKey = 'meta_id';

    public $taxTable = 'term_taxonomy';
    public $taxKey = 'term_taxonomy_id';

    public $relaTable = 'term_relationships';
    public $relaKey = 'object_id';

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
            'is_deleted' => DeletedStatus::FOR_DEFAULT,
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
                echo 'Auto create taxonomy: ' . $slug . ' (' . $taxonomy . ') <br>' . PHP_EOL;
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

    /*
     * return_exist -> trả về ID của term khi gặp trùng lặp slug
     */
    function insert_terms( $data, $taxonomy, $return_exist = false ) {
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
                $check_term_exist = $this->get_term_by_slug( $by_slug, $taxonomy, false, 1, 'term_id' );
                //print_r( $check_term_exist );
                //die( __FILE__ . ':' . __LINE__ );

                // chưa có thì bỏ qua việc kiểm tra
                if ( empty( $check_term_exist ) ) {
                    $data[ 'slug' ] = $by_slug;

                    // xác nhận slug này chưa được sử dụng
                    $has_slug = false;

                    break;
                }
                // nếu có rồi mà có kèm lệnh hủy thì trả về data luôn
                else if ( $return_exist === true ) {
                    return $check_term_exist[ 'term_id' ];
                }
                // không thì for tiếp để thêm số vào slug -> tránh trùng lặp
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

        if ( $result_id !== false ) {
            $this->base_model->insert( $this->taxTable, [
                'term_taxonomy_id' => $result_id,
                'term_id' => $result_id,
                'taxonomy' => $taxonomy,
                'description' => 'Auto create nav menu taxonomy',
            ] );

            // insert/ update meta post
            if ( isset( $_POST[ 'term_meta' ] ) ) {
                $this->insert_meta_term( $_POST[ 'term_meta' ], $result_id );
            }

            // xóa cache
            $this->delete_cache_taxonomy( $taxonomy );
        }
        return $result_id;
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
                    'term_id !=' => $term_id,
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
            'term_id' => $term_id,
        ];
        //print_r( $where );

        //
        $result_update = $this->base_model->update_multiple( $this->table, $data, $where, [
            'debug_backtrace' => debug_backtrace()[ 1 ][ 'function' ]
        ] );

        // nếu có taxonomy -> update luôn cho bảng term_taxonomy
        if ( $taxonomy != '' ) {
            $where = [
                'term_id' => $term_id,
                'taxonomy' => $taxonomy,
            ];
            //print_r( $where );
            $this->base_model->update_multiple( $this->taxTable, $data, $where, [
                'debug_backtrace' => debug_backtrace()[ 1 ][ 'function' ]
            ] );

            // xóa cache
            $this->delete_cache_taxonomy( $taxonomy );
        }
        //die( __FILE__ . ':' . __LINE__ );

        //
        if ( isset( $_POST[ 'term_meta' ] ) ) {
            $this->insert_meta_term( $_POST[ 'term_meta' ], $term_id );
        }

        //
        return $result_update;
    }

    // phiên bản xóa xong thêm -> không tối ứu
    function insert_v2_meta_term( $meta_data, $term_id ) {
        $this->base_model->delete( $this->metaTable, 'term_id', $term_id );

        // add lại
        foreach ( $meta_data as $k => $v ) {
            if ( $v != '' ) {
                $this->base_model->insert( $this->metaTable, [
                    'term_id' => $term_id,
                    'meta_key' => $k,
                    'meta_value' => $v,
                ] );
            }
        }

        // done
        return true;
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
        //return $this->insert_v2_meta_term( $meta_data, $term_id );

        /*
         * v1 -> chưa xử lý được các checkbox sau khi bị hủy
         * daidq (2021-12-14): đã xử lý được phần checkbox
         */
        // lấy toàn bộ meta của post này
        $meta_exist = $this->arr_meta_terms( $term_id );
        //print_r( $meta_exist );
        //die( __FILE__ . ':' . __LINE__ );

        // xem các meta nào không có trong lần update này -> XÓA
        foreach ( $meta_exist as $k => $v ) {
            if ( !isset( $meta_data[ $k ] ) ) {
                //echo 'DELETE ' . $k . ' ' . $v . '<br>' . "\n";

                //
                $this->base_model->delete_multiple( $this->metaTable, [
                    'term_id' => $term_id,
                    'meta_key' => $k,
                ] );
            }
        }

        //
        $insert_meta = [];
        $update_meta = [];
        foreach ( $meta_data as $k => $v ) {
            // thêm vào mảng update nếu có rồi
            if ( isset( $meta_exist[ $k ] ) ) {
                $update_meta[ $k ] = $v;
            }
            // thêm vào mảng insert nếu chưa có
            else if ( $v != '' ) {
                $insert_meta[ $k ] = $v;
            }
        }

        // các meta chưa có thì insert
        //print_r( $insert_meta );
        foreach ( $insert_meta as $k => $v ) {
            $this->base_model->insert( $this->metaTable, [
                'term_id' => $term_id,
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
                'term_id' => $term_id,
                'meta_key' => $k,
            ] );
        }

        //
        //die( __FILE__ . ':' . __LINE__ );
        return true;
    }

    // trả về mảng dữ liệu để json data -> auto select category bằng js cho nhẹ -> lấy quá nhiều dữ liệu dễ bị json lỗi
    public function get_json_taxonomy( $taxonomy = 'category', $term_id = 0, $ops = [], $in_cache = '' ) {
        // nếu không có cache key -> kiểm tra điều kiện tạo key
        if ( $in_cache == '' ) {
            if ( $term_id === 0 && empty( $ops ) ) {
                $in_cache = $taxonomy;
            }
        }

        // cố định loại cột cần lấy
        $ops[ 'select_col' ] = 'term_id, name, slug, count, parent, taxonomy';
        //$ops[ 'select_col' ] = '*';

        //
        return str_replace( '\'', '\\\'', json_encode( $this->get_all_taxonomy( $taxonomy, $term_id, $ops, $in_cache ) ) );
    }

    function json_taxonomy( $taxonomy = 'category', $term_id = 0, $ops = [], $in_cache = '' ) {
        echo $this->get_json_taxonomy( $taxonomy, $term_id, $ops, $in_cache );
    }

    function delete_cache_taxonomy( $taxonomy, $in_cache = '' ) {
        if ( $in_cache == '' ) {
            $in_cache = $taxonomy;
        }
        return $this->get_all_taxonomy( $taxonomy, 0, NULL, $in_cache, true );
    }

    function get_all_taxonomy( $taxonomy = 'category', $term_id = 0, $ops = [], $in_cache = '', $clear_cache = false, $time = 3600 ) {
        //print_r( $ops );

        // nếu không có cache key -> kiểm tra điều kiện tạo key
        if ( $in_cache == '' ) {
            if ( $term_id === 0 && empty( $ops ) ) {
                $in_cache = $taxonomy;
            }
        }

        //
        $lang_key = LanguageCost::lang_key();
        if ( $in_cache != '' ) {
            $in_cache = __FUNCTION__ . '-' . $in_cache . '-' . $lang_key;
            //echo $in_cache . '<br>' . "\n";

            //
            $cache = \Config\ Services::cache();

            // xóa cache nếu có yêu cầu
            if ( $clear_cache === true ) {
                return $cache->delete( $in_cache );
            }

            // TEST
            /*
            print_r( $cache->get( 'dfhfhdsfasffssf' ) );
            var_dump( $cache->get( 'dfhfhdsfasffssf' ) );
            $cache->save( 'dfhfhdsfasffssf', time(), 5 );
            echo __FILE__ . ':' . __LINE__ . '<br>' . "\n";
            */

            //
            $cache_value = $cache->get( $in_cache );
            //print_r( $cache_value );
            //var_dump( $cache_value );

            // có cache thì trả về
            if ( $cache_value !== NULL ) {
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
            'lang_key' => $lang_key
        ];
        $where_or_like = [];
        if ( $term_id > 0 ) {
            $where[ 'term_id' ] = $term_id;
            $ops[ 'limit' ] = 1;
        } else if ( isset( $ops[ 'slug' ] ) && !empty( $ops[ 'slug' ] ) ) {
            $where[ 'slug' ] = $ops[ 'slug' ];
            $ops[ 'limit' ] = 1;
            $ops[ 'slug_get_child' ] = 1;
        } else {
            if ( isset( $ops[ 'by_is_deleted' ] ) ) {
                //echo $ops[ 'by_is_deleted' ] . '<br>' . "\n";
                $where[ 'is_deleted' ] = $ops[ 'by_is_deleted' ];
            } else {
                $where[ 'is_deleted' ] = DeletedStatus::FOR_DEFAULT;
            }

            // tìm kiếm
            if ( isset( $ops[ 'or_like' ] ) && !empty( $ops[ 'or_like' ] ) ) {
                $where_or_like = $ops[ 'or_like' ];
                $ops[ 'get_child' ] = 0;
                unset( $ops[ 'get_child' ] );
            }
            //
            else {
                //if ( $where[ 'is_deleted' ] != DeletedStatus::DELETED ) {
                if ( isset( $ops[ 'get_child' ] ) ) {
                    if ( !isset( $ops[ 'parent' ] ) ) {
                        $ops[ 'parent' ] = 0;
                    }
                }
                if ( isset( $ops[ 'parent' ] ) ) {
                    $where[ 'parent' ] = $ops[ 'parent' ];
                }
                //}
            }
            if ( isset( $ops[ 'lang_key' ] ) ) {
                $where[ 'lang_key' ] = $ops[ 'lang_key' ];
            }
        }

        //
        if ( !isset( $ops[ 'limit' ] ) ) {
            $ops[ 'limit' ] = 500;
            /*
        } else if ( $ops[ 'limit' ] < 0 ) {
            $ops[ 'limit' ] = 0;
            */
        }
        if ( !isset( $ops[ 'offset' ] ) ) {
            $ops[ 'offset' ] = 0;
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
        $post_cat = $this->base_model->select( $ops[ 'select_col' ], WGR_TERM_VIEW, $where, array(
            'or_like' => $where_or_like,
            'order_by' => array(
                'term_order' => 'DESC',
                'term_id' => 'DESC',
            ),
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 2,
            'offset' => $ops[ 'offset' ],
            'limit' => $ops[ 'limit' ]
        ) );
        //print_r( $post_cat );
        //die( __FILE__ . ':' . __LINE__ );
        //return $post_cat;

        // daidq (2021-12-01): khi có thêm tham số by_is_deleted mà vẫn lấy term meta thì bị lỗi query -> tạm bỏ
        if ( !empty( $post_cat ) ) {
            //if ( !isset( $ops[ 'by_is_deleted' ] ) ) {
            //print_r( $ops );

            // lấy meta
            if ( $term_id > 0 || isset( $ops[ 'slug_get_child' ] ) ) {
                //die( ':' . __LINE__ );
                $post_cat = $this->terms_meta_post( [ $post_cat ] );

                // lấy các nhóm con
                if ( isset( $ops[ 'get_child' ] ) && $ops[ 'get_child' ] > 0 ) {
                    //die( ':' . __LINE__ );
                    $post_cat = $this->get_child_terms( $post_cat, $ops );
                }
                $post_cat = $post_cat[ 0 ];
            } else if ( isset( $ops[ 'get_meta' ] ) ) {
                //die( ':' . __LINE__ );
                $post_cat = $this->terms_meta_post( $post_cat );

                // lấy các nhóm con
                if ( isset( $ops[ 'get_child' ] ) && $ops[ 'get_child' ] > 0 ) {
                    //die( ':' . __LINE__ );
                    $post_cat = $this->get_child_terms( $post_cat, $ops );
                }
            } else if ( isset( $ops[ 'get_child' ] ) && $ops[ 'get_child' ] > 0 ) {
                $post_cat = $this->get_child_terms( $post_cat, $ops );
            }
            //}
        }

        //
        if ( $in_cache != '' ) {
            $cache->save( $in_cache, $post_cat, $time );
        }

        //
        return $post_cat;
    }

    public function get_taxonomy( $where, $limit = 1, $select_col = '*' ) {
        return $this->base_model->select( $select_col, WGR_TERM_VIEW, $where, array(
            'order_by' => array(
                'term_id' => 'DESC'
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
            $ops[ 'parent' ] = $v[ 'term_id' ];
            //print_r( $ops );
            //continue;

            //
            $child_term = [];
            // nếu tham số child count chưa được cập nhật -> lấy từ database và cập nhật lại
            if ( $v[ 'child_count' ] === NULL ) {
                $child_term = $this->get_all_taxonomy( $v[ 'taxonomy' ], 0, $ops );

                //
                $this->base_model->update_multiple( $this->table, [
                    'child_count' => count( $child_term ),
                ], [
                    'term_id' => $v[ 'term_id' ],
                ] );

                // thông báo kiểu dữ liệu trả về
                $data[ $k ][ 'child_count' ] = 'query';
            } else {
                // nếu có nhóm con -> mới gọi lệnh lấy nhóm con
                if ( $v[ 'child_count' ] > 0 ) {
                    $child_term = $this->get_all_taxonomy( $v[ 'taxonomy' ], 0, $ops );

                    // cập nhật lại tổng số nhóm nếu có sai số
                    if ( count( $child_term ) != $v[ 'child_count' ] ) {
                        //echo __FILE__ . ':' . __LINE__ . '<br>' . "\n";

                        //
                        $this->base_model->update_multiple( $this->table, [
                            'child_count' => count( $child_term ),
                        ], [
                            'term_id' => $v[ 'term_id' ],
                        ] );
                    }
                }

                // thông báo kiểu dữ liệu trả về
                $data[ $k ][ 'child_count' ] = 'cache';
            }
            $data[ $k ][ 'child_term' ] = $child_term;
        }
        //print_r( $data );
        return $data;
    }

    // hàm này để giả lập dữ liệu theo kiểu wordpress
    function insert_term_relationships( $post_id, $list, $term_order = 0 ) {
        $list = explode( ',', $list );

        // xóa các term_relationships cũ
        $this->base_model->delete( $this->relaTable, 'object_id', $post_id );

        // insert cái mới
        foreach ( $list as $term_id ) {
            $term_id = trim( $term_id );

            if ( $term_id != '' && $term_id > 0 ) {
                $this->base_model->insert( $this->relaTable, [
                    'object_id' => $post_id,
                    'term_taxonomy_id' => $term_id,
                    'term_order' => $term_order,
                ] );

                // tính tổng bài viết theo từng term
                $count_port_term = $this->base_model->select( 'COUNT(object_id) AS c', $this->relaTable, array(
                    // WHERE AND OR
                    'term_taxonomy_id' => $term_id,
                ), array(
                    // hiển thị mã SQL để check
                    //'show_query' => 1,
                    // trả về câu query để sử dụng cho mục đích khác
                    //'get_query' => 1,
                    //'offset' => 2,
                    //'limit' => 3
                ) );
                //print_r( $count_port_term );

                // cập nhật lại tổng số bài viết cho term
                $this->base_model->update_multiple( $this->taxTable, [
                    'count' => $count_port_term[ 0 ][ 'c' ]
                ], [
                    'term_taxonomy_id' => $term_id,
                    'term_id' => $term_id,
                ], [
                    'debug_backtrace' => debug_backtrace()[ 1 ][ 'function' ]
                ] );
            }
        }
    }

    function get_meta_terms( $term_id, $key = '' ) {
        // lấy theo key cụ thể
        if ( $key != '' ) {
            $data = $this->base_model - select( '*', $this->metaTable, array(
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

            // nếu không có dữ liệu của term meta
            if ( $v[ 'term_meta_data' ] === NULL ) {
                $term_meta_data = $this->arr_meta_terms( $v[ 'term_id' ] );
                //print_r( $term_meta_data );
                //echo __FILE__ . ':' . __LINE__ . '<br>' . "\n";

                //
                $this->base_model->update_multiple( $this->table, [
                    'term_meta_data' => json_encode( $term_meta_data ),
                ], [
                    'term_id' => $v[ 'term_id' ],
                ] );

                // thông báo kiểu dữ liệu trả về
                $data[ $k ][ 'term_meta_data' ] = 'query';
            } else {
                $term_meta_data = ( array )json_decode( $v[ 'term_meta_data' ] );
                //echo __FILE__ . ':' . __LINE__ . '<br>' . "\n";

                // thông báo kiểu dữ liệu trả về
                $data[ $k ][ 'term_meta_data' ] = 'cache';
            }
            $data[ $k ][ 'term_meta' ] = $term_meta_data;
        }
        //print_r( $data );

        //
        return $data;
    }

    // chỉ trả về link admin của 1 term
    function get_admin_permalink( $taxonomy = '', $id = 0, $controller_slug = 'terms' ) {
        //$url = base_url( 'admin/' . $controller_slug . '/add' ) . '?taxonomy=' . $taxonomy;
        $url = base_url( 'admin/' . $controller_slug . '/add' );
        if ( $id > 0 ) {
            //$url .= '&id=' . $id;
            $url .= '?id=' . $id;
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
            return DYNAMIC_BASE_URL . CATEGORY_BASE_URL . $data[ 'slug' ];
        } else if ( in_array( $data[ 'taxonomy' ], $allow_taxonomy ) ) {
            return DYNAMIC_BASE_URL . $data[ 'taxonomy' ] . '/' . $data[ 'slug' ];
        }
        return DYNAMIC_BASE_URL . '?cat=' . $data[ 'term_id' ] . '&taxonomy=' . $data[ 'taxonomy' ] . '&slug=' . $data[ 'slug' ];
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
            <td><a href="%view_url%" target="_blank">%slug% <i class="fa fa-external-link"></i></a></td>
            <td class="d-none show-if-ads-type">%custom_size%</td>
            <td>&nbsp;</td>
            <td>%lang_key%</td>
            <td>%count%</td>
            <td class="text-center">%action_link%</td>
        </tr>';

        //
        $for_redirect = '';
        if ( $is_deleted != '' ) {
            $for_redirect .= '&is_deleted=' . $is_deleted;
        }

        //
        $str = '';
        foreach ( $data as $k => $v ) {
            print_r( $v );

            //
            $node = $tmp;

            //
            if ( $v[ 'is_deleted' ] == DeletedStatus::DELETED ) {
                $action_link = '<a href="admin/' . $controller_slug . '/restore?id=%term_id%' . $for_redirect . '" onClick="return click_a_restore_record();" target="target_eb_iframe" class="bluecolor"><i class="fa fa-undo"></i></a>';
            } else {
                $action_link = '<a href="admin/' . $controller_slug . '/term_status?id=%term_id%&current_status=%term_status%' . $for_redirect . '" target="target_eb_iframe" data-status="%term_status%" class="record-status-color"><i class="fa fa-eye"></i></a> &nbsp; ';

                $action_link .= '<a href="admin/' . $controller_slug . '/delete?id=%term_id%' . $for_redirect . '" onClick="return click_a_delete_record();" target="target_eb_iframe" class="redcolor"><i class="fa fa-trash"></i></a>';
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
            $node = str_replace( '%get_admin_permalink%', $this->get_admin_permalink( $v[ 'taxonomy' ], $v[ 'term_id' ], $controller_slug ), $node );
            $node = str_replace( '%view_url%', $this->get_the_permalink( $v ), $node );

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

                $slug = $slider[ 'slug' ] . '-' . $slider[ 'taxonomy' ] . '-' . $slider[ 'term_id' ];
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

        if ( !isset( $prams[ 'term_id' ] ) ) {
            $prams[ 'term_id' ] = isset( $ops[ 'term_id' ] ) ? $ops[ 'term_id' ] : 0;
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
        return $this->get_all_taxonomy( TaxonomyType::POSTS, $prams[ 'term_id' ], $prams );
    }

    // lấy chi tiết 1 term theo ID
    public function get_term_by_id( $id, $taxonomy = 'category', $get_meta = true, $limit = 1, $select_col = '*' ) {
        $data = $this->get_taxonomy( [
            'term_id' => $id,
            'taxonomy' => $taxonomy,
        ], $limit, $select_col );

        //
        if ( $get_meta === true && !empty( $data ) ) {
            $data = $this->terms_meta_post( [ $data ] );
            return $data[ 0 ];
        }

        //
        return $data;
    }

    // lấy chi tiết 1 term theo slug
    public function get_term_by_slug( $slug, $taxonomy = 'category', $get_meta = true, $limit = 1, $select_col = '*' ) {
        $data = $this->get_taxonomy( [
            'slug' => $slug,
            'taxonomy' => $taxonomy,
        ], $limit, $select_col );

        //
        if ( $get_meta === true && !empty( $data ) ) {
            $data = $this->terms_meta_post( [ $data ] );
            return $data[ 0 ];
        }

        //
        return $data;
    }

    // vòng lặp đệ quy -> tạo option cho phần select của term
    public function term_add_child_option( $data, $term_id = 0, $gach_ngang = '' ) {
        if ( empty( $data ) ) {
            return false;
        }
        //print_r( $data );
        //return false;

        //
        foreach ( $data as $v ) {
            //print_r( $v );
            //continue;
            if ( $v[ 'term_id' ] == $term_id || $v[ 'parent' ] == $term_id ) {
                continue;
            }
            echo '<option value="' . $v[ 'term_id' ] . '">' . $gach_ngang . $v[ 'name' ] . '</option>';

            //
            $this->term_add_child_option( $v[ 'child_term' ], $term_id, $gach_ngang . '&#8212; ' );
        }
    }
}