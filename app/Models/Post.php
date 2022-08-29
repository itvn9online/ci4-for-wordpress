<?php
namespace App\ Models;

// Libraries
//use App\ Libraries\ PostType;

//
class Post extends PostPosts {
    public function __construct() {
        parent::__construct();
    }

    /*
     * cập nhật lượt xem cho post
     */
    public function update_views( $id ) {
        //echo __FUNCTION__ . '<br>' . "\n";
        //echo $id . '<br>' . "\n";

        //
        $this->base_model->update_count( $this->table, 'post_viewed', array(
            // WHERE
            'ID' => $id,
        ), [
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
        ] );
    }

    // vì permalink gán trực tiếp vào db nên thi thoảng sẽ check lại chút
    public function sync_post_term_permalink() {
        //
    }
}