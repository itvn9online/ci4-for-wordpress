<?php

namespace App\Controllers;

// Libraries
use App\Libraries\TaxonomyType;
use App\Libraries\DeletedStatus;

//
class C extends Home
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Hiển thị post tag
     **/
    public function post_tag($slug, $page_name = '', $page_num = 1)
    {
        if (is_numeric($slug)) {
            return $this->showCategory($slug, TaxonomyType::TAGS, $page_num, $slug);
        }
        return $this->tag_taxonomy($slug, TaxonomyType::TAGS, $page_name, $page_num);
    }

    /**
     * Hiển thị product tag
     **/
    public function product_tag($slug, $page_name = '', $page_num = 1)
    {
        if (is_numeric($slug)) {
            return $this->showCategory($slug, TaxonomyType::PROD_TAGS, $page_num, $slug);
        }
        return $this->tag_taxonomy($slug, TaxonomyType::PROD_TAGS, $page_name, $page_num);
    }

    protected function tag_taxonomy($slug, $taxonomy_type, $page_name = '', $page_num = 1)
    {
        $data = $this->base_model->select(
            'terms.term_id',
            'terms',
            array(
                // các kiểu điều kiện where
                'terms.slug' => $slug,
                'terms.is_deleted' => DeletedStatus::FOR_DEFAULT,
                'term_taxonomy.taxonomy' => $taxonomy_type,
            ),
            array(
                'join' => array(
                    'term_taxonomy' => 'term_taxonomy.term_id = terms.term_id',
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
                'limit' => 1
            )
        );
        // print_r($data);

        // 
        if (empty($data)) {
            return $this->page404('ERROR ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! ' . $taxonomy_type . ' not found...');
        }

        //
        return $this->showCategory($data['term_id'], $taxonomy_type, $page_num, $slug);
    }

    public function custom_taxonomy($taxonomy_type, $id, $slug = '', $page_name = '', $page_num = 1)
    {
        // chỉ kiểm tra custom taxonomy
        if (!in_array($taxonomy_type, [
            // TaxonomyType::TAGS,
            //TaxonomyType::OPTIONS,
            TaxonomyType::PROD_OTPS,
            //TaxonomyType::BLOG_TAGS,
            // TaxonomyType::PROD_TAGS,
        ])) {
            //echo $taxonomy_type . '<br>' . PHP_EOL;
            //echo $id . '<br>' . PHP_EOL;
            //echo $slug . '<br>' . PHP_EOL;
            //echo $page_num . '<br>' . PHP_EOL;
            // print_r(ARR_CUSTOM_TAXONOMY);

            // với custom taxonomy -> kiểm tra xem taxonomy này phải được đăng ký thì mới hiển thị ra
            if (!isset(ARR_CUSTOM_TAXONOMY[$taxonomy_type])) {
                return $this->page404('ERROR ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! Danh mục này chưa được đăng ký hiển thị...');
            }
            // hoặc custom taxonomy có đăng ký nhưng không hiển thị với người khác ngoại trừ admin
            else if (isset(ARR_CUSTOM_TAXONOMY[$taxonomy_type]['public']) && ARR_CUSTOM_TAXONOMY[$taxonomy_type]['public'] != 'on') {
                return $this->page404('ERROR ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! Bạn không được phép xem thông tin danh mục này...');
            }
        }
        //echo $taxonomy_type . '<br>' . PHP_EOL;

        //
        return $this->showCategory($id, $taxonomy_type, $page_num, $slug);
    }
}
