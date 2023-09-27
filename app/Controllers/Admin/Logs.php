<?php

namespace App\Controllers\Admin;

// Libraries
//use App\Libraries\ConfigType;

//
class Logs extends Admin
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Liệt kê danh sách file log trong thư mục logs
     **/
    public function index()
    {
        $this->teamplate_admin['content'] = view(
            'admin/logs/list',
            array(
                //'data' => $data,
            )
        );
        //return $this->teamplate_admin[ 'content' ];
        return view('admin/admin_teamplate', $this->teamplate_admin);
    }
}
