<?php

/**
 * Chức năng lưu RewriteRule tương tự như trong file .htaccess -> dùng để redirect khi gặp link 404
 **/

namespace App\Controllers\Admin;

// Libraries
//use App\Libraries\LanguageCost;

//
class Rewriterule extends Admin
{
    // path chứa nội dung cần redirect (nếu có)
    protected $rules_path = WRITEPATH . 'RewriteRule.txt';

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        // cập nhật nội dung cho file nếu có
        $data = $this->MY_post('data');
        if (!empty($data) && isset($data['rules'])) {
            //print_r($data);
            //die(__CLASS__ . ':' . __LINE__);

            //
            if (empty($data['has_change'])) {
                $this->base_model->alert('Không xác định được nội dung thay đổi!', 'error');
            }

            // nếu ko có nội dung và file tồn tại -> xóa file
            if (empty(trim($data['rules'])) && is_file($this->rules_path)) {
                unlink($this->rules_path);
                $this->base_model->alert('Xóa RewriteRule thành công!', 'warning');
            }

            //
            //$data['rules'] = str_replace('RewriteRule', 'rewriterule', $data['rules']);

            // còn lại sẽ cập nhật file
            file_put_contents($this->rules_path, $data['rules'], LOCK_EX);
            $this->base_model->alert('Cập nhật RewriteRule thành công!');
        }

        // hiển thị nội dung file
        $rules_content = '';
        if (is_file($this->rules_path)) {
            $rules_content = file_get_contents($this->rules_path);
        }

        //
        $this->teamplate_admin['content'] = view(
            'admin/configs/rewriterule_view',
            array(
                'rules_content' => $rules_content,
                'rules_path' => $this->rules_path,
            )
        );
        return view('admin/admin_teamplate', $this->teamplate_admin);
    }
}
