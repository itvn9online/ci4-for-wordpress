<?php

namespace App\Controllers;

//
class Firebases extends Guest
{
    public $file_auth = 'phone_auth';

    public function __construct()
    {
        parent::__construct();
    }

    // view cho phần xác thực số điện thoại  bằng firebase
    public function phone_auth()
    {
        $this->teamplate['main'] = view(
            'phone_auth_view',
            array(
                'seo' => $this->base_model->default_seo($this->lang_model->get_the_text('firebase_title', 'Xác minh số điện thoại'), $this->getClassName(__CLASS__) . '/' . __FUNCTION__),
                'breadcrumb' => '',
                'file_auth' => $this->file_auth,
                'phone_number' => $this->MY_get('phone_number', ''),
                // tạo chuỗi dùng để xác thực url sau khi đăng nhập thành công
                'sign_in_success_params' => $this->firebaseSignInSuccessParams(),
                'expires_time' => $this->expires_time,
                'firebase_config' => $this->firebase_config,
                'zalooa_config' => $this->zalooa_config,
            )
        );

        // còn không sẽ tiến hành lưu cache
        return view('layout_view', $this->teamplate);
    }
}
