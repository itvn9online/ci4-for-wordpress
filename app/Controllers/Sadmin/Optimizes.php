<?php

namespace App\Controllers\Sadmin;

class Optimizes extends Optimize
{
    public function __construct()
    {
        parent::__construct();

        // kiểm tra quyền truy cập của tài khoản hiện tại
        $this->check_permision(__CLASS__);
    }

    // sử dụng khi cần nén lại các file tĩnh bằng cách thủ công
    public function index()
    {
        $data = '';
        $time_start = microtime(true);
        // tính năng này không hoạt động trên localhost
        if (strpos($_SERVER['HTTP_HOST'], 'localhost') === false) {
            //die(__CLASS__ . ':' . __LINE__);
            // kiểm tra tiến trình push file qua fpt hay php
            if ($this->using_via_ftp() === true) {
                $this->connCacheId();
            }

            // tạo các file txt xác nhận quá trình optimize
            $c = $this->c_active_optimize;
            $f = $this->f_active_optimize;

            // base code
            $this->before_active_optimize(PUBLIC_PUBLIC_PATH . 'wp-includes/css/', $f, $c);
            $this->before_active_optimize(PUBLIC_PUBLIC_PATH . 'wp-includes/javascript/', $f, $c);
            $this->before_active_optimize(PUBLIC_PUBLIC_PATH . 'wp-includes/javascript/firebasejs/', $f, $c);
            // theme
            $this->before_active_optimize(THEMEPATH . 'css/', $f, $c);
            $this->before_active_optimize(THEMEPATH . 'js/', $f, $c);
            $this->before_active_optimize(THEMEPATH . 'page-templates/', $f, $c);
            $this->before_active_optimize(THEMEPATH . 'term-templates/', $f, $c);
            // optimize phần view -> nếu muốn optimize thủ công thì mở comment đoạn sau, còn không chỉ optimize khi update code
            $this->before_active_optimize(VIEWS_PATH, $f, $c);

            // bắt đầu optimize
            ob_start();
            $this->optimize_css_js();

            // close connect ftp sau khi xong việc
            if ($this->conn_clear_id === true) {
                ftp_close($this->conn_cache_id);
                $this->file_model->conn_cache_id = null;

                //
                echo '<strong>FTP close</strong>:<em>' . __CLASS__ . '</em>:' . __LINE__ . '<br>' . "\n";
            }
            $data = ob_get_contents();
            ob_end_clean();
        }

        //
        $this->teamplate_admin['content'] = view('vadmin/optimize_view', [
            'total_time' => number_format(microtime(true) - $time_start, 3),
            'data' => $data,
        ]);
        return view('vadmin/admin_teamplate', $this->teamplate_admin);
    }
}
