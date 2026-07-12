<?php

namespace App\Controllers\Traits;

//
trait LayoutViewTrait
{
    protected function global_header_footer()
    {
        //
        //$response = \Config\Services::response();
        //$response->removeHeader('Cache-Control');
        //$response->setHeader('Cache-Control', 'max-age=120')->appendHeader('Cache-Control', 'must-revalidate');
        //$response->setHeader('Cache-Control', 'max-age=120');

        //print_r($this->getconfig);
        $this->teamplate['header'] = view(
            'header_view',
            array(
                // các model dùng chung thì cho vào header để sau sử dụng luôn
                'base_model' => $this->base_model,
                'menu_model' => $this->menu_model,
                'htmlmenu_model' => $this->htmlmenu_model,
                'option_model' => $this->option_model,
                'post_model' => $this->post_model,
                'term_model' => $this->term_model,
                'lang_model' => $this->lang_model,
                'num_model' => $this->num_model,
                'checkbox_model' => $this->checkbox_model,
                'user_model' => $this->user_model,
                //
                //'session' => $this->session,
                'getconfig' => $this->getconfig,
                'session_data' => $this->session_data,
                'current_user_id' => $this->current_user_id,
                'current_user_type' => $this->current_user_type,
                'current_user_logged' => $this->current_user_logged,
                'current_cid' => $this->current_cid,
                'current_sid' => $this->current_sid,
                'current_pid' => $this->current_pid,
                'debug_enable' => $this->debug_enable,
                //'menu' => $menu,
                //'allurl' => $allurl,
                'isMobile' => $this->isMobile,
                'html_lang' => $this->lang_key,
                'current_search_key' => $this->MY_get('s'),
            )
        );

        //
        $this->teamplate['footer'] = view('footer_view');
        $this->teamplate['html_lang'] = $this->lang_key;

        //
        return true;
    }

    protected function create_breadcrumb($text, $url = '')
    {
        if ($url != '') {
            $this->breadcrumb_position++;

            //
            $this->breadcrumb[] = '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a href="' . $url . '" itemprop="item" title="' . str_replace('"', '', $text) . '"><span itemprop="name">' . $text . '</span></a><meta itemprop="position" content="' . $this->breadcrumb_position . '"></li>';
        } else {
            $this->breadcrumb[] = '<li>' . $text . '</li>';
        }
        //print_r( $this->breadcrumb );

        //
        return false;
    }

    protected function create_term_breadcrumb($cats)
    {
        if (empty($cats)) {
            return false;
        }
        // print_r($cats);

        // 
        $this->taxonomy_slider[] = $cats;
        $this->posts_parent_list[] = $cats['term_id'];

        //
        if ($this->taxonomy_post_size == '' && isset($cats['term_meta']['taxonomy_custom_post_size'])) {
            $this->taxonomy_post_size = $cats['term_meta']['taxonomy_custom_post_size'];
        }

        //
        if ($cats['parent'] > 0) {
            $in_cache = __FUNCTION__;
            $parent_cats = $this->term_model->the_cache($cats['parent'], $in_cache);
            if ($parent_cats === null) {
                $parent_cats = $this->term_model->get_all_taxonomy($cats['taxonomy'], $cats['parent']);

                //
                $this->term_model->the_cache($cats['parent'], $in_cache, $parent_cats);
            }
            //print_r( $parent_cats );

            $this->create_term_breadcrumb($parent_cats);
        }

        //
        return $this->create_breadcrumb($cats['name'], $this->term_model->get_full_permalink($cats));
    }

    /**
     * Kiểm tra các bản ghi trong RewriteRule để redirect cho trang 404 nếu có
     **/
    protected function rewriteRule($rules_content = '')
    {
        if ($rules_content == '') {
            // xem có file RewriteRule ko
            $rules_path = WRITEPATH . 'RewriteRule.txt';
            // ko có thì trả về false luôn
            if (!is_file($rules_path)) {
                return false;
            }
            $rules_content = file_get_contents($rules_path);
            // } else {
            //     die($rules_content);
        }

        // xác định url hiện tại
        $current_uri = $_SERVER['REQUEST_URI'];

        // xem url này có trong RewriteRule ko
        foreach (
            [
                $current_uri,
                ltrim($current_uri, '/'),
            ] as $v
        ) {
            $v = '^' . $v . '$';
            //echo $v . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . "\n";

            //
            if (strpos($rules_content, $v) === false) {
                continue;
            }
            // tách thành mảng để kiểm tra điều kiện URL -> loại bỏ phần có dấu #
            $rules_content = explode("\n", $rules_content);

            //
            foreach ($rules_content as $url) {
                $url = trim($url);

                // Không phải rewriterule -> bỏ
                if (strpos(strtolower($url), 'rewriterule') === false) {
                    //echo $url . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
                    continue;
                }

                // trống và # -> bỏ
                if (empty($url) || substr($url, 0, 1) == '#') {
                    continue;
                }
                //echo $url . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . "\n";

                // có trong chuỗi -> cắt chuỗi
                if (strpos($url, $v) !== false) {
                    //echo $url . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . "\n";

                    // xác định kiểu redirect
                    $redirect_type = 301;
                    if (strpos($url, 'R=302') !== false) {
                        $redirect_type = 302;
                    }
                    //echo $redirect_type . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . "\n";

                    //
                    $url = trim(explode($v, $url)[1]);
                    //echo $url . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
                    $url = trim(explode("[", $url)[0]);
                    //echo $url . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
                    if (strpos($url, '//') === false) {
                        $url = DYNAMIC_BASE_URL . ltrim($url, '/');
                        //echo $url . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . "\n";
                    }
                    //echo $url . ':' . __CLASS__ . ':' . __LINE__ . '<br>' . "\n";

                    // -> thực hiện redirect
                    $this->MY_redirect($url, $redirect_type);

                    //
                    break;
                }
            }
        }
        //die($current_uri . ':' . __CLASS__ . ':' . __LINE__);

        //
        return true;
    }
}
