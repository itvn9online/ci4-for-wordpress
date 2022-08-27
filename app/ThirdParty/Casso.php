<?php
namespace App\ ThirdParty;

/*
 * Xử lý dữ liệu thanh toán tự động thông qua casso.vn
 * ưu tiên ngân hàng vietinbank
 */

//
class Casso {
    protected static function testInput( $v ) {
        $f = PUBLIC_HTML_PATH . 'test.txt';

        file_put_contents( $f, 'description: ' . $v->description . "\n", FILE_APPEND );
        file_put_contents( $f, 'amount: ' . $v->amount . "\n", FILE_APPEND );
        file_put_contents( $f, 'subAccId: ' . $v->subAccId . "\n", FILE_APPEND );
        file_put_contents( $f, 'bank_sub_acc_id: ' . $v->bank_sub_acc_id . "\n", FILE_APPEND );
        file_put_contents( $f, 'id: ' . $v->id . "\n", FILE_APPEND );
        file_put_contents( $f, 'tid: ' . $v->tid . "\n", FILE_APPEND );
    }

    // hàm này sẽ trả về object chứa thông tin thanh toán
    public static function phpInput( $debug_enable = false ) {
        ini_set( 'display_errors', 0 );
        error_reporting( E_ALL );
        //error_reporting( E_ALL && E_WARNING && E_NOTICE );
        ini_set( 'log_errors', 1 );
        ini_set( 'error_log', WRITEPATH . '/logs/log-' . date( 'Y-m-d' ) . '.log' );

        //
        $result = [];
        try {
            // LIVE data
            $data_string = file_get_contents( 'php://input' );
            if ( empty( $data_string ) ) {
                if ( $debug_enable !== true ) {
                    return NULL;
                }
                // TEST data
                $data_string = '{"error":0,"data":[{"id":1517540,"tid":"184139","description":"ND:CT DEN:223900089686 MBVCB.2383815647.089686.Bill 15998.CT tu 0451001536775 DAO QUOC DAI toi 108876637379 DAO QUOC DAI (VIETINBANK) Cong Thuong Viet Nam; tai Napas","amount":1000,"cusum_balance":10000,"when":"2022-08-27 07:09:32","bank_sub_acc_id":"108876637379","subAccId":"108876637379","virtualAccount":"","virtualAccountName":"","corresponsiveName":"","corresponsiveAccount":"","corresponsiveBankId":"","corresponsiveBankName":""}]}';
            }
            file_put_contents( PUBLIC_HTML_PATH . 'test.txt', $data_string . "\n", LOCK_EX );
            file_put_contents( PUBLIC_HTML_PATH . 'test.txt', $_SERVER[ 'REQUEST_URI' ] . "\n", FILE_APPEND );
            file_put_contents( PUBLIC_HTML_PATH . 'test.txt', __CLASS__ . ':' . __LINE__ . "\n", FILE_APPEND );

            //
            //echo $data_string;
            $data_string = str_replace( '\\', '', $data_string );
            $result[ 'data_string' ] = $data_string;

            // -->
            $data = json_decode( $data_string );
            //print_r( $data );
            //echo __CLASS__ . ':' . __LINE__;
            //die( __CLASS__ . ':' . __LINE__ );

            //
            if ( isset( $data->data ) ) {
                foreach ( $data->data as $k => $v ) {
                    self::testInput( $v );

                    // -> lấy order ID theo chữ bill -> tham số bắt buộc
                    $low_description = strtolower( $v->description );

                    // thử cắt theo dấu cách
                    $order_id = explode( ' bill ', $low_description );
                    if ( count( $order_id ) > 1 ) {
                        $order_id = explode( ' ', $order_id[ 1 ] );
                        $order_id = explode( '.', $order_id[ 0 ] );

                        //
                        $data->data[ $k ]->order_id = trim( $order_id[ 0 ] );
                    } else {
                        // không thấy thì thử dấu .
                        $order_id = explode( '.bill ', $low_description );
                        if ( count( $order_id ) > 1 ) {
                            $order_id = explode( ' ', $order_id[ 1 ] );
                            $order_id = explode( '.', $order_id[ 0 ] );

                            //
                            $data->data[ $k ]->order_id = trim( $order_id[ 0 ] );
                        } else {
                            $data->data[ $k ] = NULL;
                        }
                    }
                }
            } else {
                $data = [];
            }
            $result[ 'data' ] = $data;
            //$result = $data;
        } catch ( Exception $e ) {
            //error_log( $e );
            if ( isset( $_SERVER[ 'HTTP_REFERER' ] ) ) {
                error_log( $_SERVER[ 'HTTP_REFERER' ] );
            }
            error_log( $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() );
            //error_log( $e->getPrevious() );
            //error_log( $e->getCode() );
            //error_log( $e->getTraceAsString() );
        }
        //print( $result );
        //file_put_contents( PUBLIC_HTML_PATH . 'test.txt', json_encode( $result ), LOCK_EX );
        //file_put_contents( PUBLIC_HTML_PATH . 'test.txt', $_SERVER[ 'REQUEST_URI' ], FILE_APPEND );
        //die( __CLASS__ . ':' . __LINE__ );

        //
        return $result;
    }
}