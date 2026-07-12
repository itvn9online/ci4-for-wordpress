<?php

namespace App\Controllers\Sadmin\Traits;

use App\Libraries\CommentType;

//
trait PostsCommentTrait
{
    public function add_comments()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->base_model->alert('Bad request!', 'error');
        }

        // 
        $data = $this->MY_post('data');

        // 
        foreach (
            [
                'comment_author' => $this->current_user_id,
                'comment_author_email' => $this->session_data['user_email'],
                // 'comment_author_url' => null,
                'comment_type' => $this->comment_type,
                'user_id' => $this->current_user_id,
            ] as $k => $v
        ) {
            if (isset($data[$k]) && $data[$k] != '') {
                // nếu có thì giữ nguyên
                continue;
            }
            $data[$k] = $v;
        }

        // 
        if (isset($data['comment_ID']) && $data['comment_ID'] < 1) {
            unset($data['comment_ID']);
        }

        // 
        // print_r($data);

        // 
        $comment_ID = $this->comment_model->insert_comments($data);
        // var_dump($comment_ID);
        if ($comment_ID !== false) {
            echo '<script>top.after_update_comments(' . $comment_ID . ');</script>';
            $this->base_model->alert('Done!');
        }

        // 
        $this->base_model->alert('ERROR insert new ' . $this->comment_type . '!', 'error');
    }

    /**
     * thay đổi trạng thái của 1 bình luận
     **/
    protected function approve_change_comments($comment_approved)
    {
        $id = $this->MY_get('id', 0);
        if ($id < 1) {
            $this->base_model->alert('comment_ID not found!', 'error');
        }

        // 
        $this->comment_model->update_comments($id, [
            'comment_approved' => $comment_approved,
        ], [
            'comment_type' => $this->comment_type,
        ]);

        // 
        echo '<script>top.after_update_comments(' . $id . ');</script>';
        $this->base_model->alert('Done!');
    }

    /**
     * bỏ phê duyệt bình luận
     **/
    public function unapprove_comments()
    {
        return $this->approve_change_comments(CommentType::PENDDING);
    }

    /**
     * phê duyệt bình luận
     **/
    public function approve_comments()
    {
        return $this->approve_change_comments(CommentType::APPROVED);
    }

    /**
     * xóa bình luận
     **/
    public function trash_comments()
    {
        $id = $this->MY_get('id', 0);
        if ($id < 1) {
            $this->base_model->alert('comment_ID not found!', 'error');
        }

        // 
        $this->comment_model->update_comments($id, [
            'is_deleted' => DeletedStatus::DELETED,
        ], [
            'comment_type' => $this->comment_type,
        ]);

        // 
        echo '<script>top.after_update_comments();</script>';
        $this->base_model->alert('Done!');
    }
}
