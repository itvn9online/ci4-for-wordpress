<?php

namespace App\Models;

//
use App\Libraries\MenuType;
use App\Libraries\LanguageCost;
use App\Libraries\PostType;

class Menu extends Post
{
    protected $post_type = PostType::MENU;

    public function __construct()
    {
        parent::__construct();
    }

    // chức năng lấy menu để hiển thị, đồng thời tự tạo menu nếu chưa có
    function get_dynamic_menu($slug, $menu_type, $tbl = 'posts', $auto_install = true)
    {
        $lang = LanguageCost::lang_key();
        //echo $slug . '<br>' . PHP_EOL;

        // select dữ liệu từ 1 bảng bất kỳ
        $sql = $this->base_model->select(
            '*',
            $this->table,
            array(
                // các kiểu điều kiện where
                'post_type' => $this->post_type,
                'post_status' => PostType::PUBLICITY,
                'post_name' => $slug,
                'lang_key' => $lang
            ),
            array(
                'order_by' => array(
                    'ID' => 'DESC'
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 2,
                'limit' => 1
            )
        );
        //print_r($sql);

        // nếu không có -> tạo luôn 1 menu mẫu để admin chỉnh sửa sau
        if (empty($sql)) {
            if ($auto_install === true) {
                $data_insert = [
                    'post_title' => str_replace('-', ' ', $slug),
                    'post_name' => $slug,
                    'post_type' => $this->post_type,
                    'post_status' => PostType::PUBLICITY,
                    'lang_key' => $lang,
                    'post_content' => '<ul><li>Menu mẫu #' . $slug . '</li></ul>'
                ];
                //print_r( $data_insert );

                // nếu không phải ngôn ngữ mặc định -> copy từ ngôn ngữ mặc định qua nếu có
                if ($lang != LanguageCost::default_lang()) {
                    $sql = $this->base_model->select(
                        '*',
                        $this->table,
                        array(
                            // các kiểu điều kiện where
                            'post_type' => $this->post_type,
                            'post_status' => PostType::PUBLICITY,
                            'post_name' => $slug,
                            'lang_key' => LanguageCost::default_lang()
                        ),
                        array(
                            'order_by' => array(
                                'ID' => 'DESC'
                            ),
                            // hiển thị mã SQL để check
                            //'show_query' => 1,
                            // trả về câu query để sử dụng cho mục đích khác
                            //'get_query' => 1,
                            //'offset' => 2,
                            'limit' => 1
                        )
                    );
                    //print_r($sql);
                    if (!empty($sql)) {
                        $data_insert['post_content'] = $sql['post_content'];
                        $data_insert['post_excerpt'] = $sql['post_excerpt'];
                        $data_insert['lang_parent'] = $sql['ID'];
                        //print_r($data_insert);
                    }
                    //die(__CLASS__ . ':' . __LINE__);
                }

                $result_id = $this->insert_post($data_insert);
                if (is_array($result_id) && isset($result_id['error'])) {
                    die($result_id['error'] . ' in ' . __CLASS__ . ':' . __LINE__);
                } else {
                    return $this->get_dynamic_menu($slug, $menu_type, $this->table, false);
                }
                //print_r($result_id);
                //die(__CLASS__ . ':' . __LINE__);
            } else {
                die('ERROR auto create new menu #' . $this->post_type . ':' . __CLASS__ . ':' . __LINE__);
            }

            //
            return 'ERROR create menu #' . $slug;
        }

        //
        return $sql;
    }

    function get_the_menu($slug, $add_class = '', $using_cache = true, $time = MEDIUM_CACHE_TIMEOUT)
    {
        //echo MenuType::MENU . '<br>' . PHP_EOL;

        //
        $in_cache = '';
        if ($using_cache === true) {
            $in_cache = str_replace(' ', '-', $add_class);
            $in_cache = __FUNCTION__ . '-' . $slug . '-' . $in_cache . '-' . LanguageCost::lang_key();
        }

        //
        if ($in_cache != '') {
            $cache_value = $this->base_model->scache($in_cache);

            // có cache thì trả về
            if ($cache_value !== NULL) {
                //print_r( $cache_value );
                return $cache_value;
            }
        }

        //
        $data = $this->get_dynamic_menu($slug, MenuType::MENU);
        //print_r( $data );

        $menu_content = $data['post_content'];

        // thay thế mã cho font awesome
        foreach ([
            '[i ' => '<i ',
            '][/i]' => '></i>',
            '&quot;' => '"',
            'href="#"' => 'href="javascript:;"',
            '../' => './',
            '././' => './',
        ] as $k => $v) {
            $menu_content = str_replace($k, $v, $menu_content);
        }

        //
        $str = '<div data-id="' . $data['ID'] . '" data-type="' . $this->post_type . '" class="eb-sub-menu ' . $slug . ' ' . $add_class . '">' . $menu_content . '</div>';

        //
        if ($in_cache != '') {
            $this->base_model->scache($in_cache, $str, $time);
        }
        return $str;
    }

    function the_menu($slug, $add_class = '', $using_cache = true, $time = MEDIUM_CACHE_TIMEOUT)
    {
        echo $this->get_the_menu($slug, $add_class, $using_cache, $time);
    }


    function get_slider($slug, $add_class = '')
    {
        //echo MenuType::MENU . '<br>' . PHP_EOL;

        $menu_content = $this->get_dynamic_menu($slug, MenuType::SLIDER);

        // thay thế mã cho font awesome
        //$menu_content = str_replace( '[i ', '<i ', $menu_content );
        //$menu_content = str_replace( '][/i]', '></i>', $menu_content );
        //$menu_content = str_replace( '&quot;', '"', $menu_content );

        //
        return '<div class="htv-text-slider ' . $slug . ' ' . $add_class . '">' . $menu_content . '</div>';
    }
}
