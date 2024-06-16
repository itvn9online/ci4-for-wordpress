<?php

namespace App\Controllers\Sadmin;

// Libraries
use App\Libraries\TaxonomyType;
use App\Libraries\LanguageCost;
use App\Libraries\DeletedStatus;

//
class Terms extends Sadmin
{
    protected $taxonomy = '';
    protected $name_type = '';
    //private $default_taxonomy = '';

    // tham số dùng để thay đổi URL cho controller nếu muốn
    protected $controller_slug = 'terms';
    // tham số dùng để đổi file view khi add hoặc edit bài viết nếu muốn
    protected $add_view_path = 'terms';
    // tham số dùng để đổi file view khi xem danh sách bài viết nếu muốn
    protected $list_view_path = 'terms';
    protected $list_table_path = '';

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
                // thử xem có phải custom taxonomy không
                if (isset(ARR_CUSTOM_TAXONOMY[$this->taxonomy])) {
                    // xem có slug không
                    if (
                        isset(ARR_CUSTOM_TAXONOMY[$this->taxonomy]['slug']) &&
                        // nếu có
                        ARR_CUSTOM_TAXONOMY[$this->taxonomy]['slug'] != '' &&
                        // mà khác nhau
                        ARR_CUSTOM_TAXONOMY[$this->taxonomy]['slug'] != $this->controller_slug
                    ) {

                        // tạo link redirect
                        $redirect_to = str_replace('/sadmin/' . $this->controller_slug, '/sadmin/' . ARR_CUSTOM_TAXONOMY[$this->taxonomy]['slug'], $_SERVER['REQUEST_URI']);
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
        $urlPartPage = 'sadmin/' . $this->controller_slug . '?part_type=' . $this->taxonomy;

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
            // 'show_query' => 1,
            'limit' => -1,
        ];
        //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
        // print_r($filter);


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
            $pagination = $this->base_model->EBE_pagination($page_num, $totalPage, $urlPartPage, 'page_num=');

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
            //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
            //print_r($filter);

            //
            $data = $this->term_model->get_all_taxonomy($this->taxonomy, 0, $filter);
            // print_r($data);
            if (!isset($filter['get_meta'])) {
                $data = $this->term_model->terms_meta_post($data);
                // print_r($data);
            }

            //
            $data = $this->term_treeview_data($data);
            //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;
        } else {
            $data = [];
            $pagination = '';
        }
        // print_r($data);

        //
        $this->teamplate_admin['content'] = view(
            'vadmin/' . $this->list_view_path . '/list',
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
                //'list_view_path' => $this->list_view_path,
                'list_table_path' => $this->list_table_path,
            )
        );
        return view('vadmin/admin_teamplate', $this->teamplate_admin);
    }

    protected function term_treeview_data($data)
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
                $dup_data['term_date'] = date(EBE_DATETIME_FORMAT);
                $dup_data['last_updated'] = $dup_data['term_date'];
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
        $child_term = [];

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
            // print_r($data);

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
                    'show_query' => 1,
                    // trả về câu query để sử dụng cho mục đích khác
                    //'get_query' => 1,
                ]);
            }

            // nếu có thông số lang_clone_done -> lang vừa được nhân bản -> cập nhật lại lang parent nếu chưa đúng
            $lang_clone_done = $this->MY_get('lang_clone_done');
            if ($lang_clone_done != '' && is_numeric($lang_clone_done) && $lang_clone_done != $data['term_id'] && $data['lang_parent'] < 1) {
                $lang_clone_key = $this->MY_get('lang_clone_key');
                // print_r($data);

                //
                $parent_lang_data = $this->term_model->select_term($lang_clone_done, [
                    'taxonomy' => $this->taxonomy,
                    // 'lang_key' => $clone_lang,
                ], [
                    'show_query' => 1,
                ]);
                // print_r($parent_lang_data);

                // nếu đây là lang chính và khác với lang hiện tại
                if (!empty($parent_lang_data)) {
                    // nếu đây không phải ngôn ngữ chính -> tìm ngôn ngữ chính
                    if ($parent_lang_data['lang_key'] != SITE_LANGUAGE_DEFAULT) {
                        $parent_lang_data = $this->term_model->select_term(0, [
                            'taxonomy' => $this->taxonomy,
                            'lang_key' => SITE_LANGUAGE_DEFAULT,
                            'slug' => $parent_lang_data['slug'],
                            'is_deleted' => DeletedStatus::FOR_DEFAULT,
                        ], [
                            'show_query' => 1,
                        ]);
                        // print_r($parent_lang_data);
                        // die(__CLASS__ . ':' . __LINE__);
                    }

                    //
                    if (!empty($parent_lang_data) && $parent_lang_data['lang_key'] == SITE_LANGUAGE_DEFAULT && $parent_lang_data['lang_key'] != $lang_clone_key && $parent_lang_data['lang_key'] != $data['lang_key']) {
                        // print_r($parent_lang_data);

                        // cập nhật lang parent
                        $this->base_model->update_multiple('terms', [
                            // SET
                            'lang_parent' => $parent_lang_data['term_id']
                        ], [
                            'term_id' => $id
                        ], [
                            // hiển thị mã SQL để check
                            'show_query' => 1,
                            // trả về câu query để sử dụng cho mục đích khác
                            //'get_query' => 1,
                        ]);
                    }
                }
            }

            // nếu ngôn ngữ của post không đúng với ngôn ngữ đang hiển thị
            $clone_lang = $this->MY_get('clone_lang');
            if ($clone_lang != '' && $clone_lang != $data['lang_key'] && $clone_lang != SITE_LANGUAGE_DEFAULT) {
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
                        $dup_data['term_date'] = date(EBE_DATETIME_FORMAT);
                        $dup_data['last_updated'] = $dup_data['term_date'];

                        //
                        //print_r($dup_data);
                        //die(__CLASS__ . ':' . __LINE__);

                        //
                        $result_id = $this->term_model->insert_terms($dup_data, $this->taxonomy, true, $term_meta);

                        //
                        if ($result_id > 0) {
                            // dọn dẹp cache liên quan đến taxonomy này
                            $this->cleanup_cache($this->taxonomy . '_get_child');

                            // chuẩn bị link để redirect tới
                            $redirect_to = $this->term_model->get_admin_permalink($this->taxonomy, $result_id, $this->controller_slug);
                            // thêm tham số cập nhật lang parent
                            $redirect_to .= '&lang_clone_done=' . $data['term_id'] . '&lang_clone_key=' . $clone_lang;
                            //
                            $redirect_to .= $this->get_preview_url();
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
                strpos($data['slug'], '-duplicate-') !== false &&
                // tiêu đề không còn Duplicate
                strpos($data['name'], ' - Duplicate ') === false
            ) {
                //die( DYNAMIC_BASE_URL . ltrim( $_SERVER[ 'REQUEST_URI' ], '/' ) );
                //echo 'bbbbbbbbbbbbb';
                $this->term_model->update_terms($data['term_id'], [
                    //'name' => $data['name'],
                    'slug' => $this->base_model->_eb_non_mark_seo($data['name'])
                ]);

                // dọn dẹp cache liên quan đến taxonomy này
                $this->cleanup_cache($this->taxonomy . '_get_child');

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
            $child_term = $this->term_model->select_term(0, [
                // 'term_id >' => $data['term_id'],
                // 'is_deleted' => DeletedStatus::FOR_DEFAULT,
                'taxonomy' => $this->taxonomy,
                // 'lang_key' => $this->lang_key,
            ], [
                'where_or' => [
                    'parent' => $data['term_id'],
                    'lang_parent' => $data['term_id'],
                ],
                // 'order_by' => array(
                //     'term_order' => 'DESC',
                //     'term_id' => 'ASC'
                // ),
                // 'show_query' => 1,
                'limit' => 99,
            ], 'term_id, name, slug, is_deleted, taxonomy, lang_key');
            // print_r($child_term);

            //
            $this->term_model->update_count_post_in_term($data);
            $this->term_model->sync_term_child_count();

            // 
            $term_permalink = $this->term_model->before_term_permalink($data);
            // echo $term_permalink . '<br>' . PHP_EOL;
            if ($term_permalink !== null && $data['term_permalink'] != $term_permalink) {
                $this->MY_redirect($this->term_model->get_admin_permalink($this->taxonomy, $data['term_id'], $this->controller_slug), 301);
                // die(__CLASS__ . ':' . __LINE__);
            }
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
        // print_r($data);


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
        else if (isset(ARR_CUSTOM_TAXONOMY[$this->taxonomy]) && isset(ARR_CUSTOM_TAXONOMY[$this->taxonomy]['set_parent'])) {
            $set_parent = $this->taxonomy;
        }

        //
        $arr_custom_row = [];
        if ($this->taxonomy == TaxonomyType::ADS) {
            $arr_custom_row = $this->base_model->EBE_get_file_in_folder(VIEWS_CUSTOM_PATH . 'ads_row/', '.{html}', 'file', true);
            //print_r($arr_custom_row);
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
            'vadmin/' . $this->add_view_path . '/' . $file_view,
            array(
                'arr_custom_row' => $arr_custom_row,
                'arr_custom_cloumn' => $arr_custom_cloumn,
                'lang_key' => $this->lang_key,
                'set_parent' => $set_parent,
                'prev_term' => $prev_term,
                'next_term' => $next_term,
                'child_term' => $child_term,
                'data' => $data,
                'term_lang' => ($data['lang_key'] != '' ? LanguageCost::typeList($data['lang_key']) : ''),
                'taxonomy' => $this->taxonomy,
                'name_type' => $this->name_type,
                // 'meta_default' => TaxonomyType::meta_default($this->taxonomy),
                'meta_default' => $this->term_model->taxonomy_meta_default($this->taxonomy),
                'controller_slug' => $this->controller_slug,
                'preview_url' => $this->MY_get('preview_url'),
                'preview_offset_top' => $this->MY_get('preview_offset_top'),
            )
        );
        return view('vadmin/admin_teamplate', $this->teamplate_admin);
    }

    protected function add_new($data = null)
    {
        if ($data === null) {
            $data = $this->MY_post('data');
        }
        $data['taxonomy'] = $this->taxonomy;
        //print_r( $data );
        //die( __CLASS__ . ':' . __LINE__ );

        //
        $result_id = $this->term_model->insert_terms($data, $this->taxonomy);

        //
        if ($result_id > 0) {
            // dọn dẹp cache liên quan đến taxonomy này
            $this->cleanup_cache($this->taxonomy . '_get_child');

            //$this->base_model->alert( '', base_url( 'sadmin/terms/add' ) . '?id=' . $result_id );
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
            $this->base_model->alert('Cannot be determined parent ID!', 'error');
        }

        //
        $term_name = explode(PHP_EOL, $data['term_name']);
        foreach ($term_name as $v) {
            $v = trim($v);
            if (empty($v)) {
                continue;
            }

            //
            $slug = $this->base_model->_eb_non_mark_seo($v);
            $slug = str_replace('.', '-', $slug);
            $slug = str_replace('--', '-', $slug);

            // -> thêm multi thì không cho phép trùng lặp -> kiểm tra trùng lặp theo parent
            $check_slug_exist = [
                $slug,
                rtrim($slug . '-' . $data['slug'], '-'),
                ltrim($data['slug'] . '-' . $slug, '-'),
                $slug . '-' . $data['term_id'],
                rtrim($slug . '-' . $data['slug'] . '-' . $data['term_id'], '-'),
                ltrim($data['slug'] . '-' . $slug . '-' . $data['term_id'], '-'),
            ];

            //
            $check_term_exist = $this->term_model->get_term_by_slug(
                // $slug,
                '',
                $this->taxonomy,
                // get_meta
                false,
                // limit
                1,
                // select_col
                'term_id, slug, parent',
                // lang_key
                '',
                // ops
                [
                    'where' => [
                        // 'parent' => $data['term_id'],
                    ],
                    'filter' => [
                        'where_in' => array(
                            'slug' => $check_slug_exist
                        ),
                        // 'show_query' => 1
                    ]
                ]
            );
            if (!empty($check_term_exist)) {
                print_r($check_term_exist);
                // continue;

                // nếu cùng 1 cha mà trùng -> bỏ qua luôn
                if ($check_term_exist['parent'] == $data['term_id']) {
                    echo 'Term EXIST #' . $check_term_exist['term_id'] . ' | parent #' . $check_term_exist['parent'] . '<br>' . PHP_EOL;
                    continue;
                }

                // chạy vòng lặp lấy cái ko trùng lặp
                $new_slug = false;
                foreach ($check_slug_exist as $fixed_slug) {
                    // echo $fixed_slug . '<br>' . PHP_EOL;
                    if ($fixed_slug != $check_term_exist['slug']) {
                        $slug = $fixed_slug;
                        echo $slug . '<br>' . PHP_EOL;
                        $new_slug = true;
                        break;
                    }
                }

                //
                if ($new_slug === false) {
                    echo 'Term EXIST #' . $check_term_exist['term_id'] . '<br>' . PHP_EOL;
                    continue;
                }
            }
            // continue;

            //
            $data_insert = [
                'name' => $v,
                'slug' => $slug,
                'parent' => $data['term_id'],
            ];
            print_r($data_insert);
            echo '<br>' . PHP_EOL;

            //
            $result_id = $this->term_model->insert_terms($data_insert, $this->taxonomy, false, [], false);

            //
            if ($result_id > 0) {
                echo 'Insert OK #' . $result_id . '<br>' . PHP_EOL;
            } else {
                print_r($result_id);
                echo '<br>' . PHP_EOL;
                echo 'Insert ERROR!' . '<br>' . PHP_EOL;
            }
        }
        // die(__CLASS__ . ':' . __LINE__);

        // dọn dẹp cache liên quan đến taxonomy này
        $this->cleanup_cache($this->taxonomy . '_get_child');

        //
        die('<script>top.done_multi_add_term();</script>');

        // die(__CLASS__ . ':' . __LINE__);
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
            if ($data['slug'] != '') {
                $this->cleanup_cache($data['slug']);
            }
        }

        // nạp lại trang nếu có đổi slug duplicate
        if (
            // url vẫn còn duplicate
            isset($data['slug']) && strpos($data['slug'], '-duplicate-') !== false &&
            // tiêu đề không còn Duplicate
            isset($data['name']) && strpos($data['name'], ' - Duplicate ') === false
        ) {
            // lấy data mới -> sau khi update
            $new_data = $this->term_model->select_term($id, [
                'taxonomy' => $this->taxonomy,
            ]);
            // print_r($data);
            // print_r($new_data);

            // -> lấy url mới -> thiết lập lại url ở fronend
            echo $this->term_model->update_term_permalink($new_data) . '<br>' . PHP_EOL;
            // die(__CLASS__ . ':' . __LINE__);

            // nạp lại trang
            //$this->base_model->alert('', DYNAMIC_BASE_URL . ltrim($_SERVER['REQUEST_URI'], '/'));
            $this->base_model->alert('', $this->term_model->get_admin_permalink($this->taxonomy, $id, $this->controller_slug));
        } else {
            // so sánh url cũ và mới
            $old_slug = $this->MY_post('old_slug');
            // print_r($old_slug);
            // print_r($data);

            // nếu có sự khác nhau
            if (!empty($old_slug) && $old_slug != $data['slug']) {
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

        // dọn dẹp cache liên quan đến taxonomy này
        $this->cleanup_cache($this->taxonomy . '_get_child');

        //
        $this->base_model->alert('Cập nhật ' . $this->name_type . ' thành công');
    }

    // chuyển trang sau khi XÓA xong
    protected function after_delete_restore()
    {
        die('<script>top.after_delete_restore();</script>');
    }
    protected function done_delete_restore($id)
    {
        die('<script>top.done_delete_restore(' . $id . ');</script>');
    }
    protected function before_delete_restore($is_deleted)
    {
        $id = $this->MY_get('id', 0);

        //
        $data = [
            'is_deleted' => $is_deleted,
        ];
        //print_r( $data );
        //die( __CLASS__ . ':' . __LINE__ );

        // lấy slug nếu chưa có -> lấy để lệnh update post còn thêm hoặc xóa trash (tùy request hiện tại)
        if (!isset($data['slug']) || $data['slug'] == '') {
            $check_slug = $this->base_model->select('slug', 'terms', [
                'term_id' => $id,
                // 'post_status !=' => $post_status,
            ], [
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 2,
                'limit' => 1
            ]);
            //print_r( $check_slug );
            if (!empty($check_slug)) {
                $data['slug'] = $check_slug['slug'];
            }
        }
        // print_r($data);
        // die(__CLASS__ . ':' . __LINE__);

        $update = $this->term_model->update_terms($id, $data);

        // dọn dẹp cache liên quan đến taxonomy này
        $this->cleanup_cache($this->taxonomy . '_get_child');

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
        $current_data = $this->base_model->select(
            'term_id, slug',
            'terms',
            [],
            array(
                'where_in' => array(
                    'term_id' => $ids
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
                'limit' => -1
            )
        );

        // chạy vòng lặp -> thực hiện khóa từng post
        foreach ($current_data as $v) {
            //print_r($v);
            $id = $v['term_id'];
            unset($v['term_id']);

            //
            $v['is_deleted'] = $is_deleted;
            // print_r($v);
            // continue;

            //
            $update = $this->term_model->update_terms(
                $id,
                $v
            );
        }

        //
        // print_r($current_data);
        // die(__CLASS__ . ':' . __LINE__);

        //
        if (1 > 2) {
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
        }

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
        $id = $this->MY_get('id');
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
            $this->base_model->alert('Cannot be determined category need update!', 'warning');
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
        $ids = $this->MY_post('ids');
        if (empty($ids)) {
            $this->result_json_type([
                'code' => __LINE__,
                'error' => 'ids not found!',
            ]);
        }

        //
        $ids = explode(',', $ids);
        if (count($ids) < 1) {
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
