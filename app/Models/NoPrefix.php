<?php
/*
 * Hàm này sẽ thực hiện việc thay đổi prefix để xử lý dữ liệu ở 1 số bảng prefix khác với mặc định
 * Thường dùng khi móc vào những code khác mà muốn update sang phiên bản code CI4 + Wordpress này
 */
namespace App\Models;

// Trong một số trường hợp càn thay đổi prefix table khi truy vấn -> lúc đấy sẽ gọi thông qua model này
class NoPrefix extends Csdl
{
    // tùy chỉnh prefix theo ý muốn
    protected $custom_prefix = '';

    public function __construct()
    {
        parent::__construct();
    }

    // thiết lập prefix theo yêu cầu
    protected function my_prefix()
    {
        //die( $this->custom_prefix );
        $this->db->setPrefix($this->custom_prefix);
    }

    // trả lại prefix gốc sau mỗi query
    protected function default_prefix()
    {
        $this->db->setPrefix(WGR_TABLE_PREFIX);
    }

    public function insert($table, $data, $remove_col = false, $queryType = '')
    {
        $this->my_prefix();
        $a = parent::insert($table, $data, $remove_col, $queryType);
        $this->default_prefix();
        return $a;
    }

    public function update_count($table, $col, $where_array, $ops = [])
    {
        $this->my_prefix();
        $a = parent::update_count($table, $col, $where_array, $ops);
        $this->default_prefix();
        return $a;
    }

    public function update_multiple($table, $data, $where_array, $ops = [])
    {
        $this->my_prefix();
        $a = parent::update_multiple($table, $data, $where_array, $ops);
        $this->default_prefix();
        return $a;
    }

    public function delete_multiple($table, $where, $ops = [])
    {
        $this->my_prefix();
        $a = parent::delete_multiple($table, $where, $ops);
        $this->default_prefix();
        return $a;
    }

    public function select($select, $from, $where = array(), $ops = array())
    {
        $this->my_prefix();
        $a = parent::select($select, $from, $where, $ops);
        $this->default_prefix();
        return $a;
    }

    public function default_data($table, $other_table = [])
    {
        $this->my_prefix();
        $a = parent::default_data($table, $other_table);
        $this->default_prefix();
        return $a;
    }
}