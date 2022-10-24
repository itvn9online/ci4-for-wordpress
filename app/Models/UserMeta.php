<?php
namespace App\Models;

//
//use App\Libraries\DeletedStatus;

//
class UserMeta extends UserBase
{
    public function __construct()
    {
        parent::__construct();
    }

    //
    public function set_user_meta($user_id, $key, $val)
    {
        // kiểm tra xem meta này có chưa
        $check_meta_exist = $this->get_user_meta($user_id, $key, 'umeta_id');

        // chưa thì insert
        if (empty($check_meta_exist)) {
            $result_id = $this->base_model->insert($this->metaTable, [
                'user_id' => $user_id,
                'meta_key' => $key,
                'meta_value' => $val,
            ]);
        }
        // rồi thì update
        else {
            $result_id = $check_meta_exist['umeta_id'];

            //
            $this->base_model->update_multiple($this->metaTable, [
                'meta_value' => $val,
            ], [
                    'umeta_id' => $result_id,
                ], [
                    'debug_backtrace' => debug_backtrace()[1]['function']
                ]);
        }

        //
        return $result_id;
    }

    // lấy user meta -> mặc định là theo key
    public function get_user_meta($user_id, $key, $select_col = 'meta_value')
    {
        return $this->base_model->select($select_col, $this->metaTable, array(
            // các kiểu điều kiện where
            'meta_key' => $key,
            'user_id' => $user_id,
        ), array(
                'order_by' => array(
                    'umeta_id' => 'DESC'
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 2,
                'limit' => 1
            ));
    }

    // lấy user meta -> phải khớp theo key và value
    public function get_user_value_meta($key, $user_id, $val)
    {
        return $this->base_model->select('meta_value', $this->metaTable, array(
            // các kiểu điều kiện where
            'meta_key' => $key,
            'user_id' => $user_id,
            'meta_value' => $val,
        ), array(
                'order_by' => array(
                    'umeta_id' => 'DESC'
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 2,
                'limit' => 1
            ));
    }
}