<?php

namespace App\Controllers\Sadmin\Traits;

use App\Libraries\PostType;
use App\Libraries\TaxonomyType;
use App\Libraries\LanguageCost;
use App\Libraries\DeletedStatus;
use App\Helpers\HtmlTemplate;

//
trait PostsListTrait
{
    public function index()
    {
        return $this->lists();
    }
    public function lists($ops = [])
    {
        if (!empty($this->MY_get('auto_update_module', 0))) {
            return $this->action_update_module();
        }

        //
        $post_per_page = $this->post_per_page;
        // URL cho các action dùng chung
        $for_action = '';
        // URL cho phân trang
        $urlPartPage = 'sadmin/' . $this->controller_slug . '?part_type=' . $this->post_type;

        //
        $by_keyword = $this->MY_get('s');
        $post_status = $this->MY_get('post_status');
        $by_term_id = $this->MY_get('term_id', 0);
        $by_user_id = $this->MY_get('user_id');

        // các kiểu điều kiện where
        if (!isset($ops['where'])) {
            $where = [];
        }
        //$where[ $this->table . '.post_status !=' ] = PostType::DELETED;
        $where[$this->table . '.post_type'] = $this->post_type;
        $where[$this->table . '.lang_key'] = $this->lang_key;
        if (!empty($by_user_id) && $by_user_id > 0) {
            $where[$this->table . '.post_author'] = $by_user_id;
            $urlPartPage .= '&user_id=' . $by_user_id;
            $for_action .= '&user_id=' . $by_user_id;
        }

        // tìm kiếm theo từ khóa nhập vào
        $where_or_like = [];
        $where_in = [];
        if ($by_keyword != '') {
            $urlPartPage .= '&s=' . $by_keyword;
            $for_action .= '&s=' . $by_keyword;

            // nếu là email -> tìm theo email thành viên
            if (strpos($by_keyword, '@') !== false) {
                $user_data = $this->base_model->select(
                    'ID',
                    'users',
                    array(
                        'is_deleted' => DeletedStatus::FOR_DEFAULT,
                    ),
                    array(
                        'like' => array(
                            'user_email' => $by_keyword,
                        ),
                        'order_by' => array(
                            'ID' => 'DESC'
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
                        'limit' => 100
                    )
                );
                //print_r($user_data);
                if (!empty($user_data)) {
                    $in_users_id = [];
                    foreach ($user_data as $v) {
                        $in_users_id[] = $v['ID'];
                    }
                    $where_in[$this->table . '.post_author'] = $in_users_id;
                } else {
                    // không có thì đặt điều kiện tìm kiếm là -1 -> không có user ID nào là -1 -> không có kết quả tìm kiếm
                    $where[$this->table . '.post_author'] = '-1';
                }
            }
            // còn lại sẽ tìm theo thông tin đơn hàng
            else {
                $by_like = $this->base_model->_eb_non_mark_seo($by_keyword);
                // tối thiểu từ 1 ký tự trở lên mới kích hoạt tìm kiếm
                if (strlen($by_like) > 0) {
                    //var_dump( strlen( $by_like ) );
                    // nếu là số -> chỉ tìm theo ID
                    if (is_numeric($by_like) === true) {
                        $where_or_like = [
                            'ID' => $by_like * 1,
                            'post_author' => $by_like,
                            'post_parent' => $by_like,
                        ];
                    } else {
                        $where_or_like = [
                            'post_name' => $by_like,
                            'post_title' => $by_keyword,
                        ];
                    }
                }
            }
        }

        //
        if ($post_status == '') {
            $by_post_status = [
                PostType::PUBLICITY,
                PostType::PRIVATELY,
                PostType::PENDING,
                PostType::ON_HOLD,
                PostType::DRAFT,
                PostType::INHERIT,
            ];
        } else {
            $urlPartPage .= '&post_status=' . $post_status;
            $for_action .= '&post_status=' . $post_status;

            $by_post_status = [
                $post_status,
            ];
        }
        $where_in[$this->table . '.post_status'] = $by_post_status;

        // tổng kết filter
        $filter = [
            'where_in' => $where_in,
            'or_like' => $where_or_like,
            // hiển thị mã SQL để check
            // 'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 0,
            'limit' => -1,
        ];

        // nếu có lọc theo term_id -> thêm câu lệnh để lọc
        if ($by_term_id > 0) {
            // lấy các nhóm con của nhóm này
            $ids = $this->base_model->select(
                'GROUP_CONCAT(DISTINCT term_id SEPARATOR \',\') AS ids',
                'term_taxonomy',
                array(
                    // các kiểu điều kiện where
                    'parent' => $by_term_id,
                ),
                array(
                    // trả về COUNT(column_name) AS column_name
                    //'selectCount' => 'ID',
                    // hiển thị mã SQL để check
                    //'show_query' => 1,
                    // trả về câu query để sử dụng cho mục đích khác
                    //'get_query' => 1,
                    // trả về tổng số bản ghi -> tương tự mysql num row
                    //'getNumRows' => 1,
                    //'offset' => 0,
                    'limit' => -1
                )
            );
            // print_r($ids);
            $ids = $ids[0]['ids'];

            //
            if ($ids != '') {
                $ids = str_replace(' ', '', $ids);
                $ids = explode(',', $ids);
                $ids[] = $by_term_id;
                //print_r( $ids );

                //
                $filter['where_in']['term_taxonomy.term_id'] = $ids;
            } else {
                $where['term_taxonomy.term_id'] = $by_term_id;
            }

            $urlPartPage .= '&term_id=' . $by_term_id;
            $for_action .= '&term_id=' . $by_term_id;

            $filter['join'] = [
                'term_relationships' => 'term_relationships.object_id = ' . $this->table . '.ID',
                'term_taxonomy' => 'term_relationships.term_taxonomy_id = term_taxonomy.term_taxonomy_id',
            ];
        }
        // print_r($where);
        // print_r($filter);


        //
        if (isset($ops['add_filter'])) {
            foreach ($ops['add_filter'] as $k => $v) {
                $filter[$k] = $v;
            }
        }


        /**
         * phân trang
         */
        $totalThread = $this->base_model->select_count('ID', $this->table, $where, $filter);
        // echo $totalThread . '<br>' . "\n";

        //
        if ($totalThread > 0) {
            $page_num = $this->MY_get('page_num', 1);

            $totalPage = ceil($totalThread / $post_per_page);
            if ($totalPage < 1) {
                $totalPage = 1;
            }
            // echo $totalPage . '<br>' . "\n";
            if ($page_num > $totalPage) {
                $page_num = $totalPage;
            } else if ($page_num < 1) {
                $page_num = 1;
            }
            $for_action .= ($page_num > 1 ? '&page_num=' . $page_num : '');
            // echo $totalThread . '<br>' . "\n";
            // echo $totalPage . '<br>' . "\n";
            $offset = ($page_num - 1) * $post_per_page;

            // chạy vòng lặp gán nốt các thông số khác trên url vào phân trang
            $urlPartPage = $this->base_model->auto_add_params($urlPartPage);

            //
            $pagination = $this->base_model->EBE_pagination($page_num, $totalPage, $urlPartPage, 'page_num=');


            // select dữ liệu từ 1 bảng bất kỳ
            $filter['offset'] = $offset;
            $filter['limit'] = $post_per_page;

            //
            $order_by = $this->MY_get('order_by');
            if ($order_by != '') {
                $order_by = [
                    $this->table . '.' . $order_by => 'DESC',
                ];
            }
            // với phần q.cáo sẽ sắp xếp theo nhóm và tên cho dễ xem
            else if ($this->post_type == PostType::ADS) {
                $order_by = [
                    $this->table . '.category_primary_id'  => 'ASC',
                    $this->table . '.post_name'  => 'DESC',
                ];
            }
            // mặc định sắp xếp theo stt và thời gian tạo
            else {
                $order_by = [
                    $this->table . '.menu_order' => 'DESC',
                    $this->table . '.time_order' => 'DESC',
                    $this->table . '.ID' => 'DESC',
                ];
            }
            $filter['order_by'] = $order_by;
            // $filter['show_query'] = 1;
            $data = $this->base_model->select('*', $this->table, $where, $filter);

            //
            $data = $this->post_model->list_meta_post($data);
            // print_r($data);

            // trả luôn về data nếu có yêu cầu
            if (isset($ops['get_data']) && $ops['get_data'] === 1) {
                return $data;
            }

            // xử lý dữ liệu cho angularjs
            foreach ($data as $k => $v) {
                // không cần hiển thị nội dung
                $v['post_content'] = '';
                // print_r($v);
                // continue;

                // lấy 1 số dữ liệu khác gán vào, để angularjs chỉ việc hiển thị
                $v['admin_permalink'] = $this->post_model->get_admin_permalink($this->post_type, $v['ID'], $this->controller_slug);
                if ($v['post_type'] == PostType::ORDER) {
                    $v['the_permalink'] = '#';
                } else {
                    $v['post_excerpt'] = '';
                    $v['the_permalink'] = $this->post_model->get_post_permalink($v);
                }
                if (isset($v['post_meta'])) {
                    if (isset($v['image'])) {
                        $v['thumbnail'] = $v['image'];
                    } else {
                        $v['thumbnail'] = $this->post_model->get_list_thumbnail($v['post_meta']);
                    }
                    $v['main_category_key'] = $this->post_model->return_meta_post($v['post_meta'], $this->main_category_key);
                } else {
                    $v['thumbnail'] = '';
                    $v['main_category_key'] = '';
                }

                //
                //print_r( $v );

                //
                $data[$k] = $v;
            }
        } else {
            // trả luôn về data nếu có yêu cầu
            if (isset($ops['get_data']) && $ops['get_data'] === 1) {
                return [];
            }

            // không có dữ liệu
            $data = [];
            $pagination = '';
        }

        //
        $this->teamplate_admin['content'] = view(
            'vadmin/' . $this->list_view_path . '/list',
            array(
                'for_action' => $for_action,
                'by_post_status' => $by_post_status,
                'post_status' => $post_status,
                'by_keyword' => $by_keyword,
                'by_term_id' => $by_term_id,
                'by_user_id' => $by_user_id,
                'controller_slug' => $this->controller_slug,
                'pagination' => $pagination,
                'totalThread' => $totalThread,
                'main_category_key' => $this->main_category_key,
                'tags' => $this->tags,
                'data' => $data,
                'taxonomy' => $this->taxonomy,
                'post_type' => $this->post_type,
                'name_type' => $this->name_type,
                'post_arr_status' => $this->post_arr_status,
                //'list_view_path' => $this->list_view_path,
                'list_table_path' => $this->list_table_path,
            )
        );
        //return $this->teamplate_admin[ 'content' ];
        return view('vadmin/admin_teamplate', $this->teamplate_admin);
    }
    /**
     * Chức năng export dữ liệu bài viết ra file XML hoặc CSV
     **/
}
