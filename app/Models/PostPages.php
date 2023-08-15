<?php

namespace App\Models;

// Libraries
use App\Libraries\PostType;

//
class PostPages extends PostPosts
{
    public function __construct()
    {
        parent::__construct();
    }

    // page
    public function get_pages_by($where, $ops = [])
    {
        // fix cứng tham số
        $where['post_type'] = PostType::PAGE;
        $where['post_status'] = PostType::PUBLICITY;
        //print_r($where);

        //
        if (!isset($ops['order_by']) || !is_array($ops['order_by']) || empty($ops['order_by'])) {
            $ops['order_by'] = [
                'menu_order' => 'DESC',
                'time_order' => 'DESC',
                'ID' => 'DESC',
            ];
        }

        //
        if (!isset($ops['select_col']) || $ops['select_col'] == '') {
            $ops['select_col'] = '*';
        }

        //
        if (!isset($ops['offset']) || $ops['offset'] < 0) {
            $ops['offset'] = 0;
        }
        if (!isset($ops['limit']) || $ops['limit'] < 1) {
            $ops['limit'] = 0;
        }

        //
        $data = $this->base_model->select($ops['select_col'], 'posts', $where, array(
            'order_by' => $ops['order_by'],
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            // trả về COUNT(column_name) AS column_name
            //'selectCount' => 'ID',
            // trả về tổng số bản ghi -> tương tự mysql num row
            //'getNumRows' => 1,
            'offset' => $ops['offset'],
            'limit' => $ops['limit'],
        ));
        //die(__CLASS__ . ':' . __LINE__);
        //print_r($data);

        //
        return $data;
    }
}
