<?php

namespace App\Models;

//
use App\Libraries\LanguageCost;
use App\Libraries\ConfigType;
use App\Libraries\DeletedStatus;

//
class Option extends EbModel
{
    public $table = 'options';
    public $primaryKey = 'option_id';

    public function __construct()
    {
        parent::__construct();
    }

    public function insert_options($data)
    {
        $data['is_deleted'] = DeletedStatus::FOR_DEFAULT;
        $data['last_updated'] = date(EBE_DATETIME_FORMAT);
        $data['insert_time'] = date('YmdHis');
        return $this->base_model->insert($this->table, $data);
    }

    public function backup_options($option_type, $lang_key, $arr_meta_key)
    {
        foreach ($arr_meta_key as $k => $option_name) {
            $where = [
                'option_type' => $option_type,
                'option_name' => $option_name,
                'lang_key' => $lang_key,
            ];

            /*
             * backup dữ liệu cũ
             */
            // -> dùng CI query builder để tạo query -> tránh sql injection
            $sql = $this->base_model->select(
                '*',
                $this->table,
                $where,
                array(
                    // hiển thị mã SQL để check
                    //'show_query' => 1,
                    // trả về câu query để sử dụng cho mục đích khác
                    'get_query' => 1,
                    // trả về COUNT(column_name) AS column_name
                    //'selectCount' => 'ID',
                    // trả về tổng số bản ghi -> tương tự mysql num row
                    //'getNumRows' => 1,
                    //'offset' => 0,
                    'limit' => -1
                )
            );
            //echo $sql . '<br>' . "\n";
            //die( __CLASS__ . ':' . __LINE__ );

            // -> câu SQL để thực thi trực tiếp
            $sql = "INSERT INTO `" . WGR_TABLE_PREFIX . "options_deleted` $sql";
            echo $sql . '<br>' . "\n";
            //die( __CLASS__ . ':' . __LINE__ );
            $this->base_model->MY_query($sql);
            echo 'Backup config: ' . $option_name . ':' . $option_type . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;

            // xong XÓA
            $this->base_model->delete_multiple(
                $this->table,
                $where,
                [
                    // hiển thị mã SQL để check
                    'show_query' => 1,
                    // trả về câu query để sử dụng cho mục đích khác
                    //'get_query' => 1,
                ]
            );
            //die( __CLASS__ . ':' . __LINE__ );
            echo 'Delete config: ' . $option_name . ':' . $option_type . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
        }
    }

    public function gets_config($option_type, $lang_key)
    {
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
        $data = $this->get_config_by_type($option_type, $lang_key);

        //
        $result = [];
        foreach ($data as $v) {
            $result[$v['option_name']] = $v['option_value'];
        }
        return $result;
    }

    // insert tự động 1 bản ghi vào option nếu không tìm thấy theo key
    protected function createOptions($option_name, $option_value, $option_type, $lang_key)
    {
        $data = $this->base_model->select(
            '*',
            $this->table,
            array(
                // các kiểu điều kiện where
                'option_name' => $option_name,
                'is_deleted' => DeletedStatus::FOR_DEFAULT,
                'option_type' => $option_type,
                'lang_key' => $lang_key,
            ),
            array(
                'order_by' => array(
                    'option_id' => 'DESC',
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 2,
                'limit' => 1
            )
        );
        //print_r($data);
        // nếu không có
        if (empty($data)) {
            // insert bản ghi mới
            $data = [
                'option_name' => $option_name,
                'option_value' => $option_value,
                'option_type' => $option_type,
                'lang_key' => $lang_key,
            ];
            //print_r($data);

            //
            $this->insert_options($data);
        }
        return $data['option_name'];
    }

    public function get_lang()
    {
        return $this->gets_config(ConfigType::TRANS, LanguageCost::lang_key());
    }
    public function create_lang($option_name, $option_value)
    {
        return $this->createOptions($option_name, $option_value, ConfigType::TRANS, LanguageCost::lang_key());
    }

    public function create_num($option_name, $option_value)
    {
        return $this->createOptions($option_name, $option_value, ConfigType::NUM_MON, LanguageCost::lang_key());
    }

    public function get_smtp()
    {
        return (object) $this->gets_config(ConfigType::SMTP, LanguageCost::lang_key());
    }

    public function get_config_by_type($option_type, $lang_key, $time = BIG_CACHE_TIMEOUT, $repeat = true)
    {
        $in_cache = __FUNCTION__ . '-' . $lang_key;

        //
        $data = $this->the_cache($option_type, $in_cache);

        // có cache thì trả về
        if ($data !== NULL) {
            //print_r($data);
            return $data;
        }

        //
        $data = $this->base_model->select(
            '*',
            $this->table,
            array(
                // các kiểu điều kiện where
                'is_deleted' => DeletedStatus::FOR_DEFAULT,
                'option_type' => $option_type,
                'lang_key' => $lang_key,
            ),
            array(
                'order_by' => array(
                    'option_id' => 'DESC',
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 2,
                'limit' => -1
            )
        );
        //print_r( $data );

        // nếu không phải ngôn ngữ mặc định -> copy từ ngôn ngữ mặc định qua nếu có
        if (empty($data) && $lang_key != LanguageCost::default_lang()) {
            //die( __CLASS__ . ':' . __LINE__ );
            // lấy từ ngôn ngữ mặc định
            $data = $this->get_config_by_type($option_type, LanguageCost::default_lang());
            // nếu có dữ liệu
            if (!empty($data)) {
                //print_r( $data );
                // chạy vòng lặp insret dữ liệu sang ngôn ngữ hiện tại
                foreach ($data as $v) {
                    //print_r( $v );
                    //die( __CLASS__ . ':' . __LINE__ );

                    //
                    $data_insert = $v;
                    $data_insert['option_id'] = 0;
                    unset($data_insert['option_id']);
                    $data_insert['lang_key'] = $lang_key;
                    $data_insert['lang_parent'] = $v['option_id'];
                    //print_r( $data_insert );

                    //
                    $this->insert_options($data_insert);
                }
            }
            //die( __CLASS__ . ':' . __LINE__ );
        }

        //
        $this->the_cache($option_type, $in_cache, $data, $time);

        //
        return $data;
    }

    public function list_config($lang_key = '', $time = BIG_CACHE_TIMEOUT)
    {
        global $this_cache_config;
        if ($this_cache_config !== NULL) {
            return $this_cache_config;
        }

        //
        if ($lang_key == '') {
            $lang_key = LanguageCost::lang_key();
        }

        // ưu tiên trong cache trước
        $this_cache_config = $this->the_cache(__FUNCTION__, $lang_key);
        // có cache thì trả về
        if ($this_cache_config !== NULL) {
            return $this_cache_config;
        }

        //
        $arr_option_type = [
            ConfigType::CONFIG,
            ConfigType::DISPLAY,
            ConfigType::SOCIAL,
            ConfigType::CATEGORY,
            ConfigType::POST,
            ConfigType::BLOGS,
            ConfigType::BLOG,
            ConfigType::CHECKOUT,
            // một số config sử dụng method riêng rồi thì bỏ ở đây đi
            //ConfigType::CHECKBOX,
            //ConfigType::NUM_MON,
        ];
        //print_r( $arr_option_type );

        //
        $this_cache_config = [];
        foreach ($arr_option_type as $option_type) {
            foreach ($this->get_config_by_type($option_type, $lang_key) as $v) {
                $this_cache_config[$v['option_name']] = $v['option_value'];
            }
        }
        //print_r($this_cache_config);

        //
        $config_default = [];
        foreach ($arr_option_type as $v) {
            $config_default[] = ConfigType::meta_default($v);
        }
        //print_r( $config_default );

        // gán giá trị mặc định cho các mảng dữ liệu chưa có
        foreach ($config_default as $v) {
            //print_r( $v );
            foreach ($v as $k2 => $v2) {
                if (!isset($this_cache_config[$k2])) {
                    $this_cache_config[$k2] = '';
                }
            }
        }

        // chuẩn hóa dữ liệu để tránh lỗi
        //print_r( $this_cache_config );
        $default_config_value = [
            'default_bg' => ConfigType::defaultColor('default_bg'),
            'sub_bg' => ConfigType::defaultColor('sub_bg'),
            'a_color' => ConfigType::defaultColor('a_color'),
            //
            'site_max_width' => 1024,
            'site_full_width' => 1920,
            //
            'body_font_size' => 14,
            'bodym_font_size' => 13,
        ];
        foreach ($default_config_value as $k => $v) {
            if (empty($this_cache_config[$k])) {
                $this_cache_config[$k] = $v;
            }
        }
        //print_r( $this_cache_config );
        //die( __CLASS__ . ':' . __LINE__ );

        //
        $this->the_cache(__FUNCTION__, $lang_key, $this_cache_config, $time);

        //
        //die( __CLASS__ . ':' . __LINE__ );
        return $this_cache_config;
    }

    // trả về class css cho việc hiển thị Số cột trên mỗi dòng
    public function get_posts_in_line($cog)
    {
        $arr = [
            __FUNCTION__,
            'row',
        ];
        // desktop
        $arr[] = $cog->eb_posts_per_line;
        // table
        $arr[] = $cog->eb_posts_medium_per_line;
        // mobile
        $arr[] = $cog->eb_posts_small_per_line;

        // column spacing
        $arr[] = $cog->eb_posts_column_spacing;

        //
        $arr[] = $cog->eb_posts_row_align;

        //
        return implode(' ', $arr);
    }
    public function posts_in_line($cog)
    {
        echo $this->get_posts_in_line($cog);
    }

    // trả về class css cho việc hiển thị Số cột trên mỗi dòng
    public function get_post_in_line($cog)
    {
        $arr = [
            __FUNCTION__,
            'row'
        ];
        // desktop
        $arr[] = $cog->eb_post_per_line;
        // table
        $arr[] = $cog->eb_post_medium_per_line;
        // mobile
        $arr[] = $cog->eb_post_small_per_line;

        // column spacing
        $arr[] = $cog->eb_post_column_spacing;

        //
        $arr[] = $cog->eb_post_row_align;

        //
        return implode(' ', $arr);
    }
    public function post_in_line($cog)
    {
        echo $this->get_post_in_line($cog);
    }

    // trả về class css cho việc hiển thị số bài viết blog trên mỗi dòng
    public function get_blogs_in_line($cog)
    {
        $arr = [
            __FUNCTION__,
            'row'
        ];
        // desktop
        $arr[] = $cog->eb_blogs_per_line;
        // table
        $arr[] = $cog->eb_blogs_medium_per_line;
        // mobile
        $arr[] = $cog->eb_blogs_small_per_line;

        // column spacing
        $arr[] = $cog->eb_blogs_column_spacing;

        //
        return implode(' ', $arr);
    }
    public function blogs_in_line($cog)
    {
        echo $this->get_blogs_in_line($cog);
    }

    // trả về class css cho việc hiển thị số bài viết blog trên mỗi dòng
    public function get_blog_in_line($cog)
    {
        $arr = [
            __FUNCTION__,
            'row'
        ];
        // desktop
        $arr[] = $cog->eb_blog_per_line;
        // table
        $arr[] = $cog->eb_blog_medium_per_line;
        // mobile
        $arr[] = $cog->eb_blog_small_per_line;

        // column spacing
        $arr[] = $cog->eb_blog_column_spacing;

        //
        return implode(' ', $arr);
    }
    public function blog_in_line($cog)
    {
        echo $this->get_blog_in_line($cog);
    }

    /*
     * Các function trong này sẽ được dọn dần trong base_model
     */
    public function get_the_favicon($cog, $key = 'web_favicon')
    {
        // nếu không có -> lấy mặc định logo
        //if ( !isset( $cog->$key ) || $cog->$key == '' ) {
        if ($cog->$key == '') {
            return DYNAMIC_BASE_URL . 'favicon.png';
            //return $this->get_the_logo( $cog );
        }
        return $cog->$key;
    }

    public function get_the_logo($cog, $key = 'logo')
    {
        //if ( !isset( $cog->$key ) || $cog->$key == '' ) {
        if ($cog->$key == '') {
            $cog->$key = $cog->logo;
        }
        return $cog->$key;
    }

    public function the_logo($cog, $key = 'logo', $logo_height = '')
    {
        //echo $logo_height;
        if ($logo_height != '' && isset($cog->$logo_height) && $cog->$logo_height != '') {
            $height = $cog->$logo_height;
        } else {
            $height = $cog->logo_main_height;
        }

        //
        echo '<a href="./" class="web-logo" aria-label="Home" style="background-image: url(\'' . $this->get_the_logo($cog, $key) . '\'); height: ' . $height . 'px;">&nbsp;</a>';
    }

    // trả về logo ở footer nếu có -> không thì trả về logo mặc định
    public function the_footer_logo($cog)
    {
        return $this->the_logo($cog, 'logofooter', 'logo_footer_height');
    }

    // trả về logo BTC nếu có link
    public function the_btc_logo($cog)
    {
        if ($cog->registeronline != '') {
            echo '<a href="' . $cog->registeronline . '" class="btc-logo btc-register-logo" aria-label="BCT" target="_blank" rel="nofollow">&nbsp;</a>';
        } else if ($cog->notificationbct != '') {
            echo '<a href="' . $cog->registeronline . '" class="btc-logo btc-noti-logo" aria-label="BCT" target="_blank" rel="nofollow">&nbsp;</a>';
        }
    }
    public function the_bct($cog)
    {
        return $this->the_btc_logo($cog);
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

    public function share_icons($cogs)
    {
        //print_r($cogs);
        // các cột dữ liệu sẽ được lấy để hiển thị
        $arr = [
            'facebook',
            'google',
            'linkin',
            'skype',
            'youtube',
            'zalo_me',
            'tiktok',
        ];
        $icons = [
            'facebook' => 'fa fa-facebook',
            'google' => 'fa fa-google',
            'linkin' => 'fa fa-linkedin',
            'skype' => 'fa fa-skype',
            'youtube' => 'fa fa-youtube',
            'zalo_me' => 'wgr-fa wgr-icons-zalo',
            'tiktok' => 'wgr-fa wgr-icons-tiktok',
        ];

        //
        $str = '';
        foreach ($arr as $v) {
            if ($cogs->$v == '') {
                continue;
            }

            //
            $str .= '<li><a href="' . $cogs->$v . '" target="_blank" rel="nofollow"><i class="' . $icons[$v] . '"></i> <span>' . $cogs->$v . '</span></a></li>';
        }
        return '<ul class="wgr-share-icons cf">' . $str . '</ul>';
    }

    public function the_config($config, $key, $default_value = '')
    {
        echo $this->get_config($config, $key, $default_value);
    }

    // trả về key cho option cache
    public function key_cache($config_type)
    {
        return 'option-' . $config_type . '-';
    }
    // cache cho phần option -> gán key theo mẫu thống nhất để sau còn xóa cache cho dễ
    public function the_cache($config_type, $key, $value = '', $time = MINI_CACHE_TIMEOUT)
    {
        return $this->base_model->scache($this->key_cache($config_type) . $key, $value, $time);
    }

    // in ra ảnh để tạo og:image
    public function the_og_image($seo, $getconfig)
    {
        if (!isset($seo['og_image']) || $seo['og_image'] == '') {
            $seo['og_image'] = $this->get_config($getconfig, 'image');
        }
        if (strpos($seo['og_image'], '//') === false) {
            $seo['og_image'] = base_url() . '/' . ltrim($seo['og_image'], '/');
        }

        //
        echo $seo['og_image'];
    }

    // in ra ảnh để tạo og:image:alt
    public function the_og_image_alt($seo, $getconfig)
    {
        if (!isset($seo['og_image_alt']) || $seo['og_image_alt'] == '') {
            $seo['og_image_alt'] = $this->the_config($getconfig, 'name');
        }

        //
        echo $seo['og_image_alt'];
    }
}
