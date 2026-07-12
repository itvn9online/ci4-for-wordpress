<?php

namespace App\Controllers\Traits;

use App\Libraries\PostType;
use App\Libraries\TaxonomyType;

//
trait LayoutCategoryTrait
{
    protected function category($input, $post_type, $taxonomy, $file_view = 'category_view', $ops = [])
    {
        // xem có file view tương ứng không
        if ($file_view == '' || !is_file(VIEWS_PATH . $file_view . '.php')) {
            // không có thì hiển thị lỗi luôn
            // return $this->page404('ERROR (' . $file_view . ') ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! Bạn không có quyền xem thông tin này...');
            // $file_view = 'category_auto_view';
            $file_view = 'term_view';
        }

        //echo debug_backtrace()[1]['class'] . ':' . debug_backtrace()[1]['function'] . '<br>' . "\n";
        //$config['base_url'] = $this->term_model->get_term_permalink();
        //$config['per_page'] = 50;
        //$config['uri_segment'] = 3;

        //
        if (!isset($ops['page_num'])) {
            $ops['page_num'] = 1;
        }

        //
        if (!isset($ops['cache_key'])) {
            $this->cache_key = $this->term_model->key_cache($input['term_id']) . 'page' . $ops['page_num'];
        } else {
            $this->cache_key = $ops['cache_key'];
        }
        $cache_value = $this->MY_cache($this->cache_key);
        // Will get the cache entry named 'my_foo'
        //var_dump($cache_value);
        // có thì in ra cache là được
        //if ( $_SERVER[ 'REQUEST_METHOD' ] == 'GET' && $cache_value !== null ) {
        if ($this->hasFlashSession() === false && $cache_value !== null) {
            return $this->show_cache($cache_value, $this->cache_key);
        }

        //
        //echo 'this category <br>' . "\n";

        //
        //print_r( $input );
        $data = $input;
        $data = $this->term_model->terms_meta_post([$data]);
        $data = $data[0];

        // đầu vào (input) chính là data rồi -> không cần gọi lại
        /*
        $data = $this->term_model->get_all_taxonomy( $taxonomy, $input[ 'term_id' ], [
        'parent' => $input[ 'term_id' ],
        //'where_in' => isset( $input[ 'where_in' ] ) ? $input[ 'where_in' ] : NULL
        ] );
        */

        //
        //print_r($data);
        $data = $this->term_model->get_child_terms([$data], []);
        //print_r($data);

        $data = $data[0];
        //print_r($data);

        // nếu có lệnh redirect do sai URL
        if (isset($_GET['canonical'])) {
            //echo __CLASS__ . ':' . __LINE__;
            // xóa permalink để URL được update lại
            //$data['term_permalink'] = '';
            //$data['updated_permalink'] = 0;
            $full_link = $this->term_model->update_term_permalink($data, DYNAMIC_BASE_URL);
        } else {
            $full_link = $this->term_model->get_full_permalink($data);
        }
        //echo $full_link;

        //
        $this->term_model->update_count_post_in_term($data);

        // hiện tại chỉ hỗ trợ bản amp cho tin tức
        $amp_link = '';
        if (ENABLE_AMP_VERSION === true && $data['taxonomy'] == TaxonomyType::POSTS) {
            $amp_link = $this->base_model->amp_term_link($data, $ops['page_num']);
        }

        //
        //$this->create_breadcrumb( $data[ 'name' ] );
        $this->create_term_breadcrumb($data);
        //print_r( $this->taxonomy_slider );
        $seo = $this->base_model->term_seo($data, $full_link, $amp_link);

        // chỉnh lại thông số cho canonical
        if ($ops['page_num'] > 1) {
            // $seo['canonical'] = rtrim($seo['canonical'], '/') . '/page/' . $ops['page_num'];
            $seo['shortlink'] = rtrim($seo['shortlink'], '/') . '&page_num=' . $ops['page_num'];
            $seo['title'] .= ' - Page ' . $ops['page_num'];
        }
        //print_r($seo);

        // lấy danh sách nhóm con xem có không
        //$child_cat = $this->term_model->get_all_taxonomy( $data[ 'taxonomy' ] );
        //print_r( $child_cat );

        // lấy banner quảng cáo theo taxonomy nếu có
        $taxonomy_slider = '';
        /*
        $taxonomy_slider = $this->term_model->get_the_slider($this->taxonomy_slider);
        //echo $taxonomy_slider . '<br>' . "\n";
        if ($taxonomy_slider == '') {
            $taxonomy_slider = $this->lang_model->get_the_text('main_slider_slug', '');
        }
        //echo $taxonomy_slider . '<br>' . "\n";
        if ($taxonomy_slider != '') {
            $taxonomy_slider = $this->post_model->get_the_ads(
                $taxonomy_slider,
                0,
                [
                    'add_class' => 'taxonomy-auto-slider'
                ]
            );
        }
        */

        // -> views
        $this->teamplate['breadcrumb'] = view(
            'breadcrumb_view',
            array(
                'breadcrumb' => $this->breadcrumb
            )
        );
        if ($data['parent'] > 0) {
            $this->current_cid = $data['parent'];
            $this->current_sid = $data['term_id'];
        } else {
            $this->current_cid = $data['term_id'];
        }

        //
        //echo $file_view . '<br>' . "\n";
        $this->teamplate['main'] = view(
            $file_view,
            array(
                //'post_per_page' => $post_per_page,
                'taxonomy_post_size' => $this->taxonomy_post_size,
                //'taxonomy_slider' => $this->taxonomy_slider,
                'taxonomy_slider' => $taxonomy_slider,
                'taxonomy' => $taxonomy,
                'ops' => $ops,
                'seo' => $seo,
                'post_type' => $post_type,
                'getconfig' => $this->getconfig,
                'data' => $data,
                'current_cid' => $this->current_cid,
                'current_sid' => $this->current_sid,
            )
        );

        // nếu có flash session -> trả về view luôn
        if ($this->hasFlashSession() === true) {
            return view('layout_view', $this->teamplate);
        }
        // còn không sẽ tiến hành lưu cache
        $cache_value = view('layout_view', $this->teamplate);

        // 
        $this->MY_cache($this->cache_key, $cache_value . '<!-- Served from: ' . __FUNCTION__ . ' -->');

        //
        return $cache_value;
    }
}
