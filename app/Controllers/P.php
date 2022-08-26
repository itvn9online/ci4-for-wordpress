<?php
namespace App\ Controllers;

//
class P extends Home {
    public function __construct() {
        parent::__construct();
    }

    public function custom_post_type( $post_type, $id, $slug = '' ) {
        //echo $post_type . '<br>' . "\n";
        //echo $id . '<br>' . "\n";
        //echo $slug . '<br>' . "\n";

        //
        return $this->showPostDetails( $id, $post_type, $slug );
    }
}