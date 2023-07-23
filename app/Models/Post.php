<?php

namespace App\Models;

// Libraries
use App\Libraries\PostType;
use App\Libraries\TaxonomyType;
use App\Libraries\DeletedStatus;

//
class Post extends PostPages
{
    public function __construct()
    {
        parent::__construct();
    }

    /*
     * cập nhật lượt xem cho post
     */
    public function update_views($id, $val = 1)
    {
        //echo __FUNCTION__ . '<br>' . PHP_EOL;
        //echo $id . '<br>' . PHP_EOL;

        //
        $this->base_model->update_count($this->table, 'post_viewed', array(
            // WHERE
            'ID' => $id,
        ), [
            'value' => $val,
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
        ]);
    }

    /**
     * vì permalink gán trực tiếp vào db nên thi thoảng sẽ check lại chút
     **/
    public function sync_post_term_permalink()
    {
        if ($this->base_model->scache(__FUNCTION__) !== NULL) {
            return false;
        }
        // luôn tạo giãn cách để tránh update liên tục -> chỉ 1 người update là đủ
        $this->base_model->scache(__FUNCTION__, time(), 120);

        //
        global $arr_custom_post_type;
        $allow_post_type = [
            PostType::PAGE,
            PostType::POST,
            //PostType::BLOG,
            PostType::PROD,
        ];
        foreach ($arr_custom_post_type as $k => $v) {
            if (!in_array($k, $allow_post_type)) {
                $allow_post_type[] = $k;
            }
        }

        // lấy các post chưa có permalink đẻ update
        $data = $this->base_model->select(
            '*',
            'posts',
            array(
                // các kiểu điều kiện where
                'post_status' => PostType::PUBLICITY,
                'updated_permalink <' => time(),
            ),
            array(
                /*
                'where_or' => array(
                    'post_permalink' => '',
                    'updated_permalink' => 0,
                ),
                */
                'where_in' => array(
                    'post_type' => $allow_post_type
                ),
                'order_by' => array(
                    'ID' => 'DESC'
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                // trả về COUNT(column_name) AS column_name
                //'selectCount' => 'ID',
                // trả về tổng số bản ghi -> tương tự mysql num row
                //'getNumRows' => 1,
                //'offset' => 0,
                'limit' => 20
            )
        );
        //print_r( $data );

        // nếu không có thì chuyển sang update term
        if (empty($data)) {
            global $arr_custom_taxonomy;
            $allow_taxonomy = [
                TaxonomyType::POSTS,
                TaxonomyType::TAGS,
                //TaxonomyType::BLOGS,
                //TaxonomyType::BLOG_TAGS,
                TaxonomyType::PROD_CATS,
                TaxonomyType::PROD_TAGS,
            ];
            foreach ($arr_custom_taxonomy as $k => $v) {
                if (!in_array($k, $allow_taxonomy)) {
                    $allow_taxonomy[] = $k;
                }
            }

            // lấy các term chưa có permalink đẻ update
            $data = $this->base_model->select(
                '*',
                WGR_TERM_VIEW,
                array(
                    // các kiểu điều kiện where
                    'is_deleted' => DeletedStatus::FOR_DEFAULT,
                    //'term_permalink' => '',
                    'updated_permalink <' => time(),
                ),
                array(
                    /*
                    'where_or' => array(
                        'term_permalink' => '',
                        'updated_permalink' => 0,
                    ),
                    */
                    'where_in' => array(
                        'taxonomy' => $allow_taxonomy
                    ),
                    'order_by' => array(
                        'term_id' => 'DESC'
                    ),
                    // hiển thị mã SQL để check
                    //'show_query' => 1,
                    // trả về câu query để sử dụng cho mục đích khác
                    //'get_query' => 1,
                    // trả về COUNT(column_name) AS column_name
                    //'selectCount' => 'ID',
                    // trả về tổng số bản ghi -> tương tự mysql num row
                    //'getNumRows' => 1,
                    //'offset' => 0,
                    'limit' => 20
                )
            );
            //print_r( $data );

            // nếu hết rồi thì lưu lại cache để sau đỡ dính
            if (empty($data)) {
                $this->base_model->scache(__FUNCTION__, time(), 3600);
            } else {
                foreach ($data as $v) {
                    $this->term_model->update_term_permalink($v);
                }
            }
        }
        // có thì xử lý cái phần có
        else {
            foreach ($data as $v) {
                $this->update_post_permalink($v);
            }
        }

        //
        return true;
    }

    /**
     * Tạo mặc định dữ liệu cho post data và post meta
     **/
    public function metaTitleDescription($data)
    {
        if (!isset($data['post_meta'])) {
            $data['post_meta'] = [];
        }
        // nếu không có meta title -> dùng post title
        if (!isset($data['post_meta']['meta_title']) || empty($data['post_meta']['meta_title'])) {
            $data['post_meta']['meta_title'] = $data['post_title'];
        } else {
            $data['post_meta']['meta_title'] = $this->base_model->short_string($data['post_meta']['meta_title'], 70);
        }
        // nếu không có meta description -> dùng post title
        if (!isset($data['post_meta']['meta_description']) || empty($data['post_meta']['meta_description'])) {
            $data['post_meta']['meta_description'] = $data['post_title'];
            // độ dài tối ưu của meta description mà SEO quake đưa ra là: 160-300 -> lấy trung bình cộng = 230
            // nếu có post excerpt -> chuyển sang dùng post excerpt
            $post_excerpt = trim(strip_tags($data['post_excerpt']));
            if (!empty($post_excerpt)) {
                $data['post_meta']['meta_description'] = $this->base_model->short_string($post_excerpt, 230);
            } else {
                // nếu có post content -> chuyển sang dùng post content
                $post_content = trim(strip_tags($data['post_content']));
                if (!empty($post_content)) {
                    $data['post_meta']['meta_description'] = $this->base_model->short_string($post_content, 230);
                }
            }
        } else {
            $data['post_meta']['meta_description'] = $this->base_model->short_string($data['post_meta']['meta_description'], 300);
        }

        //
        return $data;
    }

    /**
     * Tạo cấu trúc dữ liệu cho post
     **/
    public function structuredArticleData($data, $structured_data, $ops = [])
    {
        //print_r($structured_data);

        //
        if (!isset($ops['type']) || $ops['type'] == '') {
            $ops['type'] = 'Article';
        }

        //
        return $this->base_model->dynamicSchema([
            "@context" => "http://schema.org",
            "@type" => $ops['type'],
            "mainEntityOfPage" => [
                "@type" => "WebPage",
                "@id" => $structured_data['p_link']
            ],
            "name" => $data['post_title'],
            //"headline" => $data['post_title'],
            "headline" => $data['post_meta']['meta_title'],
            "image" => [
                "@type" => "ImageObject",
                "url" => $structured_data['post_img'],
                "url" => $structured_data['post_img'],
                "width" => $structured_data['trv_width_img'],
                "height" => $structured_data['trv_height_img'],
            ],
            "datePublished" => $data['post_date'],
            "dateModified" => $data['post_modified'],
            "author" => [
                "@type" => "Organization",
                "name" => $structured_data['name'],
            ],
            "publisher" => [
                "@type" => "Organization",
                "name" => $structured_data['name'],
                "logo" => [
                    "@type" => "ImageObject",
                    "url" => $structured_data['logo'],
                    "width" => $structured_data['logo_width_img'],
                    "height" => $structured_data['logo_height_img'],
                ]
            ],
            "description" => $data['post_meta']['meta_description']
        ]);
    }
}
