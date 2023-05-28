<?php

namespace App\Models;

//
use App\Libraries\ConfigType;

//
class Payment extends Option
{
    public function __construct()
    {
        parent::__construct();
    }

    //
    protected function syncPeriodPrice($a)
    {
        return str_replace(',', '', str_replace(' ', '', trim($a)));
    }

    public function getCheckoutConfig($convert_currency = true)
    {
        $a = $this->arr_config(ConfigType::CHECKOUT);
        //print_r($a);

        // mảng giá gốc
        $arr_price = [];
        // mảng giá khuyến mại (giá gốc sẽ trừ đi số này -> số thực chi)
        $arr_discount = [];
        // mảng giá trị tặng theeo (giá gốc sẽ cộng với số này -> số thực nhận)
        $arr_bonus = [];
        if (isset($a['period_price']) && $a['period_price'] != '') {
            $a['period_price'] = $this->syncPeriodPrice($a['period_price']);
            $a['period_discount'] = $this->syncPeriodPrice($a['period_discount']);
            $a['period_bonus'] = $this->syncPeriodPrice($a['period_bonus']);
            //print_r($a);

            //
            $split_period_price = explode(';', $a['period_price']);
            //print_r($split_period_price);
            $split_period_discount = explode(';', $a['period_discount']);
            //print_r($split_period_discount);
            $split_period_bonus = explode(';', $a['period_bonus']);
            //print_r($split_period_bonus);

            //
            foreach ($split_period_price as $k => $v) {
                $price = trim($v);
                if ($convert_currency === true && $price > 0) {
                    $price = $this->convertStrToVnd($price);
                }
                //
                $discount_price = trim($split_period_discount[$k]);
                if ($convert_currency === true && $discount_price > 0) {
                    $discount_price = $this->convertStrToVnd($discount_price, $price);
                }
                //
                $bonus_price = trim($split_period_bonus[$k]);
                if ($convert_currency === true && $bonus_price > 0) {
                    $bonus_price = $this->convertStrToVnd($bonus_price, $price);
                }

                //
                $arr_price[] = $price;
                $arr_discount[] = $discount_price;
                $arr_bonus[] = $bonus_price;
            }

            //
            //print_r($arr_price);
            //print_r($arr_discount);
        } else {
            // tạo mảng mặc định -> do có 1 số code sử dụng mảng số 0
            $arr_discount[] = 0;
            $arr_bonus[] = 0;
        }
        $a['period_price'] = $arr_price;
        $a['period_discount'] = $arr_discount;
        $a['period_bonus'] = $arr_bonus;

        // chưa có giá min -> tạo giá min -> code sẽ dùng giá này làm mặc định
        if (!isset($a['min_product_price']) || $a['min_product_price'] <= 0) {
            if (!empty($arr_price)) {
                $a['min_product_price'] = $arr_price[0];

                //
                if ($a['min_product_price'] <= 0) {
                    die('ERROR! ' . __CLASS__ . ':' . __LINE__);
                }
            }
        }

        //
        //print_r($a);
        return $a;
    }

    // chuyển số tiền thành VNĐ
    public function convertStrToVnd($str, $price = 0)
    {
        // 1 triệu
        if (strpos($str, 'tr') !== false) {
            $str = str_replace('tr', '', $str) * 1000000;
        }
        // 1 nghìn
        else if (strpos($str, 'k') !== false) {
            $str = str_replace('k', '', $str) * 1000;
        }
        // xem có số % không
        if ($str > 0 && strpos($str, '%') !== false) {
            // chuyển số tiền thành %
            $str = str_replace('%', '', $str) * 1;
            return $price / 100 * $str;
        }
        return $str * 1;
    }
}
