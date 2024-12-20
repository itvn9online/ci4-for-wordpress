<?php

namespace App\Models;

// 
use App\Libraries\LanguageCost;

// 
class Comment extends EbModel
{
    public $table = 'comments';
    public $primaryKey = 'comment_ID';

    public $metaTable = 'commentmeta';
    //public $metaKey = 'meta_id';
    public $request = null;

    public function __construct()
    {
        parent::__construct();

        $this->request = \Config\Services::request();
    }

    protected function sync_comment_data($data)
    {
        if (!isset($data['comment_title']) || $data['comment_title'] == '') {
            if (isset($data['comment_content']) && $data['comment_content'] != '') {
                $data['comment_title'] = strip_tags($data['comment_content']);
                $data['comment_title'] = explode(PHP_EOL, $data['comment_title']);
                $data['comment_title'] = trim($data['comment_title'][0]);
                $data['comment_title'] = $this->base_model->short_string($data['comment_title'], 75);
            }
        }

        //
        if (isset($data['comment_title']) && $data['comment_title'] != '') {
            $data['comment_title'] = $this->base_model->short_string($data['comment_title'], 110);

            //
            $data['comment_slug'] = $this->base_model->_eb_non_mark_seo($data['comment_title']);
        }

        //
        return $data;
    }

    public function insert_comments($data, $ops = [])
    {
        $data_default = [
            //'comment_author_url' => $redirect_to,
            'comment_author_IP' => $this->base_model->getIPAddress(),
            'comment_date' => date(EBE_DATETIME_FORMAT),
            'comment_title' => '',
            'comment_content' => '',
            'comment_agent' => $_SERVER['HTTP_USER_AGENT'],
            // 'comment_type' => $ops['comment_type'],
            'comment_approved' => DEFAULT_COMMENT_APPROVED,
            'user_id' => 0,
            'time_order' => time(),
            'created_source' => implode(' - ', [
                // isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null,
                isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null,
                isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null,
                // isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null,
            ]),
        ];

        // 
        $data_default['comment_date_gmt'] = $data_default['comment_date'];
        foreach ($data_default as $k => $v) {
            if (!isset($data[$k])) {
                $data[$k] = $v;
            }
        }

        // nếu có yêu cầu kiểm tra spam qua ip thì kiểm tra thôi
        if (isset($ops['check_spam_ip']) && is_numeric($ops['check_spam_ip']) && $ops['check_spam_ip'] > 0) {
            // tính số lượng bản ghi
            $totalThread = $this->base_model->select_count(
                'comment_ID',
                $this->table,
                array(
                    // kiểm tra dữ liệu cùng ip này
                    'comment_author_IP' => $data['comment_author_IP'],
                    // 'comment_type' => $data['comment_type'],
                    // trong vòng 1 giờ
                    'time_order >' => time() - 3600,
                ),
                array(
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
            // print_r($totalThread);
            // nếu số bản ghi trả về mà vượt quá số này
            if ($totalThread > $ops['check_spam_ip']) {
                // thì block thôi
                // die(__CLASS__ . ':' . __LINE__);
                die(json_encode([
                    'code' => __LINE__,
                    'error' => 'Oh my banana!',
                ]));
            }
        }

        //
        $data = $this->sync_comment_data($data);
        if (!isset($data['lang_key']) || $data['lang_key'] == '') {
            $data['lang_key'] = LanguageCost::lang_key();
        }
        //print_r($data);
        //die(__CLASS__ . ':' . __LINE__);

        //
        $result_id = $this->base_model->insert($this->table, $data, true);

        //
        if ($result_id !== false) {
            // tính lại tổng số comment cho bài viết
            if (isset($data['comment_post_ID']) && $data['comment_post_ID'] > 0) {
                //
                $comment_count = $this->base_model->select_count('comment_ID', $this->table, [
                    'comment_post_ID' => $data['comment_post_ID'],
                ]);

                // update
                $post_model = new \App\Models\Post();

                $this->base_model->update_multiple($post_model->table, [
                    // SET
                    'comment_count' => $comment_count,
                ], [
                    // WHERE
                    $post_model->primaryKey => $data['comment_post_ID'],
                ]);
            }
        }
        return $result_id;
    }

    public function update_comments($comment_ID, $data, $where = [])
    {
        $where['comment_ID'] = $comment_ID;

        //
        $data = $this->sync_comment_data($data);
        //print_r( $data );
        //return false;

        //
        $result_update = $this->base_model->update_multiple($this->table, $data, $where, [
            'debug_backtrace' => debug_backtrace()[1]['function']
        ]);

        //
        return $result_update;
    }

    public function insert_meta_comments($data)
    {
        return $this->base_model->insert($this->metaTable, $data, true);
    }
}
