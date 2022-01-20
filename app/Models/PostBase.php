<?php

namespace App\ Models;

// Libraries
//use App\ Libraries\ LanguageCost;
use App\ Libraries\ PostType;
//use App\ Libraries\ TaxonomyType;
//use App\ Libraries\ DeletedStatus;

//
class PostBase extends EbModel {
    public $table = 'posts';
    //public $primaryKey = 'ID';

    protected $createdField = 'post_date';
    protected $updatedField = 'post_modified';

    public $metaTable = 'postmeta';
    //public $metaKey = 'meta_id';

    public $product_html_node = '';
    protected $product_html_tag = 'li';
    public $blog_html_node = '';
    public $getconfig = NULL;

    public $primary_controller = 'posts';
    //public $primary_edit_view = 'posts';

    public function __construct() {
        parent::__construct();

        //
        $this->option_model = new\ App\ Models\ Option();
        $this->term_model = new\ App\ Models\ Term();

        // tạo block html cho phần sản phẩm
        //echo THEMEPATH . '<br>' . "\n";
        $this->product_html_node = $this->base_model->get_html_tmp( 'thread_node' );
        if ( $this->product_html_tag == 'li' ) {
            $this->product_html_node = '<li data-id="{tmp.ID}" data-control="' . $this->primary_controller . '" data-type="{tmp.post_type}" data-price="{tmp.trv_num_giamoi}" data-per="{tmp.pt}" data-link="{tmp.p_link}" data-status="{tmp.product_status}" class="hide-if-gia-zero">' . $this->product_html_node . '</li>';
        }

        //
        $this->blog_html_node = $this->base_model->get_html_tmp( 'blogs_node' );


        //
        $getconfig = $this->option_model->list_config();
        //print_r( $getconfig );
        $getconfig = ( object )$getconfig;
        $getconfig->cf_product_size = $this->base_model->get_config( $getconfig, 'cf_product_size', 1 );
        $getconfig->cf_blog_size = $this->base_model->get_config( $getconfig, 'cf_blog_size', '2/3' );
        if ( $getconfig->cf_blog_description_length == '' ) {
            $getconfig->cf_blog_description_length = 250;
        }
        //print_r( $getconfig );
        $this->getconfig = $getconfig;

        // kích thước hình ảnh sẽ sử dụng
        if ( $getconfig->cf_thumbnail_size == '' ) {
            $this->cf_thumbnail_size = 'medium';
        } else {
            $this->cf_thumbnail_size = $getconfig->cf_thumbnail_size;
        }

        //
        //$this->session = \Config\ Services::session();
    }

    // chỉ trả về link admin của 1 post
    public function get_admin_permalink( $post_type = '', $id = 0, $controller_slug = 'posts' ) {
        if ( $post_type == PostType::MENU ) {
            $controller_slug = 'menus';
        }
        $url = base_url( 'admin/' . $controller_slug . '/add' ) . '?post_type=' . $post_type;
        if ( $id > 0 ) {
            $url .= '&id=' . $id;
        }
        return $url;
    }

    // thường dùng trong view -> in ra link admin của 1 post
    public function admin_permalink( $post_type = '', $id = 0, $controller_slug = 'posts' ) {
        echo $this->get_admin_permalink( $post_type, $id, $controller_slug );
    }

    // trả về url của 1 post
    public function get_the_permalink( $data ) {
        //print_r( $data );

        //
        if ( $data[ 'post_type' ] == PostType::POST ) {
            return DYNAMIC_BASE_URL . $data[ 'ID' ] . '/' . $data[ 'post_name' ];
        } else if ( $data[ 'post_type' ] == PostType::BLOG ) {
            return DYNAMIC_BASE_URL . PostType::BLOG . '-' . $data[ 'ID' ] . '/' . $data[ 'post_name' ];
        } else if ( $data[ 'post_type' ] == PostType::PAGE ) {
            return DYNAMIC_BASE_URL . PAGE_BASE_URL . $data[ 'post_name' ];
        }
        return DYNAMIC_BASE_URL . '?p=' . $data[ 'ID' ] . '&post_type=' . $data[ 'post_type' ] . '&slug=' . $data[ 'post_name' ];
    }

    // thường dùng trong view -> in ra link admin của 1 post
    public function the_permalink( $data ) {
        echo $this->get_the_permalink( $data );
    }

    // trả về số thứ tự lớn nhất của 1 post type -> dùng khi muốn đưa 1 bài viết trong 1 post type lên đầu
    public function max_menu_order( $post_type ) {
        // lấy chap cuối cùng của truyện để tổng kết
        $a = $this->base_model->select( 'menu_order', $this->table, array(
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
        ) );
        //print_r( $a );
        if ( !empty( $a ) ) {
            return $a[ 'menu_order' ] * 1 + 1;
        }
        //print_r( $a );
        //die( __FILE__ . ':' . __LINE__ );

        //
        return 0;
    }
}