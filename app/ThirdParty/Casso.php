<?php

namespace App\ThirdParty;

/*
 * Xử lý dữ liệu thanh toán tự động thông qua casso.vn
 * ưu tiên ngân hàng vietinbank
 */

//
class Casso
{
    public function __construct()
    {
        //$this->base_model = new \App\Models\Base();
    }

    protected static function pathTestLog()
    {
        return WRITEPATH . '___casso_test.txt';
    }

    protected static function testInput($v)
    {
        $f = self::pathTestLog();

        //
        file_put_contents($f, 'description: ' . $v->description . PHP_EOL, FILE_APPEND);
        file_put_contents($f, 'amount: ' . $v->amount . PHP_EOL, FILE_APPEND);
        file_put_contents($f, 'subAccId: ' . $v->subAccId . PHP_EOL, FILE_APPEND);
        file_put_contents($f, 'bank_sub_acc_id: ' . $v->bank_sub_acc_id . PHP_EOL, FILE_APPEND);
        file_put_contents($f, 'id: ' . $v->id . PHP_EOL, FILE_APPEND);
        file_put_contents($f, 'tid: ' . $v->tid . PHP_EOL, FILE_APPEND);
        chmod($f, DEFAULT_FILE_PERMISSION);
    }

    // hàm này sẽ trả về object chứa thông tin thanh toán
    public static function phpInput($debug_enable = false)
    {
        ini_set('display_errors', 0);
        error_reporting(E_ALL);
        //error_reporting( E_ALL && E_WARNING && E_NOTICE );
        ini_set('log_errors', 1);
        ini_set('error_log', WRITEPATH . '/logs/log-' . date('Y-m-d') . '.log');

        //
        $result = [];
        try {
            file_put_contents(self::pathTestLog(), $_SERVER['REQUEST_URI'] . PHP_EOL, LOCK_EX);
            file_put_contents(self::pathTestLog(), __CLASS__ . ':' . __LINE__ . PHP_EOL, FILE_APPEND);

            // LIVE data
            $data_string = file_get_contents('php://input');
            if (empty($data_string)) {
                if ($debug_enable !== true) {
                    file_put_contents(self::pathTestLog(), 'empty data string' . PHP_EOL, FILE_APPEND);
                    return NULL;
                }
                // TEST data
                $data_string = '{"error":0,"data":[{"id":1844887,"tid":"246745","description":"ND:CT DEN:231915042064 MBVCB.2707051024.042064.Bill 4.CT tu 0451001536775 DAO QUOC DAI toi 105877347307 DO XUAN VIET Ngan hang Cong Thuong Viet Nam (VIETINBANK); tai Napas","amount":5000,"cusum_balance":55000,"when":"2022-11-15 22:25:00","bank_sub_acc_id":"105877347307","subAccId":"105877347307","virtualAccount":"","virtualAccountName":"","corresponsiveName":"","corresponsiveAccount":"","corresponsiveBankId":"","corresponsiveBankName":""}]}';
            }
            file_put_contents(self::pathTestLog(), $data_string . PHP_EOL, FILE_APPEND);

            //
            //echo $data_string;
            $data_string = str_replace('\\', '', $data_string);
            $result['data_string'] = $data_string;

            // -->
            $data = json_decode($data_string);
            //print_r( $data );
            //echo __CLASS__ . ':' . __LINE__;
            //die( __CLASS__ . ':' . __LINE__ );

            //
            if (isset($data->data)) {
                foreach ($data->data as $k => $v) {
                    self::testInput($v);

                    // -> lấy order ID theo chữ bill -> tham số bắt buộc
                    $low_description = strtolower($v->description);
                    // đồng bộ về 1 định dạng -> dùng dấu cách để cắt dữ liệu
                    $low_description = str_replace('.bill ', '. bill ', $low_description);
                    $low_description = str_replace(':bill ', ': bill ', $low_description);

                    // cắt theo dấu cách
                    $order_id = explode(' bill ', $low_description);
                    if (count($order_id) > 1) {
                        $order_id = explode(' ', $order_id[1]);
                        $order_id = explode('.', $order_id[0]);

                        //
                        $data->data[$k]->order_id = trim($order_id[0]);
                    } else {
                        $data->data[$k] = NULL;
                    }
                }
            } else {
                $data = [];
            }
            $result['data'] = $data;
            //$result = $data;
        } catch (Exception $e) {
            //error_log( $e );
            if (isset($_SERVER['HTTP_REFERER'])) {
                error_log($_SERVER['HTTP_REFERER']);
            }
            error_log($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            //error_log( $e->getPrevious() );
            //error_log( $e->getCode() );
            //error_log( $e->getTraceAsString() );
        }
        //print( $result );
        //file_put_contents( self::pathTestLog(), json_encode( $result ), LOCK_EX );
        //file_put_contents( self::pathTestLog(), $_SERVER[ 'REQUEST_URI' ], FILE_APPEND );
        //die( __CLASS__ . ':' . __LINE__ );

        //
        return $result;
    }
}
