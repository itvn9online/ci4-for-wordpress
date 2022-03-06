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

    public function get_lang( $using_cache = true, $time = MEDIUM_CACHE_TIMEOUT ) {
        $lang_key = LanguageCost::lang_key();

        //
        $in_cache = '';
        if ( $using_cache === true ) {
            $in_cache = __FUNCTION__ . '-' . $lang_key;
        }

        //
        if ( $in_cache != '' ) {
            $cache_value = $this->base_model->MY_cache( $in_cache );

            // có cache thì trả về
            if ( $cache_value !== NULL ) {
                //print_r( $cache_value );
                return $cache_value;
            }
        }

        //
        $data = $this->base_model->select( 'option_name, option_value', $this->table, array(
            // các kiểu điều kiện where
            'is_deleted' => DeletedStatus::FOR_DEFAULT,
            'option_type' => ConfigType::TRANS,
            'lang_key' => $lang_key,
        ), array(
            /*
            'where_in' => array(
                'option_type' => $arr_in_option_type
            ),
            */
            'order_by' => array(
                'option_id' => 'DESC',
            ),
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 2,
            //'limit' => 1
        ) );
        //print_r( $data );
        //die( 'fj fgjfg' );

        //
        if ( $in_cache != '' ) {
            $this->base_model->MY_cache( $in_cache, $data, $time );
        }
        return $data;
    }

    //var $cache_config = [];

    function list_config( $lang_key = '', $get_sql = false, $repeat = true, $clear_cache = false ) {
        if ( $lang_key == '' ) {
            $lang_key = LanguageCost::lang_key();
        }
        $in_cache = __FUNCTION__ . '-' . $lang_key;

        // xóa cache nếu có yêu cầu
        if ( $clear_cache === true ) {
            //var_dump( $clear_cache );
            return $this->base_model->cache->delete( $in_cache );
        }

        //
        $cache_value = $this->base_model->MY_cache( $in_cache );

        // có cache thì trả về
        if ( $cache_value !== NULL ) {
            //print_r( $cache_value );
            return $cache_value;
        }

        /*
        if ( isset( $this->cache_config[ $lang_key ] ) ) {
            return $this->cache_config[ $lang_key ];
        }
        */

        //
        $arr_option_type = ConfigType::list();
        unset( $arr_option_type[ ConfigType::TRANS ] );
        //print_r( $arr_option_type );
        $arr_in_option_type = [];
        $config_default = [];
        foreach ( $arr_option_type as $k => $v ) {
            $arr_in_option_type[] = $k;
            $config_default[] = ConfigType::meta_default( $k );
        }
        //print_r( $config_default );
        //print_r( $arr_in_option_type );
        $sql = $this->base_model->select( '*', $this->table, array(
            // các kiểu điều kiện where
            'is_deleted' => DeletedStatus::FOR_DEFAULT,
            //'option_type' => ConfigType::CONFIG,
            'lang_key' => $lang_key,
        ), array(
            'where_in' => array(
                'option_type' => $arr_in_option_type
            ),
            'order_by' => array(
                'option_id' => 'DESC',
            ),
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 2,
            //'limit' => 1
        ) );
        //print_r( $sql );
        //die( __CLASS__ . ':' . __LINE__ );
        if ( $get_sql === true ) {
            return $sql;
        }

        // nếu không phải ngôn ngữ mặc định -> copy từ ngôn ngữ mặc định qua nếu có
        if ( empty( $sql ) && $lang_key != LanguageCost::default_lang() ) {
            $sql = $this->list_config( LanguageCost::default_lang(), true );
            if ( !empty( $sql ) ) {
                //print_r( $sql );
                foreach ( $sql as $v ) {
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

                // xong thì trả về dữ liệu insert nếu có
                if ( $repeat === true ) {
                    return $this->list_config( $lang_key, false, false );
                }
            }
            //die( __CLASS__ . ':' . __LINE__ );
        }

        //
        $getconfig = [];
        foreach ( $sql as $v ) {
            $getconfig[ $v[ 'option_name' ] ] = $v[ 'option_value' ];
        }
        // gán giá trị mặc định cho các mảng dữ liệu chưa có
        foreach ( $config_default as $v ) {
            //print_r( $v );
            foreach ( $v as $k2 => $v2 ) {
                if ( !isset( $getconfig[ $k2 ] ) ) {
                    $getconfig[ $k2 ] = '';
                }
            }
        }

        //
        //$this->cache_config[ $lang_key ] = $getconfig;
        $this->base_model->MY_cache( $in_cache, $getconfig, MEDIUM_CACHE_TIMEOUT );

        //
        return $getconfig;
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
}