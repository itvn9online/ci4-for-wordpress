<?php
/*
 * blog model -> sử dụng chung bảng với post -> chỉ cần exten từ nó ra là được
 */
namespace App\Models;

class Blog extends Post
{
    public function __construct()
    {
        parent::__construct();
    }
}