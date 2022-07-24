<?php

namespace App\ Models;

//
use App\ Libraries\ LanguageCost;
use App\ Libraries\ ConfigType;
use App\ Libraries\ DeletedStatus;

//
class Option extends EbModel {
    public $table = 'options';
    public $primaryKey = 'option_id';

    public function __construct() {
        parent::__construct();
    }

    public function insert_options( $data ) {
        return $this->base_model->insert( $this->table, $data );
    }

    public function backup_options( $option_type, $lang_key, $arr_meta_key ) {
        foreach ( $arr_meta_key as $k => $option_name ) {
            $where = [
                "option_type = '$option_type'",
                "option_name = '$option_name'",
                "lang_key = '$lang_key'",
            ];
            $where = implode( ' AND ', $where );

            // backup dữ liệu cũ
            $sql = "INSERT INTO `" . WGR_TABLE_PREFIX . "options_deleted`
            SELECT *
            FROM
                `" . WGR_TABLE_PREFIX . $this->table . "`
            WHERE
                $where";
            //echo $sql . '<br>' . "\n";
            $this->base_model->MY_query( $sql );
            echo 'Backup config: ' . $option_name . ':' . $option_type . '<br>' . PHP_EOL;

            // xong xóa
            $sql = "DELETE FROM `" . WGR_TABLE_PREFIX . $this->table . "`
            WHERE
                $where";
            //echo $sql . '<br>' . "\n";
            $this->base_model->MY_query( $sql );
            echo 'Delete config: ' . $option_name . ':' . $option_type . '<br>' . PHP_EOL;
        }
    }

    public function gets_config( $option_type, $lang_key ) {
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
        $data = $this->get_config_by_type( $option_type, $lang_key );

        //
        $result = [];
        foreach ( $data as $v ) {
            $result[ $v[ 'option_name' ] ] = $v[ 'option_value' ];
        }
        return $result;
    }

    public function get_lang() {
        return $this->gets_config( ConfigType::TRANS, LanguageCost::lang_key() );
    }

    public function get_smtp() {
        return ( object )$this->gets_config( ConfigType::SMTP, LanguageCost::lang_key() );
    }

    public function get_config_by_type( $option_type, $lang_key, $time = BIG_CACHE_TIMEOUT, $repeat = true ) {
        $in_cache = __FUNCTION__ . '-' . $lang_key;

        //
        $data = $this->the_cache( $option_type, $in_cache );

        // có cache thì trả về
        if ( $data !== NULL ) {
            //print_r( $data );
            return $data;
        }

        //
        $data = $this->base_model->select( '*', $this->table, array(
            // các kiểu điều kiện where
            'is_deleted' => DeletedStatus::FOR_DEFAULT,
            'option_type' => $option_type,
            'lang_key' => $lang_key,
        ), array(
            'order_by' => array(
                'option_id' => 'DESC',
            ),
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 2,
            'limit' => -1
        ) );
        //print_r( $data );

        // nếu không phải ngôn ngữ mặc định -> copy từ ngôn ngữ mặc định qua nếu có
        if ( empty( $data ) && $lang_key != LanguageCost::default_lang() ) {
            //die( __CLASS__ . ':' . __LINE__ );
            // lấy từ ngôn ngữ mặc định
            $data = $this->get_config_by_type( $option_type, LanguageCost::default_lang() );
            // nếu có dữ liệu
            if ( !empty( $data ) ) {
                //print_r( $data );
                // chạy vòng lặp insret dữ liệu sang ngôn ngữ hiện tại
                foreach ( $data as $v ) {
                    //print_r( $v );
                    //die( __CLASS__ . ':' . __LINE__ );

                    //
                    $data_insert = $v;
                    $data_insert[ 'option_id' ] = 0;
                    unset( $data_insert[ 'option_id' ] );
                    $data_insert[ 'lang_key' ] = $lang_key;
                    $data_insert[ 'lang_parent' ] = $v[ 'option_id' ];
                    //print_r( $data_insert );

                    //
                    $this->base_model->insert( $this->table, $data_insert );
                }
            }
            //die( __CLASS__ . ':' . __LINE__ );
        }

        //
        $this->the_cache( $option_type, $in_cache, $data, $time );

        //
        return $data;
    }

    function list_config( $lang_key = '', $time = BIG_CACHE_TIMEOUT ) {
        global $this_cache_config;
        if ( $this_cache_config !== NULL ) {
            return $this_cache_config;
        }

        //
        if ( $lang_key == '' ) {
            $lang_key = LanguageCost::lang_key();
        }

        // ưu tiên trong cache trước
        $this_cache_config = $this->the_cache( __FUNCTION__, $lang_key );
        // có cache thì trả về
        if ( $this_cache_config !== NULL ) {
            return $this_cache_config;
        }

        //
        $arr_option_type = [
            ConfigType::CONFIG,
            ConfigType::CATEGORY,
            ConfigType::POST,
            ConfigType::BLOGS,
            ConfigType::BLOG,
        ];
        //print_r( $arr_option_type );

        //
        $this_cache_config = [];
        foreach ( $arr_option_type as $option_type ) {
            foreach ( $this->get_config_by_type( $option_type, $lang_key ) as $v ) {
                $this_cache_config[ $v[ 'option_name' ] ] = $v[ 'option_value' ];
            }
        }
        //print_r( $this_cache_config );

        //
        $config_default = [];
        foreach ( $arr_option_type as $v ) {
            $config_default[] = ConfigType::meta_default( $v );
        }
        //print_r( $config_default );

        // gán giá trị mặc định cho các mảng dữ liệu chưa có
        foreach ( $config_default as $v ) {
            //print_r( $v );
            foreach ( $v as $k2 => $v2 ) {
                if ( !isset( $this_cache_config[ $k2 ] ) ) {
                    $this_cache_config[ $k2 ] = '';
                }
            }
        }
        //print_r( $this_cache_config );

        //
        $this->the_cache( __FUNCTION__, $lang_key, $this_cache_config, $time );

        //
        //die( __CLASS__ . ':' . __LINE__ );
        return $this_cache_config;
    }

    // trả về class css cho việc hiển thị số sản phẩm trên mỗi dòng
    public function get_posts_in_line( $cog ) {
        $arr = [];
        // desktop
        $arr[] = $this->get_config( $cog, 'eb_posts_per_line' );
        // table
        //$arr[] = $this->get_config( $cog, 'medium_posts_per_line' );
        // mobile
        //$arr[] = $this->get_config( $cog, 'small_posts_per_line' );

        //
        return implode( ' ', $arr );
    }
    public function posts_in_line( $cog ) {
        echo $this->get_posts_in_line( $cog );
    }

    // trả về class css cho việc hiển thị số bài viết blog trên mỗi dòng
    public function get_blogs_in_line( $cog ) {
        $arr = [];
        // desktop
        $arr[] = $this->get_config( $cog, 'eb_blogs_per_line' );
        // table
        //$arr[] = $this->get_config( $cog, 'medium_blogs_per_line' );
        // mobile
        //$arr[] = $this->get_config( $cog, 'small_blogs_per_line' );

        //
        return implode( ' ', $arr );
    }
    public function blogs_in_line( $cog ) {
        echo $this->get_blogs_in_line( $cog );
    }

    /*
     * Các function trong này sẽ được dọn dần trong base_model
     */
    function get_the_favicon( $cog, $key = 'web_favicon' ) {
        // nếu không có -> lấy mặc định logo
        //if ( !isset( $cog->$key ) || $cog->$key == '' ) {
        if ( $cog->$key == '' ) {
            return DYNAMIC_BASE_URL . 'favicon.png';
            //return $this->get_the_logo( $cog );
        }
        return $cog->$key;
    }

    function get_the_logo( $cog, $key = 'logo' ) {
        //if ( !isset( $cog->$key ) || $cog->$key == '' ) {
        if ( $cog->$key == '' ) {
            $cog->$key = $cog->logo;
        }
        return $cog->$key;
    }

    function the_logo( $cog, $key = 'logo', $logo_height = 'logo_main_height' ) {
        //if ( !isset( $cog->$logo_height ) || $cog->$logo_height == '' ) {
        if ( $cog->$logo_height == '' ) {
            $logo_height = 'logo_main_height';
        }
        if ( isset( $cog->$logo_height ) ) {
            $height = $cog->$logo_height;
        } else {
            $height = 90;
        }

        //
        echo '<a href="./" class="web-logo" aria-label="Home" style="background-image: url(\'' . $this->get_the_logo( $cog, $key ) . '\'); height: ' . $height . 'px;">&nbsp;</a>';
    }

    function get_config( $config, $key, $default_value = '' ) {
        //print_r( $config );
        //if ( isset( $config->$key ) ) {
        if ( $config->$key != '' ) {
            return $config->$key;
        }
        return $default_value;
    }

    function the_config( $config, $key, $default_value = '' ) {
        echo $this->get_config( $config, $key, $default_value );
    }

    // trả về key cho option cache
    public function key_cache( $config_type ) {
        return 'option-' . $config_type . '-';
    }
    // cache cho phần option -> gán key theo mẫu thống nhất để sau còn xóa cache cho dễ
    public function the_cache( $config_type, $key, $value = '', $time = MINI_CACHE_TIMEOUT ) {
        return $this->base_model->scache( $this->key_cache( $config_type ) . $key, $value, $time );
    }
}