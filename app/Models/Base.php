<?php
/*
 * file này chủ yếu xử lý các vấn đề chung chung, chẳng biết gọi tên chính xác là gì -> hổ lốn
 */

namespace App\Models;

//
//use CodeIgniter\Model;

use __PHP_Incomplete_Class;
use App\Helpers\HtmlTemplate;

class Base extends Csdl
{
    public $lang_key = '';

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
        //echo $f . '<br>' . PHP_EOL;

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
        } else {
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
        //echo $f . '<br>' . PHP_EOL;
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
        die(HtmlTemplate::html(
            'wgr_alert.html',
            [
                'file' => basename($arr_debug[1]['file']),
                'line' => $arr_debug[1]['line'],
                'function' => $arr_debug[1]['function'],
                'class' => basename(str_replace('\\', '/', $arr_debug[1]['class'])),
                'm' => $m,
                'lnk' => $lnk,
            ]
        ));
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
        //$str = preg_replace('/-+-/', "-", $str);
        $str = str_replace('--', '-', $str);

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
        //echo PUBLIC_HTML_PATH . '<br>' . PHP_EOL;
        //echo APPPATH . '<br>' . PHP_EOL;
        //echo PUBLIC_HTML_PATH . APPPATH . '<br>' . PHP_EOL;

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
        } else if (isset($arr['term_meta']) && $arr['term_meta'] !== '') {
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

    // các file trong thư mục template sẽ không cho truy cập trực tiếp
    public function htaccess_custom_template($dir, $file_type = '', $type = '', $get_basename = false)
    {
        // bỏ dấu * nếu có
        $dir = rtrim($dir, '*');
        // thêm dấu / nếu chưa có
        $dir = rtrim($dir, '/') . '/';
        $file_type = ltrim($file_type, '*');
        if (!is_dir($dir)) {
            //die(__CLASS__ . ':' . __LINE__);
            return [];
        }

        // tạo file htaccess chặn truy cập nếu chưa có
        $f = $dir . '.htaccess';
        //echo $f . PHP_EOL;
        if (!file_exists($f)) {
            $this->_eb_create_file(
                $f,
                /*
                // chặn tất cả
                HtmlTemplate::html('htaccess_deny_all.txt', [
                    'created_from' => __CLASS__ . ':' . __LINE__,
                    'base_url' => DYNAMIC_BASE_URL,
                ]),
                */
                // chặn tất cả nhưng có mở 1 số định dạng file
                HtmlTemplate::html('htaccess_allow_deny.txt', [
                    'htaccess_allow' => HTACCESSS_ALLOW,
                    'created_from' => __CLASS__ . ':' . __LINE__,
                    'base_url' => DYNAMIC_BASE_URL,
                    'hotlink_protection' => '',
                ]),
                [
                    'set_permission' => 0644,
                    'ftp' => 1,
                ]
            );
        }

        // bỏ qua chế độ check thông số đầu vào
        return $this->EBE_get_file_in_folder($dir, $file_type, $type, $get_basename, false);
    }

    public function EBE_get_file_in_folder($dir, $file_type = '', $type = '', $get_basename = false, $check_params = true)
    {
        /*
         * chuẩn hóa đầu vào
         */
        if ($check_params === true) {
            // bỏ dấu * nếu có
            $dir = rtrim($dir, '*');
            $file_type = ltrim($file_type, '*');
            // thêm dấu / nếu chưa có
            $dir = rtrim($dir, '/') . '/';
            if (!is_dir($dir)) {
                //die(__CLASS__ . ':' . __LINE__);
                return [];
            }
        }
        //echo $dir . '*' . $file_type . '<br>' . PHP_EOL;

        // lấy danh sách file
        if ($file_type != '') {
            $arr = glob($dir . '*' . $file_type, GLOB_BRACE);
        } else {
            $arr = glob($dir . '*');
        }
        //print_r($arr);

        // chỉ lấy file
        if ($type == 'file') {
            $arr = array_filter($arr, 'is_file');
        }
        // chỉ lấy thư mục
        else if ($type == 'dir') {
            $arr = array_filter($arr, 'is_dir');
        }
        //print_r($arr);

        // chỉ lấy mỗi tên file hoặc thư mục
        if ($get_basename == true) {
            foreach ($arr as $k => $v) {
                $arr[$k] = basename($v);
            }
        }

        //
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

    /**
     * Tạo ra mã schema dựa theo dữ liệu đầu vào
     **/
    public function dynamicSchema($v)
    {
        return '<script type="application/ld+json">' . json_encode($v) . '</script>';
    }

    /**
     * Chạy vòng lặp tạo ra mã schema dựa theo dữ liệu đầu vào
     **/
    public function dynamicsSchema($datas)
    {
        $data = '';
        foreach ($datas as $v) {
            $data .= $this->dynamicSchema($v);
        }
        return $data;
    }

    /**
     * Tạo URL mạng xã hội cho phần sameAs
     **/
    public function sameAsSchema($datas)
    {
        $data = [];
        foreach ($datas as $v) {
            if (!empty($v) && $v != '#') {
                $data[] = $v;
            }
        }
        return $data;
    }

    public function term_seo($data, $url)
    {
        //print_r($data);
        $seo = array(
            'index' => 'on',
            'title' => $data['name'],
            'description' => ($data['description'] != '' ? trim(strip_tags($data['description'])) : $data['name']),
            'keyword' => '',
            'term_id' => $data['term_id'],
            'body_class' => 'taxonomy ' . $data['taxonomy'] . '-taxonomy',
            'updated_time' => strtotime($data['last_updated']),
            'shortlink' => DYNAMIC_BASE_URL . '?cat=' . $data['term_id'] . '&taxonomy=' . $data['taxonomy'],
            'url' => $url,
            'canonical' => $url,
        );

        //
        if (isset($data['term_meta'])) {
            if (isset($data['term_meta']['meta_title']) && $data['term_meta']['meta_title'] != '') {
                $seo['title'] = $data['term_meta']['meta_title'];
            }

            //
            if (isset($data['term_meta']['meta_description']) && $data['term_meta']['meta_description'] != '') {
                $seo['description'] = $data['term_meta']['meta_description'];
            }

            //
            if (isset($data['term_meta']['meta_keyword']) && $data['term_meta']['meta_keyword'] != '') {
                $seo['keyword'] = $data['term_meta']['meta_keyword'];
            }
        }
        //$seo['description'] = trim(strip_tags($seo['description']));
        //$seo['keyword'] = $seo['description'];
        //print_r( $seo );

        return $seo;
    }

    public function post_seo($data, $url)
    {
        //print_r($data);

        //
        $seo = array(
            'index' => 'on',
            'title' => $data['post_title'],
            //'description' => $data['post_title'],
            'description' => ($data['post_excerpt'] != '' ? trim(strip_tags($data['post_excerpt'])) : $data['post_title']),
            //'keyword' => $pageDetail[ 0 ][ 'keyword' ],
            'keyword' => '',
            //'name' => $pageDetail[ 0 ][ 'name' ],
            'post_id' => $data['ID'],
            'body_class' => 'post ' . $data['post_type'] . '-post',
            'updated_time' => strtotime($data['post_modified']),
            'shortlink' => DYNAMIC_BASE_URL . '?p=' . $data['ID'],
            'url' => $url,
            'canonical' => $url,
            //'og_image' => $data['post_meta']['image_medium_large'],
        );

        //
        if (isset($data['post_meta'])) {
            if (isset($data['post_meta']['meta_title']) && $data['post_meta']['meta_title'] != '') {
                $seo['title'] = $data['post_meta']['meta_title'];
            }

            //
            if (isset($data['post_meta']['meta_description']) && $data['post_meta']['meta_description'] != '') {
                $seo['description'] = $data['post_meta']['meta_description'];
            }

            //
            if (isset($data['post_meta']['meta_keyword']) && $data['post_meta']['meta_keyword'] != '') {
                $seo['keyword'] = $data['post_meta']['meta_keyword'];
            }
        }
        //$seo['description'] = trim(strip_tags($seo['description']));
        //$seo['keyword'] = $seo['description'];

        //
        if (isset($data['post_meta']['image_medium_large']) && $data['post_meta']['image_medium_large'] != '') {
            $seo['og_image'] =  $data['post_meta']['image_medium_large'];
            if (strpos($seo['og_image'], '//') === false) {
                $seo['og_image'] = base_url() . '/' . ltrim($seo['og_image'], '/');
            }
        }

        //
        //print_r($seo);
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
        //echo $count_str . PHP_EOL;
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

    // tạo file -> hàm cũ
    public function _eb_create_file($file_, $content_, $ops = [])
    {
        return $this->eb_create_file($file_, $content_, $ops);
    }
    // tạo file có hỗ trợ của ftp
    public function ftp_create_file($file_, $content_, $ops = [])
    {
        $ops['ftp'] = 1;
        return $this->eb_create_file($file_, $content_, $ops);
    }
    public function eb_create_file($file_, $content_, $ops = [])
    {
        if ($content_ == '') {
            echo 'ERROR put file: content is NULL<br>' . PHP_EOL;
            return false;
        }

        // các option mặc định nếu không có giá trị truyền vào
        foreach ([
            'add_line' => '',
            'set_permission' => DEFAULT_FILE_PERMISSION,
            'ftp' => 0,
        ] as $k => $v) {
            if (!isset($ops[$k])) {
                $ops[$k] = $v;
            }
        }

        // có file rồi
        if ($ops['add_line'] != '' && file_exists($file_)) {
            // -> chỉ append content
            if (@file_put_contents($file_, $content_, FILE_APPEND)) {
                return true;
            }
        } else if (@file_put_contents($file_, $content_, LOCK_EX)) {
            // tạo mới thì thêm đoạn chmod
            @chmod($file_, $ops['set_permission']);
            return true;
        }

        // -> đến được đây -> ko return được -> sử dụng ftp để tạo file
        if ($ops['ftp'] > 0) {
            $file_model = new \App\Models\File();
            return $file_model->create_file($file_, $content_, $ops);
        }
        return false;
    }

    // trả về số dạng chuỗi
    public function number_only($str = '', $re = '/[^0-9]+/')
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
        return $a;
    }
    // trả về số
    public function _eb_number_only($str = '', $re = '/[^0-9]+/')
    {
        $a = $this->number_only($str, $re);
        if (substr($str, 0, 1) == '-') {
            $a = 0 - $a;
        } else {
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
                $a = (int) $a[0] . '.' . $a[1];
            } else {
                $a = (int) $a[0];
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
            } else {
                $j++;
            }
        }
        // kết chuỗi
        return $result;
    }

    // đầu vào là 1 mảng có key -> đầu ra là mã javascript -> thay thế cho JSON.parse
    public function JSON_parse($var)
    {
        $str = [];
        // in dữ liệu đầu vào là array -> đầu ra là JSON
        foreach ($var as $k => $v) {
            $str[] = 'var ' . $k . '=' . json_encode($v) . ';';
        }
        echo '<script>' . implode(PHP_EOL, $str) . '</script>';
    }

    // đầu vào là 1 mảng có key -> đầu ra là mã javascript -> dùng cho trường hợp in thẳng JSON vào HTML
    public function JSON_echo($var, $arr = [])
    {
        $str = [];
        // đầu vào là array hoặc number -> đầu ra thì cứ echo thẳng
        foreach ($var as $k => $v) {
            $str[] = 'var ' . $k . '=' . $v . ';';
        }
        // in ra dạng string
        foreach ($arr as $k => $v) {
            $v = str_replace('/', '\/', $v);
            $v = str_replace('"', '\"', $v);

            //
            $str[] = 'var ' . $k . '="' . $v . '";';
        }
        echo '<script>' . implode(PHP_EOL, $str) . '</script>';
    }

    // kiểm tra xem 1 mảng có bị trống các dữ liệu bắt buộc hay không. Trống thì trả về true -> tương tự hàm empty của php
    public function isEmptyData($data, $required_data)
    {
        $is_empty = false;
        foreach ($required_data as $v) {
            if (!isset($data[$v]) || empty($data[$v])) {
                $is_empty = true;
                break;
            }
        }
        return $is_empty;
    }

    public function wp_check_invalid_utf8($text, $strip = false)
    {
        $text = (string) $text;

        if (0 === strlen($text)) {
            return '';
        }

        // Check for support for utf8 in the installed PCRE library once and store the result in a static.
        static $utf8_pcre = null;
        if (!isset($utf8_pcre)) {
            // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
            $utf8_pcre = @preg_match('/^./u', 'a');
        }
        // We can't demand utf8 in the PCRE installation, so just return the string in those cases.
        if (!$utf8_pcre) {
            return $text;
        }

        // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- preg_match fails when it encounters invalid UTF8 in $text.
        if (1 === @preg_match('/^./us', $text)) {
            return $text;
        }

        return '';
    }

    public function wp_specialchars($text, $quote_style = ENT_NOQUOTES, $charset = false, $double_encode = false)
    {
        $text = (string) $text;
        if (0 === strlen($text)) {
            return '';
        }

        // Don't bother if there are no specialchars - saves some processing.
        if (!preg_match('/[&<>"\']/', $text)) {
            return $text;
        }

        // Account for the previous behavior of the function when the $quote_style is not an accepted value.
        if (empty($quote_style)) {
            $quote_style = ENT_NOQUOTES;
        } elseif (ENT_XML1 === $quote_style) {
            $quote_style = ENT_QUOTES | ENT_XML1;
        } elseif (!in_array($quote_style, array(ENT_NOQUOTES, ENT_COMPAT, ENT_QUOTES, 'single', 'double'), true)) {
            $quote_style = ENT_QUOTES;
        }

        // Store the site charset as a static to avoid multiple calls to wp_load_alloptions().
        $charset = 'UTF-8';

        $_quote_style = $quote_style;

        if ('double' === $quote_style) {
            $quote_style  = ENT_COMPAT;
            $_quote_style = ENT_COMPAT;
        } elseif ('single' === $quote_style) {
            $quote_style = ENT_NOQUOTES;
        }

        $text = htmlspecialchars($text, $quote_style, $charset, $double_encode);

        // Back-compat.
        if ('single' === $_quote_style) {
            $text = str_replace("'", '&#039;', $text);
        }

        return $text;
    }

    public function wp_esc_html($text)
    {
        $safe_text = $this->wp_check_invalid_utf8($text);
        return $this->wp_specialchars($safe_text, ENT_QUOTES);
    }
    public function the_esc_html($text)
    {
        echo $this->wp_esc_html($text);
    }

    public function result_json_type($arr, $headers = [], $too_headers = [])
    {
        // reset lại view -> tránh in ra phần html nếu lỡ nạp
        ob_end_clean();

        //
        header('Content-type: application/json; charset=utf-8');
        // header mặc định, ghi đè header trước đó
        foreach ($headers as $v) {
            header($v);
        }
        // header không ghi đè -> 2 header trùng tên nhưng khác giá trị
        foreach ($too_headers as $v) {
            header($v, false);
        }
        die(json_encode($arr));
    }
}
