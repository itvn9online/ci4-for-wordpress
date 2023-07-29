<?php

namespace App\Models;

// Libraries
use App\Libraries\PostType;

//
class PostBase extends EbModel
{
    public $table = 'posts';
    //public $primaryKey = 'ID';

    protected $createdField = 'post_date';
    protected $updatedField = 'post_modified';

    public $metaTable = 'postmeta';
    //public $metaKey = 'meta_id';

    public $product_html_node = '';
    public $itempropLogoHtmlNode = '';
    public $itempropImageHtmlNode = '';
    public $itempropAuthorHtmlNode = '';
    // tùy chỉnh thẻ LI hoặc thẻ DIV
    protected $product_html_tag = 'div';
    // tùy chỉnh class css cho thẻ bao ngoài cùng của danh sách post
    protected $product_list_css = '';

    public $blog_html_node = '';
    public $getconfig = NULL;

    public $primary_controller = 'posts';
    //public $primary_edit_view = 'posts';

    public function __construct()
    {
        parent::__construct();

        //
        $this->option_model = new \App\Models\Option();
        $this->term_model = new \App\Models\Term();

        //
        $itemprop_cache_logo = $this->base_model->scache('itemprop_logo');
        if ($itemprop_cache_logo !== NULL) {
            $this->itempropLogoHtmlNode = $itemprop_cache_logo;
        }
        $itemprop_cache_author = $this->base_model->scache('itemprop_author');
        if ($itemprop_cache_author !== NULL) {
            $this->itempropAuthorHtmlNode = $itemprop_cache_author;
        }
        /*
        if (file_exists(WRITEPATH . 'itemprop-logo.txt')) {
            $this->itempropLogoHtmlNode = file_get_contents(WRITEPATH . 'itemprop-logo.txt');
        }
        if (file_exists(WRITEPATH . 'itemprop-author.txt')) {
            $this->itempropAuthorHtmlNode = file_get_contents(WRITEPATH . 'itemprop-author.txt');
        }
        */

        //
        $postbase_construct = $this->base_model->scache('postbase_construct');
        if ($postbase_construct === NULL) {
            $this->itempropImageHtmlNode = file_get_contents(VIEWS_PATH . 'html/structured-data/itemprop-image.html');

            // tạo block html cho phần sản phẩm
            //echo THEMEPATH . '<br>' . PHP_EOL;
            if ($this->product_html_node == '') {
                // thread_node
                $this->product_html_node = $this->base_model->get_html_tmp('products_node');
            }

            // tạo block html cho phần tin tức
            if ($this->blog_html_node == '') {
                // blogs_node
                $this->blog_html_node = $this->base_model->get_html_tmp('posts_node');
            }
            //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
            //echo debug_backtrace()[1]['function'] . '<br>' . PHP_EOL;

            //
            $this->base_model->scache('postbase_construct', [
                'itemprop_image' => $this->itempropImageHtmlNode,
                'product_html_node' => $this->product_html_node,
                'blog_html_node' => $this->blog_html_node,
            ], HOUR);
        } else {
            $this->itempropImageHtmlNode = $postbase_construct['itemprop_image'];
            if ($this->product_html_node == '') {
                $this->product_html_node = $postbase_construct['product_html_node'];
            }
            if ($this->blog_html_node == '') {
                $this->blog_html_node = $postbase_construct['blog_html_node'];
            }
        }

        //
        $structured_data = file_get_contents(VIEWS_PATH . 'html/structured-data/NewsArticle.html');
        foreach ([
            'product_html_tag' => $this->product_html_tag,
            'product_list_css' => $this->product_list_css,
            //'primary_controller' => $this->primary_controller,
        ] as $k => $v) {
            $structured_data = str_replace('{{' . $k . '}}', $v, $structured_data);
        }

        //
        $this->product_html_node = str_replace('{{product_html_node}}', $this->product_html_node, $structured_data);
        //echo $this->product_html_node . PHP_EOL;
        $this->blog_html_node = str_replace('{{product_html_node}}', $this->blog_html_node, $structured_data);
        //echo $this->blog_html_node . PHP_EOL;

        //
        $getconfig = $this->option_model->list_config();
        //print_r( $getconfig );
        $getconfig = (object) $getconfig;
        $getconfig->cf_posts_size = $this->base_model->get_config($getconfig, 'cf_posts_size', 1);
        //$getconfig->cf_blog_size = $this->base_model->get_config($getconfig, 'cf_blog_size', '2/3');
        //echo $getconfig->cf_blog_size;
        /*
        if ($getconfig->cf_blog_description_length == '') {
            $getconfig->cf_blog_description_length = 250;
        }
        */
        //print_r( $getconfig );
        $this->getconfig = $getconfig;

        // kích thước hình ảnh sẽ sử dụng
        if ($getconfig->cf_thumbnail_size == '') {
            $this->cf_thumbnail_size = 'medium';
        } else {
            $this->cf_thumbnail_size = $getconfig->cf_thumbnail_size;
        }

        //
        //$this->session = \Config\Services::session();
    }

    // chỉ trả về link admin của 1 post
    public function get_admin_permalink($post_type = '', $id = 0, $controller_slug = 'posts')
    {
        if ($post_type == PostType::MENU) {
            $controller_slug = 'menus';
        }
        //$url = base_url( 'admin/' . $controller_slug . '/add' ) . '?post_type=' . $post_type;
        $url = base_url('admin/' . $controller_slug . '/add');
        if ($id > 0) {
            //$url .= '&id=' . $id;
            $url .= '?id=' . $id;
        }
        return $url;
    }

    // thường dùng trong view -> in ra link admin của 1 post
    public function admin_permalink($post_type = '', $id = 0, $controller_slug = 'posts')
    {
        echo $this->get_admin_permalink($post_type, $id, $controller_slug);
    }

    // kiểm tra url đã chuẩn chưa, chưa thì redirect về url chuẩn
    public function check_canonical($slug, $data)
    {
        // nếu slug trống
        if (
            $slug == '' ||
            // hoặc đúng là post_name
            $slug == $data['post_name'] ||
            // hoặc kiểu URL có .html, .html, .etc...
            strpos($slug, $data['post_name'] . '.') !== false
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
        header('HTTP/1.1 301 Moved Permanently');
        die(header('Location: ' . $redirect_to, TRUE, 301));
        //die( __CLASS__ . ':' . __LINE__ );
    }

    /**
     * Kiểm tra dữ liệu đầu vào trước khi update post permalink -> tránh lỗi
     **/
    public function before_post_permalink($data)
    {
        // nếu có đủ các thông số còn thiếu thì tiến hành cập nhật permalink
        foreach ([
            'ID',
            'post_name',
            'post_type',
            'lang_key',
            'category_primary_slug',
            'category_second_slug',
        ] as $k) {
            if (!isset($data[$k])) {
                return false;
            }
        }
        return $this->update_post_permalink($data);
    }
    /**
     * Update update permalink định kỳ
     **/
    public function update_post_permalink($data, $base_url = '')
    {
        //
        if ($data['post_type'] == PostType::POST) {
            $url = WGR_POST_PERMALINK;
            /*
        } else if ($data['post_type'] == PostType::BLOG) {
            $url = WGR_BLOG_PERMALINK;
            */
        } else if ($data['post_type'] == PostType::PROD) {
            $url = WGR_PROD_PERMALINK;
        } else if ($data['post_type'] == PostType::PAGE) {
            $url = WGR_PAGE_PERMALINK;
        }
        // với phần hóa đơn thì khác bảng, và cũng không dùng permalink
        else if ($data['post_type'] == PostType::ORDER) {
            return '#';
        } else if (isset(WGR_CUS_POST_PERMALINK[$data['post_type']])) {
            $url = WGR_CUS_POST_PERMALINK[$data['post_type']];
        } else {
            $url = WGR_POSTS_PERMALINK;
        }
        //echo $data['post_type'] . '<br>' . PHP_EOL;
        //echo WGR_POSTS_PERMALINK . '<br>' . PHP_EOL;

        // thêm prefix cho url -> hỗ trợ đa ngôn ngữ sub-folder
        if (SITE_LANGUAGE_SUB_FOLDER == true && $data['lang_key'] != SITE_LANGUAGE_DEFAULT) {
            $url = $data['lang_key'] . '/' . $url;
        }

        //
        $tmp = [
            //'page_base' => PAGE_BASE_URL,
            'ID' => $data['ID'],
            'post_name' => $data['post_name'],
            'post_type' => $data['post_type'],
            'category_primary_slug' => $data['category_primary_slug'],
            'category_second_slug' => $data['category_second_slug'],
        ];
        foreach ($tmp as $k => $v) {
            $url = str_replace('%' . $k . '%', $v, $url);
        }
        $url = ltrim($url, '/');

        // update vào db để sau còn tái sử dụng -> nhẹ server
        $this->base_model->update_multiple(
            'posts',
            [
                // xóa cắp dấu // để tránh trường hợp gặp segment trống
                'post_permalink' => str_replace('//', '/', $url),
                // cập nhật giãn cách update lại permalink -> khi quá thời gian này sẽ tiến hành cập nhật permalink mới
                'updated_permalink' => time() + 3600,
            ],
            [
                'ID' => $data['ID'],
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
        //echo DYNAMIC_BASE_URL . PHP_EOL;
        return $this->get_post_permalink($data, DYNAMIC_BASE_URL);
    }
    // trả về url của 1 post
    public function get_the_permalink($data, $base_url = '')
    {
        return $this->get_post_permalink($data, $base_url);
    }
    public function get_post_permalink($data, $base_url = '')
    {
        //
        //return $base_url . $data[ 'post_type' ] . '/' . $data[ 'ID' ] . '/' . $data[ 'post_name' ] . '.html';
        //print_r($data);
        //return '#';

        // đoạn này sẽ để 1 thời gian, sau sẽ comment lại
        /*
        if (!isset($data['updated_permalink'])) {
            //print_r($data);
            //die(__FUNCTION__ . ' updated_permalink not found! ' . __CLASS__ . ':' . __LINE__);
            return $_SERVER['REQUEST_URI'] . '#updated_permalink-not-found';
        }
        */

        // sử dụng permalink có sẵn trong data
        /*
        if ($data['updated_permalink'] > time() && $data['post_permalink'] != '') {
            return $base_url . $data['post_permalink'];
        }
        */
        //echo $data['post_permalink'] . PHP_EOL;
        return $base_url . $data['post_permalink'];

        //
        //return $base_url . '?p=' . $data[ 'ID' ] . '&post_type=' . $data[ 'post_type' ] . '&slug=' . $data[ 'post_name' ];
        //return $base_url . 'p/' . $data[ 'post_type' ] . '/' . $data[ 'ID' ] . '/' . $data[ 'post_name' ] . '.html';
    }

    // thường dùng trong view -> in ra link admin của 1 post
    public function the_post_permalink($data)
    {
        echo $this->get_post_permalink($data);
    }
    public function the_permalink($data)
    {
        // gọi tên đầy đủ để dễ lọc function giữa post với term
        $this->the_post_permalink($data);
    }

    // trả về số thứ tự lớn nhất của 1 post type -> dùng khi muốn đưa 1 bài viết trong 1 post type lên đầu
    public function max_menu_order($post_type)
    {
        // lấy chap cuối cùng của truyện để tổng kết
        $a = $this->base_model->select(
            'menu_order',
            $this->table,
            array(
                // WHERE AND OR
                'post_type' => $post_type,
            ),
            array(
                'order_by' => array(
                    'menu_order' => 'DESC'
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 2,
                'limit' => 1
            )
        );
        //print_r( $a );
        if (!empty($a)) {
            return $a['menu_order'] * 1 + 1;
        }
        //print_r( $a );
        //die( __CLASS__ . ':' . __LINE__ );

        //
        return 0;
    }

    // trả về key cho post cache
    public function key_cache($id)
    {
        return 'post-' . $id . '-';
    }
    // cache cho phần post -> gán key theo mẫu thống nhất để sau còn xóa cache cho dễ
    public function the_cache($id, $key, $value = '', $time = MEDIUM_CACHE_TIMEOUT)
    {
        return $this->base_model->scache($this->key_cache($id) . $key, $value, $time);
    }
}
