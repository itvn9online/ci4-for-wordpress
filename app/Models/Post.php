<?php

namespace App\ Models;

// Libraries
//use App\ Libraries\ LanguageCost;
//use App\ Libraries\ PostType;
//use App\ Libraries\ TaxonomyType;
//use App\ Libraries\ DeletedStatus;

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
}