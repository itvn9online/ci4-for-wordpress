<?php

namespace App\Controllers\Admin;

//
use App\Controllers\Ajaxs;

// Libraries
use App\Libraries\LanguageCost;
use App\Libraries\AdminMenu;
//use App\Libraries\CommentType;
use App\Libraries\UsersType;

//
class Admin extends Ajaxs
{
    //public $user_group_list = array();

    // với 1 số controller, sẽ không nạp cái HTML header vào, nên có thêm tham số này để không nạp header nữa
    public $preload_admin_header = true;

    protected $body_class = '';

    public function __construct()
    {
        parent::__construct();

        //
        if ($this->current_user_id <= 0) {
            // tạo url sau khi đăng nhập xong sẽ trỏ tới
            $login_redirect = DYNAMIC_BASE_URL . ltrim($_SERVER['REQUEST_URI'], '/');
            //die($login_redirect);

            //
            $login_url = base_url('guest/login') . '?login_redirect=' . urlencode($login_redirect) . '&msg=' . urlencode('Permission deny! ' . basename(__FILE__, '.php') . ':' . __LINE__) . '&remove_parameter=';
            //die( $login_url );

            //
            die(header('Location: ' . $login_url));
            //die( 'Permission deny! ' . basename( __FILE__, '.php' ) . ':' . __LINE__ );
        }
        //print_r( $this->session_data );
        //var_dump( $this->session_data );

        // nếu không có quyền admin -> báo lỗi nếu đang vào admin
        if ($this->session_data['userLevel'] != UsersType::ADMIN_LEVEL) {
            die('404 error line ' . __CLASS__ . ':' . __LINE__);
        }

        //
        //$response = \Config\Services::response();
        //$this->response->setHeader('Cache-Control', 'no-cache');
        //$response->removeHeader('Content-Security-Policy');

        //
        $this->post_model = new \App\Models\PostAdmin();
        $this->comment_model = new \App\Models\Comment();

        //
        if ($this->preload_admin_header === true && $_SERVER['REQUEST_METHOD'] == 'GET') {
            $this->teamplate_admin = [
                'is_admin' => UsersType::ADMIN,
                'html_lang' => $this->lang_key,
                'arr_lang_list' => LanguageCost::typeList(),
                'arr_admin_menu' => $this->admin_menu(),
                'session_data' => $this->session_data,
                'body_class' => $this->body_class,
                // các biến mà view con cần sử dụng thì cho vào view trung gian này
                'header' => view(
                    'admin/header_view',
                    array(
                        //'admin_root_views' => VIEWS_PATH . 'admin/',
                        //'admin_default_views' => VIEWS_PATH . 'admin/default/',
                        'base_model' => $this->base_model,
                        //'menu_model' => $this->menu_model,
                        //'option_model' => $this->option_model,
                        'post_model' => $this->post_model,
                        'term_model' => $this->term_model,
                        'lang_model' => $this->lang_model,
                        'num_model' => $this->num_model,
                        //
                        'debug_enable' => $this->debug_enable,
                        'session_data' => $this->session_data,
                        'current_user_id' => $this->current_user_id,
                        'getconfig' => $this->getconfig,
                    )
                ),
            ];
        } else {
            $this->teamplate_admin = [];
        }
    }

    public function index()
    {
        //echo debug_backtrace()[ 1 ][ 'class' ] . ':' . debug_backtrace()[ 1 ][ 'function' ] . '<br>' . PHP_EOL;
        echo 'Controller index not found! <br>' . PHP_EOL;
        die(__CLASS__ . ':' . __LINE__);
    }

    // chức năng này sẽ kiểm tra quyền truy cập 1 module nào đó theo từng tài khoản -> truyền vào controller class -> role -> xác định theo role
    protected function check_permision($role)
    {
        // role này chính là tên controller của admin -> kiểm tra xem có file này không
        //echo $role . '<br>' . PHP_EOL;
        $role = $this->get_class_name($role);
        //echo $role . '<br>' . PHP_EOL;

        //
        $this->body_class = strtolower($role);

        //
        $session_data = $this->session_data;
        // TEST
        //$session_data[ 'member_type' ] = UsersType::AUTHOR;
        //print_r( $session_data );

        // khâu kiểm tra quyền không cần đối với tài khoản admin
        if ($session_data['member_type'] == UsersType::ADMIN) {
            return true;
        }

        //
        $check_file = __DIR__ . '/' . $role . '.php';
        //echo $check_file . '<br>' . PHP_EOL;
        // nếu không tồn tại -> báo lỗi luôn
        if (!file_exists($check_file)) {
            die('Role not found!');
        }

        // chuyển role về chữ thường
        $role = $this->body_class;
        //echo $role . '<br>' . PHP_EOL;
        if (!in_array($role, UsersType::role($session_data['member_type']))) {
            die('Permission ERROR!');
        }

        //
        return true;
    }

    /*
    function checkslug() {
    $slug = $this->MY_post( 'slug' );
    $checkslug = $this->base_model->checkslug( $slug );
    if ( $checkslug == 'true' ) {
    echo 'false';
    } else {
    echo 'true';
    }
    }
    */

    private function admin_menu()
    {
        $arr = AdminMenu::menu_list();
        //print_r($arr);
        //echo count($arr) . PHP_EOL;

        // tạo số thứ tự để sắp xếp menu
        $j = 100;
        $j_ = ceil(100 / count($arr));
        //echo $j_ . PHP_EOL;
        foreach ($arr as $k => $v) {
            $arr[$k]['order'] = $j;
            $j -= $j_;
            if ($j < 0) {
                $j = 0;
            }
        }
        //print_r($arr);

        // thêm custom menu nếu có
        if (function_exists('register_admin_menu')) {
            foreach (register_admin_menu() as $k => $v) {
                // nếu menu đã tồn tại -> gộp vào menu đó
                if (isset($arr[$k])) {
                    if (empty($v)) {
                        $arr[$k] = NULL;
                    } else {
                        //print_r( $v );
                        // nếu có tham số arr_replace -> thay toàn bộ menu cũ bằng menu mới
                        if (isset($v['arr_replace'])) {
                            $v['arr'] = $v['arr_replace'];
                            $v['arr_replace'] = NULL;
                            $arr[$k] = $v;
                        }
                        // mặc định thì bổ sung menu (nếu có)
                        else if (isset($v['arr'])) {
                            foreach ($v['arr'] as $k2 => $v2) {
                                $arr[$k]['arr'][$k2] = $v2;
                            }
                        }
                        //print_r($v);

                        // thay thế các giá trị khác cho menu. Ví dụ: phân quyền
                        foreach ($v as $k2 => $v2) {
                            //print_r($v2);

                            // nếu mảng con được đặt NULL -> ẩn nó đi
                            if (empty($v2)) {
                                //echo $k . PHP_EOL;
                                //echo $k2 . PHP_EOL;
                                $arr[$k]['arr'][$k2] = NULL;
                            } else {
                                if ($k2 == 'arr') {
                                    continue;
                                }
                                $arr[$k][$k2] = $v2;
                            }
                        }
                    }
                }
                // nếu không -> tạo mới luôn
                else {
                    $arr[$k] = $v;
                }
            }
        }
        //print_r($arr);

        //
        return $arr;
    }

    // kiểm tra và tạo file htaccess chặn truy cập ở các thư mục không phải public
    protected function auto_create_htaccess_deny($remove_file = false)
    {
        // các thư mục được phép truy cập
        $arr_allow_dir = [
            // thư mục upload ảnh
            'upload',
            // thư mục chứa các file tĩnh để người dùng sử dụng
            'public',
            // hướng dẫn sử dụng của codeigniter
            'user_guide',
            // thư mục chứa file design của từng website
            'design',
        ];

        //
        foreach (glob(PUBLIC_HTML_PATH . '*') as $filename) {
            // chỉ kiểm tra đối với thư mục
            if (is_dir($filename)) {
                //echo $filename . '<br>' . PHP_EOL;
                //echo basename( $filename ) . '<br>' . PHP_EOL;
                $f = $filename . '/.htaccess';

                // không xử lý file htaccess trong các thư mục được nêu tên
                if (in_array(basename($filename), $arr_allow_dir)) {
                    continue;
                }

                // cập nhật lại nội dung file htaccess
                if ($remove_file === true && file_exists($f)) {
                    $this->MY_unlink($f);
                }

                //
                if (file_exists($f)) {
                    continue;
                }
                //echo $f . '<br>' . PHP_EOL;

                //
                $this->base_model->ftp_create_file(
                    $f,
                    $this->helpersTmpFile(
                        'htaccess_deny_all',
                        [
                            'created_from' => __CLASS__ . ':' . __LINE__,
                            'base_url' => DYNAMIC_BASE_URL,
                        ]
                    ),
                );
            }
            //include $filename;
        }
    }

    /*
     * chức năng xóa cache theo key truyền vào
     * clean_all: một số phương thức không áp dụng được kiểu xóa theo key -> admin có thể xóa all
     */
    public function cleanup_cache($for = '', $clean_all = false)
    {
        if ($for != '' || !empty($this->MY_post('data'))) {
            /*
             * ưu tiên sử dụng cleanup mặc định của codeigniter
             */
            // xóa theo key truyền vào -> dùng khi update post, term, config...
            if ($for != '') {
                $has_cache = $this->base_model->dcache($for, $clean_all);
                // 1 số phương thức không áp dụng được kiểu xóa này do không có key 
                if ($has_cache === NULL) {
                    return false;
                }
                echo 'Using cache delete Matching `' . $for . '` --- Total clear: ' . $has_cache . '<br>' . PHP_EOL;
                //var_dump( $has_cache );
                //die( $for );
            }
            // xóa toàn bộ cache
            else {
                //var_dump( $this->base_model->cache->getCacheInfo() );
                //die( __CLASS__ . ':' . __LINE__ );
                $has_cache = $this->base_model->dcache();
            }

            // nếu lỗi -> thử phương thức xóa từng file
            if ($has_cache === false && MY_CACHE_HANDLER == 'file') {
                foreach (glob(WRITE_CACHE_PATH . $for . '*') as $filename) {
                    echo $filename . '<br>' . PHP_EOL;
                    $has_cache = true;

                    //
                    if (is_file($filename)) {
                        if (!$this->MY_unlink($filename)) {
                            $this->base_model->alert('Lỗi xóa file cache ' . basename($filename), 'error');
                        }
                    }
                }
            }
            //var_dump( $has_cache );

            // nếu có giá trị của for -> thường là gọi từ admin lúc update -> không alert
            if ($for != '') {
                return false;
            }

            //
            if ($has_cache === true) {
                $this->base_model->alert('Toàn bộ file cache đã được xóa');

                // đồng bộ lại tổng số nhóm con cho các danh mục trước đã
                $this->term_model->sync_term_child_count();
            } else {
                $this->base_model->alert('Thư mục cache trống!', 'warning');
            }
            die(__CLASS__ . ':' . __LINE__);
        }

        //
        $this->teamplate_admin['content'] = view('admin/cleanup_view', array());
        return view('admin/admin_teamplate', $this->teamplate_admin);
    }

    // reset sesion login -> giữ trạng thái đăng nhập nếu không dùng máy tính mà vẫn bật trình duyệt
    public function admin_logged()
    {
        $this->base_model->alert('CẢNH BÁO! Hãy đăng xuất admin khi không sử dụng!', 'warning');
    }

    protected function get_preview_url()
    {
        $result = [];

        //
        $a = $this->MY_get('preview_url', '');
        if (
            $a != ''
        ) {
            $result[] = 'preview_url=' . urlencode($a);
        }

        //
        $a = $this->MY_get('preview_offset_top', '');
        if ($a != '') {
            $result[] = 'preview_offset_top=' . $a;
        }

        //
        if (!empty($result)) {
            return '&' . implode('&', $result);
        }
        return '';
    }
}
