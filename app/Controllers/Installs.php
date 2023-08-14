<?php

namespace App\Controllers;

/*
 * mục đích duy nhất của controller này là để đồng bộ dữ liệu thôi
 */

//
class Installs extends Sync
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $f = APPPATH . 'sync.txt';

        // chỉ khi tồn tại file sync thì mới sync
        if (file_exists($f)) {
            $this->vendor_sync();

            // xóa toàn bộ cache
            $has_cache = $this->base_model->dcache();
            var_dump($has_cache);
        } else {
            echo 'WARNING! code #' . __LINE__ . '. sync file not found!<br>' . PHP_EOL;
        }
    }
}
