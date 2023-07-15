<?php

namespace App\Models;

//
use App\Libraries\DeletedStatus;

//
class UserBase extends EbModel
{
    public $table = 'users';
    //public $primaryKey = 'ID';

    public $metaTable = 'usermeta';
    public $metaKey = 'umeta_id';

    public function __construct()
    {
        parent::__construct();

        //
        //$this->session = \Config\Services::session();
    }

    /*
     * Chức năng này sẽ tạo ra user dựa theo email đăng ký
     */
    function generate_user_login($user_login)
    {
        $user_login = explode('@', $user_login);
        $user_login = trim($user_login[0]);
        if ($user_login == '') {
            die('user_login is NULL');
        }
        return $user_login;
    }

    function check_user_login_exist($user_login, $i = 0)
    {
        if ($i === false) {
            $new_user_login = $user_login;
        } else {
            $new_user_login = $i > 0 ? $user_login . $i : $user_login;
        }

        //
        $data = $this->base_model->select(
            '*',
            $this->table,
            [
                // các kiểu điều kiện where
                // kiểm tra user login đã được sử dụng rồi hay chưa thì không cần kiểm tra trạng thái XÓA -> vì có thể user này đã bị xóa vĩnh viễn
                //'is_deleted' => DeletedStatus::FOR_DEFAULT,
                'user_login' => $new_user_login
            ],
            array(
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 2,
                'limit' => 1
            )
        );
        //print_r( $data );

        // nếu ko có -> chưa dùng -> trả về user login này để dùng
        if (empty($data)) {
            return $new_user_login;
        }
        if ($i === false) {
            return false;
        }
        return $this->check_user_login_exist($user_login, $i + 1);
    }

    // kiểm tra user có hay chưa theo 1 thuộc tính unique
    public function check_another_user_by($id, $key, $val)
    {
        // lấy dữ liệu trong db
        $check_exist = $this->base_model->select(
            'ID',
            $this->table,
            array(
                // các kiểu điều kiện where
                'ID !=' => $id,
                $key => $val,
            ),
            array(
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 2,
                'limit' => 1
            )
        );

        //
        if (!empty($check_exist)) {
            return $key . ' has been using by another user #' . $check_exist['ID'];
        }
        //
        return true;
    }

    public function login($name, $password, $ci_pass = '', $level = '0')
    {
        /*
         * Chức năng đăng nhập này hơi cầu kỳ nên không dùng cái builder query chung được
         */
        $builder = $this->base_model->db->table($this->table);
        //$builder->select( '*' );

        // so khớp password
        $builder->groupStart();
        $builder->orWhere('user_pass', $password);
        $builder->orWhere('ci_pass', $password);
        // hỗ trợ kiểu password mdnam
        if ($ci_pass != '') {
            $builder->orWhere('ci_pass', $ci_pass);
        }
        $builder->groupEnd();

        //
        $builder->groupStart();
        // so khớp email
        if (strpos($name, '@') !== false) {
            $builder->orWhere('user_email', $name);
        }
        // so khớp username
        else {
            $builder->orWhere('user_login', $name);
            $builder->orWhere('user_phone', $name);
        }
        // so khớp với ID -> hỗ trợ dùng ID để đăng nhập
        if (is_numeric($name) === true) {
            $builder->orWhere('ID', $name * 1);
        }
        $builder->groupEnd();

        //
        $builder->where('is_deleted', DeletedStatus::FOR_DEFAULT);
        //$builder->where( 'user_status', UsersType::FOR_DEFAULT );

        //
        $builder->orderBy('ID', 'DESC');
        $builder->limit(1, 0);

        $query = $builder->get();
        //print_r( $this->base_model->db->getLastQuery()->getQuery() );
        $a = $query->getResultArray();
        //print_r( $a );
        //die( __CLASS__ . ':' . __LINE__ );
        if (!empty($a)) {
            return $a[0];
        }
        return false;
    }

    public function check_user_exist($email, $col = 'user_email', $set_flash = false)
    {
        if ($col == '') {
            $col = 'user_email';
        }

        // select dữ liệu từ 1 bảng bất kỳ
        $sql = $this->base_model->select(
            'ID',
            $this->table,
            array(
                // các kiểu điều kiện where
                // kiểm tra email đã được sử dụng rồi hay chưa thì không cần kiểm tra trạng thái XÓA -> vì có thể user này đã bị xóa vĩnh viễn
                //'is_deleted' => DeletedStatus::FOR_DEFAULT,
                // mặc định
                $col => $email,
            ),
            array(
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 2,
                'limit' => 1
            )
        );
        //print_r( $sql );

        // có rồi
        if (!empty($sql)) {
            if ($set_flash === true) {
                $this->base_model->msg_error_session('Email đã tồn tại !!!');
            }
            // trả về ID nếu có
            return $sql['ID'];
        }
        //print_r( $sql );

        // chưa có -> false
        return false;
    }

    public function check_resetpass($email)
    {
        // chưa có -> báo lỗi
        if ($this->check_user_exist($email) === false) {
            $this->base_model->msg_error_session('Email không tồn tại! ' . $email);
            return false;
        }
        // có thì trả về true
        return true;
    }

    // lưu session id của người dùng vào file, nếu máy khác truy cập thì báo luôn là đăng đăng nhập nơi khác
    public function set_logged($id)
    {
        return $this->base_model->scache($this->key_cache($id) . 'logged', [
            'key' => session_id(),
            't' => time(),
            'agent' => $_SERVER['HTTP_USER_AGENT'],
            'ip' => $_SERVER['REMOTE_ADDR'],
        ]);
    }
    // trả về thông tin phiên đăng nhập của người dùng
    public function get_logged($id)
    {
        return $this->base_model->scache($this->key_cache($id) . 'logged');
    }

    // trả về key cho user cache
    public function key_cache($id)
    {
        return 'user-' . $id . '-';
    }
    // cache cho phần user -> gán key theo mẫu thống nhất để sau còn xóa cache cho dễ
    public function the_cache($id, $key, $value = '', $time = MEDIUM_CACHE_TIMEOUT)
    {
        //echo $this->key_cache( $id ) . $key . '<br>' . PHP_EOL;
        return $this->base_model->scache($this->key_cache($id) . $key, $value, $time);
    }
}
