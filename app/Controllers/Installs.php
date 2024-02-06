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
        if (is_file($f)) {
            $this->installsSync();
        } else {
            // var_dump($this->base_model->table_exists(WGR_POST_VIEW));
            // var_dump($this->base_model->table_exists(WGR_TERM_VIEW));

            // nếu chưa có 1 trong 2 view này thì cũng thực hiện sync
            if (
                $this->base_model->table_exists(WGR_POST_VIEW) === false ||
                $this->base_model->table_exists(WGR_TERM_VIEW) === false
            ) {
                $this->installsSync();
            } else {
                echo 'WARNING! code #' . __LINE__ . '. app/sync file not found! ' . '<br>' . PHP_EOL;
                echo '<a href="' . base_url() . '">Back to home</a> ' . '<br>' . PHP_EOL;
            }
        }

        // 
        return false;
    }

    /**
     * Thực hiện đồng bộ database và vendor code
     **/
    protected function installsSync()
    {
        $this->vendor_sync();

        // xóa toàn bộ cache
        $has_cache = $this->base_model->dcache();
        var_dump($has_cache);
    }
}
