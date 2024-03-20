<?php

namespace App\Libraries;

//
class OrderType extends PostType
{
    public function __construct()
    {
        parent::__construct();
    }

    public static function arrStatus()
    {
        return array(
            //self::PUBLICITY => 'Hiển thị',
            self::PENDING => 'Pending',
            self::ON_HOLD => 'On hold',
            self::PRIVATELY => 'Processing',
            self::INHERIT => 'Completed',
            self::DRAFT => 'Cancelled',
            self::DELETED => 'Deleted',
        );
    }
}
