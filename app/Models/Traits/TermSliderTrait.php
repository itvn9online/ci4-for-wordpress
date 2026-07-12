<?php

namespace App\Models\Traits;



//
trait TermSliderTrait
{
    public function get_the_slider($taxonomy_slider, $second_slider = '')
    {
        //print_r( $taxonomy_slider );
        if (empty($taxonomy_slider)) {
            return '';
        }

        // -> chạy vòng lặp để tìm slider theo danh mục gần nhất -> con không có thì tìm cha
        foreach ($taxonomy_slider as $slider) {
            if (isset($slider['term_meta']['taxonomy_auto_slider']) && $slider['term_meta']['taxonomy_auto_slider'] == 'on') {
                //echo 'taxonomy_auto_slider';

                $slug = $slider['slug'] . '-' . $slider['taxonomy'] . '-' . $slider['term_id'];
                return $slug;

                /*
                return $this->post_model->get_the_ads( $slug, 0, [
                'add_class' => 'taxonomy-auto-slider'
                ] );
                */
                break;
            }
        }

        // đến đây vẫn không có -> tìm slider thứ cấp (slider dùng chung cho cả website)
        /*
        if ( $second_slider != '' ) {
        return $this->post_model->get_the_ads( $second_slider, 0, [
        'add_class' => 'taxonomy-auto-slider'
        ] );
        }
        */

        //
        return '';
    }
    public function the_slider($data, $second_slider = '')
    {
        echo $this->get_the_slider($data, $second_slider);
    }
}
