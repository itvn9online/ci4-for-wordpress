<?php

namespace App\ Models;

// Libraries
use App\ Libraries\ LanguageCost;
use App\ Libraries\ PostType;
use App\ Libraries\ TaxonomyType;
use App\ Libraries\ DeletedStatus;

//
class PostAdmin extends Post {
    public function __construct() {
        parent::__construct();
    }

    /*
     * Tạo danh sách link để thêm menu trong admin cho tiện
     */
    function quick_add_menu() {
        global $arr_custom_taxonomy;
        global $arr_custom_post_type;

        //
        $allow_taxonomy = [
            TaxonomyType::POSTS,
            TaxonomyType::TAGS,
            TaxonomyType::BLOGS,
            TaxonomyType::BLOG_TAGS,
        ];
        // thêm custom taxonomy vào phần add menu
        $arr_custom_name = [];
        foreach ( $arr_custom_taxonomy as $k => $v ) {
            if ( !in_array( $k, $allow_taxonomy ) ) {
                $allow_taxonomy[] = $k;

                // tên của custom taxonomy
                if ( isset( $v[ 'name' ] ) ) {
                    $arr_custom_name[ $k ] = $v[ 'name' ];
                }
            }
        }
        //print_r( $allow_taxonomy );

        //
        $arr_result = [];
        foreach ( $allow_taxonomy as $allow ) {
            $category_list = $this->term_model->get_all_taxonomy( $allow, 0, [
                'by_is_deleted' => DeletedStatus::FOR_DEFAULT,
                'parent' => 0,
                //'get_meta' => true,
                //'get_child' => true
            ] );
            //print_r( $category_list );
            if ( empty( $category_list ) ) {
                continue;
            }

            //
            if ( isset( $arr_custom_name[ $allow ] ) ) {
                $arr_result[] = '<option class="bold" disabled>' . $arr_custom_name[ $allow ] . '</option>';
            } else {
                $arr_result[] = '<option class="bold" disabled>' . TaxonomyType::list( $allow ) . '</option>';
            }

            //
            foreach ( $category_list as $cat_key => $cat_val ) {
                $arr_result[] = '<option value="' . $this->term_model->get_the_permalink( $cat_val ) . '">' . $cat_val[ 'name' ] . '</option>';

                // lấy các nhóm con thuộc nhóm này
                $child_list = $this->term_model->get_all_taxonomy( $allow, 0, [
                    'by_is_deleted' => DeletedStatus::FOR_DEFAULT,
                    'parent' => $cat_val[ 'term_id' ],
                    //'get_meta' => true,
                    //'get_child' => true
                ] );
                //print_r( $child_list );

                //
                foreach ( $child_list as $child_key => $child_val ) {
                    $arr_result[] = '<option value="' . $this->term_model->get_the_permalink( $child_val ) . '">' . $child_val[ 'name' ] . '</option>';
                }
            }
        }


        //
        $allow_post_type = [
            PostType::PAGE,
            PostType::POST,
            PostType::BLOG,
        ];
        // thêm custom post type vào phần add menu
        $arr_custom_name = [];
        foreach ( $arr_custom_post_type as $k => $v ) {
            if ( !in_array( $k, $allow_post_type ) ) {
                $allow_post_type[] = $k;

                // tên của custom taxonomy
                if ( isset( $v[ 'name' ] ) ) {
                    $arr_custom_name[ $k ] = $v[ 'name' ];
                }
            }
        }
        //print_r( $allow_post_type );

        foreach ( $allow_post_type as $allow ) {
            // các kiểu điều kiện where
            $where = [
                //'post_status !=' => PostType::DELETED,
                'post_type' => $allow,
                'post_status' => PostType::PUBLIC,
                'lang_key' => LanguageCost::lang_key()
            ];

            $filter = [
                /*
                'where_in' => array(
                    'post_status' => array(
                        //PostType::DRAFT,
                        PostType::PUBLIC,
                        //PostType::PENDING,
                    )
                ),
                */
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

            //
            if ( isset( $arr_custom_name[ $allow ] ) ) {
                $arr_result[] = '<option class="bold" disabled>' . $arr_custom_name[ $allow ] . '</option>';
            } else {
                $arr_result[] = '<option class="bold" disabled>' . PostType::list( $allow ) . '</option>';
            }

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
}