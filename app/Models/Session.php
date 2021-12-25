<?php

namespace App\ Models;

//use CodeIgniter\ Model;

class Session {
    public function __construct() {
        //
    }

    public function MY_session( $key, $value = NULL ) {
        if ( $value !== NULL ) {
            $_SESSION[ $key ] = $value;
            return true;
        }
        return isset( $_SESSION[ $key ] ) ? $_SESSION[ $key ] : '';
    }
}