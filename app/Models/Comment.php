<?php

namespace App\Models;

class Comment extends EbModel
{
    public $table = 'comments';
    public $primaryKey = 'comment_ID';

    public $metaTable = 'commentmeta';
    //public $metaKey = 'meta_id';

    public function __construct()
    {
        parent::__construct();

        $this->request = \Config\Services::request();
    }

    private function sync_comment_data($data)
    {
        if (!isset($data['comment_title']) || $data['comment_title'] == '') {
            if (isset($data['comment_content']) && $data['comment_content'] != '') {
                $data['comment_title'] = strip_tags($data['comment_content']);
                $data['comment_title'] = explode(PHP_EOL, $data['comment_title']);
                $data['comment_title'] = trim($data['comment_title'][0]);
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

    public function insert_comments($data)
    {
        $data_default = [
            //'comment_author_url' => $redirect_to,
            'comment_author_IP' => $this->request->getIPAddress(),
            'comment_date' => date(EBE_DATETIME_FORMAT),
            'comment_title' => '',
            'comment_content' => '',
            'comment_agent' => $_SERVER['HTTP_USER_AGENT'],
            //'comment_type' => $ops[ 'comment_type' ],
            'user_id' => 0,
            'time_order' => time(),
        ];
        $data_default['comment_date_gmt'] = $data_default['comment_date'];
        foreach ($data_default as $k => $v) {
            if (!isset($data[$k])) {
                $data[$k] = $v;
            }
        }

        //
        $data = $this->sync_comment_data($data);
        //print_r($data);
        //die(__CLASS__ . ':' . __LINE__);

        //
        $result_id = $this->base_model->insert($this->table, $data, true);

        //
        if ($result_id !== false) {
            // tính lại tổng số comment cho bài viết
            if (isset($data['comment_post_ID']) && $data['comment_post_ID'] > 0) {
                //
                $comment_count = $this->base_model->select('COUNT(comment_ID) AS c', $this->table, array(
                    'comment_post_ID' => $data['comment_post_ID'],
                ), [
                    'selectCount' => 'comment_ID',
                ]);
                //$comment_count = $comment_count[ 0 ][ 'c' ];
                $comment_count = $comment_count[0]['comment_ID'];

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
