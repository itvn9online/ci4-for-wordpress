<?php

namespace App\Controllers;

// Libraries
//use App\Libraries\PostType;

//
class Firebases extends Guest
{
    public function __construct()
    {
        parent::__construct();
    }

    // view cho phần xác thực số điện thoại  bằng firebase
    public function phone_auth()
    {
        $current_time = time();

        //
        $this->teamplate['main'] = view(
            'phone_auth_view',
            array(
                'seo' => $this->base_model->default_seo($this->lang_model->get_the_text('firebase_title', 'Xác minh số điện thoại'), $this->getClassName(__CLASS__) . '/' . __FUNCTION__),
                // phần đăng nhập qua điện thoại sẽ được ưu tiên code riêng do nó có mất phí
                'file_auth' => 'phone_auth',
                // các chức năng đăng nhập khác sẽ được viết riêng vào đây
                //'file_auth' => 'dynamic_auth',
                // tạo chuỗi dùng để xác thực url sau khi đăng nhập thành công
                'sign_in_success_url' => base_url('firebases/sign_in_success') . '?' . implode('&', [
                    'expires_token=' . $current_time,
                    'access_token=' . $this->base_model->mdnam($current_time),
                    'user_token=' . $this->base_model->mdnam($this->current_user_id . $current_time),
                ]),
            )
        );

        // còn không sẽ tiến hành lưu cache
        return view('layout_view', $this->teamplate);
    }

    public function sign_in_success()
    {
        # code...
    }
}
