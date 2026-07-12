<?php

namespace App\Controllers\Traits;

//
trait LayoutPage404Trait
{
    public function page404($msg_404 = '', $in_cache = '')
    {
        // kiểm tra có trong RewriteRule không đã
        $this->rewriteRule();

        // 
        $link_name = $_SERVER['REQUEST_URI'];
        if (strpos($link_name, '/apple-touch-icon.') !== false || strpos($link_name, '/apple-touch-icon-') !== false) {
            // với file ảnh thì bỏ mọi dấu ? ở sau luôn
            $link_name = explode('?', $link_name)[0];

            // copy file nếu chưa có
            if (strpos($link_name, '.png') !== false) {
                // echo PUBLIC_PUBLIC_PATH . 'favicon-full.png' . '<br>' . "\n";
                // echo PUBLIC_PUBLIC_PATH . ltrim($link_name, '/') . '<br>' . "\n";
                // die(__CLASS__ . ':' . __LINE__);

                // 
                $this->MY_copy(PUBLIC_PUBLIC_PATH . 'favicon-full.png', PUBLIC_PUBLIC_PATH . ltrim($link_name, '/'));
            }

            // chuyển hướng đến URL ảnh tương ứng
            $this->MY_redirect(DYNAMIC_BASE_URL . '/favicon-full.png', 301);
        }
        // với các file tĩnh thì bỏ mọi dấu ? ở sau luôn
        else if (
            strpos($link_name, '.php?') !== false ||
            strpos($link_name, '.aspx?') !== false ||
            strpos($link_name, '.asp?') !== false ||
            strpos($link_name, '.png?') !== false ||
            strpos($link_name, '.jpg?') !== false ||
            strpos($link_name, '.jpeg?') !== false ||
            strpos($link_name, '.gif?') !== false ||
            strpos($link_name, '.js?') !== false ||
            strpos($link_name, '.css?') !== false ||
            strpos($link_name, '.pdf?') !== false ||
            strpos($link_name, '.json?') !== false ||
            strpos($link_name, '.txt?') !== false ||
            strpos($link_name, '.html?') !== false ||
            strpos($link_name, '.htm?') !== false ||
            strpos($link_name, '.zip?') !== false ||
            strpos($link_name, '.sql?') !== false ||
            strpos($link_name, '.lock?') !== false ||
            strpos($link_name, '.env?') !== false ||
            strpos($link_name, '.ini?') !== false ||
            strpos($link_name, '.bak?') !== false ||
            strpos($link_name, '.yaml?') !== false ||
            strpos($link_name, '.yml?') !== false ||
            strpos($link_name, '.log?') !== false ||
            strpos($link_name, '.run?') !== false ||
            strpos($link_name, '.conf?') !== false ||
            strpos($link_name, '.gz?') !== false ||
            strpos($link_name, '.rar?') !== false ||
            strpos($link_name, '.tgz?') !== false ||
            strpos($link_name, '.7z?') !== false ||
            strpos($link_name, '.py?') !== false ||
            strpos($link_name, '.xml?') !== false
        ) {
            $link_name = explode('?', $link_name)[0];
        } else {
            // xóa bỏ các tham số không cần thiết trong URL
            $remove_params = array(
                'fbclid=',
                'gclid=',
                'fb_comment_id=',
                // 'add_to_wishlist=',
                '_wpnonce=',
                'utm_',
                'v',
                'nse',
            );
            foreach ($remove_params as $v) {
                $link_name = explode('?' . $v, $link_name)[0];
                $link_name = explode('&' . $v, $link_name)[0];
            }
        }

        // xóa đoạn /public/ ở đầu link nếu có
        if (strpos($link_name, '/public/') !== false) {
            // $link_name = explode('/public/', $link_name)[1];
            $link_name = str_replace('/public/', '/', $link_name, 1);
        }

        // 
        if (!empty($link_name)) {
            $data = $this->base_model->select(
                [
                    // các trường cần lấy ra
                    'link_image',
                ],
                'links',
                array(
                    // các kiểu điều kiện where
                    // 'link_image !=' => '',
                ),
                array(
                    'like_before' => array(
                        'link_name' => $link_name,
                    ),
                    'order_by' => array(
                        'link_id' => 'DESC',
                    ),
                    // hiển thị mã SQL để check
                    // 'show_query' => 1,
                    // trả về câu query để sử dụng cho mục đích khác
                    // 'get_query' => 1,
                    // trả về COUNT(column_name) AS column_name
                    // 'selectCount' => 'ID',
                    // trả về tổng số bản ghi -> tương tự mysql num row
                    // 'getNumRows' => 1,
                    // 'offset' => 0,
                    'limit' => 10,
                )
            );
            // print_r($data);
            if (!empty($data)) {
                foreach ($data as $v) {
                    if (!empty($v['link_image'])) {
                        $this->rewriteRule('RewriteRule ^' . $_SERVER['REQUEST_URI'] . '$ ' . $v['link_image'] . ' [R=301,L]');
                        break;
                    }
                }
                // return false;
            } else if (strpos($link_name, '.php') === false) {
                // } else {
                // các link kiểu php thì không cần lưu lại
                // lưu các URL 404 này vào bảng links để tiện theo dõi
                $result_id = $this->base_model->insert('links', [
                    'link_url' => $_SERVER['HTTP_HOST'],
                    'link_name' => $link_name,
                    'link_image' => '',
                    'link_target' => $in_cache == '' ? debug_backtrace()[1]['function'] : $in_cache,
                    'link_description' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
                    'link_visible' => 'N',
                    'link_owner' => $this->current_user_id,
                    'link_updated' => date(EBE_DATETIME_FORMAT),
                    'link_rel' => $this->base_model->getIPAddress(),
                    'link_notes' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
                    'link_rss' => '',
                ]);
            }
        }


        /**
         * trả về lỗi 404
         **/
        // echo __CLASS__ . ':' . __LINE__;
        if (function_exists('http_response_code')) {
            http_response_code(404);
        }
        $pcol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
        $response = \Config\Services::response();
        $response->setStatusCode(404, $pcol . ' 404 Not Found');
        // http_response_code(404);

        //
        $this->teamplate['main'] = view(
            '404',
            array(
                'seo' => $this->base_model->default_seo(
                    '404 not found',
                    __FUNCTION__,
                    [
                        'canonical' => base_url('404'),
                    ]
                ),
                'breadcrumb' => '',
                // thông điệp của việc xuất hiện lỗi 404
                'msg_404' => $msg_404,
            )
        );
        return view('layout_view', $this->teamplate);
    }
}
