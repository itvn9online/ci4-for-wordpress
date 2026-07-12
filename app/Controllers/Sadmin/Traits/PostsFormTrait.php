<?php

namespace App\Controllers\Sadmin\Traits;

use App\Libraries\PostType;
use App\Libraries\TaxonomyType;
use App\Libraries\LanguageCost;
use App\Libraries\DeletedStatus;
use App\Libraries\CommentType;
use App\Helpers\HtmlTemplate;

//
trait PostsFormTrait
{
    public function add($ops = [])
    {
        $id = $this->MY_get('id', 0);
        $auto_update_module = $this->MY_get('auto_update_module', 0);

        //
        if (!empty($this->MY_post('data'))) {
            //die(__CLASS__ . ':' . __LINE__);
            // nếu là nhân bản
            if ($this->MY_post('is_duplicate', 0) * 1 > 0) {
                // print_r($_POST);

                // select dữ liệu từ 1 bảng bất kỳ
                $dup_data = $this->post_model->select_post($id, [
                    'post_type' => $this->post_type,
                ]);
                // print_r($dup_data);
                // die(__CLASS__ . ':' . __LINE__);

                // đổi lại tiêu đề để tránh trùng lặp
                $new_dup_title = ' - Duplicate ' . date('Ymd-His');
                if (isset($dup_data['post_title'])) {
                    $dup_data['post_title'] = trim(explode('- Duplicate', $dup_data['post_title'])[0]) . $new_dup_title;
                }
                $dup_data['post_date'] = date(EBE_DATETIME_FORMAT);
                $dup_data['post_date_gmt'] = $dup_data['post_date'];
                $dup_data['post_modified'] = $dup_data['post_date'];
                $dup_data['post_modified_gmt'] = $dup_data['post_date'];
                if (isset($dup_data['post_name'])) {
                    $dup_data['post_name'] = trim(explode('-duplicate', $dup_data['post_name'])[0]) . $new_dup_title;
                }
                if (isset($dup_data['post_shorttitle']) && !empty($dup_data['post_shorttitle'])) {
                    $dup_data['post_shorttitle'] = trim(explode('- Duplicate', $dup_data['post_shorttitle'])[0]) . $new_dup_title;
                }
                $dup_data['post_permalink'] = '';
                $dup_data['created_source'] = __CLASS__ . ' ' . $_SERVER['REQUEST_URI'];
                $dup_data['ID'] = 0;
                unset($dup_data['ID']);

                // -> bỏ ID đi
                $id = 0;

                // TEST
                // print_r($dup_data);
                // die(__CLASS__ . ':' . __LINE__);

                //
                return $this->add_new($dup_data);
            }

            // update
            if ($id > 0) {
                return $this->update($id);
            }
            // insert
            return $this->add_new();
        }

        //
        $file_view = 'add';

        // edit
        $url_next_post = '';
        $prev_post = [];
        $next_post = [];
        $child_post = [];
        $data_comments = [];
        if ($id > 0) {
            // select dữ liệu từ 1 bảng bất kỳ
            $data = $this->post_model->select_post($id, [
                'post_type' => $this->post_type,
                //'lang_key' => $this->lang_key,
            ]);
            if (empty($data)) {
                die('post not found!' . __CLASS__ . ':' . __LINE__);
            }
            //print_r($data);

            // nếu ngôn ngữ của post không đúng với ngôn ngữ đang hiển thị
            $clone_lang = $this->MY_get('clone_lang');
            if ($clone_lang != '' && $clone_lang != $data['lang_key']) {
                //die(__CLASS__ . ':' . __LINE__);
                // ngôn ngữ hiện tại có cha -> chuyển đến bản ghi cha
                if ($data['lang_parent'] > 0) {
                    //die(__CLASS__ . ':' . __LINE__);
                    $this->redirectLanguage($data, $data['lang_parent']);
                }
                // nếu không có cha
                else {
                    //die(__CLASS__ . ':' . __LINE__);
                    // tìm bản ghi con
                    $child_data = $this->post_model->select_post(0, [
                        'post_type' => $this->post_type,
                        'lang_key' => $clone_lang,
                        'lang_parent' => $data['ID'],
                        // ko lấy các bản ghi đã bị xóa hẳn
                        'post_status !=' => PostType::REMOVED,
                    ]);
                    //print_r($child_data);
                    //die(__CLASS__ . ':' . __LINE__);

                    // nếu không có thì báo lỗi hiển thị
                    if (empty($child_data)) {
                        //die(__CLASS__ . ':' . __LINE__);
                        // nếu bài viết hiện tại đang là ngôn ngữ mặc định
                        //if (isset($_GET['lang_duplicate']) && $data['lang_key'] == LanguageCost::default_lang()) {
                        // nhân bản cho ngôn ngữ phụ
                        //print_r($data);
                        // nhân bản data
                        $dup_data = $data;
                        $dup_data['lang_key'] = $clone_lang;
                        $dup_data['lang_parent'] = $data['ID'];
                        $dup_data['post_permalink'] = '';
                        $dup_data['updated_permalink'] = 0;
                        // xóa phần ID để tránh xung đột primary key
                        $dup_data['ID'] = 0;
                        unset($dup_data['ID']);
                        $post_meta = $dup_data['post_meta'];
                        unset($dup_data['post_meta']);
                        //
                        $dup_data['post_date'] = date(EBE_DATETIME_FORMAT);
                        $dup_data['post_date_gmt'] = $dup_data['post_date'];
                        $dup_data['post_modified'] = $dup_data['post_date'];
                        $dup_data['post_modified_gmt'] = $dup_data['post_date'];

                        //
                        //print_r($dup_data);
                        //die(__CLASS__ . ':' . __LINE__);

                        //
                        $result_id = $this->post_model->insert_post($dup_data, $post_meta);
                        if (is_array($result_id) && isset($result_id['error'])) {
                            die($result_id['error'] . ':' . __CLASS__ . ':' . __LINE__);
                        }
                        //echo $result_id;
                        $redirect_to = $this->buildAdminPermalink($result_id);
                        // thêm tham số cập nhật lang parent
                        $redirect_to .= '&lang_clone_done=' . $data['ID'] . '&lang_clone_key=' . $clone_lang;
                        //die($redirect_to);

                        // sau đó redirect tới
                        $this->MY_redirect($redirect_to, 301);
                        // die(__CLASS__ . ':' . __LINE__);
                        //}

                        // thay đổi file view sang file thông báo tạo ngôn ngữ mới
                        //$file_view = 'clone_lang';
                        // cố định thư mục chứa view
                        //$this->add_view_path = 'posts';

                        // mặc định hiển thị thông báo lỗi cho việc lấy dữ liệu
                        //die('post not found because data_lang_key(' . $data['lang_key'] . ') != this_lang_key(' . $this->lang_key . ')');
                    } else {
                        $this->redirectLanguage($child_data, $child_data['ID']);
                    }
                }
            }

            // tự động cập nhật lại slug khi nhân bản
            if (
                // url vẫn còn duplicate
                strpos($data['post_name'], '-duplicate-') !== false &&
                // tiêu đề không còn Duplicate
                strpos($data['post_title'], ' - Duplicate ') === false
            ) {
                //die(__CLASS__ . ':' . __LINE__);
                //echo 'bbbbbbbbbbbbb';

                //
                $duplicate_name = $this->base_model->_eb_non_mark_seo($data['post_title']);
                if ($this->post_type == PostType::ADS) {
                    // với mục q.cáo -> không dùng slug nên tự thêm id vào slug để tránh trùng lặp
                    $duplicate_name .= '-' . $id;
                }

                //
                $result_update = $this->post_model->update_post($id, [
                    //'post_title' => $data['post_title'],
                    'post_name' => $duplicate_name
                ], [
                    'post_type' => $this->post_type,
                ]);

                // nếu có lỗi thì thông báo lỗi
                if ($result_update !== true && is_array($result_update) && isset($result_update['error'])) {
                    //die(__CLASS__ . ':' . __LINE__);
                    die($result_update['error']);
                    //$this->base_model->alert($result_update['error'], 'error');
                }

                // lấy data mới -> sau khi update
                $new_data = $this->post_model->select_post($id, [
                    'post_type' => $this->post_type,
                ]);
                //print_r($new_data);
                // cập nhật lại slug luôn vào ngay
                $this->post_model->before_post_permalink($new_data);

                //
                //$this->MY_redirect(DYNAMIC_BASE_URL . ltrim($_SERVER['REQUEST_URI'], '/'), 301);
                //$this->MY_redirect($this->post_model->get_admin_permalink($this->post_type, $id, $this->controller_slug), 301);
                $this->MY_redirect($this->buildAdminPermalink($id), 301);
            }

            // lấy bài tiếp theo để auto next
            if ($auto_update_module > 0) {
                //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";

                //
                $url_next_post = $this->action_update_module($id);
            } else {
                // lấy các bài khác cùng nhóm để xử lý cho tiện -> nhiều khi muốn sửa thì sửa luôn
                $prev_post = $this->post_model->select_post(0, [
                    'ID <' => $data['ID'],
                    'post_type' => $this->post_type,
                    'lang_key' => $this->lang_key,
                ], [
                    'where_in' => array(
                        'post_status' => array(
                            PostType::DRAFT,
                            PostType::PUBLICITY,
                            PostType::PENDING,
                        )
                    ),
                    'order_by' => array(
                        'menu_order' => 'DESC',
                        'time_order' => 'DESC',
                        'ID' => 'ASC'
                    ),
                    //'show_query' => 1,
                    'limit' => 5,
                ], 'ID, post_title, post_name');
                //print_r($prev_post);

                //
                $next_post = $this->post_model->select_post(0, [
                    'ID >' => $data['ID'],
                    'post_type' => $this->post_type,
                    'lang_key' => $this->lang_key,
                ], [
                    'where_in' => array(
                        'post_status' => array(
                            PostType::PUBLICITY,
                            PostType::PENDING,
                            PostType::DRAFT,
                        )
                    ),
                    'order_by' => array(
                        'menu_order' => 'DESC',
                        'time_order' => 'DESC',
                        'ID' => 'ASC'
                    ),
                    //'show_query' => 1,
                    'limit' => 5,
                ], 'ID, post_title, post_name');
                //print_r($next_post);

                // các dữ liệu con -> post_parent
                $child_post = $this->post_model->select_post(0, [
                    'post_type' => $this->post_type,
                ], [
                    'where_or' => [
                        'post_parent' => $data['ID'],
                        'lang_parent' => $data['ID'],
                    ],
                    'order_by' => array(
                        'menu_order' => 'DESC',
                        'ID' => 'ASC'
                    ),
                    // 'show_query' => 1,
                    'limit' => 99,
                ], 'ID, post_title, post_name, post_status, post_type, lang_key');
                // print_r($child_post);
            }

            // tinh chỉnh lại URL cho phần upload -> 1 số vụ nó bị ăn URL kép
            $data['post_content'] = str_replace('..//', '../', $data['post_content']);
            $data['post_content'] = str_replace('//upload/', '/upload/', $data['post_content']);

            // 
            $post_permalink = $this->post_model->before_post_permalink($data);
            // echo $post_permalink . '<br>' . "\n";
            if ($post_permalink !== null && $data['post_permalink'] != $post_permalink) {
                $this->MY_redirect($this->buildAdminPermalink($data['ID']), 301);
                // die(__CLASS__ . ':' . __LINE__);
            }

            // hỗ trợ các web dùng danh mục trực tiếp trong bảng post -> select nhanh hơn
            if (isset($data['post_meta']) && isset($data['post_meta']['post_category']) && empty($data['post_meta']['post_category'])) {
                if (!empty($data['category_second_id'])) {
                    $data['post_meta']['post_category'] = $data['category_second_id'];
                } else if (!empty($data['category_primary_id'])) {
                    $data['post_meta']['post_category'] = $data['category_primary_id'];
                }
            }

            // 
            $data_comments = $this->base_model->select('*', 'comments', [
                'comment_post_ID' => $id,
                'comment_type' => $this->comment_type,
                'is_deleted' => DeletedStatus::FOR_DEFAULT,
            ], [
                // 'show_query' => 1,
                'order_by' => [
                    'comment_ID' => 'DESC',
                ],
                'limit' => 50,
            ]);
            // print_r($data_comments);
        }
        // add
        else {
            $data = $this->base_model->default_data($this->table);
            $data['post_meta'] = [];
        }
        //print_r($this->session_data);
        //print_r($data);


        //
        $post_cat = '';
        $post_tags = '';
        $post_options = '';
        $parent_post = [];
        // lấy danh sách các trang để chọn bài cha
        if ($this->post_type == PostType::PAGE) {
            // các kiểu điều kiện where
            $where = [
                //$this->table . '.post_status !=' => PostType::DELETED,
                $this->table . '.post_type' => $this->post_type,
                $this->table . '.lang_key' => $this->lang_key
            ];

            $filter = [
                'where_in' => array(
                    $this->table . '.post_status' => array(
                        PostType::PUBLICITY,
                        PostType::PENDING,
                        PostType::DRAFT,
                    )
                ),
                'where_not_in' => array(
                    $this->table . '.ID' => array(
                        $id
                    )
                ),
                'order_by' => array(
                    $this->table . '.menu_order' => 'DESC',
                    $this->table . '.time_order' => 'DESC',
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 0,
                //'limit' => $post_per_page

            ];
            $parent_post = $this->base_model->select($this->table . '.ID, ' . $this->table . '.post_title', $this->table, $where, $filter);
            //print_r( $parent_post );
        }
        // lấy danh sách các nhóm để add cho post
        else {
            //$post_cat = $this->term_model->get_all_taxonomy( $this->taxonomy, 0, [ 'get_child' => 1 ], $this->taxonomy . '_get_child' );
            $post_cat = $this->taxonomy;
            if ($this->tags != '') {
                //$post_tags = $this->term_model->get_all_taxonomy( $this->tags, 0, [ 'get_child' => 1 ], $this->tags . '_get_child' );
                $post_tags = $this->tags;
            }
            if ($this->options != '') {
                $post_options = $this->options;
            }
        }

        //
        if ($this->debug_enable === true) {
            echo '<!-- ';
            print_r($data);
            echo ' -->';
        }

        //
        //echo ADMIN_ROOT_VIEWS;

        //
        $this->teamplate_admin['content'] = view(
            'vadmin/' . $this->add_view_path . '/' . $file_view,
            array(
                'controller_slug' => $this->controller_slug,
                'lang_key' => $this->lang_key,
                'auto_update_module' => $auto_update_module,
                'url_next_post' => $url_next_post,
                'prev_post' => $prev_post,
                'next_post' => $next_post,
                'child_post' => $child_post,
                'post_cat' => $post_cat,
                'post_tags' => $post_tags,
                'post_options' => $post_options,
                'parent_post' => $parent_post,
                'quick_menu_list' => [],
                'data' => $data,
                'post_lang' => ($data['lang_key'] != '' ? LanguageCost::typeList($data['lang_key']) : ''),
                'meta_default' => $this->post_model->post_meta_default($this->post_type),
                'post_arr_status' => $this->post_arr_status,
                'taxonomy' => $this->taxonomy,
                'tags' => $this->tags,
                'options' => $this->options,
                'post_type' => $this->post_type,
                'name_type' => $this->name_type,
                'ads_post_type' => PostType::ADS,
                'menu_post_type' => PostType::MENU,
                'preview_url' => $this->MY_get('preview_url'),
                'preview_offset_top' => $this->MY_get('preview_offset_top'),
                // mảng tham số tùy chỉnh dành cho các custom post type
                'meta_custom_type' => $this->post_model->meta_custom_label($this->post_type),
                'meta_custom_desc' => $this->post_model->meta_custom_label($this->post_type, 'desc'),
                // thêm phần controller slug theo từng taxonomy
                'arr_taxonomy_controller' => TaxonomyType::controllerList(),
                'data_comments' => $data_comments,
            )
        );
        return view('vadmin/admin_teamplate', $this->teamplate_admin);
    }
}
