<?php

namespace App\Models;

// Libraries
use App\Libraries\LanguageCost;
use App\Libraries\PostType;
use App\Libraries\TaxonomyType;
use App\Libraries\DeletedStatus;

//
class PostAdmin extends Post
{
    public function __construct()
    {
        parent::__construct();
    }

    /*
     * Tạo danh sách link để thêm menu trong admin cho tiện
     * Trả về danh sách các liên kết nội bộ trong website
     */
    public function get_site_inlink($lang_key = '', $limit = 500, $clear_cache = false, $time = MINI_CACHE_TIMEOUT)
    {
        // cache
        if ($lang_key == '') {
            $lang_key = LanguageCost::lang_key();
        }
        $in_cache = __FUNCTION__ . '-' . $lang_key;

        // xóa cache nếu có yêu cầu
        if ($clear_cache === true) {
            return $this->base_model->cache->delete($in_cache);
        }

        //
        $cache_value = $this->base_model->scache($in_cache);
        if ($cache_value !== NULL) {
            return $cache_value;
        }

        //
        global $arr_custom_taxonomy;
        global $arr_custom_post_type;

        //
        $allow_taxonomy = [
            TaxonomyType::POSTS,
            TaxonomyType::TAGS,
            //TaxonomyType::BLOGS,
            //TaxonomyType::BLOG_TAGS,
            TaxonomyType::PROD_CATS,
            TaxonomyType::PROD_TAGS,
        ];
        // thêm custom taxonomy vào phần add menu
        $arr_custom_name = [];
        foreach ($arr_custom_taxonomy as $k => $v) {
            // không tạo menu cho các taxonomy được chỉ định không public
            if (isset($v['public']) && $v['public'] != 'on') {
                continue;
            }

            //
            if (!in_array($k, $allow_taxonomy)) {
                $allow_taxonomy[] = $k;

                // tên của custom taxonomy
                if (isset($v['name'])) {
                    $arr_custom_name[$k] = $v['name'];
                }
            }
        }
        //print_r( $allow_taxonomy );

        //
        $arr_result = [];
        foreach ($allow_taxonomy as $allow) {
            $category_list = $this->term_model->get_all_taxonomy($allow, 0, [
                'lang_key' => $lang_key,
                'by_is_deleted' => DeletedStatus::FOR_DEFAULT,
                'parent' => 0,
                //'get_meta' => true,
                //'get_child' => true
                'limit' => $limit
            ]);
            //print_r( $category_list );
            //die( __CLASS__ . ':' . __LINE__ );
            if (empty($category_list)) {
                continue;
            }

            //
            if (isset($arr_custom_name[$allow])) {
                //$arr_result[] = '<option class="bold" disabled>' . $arr_custom_name[ $allow ] . '</option>';
                $arr_result[] = [
                    'class' => 'medium',
                    'selectable' => true,
                    'text' => $arr_custom_name[$allow],
                ];
            } else {
                //$arr_result[] = '<option class="bold" disabled>' . TaxonomyType::typeList( $allow ) . '</option>';
                $arr_result[] = [
                    'class' => 'medium',
                    'selectable' => true,
                    'text' => TaxonomyType::typeList($allow),
                ];
            }

            //
            foreach ($category_list as $cat_key => $cat_val) {
                $arr_result[] = [
                    'value' => $this->term_model->get_term_permalink($cat_val),
                    'text' => $cat_val['name'],
                ];

                // lấy các nhóm con thuộc nhóm này
                $child_list = $this->term_model->get_all_taxonomy($allow, 0, [
                    'lang_key' => $lang_key,
                    'by_is_deleted' => DeletedStatus::FOR_DEFAULT,
                    'parent' => $cat_val['term_id'],
                    //'get_meta' => true,
                    //'get_child' => true
                    'limit' => $limit
                ]);
                //print_r( $child_list );

                //
                foreach ($child_list as $child_key => $child_val) {
                    $arr_result[] = [
                        'value' => $this->term_model->get_term_permalink($child_val),
                        'text' => $child_val['name'],
                    ];
                }
            }
            //print_r( $arr_result );
            //die( __CLASS__ . ':' . __LINE__ );
        }


        //
        $allow_post_type = [
            PostType::PAGE,
            PostType::POST,
            //PostType::BLOG,
            PostType::PROD,
        ];
        // thêm custom post type vào phần add menu
        $arr_custom_name = [];
        foreach ($arr_custom_post_type as $k => $v) {
            if (!in_array($k, $allow_post_type)) {
                $allow_post_type[] = $k;

                // tên của custom taxonomy
                if (isset($v['name'])) {
                    $arr_custom_name[$k] = $v['name'];
                }
            }
        }
        //print_r( $allow_post_type );

        foreach ($allow_post_type as $allow) {
            // các kiểu điều kiện where
            $where = [
                //'post_status !=' => PostType::DELETED,
                'post_type' => $allow,
                'post_status' => PostType::PUBLICITY,
                'lang_key' => $lang_key
            ];

            $filter = [
                /*
                'where_in' => array(
                'post_status' => array(
                //PostType::DRAFT,
                PostType::PUBLICITY,
                //PostType::PENDING,
                )
                ),
                */
                'order_by' => array(
                    'menu_order' => 'DESC',
                    'time_order' => 'DESC',
                    //'post_date' => 'DESC',
                    //'post_modified' => 'DESC',

                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                'offset' => 0,
                'limit' => $limit
            ];
            $page_list = $this->base_model->select('*', $this->table, $where, $filter);
            //print_r( $page_list );

            //
            if (empty($page_list)) {
                continue;
            }

            //
            if (isset($arr_custom_name[$allow])) {
                //$arr_result[] = '<option class="bold" disabled>' . $arr_custom_name[ $allow ] . '</option>';
                $arr_result[] = [
                    'class' => 'medium',
                    'selectable' => true,
                    'text' => $arr_custom_name[$allow],
                ];
            } else {
                //$arr_result[] = '<option class="bold" disabled>' . PostType::typeList( $allow ) . '</option>';
                $arr_result[] = [
                    'class' => 'medium',
                    'selectable' => true,
                    'text' => PostType::typeList($allow),
                ];
            }

            //
            foreach ($page_list as $post_key => $post_val) {
                $arr_result[] = [
                    'value' => $this->get_post_permalink($post_val),
                    'text' => $post_val['post_title'],
                ];
            }
        }

        //
        $lang_prefix = '';
        if ($lang_key != LanguageCost::lang_key()) {
            $lang_prefix = $lang_key . '/';
        }

        //
        //$arr_result[] = '<option class="bold" disabled>Menu hệ thống</option>';
        $arr_system_menu = [
            './' . $lang_prefix . 'guest/login' => 'Đăng nhập',
            './' . $lang_prefix . 'guest/register' => 'Đăng ký',
            './' . $lang_prefix . 'guest/resetpass' => 'Quên mật khẩu',
            './' . $lang_prefix . 'users/profile' => 'Tài khoản',
            './' . $lang_prefix . 'users/logout' => 'Đăng xuất',
            //'./' . $lang_prefix . 'users/changepass' => 'Đổi mật khẩu',
        ];
        $arr_system_menu['./' . CUSTOM_ADMIN_URI] = 'Quản trị hệ thống';

        //
        $arr_result[] = [
            'class' => 'medium',
            'selectable' => true,
            'text' => 'Menu hệ thống',
        ];
        foreach ($arr_system_menu as $k => $v) {
            $arr_result[] = [
                'value' => $k,
                'text' => $v,
            ];
        }
        //print_r( $arr_result );
        //die( __CLASS__ . ':' . __LINE__ );

        //
        $this->base_model->scache($in_cache, $arr_result, $time);

        //
        return $arr_result;
    }
}
