<?php

/*
 * Nạp model dùng chung cho các model khác
 */
namespace App\ Models;

class EB_Model {
    function __construct() {
        $this->base_model = new\ App\ Models\ Base();
    }
}