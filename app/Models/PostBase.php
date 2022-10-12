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
    protected $product_html_tag = 'li';
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
        if (file_exists(WRITEPATH . 'itemprop-logo.txt')) {
            $this->itempropLogoHtmlNode = file_get_contents(WRITEPATH . 'itemprop-logo.txt');
        }
        if (file_exists(WRITEPATH . 'itemprop-author.txt')) {
            $this->itempropAuthorHtmlNode = file_get_contents(WRITEPATH . 'itemprop-author.txt');
        }
        $this->itempropImageHtmlNode = file_get_contents(VIEWS_PATH . 'html/structured-data/itemprop-image.html');

        //
        $structured_data = file_get_contents(VIEWS_PATH . 'html/structured-data/NewsArticle.html');
        $structured_data = str_replace('{{product_html_tag}}', $this->product_html_tag, $structured_data);
        $structured_data = str_replace('{{product_list_css}}', $this->product_list_css, $structured_data);
        $structured_data = str_replace('{{primary_controller}}', $this->primary_controller, $structured_data);

        // tạo block html cho phần sản phẩm
        //echo THEMEPATH . '<br>' . "\n";
        if ($this->product_html_node == '')
            $this->product_html_node = $this->base_model->get_html_tmp('thread_node');
        $this->product_html_node = str_replace('{{product_html_node}}', $this->product_html_node, $structured_data);

        //
        $this->blog_html_node = $this->base_model->get_html_tmp('blogs_node');
        $this->blog_html_node = str_replace('{{blog_html_node}}', $this->blog_html_node, $structured_data);


        //
        $getconfig = $this->option_model->list_config();
        //print_r( $getconfig );
        $getconfig = (object)$getconfig;
        $getconfig->cf_product_size = $this->base_model->get_config($getconfig, 'cf_product_size', 1);
        $getconfig->cf_blog_size = $this->base_model->get_config($getconfig, 'cf_blog_size', '2/3');
        if ($getconfig->cf_blog_description_length == '') {
            $getconfig->cf_blog_description_length = 250;
        }
        //print_r( $getconfig );
        $this->getconfig = $getconfig;

        // kích thước hình ảnh sẽ sử dụng
        if ($getconfig->cf_thumbnail_size == '') {
            $this->cf_thumbnail_size = 'medium';
        }
        else {
            $this->cf_thumbnail_size = $getconfig->cf_thumbnail_size;
        }

    //
    //$this->session = \Config\ Services::session();
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
        if ($slug == '' ||
        // hoặc đúng là post_name
        $slug == $data['post_name'] ||
        // hoặc kiểu URL có .html, .html, .etc...
        strpos($slug, $data['post_name'] . '.') !== false) {
            // thì cho qua
            return true;
        }
        // không thì redirect về URL chuẩn
        $redirect_to = $this->get_full_permalink($data);
        //die( $redirect_to );
        if (strpos($redirect_to, '?') === false) {
            $redirect_to .= '?';
        }
        else {
            $redirect_to .= '&';
        }
        $redirect_to .= 'canonical=server&uri=' . urlencode($_SERVER['REQUEST_URI']);

        //
        header('HTTP/1.1 301 Moved Permanently');
        die(header('Location: ' . $redirect_to, TRUE, 301));
    //die( __CLASS__ . ':' . __LINE__ );
    }

    // trả về url với đầy đủ tên miền
    public function get_full_permalink($data)
    {
        return $this->get_the_permalink($data, DYNAMIC_BASE_URL);
    }

    // trả về url của 1 post
    public function get_the_permalink($data, $base_url = '')
    {
        //print_r($data);
        //return '#';

        // sử dụng permalink có sẵn trong data
        if ($data['post_permalink'] != '') {
            return $base_url . $data['post_permalink'];
        }

        //
        //return $base_url . $data[ 'post_type' ] . '/' . $data[ 'ID' ] . '/' . $data[ 'post_name' ] . '.html';

        //
        if ($data['post_type'] == PostType::POST) {
            //return $base_url . $data[ 'ID' ] . '/' . $data[ 'post_name' ];
            $url = WGR_POST_PERMALINK;
        }
        else if ($data['post_type'] == PostType::BLOG) {
            //return $base_url . PostType::BLOG . '-' . $data[ 'ID' ] . '/' . $data[ 'post_name' ];
            $url = WGR_BLOG_PERMALINK;
        }
        else if ($data['post_type'] == PostType::PAGE) {
            //return $base_url . PAGE_BASE_URL . $data[ 'post_name' ];
            $url = WGR_PAGE_PERMALINK;
        }
        // với phần hóa đơn thì khác bảng, và cũng không dùng permalink
        else if ($data['post_type'] == PostType::ORDER) {
            return '#';
        }
        else {
            $url = WGR_POSTS_PERMALINK;
        }

        //
        foreach ([
        'page_base' => PAGE_BASE_URL,
        'ID' => $data['ID'],
        'post_name' => $data['post_name'],
        'post_type' => $data['post_type'],
        ] as $k => $v) {
            $url = str_replace('%' . $k . '%', $v, $url);
        }

        // update vào db để sau còn tái sử dụng -> nhẹ server
        $this->base_model->update_multiple('posts', [
            'post_permalink' => $url,
        ], [
            'ID' => $data['ID'],
        ], [
            // hiển thị mã SQL để check
            //'show_query' => 1,
        ]);

        //
        return $base_url . $url;

    //
    //return $base_url . '?p=' . $data[ 'ID' ] . '&post_type=' . $data[ 'post_type' ] . '&slug=' . $data[ 'post_name' ];
    //return $base_url . 'p/' . $data[ 'post_type' ] . '/' . $data[ 'ID' ] . '/' . $data[ 'post_name' ] . '.html';
    }

    // thường dùng trong view -> in ra link admin của 1 post
    public function the_permalink($data)
    {
        echo $this->get_the_permalink($data);
    }

    // trả về số thứ tự lớn nhất của 1 post type -> dùng khi muốn đưa 1 bài viết trong 1 post type lên đầu
    public function max_menu_order($post_type)
    {
        // lấy chap cuối cùng của truyện để tổng kết
        $a = $this->base_model->select('menu_order', $this->table, array(
            // WHERE AND OR
            'post_type' => $post_type,
        ), array(
            'order_by' => array(
                'menu_order' => 'DESC'
            ),
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 2,
            'limit' => 1
        ));
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