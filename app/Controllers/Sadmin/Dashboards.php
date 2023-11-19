<?php

namespace App\Controllers\Sadmin;

class Dashboards extends Dashboard
{
    public function __construct()
    {
        parent::__construct();

        // kiểm tra quyền truy cập của tài khoản hiện tại
        $this->check_permision(__CLASS__);
    }

    // chức năng upload file code zip lên host và giải nén -> update code
    public function update_code()
    {
        if (!empty($this->MY_post('data'))) {
            $this->unzip_code();
        }

        //
        $arr_list_thirdparty = $this->get_list_thirdparty([
            'public/wp-includes/thirdparty',
            'vendor',
            'app/ThirdParty',
        ]);
        //print_r( $arr_list_thirdparty );

        //
        $arr_download_thirdparty = [
            //'https://github.com/vuejs/vue/releases',
            'https://v2.vuejs.org/v2/guide/installation.html?current_version=2.7.13',
            'https://github.com/PHPMailer/PHPMailer',
            'https://jquery.com/download/?current_version=3.6.1',
            'https://getbootstrap.com/docs/5.0/getting-started/download/?current_version=5.0.2',
            'https://icons.getbootstrap.com/?current_version=1.9.0',
            'https://www.tiny.cloud/get-tiny/?current_version=4.9.11',
            'https://fontawesome.com/v4/icons/?current_version=4.7.0',
            'https://plugins.jquery.com/datetimepicker/?current_version=2.3.6',
            'https://github.com/select2/select2/releases/tag/4.0.13',
            'https://jqueryui.com/download/?current_version=1.13.2',
            'https://github.com/jquery-validation/jquery-validation/releases/tag/1.9.0',
            'https://angularjs.org/?current_version=1.8.2',
            'https://github.com/zaloplatform/zalo-php-sdk',
            'https://github.com/itvn9online/Nestable?current_version=2',
        ];

        //
        $this->teamplate_admin['content'] = view(
            'vadmin/update_code_view',
            array(
                // xác định các thư mục deleted code có tồn tại không
                'app_deleted_exist' => $this->check_deleted_exist(),
                // xác định xem thư mục theme cũ có tồn tại không
                //'theme_deleted_exist' => $theme_deleted_exist,
                'link_download_github' => $this->link_download_github,
                'link_download_system_github' => $this->link_download_system_github,
                'arr_list_thirdparty' => $arr_list_thirdparty,
                'arr_download_thirdparty' => $arr_download_thirdparty,
            )
        );
        return view('vadmin/admin_teamplate', $this->teamplate_admin);
    }
}
