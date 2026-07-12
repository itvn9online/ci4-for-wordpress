<?php

namespace App\Controllers;

// Libraries
use App\Libraries\PostType;
use App\Libraries\TaxonomyType;

use App\Controllers\Traits\LayoutCacheTrait;
use App\Controllers\Traits\LayoutViewTrait;
use App\Controllers\Traits\LayoutPage404Trait;
use App\Controllers\Traits\LayoutCategoryTrait;
use App\Controllers\Traits\LayoutInputTrait;
use App\Controllers\Traits\LayoutMediaTrait;
use App\Controllers\Traits\LayoutPermissionTrait;
use App\Controllers\Traits\LayoutStructuredDataTrait;

//
class Layout extends Sync
{
    use LayoutCacheTrait;
    use LayoutViewTrait;
    use LayoutPage404Trait;
    use LayoutCategoryTrait;
    use LayoutInputTrait;
    use LayoutMediaTrait;
    use LayoutPermissionTrait;
    use LayoutStructuredDataTrait;

    //public $CI = null;

    //
    public $breadcrumb = [];
    public $getconfig = null;
    public $taxonomy_post_size = '';
    // danh sách các nhóm cha của nhóm hiện tại đang được xem
    public $taxonomy_slider = [];
    // danh sách ID nhóm của sản phẩm đang xem -> dùng để tìm các bài cùng nhóm khi xem chi tiết bài viết
    public $posts_parent_list = [];

    // với 1 số controller, sẽ không nạp cái HTML header vào, nên có thêm tham số này để không nạp header nữa
    public $preload_header = true;

    // ID của user đang đang nhập
    public $current_user_id = 0;
    // phân loại user đang đăng nhập
    public $current_user_type = 'nologin';
    // kiểu login -> dùng để css cho frontend nó dễ hơn
    public $current_user_logged = 'free-viewer';
    //
    public $current_pid = 0;
    public $current_cid = 0;
    public $current_sid = 0;
    public $breadcrumb_position = 1;
    public $session_data = null;
    public $isMobile = '';
    public $cache_key = '';
    public $cache_mobile_key = '';
    public $teamplate = [];
    public $debug_enable = false;
    // 
    public $option_model = null;
    public $lang_model = null;
    public $num_model = null;
    public $checkbox_model = null;
    public $post_model = null;
    public $menu_model = null;
    public $htmlmenu_model = null;
    public $user_model = null;

    public function __construct()
    {
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        // 
        parent::__construct();

        //echo base_url('/') . '<br>' . "\n";

        $this->option_model = new \App\Models\Option();
        $this->lang_model = new \App\Models\Lang();
        $this->num_model = new \App\Models\Num();
        $this->checkbox_model = new \App\Models\Checkbox();
        $this->post_model = new \App\Models\Post();
        $this->menu_model = new \App\Models\Menu();
        $this->htmlmenu_model = new \App\Models\Htmlmenu();
        $this->user_model = new \App\Models\User();

        //
        //$this->session = \Config\Services::session();

        //
        /*
        helper( [
        //'cookie',
        'url',
        'form',
        'security'
        ] );
        */

        /**
         * bắt đầu code
         */

        // $allurl = 'https://' . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'REQUEST_URI' ];

        // 
        $this->getconfig = $this->option_model->list_config();
        // print_r($this->getconfig);
        $this->getconfig = (object) $this->getconfig;
        // print_r($this->getconfig);
        // die(__CLASS__ . ':' . __LINE__);

        // tạo thông tin nhà xuất bản (publisher) cho phần dữ liệu có cấu trúc
        $itemprop_cache_logo = $this->base_model->scache('itemprop_logo');
        //$itemprop_cache_logo = WRITEPATH . 'itemprop-logo.txt';
        //$itemprop_cache_author = WRITEPATH . 'itemprop-author.txt';
        //if (!is_file($itemprop_cache_logo) || time() - filemtime($itemprop_cache_logo) > HOUR) {
        if ($itemprop_cache_logo === null) {
            // logo
            $structured_data = file_get_contents(VIEWS_PATH . 'html/structured-data/itemprop-logo.html');
            foreach (
                [
                    '{{web_quot_title}}' => str_replace('"', '', $this->getconfig->name),
                    '{{image}}' => DYNAMIC_BASE_URL . $this->getconfig->logo,
                    '{{trv_width_img}}' => $this->getconfig->logo_height_img,
                    '{{trv_height_img}}' => $this->getconfig->logo_width_img,
                ] as $k => $v
            ) {
                $structured_data = str_replace($k, $v, $structured_data);
            }

            //
            //$this->base_model->eb_create_file($itemprop_cache_logo, $structured_data);
            //touch($itemprop_cache_logo, time());
            $this->base_model->scache('itemprop_logo', $structured_data, HOUR);

            // author
            $structured_data = file_get_contents(VIEWS_PATH . 'html/structured-data/itemprop-author.html');
            $structured_data = str_replace('{{author_quot_title}}', str_replace('"', '', $this->getconfig->name), $structured_data);

            //
            //$this->base_model->eb_create_file($itemprop_cache_author, $structured_data);
            $this->base_model->scache('itemprop_author', $structured_data, HOUR);
        }

        //
        $this->session_data = $this->base_model->get_ses_login();
        //print_r( $this->session_data );

        // key lưu ID hiện tại của user
        if (!empty($this->session_data) && isset($this->session_data['userID']) && $this->session_data['userID'] > 0) {
            $this->current_user_id = $this->session_data['userID'];
            $this->current_user_type = $this->session_data['member_type'];
            $this->current_user_logged = 'logged-in';
        }

        //
        $this->debug_enable = (ENVIRONMENT !== 'production');
        //var_dump( $this->debug_enable );

        //
        // $this->cache_key = '';
        // $this->cache_mobile_key = '';

        //
        // $this->isMobile = '';
        // $this->teamplate = [];
        if ($this->preload_header === true) {
            //echo 'preload header <br>' . "\n";
            //$this->isMobile = $this->checkDevice( $_SERVER[ 'HTTP_USER_AGENT' ] );
            $this->isMobile = WGR_IS_MOBILE;
            //var_dump( $this->isMobile );

            //
            $this->global_header_footer();
        }
    }
}
