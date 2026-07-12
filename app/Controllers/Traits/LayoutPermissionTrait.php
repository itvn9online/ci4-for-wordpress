<?php

namespace App\Controllers\Traits;

use App\Libraries\PostType;

//
trait LayoutPermissionTrait
{
    protected function post_permission($data)
    {
        // print_r($this->session_data);

        // nếu bài viết ở chế độ riêng tư
        if ($data['post_status'] == PostType::PRIVATELY) {
            // -> chỉ đăng nhập mới có thể xem
            if ($this->current_user_id < 1) {
                return 'WARNING ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! Bạn không có quyền xem nội dung này...';
            }
        }
        // nếu bài này không phải dạng public
        else if ($data['post_status'] != PostType::PUBLICITY) {
            // kiểm tra xem nếu không phải admin thì không cho xem
            if (empty($this->session_data) || !isset($this->session_data['userLevel']) || $this->session_data['userLevel'] < 1) {
                return 'ERROR ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! Cannot be determined post data...';
            }
        }

        //
        return true;
    }

    protected function hasFlashSession()
    {
        // không cache nếu phương thức tuyền vào không phải là get
        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
            return true;
        }
        // Save cache -> không lưu cache khi có session thông báo riêng
        else if ($this->base_model->msg_session() != '' || $this->base_model->msg_error_session() != '') {
            return true;
        }
        return false;
    }
}
