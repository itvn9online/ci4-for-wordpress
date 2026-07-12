<?php

namespace App\Controllers\Sadmin\Traits;

use App\Libraries\PostType;
use App\Libraries\DeletedStatus;

//
trait PostsWriteTrait
{
    protected function add_new($data = null)
    {
        if ($data === null) {
            $data = $this->MY_post('data');
        }
        $data['post_type'] = $this->post_type;
        //print_r( $data );
        //die( __CLASS__ . ':' . __LINE__ );

        //
        $result_id = $this->post_model->insert_post($data, [], $this->post_type == PostType::ADS ? false : true);
        if (is_array($result_id) && isset($result_id['error'])) {
            $this->base_model->alert($result_id['error'], 'error');
        }

        //
        if ($result_id > 0) {
            $this->base_model->alert('', $this->buildAdminPermalink($result_id));
        }
        $this->base_model->alert('Lỗi tạo ' . $this->name_type . ' mới', 'error');
    }

    protected function updating($id)
    {
        $data = $this->MY_post('data');
        //print_r( $data );
        //print_r( $_POST );

        // nhận dữ liệu default từ javascript khởi tạo và truyền vào trong quá trình submit
        if (isset($data['default_post_data'])) {
            foreach ($data['default_post_data'] as $k => $v) {
                if (!isset($this->default_post_data[$k])) {
                    $this->default_post_data[$k] = '';
                }
            }
        }
        //print_r( $this->default_post_data );
        foreach ($this->default_post_data as $k => $v) {
            if (!isset($data[$k])) {
                $data[$k] = $v;
            }
        }

        // convert datetime to timestamp
        //print_r( $data );
        //print_r( $this->timestamp_post_data );
        foreach ($this->timestamp_post_data as $k => $v) {
            //echo $k . '<br>' . "\n";
            //echo $data[ $k ] . '<br>' . "\n";
            if (isset($data[$k]) && $data[$k] != '') {
                $data[$k] = strtotime($data[$k]);
            }
        }
        //print_r($data);

        //
        $result_id = $this->post_model->update_post($id, $data, [
            'post_type' => $this->post_type,
        ]);

        // nếu có lỗi thì thông báo lỗi
        if ($result_id !== true && is_array($result_id) && isset($result_id['error'])) {
            $this->base_model->alert($result_id['error'], 'error');
        }

        // dọn dẹp cache liên quan đến post này -> reset cache
        echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
        $this->cleanup_cache($this->post_model->key_cache($id));
        // bổ sung thêm xóa cache với menu
        if ($this->post_type == PostType::MENU || $this->post_type == PostType::HTML_MENU) {
            if (isset($data['post_name'])) {
                $post_name = $data['post_name'];
                /*
            } else if (isset($data['post_title'])) {
                $post_name = $this->base_model->_eb_non_mark_seo($data['post_title']);
                */
            } else {
                $post_name = '';
            }
            // echo $post_name . '<br>' . "\n";
            $this->cleanup_cache('get_the_menu-' . $post_name);
        }
        // hoặc page
        else if ($this->post_type == PostType::PAGE) {
            if (isset($data['post_name'])) {
                // bỏ ký tự đặc biết để tránh lỗi xóa cache
                $this->cleanup_cache('get_page-' . $this->base_model->_eb_non_mark_seo($data['post_name']));
            }
        }
        // hoặc ads
        else if ($this->post_type == PostType::ADS) {
            $this->cleanup_cache('get_the_ads-');
        }

        // xóa cache cho term liên quan
        if (isset($_POST['post_meta']) && isset($_POST['post_meta']['post_category'])) {
            foreach ($_POST['post_meta']['post_category'] as $v) {
                //echo $v . '<br>' . "\n";
                $this->cleanup_cache($this->term_model->key_cache($v));
            }
        }

        //
        return true;
    }

    protected function update($id)
    {
        $this->updating($id);

        //
        $data = $this->MY_post('data');
        // print_r($data);
        // print_r($_POST);
        // die(__CLASS__ . ':' . __LINE__);

        // nạp lại trang nếu có đổi slug duplicate
        if (
            // url vẫn còn duplicate
            isset($data['post_name']) && strpos($data['post_name'], '-duplicate-') !== false &&
            // tiêu đề không còn Duplicate
            isset($data['post_title']) && strpos($data['post_title'], ' - Duplicate ') === false
        ) {
            // nạp lại trang
            // $this->base_model->alert('', DYNAMIC_BASE_URL . ltrim($_SERVER['REQUEST_URI'], '/'));
            // $this->base_model->alert('', $this->post_model->get_admin_permalink($this->post_type, $id, $this->controller_slug));
            $this->base_model->alert('', $this->buildAdminPermalink($id));
        } else {
            // so sánh url cũ và mới
            $old_postname = $this->MY_post('old_postname');
            // print_r($old_postname);

            // nếu có sự khác nhau
            if (isset($data['post_name']) && $old_postname != $data['post_name']) {
                // lấy data mới -> sau khi update
                $new_data = $this->post_model->select_post($id, [
                    'post_type' => $this->post_type,
                ]);
                // print_r($new_data);

                // -> lấy url mới -> thiết lập lại url ở fronend
                echo '<script>top.set_new_post_url("' . $this->post_model->before_post_permalink($new_data) . '", "' . $new_data['post_name'] . '");</script>';
            }
        }
        // die(__CLASS__ . ':' . __LINE__);

        //
        echo '<script>top.after_update_post();</script>';

        //
        $this->base_model->alert('Cập nhật ' . $this->name_type . ' thành công');
    }

    // chuyển trang sau khi XÓA xong
    protected function after_delete_restore()
    {
        die('<script>top.after_delete_restore();</script>');
    }
    protected function done_delete_restore($id)
    {
        die('<script>top.done_delete_restore(' . $id . ');</script>');
    }
    protected function before_delete_restore($post_status)
    {
        $id = $this->MY_get('id', 0);

        //
        $data = [
            'post_status' => $post_status
        ];
        // print_r($data);
        // die(__CLASS__ . ':' . __LINE__);

        // lấy slug nếu chưa có -> lấy để lệnh update post còn thêm hoặc xóa trash (tùy request hiện tại)
        if (!isset($data['post_name']) || $data['post_name'] == '') {
            $check_slug = $this->base_model->select('post_name', $this->table, [
                'ID' => $id,
                // 'post_status !=' => $post_status,
            ], [
                // hiển thị mã SQL để check
                // 'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                // 'get_query' => 1,
                // 'offset' => 2,
                'limit' => 1
            ]);
            // print_r($check_slug);
            if (!empty($check_slug)) {
                $data['post_name'] = $check_slug['post_name'];
            }
        }
        // print_r($data);
        // die(__CLASS__ . ':' . __LINE__);

        //
        $update = $this->post_model->update_post($id, $data, [
            'post_type' => $this->post_type,
        ]);
        // print_r($update);
        // die(__CLASS__ . ':' . __LINE__);

        // nếu update thành công -> gửi lệnh javascript để ẩn bài viết bằng javascript
        if ($update === true) {
            if ($post_status == PostType::REMOVED && ALLOW_USING_MYSQL_DELETE === true) {
                return $update;
            }
            return $this->done_delete_restore($id);
        }
        // không thì nạp lại cả trang để kiểm tra cho chắc chắn
        return $this->after_delete_restore();
    }
}
