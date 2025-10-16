<?php

namespace App\Models;

// Libraries
use App\Libraries\PostType;
use App\Libraries\TaxonomyType;
use App\Libraries\DeletedStatus;
use App\Libraries\CommentType;

//
class Post extends PostProducts
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
        //echo __FUNCTION__ . '<br>' . "\n";
        //echo $id . '<br>' . "\n";

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
        if ($this->base_model->scache(__FUNCTION__) !== null) {
            return false;
        }
        // luôn tạo giãn cách để tránh update liên tục -> chỉ 1 người update là đủ
        $this->base_model->scache(__FUNCTION__, time(), 120);

        //
        $allow_post_type = [
            PostType::PAGE,
            PostType::POST,
            //PostType::BLOG,
            PostType::PROD,
        ];
        foreach (ARR_CUSTOM_POST_TYPE as $k => $v) {
            if (!in_array($k, $allow_post_type)) {
                $allow_post_type[] = $k;
            }
        }

        // lấy các post chưa có permalink để update
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
            $allow_taxonomy = [
                TaxonomyType::POSTS,
                TaxonomyType::TAGS,
                //TaxonomyType::BLOGS,
                //TaxonomyType::BLOG_TAGS,
                TaxonomyType::PROD_CATS,
                TaxonomyType::PROD_OTPS,
                TaxonomyType::PROD_TAGS,
            ];
            foreach (ARR_CUSTOM_TAXONOMY as $k => $v) {
                if (!in_array($k, $allow_taxonomy)) {
                    $allow_taxonomy[] = $k;
                }
            }

            // lấy các term chưa có permalink để update
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
                $post_content = trim(str_replace('&nbsp;', ' ', strip_tags($data['post_content'])));
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
     * Tạo cấu trúc dữ liệu cho product
     **/
    public function structuredProductData($data, $structured_data, $ops = [])
    {
        // print_r($structured_data);

        if (!isset($ops['type']) || $ops['type'] == '') {
            $ops['type'] = 'Product';
        }

        // 
        $highPrice = '0';
        $lowPrice = '0';
        $offerCount = '999';

        // 
        if (isset($data['post_meta'])) {
            if (isset($data['post_meta']['_regular_price']) && !empty($data['post_meta']['_regular_price'])) {
                $highPrice = $data['post_meta']['_regular_price'];
                $lowPrice = $data['post_meta']['_regular_price'];
            }
            if (isset($data['post_meta']['_sale_price']) && !empty($data['post_meta']['_sale_price'])) {
                $lowPrice = $data['post_meta']['_sale_price'];
            }
        }
        if (isset($data['post_viewed']) && !empty($data['post_viewed'])) {
            $offerCount = $data['post_viewed'];
        }

        // 
        $arr = [
            "@context" => "http://schema.org",
            "@type" => $ops['type'],
            // "@id" => $structured_data['p_link'] . "#product",
            "@id" => $structured_data['p_link'] . "#richSnippet",
            "name" => $data['post_title'],
            "url" => $structured_data['p_link'],
            "description" => $data['post_meta']['meta_description'],
            // "category" => "Music &gt; Albums",
            // "mainEntityOfPage" => [
            //     "@id" => $structured_data['p_link'] . "#webpage"
            // ],
            // "image" => $structured_data['post_img'],
            "image" => [
                "@type" => "ImageObject",
                "url" => $structured_data['post_img'],
                "url" => $structured_data['post_img'],
                "width" => $structured_data['trv_width_img'],
                "height" => $structured_data['trv_height_img'],
            ],
            "sku" => $data['ID'],
            "offers" => [
                // type = Offer -> chỉ hỗ trợ price
                // "@type" => "Offer",
                // type = AggregateOffer -> hỗ trợ highPrice, lowPrice, offerCount
                "@type" => "AggregateOffer",
                // "price" => $lowPrice,
                "highPrice" => $highPrice,
                "lowPrice" => $lowPrice,
                "offerCount" => $offerCount,
                "priceValidUntil" => date('Y') . "-12-31",
                "priceSpecification" => [
                    "price" => $lowPrice,
                    "priceCurrency" => $structured_data['currency_sd_format'],
                    "valueAddedTaxIncluded" => "false"
                ],
                "priceCurrency" => $structured_data['currency_sd_format'],
                "availability" => "http://schema.org/InStock",
                "url" => $structured_data['p_link'],
                "seller" => [
                    "@type" => "Organization",
                    "name" => $structured_data['name'],
                    "url" => DYNAMIC_BASE_URL
                ]
            ],
        ];

        // thêm phần review sản phẩm
        $review_where = [
            'comment_post_ID' => $structured_data['ID'],
            'comment_type' => CommentType::COMMENT,
            'comment_approved' => CommentType::APPROVED,
        ];

        $review_count = $this->base_model->select_count(
            'comment_ID',
            'comments',
            $review_where,
            array(
                // hiển thị mã SQL để check
                // 'show_query'  => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                // trả về COUNT(column_name) AS column_name
                // 'selectCount' => 'comment_ID',
                // trả về tổng số bản ghi -> tương tự mysql num row
                //'getNumRows' => 1,
                //'offset' => 0,
                // 'limit' => 5
            )
        );
        // print_r($review_count);

        if ($review_count > 0) {
            $review_data = $this->base_model->select(
                '*',
                'comments',
                $review_where,
                array(
                    'order_by' => array(
                        'comment_ID' => 'DESC',
                    ),
                    // hiển thị mã SQL để check
                    // 'show_query'  => 1,
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
            // print_r($review_data);

            $arr['aggregateRating'] = [
                "@type" => "AggregateRating",
                "ratingValue" => "5",
                "reviewCount" => $review_count
            ];

            // 
            $arr_reviews = [];
            foreach ($review_data as $v) {
                $arr_reviews[] = [
                    "@type" => "Review",
                    "reviewRating" => [
                        "@type" => "Rating",
                        "bestRating" => "5",
                        "ratingValue" => "5",
                        "worstRating" => "1"
                    ],
                    "author" => ["@type" => "Person", "name" => $v['comment_author']],
                    "reviewBody" => $v['comment_content'],
                    "datePublished" => $v['comment_date']
                ];
            }
            $arr['review'] = $arr_reviews;
        }

        //
        return $this->base_model->dynamicSchema($arr);
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
        } else if ($ops['type'] == 'Product') {
            return $this->structuredProductData($data, $structured_data, $ops);
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

    /**
     * Trả về danh sách controller theo từng post_type
     **/
    public function controllerByType()
    {
        return PostType::controllerList();
    }
}
