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
    // hẹn giờ trong khoảng thời gian này mà người dùng đăng nhập 2 thiết bị -> KHÓA
    protected $time_checker = 90;

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
            die(__FUNCTION__ . ': user_login is NULL');
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
        $a = $this->base_model->select(
            'ID, member_type',
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
        if (!empty($a)) {
            return $key . ' has been using by another ' . $a['member_type'] . ' #' . $a['ID'];
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
        //$builder->groupStart();
        // so khớp email
        if (strpos($name, '@') !== false) {
            //$builder->orWhere('user_email', $name);
            $builder->where('user_email', $name);
        }
        // so khớp username
        else {
            $builder->groupStart();
            $builder->orWhere('user_login', $name);
            // so khớp với ID -> hỗ trợ dùng ID để đăng nhập
            if (is_numeric($name) === true) {
                $builder->orWhere('ID', $name * 1);
                $builder->orWhere('user_phone', $name);
            }
            $builder->groupEnd();
        }
        //$builder->groupEnd();

        //
        $builder->where('is_deleted', DeletedStatus::FOR_DEFAULT);
        //$builder->where( 'user_status', UsersType::FOR_DEFAULT );

        //
        $builder->orderBy('ID', 'DESC');
        $builder->limit(1, 0);

        $query = $builder->get();
        //print_r($this->base_model->db->getLastQuery()->getQuery());
        //die(__CLASS__ . ':' . __LINE__);
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

    /**
     * Tạo key cho vụ chống đăng nhập trên nhiều thiết bị
     **/
    protected function keyCacheId($id)
    {
        return $this->key_cache($id) . 'logged';
    }
    protected function setCacheId($id, $data, $time = MINI_CACHE_TIMEOUT)
    {
        return $this->base_model->scache($this->keyCacheId($id), $data, $time);
    }
    protected function getCacheId($id)
    {
        return $this->base_model->scache($this->keyCacheId($id));
    }

    // lưu session id của người dùng vào file, nếu máy khác truy cập thì báo luôn là đăng đăng nhập nơi khác
    public function setLogged($id)
    {
        //return $this->cacheLogged($id);
        return $this->insertLogged($id, $this->base_model->MY_sessid());
    }
    // trả về thông tin phiên đăng nhập của người dùng
    public function getLogged($id)
    {
        //return $this->getCacheId($id);
        return $this->insertLogged($id);
    }

    /**
     * Trả về session id lưu trong cache
     **/
    public function getCLogged($id)
    {
        return $this->getCacheId($id);
    }
    /**
     * Lưu session id vào cache
     **/
    public function setCLogged($id, $signature = '')
    {
        return $this->setCacheId($id, $this->base_model->MY_sessid() . '|' . $signature, $this->time_checker);
    }

    /**
     * Khi khóa tk user, nếu chưa có cache này thì chưa khóa -> bỏ qua cho 1 lần cache
     * Cái này lưu cache lâu hơn chút -> để người dùng có ngồi đợi hết cache thì vẫn bị block
     **/
    public function setCBlocked($id, $signature = '')
    {
        //return $this->setCacheId($id, $this->base_model->MY_sessid() . '|' . $signature, ceil($this->time_checker * 2));
        return $this->setCacheId($id, $this->base_model->MY_sessid() . '|' . $signature);
    }

    /**
     * Lưu phiên đăng nhập vào cache -> ko nên dùng do cache phân định mobile và desktop -> người dùng xem trên mobile và desktop là được 2 lần
     **/
    protected function cacheLogged($id)
    {
        // lấy session ID trước đó
        $sid = $this->base_model->MY_sessid();
        //$sid = $this->wrtieLogged($id);
        // xong lưu session ID mới
        //$this->wrtieLogged($id, $this->base_model->MY_sessid());

        //
        $data = [
            'key' => $sid,
            't' => time(),
            'agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
            'ip' => $_SERVER['REMOTE_ADDR'],
        ];

        // sử dụng cache -> dễ lỗi khi chạy 2 thiết bị khác nhau
        return $this->setCacheId($id, $data);
    }

    /**
     * Trả về key đê lưu phiên đăng nhập của người dùng trong database
     **/
    protected function keyLogged($id)
    {
        return $id . __FUNCTION__;
    }

    /**
     * Lưu phiên đăng nhập vào database
     **/
    protected function insertLogged($id, $sid = '')
    {
        if ($sid == '') {
            // hẹn giờ xóa bớt database cho nhẹ
            $time_remove = 600;

            //
            $in_cache = 'cleanup_' . __FUNCTION__;
            if ($this->base_model->scache($in_cache) === NULL) {
                // không cho xóa liên tục
                $this->base_model->scache($in_cache, time(), $time_remove);

                //
                $this->base_model->delete_multiple('ci_logged', [
                    // WHERE
                    'created_at <' => time() - $time_remove,
                ], [
                    // hiển thị mã SQL để check
                    //'show_query' => 1,
                    // trả về câu query để sử dụng cho mục đích khác
                    //'get_query' => 1,
                ]);
            }

            //
            return $this->base_model->select(
                'ip, session_id AS key, agent, created_at AS t',
                'ci_logged',
                array(
                    'key' => $this->keyLogged($id),
                    'session_id !=' => $this->base_model->MY_sessid(),
                    // thời gian kiểm tra, tính toán để không quá lâu cũng ko quá nhanh -> dễ khóa nhầm
                    'created_at >' => time() - $this->time_checker,
                ),
                array(
                    'order_by' => array(
                        'id' => 'DESC'
                    ),
                    // hiển thị mã SQL để check
                    //'show_query' => 1,
                    // trả về câu query để sử dụng cho mục đích khác
                    //'get_query' => 1,
                    // trả về COUNT(column_name) AS column_name
                    //'selectCount' => 'ID',
                    // trả về tổng số bản ghi -> tương tự mysql num row
                    //'getNumRows' => 1,
                    //'offset' => 0,
                    'limit' => 1
                )
            );
        }

        //
        return $this->base_model->insert('ci_logged', [
            'ip' => $_SERVER['REMOTE_ADDR'],
            'key' => $this->keyLogged($id),
            'session_id' => $sid,
            'agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
            'created_at' => time(),
        ]);
    }

    /**
     * Lưu ID phiên đăng nhập vào thư mục riêng -> lưu trong cache bị phân biệt desktop và mobile -do prefix
     **/
    protected function wrtieLogged($id, $sid = '')
    {
        // thư mục lưu trữ phiên đăng nhập theo ID
        $dir = WRITEPATH . 'logged';
        // thêm ngày tháng để xóa log cho tiện
        $f = $dir . '/' . date('Y-m-d');
        if (!is_dir($f)) {
            // tạo thư mục logged nếu chưa có
            if (!is_dir($dir)) {
                mkdir($dir, DEFAULT_DIR_PERMISSION) or die('ERROR create dir (' . __CLASS__ . ':' . __LINE__ . ')! ' . $dir);
                chmod($dir, DEFAULT_DIR_PERMISSION);
            }

            // tạo theo ngày tháng
            mkdir($f, DEFAULT_DIR_PERMISSION) or die('ERROR create dir (' . __CLASS__ . ':' . __LINE__ . ')! ' . $f);
            chmod($f, DEFAULT_DIR_PERMISSION);
        }
        $f .= '/' . $id . __FUNCTION__ . '.txt';

        //
        if ($sid != '') {
            file_put_contents($f, time() . '|' . $sid, LOCK_EX);
            chmod($f, DEFAULT_FILE_PERMISSION);
            return true;
        }

        //
        $result = '';
        if (is_file($f)) {
            $result = file_get_contents($f);
            $result = explode('|', $result);
            if (is_numeric($result[0]) && time() - $result[0] < 120) {
                $result = $result[1];
            } else {
                $result = '';
            }
        }
        return $result;
    }

    /**
     * Xóa phiên đăng nhập trong database để dọn dẹp dữ liệu mỗi khi người dùng confirm
     **/
    public function confirmLogged($id)
    {
        return $this->base_model->delete_multiple('ci_logged', [
            // WHERE
            'key' => $this->keyLogged($id),
            //'created_at <' => time() - $this->time_checker,
            'session_id !=' => $this->base_model->MY_sessid(),
        ], [
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
        ]);
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
