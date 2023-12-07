<?php

/**
 * AMP: Accelerated Mobile Pages
 */

namespace App\Controllers;

//
use App\Libraries\PostType;
use App\Libraries\DeletedStatus;

//
class Accelerated extends Layout
{
    public $amp_youtube = false;
    public $amp_iframe = false;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Trả về dữ liệu amp cho phần post
     **/
    public function post_details($id, $slug)
    {
        // var_dump($id);
        // var_dump($slug);

        //
        if (ENABLE_AMP_VERSION !== true) {
            return $this->page404('ERROR ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! AMP version is not ENABLE...');
        } else if (!is_numeric($id)) {
            return $this->page404('ERROR ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! post ID mismatch...');
        }

        //
        $this->cache_key = $this->post_model->key_cache($id) . 'amp';
        // var_dump($this->cache_key);
        $cache_value = $this->MY_cache($this->cache_key);
        if ($cache_value !== NULL) {
            return $this->show_cache($cache_value);
        }


        //
        $data = $this->post_model->select_post(
            $id,
            [
                'post_status' => PostType::PUBLICITY,
                'post_type' => PostType::POST,
            ],
            [
                // hiển thị mã SQL để check
                // 'show_query' => 1,
            ]
        );
        // print_r($data);
        // die(__CLASS__ . ':' . __LINE__);
        if (empty($data)) {
            // print_r($data);
            return $this->page404('ERROR ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! Không xác định được dữ liệu bài viết...');
        }

        //
        $data['post_content'] = $this->replace_content($data['post_content']);

        //
        $data['post_content'] = str_replace('="upload/', '="' . DYNAMIC_BASE_URL . 'upload/', $data['post_content']);
        $data['post_content'] = str_replace(', upload/', ', ' . DYNAMIC_BASE_URL . 'upload/', $data['post_content']);

        // điều chỉnh lại nội dung theo chuẩn AMP
        // Loại bỏ các attr không cần thiết và tag không được hỗ trợ
        $data['post_content'] = $this->amp_remove_attr($data['post_content']);

        // thay thế các tag cũ bằng tag mới
        $data['post_content'] = $this->amp_change_tag($data['post_content']);
        // print_r($data);

        $data = $this->post_model->metaTitleDescription($data);
        // print_r($data);

        //
        $blog_posting_width = '400';
        $blog_posting_height = '400';
        $blog_posting_url = '';
        $item_position = 1;
        $itemListElement = [[
            '@type' => 'ListItem',
            'position' => $item_position,
            'item' => [
                '@id' => DYNAMIC_BASE_URL,
                'name' => 'Trang chủ',
            ],
        ]];
        if (isset($data['post_meta'])) {
            $blog_posting_img = '';
            if (isset($data['post_meta']['image_large'])) {
                $blog_posting_img = $data['post_meta']['image_large'];
            } else if (isset($data['post_meta']['image'])) {
                $blog_posting_img = $data['post_meta']['image'];
            }
            if ($blog_posting_img != '' && strpos($blog_posting_img, '//') === false) {
                $blog_posting_url = DYNAMIC_BASE_URL . $blog_posting_img;
                $blog_posting_img = PUBLIC_PUBLIC_PATH . $blog_posting_img;
                // echo $blog_posting_img . '<br>' . PHP_EOL;
                if (is_file($blog_posting_img)) {
                    $get_file_info = getimagesize($blog_posting_img);
                    // print_r($get_file_info);

                    //
                    $blog_posting_width = $get_file_info[0];
                    $blog_posting_height = $get_file_info[1];
                }
            }

            //
            $terms_title = '';
            $terms_link = '';
            if (isset($data['post_meta']['post_category'])) {
                $post_category = explode(',', $data['post_meta']['post_category']);
                // print_r($post_category);

                //
                if (!empty($post_category)) {
                    $terms_data = $this->base_model->select(
                        '*',
                        'terms',
                        array(
                            // các kiểu điều kiện where
                            'is_deleted' => DeletedStatus::FOR_DEFAULT,
                        ),
                        array(
                            'where_in' => array(
                                'term_id' => $post_category
                            ),
                            'order_by' => array(
                                'term_id' => 'ASC'
                            ),
                            // hiển thị mã SQL để check
                            // 'show_query' => 1,
                            // trả về câu query để sử dụng cho mục đích khác
                            //'get_query' => 1,
                            // trả về COUNT(column_name) AS column_name
                            //'selectCount' => 'ID',
                            // trả về tổng số bản ghi -> tương tự mysql num row
                            //'getNumRows' => 1,
                            //'offset' => 0,
                            'limit' => 5
                        )
                    );
                    // print_r($terms_data);

                    foreach ($terms_data as $v) {
                        // $v_link = $this->term_model->get_full_permalink($v);
                        $v_link = DYNAMIC_BASE_URL . $v['term_permalink'];

                        //
                        $item_position++;
                        $itemListElement[] = [
                            '@type' => 'ListItem',
                            'position' => $item_position,
                            'item' => [
                                '@id' => $v_link,
                                'name' => $v['name'],
                            ],
                        ];

                        //
                        if ($terms_link == '') {
                            // link bản desktop
                            // $terms_link = $v_link;
                            // link bản amp
                            $terms_link = $this->base_model->amp_term_link($v);
                            $terms_title = $v['name'];
                        }
                    }
                }
            }
        }


        //
        $full_link = $this->post_model->get_full_permalink($data);
        $seo = $this->base_model->post_seo($data, $full_link);
        // print_r($seo);

        //
        $item_position++;
        $itemListElement[] = [
            '@type' => 'ListItem',
            'position' => $item_position,
            'item' => [
                '@id' => $full_link,
                'name' => $data['post_title'],
            ],
        ];
        // print_r($itemListElement);


        // lấy các bài mới hơn
        $next_post = $this->base_model->select(
            'ID, post_title, post_name, post_permalink',
            'posts',
            array(
                // các kiểu điều kiện where
                'ID >' => $data['ID'],
                'post_status' => PostType::PUBLICITY,
                'post_type' => $data['post_type'],
            ),
            array(
                'order_by' => array(
                    'ID' => 'ASC'
                ),
                // hiển thị mã SQL để check
                // 'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                // trả về COUNT(column_name) AS column_name
                //'selectCount' => 'ID',
                // trả về tổng số bản ghi -> tương tự mysql num row
                //'getNumRows' => 1,
                //'offset' => 0,
                'limit' => 10
            )
        );
        // print_r($next_post);

        // lấy các bài cũ hơn
        $prev_post = $this->base_model->select(
            'ID, post_title, post_name, post_permalink',
            'posts',
            array(
                // các kiểu điều kiện where
                'ID <' => $data['ID'],
                'post_status' => PostType::PUBLICITY,
                'post_type' => $data['post_type'],
            ),
            array(
                'order_by' => array(
                    'ID' => 'DESC'
                ),
                // hiển thị mã SQL để check
                // 'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                // trả về COUNT(column_name) AS column_name
                //'selectCount' => 'ID',
                // trả về tổng số bản ghi -> tương tự mysql num row
                //'getNumRows' => 1,
                //'offset' => 0,
                'limit' => 10
            )
        );
        // print_r($prev_post);


        // còn không sẽ tiến hành lưu cache
        $cache_value = view('layout_amp_view', [
            'data' => $data,
            'next_post' => $next_post,
            'prev_post' => $prev_post,
            'seo' => $seo,
            'full_link' => $full_link,
            'terms_title' => $terms_title,
            'terms_link' => $terms_link,
            'amp_link' => $this->base_model->amp_post_link($data),
            'amp_title' => $data['post_title'],
            'getconfig' => $this->getconfig,
            'option_model' => $this->option_model,
            'amp_youtube' => $this->amp_youtube,
            'amp_iframe' => $this->amp_iframe,
            'file_view' => 'post_amp_view',
            // structured data
            'breadcrumb_list' => [
                '@context' => 'http://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => $itemListElement,
            ],
            'blog_posting' => [
                '@context' => 'http://schema.org',
                '@type' => 'BlogPosting',
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => $this->getconfig->name,
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => DYNAMIC_BASE_URL . $this->getconfig->logo,
                    ],
                ],
                'mainEntityOfPage' => $full_link,
                'headline' => $data['post_title'],
                'datePublished' => $data['post_date'],
                'dateModified' => $data['post_modified'],
                'author' => [
                    '@type' => 'Person',
                    'name' => $this->getconfig->name,
                ],
                'description' => $seo['description'],
                'image' => [
                    '@type' => 'ImageObject',
                    'width' => $blog_posting_width,
                    'height' => $blog_posting_height,
                    'url' => $blog_posting_url,
                ],
            ],
        ]);

        $cache_save = $this->MY_cache($this->cache_key, $cache_value . '<!-- Served from: ' . __FUNCTION__ . ' -->');
        //var_dump( $cache_save );

        //
        return $cache_value;
    }

    /**
     * Trả về dữ liệu amp cho phần term
     **/
    public function post_lists($id, $slug, $page_num = 1)
    {
        // var_dump($id);
        // var_dump($slug);

        //
        if (ENABLE_AMP_VERSION !== true) {
            return $this->page404('ERROR ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! AMP version is not ENABLE...');
        } else if (!is_numeric($id)) {
            return $this->page404('ERROR ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! term ID mismatch...');
        }

        //
        $this->cache_key = $this->term_model->key_cache($id) . 'amp';
        // var_dump($this->cache_key);
        $cache_value = $this->MY_cache($this->cache_key);
        if ($cache_value !== NULL) {
            return $this->show_cache($cache_value);
        }


        //
        $data = $this->base_model->select(
            '*',
            WGR_TERM_VIEW,
            array(
                // các kiểu điều kiện where
                'is_deleted' => DeletedStatus::FOR_DEFAULT,
                'term_id' => $id,
            ),
            array(
                // hiển thị mã SQL để check
                // 'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                // trả về COUNT(column_name) AS column_name
                //'selectCount' => 'ID',
                // trả về tổng số bản ghi -> tương tự mysql num row
                //'getNumRows' => 1,
                //'offset' => 0,
                'limit' => 1
            )
        );
        // print_r($data);
        // die(__CLASS__ . ':' . __LINE__);
        if (empty($data)) {
            // print_r($data);
            return $this->page404('ERROR ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! Không xác định được dữ liệu danh mục...');
        }


        //
        $post_per_page = $this->base_model->get_config($this->getconfig, 'eb_posts_per_page', 20);

        //
        $post_where_ids = [
            // các kiểu điều kiện where
            'is_deleted' => DeletedStatus::FOR_DEFAULT,
            'term_taxonomy_id' => $id,
        ];
        $post_filter_ids = [
            // hiển thị mã SQL để check
            // 'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            // trả về COUNT(column_name) AS column_name
            //'selectCount' => 'ID',
            // trả về tổng số bản ghi -> tương tự mysql num row
            //'getNumRows' => 1,
            //'offset' => 0,
            // 'limit' => $post_per_page,
            'limit' => -1,
        ];

        // Phân trang
        if ($page_num > 1) {
            $total_filter_ids = $post_filter_ids;
            $total_filter_ids['selectCount'] = 'object_id';

            $totalThread = $this->base_model->select(
                'object_id',
                'term_relationships',
                $post_where_ids,
                $total_filter_ids
            );
            // print_r($totalThread);
            $totalThread = $totalThread[0]['object_id'];
            // echo $totalThread . '<br>' . PHP_EOL;
            $totalPage = ceil($totalThread / $post_per_page);
            if ($totalPage < 1) {
                $totalPage = 1;
            }
            // echo $totalPage . '<br>' . PHP_EOL;
            //echo $totalPage . '<br>' . PHP_EOL;
            if ($page_num > $totalPage) {
                $page_num = $totalPage;
            } else if ($page_num < 1) {
                $page_num = 1;
            }
            //echo $totalThread . '<br>' . PHP_EOL;
            //echo $totalPage . '<br>' . PHP_EOL;
            $offset = ($page_num - 1) * $post_per_page;
            // echo $offset . '<br>' . PHP_EOL;
        } else {
            $offset = 0;
        }

        //
        $post_filter_ids['offset'] = $offset;
        $post_filter_ids['limit'] = $post_per_page;
        $post_filter_ids['group_by'] = [
            'object_id'
        ];
        $post_filter_ids['order_by'] = [
            'object_id' => 'DESC'
        ];

        $post_ids = $this->base_model->select(
            'object_id',
            'term_relationships',
            $post_where_ids,
            $post_filter_ids
        );
        // print_r($post_ids);
        // die(__CLASS__ . ':' . __LINE__);
        if (empty($post_ids)) {
            // print_r($post_ids);
            return $this->page404('ERROR ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! Không xác định được danh sách bài viết...');
        }

        //
        $ids = [];
        foreach ($post_ids as $v) {
            $ids[] = $v['object_id'];
        }
        // print_r($ids);
        // die(__CLASS__ . ':' . __LINE__);


        //
        $post_data = $this->post_model->select_post(
            0,
            [
                'post_status' => PostType::PUBLICITY,
                'post_type' => PostType::POST,
            ],
            [
                'where_in' => array(
                    'ID' => $ids
                ),
                'order_by' => [
                    'menu_order' => 'DESC',
                    'time_order' => 'DESC',
                    'ID' => 'DESC',
                ],
                // hiển thị mã SQL để check
                // 'show_query' => 1,
                'limit' => -1
            ],
            '*'
        );
        // print_r($post_data);
        // die(__CLASS__ . ':' . __LINE__);
        if (empty($post_data)) {
            // print_r($post_data);
            return $this->page404('ERROR ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! Không xác định được chi tiết bài viết...');
        }
        $post_data = $this->post_model->list_meta_post($post_data);
        // print_r($post_data);

        //
        $item_position = 1;
        $itemListElement = [[
            '@type' => 'ListItem',
            'position' => $item_position,
            'item' => [
                '@id' => DYNAMIC_BASE_URL,
                'name' => 'Trang chủ',
            ],
        ]];


        //
        $full_link = $this->term_model->get_full_permalink($data);
        if ($page_num > 1) {
            $full_link = rtrim($full_link, '/') . '/page/' . $page_num;
        }
        $seo = $this->base_model->term_seo($data, $full_link);
        // print_r($seo);
        // die(__CLASS__ . ':' . __LINE__);

        //
        $item_position++;
        $itemListElement[] = [
            '@type' => 'ListItem',
            'position' => $item_position,
            'item' => [
                '@id' => $full_link,
                'name' => $data['name'],
            ],
        ];
        // print_r($itemListElement);
        // die(__CLASS__ . ':' . __LINE__);


        // còn không sẽ tiến hành lưu cache
        $cache_value = view('layout_amp_view', [
            'data' => $data,
            'post_data' => $post_data,
            'seo' => $seo,
            'full_link' => $full_link,
            'terms_link' => '',
            'amp_link' => $this->base_model->amp_term_link($data, $page_num),
            'amp_title' => $data['name'],
            'getconfig' => $this->getconfig,
            'option_model' => $this->option_model,
            'amp_youtube' => $this->amp_youtube,
            'amp_iframe' => $this->amp_iframe,
            'file_view' => 'term_amp_view',
            // structured data
            'breadcrumb_list' => [
                '@context' => 'http://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => $itemListElement,
            ],
            'blog_posting' => NULL,
        ]);

        $cache_save = $this->MY_cache($this->cache_key, $cache_value . '<!-- Served from: ' . __FUNCTION__ . ' -->');
        //var_dump( $cache_save );

        //
        return $cache_value;
    }

    /**
     * Trả về dữ liệu amp cho phần term (có phân trang)
     **/
    public function posts_lists($id, $page_num, $slug)
    {
        // var_dump($id);
        // var_dump($page_num);
        // var_dump($slug);

        //
        return $this->post_lists($id, $slug, $page_num);
    }

    protected function amp_remove_attr($str)
    {
        //
        $arr = array(
            'id',
            'class',
            'style',
            'dir',
            'type',
            'border',
            'align',
            'loading',

            // iframe
            'frameborder',
            'scrolling',
            'allowfullscreen',

            //
            'longdesc'
        );

        // xóa từng attr đã được chỉ định
        foreach ($arr as $v) {
            $str = $this->remove_attr($str, ' ' . $v . '="', '"');
            $str = $this->remove_attr($str, " " . $v . "='", "'");
        }

        // xóa các thẻ không còn được hỗ trợ
        $arr = array(
            'style',
            'font'
        );

        //
        foreach ($arr as $v) {
            $str = $this->remove_tag($str, $v);
        }

        //
        return $str;
    }

    protected function remove_tag($str, $tag)
    {

        // tách mảng theo tag nhập vào
        $c = explode('<' . $tag, $str);
        // print_r($c);

        $new_str = '';
        foreach ($c as $k => $v) {

            // bỏ qua mảng số 0
            if ($k > 0) {
                //				echo $v . "\n";
                //				echo strstr( $v, '>' ) . "\n";
                //				echo substr( strstr( $v, '>' ), 1 ) . "\n";

                // lấy từ dấu > trở đi
                $v = strstr($v, '>');

                // bỏ qua dấu > ở đầu
                $v = substr($v, 1);
            }

            //
            $new_str .= $v;
        }

        // xóa thẻ đóng
        $new_str = str_replace('</' . $tag . '>', '', $new_str);

        //
        return $new_str;
    }

    protected function remove_attr($str, $attr, $end_attr = '"')
    {

        // cắt mảng theo attr nhập vào
        $c = explode($attr, $str);
        // print_r( $c );

        $new_str = '';
        foreach ($c as $k => $v) {
            // chạy vòng lặp -> bỏ qua mảng đầu tiên
            if ($k > 0) {
                // dữ liệu mới bắt đầu từ đoạn kết thúc trước đó
                $v = strstr($v, $end_attr);

                // cắt bỏ đoạn thừa
                $v = substr($v, strlen($end_attr));
            }

            //
            $new_str .= $v;
        }

        // done
        return $new_str;
    }


    protected function amp_change_tag($str)
    {

        $arr = array(
            'img' => 'amp-img',
            'iframe' => 'amp-iframe'
        );

        foreach ($arr as $k => $v) {
            $str = $this->change_tag($str, $k, $v);
        }

        //
        $str = str_replace('</iframe>', '', $str);
        // bỏ một số thuộc tính không được hỗ trợ trong AMP
        $str = str_replace(' decoding="async"', '', $str);
        $str = str_replace(" decoding='async'", '', $str);

        //
        return $str;
    }

    protected function change_tag($str, $tag, $new_tag, $end_tag = '>')
    {
        $c = explode('<' . $tag . ' ', $str);
        // print_r( $c );

        $new_str = '';
        foreach ($c as $k => $v) {
            // bỏ qua mảng số 0
            if ($k > 0) {
                $v2 = explode('>', $v);
                $v2 = $v2[0];
                //			echo $v2. "\n";
                //			echo substr( $v2, -1 ) . "\n";
                //			echo substr( $v2, 0, -1 ) . "\n";

                // xóa đoạn
                $v = str_replace($v2, '', $v);
                $v = substr($v, 1);

                //
                if (substr($v2, -1) == '/') {
                    $v2 = substr($v2, 0, -1);
                }
                $v2 = trim($v2);

                // riêng với video youtube
                if (strpos($v2, 'youtube.com/') !== false) {
                    //				echo $v2 . "\n";
                    $v2 = explode('src="', $v2);
                    $v2 = $v2[1];
                    $v2 = explode('"', $v2);
                    $v2 = $v2[0];
                    //				echo $v2 . "\n";
                    $v2 = $this->get_youtube_id($v2);
                    //				echo $v2 . "\n";

                    // tạo nội dung mới từ ID youtube
                    $v2 = 'data-videoid="' . $v2 . '" layout="responsive" width="480" height="270"';
                    $new_tag = 'amp-youtube';

                    // tải cdn cho youtube
                    $this->amp_youtube = true;
                } else if ($new_tag == 'amp-iframe') {
                    //					echo $v2 . "\n";

                    $iframe_src = explode('src="', $v2);
                    $iframe_src = $iframe_src[1];
                    $iframe_src = explode('"', $iframe_src);
                    $iframe_src = $iframe_src[0];
                    //					echo $iframe_src . "\n";

                    $iframe_width = explode('width="', $v2);
                    $iframe_width = $iframe_width[1];
                    $iframe_width = explode('"', $iframe_width);
                    $iframe_width = $iframe_width[0];
                    //					echo $iframe_width . "\n";

                    $iframe_height = explode('height="', $v2);
                    $iframe_height = $iframe_height[1];
                    $iframe_height = explode('"', $iframe_height);
                    $iframe_height = $iframe_height[0];
                    //					echo $iframe_height . "\n";

                    $v2 = 'width="' . $iframe_width . '" height="' . $iframe_height . '" sandbox="allow-scripts allow-same-origin" layout="responsive" frameborder="0" src="' . $iframe_src . '"';

                    //
                    $this->amp_iframe = true;
                }
                // với hình ảnh, nếu thiếu layout thì bổ sung
                else if ($new_tag == 'amp-img') {
                    //echo $k . '" ----------- <br>' . "\n\n";
                    //echo '"-----' . $v2 . '------" ----------- <br>' . "\n\n";
                    if ($v2 != '' && strpos($v2, 'src=') !== false) {
                        //echo '"-----' . $v2 . '------" ----------- <br>' . "\n\n";
                        $amp_avt_size = array();

                        // lấy chiều rộng thực của ảnh nếu chưa có
                        if (strpos($v2, ' width=') === false) {
                            $amp_avt_size = $this->get_src_img($v2);
                            // print_r($amp_avt_size);

                            //
                            if (!empty($amp_avt_size)) {
                                $v2 .= ' width="' . $amp_avt_size[0] . '"';
                            } else {
                                $v2 .= ' width="400"';
                            }
                        }

                        // chiều cao thì lấy luôn từ mục chiều rộng trước đó rồi
                        if (strpos($v2, ' height=') === false) {
                            if (empty($amp_avt_size)) {
                                $amp_avt_size = $this->get_src_img($v2);
                            }

                            //
                            if (!empty($amp_avt_size)) {
                                $v2 .= ' height="' . $amp_avt_size[1] . '"';
                            } else {
                                $v2 .= ' height="400"';
                            }
                        }

                        //
                        // thêm class để resize ảnh (dựa theo AMP wp)
                        $v2 .= ' class="amp-wp-enforced-sizes"';
                        //$v2 .= ' sizes="(min-width: 600px) 600px, 100vw"';
                    } else {
                        $v2 = '';
                    }
                }

                // tổng hợp nội dung lại
                if ($v2 != '') {
                    //echo $v2 . ' :::::::::::<br>' . "\n";
                    $v = '<' . $new_tag . ' ' . $v2 . '></' . $new_tag . '>' . $v;
                } else {
                    //echo $v . ' ================ <br>' . "\n";
                }
            }

            //
            $new_str .= $v;
        }

        return $new_str;
    }

    protected function get_youtube_id($url)
    {
        if ($url == '') {
            return '';
        }

        //
        parse_str(parse_url($url, PHP_URL_QUERY), $a);

        if (isset($a['v'])) {
            return $a['v'];
        } else {
            $a = explode('/embed/', $url);
            if (isset($a[1])) {
                $a = explode('?', $a[1]);
                $a = explode('&', $a[0]);

                return $a[0];
            }

            $a = explode('/youtu.be/', $url);
            if (isset($a[1])) {
                $a = explode('?', $a[1]);
                $a = explode('&', $a[0]);

                return $a[0];
            }
        }

        return '';
    }

    // tìm kích thước ảnh trên host
    function img_size($img, $default_width = 300, $default_height = 300)
    {
        //		echo $img . '<br>' . "\n";

        //
        $amp_avt_width = $default_width;
        $amp_avt_height = $default_height;

        // lấy domain hiện tại
        $domain = str_replace('www.', '', $_SERVER['HTTP_HOST']) . '/';

        //
        $check_img = strstr($img, $domain);
        $local_img = '';

        // nếu không -> thử tìm theo thư mục upload
        if ($check_img == '') {
            $check_img = strstr($img, '/upload/');
            if ($check_img != '') {
                $local_img = PUBLIC_PUBLIC_PATH . substr($check_img, 1);
            }
        }
        // nếu có -> dùng luôn
        else {
            $local_img = PUBLIC_PUBLIC_PATH . str_replace($domain, '', $check_img);
        }
        //		echo $local_img . '<br>' . "\n";

        //
        if ($local_img != '' && is_file($local_img)) {
            $local_img = getimagesize($local_img);
            // print_r( $check_img );

            //
            $amp_avt_width = $local_img[0];
            $amp_avt_height = $local_img[1];
        }


        //
        return array(
            $amp_avt_width,
            $amp_avt_height,
        );
    }

    protected function get_src_img($v2)
    {
        $get_img_src = str_replace("'", '"', $v2);
        //		echo $get_img_src . '<br>' . "\n";

        $get_img_src = explode('src="', $get_img_src);
        // print_r( $get_img_src );

        if (isset($get_img_src[1])) {
            //			echo $get_img_src . '<br>' . "\n";

            $get_img_src = explode('"', $get_img_src[1]);
            $get_img_src = $get_img_src[0];
            //			echo $get_img_src . '<br>' . "\n";

            //
            return $this->img_size($get_img_src, 400, 400);
        }

        //
        return array();
    }
}
