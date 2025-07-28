<?php

/**
 * Custom Ajax Controller for Sadmin
 * Dùng để custom lại các chức năng AJAX trong khu vực quản trị Sadmin
 * Thừa kế từ Asjaxs để sử dụng các chức năng đã có
 * Copy file này vào project của child-theme để sử dụng
 */

namespace App\Controllers\Sadmin;

class Acjaxs extends Asjaxs
{
    public function __construct()
    {
        parent::__construct();
    }
}
