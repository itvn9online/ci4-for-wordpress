<?php

namespace App\Controllers\Traits;

//
trait LayoutCacheTrait
{
    protected function global_cache($key, $value = '', $time = MINI_CACHE_TIMEOUT)
    {
        $key .= $this->cache_mobile_key . '-' . $this->lang_key;

        //
        return $this->base_model->scache($key, $value, $time);
    }

    // kiểm tra session của user, nếu đang đăng nhập thì bỏ qua chế độ cache
    protected function MY_cache($key, $value = '', $time = MINI_CACHE_TIMEOUT)
    {
        // không thực thi cache đối với tài khoản đang đăng nhập
        if (MY_CACHE_HANDLER == 'disable' || $this->current_user_id > 0 || isset($_GET['set_lang'])) {
            return null;
        }

        //
        return $this->global_cache($key, $value, $time);
    }

    // hiển thị nội dung từ cache -> thêm 1 số đoạn comment HTML vào
    protected function show_cache($content, $key = '')
    {
        echo $content;

        //
        if (MY_CACHE_HANDLER == 'disable') {
            echo '<!-- Cached is disabled -->' . "\n";
        } else {
            echo '<!-- Cached by ebcache with key ' . CACHE_HOST_PREFIX . ':' . $key . "\n";
            if (MY_CACHE_HANDLER == 'file') {
                echo 'Caching using hard disk drive. Recommendations using SSD drive for your website.' . "\n";
            } else {
                echo 'How wonderful! Caching using ' . MY_CACHE_HANDLER . ' handler.' . "\n";
            }
            echo 'Compression = gzip -->';
        }

        //
        return true;
    }

    // chỉ gọi đến chức năng nạp header, footer khi cần hiển thị
}
