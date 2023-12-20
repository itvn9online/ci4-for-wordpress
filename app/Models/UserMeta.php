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

    // lấy tất cả user meta của 1 id
    public function get_users_meta($user_id, $default_value = [], $select_col = 'meta_key, meta_value')
    {
        //
        $data_meta = $this->the_cache($user_id, __FUNCTION__);
        // var_dump($data_meta);
        if ($data_meta !== NULL) {
            return $data_meta;
        }

        //
        $meta = $this->base_model->select($select_col, $this->metaTable, array(
            // các kiểu điều kiện where
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
            // 'limit' => 1
        ));
        // print_r($meta);

        //
        $data_meta = [];
        foreach ($meta as $v) {
            $data_meta[$v['meta_key']] = $v['meta_value'];
        }

        //
        foreach ($default_value as $k => $v) {
            if (!isset($data_meta[$k])) {
                $data_meta[$k] = $v;
            }
        }

        //
        // print_r($data_meta);
        $this->the_cache($user_id, __FUNCTION__, $data_meta);

        //
        return $data_meta;
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

    /**
     * Cập nhật user meta
     **/
    public function update_umeta($id, $meta_data)
    {
        // print_r($meta_data);

        //
        foreach ($meta_data as $k => $v) {
            $this->set_user_meta($id, $k, $v);
        }
    }
}
