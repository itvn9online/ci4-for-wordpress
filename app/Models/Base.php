<?php
/*
 * file này chủ yếu xử lý các vấn đề chung chung, chẳng biết gọi tên chính xác là gì -> hổ lốn
 */
namespace App\Models;

//
//use CodeIgniter\ Model;
use App\Helpers\HtmlTemplate;

class Base extends Csdl
{
    public function __construct()
    {
        parent::__construct();
    }

    // nạp CSS, JS để tránh phải bấm Ctrl + F5
    public function get_add_css($f, $ops = [], $attr = [])
    {
        //print_r( $ops );
        $f = str_replace(PUBLIC_PUBLIC_PATH, '', $f);
        $f = ltrim($f, '/');
        //echo $f . '<br>' . "\n";

        if (!file_exists(PUBLIC_PUBLIC_PATH . $f)) {
            return '<!-- ' . $f . ' not exist! -->';
        }

        //
        if (isset($ops['get_content'])) {
            return '<style>' . file_get_contents($f, 1) . '</style>' . PHP_EOL;
        }

        // xem có chạy qua CDN không -> có thì nó sẽ giảm tải cho server
        if (!isset($ops['cdn'])) {
            $ops['cdn'] = '';
        }

        //
        if (isset($ops['preload'])) {
            $rel = 'rel="preload" as="style" onload="this.onload=null;this.rel=\'stylesheet\'"';
        }
        else {
            $rel = 'rel="stylesheet" type="text/css" media="all"';
        }
        return '<link ' . $rel . ' href="' . $ops['cdn'] . $f . '?v=' . filemtime(PUBLIC_PUBLIC_PATH . $f) . '"' . implode(' ', $attr) . ' />';
    }
    // chế độ nạp css thông thường
    public function add_css($f, $ops = [], $attr = [])
    {
        echo $this->get_add_css($f, $ops, $attr) . PHP_EOL;
    }
    // thêm nhiều file cùng 1 thuộc tính
    public function adds_css($fs, $ops = [], $attr = [])
    {
        foreach ($fs as $f) {
            echo $this->get_add_css($f, $ops, $attr) . PHP_EOL;
        }
    }
    // chế độ nạp trước css
    public function preload_css($f, $ops = [])
    {
        $ops['preload'] = 1;

        //
        echo $this->get_add_css($f, $ops) . PHP_EOL;
    }

    public function preloads_css($fs, $ops = [])
    {
        $ops['preload'] = 1;

        //
        foreach ($fs as $f) {
            echo $this->get_add_css($f, $ops) . PHP_EOL;
        }
    }

    public function get_add_js($f, $ops = [], $attr = [])
    {
        //print_r( $ops );
        $f = str_replace(PUBLIC_PUBLIC_PATH, '', $f);
        $f = ltrim($f, '/');
        //echo $f . '<br>' . "\n";
        if (!file_exists(PUBLIC_PUBLIC_PATH . $f)) {
            return '<!-- ' . $f . ' not exist! -->';
        }

        //
        if (isset($ops['get_content'])) {
            return '<script>' . file_get_contents($f, 1) . '</script>';
        }

        // xem có chạy qua CDN không -> có thì nó sẽ giảm tải cho server
        if (!isset($ops['cdn'])) {
            $ops['cdn'] = '';
        }

        //
        if (isset($ops['preload'])) {
            return '<link rel="preload" as="script" href="' . $ops['cdn'] . $f . '?v=' . filemtime(PUBLIC_PUBLIC_PATH . $f) . '">';
        }
        //print_r( $attr );
        return '<script src="' . $ops['cdn'] . $f . '?v=' . filemtime(PUBLIC_PUBLIC_PATH . $f) . '" ' . implode(' ', $attr) . '></script>';
    }
    // thêm 1 file
    public function add_js($f, $ops = [], $attr = [])
    {
        echo $this->get_add_js($f, $ops, $attr) . PHP_EOL;
    }
    // thêm nhiều file cùng 1 thuộc tính
    public function adds_js($fs, $ops = [], $attr = [])
    {
        foreach ($fs as $f) {
            echo $this->get_add_js($f, $ops, $attr) . PHP_EOL;
        }
    }
    // chế độ nạp trước css
    public function preload_js($f, $ops = [])
    {
        $ops['preload'] = 1;

        //
        echo $this->get_add_js($f, $ops) . PHP_EOL;
    }

    public function preloads_js($fs, $ops = [])
    {
        $ops['preload'] = 1;

        //
        foreach ($fs as $f) {
            echo $this->get_add_js($f, $ops) . PHP_EOL;
        }
    }

    // -> trả về alert của javascript
    public function alert($m, $lnk = '')
    {
        $arr_debug = debug_backtrace();
        //print_r($arr_debug);

        //
        die(HtmlTemplate::html('wgr_alert.html', [
            'file' => basename($arr_debug[1]['file']),
            'line' => $arr_debug[1]['line'],
            'function' => $arr_debug[1]['function'],
            'class' => basename(str_replace('\\', '/', $arr_debug[1]['class'])),
            'm' => $m,
            'lnk' => $lnk,
        ]));
    }

    //
    public function _eb_non_mark_seo_v2($str)
    {
        // Chuyển đổi toàn bộ chuỗi sang chữ thường
        if (function_exists('mb_convert_case')) {
            $str = mb_convert_case(trim($str), MB_CASE_LOWER, "UTF-8");
        }

        //Tạo mảng chứa key và chuỗi regex cần so sánh
        $unicode = array(
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            '-' => '\+|\*|\/|\&|\!| |\^|\%|\$|\#|\@'
        );

        foreach ($unicode as $key => $value) {
            //So sánh và thay thế bằng hàm preg_replace
            $str = preg_replace("/($value)/", $key, $str);
        }
        $str = ltrim($str, '-');
        $str = rtrim($str, '-');
        $str = ltrim($str, '.');
        $str = rtrim($str, '.');
        $str = trim($str);

        //Trả về kết quả
        return $str;
    }

    public function _eb_non_mark_seo_v1($str)
    {
        $str = $this->_eb_non_mark(trim($str));

        //
        $unicode = array(
            /*
     'a' => array('á','à','ả','ã','ạ','ă','ắ','ặ','ằ','ẳ','ẵ','â','ấ','ầ','ẩ','ẫ','ậ','Á','À','Ả','Ã','Ạ','Ă','Ắ','Ặ','Ằ','Ẳ','Ẵ','Â','Ấ','Ầ','Ẩ','Ẫ','Ậ'),
     'd' => array('đ','Đ'),
     'e' => array('é','è','ẻ','ẽ','ẹ','ê','ế','ề','ể','ễ','ệ','É','È','Ẻ','Ẽ','Ẹ','Ê','Ế','Ề','Ể','Ễ','Ệ'),
     'i' => array('í','ì','ỉ','ĩ','ị', 'Í','Ì','Ỉ','Ĩ','Ị'),
     'o' => array('ó','ò','ỏ','õ','ọ','ô','ố','ồ','ổ','ỗ','ộ','ơ','ớ','ờ','ở','ỡ','ợ','Ó','Ò','Ỏ','Õ','Ọ','Ô','Ố','Ồ','Ổ','Ỗ','Ộ','Ơ','Ớ','Ờ','Ở','Ỡ','Ợ'),
     'u' => array('ú','ù','ủ','ũ','ụ','ư','ứ','ừ','ử','ữ','ự','Ú','Ù','Ủ','Ũ','Ụ','Ư','Ứ','Ừ','Ử','Ữ','Ự'),
     'y' => array('ý','ỳ','ỷ','ỹ','ỵ','Ý','Ỳ','Ỷ','Ỹ','Ỵ'),
     */
            '-' => array(' ', '~', '`', '!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '=', '[', ']', '{', '}', '\\', '|', ';', ':', '\'', '"', ',', '<', '>', '/', '?')
        );
        foreach ($unicode as $nonUnicode => $uni) {
            foreach ($uni as $v) {
                $str = str_replace($v, $nonUnicode, $str);
            }
        }

        //
        return $str;
    }

    public function _eb_non_mark_seo($str)
    {
        //$str = _eb_non_mark_seo_v1( $str );
        $str = $this->_eb_non_mark_seo_v2($str);


        //	$str = urlencode($str);
        // thay thế 2- thành 1-  
        $str = preg_replace('/-+-/', "-", $str);

        // cắt bỏ ký tự - ở đầu và cuối chuỗi
        $str = preg_replace('/^\-+|\-+$/', "", $str);

        //
        $str = $this->_eb_text_only($str);

        //
        return $str;
    //	return strtolower($str);
    }

    public function _eb_non_mark($str)
    {
        $unicode = array(
            'a' => array('á', 'à', 'ả', 'ã', 'ạ', 'ă', 'ắ', 'ặ', 'ằ', 'ẳ', 'ẵ', 'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ'),
            'A' => array('Á', 'À', 'Ả', 'Ã', 'Ạ', 'Ă', 'Ắ', 'Ặ', 'Ằ', 'Ẳ', 'Ẵ', 'Â', 'Ấ', 'Ầ', 'Ẩ', 'Ẫ', 'Ậ'),
            'd' => array('đ'),
            'D' => array('Đ'),
            'e' => array('é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ế', 'ề', 'ể', 'ễ', 'ệ'),
            'E' => array('É', 'È', 'Ẻ', 'Ẽ', 'Ẹ', 'Ê', 'Ế', 'Ề', 'Ể', 'Ễ', 'Ệ'),
            'i' => array('í', 'ì', 'ỉ', 'ĩ', 'ị'),
            'I' => array('Í', 'Ì', 'Ỉ', 'Ĩ', 'Ị'),
            'o' => array('ó', 'ò', 'ỏ', 'õ', 'ọ', 'ô', 'ố', 'ồ', 'ổ', 'ỗ', 'ộ', 'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ'),
            'O' => array('Ó', 'Ò', 'Ỏ', 'Õ', 'Ọ', 'Ô', 'Ố', 'Ồ', 'Ổ', 'Ỗ', 'Ộ', 'Ơ', 'Ớ', 'Ờ', 'Ở', 'Ỡ', 'Ợ'),
            'u' => array('ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'ứ', 'ừ', 'ử', 'ữ', 'ự'),
            'U' => array('Ú', 'Ù', 'Ủ', 'Ũ', 'Ụ', 'Ư', 'Ứ', 'Ừ', 'Ử', 'Ữ', 'Ự'),
            'y' => array('ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ'),
            'Y' => array('Ý', 'Ỳ', 'Ỷ', 'Ỹ', 'Ỵ')
        );
        foreach ($unicode as $nonUnicode => $uni) {
            foreach ($uni as $v) {
                $str = str_replace($v, $nonUnicode, $str);
            }
        }
        return $str;
    }

    public function _eb_text_only($str = '')
    {
        if ($str == '') {
            return '';
        }
        return preg_replace('/[^a-zA-Z0-9\-\.]+/', '', $str);
    }

    // trả về nội dung HTML mẫu
    public function get_html_tmp($file_name, $path = '', $sub_path = 'html/', $file_type = '.html')
    {
        //echo PUBLIC_HTML_PATH . '<br>' . "\n";
        //echo APPPATH . '<br>' . "\n";
        //echo PUBLIC_HTML_PATH . APPPATH . '<br>' . "\n";

        // nếu path được chỉ định -> dùng path
        if ($path != '') {
            $f = $path . $sub_path . $file_name . $file_type;
        }
        // nếu không
        else {
            // ưu tiên file trong child-theme
            $f = VIEWS_CUSTOM_PATH . $sub_path . $file_name . $file_type;
            // nếu không có -> dùng trong theme mặc định
            if (!file_exists($f)) {
                $f = VIEWS_PATH . $sub_path . $file_name . $file_type;
            }
            // file mặc định bắt buộc phải có -> return ở đây mục đích là để tiết kiệm 1 pha if else phía sau =))
            return file_get_contents($f, 1);
        }
        if (!file_exists($f)) {
            return 'File HTML tmp not exist #' . $file_name . $file_type;
        }
        return file_get_contents($f, 1);
    }
    // trả về mẫu HTML ở theme cha
    public function parent_html_tmp($file_name)
    {
        return $this->get_html_tmp($file_name, VIEWS_PATH);
    }
    // trả về mẫu HTML ở theme con
    public function custom_html_tmp($file_name)
    {
        return $this->get_html_tmp($file_name, VIEWS_CUSTOM_PATH);
    }

    // chuyển đổi từ mảng php sang html tương ứng
    public function tmp_to_html($tmp_html, $arr, $default_arr = [])
    {
        // meta
        $arr_meta = NULL;
        if (isset($arr['post_meta']) && $arr['post_meta'] !== '') {
            $arr_meta = $arr['post_meta'];
            $arr['post_meta'] = '';
        }
        else if (isset($arr['term_meta']) && $arr['term_meta'] !== '') {
            $arr_meta = $arr['term_meta'];
            $arr['term_meta'] = '';
        }

        //print_r( $arr );
        $tmp_html = HtmlTemplate::render($tmp_html, $arr);

        // meta
        if ($arr_meta !== NULL) {
            $tmp_html = HtmlTemplate::render($tmp_html, $arr_meta);
        }

        // thay các dữ liệu không có key bằng dữ liệu mặc định
        $tmp_html = HtmlTemplate::render($tmp_html, $default_arr);

        //
        return $tmp_html;
    }

    public function EBE_get_file_in_folder($dir, $file_type = '', $type = '', $get_basename = false)
    {
        /*
         * chuẩn hóa đầu vào
         */
        // bỏ dấu * nếu có
        $dir = rtrim($dir, '*');
        $file_type = ltrim($file_type, '*');
        // thêm dấu / nếu chưa có
        $dir = rtrim($dir, '/') . '/';
        //echo $dir . '*' . $file_type . '<br>' . "\n";

        // lấy danh sách file
        if ($file_type != '') {
            $arr = glob($dir . '*' . $file_type, GLOB_BRACE);
        }
        else {
            $arr = glob($dir . '*');
        }
        //print_r( $arr );

        // chỉ lấy file
        if ($type == 'file') {
            $arr = array_filter($arr, 'is_file');
        }
        // chỉ lấy thư mục
        else if ($type == 'dir') {
            $arr = array_filter($arr, 'is_dir');
        }

        //	print_r($arr);
        //	exit();

        // chỉ lấy mỗi tên file hoặc thư mục
        if ($get_basename == true) {
            foreach ($arr as $k => $v) {
                $arr[$k] = basename($v);
            }
        }

        return $arr;
    }

    public function default_seo($name, $uri = '', $params = [])
    {
        $result = array(
            'index' => 'off',
            'title' => $name,
            'description' => $name,
            'keyword' => $name,
            'name' => $name,
            'body_class' => str_replace('/', '-', $uri),
            'canonical' => '',
            //'canonical' => base_url( '/' . $uri ),
            'shortlink' => '',
            'updated_time' => strtotime(date('Y-m-d')),
        );
        // thay thế dữ liệu riêng nếu có
        foreach ($params as $k => $v) {
            $result[$k] = $v;
        }
        return $result;
    }

    public function term_seo($data, $url)
    {
        //print_r( $data );
        $seo = array(
            'index' => 'on',
            'title' => $data['name'],
            'description' => $data['description'] != '' ? $data['description'] : $data['name'],
            'term_id' => $data['term_id'],
            'body_class' => 'taxonomy ' . $data['taxonomy'] . '-taxonomy',
            'updated_time' => strtotime($data['last_updated']),
            'shortlink' => DYNAMIC_BASE_URL . '?cat=' . $data['term_id'] . '&taxonomy=' . $data['taxonomy'],
            'url' => $url,
            'canonical' => $url,
        );
        $seo['description'] = trim(strip_tags($seo['description']));
        $seo['keyword'] = $seo['description'];
        //print_r( $seo );

        return $seo;
    }

    public function post_seo($data, $url)
    {
        //print_r( $data );
        $seo = array(
            'index' => 'on',
            'title' => $data['post_title'],
            'description' => $data['post_title'],
            //'keyword' => $pageDetail[ 0 ][ 'keyword' ],
            //'name' => $pageDetail[ 0 ][ 'name' ],
            'post_id' => $data['ID'],
            'body_class' => 'post ' . $data['post_type'] . '-post',
            'updated_time' => strtotime($data['post_modified']),
            'shortlink' => DYNAMIC_BASE_URL . '?p=' . $data['ID'],
            'url' => $url,
            'canonical' => $url,
        );

        //
        if (isset($data['post_meta']['meta_description']) && $data['post_meta']['meta_description'] != '') {
            $seo['description'] = $data['post_meta']['meta_description'];
        }
        else if ($data['post_excerpt'] != '') {
            $seo['description'] = strip_tags($data['post_excerpt']);
        }
        $seo['description'] = trim(strip_tags($seo['description']));
        $seo['keyword'] = $seo['description'];
        //print_r( $seo );

        return $seo;
    }

    // cắt chuỗi ngắn lại (phải làm phức tạp chút vì cắt tiếng Việt có dấu sẽ bị lỗi nếu cắt đúng chỗ có dấu)
    public function short_string($str, $len, $add_more = true)
    {
        // sử dụng function mặc định xem như nào, nếu sau lỗi thì bỏ
        return mb_strimwidth($str, 0, $len, $add_more == true ? '...' : '', 'utf-8');
    }

    public function short_string_v1($str, $len, $add_more = true)
    {
        if (strlen($str) < $len) {
            return $str;
        }

        // cắt chuỗi
        $str = substr($str, 0, $len);
        // bỏ mảng cuối cùng
        $str = explode(' ', $str);
        $count_str = count($str);
        //echo $count_str . "\n";
        //print_r($str);
        unset($str[$count_str - 1]);
        if ($add_more == true) {
            unset($str[$count_str - 2]);
        }
        //print_r($str);

        // nối lại
        $str = implode(' ', $str);

        // trả về
        if ($add_more == true) {
            return trim($str) . '...';
        }
        return trim($str);
    }

    public function get_config($config, $key, $default_value = '')
    {
        //print_r( $config );
        //if ( isset( $config->$key ) ) {
        if ($config->$key != '') {
            return $config->$key;
        }
        return $default_value;
    }

    public function the_config($config, $key, $default_value = '')
    {
        echo $this->get_config($config, $key, $default_value);
    }

    public function EBE_pagination($Page, $TotalPage, $strLinkPager, $sub_part = '/page/')
    {
        return '<div data-page="' . $Page . '" data-total="' . $TotalPage . '" data-url="' . $strLinkPager . '" data-params="' . $sub_part . '" class="each-to-page-part"></div>';
    }

    // tạo file
    public function _eb_create_file($file_, $content_, $ops = [])
    {
        if ($content_ == '') {
            echo 'ERROR put file: content is NULL<br>' . PHP_EOL;
            return false;
        }

        //
        if (!isset($ops['add_line'])) {
            $ops['add_line'] = '';
        }

        //
        if (!isset($ops['set_permission'])) {
            $ops['set_permission'] = DEFAULT_FILE_PERMISSION;
        }

        //
        if (!isset($ops['ftp'])) {
            $ops['ftp'] = 0;
        }

        //
        if (!file_exists($file_)) {
            $filew = @fopen($file_, 'x+');
            if (!$filew) {
                // thử tạo bằng ftp
                if ($ops['ftp'] === 1) {
                    $file_model = new \App\Models\File();
                    return $file_model->create_file($file_, $content_, $ops);
                }
            }
            else {
                // nhớ set 777 cho file
                chmod($file_, $ops['set_permission']);
            }
            fclose($filew);
        }

        //
        if ($ops['add_line'] != '') {
            if (@!file_put_contents($file_, $content_, FILE_APPEND, LOCK_EX)) {
                $file_model = new \App\Models\File();
                return $file_model->create_file($file_, $content_, $ops);
            }
        }
        //
        else {
            if (@!file_put_contents($file_, $content_)) {
                $file_model = new \App\Models\File();
                return $file_model->create_file($file_, $content_, $ops);
            }
        }

        //
        return true;
    }

    public function _eb_number_only($str = '', $re = '/[^0-9]+/')
    {
        $str = trim($str);
        if ($str == '') {
            return 0;
        }
        //	echo $str . ' str number<br>';
        $a = preg_replace($re, '', $str);
        //	echo $a . ' a number<br>';
        if ($a == '') {
            $a = 0;
        }
        else if (substr($str, 0, 1) == '-') {
            $a = 0 - $a;
        }
        else {
            $a *= 1;
        }
        return $a;
    }
    public function _eb_float_only($str = '', $lam_tron = 0)
    {
        $str = trim($str);
        //	echo $str . ' str float<br>';
        $a = $this->_eb_number_only($str, '/[^0-9|\.]+/');
        //	echo $a . ' a float<br>';

        // làm tròn hết sang số nguyên
        if ($lam_tron == 1) {
            $a = ceil($a);
        }
        // làm tròn phần số nguyên, số thập phân giữ nguyên
        else if ($lam_tron == 2) {
            $a = explode('.', $a);
            if (isset($a[1])) {
                $a = (int)$a[0] . '.' . $a[1];
            }
            else {
                $a = (int)$a[0];
            }
        }

        return $a;
    }
    public function un_money_format($str)
    {
        return $this->_eb_number_only($str);
    }
    public function unmoney_format($str)
    {
        return $this->_eb_number_only($str);
    }
    public function number_only($str)
    {
        return $this->_eb_number_only($str);
    }
    public function text_only($str = '')
    {
        return $this->_eb_text_only($str);
    }

    // lưu hoặc lấy session báo lỗi
    public function msg_error_session($value = NULL, $alert = false)
    {
        // alert = error || warning
        if ($alert !== false) {
            $this->alert($value, $alert);
        }
        return $this->MY_session('msg_error', $value);
    }
    // lưu hoặc lấy session thông báo
    public function msg_session($value = NULL, $alert = false)
    {
        // alert = error || warning
        if ($alert !== false) {
            $this->alert($value, $alert);
        }
        return $this->MY_session('msg', $value);
    }

    // trả về 1 chuỗi ngẫu nhiên với số từ được chỉ định
    public function random_string($length = 5)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    // mã hóa 1 chuỗi
    public function wgr_encode($str, $streng = 5)
    {
        $len = strlen($str);
        $result = '';
        for ($i = 0; $i < $len; $i++) {
            $result .= $this->random_string($streng);
            $result .= substr($str, $i, 1);
        }
        // kết chuỗi
        $result .= $this->random_string($streng - 1);
        return $result;
    }

    // giải mã 1 chuỗi
    public function wgr_decode($str, $streng = 5)
    {
        if (empty($str)) {
            return '';
        }

        //
        $len = strlen($str);
        $result = '';
        $j = 0;
        for ($i = 0; $i < $len; $i++) {
            if ($j > 0 && $j % $streng === 0) {
                $result .= substr($str, $i, 1);
                $j = 0;
            }
            else {
                $j++;
            }
        }
        // kết chuỗi
        return $result;
    }
}