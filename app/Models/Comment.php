<?php

namespace App\ Models;

class Comment extends EB_Model {
    public $tbl = 'wp_comments';
    public $metatbl = 'wp_commentmeta';

    function __construct() {
        parent::__construct();
    }

    public function insert_comments( $data ) {
        return $this->base_model->insert( $this->tbl, $data, true );
    }

    public function insert_meta_comments( $data ) {
        return $this->base_model->insert( $this->metatbl, $data, true );
    }
}