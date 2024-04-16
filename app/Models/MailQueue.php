<?php

namespace App\Models;

// Libraries
use App\Libraries\PostType;
// use App\Libraries\TaxonomyType;
// use App\Libraries\DeletedStatus;
use App\Libraries\PHPMaillerSend;

//
class MailQueue extends EbModel
{
    public $table = 'mail_queue';
    public $primaryKey = 'id';
    public $request = null;
    public $post_model = null;
    public $option_model = null;

    public function __construct()
    {
        parent::__construct();

        // 
        $this->request = \Config\Services::request();
        $this->post_model = new \App\Models\Post();
        $this->option_model = new \App\Models\Option();
    }

    /**
     * Thêm mới 1 email vào hàng đợi gửi đi
     **/
    public function insert_mailq($data, $add_data = [])
    {
        // dữ liệu mặc định
        foreach ([
            'ip' => $this->request->getIPAddress(),
            'session_id' => $this->base_model->MY_sessid(),
            'agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null,
            'mailto' => null,
            'title' => null,
            'content' => null,
            'status' => PostType::PENDING,
            'post_id' => 0,
            'order_id' => 0,
            'created_at' => time(),
        ] as $k => $v) {
            if (!isset($data[$k])) {
                $data[$k] = $v;
            }
        }

        // dữ liệu cố định (nếu có)
        foreach ($add_data as $k => $v) {
            $data[$k] = $v;
        }

        // nếu thiếu các thông số bắt buộc
        if (empty($data['mailto']) || empty($data['title']) || empty($data['content'])) {
            // thì chuyển trạng thái thành NHÁP -> mail sẽ không được gửi đi
            $data['status'] = PostType::DRAFT;
            // return false;
        }

        // 
        return $this->base_model->insert($this->table, $data);
    }

    /**
     * Cập nhật email (thường dùng khi đã gửi xong 1 email)
     **/
    public function update_mailq($id, $data)
    {
        return $this->base_model->update_multiple($this->table, $data, [
            // WHERE
            $this->primaryKey => $id,
        ], [
            'debug_backtrace' => debug_backtrace()[1]['function'],
            // hiển thị mã SQL để check
            // 'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            // 'get_query' => 1,
            // mặc định sẽ remove các field không có trong bảng, nếu muốn bỏ qua chức năng này thì kích hoạt no_remove_field
            //'no_remove_field' => 1
        ]);
    }

    /**
     * Lấy email trong hàng đợi
     **/
    public function get_pending_mailq($where = [], $filter = [])
    {
        $where['status'] = PostType::PENDING;

        // 
        return $this->get_mailq($where, $filter);
    }

    /**
     * Lấy email theo điều kiện where truyền vào
     **/
    public function get_mailq($add_where = [], $add_filter = [])
    {
        // 
        $where = [
            // mặc định chỉ lấy trong vòng 24h trở lại
            'created_at >' => time() - DAY,
        ];
        foreach ($add_where as $k => $v) {
            $where[$k] = $v;
        }

        // 
        $filter = [
            'order_by' => array(
                $this->primaryKey => 'ASC',
            ),
            // hiển thị mã SQL để check
            // 'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            // trả về COUNT(column_name) AS column_name
            //'selectCount' => 'ID',
            // trả về tổng số bản ghi -> tương tự mysql num row
            //'getNumRows' => 1,
            //'offset' => 0,
            'limit' => 1
        ];
        foreach ($add_filter as $k => $v) {
            $filter[$k] = $v;
        }

        // 
        return $this->base_model->select(
            '*',
            $this->table,
            $where,
            $filter
        );
    }

    /**
     * lấy và gửi email theo session hiện tại của người dùng
     **/
    public function session_sending_mailq()
    {
        $data = $this->get_pending_mailq([
            'session_id' => $this->base_model->MY_sessid(),
            'status' => PostType::PENDING,
        ], [
            'limit' => 10,
        ]);

        // 
        if (empty($data)) {
            return false;
        }

        // 
        $smtp_config = $this->option_model->get_smtp();
        //print_r($smtp_config);

        // 
        $result = [];
        foreach ($data as $v) {
            $result[] = $v['mailto'];

            // 
            $sended = PHPMaillerSend::the_send([
                'to' => $v['mailto'],
                'subject' => $v['title'],
                'message' => $v['content'],
            ], $smtp_config);

            // 
            if ($sended === true) {
                $result[] = true;

                // 
                $this->update_mailq($v['id'], [
                    'status' => PostType::PRIVATELY,
                    // 'comment' => null,
                ]);
            } else {
                $result[] = false;

                // 
                $this->update_mailq($v['id'], [
                    'status' => PostType::DRAFT,
                    'comment' => 'Mail send faild!',
                ]);
            }
        }

        // 
        return $result;
    }

    /**
     * trả về nội dung của mail template
     **/
    public function content_mailq($key = '')
    {
        $slug = 'booking-done-mail';
        if ($key != '') {
            $slug .= '-' . $key;
        }

        // có trong cache thì lấy trong cache -> dùng key get_the_ads để cache được clear mỗi khi update ads
        $in_cache = 'get_the_ads-' . $slug;
        $data = $this->base_model->scache($in_cache);
        if ($data !== null) {
            return $data;
        }

        // chưa có thì kiểm tra trong db
        $data = $this->base_model->select(
            'post_title AS title, post_content AS content',
            'posts',
            array(
                // các kiểu điều kiện where
                'post_type' => PostType::ADS,
                'post_status' => PostType::PRIVATELY,
                'post_name' => $slug,
            ),
            array(
                'order_by' => array(
                    'ID' => 'DESC',
                ),
                // hiển thị mã SQL để check
                // 'show_query' => 1,
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

        // 
        if (empty($data)) {
            $this->post_model->insert_post([
                'post_type' => PostType::ADS,
                'post_status' => PostType::PRIVATELY,
                'post_name' => $slug,
                'post_title' => str_replace('-', ' ', $slug),
                'post_content' => $slug . ' auto create mail template in post type: ' . PostType::ADS,
            ]);

            // 
            return [
                'title' => null,
                'content' => null,
            ];
        }
        $this->base_model->scache($in_cache, $data, HOUR);

        // 
        return $data;
    }
}
