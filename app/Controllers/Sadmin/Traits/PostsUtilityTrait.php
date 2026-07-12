<?php

namespace App\Controllers\Sadmin\Traits;

//
trait PostsUtilityTrait
{
    protected function createdThumbnail($imagePath)
    {
        $listSizeThumb = $this->config->item('list_thumbnail');
        $listThumbFolder = $this->config->item('thumbnail_folder');

        for ($i = 0; $i < count($listThumbFolder); $i++) {

            $pimageFullPath = $imagePath; // đg dẫn full path

            $config_manip = array(
                'image_library' => 'gd2',
                'source_image' => $pimageFullPath,
                'new_image' => $this->config->item('base_path') . '/Images/' . $listThumbFolder[$i],
                'maintain_ratio' => TRUE,
                'width' => $listSizeThumb[$i]['width'],
                'height' => $listSizeThumb[$i]['height'],
            );

            $this->load->library('image_lib');
            $this->image_lib->initialize($config_manip);

            if (!$this->image_lib->resize()) {
                echo $this->image_lib->display_errors();
                die(' lỗi resize');
            }
            $this->image_lib->clear();
        }
    }

    // chức năng tự động cập nhật lại toàn bộ bài viết mỗi khi có cập nhật mới và cần auto submit
    protected function action_update_module($id = 0)
    {
        $where = [
            // các kiểu điều kiện where
            'post_status' => PostType::PUBLICITY,
            'post_type' => $this->post_type,
        ];
        if ($id > 0) {
            $where['ID <'] = $id;
        }

        //
        $data = $this->base_model->select(
            '*',
            $this->post_model->table,
            $where,
            array(
                'order_by' => array(
                    'ID' => 'DESC'
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 2,
                'limit' => 1
            )
        );
        //print_r( $data );

        //
        if (empty($data)) {
            if ($id > 0) {
                return '';
            }
            echo __FUNCTION__ . '! All done.';
            return false;
        }

        // lấy link sửa bài viết trong admin
        $admin_permalink = $this->post_model->get_admin_permalink($data['post_type'], $data['ID'], $this->controller_slug);
        //echo $admin_permalink . '<br>' . "\n";

        // thêm tham số tự động submit
        $admin_permalink .= '&auto_update_module=1';
        //echo $admin_permalink . '<br>' . "\n";

        //
        if ($id > 0) {
            return $admin_permalink;
        }
        $this->MY_redirect($admin_permalink, 301);
    }

    // chuyển đến bản ghi dựa theo ngôn ngữ đang xem
    protected function redirectLanguage($data, $id)
    {
        // xác định url cha
        $redirect_to = $this->buildAdminPermalink($id, $data['post_type']);
        //die($redirect_to);

        // sau đó redirect tới
        $this->MY_redirect($redirect_to, 301);
        die(__CLASS__ . ':' . __LINE__);
    }

    // trả về URL chỉnh sửa post cho admin
    protected function buildAdminPermalink($id, $post_type = '')
    {
        if ($post_type == '') {
            $post_type = $this->post_type;
        }

        //
        return $this->post_model->get_admin_permalink($post_type, $id, $this->controller_slug) . $this->get_preview_url();
    }
}
