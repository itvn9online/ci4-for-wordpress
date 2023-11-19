<?php

namespace App\Controllers\Sadmin;

// Libraries
//use App\Libraries\ConfigType;

//
class Logs extends Sadmin
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
            'vadmin/logs/list',
            array(
                //'data' => $data,
            )
        );
        //return $this->teamplate_admin[ 'content' ];
        return view('vadmin/admin_teamplate', $this->teamplate_admin);
    }
}
