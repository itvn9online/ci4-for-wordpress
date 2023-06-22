<?php

namespace App\Controllers\Admin;

class Optimize extends Admin
{
    private $f_active_optimize = 'active-optimize.txt';
    private $c_active_optimize = 'Nếu tồn tại file này -> sẽ kích hoạt lệnh optimize file CSS hoặc JS trong thư mục tương ứng';
    protected $upload_via_ftp = NULL;
    protected $file_model = NULL;

    // lưu connect qua ftp -> đỡ phải connect nhiều
    public $conn_cache_id = NULL;
    public $conn_clear_id = false;
    public $base_cache_dir = NULL;

    //
    public function __construct()
    {
        parent::__construct();

        //
        $this->file_model = new \App\Models\File();
    }

    // sử dụng khi cần nén lại các file tĩnh bằng cách thủ công
    public function index()
    {
        $data = '';
        $time_start = time();
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
            $this->before_active_optimize(PUBLIC_PUBLIC_PATH . 'css/', $f, $c);
            $this->before_active_optimize(PUBLIC_PUBLIC_PATH . 'javascript/', $f, $c);
            $this->before_active_optimize(PUBLIC_PUBLIC_PATH . 'javascript/firebasejs/', $f, $c);
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
                $this->file_model->conn_cache_id = NULL;

                //
                echo '<strong>FTP close</strong>:<em>' . __CLASS__ . '</em>:' . __LINE__ . '<br>' . PHP_EOL;
            }
            $data = ob_get_contents();
            ob_end_clean();
        }

        //
        $this->teamplate_admin['content'] = view('admin/optimize_view', [
            'total_time' => time() - $time_start,
            'data' => $data,
        ]);
        return view('admin/admin_teamplate', $this->teamplate_admin);
    }

    protected function before_compress_css_js()
    {
        if ($this->base_model->scache(__FUNCTION__) !== NULL) {
            //die(__CLASS__ . ':' . __LINE__);
            return false;
        }

        // kiểm tra tiến trình push file qua fpt hay php
        if ($this->using_via_ftp() === true) {
            $this->connCacheId();
        }

        //
        $this->optimize_css_js();

        // close connect ftp sau khi xong việc
        if ($this->conn_clear_id === true) {
            ftp_close($this->conn_cache_id);
            $this->file_model->conn_cache_id = NULL;
        }

        //
        $this->base_model->scache(__FUNCTION__, time());
    }

    protected function before_active_optimize($dir, $f, $c)
    {
        if (!is_dir($dir)) {
            echo 'mkdir: ' . $dir . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;

            //
            if ($this->using_via_ftp() === true) {
                $this->file_model->create_dir($dir);
            } else {
                $this->mk_dir($dir, __CLASS__ . ':' . __LINE__);
            }
        }

        // chạy theo cách này để hạn chế connect ftp
        if ($this->using_via_ftp() === true) {
            if (!$this->file_model->ftp_my_chmod($dir, DEFAULT_FILE_PERMISSION)) {
                die(__CLASS__ . ':' . __LINE__);
            }
        }
        $this->push_content_file($dir . $f, $c, DEFAULT_FILE_PERMISSION);
    }

    protected function optimize_css_js()
    {
        // tính năng này không hoạt động trên localhost
        if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
            return false;
        }

        // css, js chung
        $this->optimize_action_css(PUBLIC_PUBLIC_PATH);
        $this->optimize_action_js(PUBLIC_PUBLIC_PATH, 'javascript');
        $this->optimize_action_js(PUBLIC_PUBLIC_PATH, 'javascript/firebasejs');

        // css, js của từng theme
        if ($this->optimize_action_css(THEMEPATH) === true) {
            // riêng với CSS thì còn thừa file style.css của theme -> sinh ra đoạn này để xử lý nó
            $filename = THEMEPATH . 'style.css';
            if (file_exists($filename)) {
                $c = $this->WGR_remove_css_multi_comment(file_get_contents($filename, 1));
                if ($c !== false) {
                    echo $filename . ':<em>' . __CLASS__ . '</em>:' . __LINE__ . '<br>' . PHP_EOL;
                    $c = trim($c);
                    if (!empty($c)) {
                        $this->push_content_file($filename, $c);
                    }
                }
            }
        }
        $this->optimize_action_js(THEMEPATH);

        //
        if ($this->optimize_action_js(THEMEPATH, 'page-templates') === true) {
            // tạo lại file xác nhận để css còn có cái mà dùng
            $this->before_active_optimize(THEMEPATH . 'page-templates/', $this->f_active_optimize, $this->c_active_optimize);

            //
            $this->optimize_action_css(THEMEPATH, 'page-templates');
        }

        //
        if (
            $this->optimize_action_js(THEMEPATH, 'term-templates') === true
        ) {
            // tạo lại file xác nhận để css còn có cái mà dùng
            $this->before_active_optimize(THEMEPATH . 'term-templates/', $this->f_active_optimize, $this->c_active_optimize);

            //
            $this->optimize_action_css(
                THEMEPATH,
                'term-templates'
            );
        }

        // optimize phần view -> optimize HTML
        $this->optimize_action_views(VIEWS_PATH);
        $this->optimize_action_views(VIEWS_CUSTOM_PATH);
    }

    private function optimize_action_views($path, $check_active = true)
    {
        $path = rtrim($path, '/');
        //echo $path . ':<em>' . __CLASS__ . '</em>:' . __LINE__ . '<br>' . PHP_EOL;
        if ($this->check_active_optimize($path . '/') !== true) {
            if ($check_active === true) {
                return false;
            }
        }
        echo '<strong>' . $path . '</strong>:<em>' . __CLASS__ . '</em>:' . __LINE__ . '<br>' . PHP_EOL;

        // optimize file php
        foreach (glob($path . '/*.php') as $filename) {
            echo $filename . ':<em>' . __CLASS__ . '</em>:' . __LINE__ . '<br>' . PHP_EOL;

            //
            $c = $this->WGR_update_core_remove_php_multi_comment($this->WGR_update_core_remove_php_comment(file_get_contents($filename, 1)));
            if ($c != '') {
                $c .= PHP_EOL;
                //$c .= ' ';
            }

            //
            $this->push_content_file($filename, $c);
        }

        // optimize file html
        foreach (glob($path . '/*.html') as $filename) {
            echo $filename . ':<em>' . __CLASS__ . '</em>:' . __LINE__ . '<br>' . PHP_EOL;

            //
            $c = $this->WGR_update_core_remove_html_comment(file_get_contents($filename, 1));

            //
            $this->push_content_file($filename, $c);
        }

        // optimize các thư mục con
        foreach (glob($path . '/*') as $filename) {
            if (is_dir($filename)) {
                $this->optimize_action_views($filename, false);
            }
        }
    }

    private function optimize_action_css($path, $dir = 'css', $type = 'css')
    {
        $path = $path . rtrim($dir, '/');
        //echo $path . ':<em>' . __CLASS__ . '</em>:' . __LINE__ . '<br>' . PHP_EOL;
        if ($this->check_active_optimize($path . '/') !== true) {
            return false;
        }
        echo '<strong>' . $path . '</strong>:<em>' . __CLASS__ . '</em>:' . __LINE__ . '<br>' . PHP_EOL;

        //
        foreach (glob($path . '/*.' . $type) as $filename) {
            $c = file_get_contents($filename, 1);
            // nếu file không có nội dung gì thì xóa luôn file đí -> tối ưu cho frontend đỡ phải nạp
            if (trim($c) == false) {
                $this->MY_unlink($filename);
                continue;
            }
            $c = $this->WGR_remove_css_multi_comment($c);
            //var_dump( $c );
            if ($c === false) {
                echo 'continue (' . basename($filename) . ') <br>' . PHP_EOL;
                continue;
            }
            echo $filename . ':<em>' . __CLASS__ . '</em>:' . __LINE__ . '<br>' . PHP_EOL;

            //
            $c = trim($c);
            if (!empty($c)) {
                $this->push_content_file($filename, $c);
            }
        }

        //
        return true;
    }

    private function optimize_action_js($path, $dir = 'js', $type = 'js')
    {
        $path = $path . rtrim($dir, '/');
        //echo $path . ':<em>' . __CLASS__ . '</em>:' . __LINE__ . '<br>' . PHP_EOL;
        if ($this->check_active_optimize($path . '/') !== true) {
            return false;
        }
        echo '<strong>' . $path . '</strong>:<em>' . __CLASS__ . '</em>:' . __LINE__ . '<br>' . PHP_EOL;

        //
        foreach (glob($path . '/*.' . $type) as $filename) {
            $c = file_get_contents($filename, 1);
            // nếu file không có nội dung gì thì xóa luôn file đí -> tối ưu cho frontend đỡ phải nạp
            if (trim($c) == false) {
                $this->MY_unlink($filename);
                continue;
            }
            $c = $this->WGR_update_core_remove_js_comment($c);
            if ($c === false) {
                echo 'continue (' . basename($filename) . ') <br>' . PHP_EOL;
                continue;
            }
            echo $filename . ':<em>' . __CLASS__ . '</em>:' . __LINE__ . '<br>' . PHP_EOL;

            //
            if (!empty($c)) {
                $this->push_content_file($filename, $c);
            }
        }

        //
        return true;
    }

    // kiểm tra xem có sự tồn tại của file kích hoạt chế độ optimize không
    private function check_active_optimize($path)
    {
        //echo '<strong>' . $path . '</strong>:<em>' . __CLASS__ . '</em>:' . __LINE__ . '<br>' . PHP_EOL;
        $full_path = $path . $this->f_active_optimize;
        //echo $full_path . ':<em>' . __CLASS__ . '</em>:' . __LINE__ . '<br>' . PHP_EOL;
        if (file_exists($full_path)) {
            // thử xóa file optimize -> XÓA được thì mới trả về true -> đảm bảo có quyền chỉnh sửa các file trong này
            if ($this->MY_unlink($full_path)) {
                return true;
            }
        }
        return false;
    }

    // optimize cho file css
    private function WGR_remove_css_multi_comment($a)
    {
        $a = explode('*/', $a);
        $str = '';
        foreach ($a as $v) {
            $v = explode('/*', $v);
            $str .= $v[0];
        }

        //
        $a = explode(PHP_EOL, $str);
        if (count($a) < 10) {
            return false;
        }
        //echo 'count a: ' . count( $a ) . '<br>' . PHP_EOL;
        $str = '';
        foreach ($a as $v) {
            $v = trim($v);
            if ($v != '') {
                $str .= $v;
            }
        }

        // bỏ các ký tự thừa nhiều nhất có thể
        $str = str_replace('; }', '}', $str);
        $str = str_replace(';}', '}', $str);
        $str = str_replace(' { ', '{', $str);
        $str = str_replace(' {', '{', $str);
        $str = str_replace(', .', ',.', $str);
        $str = str_replace(', #', ',#', $str);
        $str = str_replace(': ', ':', $str);
        $str = str_replace('} .', '}.', $str);
        $str = str_replace('{ }', '{}', $str);
        $str = str_replace('{ }', '{}', $str);

        // chuyển đổi tên màu sang mã màu
        $arr_colorname_to_code = [
            'transparent' => '00000000',
            'red' => 'ff0000',
            'darkred' => '8b0000',
            'black' => '000000',
            'white' => 'ffffff',
            'blue' => '0000ff',
            'darkblue' => '00008b',
            'green' => '008000',
            'darkgreen' => '006400',
            'orange' => 'ffa500',
            'darkorange' => 'ff8c00',
        ];
        foreach ($arr_colorname_to_code as $k => $v) {
            $str = str_replace(':' . $k . '}', ':#' . $v . '}', $str);
            $str = str_replace(':' . $k . ';', ':#' . $v . ';', $str);

            // !important
            $str = str_replace(':' . $k . ' !', ':#' . $v . ' !', $str);
            $str = str_replace(':' . $k . '!', ':#' . $v . '!', $str);
        }

        // loại bỏ các dòng css chưa có code
        $str = $this->remove_css_not_using($str);

        //
        return $str;
    }

    // loại bỏ các dòng css chưa có code
    protected function remove_css_not_using($str)
    {
        $str = str_replace('{  }', '{}', $str);
        $str = str_replace('{ }', '{}', $str);
        $str = explode('{}', $str);
        //print_r($str);
        foreach ($str as $k => $v) {
            // cắt chuỗi
            $v = explode('}', $v);
            //print_r($v);
            //$v = array_pop($v);
            $v[count($v) - 1] = '';
            //print_r($v);
            $v = implode('}', $v);
            //print_r($v);

            //
            $str[$k] = $v;
        }
        $str = implode('', $str);
        //die(__CLASS__ . ':' . __LINE__);

        //
        return $str;
    }

    private function WGR_update_core_remove_js_comment($a)
    {
        $a = $this->WGR_remove_js_comment($a);
        if ($a === false) {
            return false;
        }
        $a = $this->_eb_str_text_fix_js_content($a);
        //$a = $this->WGR_remove_js_multi_comment( $a );

        return trim($a);
    }

    private function WGR_remove_js_comment($a, $chim = false)
    {
        $a = explode(PHP_EOL, $a);
        if (count($a) < 10) {
            return false;
        }

        $str = '';
        foreach ($a as $v) {
            $v = trim($v);

            if ($v == '' || substr($v, 0, 2) == '//') {
            } else {
                // thêm dấu xuống dòng với 1 số trường hợp
                if ($chim == true || strpos($v, '//') !== false || substr($v, -1) == '\\') {
                    $v .= PHP_EOL;
                }
                $str .= $v;
            }
        }

        // loại bỏ khoảng trắng
        $arr = array(
            ' ( ' => '(',
            ' ) ' => ')',
            '( \'' => '(\'',
            '\' )' => '\')',

            '\' + ' => '\'+',
            ' + \'' => '+\'',

            ' == ' => '==',
            ' != ' => '!=',
            ' || ' => '||',
            ' === ' => '===',

            ' () ' => '()',
            ' && ' => '&&',
            '\' +\'' => '\'+\'',
            ' += ' => '+=',
            '+ \'' => '+\'',
            '; i < ' => ';i<',
            'var i = 0;' => 'var i=0;',
            '; i' => ';i',
            ' = \'' => '=\''
        );

        foreach ($arr as $k => $v) {
            $str = str_replace($k, $v, $str);
        }

        //
        return $str;
    }

    private function _eb_str_text_fix_js_content($str)
    {
        if ($str == '') {
            return '';
        }

        //	$str = iconv('UTF-16', 'UTF-8', $str);
        //	$str = mb_convert_encoding($str, 'UTF-8', 'UTF-16');
        //	$str = mysqli_escape_string($str);
        //	$str = htmlentities($str, ENT_COMPAT, 'UTF-16');
        $arr = $this->_eb_arr_block_fix_content();

        //
        foreach ($arr as $k => $v) {
            if ($v != '') {
                $str = str_replace($k, $v, $str);
            }
        }
        return $str;
    }

    private function WGR_remove_js_multi_comment($a, $begin = '/*', $end = '*/')
    {

        $str = $a;

        $b = explode($begin, $a);
        $a = explode($end, $a);

        // nếu số thẻ đóng với thẻ mở khác nhau -> hủy luôn
        if (count($a) != count($b)) {
            return $str;
            //		return _eb_str_block_fix_content( $str );
        }

        //
        $str = '';

        //
        foreach ($a as $v) {
            $v = explode($begin, $v);
            $str .= $v[0];
        }

        return $str;
        //	return _eb_str_block_fix_content( $str );
    }

    private function _eb_arr_block_fix_content()
    {
        // https://www.google.com/search?q=site:charbase.com+%E1%BB%9D#q=site:charbase.com+%E1%BA%A3
        return array(
            'á' => '\u00e1',
            'à' => '\u00e0',
            'ả' => '\u1ea3',
            'ã' => '\u00e3',
            'ạ' => '\u1ea1',
            'ă' => '\u0103',
            'ắ' => '\u1eaf',
            'ặ' => '\u1eb7',
            'ằ' => '\u1eb1',
            'ẳ' => '\u1eb3',
            'ẵ' => '\u1eb5',
            'â' => '\u00e2',
            'ấ' => '\u1ea5',
            'ầ' => '\u1ea7',
            'ẩ' => '\u1ea9',
            'ẫ' => '\u1eab',
            'ậ' => '\u1ead',
            'Á' => '\u00c1',
            'À' => '\u00c0',
            'Ả' => '\u1ea2',
            'Ã' => '\u00c3',
            'Ạ' => '\u1ea0',
            'Ă' => '\u0102',
            'Ắ' => '\u1eae',
            'Ặ' => '\u1eb6',
            'Ằ' => '\u1eb0',
            'Ẳ' => '\u1eb2',
            'Ẵ' => '\u1eb4',
            'Â' => '\u00c2',
            'Ấ' => '\u1ea4',
            'Ầ' => '\u1ea6',
            'Ẩ' => '\u1ea8',
            'Ẫ' => '\u1eaa',
            'Ậ' => '\u1eac',
            'đ' => '\u0111',
            'Đ' => '\u0110',
            'é' => '\u00e9',
            'è' => '\u00e8',
            'ẻ' => '\u1ebb',
            'ẽ' => '\u1ebd',
            'ẹ' => '\u1eb9',
            'ê' => '\u00ea',
            'ế' => '\u1ebf',
            'ề' => '\u1ec1',
            'ể' => '\u1ec3',
            'ễ' => '\u1ec5',
            'ệ' => '\u1ec7',
            'É' => '\u00c9',
            'È' => '\u00c8',
            'Ẻ' => '\u1eba',
            'Ẽ' => '\u1ebc',
            'Ẹ' => '\u1eb8',
            'Ê' => '\u00ca',
            'Ế' => '\u1ebe',
            'Ề' => '\u1ec0',
            'Ể' => '\u1ec2',
            'Ễ' => '\u1ec4',
            'Ệ' => '\u1ec6',
            'í' => '\u00ed',
            'ì' => '\u00ec',
            'ỉ' => '\u1ec9',
            'ĩ' => '\u0129',
            'ị' => '\u1ecb',
            'Í' => '\u00cd',
            'Ì' => '\u00cc',
            'Ỉ' => '\u1ec8',
            'Ĩ' => '\u0128',
            'Ị' => '\u1eca',
            'ó' => '\u00f3',
            'ò' => '\u00f2',
            'ỏ' => '\u1ecf',
            'õ' => '\u00f5',
            'ọ' => '\u1ecd',
            'ô' => '\u00f4',
            'ố' => '\u1ed1',
            'ồ' => '\u1ed3',
            'ổ' => '\u1ed5',
            'ỗ' => '\u1ed7',
            'ộ' => '\u1ed9',
            'ơ' => '\u01a1',
            'ớ' => '\u1edb',
            'ờ' => '\u1edd',
            'ở' => '\u1edf',
            'ỡ' => '\u1ee1',
            'ợ' => '\u1ee3',
            'Ó' => '\u00d3',
            'Ò' => '\u00d2',
            'Ỏ' => '\u1ece',
            'Õ' => '\u00d5',
            'Ọ' => '\u1ecc',
            'Ô' => '\u00d4',
            'Ố' => '\u1ed0',
            'Ồ' => '\u1ed2',
            'Ổ' => '\u1ed4',
            'Ỗ' => '\u1ed6',
            'Ộ' => '\u1ed8',
            'Ơ' => '\u01a0',
            'Ớ' => '\u1eda',
            'Ờ' => '\u1edc',
            'Ở' => '\u1ede',
            'Ỡ' => '\u1ee0',
            'Ợ' => '\u1ee2',
            'ú' => '\u00fa',
            'ù' => '\u00f9',
            'ủ' => '\u1ee7',
            'ũ' => '\u0169',
            'ụ' => '\u1ee5',
            'ư' => '\u01b0',
            'ứ' => '\u1ee9',
            'ừ' => '\u1eeb',
            'ử' => '\u1eed',
            'ữ' => '\u1eef',
            'ự' => '\u1ef1',
            'Ú' => '\u00da',
            'Ù' => '\u00d9',
            'Ủ' => '\u1ee6',
            'Ũ' => '\u0168',
            'Ụ' => '\u1ee4',
            'Ư' => '\u01af',
            'Ứ' => '\u1ee8',
            'Ừ' => '\u1eea',
            'Ử' => '\u1eec',
            'Ữ' => '\u1eee',
            'Ự' => '\u1ef0',
            'ý' => '\u00fd',
            'ỳ' => '\u1ef3',
            'ỷ' => '\u1ef7',
            'ỹ' => '\u1ef9',
            'ỵ' => '\u1ef5',
            'Ý' => '\u00dd',
            'Ỳ' => '\u1ef2',
            'Ỷ' => '\u1ef6',
            'Ỹ' => '\u1ef8',
            'Ỵ' => '\u1ef4'
        );
    }

    private function WGR_update_core_remove_html_comment($a)
    {
        $a = explode(PHP_EOL, $a);

        $str = '';
        foreach ($a as $v) {
            $v = trim($v);

            if ($v == '') {
                continue;
            }
            // loại bỏ các comment html đơn giản
            //echo substr( $v, 0, 4 ) . '<br>' . PHP_EOL;
            //echo substr( $v, -3 ) . '<br>' . PHP_EOL;
            if (substr($v, 0, 4) == '<!--' && substr($v, -3) == '-->') {
                continue;
            }

            $str .= $v . PHP_EOL;
            /*
            if ( strpos( $v, '//' ) !== false ) {
            $str .= PHP_EOL;
            } else {
            $str .= ' ';
            }
            */
        }

        //
        return trim($str);
        //	return trim( $str );
    }

    private function WGR_update_core_remove_php_comment($a)
    {
        $a = explode(PHP_EOL, $a);

        $str = '';
        foreach ($a as $v) {
            $v = trim($v);

            // loại bỏ các dòng comment đơn
            if ($v == '' || substr($v, 0, 2) == '//' || substr($v, 0, 2) == '# ') {
                continue;
            }

            // loại bỏ comment php nếu nó nằm trên 1 dòng
            //			if ( substr( $v, 0, 2 ) == '/*' && substr( $v, -2 ) == '*/' ) {
            //			}
            // trong code php có sẽ code html -> loại bỏ như html luôn
            if (substr($v, 0, 4) == '<!--' && substr($v, -3) == '-->') {
                continue;
            }

            //
            $str .= $v . ' ' . PHP_EOL;
            /*
            if ( strpos( $v, '//' ) !== false ) {
            $str .= PHP_EOL;
            } else {
            $str .= ' ';
            }
            */
        }
        // tránh dấu cách khi optimize file php
        $str = str_replace('?><?php', '?> <?php', $str);

        //	return trim( WGR_remove_js_multi_comment( $str ) );
        return trim($str);
    }

    private function WGR_update_core_remove_php_multi_comment($fileStr)
    {
        // https://stackoverflow.com/questions/503871/best-way-to-automatically-remove-comments-from-php-code
        $str = '';

        //
        $commentTokens = array(T_COMMENT);
        if (defined('T_DOC_COMMENT')) {
            $commentTokens[] = T_DOC_COMMENT; // PHP 5
        }
        if (defined('T_ML_COMMENT')) {
            $commentTokens[] = T_ML_COMMENT; // PHP 4
        }

        //
        $tokens = token_get_all($fileStr);

        //
        foreach ($tokens as $token) {
            if (is_array($token)) {
                if (in_array($token[0], $commentTokens)) {
                    continue;
                }

                $token = $token[1];
            }

            $str .= $token;
        }

        return trim($str);
    }

    // xóa các file cache cũ để dọn bớt file cho thư mục
    protected function cleanup_old_cache($time = MEDIUM_CACHE_TIMEOUT)
    {
        $current_time = time();

        //
        $last_run = $this->base_model->scache(__FUNCTION__);
        if ($last_run !== NULL) {
            echo __FUNCTION__ . ' RUN ' . ($current_time - $last_run) . 's ago ---`/ CLEAR cache for continue... ' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
            return false;
        }

        // phương pháp này chỉ áp dụng cho file cache
        if (MY_CACHE_HANDLER == 'file') {
            if ($time < MEDIUM_CACHE_TIMEOUT) {
                $time = MEDIUM_CACHE_TIMEOUT;
            }
            //echo $time . '<br>' . PHP_EOL;

            //
            foreach (glob(WRITE_CACHE_PATH . '*') as $filename) {
                //echo $filename . '<br>' . PHP_EOL;

                // xem file được tạo lâu rồi thì xóa nó đi
                if (is_file($filename) && $current_time - filemtime($filename) > $time) {
                    //echo $filename . ' (' . date( 'r', filemtime( $filename ) ) . ')' . '<br>' . PHP_EOL;

                    // không xóa được file thì break luôn -> file 0777 mà không xóa được 1 file thì các file khác cũng vậy
                    if (!$this->MY_unlink($filename)) {
                        break;
                    }
                }
            }
        }

        // và không cần xóa liên tục
        $this->base_model->scache(__FUNCTION__, $current_time, $time);
    }

    // kiểm tra xem có put file bằng php được hay phải dùng ftp
    protected function using_via_ftp($upload_check = '')
    {
        if ($this->upload_via_ftp !== NULL) {
            return $this->upload_via_ftp;
        }

        // thử kiểm tra quyền đọc ghi file trong thư mục app, nếu không ghi được -> sẽ sử dụng FTP để xử lý file
        if ($upload_check == '') {
            $upload_check = PUBLIC_HTML_PATH . 'app/';
        }
        $path = $upload_check . 'test_permission.txt';
        if (@file_put_contents($path, time())) {
            $this->upload_via_ftp = false;
            chmod($path, DEFAULT_FILE_PERMISSION);
            unlink($path);
        } else {
            $this->upload_via_ftp = true;
        }
        return $this->upload_via_ftp;
    }

    // tạo thư mục
    protected function mk_dir($dir, $msg, $permision = DEFAULT_DIR_PERMISSION)
    {
        mkdir($dir, $permision) or die('ERROR create dir (' . $msg . ')! ' . $dir);
        chmod($dir, $permision);
    }

    protected function connCacheId()
    {
        if ($this->conn_cache_id === NULL) {
            // nếu không xác định được server -> bỏ qua
            if ($this->file_model->get_server() === false) {
                $this->conn_cache_id = false;
            } else {
                //die($this->file_model->ftp_server);
                $this->conn_cache_id = ftp_connect($this->file_model->ftp_server);
                // đặt tham số close connect ftp sau khi xong việc
                $this->conn_clear_id = true;
                // gán cho connect này model
                $this->file_model->conn_cache_id = $this->conn_cache_id;
                // xác định FTP root
                if ($this->file_model->root_dir() === true) {
                    $this->base_cache_dir = $this->file_model->base_dir;
                    //echo 'base_cache_dir: ' . $this->base_cache_dir . '<br>' . PHP_EOL;
                }
            }
        }
        return $this->conn_cache_id;
    }

    protected function push_content_file($f, $c, $set_permission = 0644)
    {
        if ($this->using_via_ftp() === true) {
            if ($this->conn_cache_id === false) {
                echo '<strong>conn_cache_id is false</strong>:<em>' . __CLASS__ . '</em>:' . __LINE__ . '<br>' . PHP_EOL;
                return false;
            }

            //
            if ($this->base_cache_dir !== NULL) {
                $this->file_model->base_dir = $this->base_cache_dir;
            }

            // tạo file qua ftp -> còn liên quan đến chown
            return $this->file_model->create_file($f, $c);
        }
        return $this->base_model->eb_create_file($f, $c);
    }
}
