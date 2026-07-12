<?php

namespace App\Controllers\Traits;

//
trait LayoutInputTrait
{
    protected function MY_data($a, $default_value = '', $xss_clean = true)
    {
        // với kiểu chuỗi -> so sánh lấy chuỗi trống
        if (is_string($a) && $a == '') {
            return $default_value;
        } else if (is_numeric($a)) {
            return $a;
        } else if (empty($a)) {
            return $default_value;
        }

        //
        return $a;
    }
    protected function MY_get($key, $default_value = '', $xss_clean = true)
    {
        return $this->MY_data($this->request->getGet($key), $default_value, $xss_clean);
    }
    protected function MY_post($key, $default_value = '', $xss_clean = true)
    {
        return $this->MY_data($this->request->getPost($key), $default_value, $xss_clean);
    }
}
