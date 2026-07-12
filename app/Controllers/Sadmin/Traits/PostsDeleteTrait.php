<?php

namespace App\Controllers\Sadmin\Traits;

use App\Libraries\PostType;
use App\Libraries\DeletedStatus;

//
trait PostsDeleteTrait
{
    public function delete()
    {
        return $this->before_delete_restore(PostType::DELETED);
    }

    // phục hồi 1 bản ghi
    public function restore()
    {
        return $this->before_delete_restore(PostType::DRAFT);
    }

    // xóa hoàn toàn 1 bản ghi
    protected function before_remove()
    {
        $id = $this->MY_get('id', 0);

        // xem bản ghi này có được đánh dấu là XÓA không
        $data = $this->base_model->select(
            '*',
            $this->post_model->table,
            [
                'ID' => $id,
                'post_status' => PostType::DELETED,
                'post_type' => $this->post_type,
            ],
            array(
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 2,
                'limit' => 1
            )
        );

        //
        if (empty($data)) {
            $this->base_model->alert('Cannot be determined record need DELETE', 'error');
        }
        return $data;
    }
    public function remove($confirm_delete = false)
    {
        // nếu có thuộc tính cho phép xóa hoàn toàn dữ liệu thì tiến hành xóa
        if (ALLOW_USING_MYSQL_DELETE === true) {
            $this->delete_remove($this->MY_get('id', 0));
            return $this->done_delete_restore($this->MY_get('id', 0));
        }
        // mặc định thì chỉ là chuyển về trang thái remove để ẩn khỏi admin
        else {
            $result = $this->before_delete_restore(PostType::REMOVED);
        }

        //
        return $result;
    }

    //
    protected function get_ids()
    {
        $ids = $this->MY_post('ids');
        if (empty($ids)) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => 'ids not found!',
            ]);
        }

        //
        $ids = explode(',', $ids);
        if (count($ids) < 1) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => 'ids EMPTY!',
            ]);
        }
        //print_r( $ids );

        //
        return $ids;
    }

    // xóa hoàn toàn dữ liệu
    protected function delete_remove($id = 0)
    {
        if ($id > 0) {
            $ids = [$id];
        } else {
            $ids = $this->get_ids();
        }

        //die( __CLASS__ . ':' . __LINE__ );
        // XÓA relationships
        $result = $this->base_model->delete_multiple($this->term_model->relaTable, [
            // WHERE
            //'t2.post_status' => PostType::REMOVED,
        ], [
            /*
                'left_join' => array(
                $this->post_model->table . ' AS t2' => $this->term_model->relaTable . '.object_id = t2.ID'
                ),
                */
            'where_in' => array(
                'object_id' => $ids
            ),
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
        ]);
        //die( __CLASS__ . ':' . __LINE__ );

        // XÓA dữ liệu chính
        $result = $this->base_model->delete_multiple($this->post_model->metaTable, [
            // WHERE
            //'t2.post_status' => PostType::REMOVED,
        ], [
            /*
                'left_join' => array(
                $this->post_model->table . ' AS t2' => $this->post_model->metaTable . '.post_id = t2.ID'
                ),
                */
            'where_in' => array(
                'post_id' => $ids
            ),
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
        ]);

        //
        $this->base_model->delete_multiple($this->post_model->table, [
            // WHERE
            //'post_status' => PostType::REMOVED,
        ], [
            'where_in' => array(
                'ID' => $ids
            ),
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
        ]);

        //
        return $result;
    }

    //
    public function before_all_delete_restore($post_status)
    {
        $ids = $this->get_ids();

        //
        $current_data = $this->base_model->select(
            'ID, post_name, post_type',
            $this->table,
            [],
            array(
                'where_in' => array(
                    'ID' => $ids
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
                'limit' => -1
            )
        );

        // chạy vòng lặp -> thực hiện khóa từng post
        foreach ($current_data as $v) {
            //print_r($v);
            $id = $v['ID'];
            unset($v['ID']);

            //
            $v['post_status'] = $post_status;
            //print_r($v);
            //continue;

            //
            $update = $this->post_model->update_post(
                $id,
                $v
            );
        }

        //
        //print_r($current_data);
        //die(__CLASS__ . ':' . __LINE__);

        //
        if (1 > 2) {
            $update = $this->base_model->update_multiple($this->table, [
                // SET
                'post_status' => $post_status
            ], [
                'post_status !=' => $post_status
            ], [
                'where_in' => array(
                    'ID' => $ids
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
            ]);
        }

        // nếu update thành công -> gửi lệnh javascript để ẩn bài viết bằng javascript
        if ($update === true && $post_status == PostType::REMOVED && ALLOW_USING_MYSQL_DELETE === true) {
            return $update;
        }

        //
        $this->result_json_type([
            'code' => __LINE__,
            'result' => $update,
        ]);
    }

    // chức năng xóa nhiều bản ghi 1 lúc
    public function delete_all()
    {
        return $this->before_all_delete_restore(PostType::DELETED);
    }

    // chức năng restore nhiều bản ghi 1 lúc
    public function restore_all()
    {
        return $this->before_all_delete_restore(PostType::DRAFT);
    }

    // chức năng remove nhiều bản ghi 1 lúc
    public function remove_all()
    {
        // nếu có thuộc tính cho phép xóa hoàn toàn dữ liệu thì tiến hành xóa
        if (ALLOW_USING_MYSQL_DELETE === true) {
            $result = $this->delete_remove();
        } else {
            $result = $this->before_all_delete_restore(PostType::REMOVED);
        }

        //
        $this->result_json_type([
            'code' => __LINE__,
            'result' => $result,
            //'ids' => $ids,
        ]);
    }
}
