<?php

namespace App\Controllers\Sadmin;

// Libraries
use App\Libraries\TaxonomyType;

//
class Coupons extends Terms
{
    protected $taxonomy = TaxonomyType::SHOP_COUPON;

    public function __construct()
    {
        $this->controller_slug = TaxonomyType::controllerList(TaxonomyType::SHOP_COUPON);

        //
        parent::__construct();
    }
}
