<?php

namespace App\ Models;

class Comment extends EbModel {
    public $table = WGR_TABLE_PREFIX . 'comments';
    public $primaryKey = 'comment_ID';

    public $metaTable = WGR_TABLE_PREFIX . 'commentmeta';
    //public $metaKey = 'meta_id';

    public function __construct() {
        parent::__construct();

        $this->request = \Config\ Services::request();
    }

    public function insert_comments( $data ) {
        $data_default = [
            //'comment_author_url' => $redirect_to,
            'comment_author_IP' => $this->request->getIPAddress(),
            'comment_date' => date( 'Y-m-d H:i:s' ),
            'comment_content' => '',
            'comment_agent' => $_SERVER[ 'HTTP_USER_AGENT' ],
            //'comment_type' => $ops[ 'comment_type' ],
            'user_id' => 0,
            'time_order' => time(),
        ];
        $data_default[ 'comment_date_gmt' ] = $data_default[ 'comment_date' ];
        foreach ( $data_default as $k => $v ) {
            if ( !isset( $data[ $k ] ) ) {
                $data[ $k ] = $v;
            }
        }

        //
        $result_id = $this->base_model->insert( $this->table, $data, true );

        //
        if ( $result_id > 0 ) {
            // tính lại tổng số comment cho bài viết
            if ( isset( $data[ 'comment_post_ID' ] ) && $data[ 'comment_post_ID' ] > 0 ) {
                //
                $comment_count = $this->base_model->select( 'COUNT(comment_ID) AS c', $this->table, array(
                    'comment_post_ID' => $data[ 'comment_post_ID' ],
                ) );
                $comment_count = $comment_count[ 0 ][ 'c' ];

                // update
                $post_model = new\ App\ Models\ Post();

                $this->base_model->update_multiple( $post_model->table, [
                    // SET
                    'comment_count' => $comment_count,
                ], [
                    // WHERE
                    $post_model->primaryKey => $data[ 'comment_post_ID' ],
                ] );
            }
        }
    }

    public function insert_meta_comments( $data ) {
        return $this->base_model->insert( $this->metaTable, $data, true );
    }
}