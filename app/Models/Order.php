<?php

namespace App\ Models;

// Libraries
use App\ Libraries\ OrderType;

//
class Order extends Post {
    public $post_type = OrderType::ORDER;

    //
    public function __construct() {
        parent::__construct();
    }

    public function insert_order( $data_insert ) {
        $data_insert[ 'post_status' ] = OrderType::PENDING;
        $data_insert[ 'post_type' ] = $this->post_type;

        //
        if ( !isset( $data_insert[ 'post_name' ] ) || $data_insert[ 'post_name' ] == '' ) {
            if ( isset( $data_insert[ 'post_author' ] ) && $data_insert[ 'post_author' ] > 0 ) {
                $data_insert[ 'post_name' ] = $data_insert[ 'post_author' ] . '-' . date( 'YmdHis' );
            }
        }

        //
        return parent::insert_post( $data_insert );
    }

    public function get_order( $where = [], $filter = [] ) {
        $where[ 'post_type' ] = $this->post_type;

        //
        return $this->base_model->select( '*', 'posts', $where, $filter );
    }

    public function update_order( $data, $where = [], $filter = [] ) {
        $where[ 'post_type' ] = $this->post_type;

        //
        return $this->base_model->update_multiple( $data, $where, $filter );
    }
}