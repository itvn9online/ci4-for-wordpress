<?php
namespace App\ Controllers;

//
class Csrf extends Layout {
    public function __construct() {
        parent::__construct();

        // bảo mật đầu vào khi submit form
        $this->base_model->check_csrf();
    }
}