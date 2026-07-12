<?php

namespace App\Controllers\Traits;

//
trait LayoutStructuredDataTrait
{
    /**
     * Tạo dữ liệu để tạo schema cho thống nhất
     **/
    protected function structuredData($data, $f, $html = '', $get_data = false)
    {
        //print_r( $data );
        $data['name'] = $this->getconfig->name;
        $data['logo'] = DYNAMIC_BASE_URL . $this->getconfig->logo;
        $data['logo_height_img'] = $this->getconfig->logo_height_img;
        $data['logo_width_img'] = $this->getconfig->logo_width_img;
        $data['currency_sd_format'] = empty($this->getconfig->currency_sd_format) ? 'USD' : $this->getconfig->currency_sd_format;

        //
        $data['post_img'] = '';
        $data['trv_width_img'] = 0;
        $data['trv_height_img'] = 0;
        $data['trv_img'] = $this->post_model->get_list_thumbnail($data, 'large');
        //$data['trv_img'] = $this->post_model->get_post_thumbnail($data);
        if ($data['trv_img'] != '') {
            $data['trv_img'] = explode('?', $data['trv_img'])[0];
            // nếu file tồn tại trong host -> xác định size của file
            //echo PUBLIC_PUBLIC_PATH . $data['trv_img'];
            if (is_file(PUBLIC_PUBLIC_PATH . $data['trv_img'])) {
                $logo_data = getimagesize(PUBLIC_PUBLIC_PATH . $data['trv_img']);

                //
                $data['post_img'] = DYNAMIC_BASE_URL . $data['trv_img'];
                $data['trv_width_img'] = $logo_data[0];
                $data['trv_height_img'] = $logo_data[1];
            } else if (strpos($data['trv_img'], '//') !== false) {
                $data['post_img'] = $data['trv_img'];
                $data['trv_width_img'] = 280;
                $data['trv_height_img'] = 280;
            }
        }
        $data['image'] = $data['trv_img'];

        //
        $data['p_link'] = $this->post_model->get_full_permalink($data);

        //
        //print_r( $data );
        if ($get_data === true) {
            return $data;
        }

        //
        if ($html == '') {
            $html = file_get_contents(VIEWS_PATH . 'html/structured-data/' . $f);
        }
        // thay data chính
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                continue;
            }
            $html = str_replace('{{' . $k . '}}', str_replace('"', '', $v), $html);
        }
        // sau đó là meta
        if (isset($data['post_meta'])) {
            foreach ($data['post_meta'] as $k => $v) {
                if (is_array($v)) {
                    continue;
                }
                $html = str_replace('{{' . $k . '}}', str_replace('"', '', $v), $html);
            }
        }

        // thay 1 số template đề phòng không có dữ liệu tương ứng
        foreach (
            [
                'meta_description' => $data['post_title'],
            ] as $k => $v
        ) {
            $html = str_replace('{{' . $k . '}}', $v, $html);
        }

        //
        return $html;
    }

    /**
     * Trả về mảng dữ liệu đã được build để tạo cấu trúc
     **/
    public function structuredGetData($data)
    {
        return $this->structuredData($data, '', '', true);
    }

    /**
     * 1 số controller bắt buộc phải đăng nhập mới cho tiếp tục
     **/
    protected function required_logged($add_params = '')
    {
        if ($this->current_user_id < 1) {
            // tạo url sau khi đăng nhập xong sẽ trỏ tới
            $login_redirect = DYNAMIC_BASE_URL . ltrim($_SERVER['REQUEST_URI'], '/');
            //die($login_redirect);

            //
            $login_url = base_url('guest/login') . '?login_redirect=' . urlencode($login_redirect);
            //$login_url .= '&msg=' . urlencode('Permission deny! ' . basename(__FILE__, '.php') . ':' . __LINE__);
            $login_url .= '&reauth=1';
            $login_url .= $add_params;
            //die( $login_url );

            //
            die(header('Location: ' . $login_url));
            //die( 'Permission deny! ' . basename( __FILE__, '.php' ) . ':' . __LINE__ );
        }
    }
}
