<?php

namespace App\Controllers\Admin;

// Libraries
use App\Libraries\TaxonomyType;
use App\Libraries\LanguageCost;
use App\Libraries\DeletedStatus;

//
class Terms extends Admin
{
    protected $taxonomy = '';
    protected $name_type = '';
    //private $default_taxonomy = '';

    // tham số dùng để thay đổi URL cho controller nếu muốn
    protected $controller_slug = 'terms';
    // tham số dùng để đổi file view khi add hoặc edit bài viết nếu muốn
    protected $add_view_path = 'terms';

    /*
     * for_extends: khi một controller extends lại class này và sử dụng các taxonomy khác (custom taxonomy) thì khai báo nó bằng true để bỏ qua các điều kiện kiểm tra
     */
    public function __construct($for_extends = false)
    {
        parent::__construct();

        // kiểm tra quyền truy cập của tài khoản hiện tại
        $this->check_permision(__CLASS__);

        // hỗ trợ lấy theo params truyền vào từ url
        if ($this->taxonomy == '') {
            $this->taxonomy = $this->MY_get('taxonomy', TaxonomyType::POSTS);
        }

        // chỉ kiểm tra các điều kiện này nếu không được chỉ định là extends
        if ($for_extends === false) {
            // lọc term dựa theo taxonomy
            //$this->default_taxonomy = TaxonomyType::POSTS;
            $this->name_type = TaxonomyType::typeList($this->taxonomy, true);

            // nếu không xác định được taxonomy
            if ($this->name_type == '') {
                global $arr_custom_taxonomy;
                //print_r( $arr_custom_taxonomy );

                // thử xem có phải custom taxonomy không
                if (isset($arr_custom_taxonomy[$this->taxonomy])) {
                    // xem có slug không
                    if (
                        isset($arr_custom_taxonomy[$this->taxonomy]['slug']) &&
                        // nếu có
                        $arr_custom_taxonomy[$this->taxonomy]['slug'] != '' &&
                        // mà khác nhau
                        $arr_custom_taxonomy[$this->taxonomy]['slug'] != $this->controller_slug
                    ) {

                        // tạo link redirect
                        $redirect_to = str_replace('/admin/' . $this->controller_slug, '/admin/' . $arr_custom_taxonomy[$this->taxonomy]['slug'], $_SERVER['REQUEST_URI']);
                        $redirect_to = rtrim(DYNAMIC_BASE_URL, '/') . $redirect_to;
                        //die( $redirect_to );

                        // và redirect tới đúng link
                        die(header('Location: ' . $redirect_to));
                    }
                    //die( $this->taxonomy );
                }

                // không xác định được thì báo lỗi
                die('Taxonomy not register in system: ' . $this->taxonomy);
            }
        }
    }

    public function index()
    {
        return $this->lists();
    }
    public function lists($ops = [])
    {
        $post_per_page = 100;
        // URL cho các action dùng chung
        $for_action = '';
        // URL cho phân trang
        $urlPartPage = 'admin/' . $this->controller_slug . '?part_type=' . $this->taxonomy;

        //
        $by_keyword = $this->MY_get('s');
        $by_is_deleted = $this->MY_get('is_deleted', DeletedStatus::FOR_DEFAULT);

        //
        if ($by_is_deleted > 0) {
            $urlPartPage .= '&is_deleted=' . $by_is_deleted;
            $for_action .= '&is_deleted=' . $by_is_deleted;
        }

        // tìm kiếm theo từ khóa nhập vào
        $where_or_like = [];
        if ($by_keyword != '') {
            $urlPartPage .= '&s=' . $by_keyword;
            $for_action .= '&s=' . $by_keyword;

            //
            $by_like = $this->base_model->_eb_non_mark_seo($by_keyword);
            // tối thiểu từ 1 ký tự trở lên mới kích hoạt tìm kiếm
            if (strlen($by_like) > 0) {
                //var_dump( strlen( $by_like ) );
                // nếu là số -> chỉ tìm theo ID
                if (is_numeric($by_like) === true) {
                    $where_or_like = [
                        'term_id' => $by_like * 1,
                        //'parent' => $by_like,
                    ];
                } else {
                    $where_or_like = [
                        'slug' => $by_like,
                        'name' => $by_keyword,
                    ];
                }
            }
        }

        //
        $filter = [
            'or_like' => $where_or_like,
            'by_is_deleted' => $by_is_deleted,
            'lang_key' => $this->lang_key,
            'limit' => -1,
        ];


        /*
         * phân trang
         */
        $totalThread = $this->term_model->count_all_taxonomy($this->taxonomy, 0, $filter);

        if ($totalThread > 0) {
            $page_num = $this->MY_get('page_num', 1);

            $totalPage = ceil($totalThread / $post_per_page);
            if ($totalPage < 1) {
                $totalPage = 1;
            }
            //echo $totalPage . '<br>' . PHP_EOL;
            if ($page_num > $totalPage) {
                $page_num = $totalPage;
            } else if ($page_num < 1) {
                $page_num = 1;
            }
            $for_action .= $page_num > 1 ? '&page_num=' . $page_num : '';
            //echo $totalThread . '<br>' . PHP_EOL;
            //echo $totalPage . '<br>' . PHP_EOL;
            $offset = ($page_num - 1) * $post_per_page;
            //echo $offset . '<br>' . PHP_EOL;
            //die( __CLASS__ . ':' . __LINE__ );

            //
            $pagination = $this->base_model->EBE_pagination($page_num, $totalPage, $urlPartPage, '&page_num=');

            //
            $filter['offset'] = $offset;
            $filter['limit'] = $post_per_page;
            // daidq (2021-01-24): tạm thời không cần lấy nhóm cấp 1
            if ($this->taxonomy == TaxonomyType::ADS) {
                $filter['get_meta'] = true;
                // với mục q.cáo thì sắp xếp theo tên cho dễ xem
                $filter['order_by'] = [
                    'slug' => 'ASC',
                    'term_id' => 'DESC',
                ];
            }
            //$filter[ 'get_child' ] = 1;

            //
            $data = $this->term_model->get_all_taxonomy($this->taxonomy, 0, $filter);
            //print_r( $data );
            //$data = $this->term_model->terms_meta_post( $data );
            //print_r( $data );

            //
            $data = $this->term_treeview_data($data);
            //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
        } else {
            $data = [];
            $pagination = '';
        }
        //print_r( $data );

        //
        $this->teamplate_admin['content'] = view(
            'admin/terms/list',
            array(
                'for_action' => $for_action,
                'by_keyword' => $by_keyword,
                'data' => $data,
                'by_is_deleted' => $by_is_deleted,
                'pagination' => $pagination,
                'totalThread' => $totalThread,
                'taxonomy' => $this->taxonomy,
                'name_type' => $this->name_type,
                'controller_slug' => $this->controller_slug,
                'DeletedStatus_DELETED' => DeletedStatus::DELETED,
            )
        );
        return view('admin/admin_teamplate', $this->teamplate_admin);
    }

    private function term_treeview_data($data)
    {
        foreach ($data as $k => $v) {
            $v['get_admin_permalink'] = $this->term_model->get_admin_permalink($v['taxonomy'], $v['term_id'], $this->controller_slug);
            $v['view_url'] = $this->term_model->get_term_permalink($v);
            $v['gach_ngang'] = '';

            // phiên bản dùng angular js -> có sử dụng child_term
            /*
            if ( count( $v[ 'child_term' ] ) > 0 ) {
            $v[ 'child_term' ] = $this->term_treeview_data( $v[ 'child_term' ] );
            }
            */

            //
            $data[$k] = $v;
        }
        return $data;
    }

    public function add($ops = [])
    {
        $id = $this->MY_get('id', 0);

        //
        if (!empty($this->MY_post('data'))) {
            // nếu là nhân bản
            if ($this->MY_post('is_duplicate', 0) * 1 > 0) {
                //print_r( $_POST );

                // select dữ liệu từ 1 bảng bất kỳ
                $dup_data = $this->term_model->select_term($id, [
                    'taxonomy' => $this->taxonomy,
                ]);
                //print_r($dup_data);
                //die(__CLASS__ . ':' . __LINE__);

                // đổi lại tiêu đề để tránh trùng lặp
                if (isset($dup_data['name'])) {
                    $duplicate_title = explode('- Duplicate', $dup_data['name']);
                    $dup_data['name'] = trim($duplicate_title[0]) . ' - Duplicate ' . date('Ymd-His');
                }
                $dup_data['slug'] = '';
                $dup_data['term_shortname'] = '';
                $dup_data['term_id'] = 0;
                unset($dup_data['term_id']);
                //print_r($dup_data);

                // -> bỏ ID đi
                $id = 0;
                //die(__CLASS__ . ':' . __LINE__);

                //
                return $this->add_new($dup_data);
            }

            // update
            if ($id > 0) {
                return $this->update($id);
            }
            // insert
            return $this->add_new();
        }

        //
        $file_view = 'add';
        $next_term = [];
        $prev_term = [];

        // edit
        if ($id > 0) {
            //echo $this->lang_key . PHP_EOL;

            //
            $data = $this->term_model->select_term($id, [
                'taxonomy' => $this->taxonomy,
                //'lang_key' => $this->lang_key,
            ], [
                //'show_query' => 1,
            ]);
            if (empty($data)) {
                die('term not found!' . __CLASS__ . ':' . __LINE__);
            }
            //print_r($data);

            // cập nhật lang default nếu chưa có
            if ($data['lang_key'] == '') {
                $data['lang_key'] = LanguageCost::default_lang();

                //
                $this->base_model->update_multiple('terms', [
                    // SET
                    'lang_key' => $data['lang_key']
                ], [
                    'term_id' => $id
                ], [
                    // hiển thị mã SQL để check
                    //'show_query' => 1,
                    // trả về câu query để sử dụng cho mục đích khác
                    //'get_query' => 1,
                ]);
            }

            // nếu ngôn ngữ của post không đúng với ngôn ngữ đang hiển thị
            $clone_lang = $this->MY_get('clone_lang', '');
            if ($clone_lang != '' && $clone_lang != $data['lang_key']) {
                // ngôn ngữ hiện tại có cha -> chuyển đến bản ghi cha
                if ($data['lang_parent'] > 0) {
                    $this->redirectLanguage($data, $data['lang_parent']);
                }
                // nếu không có cha
                else {
                    // tìm bản ghi con
                    $child_data = $this->term_model->select_term(0, [
                        'taxonomy' => $this->taxonomy,
                        'lang_key' => $clone_lang,
                        'lang_parent' => $data['term_id'],
                    ], [
                        //'show_query' => 1,
                    ]);
                    //print_r($child_data);

                    // nếu không có thì báo lỗi hiển thị
                    if (empty($child_data)) {
                        // nếu bài viết hiện tại đang là ngôn ngữ mặc định
                        //if (isset($_GET['lang_duplicate']) && $data['lang_key'] == LanguageCost::default_lang()) {
                        // nhân bản cho ngôn ngữ phụ
                        //print_r($data);
                        // nhân bản data
                        $dup_data = $data;
                        $dup_data['lang_key'] = $clone_lang;
                        $dup_data['lang_parent'] = $data['term_id'];
                        $dup_data['term_permalink'] = '';
                        $dup_data['updated_permalink'] = 0;
                        // xóa phần ID để tránh xung đột primary key
                        $dup_data['term_id'] = 0;
                        unset($dup_data['term_id']);
                        $dup_data['term_taxonomy_id'] = 0;
                        unset($dup_data['term_taxonomy_id']);
                        $term_meta = $dup_data['term_meta'];
                        unset($dup_data['term_meta']);

                        //
                        //print_r($dup_data);
                        //die(__CLASS__ . ':' . __LINE__);

                        //
                        $result_id = $this->term_model->insert_terms($dup_data, $this->taxonomy, true, $term_meta);

                        //
                        if ($result_id > 0) {
                            $redirect_to = $this->term_model->get_admin_permalink($this->taxonomy, $result_id, $this->controller_slug) . $this->get_preview_url();
                            //die($redirect_to);

                            // sau đó redirect tới
                            $this->MY_redirect($redirect_to, 301);
                            die(__CLASS__ . ':' . __LINE__);
                        }
                        // nếu tồn tại rồi thì báo đã tồn tại
                        else if ($result_id < 0) {
                            die('Không thể nhân bản do đã tồn tại slug trong hệ thống (' . $this->taxonomy . ')');
                        }
                        die('Lỗi nhân bản ' . $this->name_type . ' mới');
                        //die(__CLASS__ . ':' . __LINE__);
                        //}

                        // thay đổi file view sang file thông báo tạo ngôn ngữ mới
                        //$file_view = 'clone_lang';
                        // cố định thư mục chứa view
                        //$this->add_view_path = 'terms';

                        // mặc định hiển thị thông báo lỗi cho việc lấy dữ liệu
                        //die('term not found because data_lang_key(' . $data['lang_key'] . ') != this_lang_key(' . $this->lang_key . ')');
                    } else {
                        $this->redirectLanguage($child_data, $child_data['term_id']);
                    }
                    //die(__CLASS__ . ':' . __LINE__);
                }
            }
            //die(__CLASS__ . ':' . __LINE__);

            // tự động cập nhật lại slug khi nhân bản
            if (
                // url vẫn còn duplicate
                strstr($data['slug'], '-duplicate-') == true &&
                // tiêu đề không còn Duplicate
                strstr($data['name'], ' - Duplicate ') == false
            ) {
                //die( DYNAMIC_BASE_URL . ltrim( $_SERVER[ 'REQUEST_URI' ], '/' ) );
                //echo 'bbbbbbbbbbbbb';
                $this->term_model->update_terms($data['term_id'], [
                    //'name' => $data['name'],
                    'slug' => $this->base_model->_eb_non_mark_seo($data['name'])
                ]);

                // lấy data mới -> sau khi update
                $new_data = $this->term_model->select_term($data['term_id'], [
                    'taxonomy' => $this->taxonomy,
                ]);
                //print_r($new_data);
                // cập nhật lại slug luôn vào ngay
                $this->term_model->the_term_permalink($new_data);

                //
                //$this->MY_redirect(DYNAMIC_BASE_URL . ltrim($_SERVER['REQUEST_URI'], '/'), 301);
                $this->MY_redirect($this->term_model->get_admin_permalink($this->taxonomy, $data['term_id'], $this->controller_slug), 301);
            }

            // lấy các nhóm khác cùng nhóm để xử lý cho tiện -> nhiều khi muốn sửa thì sửa luôn
            $prev_term = $this->term_model->select_term(0, [
                'term_id <' => $data['term_id'],
                'is_deleted' => DeletedStatus::FOR_DEFAULT,
                'taxonomy' => $this->taxonomy,
                'lang_key' => $this->lang_key,
            ], [
                'order_by' => array(
                    'term_order' => 'DESC',
                    'term_id' => 'ASC'
                ),
                //'show_query' => 1,
                'limit' => 5,
            ], 'term_id, name, slug');
            //print_r($prev_term);

            //
            $next_term = $this->term_model->select_term(0, [
                'term_id >' => $data['term_id'],
                'is_deleted' => DeletedStatus::FOR_DEFAULT,
                'taxonomy' => $this->taxonomy,
                'lang_key' => $this->lang_key,
            ], [
                'order_by' => array(
                    'term_order' => 'DESC',
                    'term_id' => 'ASC'
                ),
                //'show_query' => 1,
                'limit' => 5,
            ], 'term_id, name, slug');
            //print_r($next_term);

            //
            $this->term_model->update_count_post_in_term($data);
            $this->term_model->sync_term_child_count();
        }
        // add
        else {
            $data = $this->base_model->default_data(WGR_TERM_VIEW);
            /*
            $data = $this->base_model->default_data( 'terms', [
            'term_taxonomy'
            ] );
            */
            $data['term_meta'] = [];
        }
        //print_r( $data );


        // lấy danh sách các nhóm để tạo cha con
        $set_parent = '';
        if (
            in_array($this->taxonomy, [
                TaxonomyType::POSTS,
                //TaxonomyType::BLOGS,
                //TaxonomyType::OPTIONS,
                TaxonomyType::PROD_CATS,
                TaxonomyType::PROD_OTPS,
            ])
        ) {
            $set_parent = $this->taxonomy;
        }
        // với custom taxonomy -> kiểm tra xem có tham số set cha con không
        else {
            global $arr_custom_taxonomy;
            //print_r( $arr_custom_taxonomy );

            //
            if (isset($arr_custom_taxonomy[$this->taxonomy]) && isset($arr_custom_taxonomy[$this->taxonomy]['set_parent'])) {
                $set_parent = $this->taxonomy;
            }
        }

        //
        $arr_custom_cloumn = [];
        if ($this->taxonomy == TaxonomyType::ADS) {
            $arr_custom_cloumn = $this->base_model->EBE_get_file_in_folder(VIEWS_CUSTOM_PATH . 'ads_node/', '.{html}', 'file', true);
            //print_r($arr_custom_cloumn);
        }

        //
        if ($this->debug_enable === true) {
            echo '<!-- ';
            print_r($data);
            echo ' -->';
        }

        //
        $this->teamplate_admin['content'] = view(
            'admin/' . $this->add_view_path . '/' . $file_view,
            array(
                'arr_custom_cloumn' => $arr_custom_cloumn,
                'lang_key' => $this->lang_key,
                'set_parent' => $set_parent,
                'prev_term' => $prev_term,
                'next_term' => $next_term,
                'data' => $data,
                'term_lang' => ($data['lang_key'] != '' ? LanguageCost::typeList($data['lang_key']) : ''),
                'taxonomy' => $this->taxonomy,
                'name_type' => $this->name_type,
                'meta_detault' => TaxonomyType::meta_default($this->taxonomy),
                'controller_slug' => $this->controller_slug,
                'preview_url' => $this->MY_get('preview_url', ''),
                'preview_offset_top' => $this->MY_get('preview_offset_top', ''),
            )
        );
        return view('admin/admin_teamplate', $this->teamplate_admin);
    }

    protected function add_new($data = NULL)
    {
        if ($data === NULL) {
            $data = $this->MY_post('data');
        }
        $data['taxonomy'] = $this->taxonomy;
        //print_r( $data );
        //die( __CLASS__ . ':' . __LINE__ );

        //
        $result_id = $this->term_model->insert_terms($data, $this->taxonomy);

        //
        if ($result_id > 0) {
            //$this->base_model->alert( '', base_url( 'admin/terms/add' ) . '?id=' . $result_id );
            $this->base_model->alert('', $this->term_model->get_admin_permalink($this->taxonomy, $result_id, $this->controller_slug));
        }
        // nếu tồn tại rồi thì báo đã tồn tại
        else if ($result_id < 0) {
            $this->base_model->alert('Danh mục đã tồn tại trong hệ thống (' . $this->taxonomy . ')', 'error');
        }
        $this->base_model->alert('Lỗi tạo ' . $this->name_type . ' mới', 'error');
    }

    // thêm nhiều term 1 lúc
    public function multi_add()
    {
        //print_r( $_POST );
        $data = $this->MY_post('data');
        //print_r( $data );
        //die( $this->taxonomy );

        //
        if (empty($data['term_id'])) {
            $this->base_model->alert('Không xác định được ID nhóm cha!', 'error');
        }

        //
        $term_name = explode(PHP_EOL, $data['term_name']);
        foreach ($term_name as $v) {
            $v = trim($v);
            if (empty($v)) {
                continue;
            }

            // -> thêm multi thì không cho phép trùng lặp
            $result_id = $this->term_model->insert_terms([
                'name' => $v,
                'slug' => $v,
                'parent' => $data['term_id'],
            ], $this->taxonomy, true);
        }

        //
        die('<script>top.done_multi_add_term();</script>');
        //die(__CLASS__ . ':' . __LINE__);
    }

    protected function update($id)
    {
        $data = $this->MY_post('data');
        //print_r($data);
        //die( __LINE__ );

        //
        $this->term_model->update_terms($id, $data, $this->taxonomy, [
            'alert' => 1,
        ]);

        //
        $data['term_id'] = $id;
        $data['child_last_count'] = 0;
        $this->term_model->update_count_post_in_term($data);
        $this->term_model->sync_term_child_count();

        // dọn dẹp cache liên quan đến post này -> reset cache
        $this->cleanup_cache($this->term_model->key_cache($id));
        //echo $this->taxonomy . '<br>' . PHP_EOL;
        // xóa cache cho riêng phần ads
        if ($this->taxonomy == TaxonomyType::ADS) {
            // dọn dẹp theo slug truyền vào
            if (isset($data['slug']) && $data['slug'] != '') {
                $this->cleanup_cache($data['slug']);
            }

            // slug tự động theo tên danh mục
            $data['slug'] = $data['name'];
            $data['slug'] = $this->base_model->_eb_non_mark_seo($data['slug']);
            $data['slug'] = str_replace('.', '-', $data['slug']);

            //
            //print_r($data);
            $this->cleanup_cache($data['slug']);
        }

        // nạp lại trang nếu có đổi slug duplicate
        if (
            // url vẫn còn duplicate
            isset($data['slug']) && strstr($data['slug'], '-duplicate-') == true &&
            // tiêu đề không còn Duplicate
            isset($data['name']) && strstr($data['name'], ' - Duplicate ') == false
        ) {
            // nạp lại trang
            //$this->base_model->alert('', DYNAMIC_BASE_URL . ltrim($_SERVER['REQUEST_URI'], '/'));
            $this->base_model->alert('', $this->term_model->get_admin_permalink($this->taxonomy, $id, $this->controller_slug));
        } else {
            // so sánh url cũ và mới
            $old_slug = $this->MY_post('old_slug');
            //print_r($old_slug);

            // nếu có sự khác nhau
            if ($old_slug != $data['slug']) {
                // lấy data mới -> sau khi update
                $new_data = $this->term_model->select_term($id, [
                    'taxonomy' => $this->taxonomy,
                ]);
                //print_r($new_data);

                // -> lấy url mới -> thiết lập lại url ở fronend
                echo '<script>top.set_new_term_url("' . $this->term_model->update_term_permalink($new_data) . '", "' . $new_data['slug'] . '");</script>';
            }
        }

        //
        echo '<script>top.after_update_term();</script>';

        //
        $this->base_model->alert('Cập nhật ' . $this->name_type . ' thành công');
    }

    // chuyển trang sau khi XÓA xong
    protected function after_delete_restore()
    {
        $for_redirect = base_url('admin/' . $this->controller_slug);
        $urlParams = [];

        //
        $is_deleted = $this->MY_get('is_deleted');
        if ($is_deleted != '') {
            $urlParams[] = 'is_deleted=' . $is_deleted;
        }

        //
        if (count($urlParams) > 0) {
            $for_redirect .= '?' . implode('&', $urlParams);
        }
        $this->base_model->alert('', $for_redirect);
    }
    protected function done_delete_restore($id)
    {
        die('<script>top.done_delete_restore(' . $id . ', "' . base_url('admin/' . $this->controller_slug) . '");</script>');
    }
    protected function before_delete_restore($is_deleted)
    {
        $id = $this->MY_get('id', 0);

        $update = $this->term_model->update_terms($id, [
            'is_deleted' => $is_deleted,
        ]);

        // nếu update thành công -> gửi lệnh javascript để ẩn bài viết bằng javascript
        if ($update === true) {
            if ($is_deleted == DeletedStatus::REMOVED && ALLOW_USING_MYSQL_DELETE === true) {
                return $update;
            }
            return $this->done_delete_restore($id);
        }
        // không thì nạp lại cả trang để kiểm tra cho chắc chắn
        $this->after_delete_restore();
    }

    public function delete()
    {
        return $this->before_delete_restore(DeletedStatus::DELETED);
    }

    public function restore()
    {
        return $this->before_delete_restore(DeletedStatus::FOR_DEFAULT);
    }
    public function remove($confirm_delete = false)
    {
        // nếu có thuộc tính cho phép xóa hoàn toàn dữ liệu thì tiến hành xóa
        if (ALLOW_USING_MYSQL_DELETE === true) {
            $this->delete_remove($this->MY_get('id', 0));
            return $this->done_delete_restore($this->MY_get('id', 0));
        }
        // mặc định thì chỉ là chuyển về trang thái remove để ẩn khỏi admin
        else {
            $result = $this->before_delete_restore(DeletedStatus::REMOVED);
        }

        //
        return $result;
    }

    //
    public function before_all_delete_restore($is_deleted)
    {
        $ids = $this->get_ids();

        //
        $update = $this->base_model->update_multiple('terms', [
            // SET
            'is_deleted' => $is_deleted
        ], [
            'is_deleted !=' => $is_deleted
        ], [
            'where_in' => array(
                'term_id' => $ids
            ),
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
        ]);

        // riêng với lệnh remove -> kiểm tra nếu remove hoàn toàn thì xử lý riêng
        if ($update === true && $is_deleted == DeletedStatus::REMOVED && ALLOW_USING_MYSQL_DELETE === true) {
            return $update;
        }

        //
        $this->result_json_type([
            'code' => __LINE__,
            'result' => $update,
        ]);
    }

    // chức năng xóa nhiều bản ghi 1 lúc
    public function delete_all()
    {
        return $this->before_all_delete_restore(DeletedStatus::DELETED);
    }

    // chức năng restore nhiều bản ghi 1 lúc
    public function restore_all()
    {
        return $this->before_all_delete_restore(DeletedStatus::FOR_DEFAULT);
    }

    // chức năng remove nhiều bản ghi 1 lúc
    public function remove_all()
    {
        // nếu có thuộc tính cho phép xóa hoàn toàn dữ liệu thì tiến hành xóa
        if (ALLOW_USING_MYSQL_DELETE === true) {
            $result = $this->delete_remove();
        } else {
            $result = $this->before_all_delete_restore(DeletedStatus::REMOVED);
        }

        //
        $this->result_json_type([
            'code' => __LINE__,
            'result' => $result,
            //'ids' => $ids,
        ]);
    }

    public function term_status()
    {
        $id = $this->MY_get('id', '');
        if (empty($id)) {
            die('id empty');
        }

        //
        $data = $this->base_model->select(
            'term_status',
            'terms',
            array(
                'term_id' => $id,
            ),
            array(
                // hiển thị mã SQL để check
                //'show_query' => 1,
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
        //print_r($data);
        if (empty($data)) {
            $this->base_model->alert('Không xác định được danh mục cần cập nhật!', 'warning');
        }
        if ($data['term_status'] == TaxonomyType::VISIBLE) {
            $data['term_status'] = TaxonomyType::HIDDEN;
        } else {
            $data['term_status'] = TaxonomyType::VISIBLE;
        }
        //print_r($data);

        //
        $this->base_model->update_multiple(
            'terms',
            [
                'term_status' => $data['term_status'],
            ],
            [
                'term_id' => $id,
            ],
            [
                'debug_backtrace' => debug_backtrace()[1]['function']
            ]
        );
        //$this->base_model->alert('Không xác định được danh mục cần cập nhật!', 'warning');
        die('<script>top.record_status_color(' . $id . ',' . $data['term_status'] . ');</script>');
    }

    //
    protected function get_ids()
    {
        $ids = $this->MY_post('ids', '');
        if (empty($ids)) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => 'ids not found!',
            ]);
        }

        //
        $ids = explode(',', $ids);
        if (count($ids) <= 0) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => 'ids EMPTY!',
            ]);
        }
        //print_r( $ids );

        //
        return $ids;
    }

    // xóa hoàn toàn dữ liệu
    protected function delete_remove($id = 0)
    {
        if ($id > 0) {
            $ids = [$id];
        } else {
            $ids = $this->get_ids();
        }
        //die( __CLASS__ . ':' . __LINE__ );

        // XÓA term taxonomy
        $result = $this->base_model->delete_multiple($this->term_model->taxTable, [
            // WHERE
            //'t2.is_deleted' => DeletedStatus::REMOVED,
        ], [
            /*
                'join' => array(
                $this->term_model->table . ' AS t2' => $this->term_model->taxTable . '.term_id = t2.term_id'
                ),
                */
            'where_in' => array(
                'term_id' => $ids
            ),
        ]);

        // XÓA relationships
        $this->base_model->delete_multiple($this->term_model->relaTable, [
            // WHERE
            //'t2.is_deleted' => DeletedStatus::REMOVED,
        ], [
            /*
                'join' => array(
                $this->term_model->table . ' AS t2' => $this->term_model->relaTable . '.term_taxonomy_id = t2.term_id'
                ),
                */
            'where_in' => array(
                'term_taxonomy_id' => $ids
            ),
        ]);

        // XÓA meta
        $this->base_model->delete_multiple($this->term_model->metaTable, [
            // WHERE
            //'t2.is_deleted' => DeletedStatus::REMOVED,
        ], [
            /*
                'join' => array(
                $this->term_model->table . ' AS t2' => $this->term_model->metaTable . '.term_id = t2.term_id'
                ),
                */
            'where_in' => array(
                'term_id' => $ids
            ),
        ]);

        // XÓA dữ liệu chính
        $this->base_model->delete_multiple($this->term_model->table, [
            // WHERE
            //'is_deleted' => DeletedStatus::REMOVED,
        ], [
            'where_in' => array(
                'term_id' => $ids
            ),
        ]);

        //
        return $result;
    }

    // chuyển đến bản ghi dựa theo ngôn ngữ đang xem
    protected function redirectLanguage($data, $id)
    {
        // xác định url cha
        $redirect_to = $this->term_model->get_admin_permalink($data['taxonomy'], $id, $this->controller_slug) . $this->get_preview_url();
        //die($redirect_to);

        // sau đó redirect tới
        $this->MY_redirect($redirect_to, 301);
        die(__CLASS__ . ':' . __LINE__);
    }
}
