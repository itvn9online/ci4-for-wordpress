<?php

namespace App\Controllers\Sadmin;

// Libraries
//use App\Libraries\ConfigType;

//
class Logs extends Dev
{
    protected $dir_log = WRITEPATH . 'logs';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Liệt kê danh sách file log trong thư mục logs
     **/
    public function index()
    {
        // echo $this->dir_log . '<br>' . "\n";

        // xóa log với phương thức POST
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            echo $this->dir_log . '<br>' . "\n";
            $arr = glob($this->dir_log . '/*.{log,txt}', GLOB_BRACE);
            // print_r($arr);
            foreach ($arr as $filename) {
                echo $filename . ':<em>' . __CLASS__ . '</em>:' . __LINE__ . '<br>' . "\n";

                //
                if (is_file($filename)) {
                    // delete file
                    $this->MY_unlink($filename);
                    // unlink($filename);
                }
            }

            // xóa debug -> loại này khá nặng
            $dir_debugbar = WRITEPATH . 'debugbar';
            echo $dir_debugbar . '<br>' . "\n";
            $arr = glob($dir_debugbar . '/*.json');
            // print_r($arr);
            foreach ($arr as $filename) {
                echo $filename . ':<em>' . __CLASS__ . '</em>:' . __LINE__ . '<br>' . "\n";

                //
                if (is_file($filename)) {
                    // delete file
                    $this->MY_unlink($filename);
                    // unlink($filename);
                }
            }

            // xóa session nếu ko dùng đến
            if (MY_SESSION_DRIVE != 'FileHandler') {
                $dir_session = WRITEPATH . 'session';
                echo $dir_session . '<br>' . "\n";
                $arr = glob($dir_session . '/*.');
                // print_r($arr);
                foreach ($arr as $filename) {
                    echo $filename . ':<em>' . __CLASS__ . '</em>:' . __LINE__ . '<br>' . "\n";

                    //
                    if (is_file($filename)) {
                        // delete file
                        $this->MY_unlink($filename);
                        // unlink($filename);
                    }
                }
            }

            // 
            // die(__CLASS__ . ':' . __LINE__);
            $this->base_model->alert('', base_url('sadmin/logs'));
        }

        //
        // ini_set('memory_limit', '100M');
        ini_set('memory_limit', -1);

        /**
         * db không cần update liên tục, nếu cần thì clear cache để tái sử dụng
         */
        $has_update = $this->base_model->scache(__FUNCTION__);
        // $has_update = null;
        if ($has_update === null) {
            $this->base_model->scache(__FUNCTION__, time(), MEDIUM_CACHE_TIMEOUT);

            /**
             * xóa log quá 1 tháng trước
             */
            $current_time = time() - (86400 * 30);
            $max_i = 60;
            for ($i = 0; $i < 500; $i++) {
                if ($max_i < 0) {
                    echo 'max_i: ' . $max_i . '<br>' . "\n";
                    break;
                }

                //
                $old_log = WRITEPATH . 'logs/log-' . date('Y-m-d', $current_time - ($i * DAY)) . '.log';
                // echo $old_log . '<br>' . "\n";

                //
                if (!is_file($old_log)) {
                    $max_i--;
                    continue;
                }
                echo $old_log . '<br>' . "\n";
                unlink($old_log);
            }
        }

        //
        $this->teamplate_admin['content'] = view(
            'vadmin/logs/list',
            array(
                'file_log' => $this->MY_get('f'),
                'dir_log' => $this->dir_log,
            )
        );
        //return $this->teamplate_admin[ 'content' ];
        return view('vadmin/admin_teamplate', $this->teamplate_admin);
    }
}
