<?php

namespace App\Controllers\Admin;

// Libraries
use App\Libraries\PostType;
use App\Libraries\TaxonomyType;
use App\Libraries\LanguageCost;
use App\Libraries\DeletedStatus;

//
class Posts extends Admin
{
    protected $post_type = '';
    protected $name_type = '';
    //private $detault_type = '';
    protected $post_arr_status = [];

    // các taxonomy được hỗ trợ -> cái nào trống nghĩa là không hỗ trợ theo post_type tương ứng
    protected $taxonomy = TaxonomyType::POSTS;
    protected $tags = TaxonomyType::TAGS;
    //protected $options = TaxonomyType::OPTIONS;
    protected $options = '';

    // tham số dùng để thay đổi bảng cần gọi dữ liệu
    public $table = 'posts';
    public $metaTable = 'postmeta';
    // tham số dùng để thay đổi URL cho controller nếu muốn
    protected $controller_slug = 'posts';
    // tham số dùng để đổi file view khi add hoặc edit bài viết nếu muốn
    protected $add_view_path = 'posts';
    // tham số dùng để đổi file view khi xem danh sách bài viết nếu muốn
    protected $list_view_path = 'posts';
    protected $list_table_path = '';
    // dùng để chọn xem hiển thị nhóm sản phẩm nào ra ở phần danh mục
    protected $main_category_key = 'post_category';

    /*
     * khi update hoặc insert sẽ kiểm tra xem các dữ liệu trong này có không, nếu có không sẽ gán mặc định
     * vì các checkbox khi bỏ chọn tất cả sẽ không xuất hiện trong post -> không được update
     */
    protected $default_post_data = [];
    // các cột được liệt kê trong này sẽ được chuyển đổi từ datetime sang timestamp -> do plugin tạo thời gian nó lấy theo múi giờ hiện tại của người dùng -> lên server phải convert về múi giờ của server
    protected $timestamp_post_data = [];

    /*
     * for_extends: khi một controller extends lại class này và sử dụng các post type khác (custom post type) thì khai báo nó bằng true để bỏ qua các điều kiện kiểm tra
     */
    public function __construct($for_extends = false)
    {
        parent::__construct();

        // kiểm tra quyền truy cập của tài khoản hiện tại
        $this->check_permision(__CLASS__);

        // hỗ trợ lấy theo params truyền vào từ url
        if ($this->post_type == '') {
            $this->post_type = $this->MY_get('post_type', PostType::POST);
        }

        // chỉ kiểm tra các điều kiện này nếu không được chỉ định là extends
        if ($for_extends === false) {
            // lọc bài viết dựa theo post type
            //$this->detault_type = PostType::POST;
            $this->name_type = PostType::typeList($this->post_type);

            // báo lỗi nếu không xác định được post_type
            //if ( $this->post_type == '' || $this->name_type == '' ) {
            if ($this->name_type == '') {
                die('Post type not register in system: ' . $this->post_type);
            }
        }

        //
        $this->post_arr_status = PostType::arrStatus();
        //print_r( $this->post_arr_status );

        // chỉnh lại bảng select của model
        $this->post_model->table = $this->table;
        $this->post_model->metaTable = $this->metaTable;
    }

    public function index()
    {
        return $this->lists();
    }
    public function lists($ops = [])
    {
        if (!empty($this->MY_get('auto_update_module', 0))) {
            return $this->action_update_module();
        }

        //
        $post_per_page = 20;
        // URL cho các action dùng chung
        $for_action = '';
        // URL cho phân trang
        $urlPartPage = 'admin/' . $this->controller_slug . '?part_type=' . $this->post_type;

        //
        $by_keyword = $this->MY_get('s');
        $post_status = $this->MY_get('post_status');
        $by_term_id = $this->MY_get('term_id', 0);
        $by_user_id = $this->MY_get('user_id', 0);

        // các kiểu điều kiện where
        if (!isset($ops['where'])) {
            $where = [];
        }
        //$where[ $this->table . '.post_status !=' ] = PostType::DELETED;
        $where[$this->table . '.post_type'] = $this->post_type;
        $where[$this->table . '.lang_key'] = $this->lang_key;
        if ($by_user_id > 0) {
            $where[$this->table . '.post_author'] = $by_user_id;
            $urlPartPage .= '&user_id=' . $by_user_id;
            $for_action .= '&user_id=' . $by_user_id;
        }

        // tìm kiếm theo từ khóa nhập vào
        $where_or_like = [];
        $where_in = [];
        if ($by_keyword != '') {
            $urlPartPage .= '&s=' . $by_keyword;
            $for_action .= '&s=' . $by_keyword;

            // nếu là email -> tìm theo email thành viên
            if (strpos($by_keyword, '@') !== false) {
                $user_data = $this->base_model->select(
                    'ID',
                    'users',
                    array(
                        'is_deleted' => DeletedStatus::FOR_DEFAULT,
                    ),
                    array(
                        'like' => array(
                            'user_email' => $by_keyword,
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
                        'limit' => 100
                    )
                );
                //print_r($user_data);
                if (!empty($user_data)) {
                    $in_users_id = [];
                    foreach ($user_data as $v) {
                        $in_users_id[] = $v['ID'];
                    }
                    $where_in[$this->table . '.post_author'] = $in_users_id;
                } else {
                    // không có thì đặt điều kiện tìm kiếm là -1 -> không có user ID nào là -1 -> không có kết quả tìm kiếm
                    $where[$this->table . '.post_author'] = '-1';
                }
            }
            // còn lại sẽ tìm theo thông tin đơn hàng
            else {
                $by_like = $this->base_model->_eb_non_mark_seo($by_keyword);
                // tối thiểu từ 1 ký tự trở lên mới kích hoạt tìm kiếm
                if (strlen($by_like) > 0) {
                    //var_dump( strlen( $by_like ) );
                    // nếu là số -> chỉ tìm theo ID
                    if (is_numeric($by_like) === true) {
                        $where_or_like = [
                            'ID' => $by_like * 1,
                            'post_author' => $by_like,
                            'post_parent' => $by_like,
                        ];
                    } else {
                        $where_or_like = [
                            'post_name' => $by_like,
                            'post_title' => $by_keyword,
                        ];
                    }
                }
            }
        }

        //
        if ($post_status == '') {
            $by_post_status = [
                PostType::DRAFT,
                PostType::PUBLICITY,
                PostType::PENDING,
                PostType::PRIVATELY,
            ];
        } else {
            $urlPartPage .= '&post_status=' . $post_status;
            $for_action .= '&post_status=' . $post_status;

            $by_post_status = [
                $post_status,
            ];
        }
        $where_in[$this->table . '.post_status'] = $by_post_status;

        // tổng kết filter
        $filter = [
            'where_in' => $where_in,
            'or_like' => $where_or_like,
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 0,
            'limit' => -1
        ];

        // nếu có lọc theo term_id -> thêm câu lệnh để lọc
        if ($by_term_id > 0) {
            // lấy các nhóm con của nhóm này
            $ids = $this->base_model->select(
                'GROUP_CONCAT(DISTINCT term_id SEPARATOR \',\') AS ids',
                'term_taxonomy',
                array(
                    // các kiểu điều kiện where
                    'parent' => $by_term_id,
                ),
                array(
                    // trả về COUNT(column_name) AS column_name
                    //'selectCount' => 'ID',
                    // hiển thị mã SQL để check
                    //'show_query' => 1,
                    // trả về câu query để sử dụng cho mục đích khác
                    //'get_query' => 1,
                    // trả về tổng số bản ghi -> tương tự mysql num row
                    //'getNumRows' => 1,
                    //'offset' => 0,
                    'limit' => -1
                )
            );
            //print_r( $ids );
            $ids = $ids[0]['ids'];

            //
            if ($ids != '') {
                $ids = str_replace(' ', '', $ids);
                $ids = explode(',', $ids);
                $ids[] = $by_term_id;
                //print_r( $ids );

                //
                $filter['where_in']['term_taxonomy.term_id'] = $ids;
            } else {
                $where['term_taxonomy.term_id'] = $by_term_id;
            }

            $urlPartPage .= '&term_id=' . $by_term_id;
            $for_action .= '&term_id=' . $by_term_id;

            $filter['join'] = [
                'term_relationships' => 'term_relationships.object_id = ' . $this->table . '.ID',
                'term_taxonomy' => 'term_relationships.term_taxonomy_id = term_taxonomy.term_taxonomy_id',
            ];
        }
        //print_r($where);
        //print_r($filter);


        //
        if (isset($ops['add_filter'])) {
            foreach ($ops['add_filter'] as $k => $v) {
                $filter[$k] = $v;
            }
        }


        /*
         * phân trang
         */
        $totalThread = $this->base_model->select('COUNT(ID) AS c', $this->table, $where, $filter);
        //print_r( $totalThread );
        $totalThread = $totalThread[0]['c'];
        //print_r( $totalThread );

        //
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
            $for_action .= ($page_num > 1 ? '&page_num=' . $page_num : '');
            //echo $totalThread . '<br>' . PHP_EOL;
            //echo $totalPage . '<br>' . PHP_EOL;
            $offset = ($page_num - 1) * $post_per_page;

            // chạy vòng lặp gán nốt các thông số khác trên url vào phân trang
            $urlPartPage = $this->base_model->auto_add_params($urlPartPage);

            //
            $pagination = $this->base_model->EBE_pagination($page_num, $totalPage, $urlPartPage, '&page_num=');


            // select dữ liệu từ 1 bảng bất kỳ
            $filter['offset'] = $offset;
            $filter['limit'] = $post_per_page;

            //
            $order_by = $this->MY_get('order_by', '');
            if ($order_by != '') {
                $order_by = [
                    $this->table . '.' . $order_by => 'DESC',
                ];
            }
            // với phần q.cáo sẽ sắp xếp theo nhóm và tên cho dễ xem
            else if ($this->post_type == PostType::ADS) {
                $order_by = [
                    $this->table . '.category_primary_id'  => 'ASC',
                    $this->table . '.post_name'  => 'DESC',
                ];
            }
            // mặc định sắp xếp theo stt và thời gian tạo
            else {
                $order_by = [
                    $this->table . '.menu_order' => 'DESC',
                    $this->table . '.time_order' => 'DESC',
                    $this->table . '.ID' => 'DESC',
                ];
            }
            $filter['order_by'] = $order_by;
            $data = $this->base_model->select('*', $this->table, $where, $filter);

            //
            $data = $this->post_model->list_meta_post($data);
            //print_r( $data );

            // xử lý dữ liệu cho angularjs
            foreach ($data as $k => $v) {
                // không cần hiển thị nội dung
                $v['post_content'] = '';
                $v['post_excerpt'] = '';
                //print_r($v);
                //continue;

                // lấy 1 số dữ liệu khác gán vào, để angularjs chỉ việc hiển thị
                $v['admin_permalink'] = $this->post_model->get_admin_permalink($this->post_type, $v['ID'], $this->controller_slug);
                if ($v['post_type'] == PostType::ORDER) {
                    $v['the_permalink'] = '#';
                } else {
                    $v['the_permalink'] = $this->post_model->get_post_permalink($v);
                }
                if (isset($v['post_meta'])) {
                    $v['thumbnail'] = $this->post_model->get_list_thumbnail($v['post_meta']);
                    $v['main_category_key'] = $this->post_model->return_meta_post($v['post_meta'], $this->main_category_key);
                } else {
                    $v['thumbnail'] = '';
                    $v['main_category_key'] = '';
                }

                //
                //print_r( $v );

                //
                $data[$k] = $v;
            }
        } else {
            $data = [];
            $pagination = '';
        }

        //
        $this->teamplate_admin['content'] = view(
            'admin/' . $this->list_view_path . '/list',
            array(
                'for_action' => $for_action,
                'by_post_status' => $by_post_status,
                'post_status' => $post_status,
                'by_keyword' => $by_keyword,
                'by_term_id' => $by_term_id,
                'by_user_id' => $by_user_id,
                'controller_slug' => $this->controller_slug,
                'pagination' => $pagination,
                'totalThread' => $totalThread,
                'main_category_key' => $this->main_category_key,
                'data' => $data,
                'taxonomy' => $this->taxonomy,
                'post_type' => $this->post_type,
                'name_type' => $this->name_type,
                'post_arr_status' => $this->post_arr_status,
                //'list_view_path' => $this->list_view_path,
                'list_table_path' => $this->list_table_path,
            )
        );
        //return $this->teamplate_admin[ 'content' ];
        return view('admin/admin_teamplate', $this->teamplate_admin);
    }

    public function add($ops = [])
    {
        $id = $this->MY_get('id', 0);
        $auto_update_module = $this->MY_get('auto_update_module', 0);

        //
        if (!empty($this->MY_post('data'))) {
            //die( __CLASS__ . ':' . __LINE__ );
            // nếu là nhân bản
            if ($this->MY_post('is_duplicate', 0) * 1 > 0) {
                //print_r( $_POST );

                // select dữ liệu từ 1 bảng bất kỳ
                $dup_data = $this->post_model->select_post($id, [
                    'post_type' => $this->post_type,
                ]);
                //print_r( $dup_data );
                //die(__CLASS__.':'.__LINE__);

                // đổi lại tiêu đề để tránh trùng lặp
                if (isset($dup_data['post_title'])) {
                    $duplicate_title = explode('- Duplicate', $dup_data['post_title']);
                    $dup_data['post_title'] = trim($duplicate_title[0]) . ' - Duplicate ' . date('Ymd-His');
                }
                $dup_data['post_name'] = '';
                $dup_data['post_shorttitle'] = '';
                $dup_data['ID'] = 0;
                unset($dup_data['ID']);
                //print_r($dup_data);

                // -> bỏ ID đi
                $id = 0;
                //die( __CLASS__ . ':' . __LINE__ );

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

        // edit
        $url_next_post = '';
        $prev_post = [];
        $next_post = [];
        if ($id > 0) {
            // select dữ liệu từ 1 bảng bất kỳ
            $data = $this->post_model->select_post($id, [
                'post_type' => $this->post_type,
                //'lang_key' => $this->lang_key,
            ]);
            if (empty($data)) {
                die('post not found!' . __CLASS__ . ':' . __LINE__);
            }
            //print_r($data);

            // nếu ngôn ngữ của post không đúng với ngôn ngữ đang hiển thị
            $clone_lang = $this->MY_get('clone_lang', '');
            if ($clone_lang != '' && $clone_lang != $data['lang_key']) {
                //die(__CLASS__ . ':' . __LINE__);
                // ngôn ngữ hiện tại có cha -> chuyển đến bản ghi cha
                if ($data['lang_parent'] > 0) {
                    //die(__CLASS__ . ':' . __LINE__);
                    $this->redirectLanguage($data, $data['lang_parent']);
                }
                // nếu không có cha
                else {
                    //die(__CLASS__ . ':' . __LINE__);
                    // tìm bản ghi con
                    $child_data = $this->post_model->select_post(0, [
                        'post_type' => $this->post_type,
                        'lang_key' => $clone_lang,
                        'lang_parent' => $data['ID'],
                    ]);
                    //print_r($child_data);
                    //die(__CLASS__ . ':' . __LINE__);

                    // nếu không có thì báo lỗi hiển thị
                    if (empty($child_data)) {
                        //die(__CLASS__ . ':' . __LINE__);
                        // nếu bài viết hiện tại đang là ngôn ngữ mặc định
                        //if (isset($_GET['lang_duplicate']) && $data['lang_key'] == LanguageCost::default_lang()) {
                        // nhân bản cho ngôn ngữ phụ
                        //print_r($data);
                        // nhân bản data
                        $dup_data = $data;
                        $dup_data['lang_key'] = $clone_lang;
                        $dup_data['lang_parent'] = $data['ID'];
                        $dup_data['post_permalink'] = '';
                        $dup_data['updated_permalink'] = 0;
                        // xóa phần ID để tránh xung đột primary key
                        $dup_data['ID'] = 0;
                        unset($dup_data['ID']);
                        $post_meta = $dup_data['post_meta'];
                        unset($dup_data['post_meta']);

                        //
                        //print_r($dup_data);
                        //die(__CLASS__ . ':' . __LINE__);

                        //
                        $result_id = $this->post_model->insert_post($dup_data, $post_meta);
                        if (is_array($result_id) && isset($result_id['error'])) {
                            die($result_id['error'] . ':' . __CLASS__ . ':' . __LINE__);
                        }
                        //echo $result_id;
                        $redirect_to = $this->buildAdminPermalink($result_id);
                        //die($redirect_to);

                        // sau đó redirect tới
                        $this->MY_redirect($redirect_to, 301);
                        die(__CLASS__ . ':' . __LINE__);
                        //}

                        // thay đổi file view sang file thông báo tạo ngôn ngữ mới
                        //$file_view = 'clone_lang';
                        // cố định thư mục chứa view
                        //$this->add_view_path = 'posts';

                        // mặc định hiển thị thông báo lỗi cho việc lấy dữ liệu
                        //die('post not found because data_lang_key(' . $data['lang_key'] . ') != this_lang_key(' . $this->lang_key . ')');
                    } else {
                        $this->redirectLanguage($child_data, $child_data['ID']);
                    }
                }
            }

            // tự động cập nhật lại slug khi nhân bản
            if (
                // url vẫn còn duplicate
                strstr($data['post_name'], '-duplicate-') == true &&
                // tiêu đề không còn Duplicate
                strstr($data['post_title'], ' - Duplicate ') == false
            ) {
                //die(__CLASS__ . ':' . __LINE__);
                //echo 'bbbbbbbbbbbbb';

                //
                $duplicate_name = $this->base_model->_eb_non_mark_seo($data['post_title']);
                if ($this->post_type == PostType::ADS) {
                    // với mục q.cáo -> không dùng slug nên tự thêm id vào slug để tránh trùng lặp
                    $duplicate_name .= '-' . $id;
                }

                //
                $result_update = $this->post_model->update_post($id, [
                    //'post_title' => $data['post_title'],
                    'post_name' => $duplicate_name
                ], [
                    'post_type' => $this->post_type,
                ]);

                // nếu có lỗi thì thông báo lỗi
                if ($result_update !== true && is_array($result_update) && isset($result_update['error'])) {
                    //die(__CLASS__ . ':' . __LINE__);
                    die($result_update['error']);
                    //$this->base_model->alert($result_update['error'], 'error');
                }

                // lấy data mới -> sau khi update
                $new_data = $this->post_model->select_post($id, [
                    'post_type' => $this->post_type,
                ]);
                //print_r($new_data);
                // cập nhật lại slug luôn vào ngay
                $this->post_model->update_post_permalink($new_data);

                //
                //$this->MY_redirect(DYNAMIC_BASE_URL . ltrim($_SERVER['REQUEST_URI'], '/'), 301);
                //$this->MY_redirect($this->post_model->get_admin_permalink($this->post_type, $id, $this->controller_slug), 301);
                $this->MY_redirect($this->buildAdminPermalink($id), 301);
            }

            // lấy bài tiếp theo để auto next
            if ($auto_update_module > 0) {
                //echo __CLASS__ . ':' . __LINE__ . '<br>' . PHP_EOL;

                //
                $url_next_post = $this->action_update_module($id);
            } else {
                // lấy các bài khác cùng nhóm để xử lý cho tiện -> nhiều khi muốn sửa thì sửa luôn
                $prev_post = $this->post_model->select_post(0, [
                    'ID <' => $data['ID'],
                    'post_type' => $this->post_type,
                    'lang_key' => $this->lang_key,
                ], [
                    'where_in' => array(
                        'post_status' => array(
                            PostType::DRAFT,
                            PostType::PUBLICITY,
                            PostType::PENDING,
                        )
                    ),
                    'order_by' => array(
                        'menu_order' => 'DESC',
                        'ID' => 'ASC'
                    ),
                    //'show_query' => 1,
                    'limit' => 5,
                ], 'ID, post_title, post_name');
                //print_r($prev_post);

                //
                $next_post = $this->post_model->select_post(0, [
                    'ID >' => $data['ID'],
                    'post_type' => $this->post_type,
                    'lang_key' => $this->lang_key,
                ], [
                    'where_in' => array(
                        'post_status' => array(
                            PostType::DRAFT,
                            PostType::PUBLICITY,
                            PostType::PENDING,
                        )
                    ),
                    'order_by' => array(
                        'menu_order' => 'DESC',
                        'ID' => 'ASC'
                    ),
                    //'show_query' => 1,
                    'limit' => 5,
                ], 'ID, post_title, post_name');
                //print_r($next_post);
            }
        }
        // add
        else {
            $data = $this->base_model->default_data($this->table);
            $data['post_meta'] = [];
        }
        //print_r( $this->session_data );
        //print_r( $data );


        //
        $post_cat = '';
        $post_tags = '';
        $parent_post = [];
        // lấy danh sách các trang để chọn bài cha
        if ($this->post_type == PostType::PAGE) {
            // các kiểu điều kiện where
            $where = [
                //$this->table . '.post_status !=' => PostType::DELETED,
                $this->table . '.post_type' => $this->post_type,
                $this->table . '.lang_key' => $this->lang_key
            ];

            $filter = [
                'where_in' => array(
                    $this->table . '.post_status' => array(
                        PostType::DRAFT,
                        PostType::PUBLICITY,
                        PostType::PENDING,
                    )
                ),
                'where_not_in' => array(
                    $this->table . '.ID' => array(
                        $id
                    )
                ),
                'order_by' => array(
                    $this->table . '.menu_order' => 'DESC',
                    $this->table . '.time_order' => 'DESC',
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 0,
                //'limit' => $post_per_page

            ];
            $parent_post = $this->base_model->select($this->table . '.ID, ' . $this->table . '.post_title', $this->table, $where, $filter);
            //print_r( $parent_post );
        }
        // lấy danh sách các nhóm để add cho post
        else {
            //$post_cat = $this->term_model->get_all_taxonomy( $this->taxonomy, 0, [ 'get_child' => 1 ], $this->taxonomy . '_get_child' );
            $post_cat = $this->taxonomy;
            if ($this->tags != '') {
                //$post_tags = $this->term_model->get_all_taxonomy( $this->tags, 0, [ 'get_child' => 1 ], $this->tags . '_get_child' );
                $post_tags = $this->tags;
            }
        }

        //
        if ($this->debug_enable === true) {
            echo '<!-- ';
            print_r($data);
            echo ' -->';
        }

        //
        //echo ADMIN_ROOT_VIEWS;

        //
        $this->teamplate_admin['content'] = view(
            'admin/' . $this->add_view_path . '/' . $file_view,
            array(
                'controller_slug' => $this->controller_slug,
                'lang_key' => $this->lang_key,
                'auto_update_module' => $auto_update_module,
                'url_next_post' => $url_next_post,
                'prev_post' => $prev_post,
                'next_post' => $next_post,
                'post_cat' => $post_cat,
                'post_tags' => $post_tags,
                'parent_post' => $parent_post,
                'quick_menu_list' => [],
                'data' => $data,
                'post_lang' => ($data['lang_key'] != '' ? LanguageCost::typeList($data['lang_key']) : ''),
                'meta_detault' => PostType::meta_default($this->post_type),
                'post_arr_status' => $this->post_arr_status,
                'taxonomy' => $this->taxonomy,
                'tags' => $this->tags,
                'post_type' => $this->post_type,
                'name_type' => $this->name_type,
                'preview_url' => $this->MY_get('preview_url', ''),
                'preview_offset_top' => $this->MY_get('preview_offset_top', ''),
                // mảng tham số tùy chỉnh dành cho các custom post type
                'meta_custom_type' => [],
                'meta_custom_desc' => [],
            )
        );
        return view('admin/admin_teamplate', $this->teamplate_admin);
    }

    protected function add_new($data = NULL)
    {
        if ($data === NULL) {
            $data = $this->MY_post('data');
        }
        $data['post_type'] = $this->post_type;
        //print_r( $data );
        //die( __CLASS__ . ':' . __LINE__ );

        //
        $result_id = $this->post_model->insert_post($data, [], $this->post_type == PostType::ADS ? false : true);
        if (is_array($result_id) && isset($result_id['error'])) {
            $this->base_model->alert($result_id['error'], 'error');
        }

        //
        if ($result_id > 0) {
            $this->base_model->alert('', $this->buildAdminPermalink($result_id));
        }
        $this->base_model->alert('Lỗi tạo ' . $this->name_type . ' mới', 'error');
    }

    protected function updating($id)
    {
        $data = $this->MY_post('data');
        //print_r( $data );
        //print_r( $_POST );

        // nhận dữ liệu default từ javascript khởi tạo và truyền vào trong quá trình submit
        if (isset($data['default_post_data'])) {
            foreach ($data['default_post_data'] as $k => $v) {
                if (!isset($this->default_post_data[$k])) {
                    $this->default_post_data[$k] = '';
                }
            }
        }
        //print_r( $this->default_post_data );
        foreach ($this->default_post_data as $k => $v) {
            if (!isset($data[$k])) {
                $data[$k] = $v;
            }
        }

        // convert datetime to timestamp
        //print_r( $data );
        //print_r( $this->timestamp_post_data );
        foreach ($this->timestamp_post_data as $k => $v) {
            //echo $k . '<br>' . PHP_EOL;
            //echo $data[ $k ] . '<br>' . PHP_EOL;
            if (isset($data[$k]) && $data[$k] != '') {
                $data[$k] = strtotime($data[$k]);
            }
        }
        //print_r( $data );

        //
        $result_id = $this->post_model->update_post($id, $data, [
            'post_type' => $this->post_type,
        ]);

        // nếu có lỗi thì thông báo lỗi
        if ($result_id !== true && is_array($result_id) && isset($result_id['error'])) {
            $this->base_model->alert($result_id['error'], 'error');
        }

        // dọn dẹp cache liên quan đến post này -> reset cache
        $this->cleanup_cache($this->post_model->key_cache($id));
        //
        if (isset($data['post_title'])) {
            // bổ sung thêm xóa cache với menu
            if ($this->post_type == PostType::MENU || $this->post_type == PostType::HTML_MENU) {
                $post_name = $this->base_model->_eb_non_mark_seo($data['post_title']);
                //echo $post_name . '<br>' . PHP_EOL;
                $this->cleanup_cache('get_the_menu-' . $post_name);
            }
            // hoặc page
            else if ($this->post_type == PostType::PAGE) {
                $this->cleanup_cache('get_page-' . $data['post_name']);
            }
            // hoặc ads
            else if ($this->post_type == PostType::ADS) {
                $this->cleanup_cache('get_the_ads-');
            }
        }

        // xóa cache cho term liên quan
        if (isset($_POST['post_meta']) && isset($_POST['post_meta']['post_category'])) {
            foreach ($_POST['post_meta']['post_category'] as $v) {
                //echo $v . '<br>' . PHP_EOL;
                $this->cleanup_cache($this->term_model->key_cache($v));
            }
        }

        //
        return true;
    }

    protected function update($id)
    {
        $this->updating($id);

        //
        $data = $this->MY_post('data');
        //print_r($data);
        //print_r( $_POST );

        // nạp lại trang nếu có đổi slug duplicate
        if (
            // url vẫn còn duplicate
            isset($data['post_name']) && strstr($data['post_name'], '-duplicate-') == true &&
            // tiêu đề không còn Duplicate
            isset($data['post_title']) && strstr($data['post_title'], ' - Duplicate ') == false
        ) {
            // nạp lại trang
            //$this->base_model->alert('', DYNAMIC_BASE_URL . ltrim($_SERVER['REQUEST_URI'], '/'));
            //$this->base_model->alert('', $this->post_model->get_admin_permalink($this->post_type, $id, $this->controller_slug));
            $this->base_model->alert('', $this->buildAdminPermalink($id));
        } else {
            // so sánh url cũ và mới
            $old_postname = $this->MY_post('old_postname');
            //print_r($old_postname);

            // nếu có sự khác nhau
            if (isset($data['post_name']) && $old_postname != $data['post_name']) {
                // lấy data mới -> sau khi update
                $new_data = $this->post_model->select_post($id, [
                    'post_type' => $this->post_type,
                ]);
                //print_r($new_data);

                // -> lấy url mới -> thiết lập lại url ở fronend
                echo '<script>top.set_new_post_url("' . $this->post_model->update_post_permalink($new_data) . '", "' . $new_data['post_name'] . '");</script>';
            }
        }

        //
        echo '<script>top.after_update_post();</script>';

        //
        $this->base_model->alert('Cập nhật ' . $this->name_type . ' thành công');
    }

    // chuyển trang sau khi XÓA xong
    protected function after_delete_restore()
    {
        $for_redirect = base_url('admin/' . $this->controller_slug);
        $urlParams = [];

        //
        $page_num = $this->MY_get('page_num');
        if ($page_num != '') {
            $urlParams[] = 'page_num=' . $page_num;
        }

        //
        $post_status = $this->MY_get('post_status');
        if ($post_status != '') {
            $urlParams[] = 'post_status=' . $post_status;
        }

        //
        if (count($urlParams) > 0) {
            $for_redirect .= '?' . implode('&', $urlParams);
        }
        return $this->base_model->alert('', $for_redirect);
    }
    protected function done_delete_restore($id)
    {
        die('<script>top.done_delete_restore(' . $id . ', "' . base_url('admin/' . $this->controller_slug) . '");</script>');
    }
    protected function before_delete_restore($post_status)
    {
        $id = $this->MY_get('id', 0);

        //
        $data = [
            'post_status' => $post_status
        ];
        //print_r( $data );
        //die( __CLASS__ . ':' . __LINE__ );

        // tiêu đề gắn thêm khi post bị xóa
        $post_trash_title = '___' . PostType::DELETED;

        //
        if ($post_status == PostType::DELETED) {
            $check_slug = $this->base_model->select('post_name', $this->table, [
                'ID' => $id,
                'post_status !=' => $post_status,
            ], [
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 2,
                'limit' => 1
            ]);
            //print_r( $check_slug );
            //var_dump( strpos( $check_slug[ 'post_name' ], '___' . $post_status ) );
            if (
                !empty($check_slug) && $check_slug['post_name'] != '' &&
                strpos($check_slug['post_name'], $post_trash_title) === false
            ) {
                $data['post_name'] = $this->base_model->_eb_non_mark_seo($check_slug['post_name']);
                $data['post_name'] .= $post_trash_title;
            }
        }
        //print_r( $data );
        //die( __CLASS__ . ':' . __LINE__ );

        $update = $this->post_model->update_post($id, $data, [
            'post_type' => $this->post_type,
        ]);
        //print_r( $update );
        //die( __CLASS__ . ':' . __LINE__ );

        // nếu update thành công -> gửi lệnh javascript để ẩn bài viết bằng javascript
        if ($update === true) {
            if ($post_status == PostType::REMOVED && ALLOW_USING_MYSQL_DELETE === true) {
                return $update;
            }
            return $this->done_delete_restore($id);
        }
        // không thì nạp lại cả trang để kiểm tra cho chắc chắn
        return $this->after_delete_restore();
    }

    // xóa (tạm ẩn) 1 bản ghi
    public function delete()
    {
        return $this->before_delete_restore(PostType::DELETED);
    }

    // phục hồi 1 bản ghi
    public function restore()
    {
        return $this->before_delete_restore(PostType::DRAFT);
    }

    // xóa hoàn toàn 1 bản ghi
    protected function before_remove()
    {
        $id = $this->MY_get('id', 0);

        // xem bản ghi này có được đánh dấu là XÓA không
        $data = $this->base_model->select(
            '*',
            $this->post_model->table,
            [
                'ID' => $id,
                'post_status' => PostType::DELETED,
                'post_type' => $this->post_type,
            ],
            array(
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 2,
                'limit' => 1
            )
        );

        //
        if (empty($data)) {
            $this->base_model->alert('Không xác định được bản ghi cần XÓA', 'error');
        }
        return $data;
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
            $result = $this->before_delete_restore(PostType::REMOVED);
        }

        //
        return $result;
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
        // XÓA relationships
        $result = $this->base_model->delete_multiple($this->term_model->relaTable, [
            // WHERE
            //'t2.post_status' => PostType::REMOVED,

        ], [
            /*
                'left_join' => array(
                $this->post_model->table . ' AS t2' => $this->term_model->relaTable . '.object_id = t2.ID'
                ),
                */
            'where_in' => array(
                'object_id' => $ids
            ),
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
        ]);
        //die( __CLASS__ . ':' . __LINE__ );

        // XÓA dữ liệu chính
        $result = $this->base_model->delete_multiple($this->post_model->metaTable, [
            // WHERE
            //'t2.post_status' => PostType::REMOVED,

        ], [
            /*
                'left_join' => array(
                $this->post_model->table . ' AS t2' => $this->post_model->metaTable . '.post_id = t2.ID'
                ),
                */
            'where_in' => array(
                'post_id' => $ids
            ),
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
        ]);

        //
        $this->base_model->delete_multiple($this->post_model->table, [
            // WHERE
            //'post_status' => PostType::REMOVED,

        ], [
            'where_in' => array(
                'ID' => $ids
            ),
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
        ]);

        //
        return $result;
    }

    //
    public function before_all_delete_restore($post_status)
    {
        $ids = $this->get_ids();

        //
        $update = $this->base_model->update_multiple($this->table, [
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
        ]);

        // nếu update thành công -> gửi lệnh javascript để ẩn bài viết bằng javascript
        if ($update === true && $post_status == PostType::REMOVED && ALLOW_USING_MYSQL_DELETE === true) {
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
        return $this->before_all_delete_restore(PostType::DELETED);
    }

    // chức năng restore nhiều bản ghi 1 lúc
    public function restore_all()
    {
        return $this->before_all_delete_restore(PostType::DRAFT);
    }

    // chức năng remove nhiều bản ghi 1 lúc
    public function remove_all()
    {
        // nếu có thuộc tính cho phép xóa hoàn toàn dữ liệu thì tiến hành xóa
        if (ALLOW_USING_MYSQL_DELETE === true) {
            $result = $this->delete_remove();
        } else {
            $result = $this->before_all_delete_restore(PostType::REMOVED);
        }

        //
        $this->result_json_type([
            'code' => __LINE__,
            'result' => $result,
            //'ids' => $ids,

        ]);
    }

    //
    private function createdThumbnail($imagePath)
    {
        $listSizeThumb = $this->config->item('list_thumbnail');
        $listThumbFolder = $this->config->item('thumbnail_folder');

        for ($i = 0; $i < count($listThumbFolder); $i++) {

            $pimageFullPath = $imagePath; // đg dẫn full path

            $config_manip = array(
                'image_library' => 'gd2',
                'source_image' => $pimageFullPath,
                'new_image' => $this->config->item('base_path') . '/Images/' . $listThumbFolder[$i],
                'maintain_ratio' => TRUE,
                'width' => $listSizeThumb[$i]['width'],
                'height' => $listSizeThumb[$i]['height'],
            );

            $this->load->library('image_lib');
            $this->image_lib->initialize($config_manip);

            if (!$this->image_lib->resize()) {
                echo $this->image_lib->display_errors();
                die(' lỗi resize');
            }
            $this->image_lib->clear();
        }
    }

    // chức năng tự động cập nhật lại toàn bộ bài viết mỗi khi có cập nhật mới và cần auto submit
    private function action_update_module($id = 0)
    {
        $where = [
            // các kiểu điều kiện where
            'post_status' => PostType::PUBLICITY,
            'post_type' => $this->post_type,
        ];
        if ($id > 0) {
            $where['ID <'] = $id;
        }

        //
        $data = $this->base_model->select(
            '*',
            $this->post_model->table,
            $where,
            array(
                'order_by' => array(
                    'ID' => 'DESC'
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 2,
                'limit' => 1
            )
        );
        //print_r( $data );

        //
        if (empty($data)) {
            if ($id > 0) {
                return '';
            }
            echo __FUNCTION__ . '! All done.';
            return false;
        }

        // lấy link sửa bài viết trong admin
        $admin_permalink = $this->post_model->get_admin_permalink($data['post_type'], $data['ID'], $this->controller_slug);
        //echo $admin_permalink . '<br>' . PHP_EOL;

        // thêm tham số tự động submit
        $admin_permalink .= '&auto_update_module=1';
        //echo $admin_permalink . '<br>' . PHP_EOL;

        //
        if ($id > 0) {
            return $admin_permalink;
        }
        $this->MY_redirect($admin_permalink, 301);
    }

    // chuyển đến bản ghi dựa theo ngôn ngữ đang xem
    protected function redirectLanguage($data, $id)
    {
        // xác định url cha
        $redirect_to = $this->buildAdminPermalink($id, $data['post_type']);
        //die($redirect_to);

        // sau đó redirect tới
        $this->MY_redirect($redirect_to, 301);
        die(__CLASS__ . ':' . __LINE__);
    }

    // trả về URL chỉnh sửa post cho admin
    protected function buildAdminPermalink($id, $post_type = '')
    {
        if ($post_type == '') {
            $post_type = $this->post_type;
        }

        //
        return $this->post_model->get_admin_permalink($post_type, $id, $this->controller_slug) . $this->get_preview_url();
    }
}
