<?php
namespace App\ Controllers\ Admin;

// Libraries
use App\ Libraries\ PostType;
use App\ Libraries\ TaxonomyType;
use App\ Libraries\ LanguageCost;

//
class Posts extends Admin {
    protected $post_type = PostType::POST;
    protected $name_type = '';
    //private $detault_type = '';

    // các taxonomy được hỗ trợ -> cái nào trống nghĩa là không hỗ trợ theo post_type tương ứng
    protected $taxonomy = TaxonomyType::POSTS;
    protected $tags = TaxonomyType::TAGS;
    protected $options = TaxonomyType::OPTIONS;

    // tham số dùng để thay đổi URL cho controller nếu muốn
    protected $controller_slug = 'posts';
    // tham số dùng để đổi file view khi add hoặc edit bài viết nếu muốn
    protected $add_edit_view = 'posts';
    // tham số dùng để đổi file view khi xem danh sách bài viết nếu muốn
    protected $add_list_view = 'posts';
    // dùng để chọn xem hiển thị nhóm sản phẩm nào ra ở phần danh mục
    protected $main_category_key = 'post_category';

    /*
     * khi update hoặc insert sẽ kiểm tra xem các dữ liệu trong này có không, nếu có không sẽ gán mặc định
     * vì các checkbox khi bỏ chọn tất cả sẽ không xuất hiện trong post -> không được update
     */
    protected $default_post_data = [];

    /*
     * for_extends: khi một controller extends lại class này và sử dụng các post type khác thì khai báo nó bằng true để bỏ qua các điều kiện kiểm tra
     */
    public function __construct( $for_extends = false ) {
        parent::__construct();

        // kiểm tra quyền truy cập của tài khoản hiện tại
        $this->check_permision( __CLASS__ );

        // hỗ trợ lấy theo params truyền vào từ url
        $this->post_type = $this->MY_get( 'post_type', $this->post_type );

        // chỉ kiểm tra các điều kiện này nếu không được chỉ định là extends
        if ( $for_extends === false ) {
            // lọc bài viết dựa theo post type
            //$this->detault_type = PostType::POST;
            $this->name_type = PostType::list( $this->post_type );

            // báo lỗi nếu không xác định được post_type
            //if ( $this->post_type == '' || $this->name_type == '' ) {
            if ( $this->name_type == '' ) {
                die( 'post type (' . $this->post_type . ') not register in system!' );
            }

            /*
            // -> category tương ứng
            if ( $this->post_type == PostType::ADS ) {
                $this->taxonomy = TaxonomyType::ADS;
            } else if ( $this->post_type == PostType::BLOG ) {
                $this->taxonomy = TaxonomyType::BLOGS;
                $this->tags = TaxonomyType::BLOG_TAGS;
            } else if ( $this->post_type == PostType::MENU ) {
                // riêng với phần menu, do dùng chung controller với post -> kiểm tra lại permission lần nữa cho chắc
                //$this->check_permision( 'Menus' );

                //$this->taxonomy = TaxonomyType::MENU;
                $this->controller_slug = 'menus';
            } else if ( $this->post_type == PostType::PAGE ) {
                //$this->taxonomy = TaxonomyType::PAGE;
            } else {
                $this->taxonomy = TaxonomyType::POSTS;
                $this->tags = TaxonomyType::TAGS;
            }
            $this->options = TaxonomyType::OPTIONS;
            */
        }
    }

    public function index() {
        if ( $this->MY_get( 'auto_update_module' ) != '' ) {
            return $this->action_update_module();
        }

        //
        $post_per_page = 20;
        // URL cho các action dùng chung
        $for_action = '';
        // URL cho phân trang
        $urlPartPage = 'admin/' . $this->controller_slug . '?part_type=' . $this->post_type;

        //
        $by_keyword = $this->MY_get( 's' );
        $post_status = $this->MY_get( 'post_status' );
        $by_term_id = $this->MY_get( 'term_id', 0 );

        // các kiểu điều kiện where
        $where = [
            //'posts.post_status !=' => PostType::DELETED,
            'posts.post_type' => $this->post_type,
            'posts.lang_key' => $this->lang_key
        ];

        // tìm kiếm theo từ khóa nhập vào
        $where_or_like = [];
        if ( $by_keyword != '' ) {
            $urlPartPage .= '&s=' . $by_keyword;
            $for_action .= '&s=' . $by_keyword;

            //
            $by_like = $this->base_model->_eb_non_mark_seo( $by_keyword );
            // tối thiểu từ 1 ký tự trở lên mới kích hoạt tìm kiếm
            if ( strlen( $by_like ) > 0 ) {
                //var_dump( strlen( $by_like ) );
                // nếu là số -> chỉ tìm theo ID
                if ( is_numeric( $by_like ) === true ) {
                    $where_or_like = [
                        'ID' => $by_like * 1,
                        //'post_author' => $by_like,
                        //'post_parent' => $by_like,
                    ];
                } else {
                    $where_or_like = [
                        'post_name' => $by_like,
                        'post_title' => $by_keyword,
                    ];
                }
            }
        }

        //
        if ( $post_status == '' ) {
            $by_post_status = [
                PostType::DRAFT,
                PostType::PUBLIC,
                PostType::PENDING,
            ];
        } else {
            $urlPartPage .= '&post_status=' . $post_status;
            $for_action .= '&post_status=' . $post_status;

            $by_post_status = [
                $post_status,
            ];
        }

        // tổng kết filter
        $filter = [
            'where_in' => array(
                'posts.post_status' => $by_post_status
            ),
            'or_like' => $where_or_like,
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 0,
            'limit' => -1
        ];

        // nếu có lọc theo term_id -> thêm câu lệnh để lọc
        if ( $by_term_id > 0 ) {
            $urlPartPage .= '&term_id=' . $by_term_id;
            $for_action .= '&term_id=' . $by_term_id;

            $where[ 'term_taxonomy.term_id' ] = $by_term_id;

            $filter[ 'join' ] = [
                'term_relationships' => 'term_relationships.object_id = posts.ID',
                'term_taxonomy' => 'term_relationships.term_taxonomy_id = term_taxonomy.term_taxonomy_id',
            ];
        }
        //print_r( $where );
        //print_r( $filter );


        /*
         * phân trang
         */
        $totalThread = $this->base_model->select( 'COUNT(ID) AS c', 'posts', $where, $filter );
        //print_r( $totalThread );
        $totalThread = $totalThread[ 0 ][ 'c' ];
        //print_r( $totalThread );

        //
        if ( $totalThread > 0 ) {
            $page_num = $this->MY_get( 'page_num', 1 );

            $totalPage = ceil( $totalThread / $post_per_page );
            if ( $totalPage < 1 ) {
                $totalPage = 1;
            }
            //echo $totalPage . '<br>' . "\n";
            if ( $page_num > $totalPage ) {
                $page_num = $totalPage;
            } else if ( $page_num < 1 ) {
                $page_num = 1;
            }
            $for_action .= $page_num > 1 ? '&page_num=' . $page_num : '';
            //echo $totalThread . '<br>' . "\n";
            //echo $totalPage . '<br>' . "\n";
            $offset = ( $page_num - 1 ) * $post_per_page;

            //
            $pagination = $this->base_model->EBE_pagination( $page_num, $totalPage, $urlPartPage, '&page_num=' );


            // select dữ liệu từ 1 bảng bất kỳ
            $filter[ 'offset' ] = $offset;
            $filter[ 'limit' ] = $post_per_page;
            $filter[ 'order_by' ] = [
                //'posts.menu_order' => 'DESC',
                'posts.ID' => 'DESC',
                //'posts.post_date' => 'DESC',
                //'post_modified' => 'DESC',
            ];
            $data = $this->base_model->select( '*', 'posts', $where, $filter );

            //
            $data = $this->post_model->list_meta_post( $data );
            //print_r( $data );

            // xử lý dữ liệu cho angularjs
            foreach ( $data as $k => $v ) {
                // không cần hiển thị nội dung
                $v[ 'post_content' ] = '';

                // lấy 1 số dữ liệu khác gán vào, để angularjs chỉ việc hiển thị
                $v[ 'admin_permalink' ] = $this->post_model->get_admin_permalink( $this->post_type, $v[ 'ID' ], $this->controller_slug );
                $v[ 'the_permalink' ] = $this->post_model->get_the_permalink( $v );
                $v[ 'thumbnail' ] = $this->post_model->get_list_thumbnail( $v[ 'post_meta' ] );
                $v[ 'main_category_key' ] = $this->post_model->return_meta_post( $v[ 'post_meta' ], $this->main_category_key );

                //
                //print_r( $v );

                //
                $data[ $k ] = $v;
            }
        } else {
            $data = [];
            $pagination = '';
        }

        //
        $this->teamplate_admin[ 'content' ] = view( 'admin/' . $this->add_list_view . '/list', array(
            'for_action' => $for_action,
            'by_post_status' => $by_post_status,
            'post_status' => $post_status,
            'by_keyword' => $by_keyword,
            'by_term_id' => $by_term_id,
            'controller_slug' => $this->controller_slug,
            'pagination' => $pagination,
            'totalThread' => $totalThread,
            'main_category_key' => $this->main_category_key,
            'data' => $data,
            'taxonomy' => $this->taxonomy,
            'post_type' => $this->post_type,
            'name_type' => $this->name_type,
        ) );
        //return $this->teamplate_admin[ 'content' ];
        return view( 'admin/admin_teamplate', $this->teamplate_admin );
    }

    public function lists() {
        return $this->index();
    }

    public function add() {
        $id = $this->MY_get( 'id', 0 );
        $auto_update_module = $this->MY_get( 'auto_update_module', 0 );

        //
        if ( !empty( $this->MY_post( 'data' ) ) ) {
            // nếu là nhân bản
            if ( $this->MY_post( 'is_duplicate', 0 ) * 1 === 1 ) {
                //print_r( $_POST );
                $dup_data = $this->MY_post( 'data' );
                //print_r( $dup_data );

                // đổi lại tiêu đề để tránh trùng lặp
                if ( isset( $dup_data[ 'post_title' ] ) ) {
                    $duplicate_title = explode( '- Duplicate', $dup_data[ 'post_title' ] );
                    $dup_data[ 'post_title' ] = trim( $duplicate_title[ 0 ] ) . ' - Duplicate ' . date( 'Ymd-His' );

                    // tạo lại slug
                    $dup_data[ 'post_name' ] = '';
                }
                //print_r( $dup_data );

                // -> bỏ ID đi
                $id = 0;

                //
                //die( __CLASS__ . ':' . __LINE__ );

                //
                return $this->add_new( $dup_data );
            }

            // update
            if ( $id > 0 ) {
                return $this->update( $id );
            }
            // insert
            return $this->add_new();
        }

        // edit
        $url_next_post = '';
        if ( $id != '' ) {
            // select dữ liệu từ 1 bảng bất kỳ
            $data = $this->post_model->select_post( $id, [
                'post_type' => $this->post_type,
            ] );
            if ( empty( $data ) ) {
                die( 'post not found!' );
            }

            // tự động cập nhật lại slug khi nhân bản
            if ( strstr( $data[ 'post_name' ], '-duplicate-' ) == true && strstr( $data[ 'post_title' ], ' - Duplicate ' ) == false ) {
                //echo 'bbbbbbbbbbbbb';
                $this->post_model->update_post( $id, [
                    'post_title' => $data[ 'post_title' ],
                    'post_name' => '',
                ], [
                    'post_type' => $this->post_type,
                ] );

                //
                die( header( 'Location:' . DYNAMIC_BASE_URL . ltrim( $_SERVER[ 'REQUEST_URI' ], '/' ) ) );
                //return redirect()->to( DYNAMIC_BASE_URL . ltrim( $_SERVER[ 'REQUEST_URI' ], '/' ) );
            }

            // lấy bài tiếp theo để auto next
            if ( $auto_update_module > 0 ) {
                //echo __CLASS__ . ':' . __LINE__ . '<br>' . "\n";

                //
                $url_next_post = $this->action_update_module( $id );
            }
        }
        // add
        else {
            $data = $this->base_model->default_data( 'posts' );
            $data[ 'post_meta' ] = [];
        }
        //print_r( $this->session_data );
        //print_r( $data );


        //
        $post_cat = '';
        $post_tags = '';
        $parent_post = [];
        // lấy danh sách các trang để chọn bài cha
        if ( $this->post_type == PostType::PAGE ) {
            // các kiểu điều kiện where
            $where = [
                //'posts.post_status !=' => PostType::DELETED,
                'posts.post_type' => $this->post_type,
                'posts.lang_key' => $this->lang_key
            ];

            $filter = [
                'where_in' => array(
                    'posts.post_status' => array(
                        PostType::DRAFT,
                        PostType::PUBLIC,
                        PostType::PENDING,
                    )
                ),
                'where_not_in' => array(
                    'posts.ID' => array(
                        $id
                    )
                ),
                'order_by' => array(
                    'posts.menu_order' => 'DESC',
                    'posts.post_date' => 'DESC',
                    //'post_modified' => 'DESC',
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 0,
                //'limit' => $post_per_page
            ];
            $parent_post = $this->base_model->select( 'posts.ID, posts.post_title', 'posts', $where, $filter );
            //print_r( $parent_post );
        }
        // lấy danh sách các nhóm để add cho post
        else {
            //$post_cat = $this->term_model->get_all_taxonomy( $this->taxonomy, 0, [ 'get_child' => 1 ], $this->taxonomy . '_get_child' );
            $post_cat = $this->taxonomy;
            if ( $this->tags != '' ) {
                //$post_tags = $this->term_model->get_all_taxonomy( $this->tags, 0, [ 'get_child' => 1 ], $this->tags . '_get_child' );
                $post_tags = $this->tags;
            }
        }

        //
        if ( $this->debug_enable === true ) {
            echo '<!-- ';
            print_r( $data );
            echo ' -->';
        }

        //
        $this->teamplate_admin[ 'content' ] = view( 'admin/' . $this->add_edit_view . '/add', array(
            'controller_slug' => $this->controller_slug,
            'lang_key' => $this->lang_key,
            'auto_update_module' => $auto_update_module,
            'url_next_post' => $url_next_post,
            'post_cat' => $post_cat,
            'post_tags' => $post_tags,
            'parent_post' => $parent_post,
            'data' => $data,
            'meta_detault' => PostType::meta_default( $this->post_type ),
            'taxonomy' => $this->taxonomy,
            'tags' => $this->tags,
            'post_type' => $this->post_type,
            'name_type' => $this->name_type,
        ) );
        return view( 'admin/admin_teamplate', $this->teamplate_admin );
    }

    protected function add_new( $data = NULL ) {
        if ( $data === NULL ) {
            $data = $this->MY_post( 'data' );
        }
        $data[ 'post_type' ] = $this->post_type;

        //
        //print_r( $data );
        //die( __CLASS__ . ':' . __LINE__ );
        $result_id = $this->post_model->insert_post( $data );
        if ( is_array( $result_id ) && isset( $result_id[ 'error' ] ) ) {
            $this->base_model->alert( $result_id[ 'error' ], 'error' );
        }

        //
        if ( $result_id > 0 ) {
            $this->base_model->alert( '', $this->post_model->get_admin_permalink( $this->post_type, $result_id, $this->controller_slug ) );
        }
        $this->base_model->alert( 'Lỗi tạo ' . $this->name_type . ' mới', 'error' );
    }

    protected function update( $id ) {
        $data = $this->MY_post( 'data' );
        //print_r( $data );
        //print_r( $_POST );

        // nhận dữ liệu default từ javascript khởi tạo và truyền vào trong quá trình submit
        if ( isset( $data[ 'default_post_data' ] ) ) {
            foreach ( $data[ 'default_post_data' ] as $k => $v ) {
                if ( !isset( $this->default_post_data[ $k ] ) ) {
                    $this->default_post_data[ $k ] = '';
                }
            }
        }
        print_r( $this->default_post_data );
        foreach ( $this->default_post_data as $k => $v ) {
            if ( !isset( $data[ $k ] ) ) {
                $data[ $k ] = $v;
            }
        }

        //
        $result_id = $this->post_model->update_post( $id, $data, [
            'post_type' => $this->post_type,
        ] );

        // nếu có lỗi thì thông báo lỗi
        if ( $result_id !== true && is_array( $result_id ) && isset( $result_id[ 'error' ] ) ) {
            $this->base_model->alert( $result_id[ 'error' ], 'error' );
        }

        // dọn dẹp cache liên quan đến post này -> reset cache
        $this->cleanup_cache( $this->post_model->key_cache( $id ) );
        //
        if ( isset( $data[ 'post_title' ] ) ) {
            // bổ sung thêm xóa cache với menu
            if ( $this->post_type == PostType::MENU ) {
                $post_name = $this->base_model->_eb_non_mark_seo( $data[ 'post_title' ] );
                //echo $post_name . '<br>' . "\n";
                $this->cleanup_cache( 'get_the_menu-' . $post_name );
            }
            // hoặc page
            else if ( $this->post_type == PostType::PAGE ) {
                $this->cleanup_cache( 'get_page-' . $data[ 'post_name' ] );
            }
        }

        // xóa cache cho term liên quan
        if ( isset( $_POST[ 'post_meta' ] ) && isset( $_POST[ 'post_meta' ][ 'post_category' ] ) ) {
            foreach ( $_POST[ 'post_meta' ][ 'post_category' ] as $v ) {
                //echo $v . '<br>' . "\n";
                $this->cleanup_cache( $this->term_model->key_cache( $v ) );
            }
        }

        //
        $this->base_model->alert( 'Cập nhật ' . $this->name_type . ' thành công' );
    }

    // chuyển trang sau khi XÓA xong
    protected function after_delete_restore() {
        $for_redirect = base_url( 'admin/' . $this->controller_slug );
        $urlParams = [];

        //
        $page_num = $this->MY_get( 'page_num' );
        if ( $page_num != '' ) {
            $urlParams[] = 'page_num=' . $page_num;
        }

        //
        $post_status = $this->MY_get( 'post_status' );
        if ( $post_status != '' ) {
            $urlParams[] = 'post_status=' . $post_status;
        }

        //
        if ( count( $urlParams ) > 0 ) {
            $for_redirect .= '?' . implode( '&', $urlParams );
        }
        return $this->base_model->alert( '', $for_redirect );
    }
    protected function done_delete_restore( $id ) {
        die( '<script>top.done_delete_restore(' . $id . ');</script>' );
    }
    protected function before_delete_restore( $post_status ) {
        $id = $this->MY_get( 'id', 0 );

        $update = $this->post_model->update_post( $id, [
            'post_status' => $post_status
        ], [
            'post_type' => $this->post_type,
        ] );
        //print_r( $update );
        //die( __CLASS__ . ':' . __LINE__ );

        // nếu update thành công -> gửi lệnh javascript để ẩn bài viết bằng javascript
        if ( $update === true ) {
            return $this->done_delete_restore( $id );
        }
        // không thì nạp lại cả trang để kiểm tra cho chắc chắn
        return $this->after_delete_restore();
    }

    // xóa (tạm ẩn) 1 bản ghi
    public function delete() {
        return $this->before_delete_restore( PostType::DELETED );
    }

    // phục hồi 1 bản ghi
    public function restore() {
        return $this->before_delete_restore( PostType::DRAFT );
    }

    // xóa hoàn toàn 1 bản ghi
    protected function before_remove() {
        $id = $this->MY_get( 'id', 0 );

        // xem bản ghi này có được đánh dấu là XÓA không
        $data = $this->base_model->select( '*', $this->post_model->table, [
            'ID' => $id,
            'post_status' => PostType::DELETED,
            'post_type' => $this->post_type,
        ], array(
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 2,
            'limit' => 1
        ) );

        //
        if ( empty( $data ) ) {
            $this->base_model->alert( 'Không xác định được bản ghi cần XÓA', 'error' );
        }
        return $data;
    }
    public function remove( $confirm_delete = false ) {
        $data = $this->before_remove();

        /*
         * confirm_delete: thường được truyền tới từ custom post type và có nó thì sẽ xác nhận xóa hoàn toàn dữ liệu
         */
        if ( $confirm_delete === true ) {
            // XÓA dữ liệu chính
            $this->base_model->delete_multiple( $this->post_model->table, [
                // WHERE
                'ID' => $data[ 'ID' ],
            ] );

            // XÓA meta
            $this->base_model->delete_multiple( $this->post_model->metaTable, [
                // WHERE
                'post_id' => $data[ 'ID' ],
            ] );

            //
            return $data;
        }

        // mặc định chỉ hiển thị thông báo thôi
        //return $data;

        //
        $this->base_model->alert( 'Chức năng XÓA đang trong giai đoạn thử nghiệm', 'warning' );
    }

    //
    public function before_all_delete_restore( $post_status ) {
        $ids = $this->MY_post( 'ids', '' );
        if ( empty( $ids ) ) {
            $this->result_json_type( [
                'code' => __LINE__,
                'error' => 'ids not found!',
            ] );
        }

        //
        $ids = explode( ',', $ids );
        if ( count( $ids ) <= 0 ) {
            $this->result_json_type( [
                'code' => __LINE__,
                'error' => 'ids EMPTY!',
            ] );
        }

        //
        $result = $this->base_model->update_multiple( 'posts', [
            // SET
            'post_status' => $post_status
        ], [
            'post_status !=' => $post_status
        ], [
            'where_in' => array(
                'ID' => $ids
            ),
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
        ] );
        $this->result_json_type( [
            'code' => __LINE__,
            'result' => $result,
        ] );
    }

    // chức năng xóa nhiều tài khoản 1 lúc
    public function delete_all() {
        return $this->before_all_delete_restore( PostType::DELETED );
    }

    // chức năng xóa nhiều tài khoản 1 lúc
    public function restore_all() {
        return $this->before_all_delete_restore( PostType::DRAFT );
    }

    //
    private function createdThumbnail( $imagePath ) {
        $listSizeThumb = $this->config->item( 'list_thumbnail' );
        $listThumbFolder = $this->config->item( 'thumbnail_folder' );

        for ( $i = 0; $i < count( $listThumbFolder ); $i++ ) {

            $pimageFullPath = $imagePath; // đg dẫn full path

            $config_manip = array(
                'image_library' => 'gd2',
                'source_image' => $pimageFullPath,
                'new_image' => $this->config->item( 'base_path' ) . '/Images/' . $listThumbFolder[ $i ],
                'maintain_ratio' => TRUE,
                'width' => $listSizeThumb[ $i ][ 'width' ],
                'height' => $listSizeThumb[ $i ][ 'height' ],
            );

            $this->load->library( 'image_lib' );
            $this->image_lib->initialize( $config_manip );

            if ( !$this->image_lib->resize() ) {
                echo $this->image_lib->display_errors();
                die( ' lỗi resize' );
            }
            $this->image_lib->clear();
        }
    }

    // chức năng tự động cập nhật lại toàn bộ bài viết mỗi khi có cập nhật mới và cần auto submit
    private function action_update_module( $id = 0 ) {
        $where = [
            // các kiểu điều kiện where
            'post_status' => PostType::PUBLIC,
            'post_type' => $this->post_type,
        ];
        if ( $id > 0 ) {
            $where[ 'ID <' ] = $id;
        }

        //
        $data = $this->base_model->select( '*', $this->post_model->table, $where, array(
            'order_by' => array(
                'ID' => 'DESC'
            ),
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 2,
            'limit' => 1
        ) );
        //print_r( $data );

        //
        if ( empty( $data ) ) {
            if ( $id > 0 ) {
                return '';
            }
            echo __FUNCTION__ . '! All done.';
            return false;
        }

        // lấy link sửa bài viết trong admin
        $admin_permalink = $this->post_model->get_admin_permalink( $data[ 'post_type' ], $data[ 'ID' ], $this->controller_slug );
        //echo $admin_permalink . '<br>' . "\n";

        // thêm tham số tự động submit
        $admin_permalink .= '&auto_update_module=1';
        //echo $admin_permalink . '<br>' . "\n";

        //
        if ( $id > 0 ) {
            return $admin_permalink;
        }
        return redirect()->to( $admin_permalink );
    }
}