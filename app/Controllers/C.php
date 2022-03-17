<?php
namespace App\ Controllers;

//
class C extends Home {
    public function __construct() {
        parent::__construct();
    }

    public function custom_taxonomy( $taxonomy_type, $id, $slug = '', $page_name = '', $page_num = 1 ) {
        //echo $taxonomy_type . '<br>' . "\n";
        //echo $id . '<br>' . "\n";
        //echo $slug . '<br>' . "\n";
        //echo $page_num . '<br>' . "\n";

        //
        return $this->showCategory( $id, $taxonomy_type, $page_num );
    }
}