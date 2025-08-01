<?php

/**
 * AMP: Accelerated Mobile Pages
 * https://amp.dev/documentation/components/amp-video
 */

namespace App\Controllers;

//
use App\Libraries\PostType;
use App\Libraries\DeletedStatus;
use App\Libraries\TaxonomyType;

//
class Accelerated extends Layout
{
    public $amp_youtube = false;
    public $amp_iframe = false;
    public $amp_video = false;
    public $amp_audio = false;
    public $amp_base_url = '';
    public $amp_home_label = '';

    public function __construct()
    {
        parent::__construct();

        //
        $this->amp_home_label = $this->lang_model->get_the_text('breadcrumb_home', 'Home');
        $this->amp_base_url = DYNAMIC_BASE_URL;
        // thêm prefix cho url -> hỗ trợ đa ngôn ngữ sub-folder
        if (SITE_LANGUAGE_SUB_FOLDER == true && $this->lang_key != SITE_LANGUAGE_DEFAULT) {
            $this->amp_base_url .= $this->lang_key . '/';
        }

        //
        // ini_set('display_errors', 1);
        // error_reporting(E_ALL);
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
        if ($cache_value !== null) {
            return $this->show_cache($cache_value, $this->cache_key);
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
            return $this->page404('ERROR ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! Cannot be determined post data...');
        }

        //
        $data['post_content'] = $this->replace_content($data['post_content']);

        //
        $data['post_content'] = str_replace('="upload/', '="' . DYNAMIC_BASE_URL . 'upload/', $data['post_content']);
        $data['post_content'] = str_replace(', upload/', ', ' . DYNAMIC_BASE_URL . 'upload/', $data['post_content']);

        // điều chỉnh lại nội dung theo chuẩn AMP
        // Loại bỏ các attr không cần thiết và tag không được hỗ trợ
        $data['post_content'] = $this->removes_attr($data['post_content']);

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
        $arr_where_in = [];
        $itemListElement = [[
            '@type' => 'ListItem',
            'position' => $item_position,
            'item' => [
                '@id' => $this->amp_base_url,
                'name' => $this->amp_home_label,
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


                    //
                    $category_ids = [];
                    foreach ($terms_data as $v) {
                        // $v_link = $this->term_model->get_full_permalink($v);
                        $v_link = $this->amp_base_url . $v['term_permalink'];
                        $category_ids[] = $v['term_id'];

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

                    //
                    $arr_where_in = [
                        'term_id' => $post_category
                    ];
                }
            }
        }
        // print_r($arr_where_in);
        // die(__CLASS__ . ':' . __LINE__);


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
            '*',
            WGR_POST_VIEW,
            array(
                // các kiểu điều kiện where
                'ID >' => $data['ID'],
                'post_status' => PostType::PUBLICITY,
                // 'taxonomy' => TaxonomyType::POSTS,
                'post_type' => $data['post_type'],
                'lang_key' => $this->lang_key,
            ),
            array(
                'where_in' => $arr_where_in,
                'group_by' => array(
                    'ID',
                ),
                'order_by' => array(
                    'menu_order' => 'ASC',
                    //'time_order' => 'ASC',
                    'ID' => 'ASC',
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
            '*',
            WGR_POST_VIEW,
            array(
                // các kiểu điều kiện where
                'ID <' => $data['ID'],
                'post_status' => PostType::PUBLICITY,
                // 'taxonomy' => TaxonomyType::POSTS,
                'post_type' => $data['post_type'],
                'lang_key' => $this->lang_key,
            ),
            array(
                'where_in' => $arr_where_in,
                'group_by' => array(
                    'ID',
                ),
                'order_by' => array(
                    'menu_order' => 'DESC',
                    'time_order' => 'DESC',
                    'ID' => 'DESC',
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
            'amp_base_url' => $this->amp_base_url,
            'amp_home_label' => $this->amp_home_label,
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
            'amp_video' => $this->amp_video,
            'amp_audio' => $this->amp_audio,
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

        // 
        $this->MY_cache($this->cache_key, $cache_value . '<!-- Served from: ' . __FUNCTION__ . ' -->');

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
        // var_dump($page_num);

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
        if ($cache_value !== null) {
            return $this->show_cache($cache_value, $this->cache_key);
        }


        //
        $data = $this->base_model->select(
            '*',
            WGR_TERM_VIEW,
            array(
                // các kiểu điều kiện where
                'is_deleted' => DeletedStatus::FOR_DEFAULT,
                'term_id' => $id,
                'taxonomy' => TaxonomyType::POSTS,
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
            return $this->page404('ERROR ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! Cannot be determined category data...');
        }

        // lấy cả bài của nhóm con
        $data['child_term'] = $this->base_model->select(
            '*',
            WGR_TERM_VIEW,
            array(
                // các kiểu điều kiện where
                'is_deleted' => DeletedStatus::FOR_DEFAULT,
                'parent' => $id,
                'taxonomy' => TaxonomyType::POSTS,
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
                'limit' => -1
            )
        );

        //
        $post_per_page = $this->base_model->get_config($this->getconfig, 'eb_posts_per_page', 20);
        // var_dump($post_per_page);
        $totalThread = $this->post_model->fix_term_count($data, PostType::POST);
        // echo $totalThread . '<br>' . PHP_EOL;

        // Phân trang
        if ($page_num > 1 && $totalThread > $post_per_page) {
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
            //echo $totalPage . '<br>' . PHP_EOL;
            $offset = ($page_num - 1) * $post_per_page;
            // echo $offset . '<br>' . PHP_EOL;
        } else {
            $offset = 0;
        }

        //
        $post_data = $this->post_model->post_category(PostType::POST, $data, [
            'offset' => $offset,
            'limit' => $post_per_page
        ]);
        // print_r($post_data);
        // die(__CLASS__ . ':' . __LINE__);
        if (empty($post_data)) {
            // print_r($post_data);
            return $this->page404('ERROR ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! Cannot be determined post details...');
        }
        $post_data = $this->post_model->list_meta_post($post_data);
        // print_r($post_data);

        //
        $item_position = 1;
        $itemListElement = [[
            '@type' => 'ListItem',
            'position' => $item_position,
            'item' => [
                '@id' => $this->amp_base_url,
                'name' => $this->amp_home_label,
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
            'amp_base_url' => $this->amp_base_url,
            'amp_home_label' => $this->amp_home_label,
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
            'amp_video' => $this->amp_video,
            'amp_audio' => $this->amp_audio,
            'file_view' => 'term_amp_view',
            // structured data
            'breadcrumb_list' => [
                '@context' => 'http://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => $itemListElement,
            ],
            'blog_posting' => NULL,
        ]);

        // 
        $this->MY_cache($this->cache_key, $cache_value . '<!-- Served from: ' . __FUNCTION__ . ' -->');

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

    protected function removes_attr($str)
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
            'decoding',
            'color',
            // video
            'autoplay',
            'loop',
            // img
            'fetchpriority',
            // iframe
            'frameborder',
            'scrolling',
            'allowfullscreen',
            //
            'longdesc'
        );


        // xóa từng attr đã được chỉ định
        foreach ($arr as $v) {
            // v2 -> thay thành 1 attr sau đó remove 1 thể
            $str = str_replace(' ' . $v . '=\'', ' for-remove-attr=\'', $str);
            $str = str_replace(' ' . $v . '="', ' for-remove-attr="', $str);

            // v1
            // $str = $this->remove_attr($str, ' ' . $v . '="', '"');
            // $str = $this->remove_attr($str, " " . $v . "='", "'");
        }

        // xóa riêng thẻ height cho table
        $str = $this->remove1_attr($str, 'table', 'height');


        // bắt đầu xóa attr đã được thay thế
        $str = $this->remove_attr($str, ' for-remove-attr="', '"');
        $str = $this->remove_attr($str, " for-remove-attr='", "'");


        // xóa các thẻ không còn được hỗ trợ
        foreach (
            [
                'style',
                'font'
            ] as $v
        ) {
            $str = $this->remove_tag($str, $v);
        }

        //
        return $str;
    }

    // thay thế các tag không khả dụng trong AMP bằng span
    protected function remove_tag($str, $tag)
    {
        $str = str_replace('<' . $tag, '<span', $str);
        $str = str_replace('</' . $tag . '>', '</span>', $str);

        //
        return $str;
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

    /**
     * Xóa riêng 1 attr cho 1 tag cụ thể
     **/
    protected function remove1_attr($str, $tag, $attr)
    {
        $search = array();
        $replace = array();
        $matches = array();
        preg_match_all('/<' . $tag . '[\s\r\n]+.*?>/is', $str, $matches);
        // print_r($matches);
        foreach ($matches[0] as $imgHTML) {
            if (strpos($imgHTML, $attr) === false) {
                continue;
            }

            // replace the attr and add the for-remove-attr attribute
            $replaceHTML = $imgHTML;
            $replaceHTML = str_replace(' ' . $attr . '=\'', ' for-remove-attr=\'', $replaceHTML);
            $replaceHTML = str_replace(' ' . $attr . '="', ' for-remove-attr="', $replaceHTML);

            // cho vào mảng để thay thế nội dung
            $search[] = $imgHTML;
            $replace[] = $replaceHTML;
        }
        return str_replace($search, $replace, $str);
    }


    protected function amp_change_tag($str)
    {
        //
        $search = array();
        $replace = array();

        // amp-img
        $matches = array();
        preg_match_all('/<img[\s\r\n]+.*?>/is', $str, $matches);
        // print_r($matches);
        foreach ($matches[0] as $imgHTML) {
            // if (in_array($imgHTML, $search, true)) {
            //     // already has a replacement
            //     continue;
            // }

            // replace the src and add the data-src attribute
            $replaceHTML = $imgHTML;
            // $replaceHTML = preg_replace('/<img(.*?)srcset=/is', '<img$1srcset="" data-srcset=', $replaceHTML);

            // thêm class để resize ảnh (dựa theo AMP wp)
            $classes = 'amp-wp-enforced-sizes';
            if (preg_match('/class=["\']/i', $replaceHTML)) {
                $replaceHTML = preg_replace('/class=(["\'])(.*?)["\']/is', 'class=$1' . $classes . ' $2$1', $replaceHTML);
            } else {
                $replaceHTML = preg_replace('/<img/is', '<img class="' . $classes . '"', $replaceHTML);
            }

            // chưa có sizes thì bổ sung
            if (!preg_match('/sizes=["\']/i', $replaceHTML)) {
                $replaceHTML = preg_replace('/<img/is', '<img sizes="(max-width: 450px) 100vw, 450px"', $replaceHTML);
            }

            // thêm thẻ đóng amp-img
            // $replaceHTML = str_replace(' />', '>', $replaceHTML);
            $replaceHTML = str_replace('/>', '>', $replaceHTML);
            $replaceHTML .= '</amp-img>';

            // cho vào mảng để thay thế nội dung
            $search[] = $imgHTML;
            $replace[] = $replaceHTML;
        }


        // amp-video
        $matches = array();
        preg_match_all('/<video[\s\r\n]+.*?>/is', $str, $matches);
        // print_r($matches);
        foreach ($matches[0] as $imgHTML) {
            // if (in_array($imgHTML, $search, true)) {
            //     // already has a replacement
            //     continue;
            // }

            //
            $replaceHTML = $imgHTML;

            // thêm layout responsive
            $replaceHTML = str_replace('<video ', '<video layout="responsive" ', $replaceHTML);

            // bổ sung poster là logo (nếu chưa có)
            if (strpos($replaceHTML, 'poster=') === false) {
                $replaceHTML = str_replace('<video ', '<video poster="' . DYNAMIC_BASE_URL . $this->option_model->get_the_logo($this->getconfig) . '" ', $replaceHTML);
            }

            // cho vào mảng để thay thế nội dung
            $search[] = $imgHTML;
            $replace[] = $replaceHTML;

            //
            $this->amp_video = true;
        }


        // amp-audio
        if (strpos($str, '<audio') !== false) {
            $this->amp_audio = true;
        }


        // amp-iframe
        $matches = array();
        preg_match_all('/<iframe[\s\r\n]+.*?>/is', $str, $matches);
        // print_r($matches);
        foreach ($matches[0] as $imgHTML) {
            // if (in_array($imgHTML, $search, true)) {
            //     // already has a replacement
            //     continue;
            // }

            //
            $replaceHTML = $imgHTML;

            // xử lý riêng với video youtube
            if (strpos($replaceHTML, 'youtube.com/') !== false || strpos($replaceHTML, 'youtu.be/') !== false) {
                // tách lấy id video
                $replaceHTML = explode('src="', $replaceHTML);
                $replaceHTML = $replaceHTML[1];
                $replaceHTML = explode('"', $replaceHTML);
                $replaceHTML = $replaceHTML[0];

                // khởi tạo mã mới
                $replaceHTML = '<amp-youtube data-videoid="' . $this->get_youtube_id($replaceHTML) . '" layout="responsive" width="480" height="270"></amp-youtube>';

                //
                $this->amp_youtube = true;
            } else {
                $iframe_src = explode('src="', $replaceHTML);
                $iframe_src = $iframe_src[1];
                $iframe_src = explode('"', $iframe_src);
                $iframe_src = $iframe_src[0];
                // echo $iframe_src . "\n";

                $iframe_width = explode('width="', $replaceHTML);
                $iframe_width = $iframe_width[1];
                $iframe_width = explode('"', $iframe_width);
                $iframe_width = $iframe_width[0];
                // echo $iframe_width . "\n";

                $iframe_height = explode('height="', $replaceHTML);
                $iframe_height = $iframe_height[1];
                $iframe_height = explode('"', $iframe_height);
                $iframe_height = $iframe_height[0];
                // echo $iframe_height . "\n";

                // khởi tạo mã mới
                $replaceHTML = '<amp-iframe width="' . $iframe_width . '" height="' . $iframe_height . '" sandbox="allow-scripts allow-same-origin" layout="responsive" src="' . $iframe_src . '"></amp-iframe>';

                //
                $this->amp_iframe = true;
            }

            // cho vào mảng để thay thế nội dung
            $search[] = $imgHTML;
            $replace[] = $replaceHTML;
        }


        // bắt đầu thay nội dung
        // print_r($search);
        // print_r($replace);
        $str = str_replace($search, $replace, $str);


        // thay nốt các dữ liệu còn sót
        $str = str_replace('<img ', '<amp-img ', $str);
        //
        $str = str_replace('<video ', '<amp-video ', $str);
        $str = str_replace('</video>', '</amp-video>', $str);
        //
        $str = str_replace('<audio ', '<amp-audio ', $str);
        $str = str_replace('</audio>', '</amp-audio>', $str);
        //
        $str = str_replace('</iframe>', '', $str);
        // bỏ một số thuộc tính không được hỗ trợ trong AMP
        // $str = str_replace(' decoding="async"', '', $str);
        // $str = str_replace(" decoding='async'", '', $str);
        // $str = str_replace(' fetchpriority="high"', '', $str);

        //
        return $str;
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
}
