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
    protected function minifyFromURL($url, $type = 'js', $useDefaultOptions = true)
    {
        $apiUrl = 'https://closure-compiler.echbay.com/api/minify';

        $payload = [
            'url' => $url . '?v=' . time(),
            'type' => $type
        ];

        // Thêm options nếu cần
        if ($useDefaultOptions) {
            if ($type === 'js') {
                $payload['options'] = [
                    // Nén code, xóa dead code
                    'compress' => true,
                    // Rút ngắn tên biến (a, b, c...)
                    'mangle' => true,
                    'format' => [
                        // Xóa comments
                        'comments' => false
                    ]
                ];
            } else if ($type === 'css') {
                $payload['options'] = [
                    // Mức độ tối ưu hóa cao nhất
                    'level' => 2
                ];
            }
        }

        $data = json_encode($payload);

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
        // print_r($result);
        if (isset($result['error'])) {
            if (gettype($result['error']) == 'array') {
                $result['error'] = implode('; ', $result['error']);
            }
            echo '<script type="text/javascript">top.after_closure_compiler_echbay("error");</script>';
            $this->base_model->alert($result['error'], 'error');
        }
        // die(__CLASS__ . ':' . __LINE__);

        return false;
    }

    /**
     * Minify CSS/JS from file using external API
     **/
    protected function minifyFile($code, $type = 'js', $filePath = null)
    {
        // Đọc nội dung file
        if ($filePath !== null) {
            $code = file_get_contents($filePath);

            if ($code === false) {
                throw new Exception("Cannot read file: $filePath");
            }
        }

        // Gọi API minify
        $url = 'https://closure-compiler.echbay.com/api/minify';

        // Cấu hình options theo loại file
        $options = [];
        if ($type === 'js') {
            $options = [
                // Nén code, xóa dead code
                'compress' => true,
                // Rút ngắn tên biến (a, b, c...)
                'mangle' => true,
                'format' => [
                    // Xóa comments
                    'comments' => false
                ]
            ];
        } else if ($type === 'css') {
            $options = [
                // Mức độ tối ưu hóa cao nhất
                'level' => 2
            ];
        }

        $data = json_encode([
            'code' => $code,
            'type' => $type,
            'options' => $options
        ]);

        $ch = curl_init($url);
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
        // print_r($result);
        if (isset($result['error'])) {
            if (gettype($result['error']) == 'array') {
                $result['error'] = implode('; ', $result['error']);
            }
            echo '<script type="text/javascript">top.after_closure_compiler_echbay("error");</script>';
            $this->base_model->alert($result['error'], 'error');
        }
        // die(__CLASS__ . ':' . __LINE__);

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
            echo '<script type="text/javascript">top.after_closure_compiler_echbay("error");</script>';
            $this->base_model->alert('No file specified.', 'error');
        }

        if (!is_file($file)) {
            echo '<script type="text/javascript">top.after_closure_compiler_echbay("error");</script>';
            $this->base_model->alert('File does not exist.', 'error');
        }

        // lấy nội dung file
        $file_content = file_get_contents($file);
        if ($file_content === false) {
            echo '<script type="text/javascript">top.after_closure_compiler_echbay("error");</script>';
            $this->base_model->alert('Failed to read file.', 'error');
        }

        // nếu đầu file có chứa chú thích của trình nén thì bỏ qua
        if (strpos($file_content, $this->minify_comment) !== false || strpos($file_content, $this->minify_local_comment) !== false) {
            echo '<script type="text/javascript">top.after_closure_compiler_echbay("error");</script>';
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
            echo '<script type="text/javascript">top.after_closure_compiler_echbay("error");</script>';
            $this->base_model->alert('Unsupported file type.', 'error');
        }
        // die($file_type);

        $url = str_replace(PUBLIC_PUBLIC_PATH, DYNAMIC_BASE_URL, $file);
        // die($url);

        // $minified = $this->minifyFromURL($url, $file_type);
        $minified = $this->minifyFile($file_content, $file_type);
        if ($minified !== false) {
            // thêm 1 số chú thích vào file đã nén
            $minified = "/* {$this->minify_comment} */\n" . $minified;
            // lưu file đã nén lại
            if (file_put_contents($file, $minified) !== false) {
                // die($minified);
                echo '<script type="text/javascript">top.after_closure_compiler_echbay("ok", "' . $url . '");</script>';
                $this->base_model->alert('Minification successful.');
            }
        } else {
            if ($file_type == 'css') {
                $c = $this->WGR_remove_css_multi_comment($file_content);
            } else if ($file_type == 'js') {
                $c = $this->WGR_update_core_remove_js_comment($file_content);
            } else {
                $c = false;
            }
            if ($c !== false) {
                $c = trim($c);
                if (!empty($c)) {
                    // thêm 1 số chú thích vào file đã nén
                    $minified = "/* {$this->minify_local_comment} */\n" . $minified;
                    // lưu file đã nén lại
                    if (file_put_contents($file, $minified) !== false) {
                        // die($minified);
                        echo '<script type="text/javascript">top.after_closure_compiler_echbay("ok", "' . $url . '");</script>';
                        $this->base_model->alert('Minification successful.', 'warning');
                    }
                }
            }
        }
        echo '<script type="text/javascript">top.after_closure_compiler_echbay("error");</script>';
        $this->base_model->alert('Minification failed.', 'error');
    }
}
