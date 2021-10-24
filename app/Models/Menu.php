<?php

namespace App\ Models;

//
use App\ Libraries\ MenuType;
use App\ Libraries\ LanguageCost;
use App\ Libraries\ PostType;

class Menu extends EB_Model {
    function __construct() {
        parent::__construct();

        $this->post_model = new\ App\ Models\ Post();
    }

    // chức năng lấy menu để hiển thị, đồng thời tự tạo menu nếu chưa có
    function get_dynamic_menu( $slug, $menu_type, $tbl = 'wp_posts', $auto_install = true ) {
        $lang = LanguageCost::lang_key();
        //echo $slug . '<br>' . "\n";

        // select dữ liệu từ 1 bảng bất kỳ
        $sql = $this->base_model->select( '*', $tbl, array(
            // các kiểu điều kiện where
            'post_type' => PostType::MENU,
            'post_status' => 'publish',
            'post_name' => $slug,
            'lang_key' => $lang
        ), array(
            'order_by' => array(
                'ID' => 'DESC'
            ),
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 2,
            'limit' => 1
        ) );
        //print_r( $sql );

        // nếu không có -> tạo luôn 1 menu mẫu để admin chỉnh sửa sau
        if ( empty( $sql ) ) {
            if ( $auto_install === true ) {
                $data_insert = [
                    'post_title' => str_replace( '-', ' ', $slug ),
                    'post_name' => $slug,
                    'post_type' => PostType::MENU,
                    'post_status' => 'publish',
                    'lang_key' => $lang,
                    'post_content' => '<ul><li>Menu mẫu #' . $slug . '</li></ul>'
                ];
                //print_r( $data_insert );

                // nếu không phải ngôn ngữ mặc định -> copy từ ngôn ngữ mặc định qua nếu có
                if ( $lang != LanguageCost::default_lang() ) {
                    $sql = $this->base_model->select( '*', $tbl, array(
                        // các kiểu điều kiện where
                        'post_type' => PostType::MENU,
                        'post_status' => 'publish',
                        'post_name' => $slug,
                        'lang_key' => LanguageCost::default_lang()
                    ), array(
                        'order_by' => array(
                            'ID' => 'DESC'
                        ),
                        // hiển thị mã SQL để check
                        //'show_query' => 1,
                        // trả về câu query để sử dụng cho mục đích khác
                        //'get_query' => 1,
                        //'offset' => 2,
                        'limit' => 1
                    ) );
                    //print_r( $sql );
                    if ( !empty( $sql ) ) {
                        $data_insert[ 'post_content' ] = $sql[ 'post_content' ];
                        $data_insert[ 'post_excerpt' ] = $sql[ 'post_excerpt' ];
                        $data_insert[ 'lang_parent' ] = $sql[ 'ID' ];
                        //print_r( $data_insert );
                    }
                    //die( 'df dhdassa' );
                }

                $insert_id = $this->post_model->insert_post( $data_insert );
                //echo $insert_id . '<br>' . "\n";

                if ( $insert_id > 0 ) {
                    return $this->get_dynamic_menu( $slug, $menu_type, $tbl, false );
                }
            } else {
                die( 'ERROR auto create new menu #' . PostType::MENU . ':' . basename( __FILE__ ) . ':' . __LINE__ );
            }

            //
            return 'ERROR create menu #' . $slug;
        }

        //
        return $sql;
    }

    function get_the_menu( $slug, $add_class = '' ) {
        //echo MenuType::MENU . '<br>' . "\n";

        $data = $this->get_dynamic_menu( $slug, MenuType::MENU );
        //print_r( $data );

        $menu_content = $data[ 'post_content' ];

        // thay thế mã cho font awesome
        $menu_content = str_replace( '[i ', '<i ', $menu_content );
        $menu_content = str_replace( '][/i]', '></i>', $menu_content );
        $menu_content = str_replace( '&quot;', '"', $menu_content );
        $menu_content = str_replace( 'href="#"', 'href="javascript:;"', $menu_content );

        //
        return '<div data-id="' . $data[ 'ID' ] . '" data-type="' . PostType::MENU . '" class="eb-sub-menu ' . $slug . ' ' . $add_class . '">' . $menu_content . '</div>';
    }

    function the_menu( $slug, $add_class = '' ) {
        echo $this->get_the_menu( $slug, $add_class );
    }


    function get_slider( $slug, $add_class = '' ) {
        //echo MenuType::MENU . '<br>' . "\n";

        $menu_content = $this->get_dynamic_menu( $slug, MenuType::SLIDER );

        // thay thế mã cho font awesome
        //$menu_content = str_replace( '[i ', '<i ', $menu_content );
        //$menu_content = str_replace( '][/i]', '></i>', $menu_content );
        //$menu_content = str_replace( '&quot;', '"', $menu_content );

        //
        return '<div class="htv-text-slider ' . $slug . ' ' . $add_class . '">' . $menu_content . '</div>';
    }
}