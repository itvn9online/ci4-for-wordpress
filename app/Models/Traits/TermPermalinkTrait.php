<?php

namespace App\Models\Traits;



//
trait TermPermalinkTrait
{
    function get_admin_permalink($taxonomy = '', $id = 0, $controller_slug = 'terms')
    {
        //$url = base_url( 'sadmin/' . $controller_slug . '/add' ) . '?taxonomy=' . $taxonomy;
        $url = base_url('sadmin/' . $controller_slug . '/add');
        if ($id > 0) {
            //$url .= '&id=' . $id;
            $url .= '?id=' . $id;
        }
        return $url;
    }

    // thường dùng trong view -> in ra link admin của 1 term
    public function admin_permalink($taxonomy = '', $id = 0, $controller_slug = 'terms')
    {
        echo $this->get_admin_permalink($taxonomy, $id, $controller_slug);
    }

    // kiểm tra url đã chuẩn chưa, chưa thì redirect về url chuẩn
    public function check_canonical($slug, $data)
    {
        // nếu slug trống
        if (
            $slug == '' ||
            // hoặc đúng là slug
            $slug == $data['slug'] ||
            // hoặc kiểu URL có .html, .html, .etc...
            strpos($slug, $data['slug'] . '.') !== false ||
            // hoặc đã qua kiểm tra canonical rồi, tránh lặp redirect
            isset($_GET['canonical'])
        ) {
            // thì cho qua
            return true;
        }
        // không thì redirect về URL chuẩn
        $redirect_to = $this->get_full_permalink($data);
        //die( $redirect_to );
        if (strpos($redirect_to, '?') === false) {
            $redirect_to .= '?';
        } else {
            $redirect_to .= '&';
        }
        $redirect_to .= 'canonical=server&uri=' . urlencode($_SERVER['REQUEST_URI']);

        //
        // header('HTTP/1.1 301 Moved Permanently');
        http_response_code(301);
        die(header('Location: ' . $redirect_to, true, 301));
        //die( __CLASS__ . ':' . __LINE__ );
    }

    /**
     * Kiểm tra dữ liệu đầu vào trước khi update post permalink -> tránh lỗi
     **/
    public function before_term_permalink($data)
    {
        // nếu có đủ các thông số còn thiếu thì tiến hành cập nhật permalink
        foreach (
            [
                'term_id',
                'slug',
                'taxonomy',
                'lang_key',
            ] as $k
        ) {
            if (!isset($data[$k])) {
                return false;
            }
        }
        return $this->update_term_permalink($data);
    }
    /**
     * Update update permalink định kỳ
     **/
    public function update_term_permalink($data, $base_url = '')
    {
        // không có thì mới tạo và update vào db
        if ($data['taxonomy'] == TaxonomyType::POSTS) {
            $url = WGR_CATEGORY_PERMALINK;
            /*
        } else if ($data['taxonomy'] == TaxonomyType::BLOGS) {
            $url = WGR_BLOGS_PERMALINK;
            */
        } else if ($data['taxonomy'] == TaxonomyType::PROD_CATS) {
            $url = WGR_PRODS_PERMALINK;
        } else if ($data['taxonomy'] == TaxonomyType::TAGS) {
            $url = WGR_TAGS_PERMALINK;
            /*
        } else if ($data['taxonomy'] == TaxonomyType::OPTIONS) {
            $url = WGR_OPTIONS_PERMALINK;
        } else if ($data['taxonomy'] == TaxonomyType::BLOG_TAGS) {
            $url = WGR_BLOG_TAGS_PERMALINK;
            */
        } else if ($data['taxonomy'] == TaxonomyType::PROD_TAGS) {
            $url = WGR_PROD_TAGS_PERMALINK;
        } else if (isset(WGR_CUS_TAX_PERMALINK[$data['taxonomy']])) {
            $url = WGR_CUS_TAX_PERMALINK[$data['taxonomy']];
        } else {
            $url = WGR_TAXONOMY_PERMALINK;
        }

        // thêm prefix cho url -> hỗ trợ đa ngôn ngữ sub-folder
        if (SITE_LANGUAGE_SUB_FOLDER == true && $data['lang_key'] != SITE_LANGUAGE_DEFAULT) {
            $url = $data['lang_key'] . '/' . $url;
        }

        //
        foreach (
            [
                //'category_base' => CATEGORY_BASE_URL,
                'term_id' => $data['term_id'],
                'slug' => $data['slug'],
                'taxonomy' => $data['taxonomy'],
            ] as $k => $v
        ) {
            $url = str_replace('%' . $k . '%', $v, $url);
        }

        // update vào db để sau còn tái sử dụng -> nhẹ server
        $this->base_model->update_multiple(
            $this->table,
            [
                // xóa cắp dấu // để tránh trường hợp gặp segment trống
                'term_permalink' => str_replace('//', '/', $url),
                // cập nhật giãn cách update lại permalink -> khi quá thời gian này sẽ tiến hành cập nhật permalink mới
                'updated_permalink' => time() + 3600,
            ],
            [
                'term_id' => $data['term_id'],
            ],
            [
                // hiển thị mã SQL để check
                //'show_query' => 1,
            ]
        );

        //
        return $base_url . $url;
    }
    // trả về url với đầy đủ tên miền
    public function get_full_permalink($data)
    {
        return $this->get_term_permalink($data, DYNAMIC_BASE_URL);
    }
    // trả về url của 1 term
    public function get_the_permalink($data, $base_url = '')
    {
        return $this->get_term_permalink($data, $base_url);
    }
    public function get_term_permalink($data, $base_url = '')
    {
        //print_r( $data );

        // chức năng này sẽ để 1 thời gian sau đó comment lại
        /*
        if (!isset($data['updated_permalink'])) {
            //print_r($data);
            die(__FUNCTION__ . ' Updated permalink! ' . __CLASS__ . ':' . __LINE__);
        }
        */

        // sử dụng permalink có sẵn trong data
        /*
        if ($data['updated_permalink'] > time() && $data['term_permalink'] != '') {
            return $base_url . $data['term_permalink'];
        }
        */
        return $base_url . $data['term_permalink'];

        //
        //return $base_url . '?cat=' . $data[ 'term_id' ] . '&taxonomy=' . $data[ 'taxonomy' ] . '&slug=' . $data[ 'slug' ];
        //return $base_url . 'c/' . $data['taxonomy'] . '/' . $data['term_id'] . '/' . $data['slug'];
    }
    // thường dùng trong view -> in ra link admin của 1 term
    public function the_term_permalink($data)
    {
        echo $this->get_term_permalink($data);
    }
    public function the_permalink($data)
    {
        // gọi tên đầy đủ để dễ lọc function giữa post với term
        $this->the_term_permalink($data);
    }

    // tạo html trong này -> do trong view không viết được tham số $this để tạo vòng lặp đệ quy
    function list_html_view($data, $gach_ngang = '', $is_deleted = '', $controller_slug = 'terms')
    {
        $tmp = '<tr>
            <td>&nbsp;</td>
            <td><a href="%get_admin_permalink%">' . $gach_ngang . ' %name% <i class="fa fa-edit"></i></a></td>
            <td><a href="%view_url%" target="_blank">%slug% <i class="fa fa-eye"></i></a></td>
            <td class="d-none show-if-ads-type">%custom_size%</td>
            <td>&nbsp;</td>
            <td>%lang_key%</td>
            <td>%count%</td>
            <td class="text-center">%action_link%</td>
        </tr>';

        //
        $for_redirect = '';
        if ($is_deleted != '') {
            $for_redirect .= '&is_deleted=' . $is_deleted;
        }

        //
        $str = '';
        foreach ($data as $k => $v) {
            print_r($v);

            //
            $node = $tmp;

            //
            if ($v['is_deleted'] == DeletedStatus::DELETED) {
                $action_link = '<a href="sadmin/' . $controller_slug . '/restore?id=%term_id%' . $for_redirect . '" onClick="return click_a_restore_record();" target="target_eb_iframe" class="bluecolor"><i class="fa fa-undo"></i></a>';
            } else {
                $action_link = '<a href="sadmin/' . $controller_slug . '/term_status?id=%term_id%' . $for_redirect . '" target="target_eb_iframe" data-id="%term_id%" data-status="%term_status%" class="record-status-color"><i class="fa fa-eye"></i></a> &nbsp; ';

                $action_link .= '<a href="sadmin/' . $controller_slug . '/delete?id=%term_id%' . $for_redirect . '" onClick="return click_a_delete_record();" target="target_eb_iframe" class="redcolor"><i class="fa fa-trash"></i></a>';
            }
            $node = str_replace('%action_link%', $action_link, $node);

            //
            foreach ($v as $key => $val) {
                if ($key == 'term_meta') {
                    //print_r( $val );
                    foreach ($val as $key_val => $val_val) {
                        $node = str_replace('%' . $key_val . '%', $val_val, $node);
                    }
                } else if (!is_array($val)) {
                    $node = str_replace('%' . $key . '%', $val, $node);
                }
            }
            $node = str_replace('%get_admin_permalink%', $this->get_admin_permalink($v['taxonomy'], $v['term_id'], $controller_slug), $node);
            $node = str_replace('%view_url%', $this->get_term_permalink($v), $node);

            //
            $str .= $node;

            //
            if (isset($v['child_term'])) {
                $str .= $this->list_html_view($v['child_term'], $gach_ngang . ' &#8212;', $is_deleted);
            }
        }

        //
        return $str;
    }
}
