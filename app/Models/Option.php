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

    public function backup_options( $option_type, $lang_key ) {
        // backup dữ liệu cũ
        $sql = "INSERT INTO `options_deleted` SELECT * FROM `$this->table` WHERE option_type = '$option_type' AND lang_key = '$lang_key'";
        //echo $sql . '<br>' . "\n";
        $this->base_model->MY_query( $sql );
        echo 'Backup config: ' . $option_type . '<br>' . PHP_EOL;

        // xong xóa
        $sql = "DELETE FROM `$this->table` WHERE option_type = '$option_type' AND lang_key = '$lang_key'";
        //echo $sql . '<br>' . "\n";
        $this->base_model->MY_query( $sql );
        echo 'Delete config: ' . $option_type . '<br>' . PHP_EOL;
    }

    public function get_lang() {
        $data = $this->base_model->select( 'option_name, option_value', $this->table, array(
            // các kiểu điều kiện where
            'is_deleted' => DeletedStatus::FOR_DEFAULT,
            'option_type' => ConfigType::TRANS,
            'lang_key' => LanguageCost::lang_key(),
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
        return $data;
    }

    var $cache_config = [];

    function list_config( $lang_key = '', $get_sql = false, $repeat = true ) {
        if ( $lang_key == '' ) {
            $lang_key = LanguageCost::lang_key();
        }
        if ( isset( $this->cache_config[ $lang_key ] ) ) {
            return $this->cache_config[ $lang_key ];
        }

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
        //die( __FILE__ . ':' . __LINE__ );
        if ( $get_sql === true ) {
            return $sql;
        }

        // nếu không phải ngôn ngữ mặc định -> copy từ ngôn ngữ mặc định qua nếu có
        if ( empty( $sql ) && $lang_key != LanguageCost::default_lang() ) {
            $sql = $this->list_config( LanguageCost::default_lang(), true );
            if ( !empty( $sql ) ) {
                foreach ( $sql as $v ) {
                    //print_r( $v );

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
            //die( 'fhj s gs' );
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
        $this->cache_config[ $lang_key ] = $getconfig;
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
        echo '<a href="./" class="web-logo" style="background-image: url(\'' . $this->get_the_logo( $cog, $key ) . '\'); height: ' . $height . 'px;">&nbsp;</a>';
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