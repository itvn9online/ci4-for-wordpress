<?php

namespace App\Models;

// Libraries
// use App\Libraries\LanguageCost;
use App\Libraries\DeletedStatus;
use App\Libraries\TaxonomyType;
use App\Libraries\PostType;

//
class TermTag extends Term
{
    public $taxonomy = TaxonomyType::TAGS;
    public $post_type = PostType::POST;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Trả về thông tin của 1 tag theo slug
     **/
    public function getTagBySlug($slug, $taxonomy_type = '', $limit = 1)
    {
        return $this->base_model->select(
            'terms.term_id',
            'terms',
            array(
                // các kiểu điều kiện where
                'terms.slug' => $slug,
                'terms.is_deleted' => DeletedStatus::FOR_DEFAULT,
                'term_taxonomy.taxonomy' => $taxonomy_type == '' ? $this->taxonomy : $taxonomy_type,
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
                'limit' => $limit,
            )
        );
    }

    /**
     * Trả về danh sách bài viết hoặc sản phẩm của 1 tag
     **/
    public function getPostByTagSlug($slug, $limit = 10)
    {
        $post_model = new \App\Models\Post();

        // 
        $tag_id = $this->getTagBySlug($slug, $this->taxonomy);
        // print_r($tag_id);

        // 
        if (empty($tag_id)) {
            return [];
        }

        // 
        $tag_data = $this->get_taxonomy([
            // các kiểu điều kiện where
            'term_id' => $tag_id['term_id'],
            'is_deleted' => DeletedStatus::FOR_DEFAULT,
            // 'lang_key' => $this->lang_key,
            'taxonomy' => TaxonomyType::PROD_TAGS,
        ], [
            // 'show_query' => 1,
        ]);
        // print_r($tag_data);

        // 
        $data = $post_model->post_category($this->post_type, $tag_data, [
            'offset' => 0,
            'limit' => $limit,
        ]);
        if (!empty($data)) {
            $data = $post_model->list_meta_post($data);
        }
        // print_r($data);

        // 
        return $data;
    }
}
