<?php

/**
 * Giả lập feed của wordpress
 **/

namespace App\Controllers;

// Libraries
//use App\Libraries\UsersType;

//
class Comments extends Layout
{
    // chức năng này không cần nạp header
    //public $preload_header = false;

    public function __construct()
    {
        parent::__construct();
    }

    public function feed()
    {
        // reset lại view -> tránh in ra phần html nếu lỡ nạp
        ob_end_clean();

        //
        header("Content-type: text/xml");

        $xml_content = file_get_contents(VIEWS_PATH . '/feed_comments_layout.xml');
        foreach ([
            'base_url' => DYNAMIC_BASE_URL,
            'wp_version' => FAKE_WORDPRESS_VERSION,
            'name' => $this->getconfig->name,
            'description' => $this->getconfig->description,
            'web_favicon' => $this->getconfig->web_favicon,
            'last_build' => date('r', strtotime(date('Y-m-d H') . ':33:33')),
        ] as $k => $v) {
            $xml_content = str_replace('{' . $k . '}', $v, $xml_content);
        }

        //
        die($xml_content);
    }
}
