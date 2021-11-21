<?php
/*
 * ads model -> sử dụng chung bảng với post -> chỉ cần exten từ nó ra là được
 */
//require_once __DIR__ . '/Post.php';
namespace App\ Models;

class Ads extends Post {
    public function __construct() {
        parent::__construct();
    }
}