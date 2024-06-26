<?php

namespace App\Controllers;

// Libraries
use App\Libraries\DeletedStatus;
use App\Libraries\TaxonomyType;
use App\Libraries\PostType;

//
class Products extends Posts
{
    protected $post_type = PostType::PROD;
    protected $taxonomy = TaxonomyType::PROD_CATS;
    protected $file_view = 'product_view';

    public function __construct()
    {
        parent::__construct();
    }

    public function product_details($id, $slug)
    {
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
        return $this->post_details($id, $slug);
    }

    /**
     * hỗ trợ mẫu URL mặc định của trang tin tức -> thấy tốt cho seo
     **/
    public function product2_details($cat = '', $id = 0, $slug = '')
    {
        return $this->product_details($id, $slug);
    }

    public function products_list($slug, $set_page = '', $page_num = 1)
    {
        //echo $slug . ' <br>' . PHP_EOL;
        //echo $set_page . ' <br>' . PHP_EOL;
        //echo $page_num . ' <br>' . PHP_EOL;
        //echo 'blog list <br>' . PHP_EOL;

        //
        if ($slug == '') {
            die('404 slug error!');
        }

        // -> kiểm tra theo category
        $data = $this->term_model->get_taxonomy(
            array(
                // các kiểu điều kiện where
                'slug' => $slug,
                'is_deleted' => DeletedStatus::FOR_DEFAULT,
                'lang_key' => $this->lang_key,
                'taxonomy' => $this->taxonomy,
            )
        );
        //print_r($data);

        // có -> ưu tiên category
        if (!empty($data)) {
            return $this->category($data, PostType::PROD, $this->taxonomy, 'product_cat_view', [
                'page_num' => $page_num,
            ]);
        }

        //
        return $this->page404('ERROR ' . strtolower(__FUNCTION__) . ':' . __LINE__ . '! Cannot be determined post category...');
    }
}
