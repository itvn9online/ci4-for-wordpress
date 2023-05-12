<?php

namespace App\Controllers;

// Libraries
//use App\Libraries\PostType;

//
class Firebases extends Layout
{
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
                'seo' => $this->base_model->default_seo('Phone verify', $this->getClassName(__CLASS__) . '/' . __FUNCTION__),
            )
        );

        // còn không sẽ tiến hành lưu cache
        return view('layout_view', $this->teamplate);
    }
}
