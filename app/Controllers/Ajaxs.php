<?php

namespace App\Controllers;

class Ajaxs extends Layout
{
    // chức năng này không cần nạp header
    public $preload_header = false;

    protected $select_term_col = 'term_id, name, slug, term_shortname, term_group, count, parent, taxonomy, child_count, child_last_count';

    public function __construct()
    {
        parent::__construct();
    }

    public function multi_loged()
    {
        //die( json_encode( $_GET ) );

        // lấy nội dung đăng nhập cũ trước khi lưu phiên mới
        $result = $this->user_model->get_logged($this->current_user_id);

        // lưu session id của người dùng vào file
        $this->user_model->set_logged($this->current_user_id);


        // trả về key đã lưu của người dùng trong file
        $this->result_json_type(
            [
                't' => time(),
                'hash' => $result
            ]
        );
    }

    public function get_taxonomy_by_ids()
    {
        header('Content-type: application/json; charset=utf-8');

        //
        $ids = $this->MY_post('ids', '');
        if (empty($ids)) {
            die(json_encode(
                [
                    'code' => __LINE__,
                    'error' => 'EMPTY ids'
                ]
            ));
        }

        //
        $data = $this->base_model->select(
            $this->select_term_col,
            WGR_TERM_VIEW,
            array(
                // WHERE AND OR
                //'is_member' => User_type::GUEST,

            ),
            array(
                'where_in' => array(
                    'term_id' => explode(',', $ids)
                ),
                // trả về COUNT(column_name) AS column_name
                //'selectCount' => 'ID',
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                // trả về tổng số bản ghi -> tương tự mysql num row
                //'getNumRows' => 1,
                //'offset' => 2,
                'limit' => -1
            )
        );

        //
        die(json_encode($data));
    }

    public function the_base_url()
    {
        die(DYNAMIC_BASE_URL);
    }

    public function sync_ajax_post_term()
    {
        // đồng bộ lại tổng số nhóm con cho các danh mục trước đã
        $this->result_json_type(
            [
                'term' => $this->term_model->sync_term_child_count(),
                'post' => $this->post_model->sync_post_term_permalink(),
            ]
        );
    }
}
