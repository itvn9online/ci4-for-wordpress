<?php
namespace App\ Controllers\ Admin;

// Libraries
use App\ Libraries\ LanguageCost;
use App\ Libraries\ ConfigType;
use App\ Libraries\ DeletedStatus;
use App\ Libraries\ PHPMaillerSend;

//
class Configs extends Admin {
    //private $lang_key = '';
    protected $config_type = ConfigType::CONFIG;
    protected $view_edit = 'edit';

    public function __construct() {
        parent::__construct();
        //$this->load->library( 'LanguageCost' );

        // kiểm tra quyền truy cập của tài khoản hiện tại
        $this->check_permision( __CLASS__ );

        //
        $this->lang_key = $this->lang_key;
        $this->config_type = $this->MY_get( 'config_type', $this->config_type );
    }

    public function index() {
        if ( !empty( $this->MY_post( 'data' ) ) ) {
            return $this->updated( $this->config_type );
        }

        if ( isset( $_GET[ 'test_mail' ] ) ) {
            return $this->test_mail();
        }

        //
        $meta_default = ConfigType::meta_default( $this->config_type );
        //print_r( $meta_default );

        // select dữ liệu từ 1 bảng bất kỳ
        $sql = $this->base_model->select( '*', $this->option_model->table, array(
            // các kiểu điều kiện where
            'is_deleted' => DeletedStatus::FOR_DEFAULT,
            'option_type' => $this->config_type,
            'lang_key' => $this->lang_key
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

        $this->teamplate_admin[ 'content' ] = view( 'admin/configs/' . $this->view_edit, array(
            'lang_key' => $this->lang_key,
            'config_type' => $this->config_type,
            'meta_default' => $meta_default,
            'data' => $value,
            'vue_data' => [
                'lang_key' => $this->lang_key,
                'lang_name' => LanguageCost::list( $this->lang_key ),
                'config_type' => $this->config_type,
                'config_name' => ConfigType::list( $this->config_type ),
            ],
            'value' => ( object )$value,
        ) );
        return view( 'admin/admin_teamplate', $this->teamplate_admin );
    }

    protected function updated( $option_type ) {
        if ( !empty( $this->MY_post( 'data' ) ) ) {
            $data = $this->MY_post( 'data' );
        } else {
            $data = $_POST;
        }
        //print_r( $data );

        //
        $arr_meta_key = [];

        //
        if ( $option_type == ConfigType::CONFIG ) {
            // chỉ cho phép một số định dạng file được truy cập trong thư mục upload
            echo $this->deny_visit_upload( '', true, isset( $data[ 'enable_hotlink_protection' ] ) ? true : false ) . '<br>' . "\n";
            $this->auto_create_htaccess_deny( true );

            // chỉ cho phép một số định dạng file được truy cập trong thư mục themes
            echo $this->deny_visit_upload( PUBLIC_PUBLIC_PATH . 'themes', true, false ) . '<br>' . "\n";

            //
            $data[ 'logo_width_img' ] = 0;
            $data[ 'logo_height_img' ] = 0;
            //echo PUBLIC_PUBLIC_PATH . $data[ 'logo' ] . '<br>' . "\n";
            if ( isset( $data[ 'logo' ] ) && $data[ 'logo' ] != '' && file_exists( PUBLIC_PUBLIC_PATH . $data[ 'logo' ] ) ) {
                $logo_data = getimagesize( PUBLIC_PUBLIC_PATH . $data[ 'logo' ] );

                //
                $data[ 'logo_width_img' ] = $logo_data[ 0 ];
                $data[ 'logo_height_img' ] = $logo_data[ 1 ];
            }
            $arr_meta_key[] = 'logo_width_img';
            $arr_meta_key[] = 'logo_height_img';
            //print_r( $data );

            //
            //die( __CLASS__ . ':' . __LINE__ );
        }

        $list_field_has_change = $this->MY_post( 'list_field_has_change' );
        if ( empty( $list_field_has_change ) ) {
            $this->base_model->alert( 'Không xác định được dữ liệu cần thay đổi #' . $option_type, 'warning' );
        }
        //echo $list_field_has_change . '<br>' . "\n";
        $list_field_has_change = json_decode( $list_field_has_change );
        //print_r( $list_field_has_change );
        if ( empty( $list_field_has_change ) ) {
            $this->base_model->alert( 'Không có thay đổi nào được chỉ định #' . $option_type, 'warning' );
        }

        foreach ( $list_field_has_change as $k => $v ) {
            $arr_meta_key[] = $k;
        }
        //print_r( $arr_meta_key );

        //
        if ( !empty( $data[ 'list_slide' ] ) ) {
            $data[ 'list_slide' ] = implode( ';', $data[ 'list_slide' ] );
        } else {
            $data[ 'list_slide' ] = '';
        }
        //$data[ 'min_price' ] = str_replace( ',', '', $data[ 'min_price' ] );
        //$data[ 'max_price' ] = str_replace( ',', '', $data[ 'max_price' ] );
        //print_r( $data );
        //die( __CLASS__ . ':' . __LINE__ );

        //
        if ( isset( $data[ 'blog_private' ] ) && $data[ 'blog_private' ] == 'on' ) {
            $this->base_model->_eb_create_file( PUBLIC_PUBLIC_PATH . 'robots.txt', $this->helpersTmpFile( 'robots_disallow_all' ), [ 'ftp' => 1 ] );
        }
        //
        else if ( isset( $data[ 'robots' ] ) ) {
            // cập nhật lại robots.txt khi không có nội dung hoặc sai địa chỉ sitemap
            if ( $data[ 'robots' ] == '' || strpos( $data[ 'robots' ], DYNAMIC_BASE_URL ) === false ) {
                $data[ 'robots' ] = $this->helpersTmpFile( 'robots_default', [
                    'base_url' => DYNAMIC_BASE_URL,
                ] );
                //echo nl2br( $data[ 'robots' ] );

                //
                $arr_meta_key[] = 'robots';
            }
            //$data[ 'robots' ] = trim( $data[ 'robots' ] );

            //
            //$id = '1';

            /*
            $robot = fopen( PUBLIC_PUBLIC_PATH . 'robots.txt', 'w' )or die( 'Unable to open file!' );
            fwrite( $robot, $data[ 'robots' ] );
            fclose( $robot );
            */

            //
            $this->base_model->_eb_create_file( PUBLIC_PUBLIC_PATH . 'robots.txt', $data[ 'robots' ], [ 'ftp' => 1 ] );
        }
        //print_r( $data );

        //
        $this->option_model->backup_options( $option_type, $this->lang_key, $arr_meta_key );

        // sau đó insert cái mới
        $last_updated = date( EBE_DATETIME_FORMAT );
        $insert_time = date( 'YmdHis' );
        foreach ( $data as $k => $v ) {
            // có tác động thì mới update -> tác động thì sẽ có tên trong danh sách update
            if ( !in_array( $k, $arr_meta_key ) ) {
                continue;
            }

            // có giá trị thì mới update
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
        //die( __CLASS__ . ':' . __LINE__ );

        // chạy vòng lặp xóa các dữ liệu dư thừa -> không có trong config
        $meta_default = ConfigType::meta_default( $this->config_type );
        //print_r( $meta_default );
        $remove_not_in = [];
        foreach ( $meta_default as $k => $v ) {
            $remove_not_in[] = $k;
        }
        //print_r( $remove_not_in );

        // DELETE dữ liệu
        if ( !empty( $remove_not_in ) ) {
            $this->base_model->delete_multiple( $this->option_model->table, [
                // WHERE
                'option_type' => $this->config_type,
            ], [
                'where_not_in' => array(
                    'option_name' => $remove_not_in
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
            ] );
        }


        // dọn dẹp cache liên quan đến config này -> reset cache
        $this->cleanup_cache( $this->option_model->key_cache( 'list_config' ) );
        $this->cleanup_cache( $this->option_model->key_cache( $option_type ) );

        // xác nhận việc update đã xong
        echo '<script>top.done_field_has_change();</script>';

        //
        $this->base_model->alert( 'Cập nhật dữ liệu thành công #' . $option_type );
    }

    private function test_mail() {
        $smtp_config = $this->option_model->get_smtp();
        //print_r( $smtp_config );
        //die( __CLASS__ . ':' . __LINE__ );
        if ( !isset( $smtp_config->smtp_test_email ) || empty( $smtp_config->smtp_test_email ) ) {
            //print_r( $smtp_config );
            die( json_encode( [
                'code' => __LINE__,
                'error' => 'Test email is NULL or not found!'
            ] ) );
        }

        //
        $data_send = [
            'to' => $smtp_config->smtp_test_email,
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
            'message' => implode( '<br>', [
                'PHPMailer version: ' . file_get_contents( APPPATH . 'ThirdParty/PHPMailer/VERSION', 1 ),
                'Domain: ' . $_SERVER[ 'HTTP_HOST' ],
                'Request: ' . $_SERVER[ 'REQUEST_URI' ],
                'Method: ' . $_SERVER[ 'REQUEST_METHOD' ],
                'Time: ' . date( 'r' ),
                'IP: ' . $this->request->getIPAddress(),
                'Browser: ' . $_SERVER[ 'HTTP_USER_AGENT' ],
                'Server: ' . $_SERVER[ 'SERVER_ADDR' ],
                'Session: ' . session_id(),
            ] ),
        ];
        if ( isset( $smtp_config->smtp_test_bcc_email ) && !empty( $smtp_config->smtp_test_bcc_email ) ) {
            $data_send[ 'bcc_email' ] = [
                $smtp_config->smtp_test_bcc_email
            ];
        }
        if ( isset( $smtp_config->smtp_test_cc_email ) && !empty( $smtp_config->smtp_test_cc_email ) ) {
            $data_send[ 'cc_email' ] = [
                $smtp_config->smtp_test_cc_email
            ];
        }
        //print_r( $data_send );
        //die( __CLASS__ . ':' . __LINE__ );

        //
        echo 'PHPMailer version: ' . file_get_contents( APPPATH . 'ThirdParty/PHPMailer/VERSION', 1 ) . '<br>' . "\n";
        echo 'Username/ Email: ' . $smtp_config->smtp_host_user . '<br>' . "\n";
        echo 'Password: ' . substr( $smtp_config->smtp_host_pass, 0, 6 ) . '******<br>' . "\n";
        echo 'Hostname: ' . $smtp_config->smtp_host_name . '<br>' . "\n";
        echo 'Secure: ' . $smtp_config->smtp_secure . '<br>' . "\n";
        echo 'Port: ' . $smtp_config->smtp_host_port . '<br>' . "\n";
        echo '<hr>' . "\n";

        //
        $result = PHPMaillerSend::the_send( $data_send, $smtp_config, PHPMaillerSend::DEBUG_2 );
        if ( $result === true ) {
            echo 'Gửi email thành công! from <strong>' . $smtp_config->smtp_host_user . '</strong> to <strong>' . $data_send[ 'to' ] . '</strong> <br>' . "\n";

            //
            return true;
        } else {
            echo 'Gửi email THẤT BẠI! from <strong>' . $smtp_config->smtp_host_user . '</strong> <br>' . "\n";
            print_r( $result );
        }

        //
        return false;
    }
}