<?php
/*
 * file này chủ yếu xử lý các vấn đề liên quan đến database
 */

namespace App\Models;

// Cơ sở dữ liệu =)) thi thoảng làm tí thuần Việt cho đỡ bị xung đột với framework
class Csdl extends Session
{
    public $default_post_type = 'post';
    public $default_taxonomy = 'category';

    public function __construct()
    {
        parent::__construct();

        $this->db = \Config\Database::connect();
    }

    public function insert($table, $data, $remove_col = false, $queryType = '')
    {
        if ($remove_col === true) {
            $data = $this->removeInvalidField($data, $table);

            //
            if (empty($data)) {
                die('data insert empty ' . $table . ':' . __CLASS__ . ':' . __LINE__);
            }
        }

        //
        $builder = $this->db->table($table);
        if ($queryType == 'ignore') {
            $builder->ignore(true)->insert($data);
        } else if ($queryType == 'getQuery') {
            echo $builder->set($data)->getCompiledInsert();
            return false;
        } else if ($queryType == 'replace') {
            $builder->replace($data);
        } else {
            $builder->insert($data);
        }
        //die(' bb'); // lỗi sẽ hiển thị ở đây khi không insert đc
        if ($this->db->affectedRows()) {
            //var_dump( $this->db->affectedRows() );
            //echo $this->db->insertID() . '<br>' . PHP_EOL;
            return $this->db->insertID();
            /*
            } else {
            print_r( $this->db->error() );
            print_r( $this->db->_error_message() );
            */
        }
        return false;
    }

    // update giá trị cho 1 cột nào đó lên 1 đơn vị. Ví dụ: update lượt xem
    public function update_count($table, $col, $where_array, $ops = [])
    {
        //
        if (!isset($ops['value']) || !is_numeric($ops['value']) || $ops['value'] < 1) {
            $ops['value'] = 1;
        }

        //
        $builder = $this->db->table($table);
        $builder->set($col, $col . '+' . $ops['value'], false);
        foreach ($where_array as $key => $value) {
            $builder->where($key, $value);
        }
        $builder->update();
    }

    public function update_multiple($table, $data, $where_array, $ops = [])
    {
        $has_where = false;
        //print_r( $where_array );
        //print_r( $ops );
        //die( __CLASS__ . ':' . __LINE__ );

        //
        $builder = $this->db->table($table);

        //
        foreach ($where_array as $key => $value) {
            $builder->where($key, $value);
            $has_where = true;
        }

        //
        if (isset($ops['where_in'])) {
            foreach ($ops['where_in'] as $k => $v) {
                if (!empty($v)) {
                    $builder->whereIn($k, $v);
                    $has_where = true;
                }
            }
        }

        // where not in
        if (isset($ops['where_not_in'])) {
            foreach ($ops['where_not_in'] as $k => $v) {
                if (!empty($v)) {
                    $builder->whereNotIn($k, $v);
                    $has_where = true;
                }
            }
        }

        //
        if ($has_where !== true) {
            if (isset($ops['debug_backtrace'])) {
                echo $ops['debug_backtrace'] . '<br>' . PHP_EOL;
            }
            echo debug_backtrace()[1]['class'] . ':' . debug_backtrace()[1]['function'] . '<br>' . PHP_EOL;

            //
            die('data update empty ' . $table . ':' . __CLASS__ . ':line:' . __LINE__);
        }

        //print_r( $data );
        if (!isset($ops['no_remove_field'])) {
            $data = $this->removeInvalidField($data, $table);
        }
        //print_r( $data );
        if (empty($data)) {
            if (isset($ops['debug_backtrace'])) {
                echo $ops['debug_backtrace'] . '<br>' . PHP_EOL;
            }
            echo debug_backtrace()[1]['class'] . ':' . debug_backtrace()[1]['function'] . '<br>' . PHP_EOL;

            //
            die(__FUNCTION__ . ' data update empty ' . $table . ':line:' . __LINE__);
        }

        //
        /*
        if ( isset( $ops[ 'join' ] ) ) {
        foreach ( $ops[ 'join' ] as $k => $v ) {
        $builder->join( $k, $v, 'inner' );
        }
        }
        if ( isset( $ops[ 'left_join' ] ) ) {
        foreach ( $ops[ 'left_join' ] as $k => $v ) {
        $builder->join( $k, $v, 'left' );
        }
        }
        if ( isset( $ops[ 'right_join' ] ) ) {
        foreach ( $ops[ 'right_join' ] as $k => $v ) {
        $builder->join( $k, $v, 'right' );
        }
        }
        */

        //
        $builder->update($data);

        // in luôn ra query để test
        if (isset($ops['show_query'])) {
            print_r($this->db->getLastQuery()->getQuery());
            echo '<br>' . PHP_EOL;
        }

        // trả về query để sử dụng cho mục đích khác
        if (isset($ops['get_query'])) {
            return $this->db->getLastQuery()->getQuery();
        }

        //
        if (!$this->query_error($this->db->error())) {
            print_r($this->db->error());
        }

        //
        if ($this->db->affectedRows() > 0) {
            return true;
        }
        return false;
    }

    public function update($table, $data, $where, $id)
    {
        // ép buộc sử dụng update_multiple
        return $this->update_multiple($table, $data, [
            $where => $id
        ], [
            'debug_backtrace' => debug_backtrace()[1]['function']
        ]);
    }

    public function removeInvalidField($items, $tbl_name, $disableFields = array())
    {
        if (!is_array($items)) {
            return [];
        }
        $column_name = $this->db->getFieldNames($tbl_name); // list ra những cột có trong table
        //print_r( $column_name );
        //die( __CLASS__ . ':' . __LINE__ );

        foreach ($items as $key => $value) { //lặp lại cột lấy giá trị mà người dùng đẩy lên table: $item = array('newstitle'=>'tin tuc hom nay', 'slug'=>'tin-tuc-hom-nay'...)
            if (!in_array($key, $column_name)) { // kiểm tra xem những giá trị trường đẩy lên có trùng với cột trong table ở csdl k
                unset($items[$key]); // nếu không trùng thì loại bỏ những cột mà người dùng đẩy lên
            }
        }
        // remove disallow fields
        foreach ($disableFields as $key => $value) { //kiểm tra những field nào không đc phép cập nhật thì loại bỏ
            if (in_array($value, $column_name)) { // kiểm tra nếu tồn tại những cột được disable
                unset($items[$value]); // nếu tồn tại những cột đã disable thì sẽ remove.
            }
        }

        return $items;
    }

    public function delete_multiple($table, $where, $ops = [])
    {
        //print_r( $ops );
        //die( __CLASS__ . ':' . __LINE__ );
        $has_where = false;

        //
        $builder = $this->db->table($table);
        foreach ($where as $k => $v) {
            $builder->where($k, $v);
            $has_where = true;
        }

        // where in
        if (isset($ops['where_in'])) {
            //print_r( $ops[ 'where_in' ] );
            //die( __CLASS__ . ':' . __LINE__ );
            foreach ($ops['where_in'] as $k => $v) {
                if (!empty($v)) {
                    $builder->whereIn($k, $v);
                    $has_where = true;
                }
            }
        }

        // where not in
        if (isset($ops['where_not_in'])) {
            foreach ($ops['where_not_in'] as $k => $v) {
                if (!empty($v)) {
                    $builder->whereNotIn($k, $v);
                    $has_where = true;
                }
            }
        }

        //
        if ($has_where !== true) {
            if (isset($ops['debug_backtrace'])) {
                echo $ops['debug_backtrace'] . '<br>' . PHP_EOL;
            }
            echo debug_backtrace()[1]['class'] . ':' . debug_backtrace()[1]['function'] . '<br>' . PHP_EOL;

            //
            die(__FUNCTION__ . ' where update empty ' . $table . ':line:' . __LINE__);
            return false;
        }

        //
        if (isset($ops['join'])) {
            foreach ($ops['join'] as $k => $v) {
                $builder->join($k, $v, 'inner');
            }
        }
        if (isset($ops['left_join'])) {
            foreach ($ops['left_join'] as $k => $v) {
                $builder->join($k, $v, 'left');
            }
        }
        if (isset($ops['right_join'])) {
            foreach ($ops['right_join'] as $k => $v) {
                $builder->join($k, $v, 'right');
            }
        }

        //
        $builder->delete();

        // in luôn ra query để test
        if (isset($ops['show_query'])) {
            print_r($this->db->getLastQuery()->getQuery());
            echo '<br>' . PHP_EOL;
        }

        // trả về query để sử dụng cho mục đích khác
        if (isset($ops['get_query'])) {
            return $this->db->getLastQuery()->getQuery();
        }

        //
        if (!$this->query_error($this->db->error())) {
            print_r($this->db->error());
        }

        //
        if ($this->db->affectedRows()) {
            return true;
        }
        return false;
    }

    public function delete($table, $where, $id)
    {
        return $this->delete_multiple($table, [
            $where => $id
        ], [
            'debug_backtrace' => debug_backtrace()[1]['function']
        ]);
    }

    // trả về các cột dữ liệu mặc định trong 1 bảng
    public function default_data($table, $other_table = [])
    {
        $column_name = $this->db->getFieldNames($table); // list ra những cột có trong table

        $result = [];
        foreach ($column_name as $v) {
            $result[$v] = '';
        }

        //
        foreach ($other_table as $table2) {
            if ($table2 != '') {
                $column_name = $this->db->getFieldNames($table2);
                foreach ($column_name as $v) {
                    $result[$v] = '';
                }
            }
        }

        return $result;
    }

    public function table_exists($tbl)
    {
        if ($this->db->tableExists($tbl)) {
            return true;
        }
        return false;
    }

    // tự tạo 1 hàm select riêng, viết kiểu code cũ, mỗi lần select lại phải viết function khác -> mệt
    public function select($select, $from, $where = array(), $ops = array())
    {
        //print_r($ops);

        //
        $builder = $this->db->table($from);
        // lấy tổng số bản ghi
        if (isset($ops['selectCount'])) {
            $builder->selectCount($ops['selectCount']);
            $ops['limit'] = -1;
        }
        // select thông thường
        else {
            $builder->select($select);
        }

        if (isset($ops['join'])) {
            foreach ($ops['join'] as $k => $v) {
                $builder->join($k, $v, 'inner');
            }
        }
        if (isset($ops['left_join'])) {
            foreach ($ops['left_join'] as $k => $v) {
                $builder->join($k, $v, 'left');
            }
        }
        if (isset($ops['right_join'])) {
            foreach ($ops['right_join'] as $k => $v) {
                $builder->join($k, $v, 'right');
            }
        }
        /*
        if ( isset( $ops[ 'full_join' ] ) ) {
        foreach ( $ops[ 'full_join' ] as $k => $v ) {
        $builder->join( $k, $v, 'full' );
        }
        }
        */
        /*
        if ( isset( $ops[ 'self_join' ] ) ) {
        foreach ( $ops[ 'self_join' ] as $k => $v ) {
        $builder->join( $k, $v, 'self' );
        }
        }
        */
        // điều kiện lấy dữ liệu
        foreach ($where as $k => $v) {
            if ($v === NULL) {
                //$builder->where( $k, NULL, FALSE );
                $builder->where($k);
            } else {
                $builder->where($k, $v);
            }
        }


        // các thông số tùy chỉnh khác
        // -> chuyển or where thành where or
        if (isset($ops['or_where']) && !isset($ops['where_or'])) {
            $ops['where_or'] = $ops['or_where'];
        }
        // where or
        if (isset($ops['where_or']) && !empty($ops['where_or'])) {
            //$and_or = array();
            $builder->groupStart();
            foreach ($ops['where_or'] as $k => $v) {
                // nếu v là 1 mảng -> đây là kiểu WHERE OR lồng nhau
                if (is_array($v)) {
                    if (!empty($v)) {
                        //$builder->orGroupStart();
                        $builder->groupStart();
                        foreach ($v as $k2 => $v2) {
                            if ($v2 === NULL) {
                                $builder->orWhere($k2);
                            } else {
                                $builder->orWhere($k2, $v2);
                            }
                        }
                        $builder->groupEnd();
                    }
                }
                // còn không thì WHERE OR bình thường
                else {
                    $builder->orWhere($k, $v);
                }
            }
            $builder->groupEnd();
            //print_r($and_or);
        }

        // where in
        if (isset($ops['where_in'])) {
            foreach ($ops['where_in'] as $k => $v) {
                if (!empty($v)) {
                    $builder->whereIn($k, $v);
                }
            }
        }

        // where not in
        if (isset($ops['where_not_in'])) {
            foreach ($ops['where_not_in'] as $k => $v) {
                if (!empty($v)) {
                    $builder->whereNotIn($k, $v);
                }
            }
        }

        // like
        if (isset($ops['like'])) {
            foreach ($ops['like'] as $k => $v) {
                if ($v != '') {
                    // Produces: WHERE `title` LIKE '%match%' ESCAPE '!'
                    $builder->like($k, $v);
                }
            }
        }
        // like before
        if (isset($ops['like_before'])) {
            foreach ($ops['like_before'] as $k => $v) {
                if ($v != '') {
                    // Produces: WHERE `title` LIKE '%match' ESCAPE '!'
                    $builder->like($k, $v, 'before');
                }
            }
        }
        // like after
        if (isset($ops['like_after'])) {
            foreach ($ops['like_after'] as $k => $v) {
                if ($v != '') {
                    // Produces: WHERE `title` LIKE 'match%' ESCAPE '!'
                    $builder->like($k, $v, 'after');
                }
            }
        }
        // not like
        if (isset($ops['not_like'])) {
            foreach ($ops['not_like'] as $k => $v) {
                $len = strlen($v);
                // từ 3 ký tự trở lên sẽ tìm theo dạng '%tu-khoa%'
                if ($len > 2) {
                    $builder->notLike($k, $v);
                }
                // từ 3 ký tự trở xuống sẽ tìm theo dạng 'tu-khoa%' -> bắt đầu bằng
                else if ($len > 0) {
                    $builder->notLike($k, $v, 'after');
                }
            }
        }
        // or like
        if (isset($ops['or_like']) && !empty($ops['or_like'])) {
            $builder->groupStart();
            foreach ($ops['or_like'] as $k => $v) {
                $len = strlen($v);
                // từ 3 ký tự trở lên sẽ tìm theo dạng '%tu-khoa%'
                if ($len > 2) {
                    $builder->orLike($k, $v);
                }
                // từ 3 ký tự trở xuống sẽ tìm theo dạng 'tu-khoa%' -> bắt đầu bằng
                else if ($len > 0) {
                    $builder->orLike($k, $v, 'after');
                }
            }
            $builder->groupEnd();
        }
        // or not like
        if (isset($ops['or_not_like']) && !empty($ops['or_not_like'])) {
            $builder->groupStart();
            foreach ($ops['or_not_like'] as $k => $v) {
                $len = strlen($v);
                // từ 3 ký tự trở lên sẽ tìm theo dạng '%tu-khoa%'
                if ($len > 2) {
                    $builder->orNotLike($k, $v);
                }
                // từ 3 ký tự trở xuống sẽ tìm theo dạng 'tu-khoa%' -> bắt đầu bằng
                else if ($len > 0) {
                    $builder->orNotLike($k, $v, 'after');
                }
            }
            $builder->groupEnd();
        }

        // group by
        if (isset($ops['group_by'])) {
            foreach ($ops['group_by'] as $k => $v) {
                $builder->groupBy($v);
            }
        }

        // order by
        if (isset($ops['order_by'])) {
            foreach ($ops['order_by'] as $k => $v) {
                $builder->orderBy($k, $v);
            }
        }

        // offset -> limit
        if (!isset($ops['offset']) || $ops['offset'] < 0) {
            $ops['offset'] = 0;
        }
        //print_r($op);
        /*
        if ( isset( $ops[ 'limit' ] ) && $ops[ 'limit' ] > 0 ) {
        $builder->limit( $ops[ 'limit' ], $ops[ 'offset' ] );
        }
        */
        // daidq (2021-12-25): để tránh trường hợp select unlimit cho dữ liệu lớn -> đặt mặc định lệnh LIMIT nếu không được chỉ định
        if (!isset($ops['limit']) || $ops['limit'] === 0) {
            //echo 'auto limit <br>' . PHP_EOL;
            $ops['limit'] = 500;
        }
        if ($ops['limit'] > 0) {
            $builder->limit($ops['limit'], $ops['offset']);
        }

        // trả về kết quả
        $a = array();
        $query = $builder->get();

        //
        if (!$this->query_error($this->db->error())) {
            print_r($this->db->error());
        }

        // in luôn ra query để test
        if (isset($ops['show_query'])) {
            print_r($this->db->getLastQuery()->getQuery());
            echo '<br>' . PHP_EOL;
        }

        // trả về query để sử dụng cho mục đích khác
        if (isset($ops['get_query'])) {
            return $this->db->getLastQuery()->getQuery();
        }


        //print_r( $query );
        //print_r( $this->db->_error_message() );
        if (isset($ops['getNumRows'])) {
            return $query->getNumRows();
        } else {
            $a = $query->getResultArray();
        }
        //if ( $builder->countAllResults() > 0 ) {
        //print_r( $a );

        // nếu chỉ lấy 1 kết quả -> trả về luôn mảng số 0
        if (isset($ops['limit']) && $ops['limit'] === 1 && !empty($a)) {
            //echo $ops[ 'limit' ] . '<br>' . PHP_EOL;
            //echo $builder->countAllResults() . '<br>' . PHP_EOL;
            //print_r( $a );
            //print_r( $a[ 0 ] );
            //die( 'fgjhkgsd gsdfgsgs' );
            $a = $a[0];
        }
        //}
        //print_r( $a );
        return $a;
    }

    public function query_error($arr)
    {
        if ($arr['code'] > 0) {
            return false;
        }
        return true;
    }

    /*
     * Sử dụng query bindings để hạn chế sql injection -> params
     * https://www.codeigniter.com/user_guide/database/queries.html#query-bindings
     */
    public function MY_query($sql, $params = [])
    {
        return $this->db->query($sql, $params);
    }
}
