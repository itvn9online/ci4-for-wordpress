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

    /**
     * Minify CSS/JS from URL using external API
     */
    protected function minifyFromURL($url, $type = 'js')
    {
        $apiUrl = 'https://closure-compiler.echbay.com/api/minify';

        $data = json_encode([
            'url' => $url,
            'type' => $type
        ]);

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);

        if ($result['success']) {
            return $result['data']['minified'];
        }

        return false;
    }

    /**
     * Compiler for manual minification of CSS/JS files
     **/
    public function compiler()
    {
        // xóa toàn bộ html trước đó
        ob_clean();

        // định dạng header
        // header('Content-Type: text/plain; charset=utf-8');

        // kiểm tra đầu vào
        $file = $this->MY_get('file');
        if (empty($file)) {
            $this->base_model->alert('No file specified.', 'error');
        }

        if (!is_file($file)) {
            $this->base_model->alert('File does not exist.', 'error');
        }

        // lấy nội dung file
        $file_content = file_get_contents($file);
        if ($file_content === false) {
            $this->base_model->alert('Failed to read file.', 'error');
        }

        // nếu đầu file có chứa chú thích của trình nén thì bỏ qua
        if (strpos($file_content, '/* ' . $this->minify_comment . ' */') !== false) {
            $this->base_model->alert('File is already minified.', 'warning');
        }

        // xác định file type
        $file_type = pathinfo($file, PATHINFO_EXTENSION);
        if ($file_type == 'js') {
            // định dạng header
            // header('Content-Type: application/javascript; charset=utf-8');
        } else if ($file_type == 'css') {
            // định dạng header
            // header('Content-Type: text/css; charset=utf-8');
        } else {
            $this->base_model->alert('Unsupported file type.', 'error');
        }
        // die($file_type);

        $url = DYNAMIC_BASE_URL . str_replace(PUBLIC_PUBLIC_PATH, '', $file);
        // die($url);

        $minified = $this->minifyFromURL($url, $file_type);
        if ($minified !== false) {
            // thêm 1 số chú thích vào file đã nén
            $minified = "/* {$this->minify_comment} */\n" . $minified;
            // lưu file đã nén lại
            if (file_put_contents($file, $minified) !== false) {
                // die($minified);
                echo '<script type="text/javascript">top.after_closure_compiler_echbay();</script>';
                $this->base_model->alert('Minification successful.');
            }
        }
        $this->base_model->alert('Minification failed.', 'error');
    }
}
