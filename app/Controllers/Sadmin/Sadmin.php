<?php

namespace App\Controllers\Sadmin;

//
use App\Controllers\Ajaxs;

// Libraries
use App\Libraries\LanguageCost;
use App\Libraries\AdminMenu;
//use App\Libraries\CommentType;
use App\Libraries\UsersType;

//
class Sadmin extends Ajaxs
{
    //public $user_group_list = array();

    // với 1 số controller, sẽ không nạp cái HTML header vào, nên có thêm tham số này để không nạp header nữa
    public $preload_admin_header = true;

    protected $body_class = '';
    // public $post_model = null;
    public $comment_model = null;
    public $teamplate_admin = null;

    public function __construct()
    {
        // trong admin thì luôn bật hiển thị lỗi cho dễ làm việc
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        //
        parent::__construct();

        //
        $this->required_logged('&remove_parameter=');

        //
        //print_r( $this->session_data );
        //var_dump( $this->session_data );
        // nếu không có quyền admin -> báo lỗi nếu đang vào admin
        if ($this->session_data['userLevel'] != UsersType::ADMIN_LEVEL) {
            die('Permission ERROR line ' . __CLASS__ . ':' . __LINE__);
        }

        //
        //$response = \Config\Services::response();
        //$response->setHeader('Cache-Control', 'no-cache');
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
                    'vadmin/header_view',
                    array(
                        //'admin_root_views' => VIEWS_PATH . 'vadmin/',
                        //'admin_default_views' => VIEWS_PATH . 'vadmin/default/',
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
        //echo debug_backtrace()[ 1 ][ 'class' ] . ':' . debug_backtrace()[ 1 ][ 'function' ] . '<br>' . "\n";
        return $this->result_json_type([
            'code' => __LINE__,
            'error' => 'Bad request!',
        ]);
    }

    // chức năng này sẽ kiểm tra quyền truy cập 1 module nào đó theo từng tài khoản -> truyền vào controller class -> role -> xác định theo role
    protected function check_permision($role)
    {
        // role này chính là tên controller của admin -> kiểm tra xem có file này không
        //echo $role . '<br>' . "\n";
        $role = $this->get_class_name($role);
        //echo $role . '<br>' . "\n";

        //
        $this->body_class = strtolower($role);

        //
        $session_data = $this->session_data;
        // TEST
        //$session_data['member_type'] = UsersType::GUEST;
        //$session_data['member_type'] = UsersType::MEMBER;
        //$session_data['member_type'] = UsersType::AUTHOR;
        //$session_data['member_type'] = UsersType::MOD;
        //print_r($session_data);

        // khâu kiểm tra quyền không cần đối với tài khoản admin
        if ($session_data['member_type'] == UsersType::ADMIN) {
            return true;
        }

        //
        $check_file = __DIR__ . '/' . $role . '.php';
        //echo $check_file . '<br>' . "\n";
        // nếu không tồn tại -> báo lỗi luôn
        if (!is_file($check_file)) {
            die('Role not found!');
        }

        // chuyển role về chữ thường
        $role = $this->body_class;
        //echo $role . '<br>' . "\n";
        if (!in_array($role, UsersType::role($session_data['member_type']))) {
            die('Permission ERROR! ' . $role . ' for ' . $session_data['member_type']);
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

    protected function admin_menu()
    {
        $arr = AdminMenu::menu_list();
        //print_r($arr);
        //echo count($arr) . "\n";

        // Hiển thị menu phân loại cho thành viên
        foreach (ARR_CUSTOM_USER_TYPE as $k => $v) {
            if (!isset($v['controller'])) {
                continue;
            }
            $arr['sadmin/users']['arr']['sadmin/' . $v['controller']] = $v;
        }
        //print_r($arr);

        // tạo số thứ tự để sắp xếp menu
        $j = 100;
        $j_ = ceil(100 / count($arr));
        //echo $j_ . "\n";
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
                        $arr[$k] = null;
                    } else {
                        //print_r( $v );
                        // nếu có tham số arr_replace -> thay toàn bộ menu cũ bằng menu mới
                        if (isset($v['arr_replace'])) {
                            $v['arr'] = $v['arr_replace'];
                            $v['arr_replace'] = null;
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
                                //echo $k . "\n";
                                //echo $k2 . "\n";
                                $arr[$k]['arr'][$k2] = null;
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
                //echo $filename . '<br>' . "\n";
                //echo basename( $filename ) . '<br>' . "\n";
                $f = $filename . '/.htaccess';

                // không xử lý file htaccess trong các thư mục được nêu tên
                if (in_array(basename($filename), $arr_allow_dir)) {
                    continue;
                }

                // cập nhật lại nội dung file htaccess
                if ($remove_file === true && is_file($f)) {
                    $this->MY_unlink($f);
                }

                //
                if (is_file($f)) {
                    continue;
                }
                //echo $f . '<br>' . "\n";

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
                if ($has_cache === null) {
                    return false;
                }
                echo 'Using cache delete Matching `' . $for . '` --- Total clear: ' . $has_cache . '<br>' . "\n";
                // var_dump($has_cache);
                //die( $for );
            }
            // xóa toàn bộ cache
            else {
                // var_dump($this->base_model->cache->getCacheInfo());
                // die(__CLASS__ . ':' . __LINE__);
                $has_cache = $this->base_model->dcache();
                if ($has_cache === null) {
                    var_dump($has_cache);
                    echo '<br>' . "\n";

                    // thử xóa theo từng key
                    foreach (
                        [
                            'post',
                            'get_page',
                            'term',
                            'get_all_taxonomy',
                            'get_the_menu',
                            'user',
                        ] as $v
                    ) {
                        $this->cleanup_cache($v . '-');
                    }

                    // cache config
                    $this->option_model->clearAllOpsCache();

                    //
                    $this->base_model->alert('Cache driver == Session driver! Thực hiện XÓA từng phần của cache...');
                }
            }

            // nếu lỗi -> thử phương thức xóa từng file
            if (MY_CACHE_HANDLER == 'file') {
                if ($has_cache === false) {
                    echo WRITE_CACHE_PATH . '<br>' . "\n";
                    foreach (glob(WRITE_CACHE_PATH . $for . '*') as $filename) {
                        echo $filename . '<br>' . "\n";
                        $has_cache = true;

                        //
                        if (is_file($filename)) {
                            if (!$this->MY_unlink($filename)) {
                                $this->base_model->alert('Lỗi xóa file cache ' . basename($filename), 'error');
                            }
                        }
                    }
                }

                // hỗ trợ xóa cả cache cho bản mobile
                $mobile_cache_path = rtrim(WRITE_CACHE_PATH, '/') . '_m/';
                echo $mobile_cache_path . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
                if (is_dir($mobile_cache_path)) {
                    foreach (glob($mobile_cache_path . $for . '*') as $filename) {
                        echo $filename . '<br>' . "\n";
                        $has_cache = true;

                        //
                        if (is_file($filename)) {
                            if (!$this->MY_unlink($filename)) {
                                $this->base_model->alert('Lỗi xóa file mobile cache ' . basename($filename), 'error');
                            }
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
        $this->teamplate_admin['content'] = view('vadmin/cleanup_view', array());
        return view('vadmin/admin_teamplate', $this->teamplate_admin);
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
        $a = $this->MY_get('preview_url');
        if (
            $a != ''
        ) {
            $result[] = 'preview_url=' . urlencode($a);
        }

        //
        $a = $this->MY_get('preview_offset_top');
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
