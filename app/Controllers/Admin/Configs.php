<?php
//require_once __DIR__ . '/Admin.php';
namespace App\ Controllers\ Admin;

// Libraries
use App\ Libraries\ LanguageCost;
use App\ Libraries\ ConfigType;
use App\ Libraries\ DeletedStatus;
use App\ Libraries\ PHPMaillerSend;

//
class Configs extends Admin {
    //private $lang_key = '';
    private $config_type = '';

    public function __construct() {
        parent::__construct();
        //$this->load->library( 'LanguageCost' );

        // kiểm tra quyền truy cập của tài khoản hiện tại
        $this->check_permision( __CLASS__ );

        //
        $this->lang_key = LanguageCost::lang_key();
        $this->config_type = $this->MY_get( 'config_type', ConfigType::CONFIG );
    }

    public function index() {
        if ( !empty( $this->MY_post( 'data' ) ) ) {
            $this->updated( $this->config_type );
        }

        if ( isset( $_GET[ 'test_mail' ] ) ) {
            return $this->test_mail();
        }

        //
        $meta_default = ConfigType::meta_default( $this->config_type );
        //print_r( $meta_default );

        // select dữ liệu từ 1 bảng bất kỳ
        $sql = $this->base_model->select( '*', $this->option_model->tbl, array(
            // các kiểu điều kiện where
            'is_deleted' => DeletedStatus::DEFAULT,
            'option_type' => $this->config_type,
            'lang_key' => $this->lang_key,
        ), array(
            'order_by' => array(
                'option_id' => 'DESC',
            ),
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 2,
            //'limit' => 3
        ) );
        //print_r( $sql );
        $value = [];
        foreach ( $sql as $v ) {
            $value[ $v[ 'option_name' ] ] = $v[ 'option_value' ];
        }
        //print_r( $value );

        // gán giá trị mặc định
        foreach ( $meta_default as $k => $v ) {
            if ( !isset( $value[ $k ] ) ) {
                $value[ $k ] = '';
            }
        }

        $this->teamplate_admin[ 'content' ] = view( 'admin/configs/edit', array(
            'lang_key' => $this->lang_key,
            'config_type' => $this->config_type,
            'meta_default' => $meta_default,
            'data' => $value,
            'value' => ( object )$value,
        ) );
        return view( 'admin/admin_teamplate', $this->teamplate_admin );
    }

    public function updated( $option_type ) {
        if ( !empty( $this->MY_post( 'data' ) ) ) {
            $data = $this->MY_post( 'data' );
        } else {
            $data = $_POST;
        }
        if ( !empty( $data[ 'list_slide' ] ) ) {
            $data[ 'list_slide' ] = implode( ';', $data[ 'list_slide' ] );
        } else {
            $data[ 'list_slide' ] = '';
        }
        //$data[ 'min_price' ] = str_replace( ',', '', $data[ 'min_price' ] );
        //$data[ 'max_price' ] = str_replace( ',', '', $data[ 'max_price' ] );
        //print_r( $data );
        //die( __FILE__ . ':' . __LINE__ );

        //
        if ( isset( $data[ 'robots' ] ) ) {
            if ( $data[ 'robots' ] == '' ) {
                $data[ 'robots' ] = '
User-agent: *
Disallow: /cgi-bin/
Disallow: /admin/
Disallow: /manager/
Disallow: /search?q=*
Disallow: *?replytocom
Disallow: */attachment/*
Disallow: /images/

Allow: /*.js$
Allow: /*.css$
Sitemap: ' . DYNAMIC_BASE_URL . 'sitemap';
            }
            $data[ 'robots' ] = trim( $data[ 'robots' ] );

            //
            //$id = '1';

            $robot = fopen( PUBLIC_PUBLIC_PATH . 'robots.txt', "w" )or die( "Unable to open file!" );
            fwrite( $robot, $data[ 'robots' ] );
            fclose( $robot );
        }

        //
        $this->option_model->backup_options( $option_type, $this->lang_key );

        //
        $meta_default = ConfigType::meta_default( $this->config_type );
        //print_r( $meta_default );

        // sau đó insert cái mới
        $last_updated = date( 'Y-m-d H:i:s' );
        $insert_time = date( 'YmdHis' );
        foreach ( $data as $k => $v ) {
            $v = trim( $v );
            if ( $v == '' ) {
                continue;
            }

            //
            echo 'Insert: ' . $k . ' = ' . $v . '<br>' . "\n";

            //
            $this->option_model->insert_options( [
                'option_name' => $k,
                'option_value' => $v,
                'option_type' => $option_type,
                'lang_key' => $this->lang_key,
                'last_updated' => $last_updated,
                'insert_time' => $insert_time,
            ] );
        }
        //die( __FILE__ . ':' . __LINE__ );

        //
        $this->base_model->alert( 'Cập nhật menu thành công #' . $option_type );
    }

    private function test_mail() {
        //print_r( $this->getconfig );
        if ( !isset( $this->getconfig->smtp_test_email ) || empty( $this->getconfig->smtp_test_email ) ) {
            die( 'smtp_test_email not found!' );
        }

        //
        $data_send = [
            'to' => $this->getconfig->smtp_test_email,
            'to_name' => 'Dao Quoc Dai',
            /*
            'bcc_email' => [
                'v0tjnhlangtu@gmail.com'
            ],
            'cc_email' => [
                'itvn9online@yahoo.com'
            ],
            */
            'subject' => 'Test email ' . date( 'r' ),
            'message' => $_SERVER[ 'HTTP_HOST' ] . ' ' . date( 'r' ),
        ];
        if ( isset( $this->getconfig->smtp_test_bcc_email ) && !empty( $this->getconfig->smtp_test_bcc_email ) ) {
            $data_send[ 'bcc_email' ] = [
                $this->getconfig->smtp_test_bcc_email
            ];
        }
        if ( isset( $this->getconfig->smtp_test_cc_email ) && !empty( $this->getconfig->smtp_test_cc_email ) ) {
            $data_send[ 'cc_email' ] = [
                $this->getconfig->smtp_test_cc_email
            ];
        }
        //print_r( $data_send );
        //die( __FILE__ . ':' . __LINE__ );

        //
        $result = PHPMaillerSend::the_send( $data_send, $this->getconfig, 2 );
        if ( $result === true ) {
            echo 'Gửi email thành công <br>' . "\n";
        } else {
            echo 'Gửi email THẤT BẠI <br>' . "\n";
            print_r( $result );
        }
    }
}