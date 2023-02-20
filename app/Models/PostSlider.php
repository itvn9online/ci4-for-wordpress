<?php

namespace App\Models;

// Libraries
//use App\Libraries\PostType;

//
class PostSlider extends PostGet
{
    public function __construct()
    {
        parent::__construct();
    }

    // tự động tạo slider nếu có
    public function get_the_slider($data, $taxonomy_slider = [], $second_slider = '')
    {
        //print_r( $data );
        //print_r( $taxonomy_slider );
        if (empty($data)) {
            return '';
        }

        // ưu tiên tìm trong 
        if (isset($data['post_meta']['post_auto_slider']) && $data['post_meta']['post_auto_slider'] == 'on') {
            //echo 'post_auto_slider';
            //print_r( $data );
            return $this->get_the_ads($data['post_name'] . '-' . $data['post_type'] . '-' . $data['ID'], 0, [
                'add_class' => 'taxonomy-auto-slider'
            ]);
        } else {
            // thử tìm của bài cha nếu có
            if ($data['post_parent'] > 0) {
                $parent_data = $this->select_post($data['post_parent']);
                //print_r( $parent_data );

                // thử tìm slider của bài cha -> có thì trả về luôn
                $parent_slider = $this->get_the_slider($parent_data);
                if ($parent_slider != '') {
                    return $parent_slider;
                }
            }
            //echo $taxonomy_slider . '<br>';
            //echo $second_slider . '<br>';

            // không có -> sử dụng của taxonomy
            $tax_slider = $this->term_model->get_the_slider($taxonomy_slider, $second_slider);
            if (!empty($tax_slider)) {
                $tax_slider = $this->get_the_ads($tax_slider, 0, [
                    'add_class' => 'taxonomy-auto-slider'
                ]);
                if (!empty($tax_slider)) {
                    return $tax_slider;
                }
            }

            // đến đây vẫn không có -> tìm slider thứ cấp (slider dùng chung cho cả website)
            //$second_slider = 'top-main-slider'; // main_slider_slug
            //echo $second_slider . '<br>';
            if ($second_slider != '') {
                return $this->get_the_ads($second_slider, 0, [
                    'add_class' => 'taxonomy-auto-slider'
                ]);
            }
        }

        //
        return '';
    }
    public function the_slider($data, $taxonomy_slider = [], $second_slider = '')
    {
        echo $this->get_the_slider($data, $taxonomy_slider, $second_slider);
    }
}
