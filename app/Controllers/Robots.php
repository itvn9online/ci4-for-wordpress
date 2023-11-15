<?php

namespace App\Controllers;

// Libraries
// use App\Libraries\PostType;

//
class Robots extends Layout
{
    // chức năng này không cần nạp header
    public $preload_header = false;

    public function __construct()
    {
        parent::__construct();

        //
        $this->web_link = DYNAMIC_BASE_URL;
        if (SITE_LANGUAGE_DEFAULT != $this->lang_key) {
            $this->web_link .= $this->lang_key . '/';
        }
    }

    public function index()
    {
        // reset lại view -> tránh in ra phần html nếu lỡ nạp
        ob_end_clean();

        //
        header('Content-Type: text/plain; charset=UTF-8');

        //
        $c = $this->getconfig->robots;
        $c = str_replace('{{base_url}}', $this->web_link, $c);
        $c = str_replace('%base_url%', $this->web_link, $c);

        //
        echo $c;

        //
        exit();
    }
}
