<?php

namespace App\Controllers\Admin;

// Libraries
use App\Libraries\MediaType;

//
class Uploads extends Admin
{
    protected $post_type = MediaType::MEDIA;
    protected $name_type = '';
    protected $controller_slug = 'uploads';

    // định dạng file được phép upload
    public $allow_image_type = MediaType::IMAGE_MIME_TYPE;
    public $allow_media_type = MediaType::ALLOW_MIME_TYPE;

    public function __construct()
    {
        parent::__construct();

        // kiểm tra quyền truy cập của tài khoản hiện tại
        $this->check_permision(__CLASS__);
    }

    public function index($url = '')
    {
        //print_r( $_POST );
        //print_r( $this->MY_post( 'data' ) );
        if (!empty($this->MY_post('data'))) {
            $this->upload();
        }

        //
        $this->sync_no_parent();

        //
        $post_per_page = 36;

        // các kiểu điều kiện where
        $where = [
            'post_status !=' => MediaType::DELETED,
        ];

        // tìm kiếm theo từ khóa nhập vào
        $by_keyword = $this->MY_get('s');
        $where_or_like = [];
        // URL cho phân trang tìm kiếm
        $urlPartPage = 'admin/' . $this->controller_slug;
        $urlParams = [];

        // loại bớt các tham số trong URL
        //print_r( $_GET );
        $arr_deny_params = [
            'post_type',
            's',
            'page_num',
            'mode',
            'attachment-filter',
            'm',
        ];
        $hiddenSearchForm = [];
        foreach ($_GET as $k => $v) {
            if (in_array($k, $arr_deny_params)) {
                continue;
            }
            $urlParams[] = $k . '=' . $v;
            $hiddenSearchForm[$k] = $v;
        }

        //
        if ($by_keyword != '') {
            $urlParams[] = 's=' . $by_keyword;

            //
            $by_like = $this->base_model->_eb_non_mark_seo($by_keyword);
            // tối thiểu từ 1 ký tự trở lên mới kích hoạt tìm kiếm
            if (strlen($by_like) > 0) {
                //var_dump( strlen( $by_like ) );
                $where_or_like = [
                    //'ID' => $by_like,
                    'post_name' => $by_like,
                    'post_title' => $by_keyword,
                ];
            }
        }

        //
        $where_like = [];
        $where_like_after = [];

        //
        $alow_mime_type = [
            'image' => 'Hình ảnh',
            'audio' => 'Audio',
            'video' => 'Video',
        ];

        // lọc theo định dạng file
        $attachment_filter = $this->MY_get('attachment-filter', '');
        //echo 'attachment filter: ' . $attachment_filter . '<br>' . PHP_EOL;
        if ($attachment_filter != '' && isset($alow_mime_type[$attachment_filter])) {
            $urlParams[] = 'attachment-filter=' . $attachment_filter;
            $where_like_after['post_mime_type'] = $attachment_filter;
        }

        // lọc theo tháng upload
        $month_filter = $this->MY_get('m', '');
        //echo 'month filter: ' . $month_filter . '<br>' . PHP_EOL;
        if ($month_filter != '') {
            $urlParams[] = 'm=' . $month_filter;

            //
            //$by_post_date = $month_filter . '-01 00:00:00';
            $by_post_date = $month_filter . '-01';

            //
            $where['post_date >='] = $by_post_date;
            $where['post_date <'] = date('Y-m-d', strtotime('+1 month', strtotime($by_post_date)));
        }

        //
        $mode = $this->MY_get('mode', 'grid');
        if ($mode != '') {
            $urlParams[] = 'mode=' . $mode;
        }

        //
        $filter = [
            'where_in' => array(
                'post_type' => array(
                    $this->post_type,
                    MediaType::WP_MEDIA,
                )
            ),
            'like_after' => $where_like_after,
            'like' => $where_like,
            'or_like' => $where_or_like,
            'order_by' => array(
                //'menu_order' => 'DESC',
                'ID' => 'DESC',
                //'post_date' => 'DESC',
                //'post_modified' => 'DESC',
            ),
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 0,
            'limit' => -1
        ];


        /*
         * phân trang
         */
        $totalThread = $this->base_model->select('COUNT(ID) AS c', 'posts', $where, $filter);
        //print_r( $totalThread );
        $totalThread = $totalThread[0]['c'];
        //print_r( $totalThread );

        if ($totalThread > 0) {
            $totalPage = ceil($totalThread / $post_per_page);
            if ($totalPage < 1) {
                $totalPage = 1;
            }
            $page_num = $this->MY_get('page_num', 1);
            //echo $totalPage . '<br>' . PHP_EOL;
            if ($page_num > $totalPage) {
                $page_num = $totalPage;
            } else if ($page_num < 1) {
                $page_num = 1;
            }
            //echo $totalThread . '<br>' . PHP_EOL;
            //echo $totalPage . '<br>' . PHP_EOL;
            $offset = ($page_num - 1) * $post_per_page;

            //
            $urlParams[] = 'page_num=';
            $urlPartPage .= '?' . implode('&', $urlParams);
            $pagination = $this->base_model->EBE_pagination($page_num, $totalPage, $urlPartPage, '');


            // select dữ liệu từ 1 bảng bất kỳ
            $filter['offset'] = $offset;
            $filter['limit'] = $post_per_page;
            $data = $this->base_model->select('*', 'posts', $where, $filter);

            //
            $data = $this->post_model->list_meta_post($data);
            //print_r($data);
        } else {
            $data = [];
            $pagination = '';
        }

        // lấy các ngày có ảnh để tạo bộ lọc
        $m_filter = $this->base_model->scache('upload_post_date_filter');
        //$m_filter = NULL;
        if ($m_filter === NULL) {
            $m_data = $this->base_model->select("DATE_FORMAT(`post_date`, '%Y-%m') as d", 'posts', [
                //
            ], [
                'where_in' => array(
                    'post_type' => array(
                        $this->post_type,
                        MediaType::WP_MEDIA,
                    )
                ),
                'group_by' => array(
                    'd',
                ),
                'order_by' => array(
                    'ID' => 'DESC'
                ),
                //'show_query' => 1,
                'limit' => -1
            ]);
            //print_r( $m_data );

            //
            $m_filter = [];
            foreach ($m_data as $v) {
                $m_filter[] = $v['d'];
            }

            //
            $this->base_model->scache('upload_post_date_filter', $m_filter, DAY);
        }
        //print_r( $m_filter );

        //
        $this->teamplate_admin['body_class'] = $this->body_class;

        //
        $this->teamplate_admin['content'] = view('admin/uploads/list', array(
            'attachment_filter' => $attachment_filter,
            'alow_mime_type' => $alow_mime_type,
            'm_filter' => $m_filter,
            'month_filter' => $month_filter,
            'by_keyword' => $by_keyword,
            'data' => $data,
            'hiddenSearchForm' => $hiddenSearchForm,
            'pagination' => $pagination,
            'totalThread' => $totalThread,
            'mode' => $mode,
            //'taxonomy' => $this->taxonomy,
            'post_type' => $this->post_type,
            'controller_slug' => $this->controller_slug,
            'name_type' => MediaType::typeList($this->post_type),
        ));
        return view('admin/admin_teamplate', $this->teamplate_admin);
    }

    protected function upload($key = 'upload_image')
    {
        // gọi tới function upload ảnh thôi
        $this->media_upload(false);
        //die(__CLASS__ . ':' . __LINE__);

        // -> gọi hàm này để nó nạp lại trang cha
        $this->alert('');
    }

    public function delete()
    {
        $id = $this->MY_get('id', 0);
        $id *= 1;
        if ($id <= 0) {
            return false;
        }

        //
        $data = $this->post_model->select_post($id, [
            'post_type' => $this->post_type,
        ]);
        if (empty($data)) {
            $data = $this->post_model->select_post($id, [
                'post_type' => MediaType::WP_MEDIA,
            ]);
        }
        $update = false;
        if (!empty($data)) {
            //print_r( $data );

            //
            if ($data['post_type'] == MediaType::WP_MEDIA) {
                $secondes_path = MediaType::WP_MEDIA_URI;
            } else {
                $secondes_path = MediaType::MEDIA_PATH;
            }
            $secondes_path = PUBLIC_HTML_PATH . $secondes_path;
            //echo $secondes_path . '<br>' . PHP_EOL;
            //die( __CLASS__ . ':' . __LINE__ );

            //
            $delete_file = [];
            // Don't attempt to unserialize data that wasn't serialized going in.
            if (isset($data['post_meta']['_wp_attachment_metadata']) && $data['post_meta']['_wp_attachment_metadata'] != '') {
                //if ( is_serialized( $v[ 'post_meta' ][ '_wp_attachment_metadata' ] ) ) {
                $attachment_metadata = unserialize($data['post_meta']['_wp_attachment_metadata']);
                //}

                //print_r( $attachment_metadata );
                if (empty($attachment_metadata)) {
                    return '';
                }
                //print_r( $attachment_metadata );

                $src = $attachment_metadata['file'];
                $delete_file[] = $src;
                if (isset($attachment_metadata['sizes'])) {
                    foreach ($attachment_metadata['sizes'] as $size_name => $size) {
                        $delete_file[] = dirname($src) . '/' . $size['file'];
                    }
                }
            } else if (isset($data['post_meta']['_wp_attached_file']) && $data['post_meta']['_wp_attached_file'] != '') {
                $delete_file[] = $data['post_meta']['_wp_attached_file'];
            }
            //print_r( $delete_file );
            foreach ($delete_file as $v) {
                $remove_file = $secondes_path . $v;

                //
                if (file_exists($remove_file)) {
                    //echo $remove_file . '<br>' . PHP_EOL;
                    $this->MY_unlink($remove_file) or die('ERROR remove upload file: ' . $v);
                }
            }
            //die( 'delete media' );

            //
            $update = $this->post_model->update_post($data['ID'], [
                'post_status' => MediaType::DELETED
            ], [
                'post_type' => $data['post_type'],
            ]);
        }

        //
        if ($update === true) {
            $this->done_delete_restore($id);
        }
        $this->alert('');
    }
    protected function done_delete_restore($id)
    {
        die('<script>top.done_delete_restore(' . $id . ', "' . base_url('admin/' . $this->controller_slug) . '");</script>');
    }

    protected function alert($m, $url = '')
    {
        if ($url == '') {
            $url = base_url('admin/uploads');
            $uri_quick_upload = [];
            foreach ($_GET as $k => $v) {
                if ($k != 'id') {
                    $uri_quick_upload[] = $k . '=' . $v;
                }
            }
            if (!empty($uri_quick_upload)) {
                $url .= '?' . implode('&', $uri_quick_upload);
            }
            //die( $url );
        }

        //
        //$this->base_model->alert( '', $url );
        die('<script>parent.window.location = "' . $url . '";</script>');
    }

    // tối ưu hóa ảnh -> nhiều quả ảnh up lên nhưng quá nặng -> cần tối ưu hóa lại chút
    public function optimize()
    {
        $post_per_page = 50;

        // các kiểu điều kiện where
        $where = [
            //'post_parent' => 688, // TEST
            'post_status !=' => MediaType::DELETED,
        ];

        // URL cho phân trang tìm kiếm
        $urlPartPage = 'admin/' . $this->controller_slug . '/optimize';

        //
        $filter = [
            'where_in' => array(
                'post_type' => array(
                    $this->post_type,
                    MediaType::WP_MEDIA,
                )
            ),
            'order_by' => array(
                //'menu_order' => 'DESC',
                'ID' => 'DESC',
                //'post_date' => 'DESC',
                //'post_modified' => 'DESC',
            ),
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 0,
            //'limit' => $post_per_page
        ];


        /*
         * phân trang
         */
        $totalThread = $this->base_model->select('COUNT(ID) AS c', 'posts', $where, $filter);
        //print_r( $totalThread );
        $totalThread = $totalThread[0]['c'];
        //print_r( $totalThread );

        if ($totalThread > 0) {
            $totalPage = ceil($totalThread / $post_per_page);
            if ($totalPage < 1) {
                $totalPage = 1;
            }
            $page_num = $this->MY_get('page_num', 1);
            //echo $totalPage . '<br>' . PHP_EOL;
            if ($page_num > $totalPage) {
                $page_num = $totalPage;
            } else if ($page_num < 1) {
                $page_num = 1;
            }
            //echo $totalThread . '<br>' . PHP_EOL;
            //echo $totalPage . '<br>' . PHP_EOL;
            $offset = ($page_num - 1) * $post_per_page;

            //
            $pagination = $this->base_model->EBE_pagination($page_num, $totalPage, $urlPartPage, '?page_num=');


            // select dữ liệu từ 1 bảng bất kỳ
            $filter['offset'] = $offset;
            $filter['limit'] = $post_per_page;
            $data = $this->base_model->select('*', 'posts', $where, $filter);

            //
            $data = $this->post_model->list_meta_post($data);
            //print_r( $data );
        } else {
            $data = [];
            $pagination = '';
        }

        //
        $this->teamplate_admin['body_class'] = $this->body_class;

        //
        $this->teamplate_admin['content'] = view('admin/uploads/optimize', array(
            'data' => $data,
            'pagination' => $pagination,
            'totalThread' => $totalThread,
            'totalPage' => $totalPage,
            'post_type' => $this->post_type,
            'controller_slug' => $this->controller_slug,
            'name_type' => MediaType::typeList($this->post_type),
        ));
        return view('admin/admin_teamplate', $this->teamplate_admin);
    }

    // tìm cha cho các ảnh không có parent
    private function sync_no_parent()
    {
        // daidq (2022-03-05): chưa có site để test nên tính năng này đang tạm dừng
        return false;

        //
        $data = $this->base_model->select('*', 'posts', [
            'post_type' => $this->post_type,
            'post_parent' => 0,
            'post_status' => MediaType::INHERIT,
        ], [
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'getNumRows' => 1,
            'offset' => $offset,
            'limit' => 5
        ]);
        //print_r( $data );
        //die( __CLASS__ . ':' . __LINE__ );
    }

    public function drop_upload()
    {
        //
        $img = $this->MY_post('img', '');
        if (empty($img)) {
            $this->result_json_type([
                'in' => __CLASS__,
                'code' => __LINE__,
                'error' => 'img EMPTY!'
            ]);
        }

        // tên file
        $file_name = $this->MY_post('file_name', '');
        if (empty($file_name)) {
            $this->result_json_type([
                'in' => __CLASS__,
                'code' => __LINE__,
                'error' => 'file name EMPTY!'
            ]);
        }

        // thời gian chỉnh sửa file
        $last_modified = $this->MY_post('last_modified', '');
        // tên file lấy theo thời gian chỉnh sửa -> nếu không có gì khác bọt thì khỏi upload lại
        //$format_modified = 'ymdHis';
        $format_modified = 'His';
        if (empty($last_modified)) {
            $last_modified = time();
            //$format_modified = 'ymdH';
        }

        // thêm ngày tháng năm vào tên file để tránh trùng lặp -> upload trong admin có insert db nên n gày tháng để sau
        $file_name .= '-' . date($format_modified, $last_modified);

        //
        $upload_root = PUBLIC_HTML_PATH . MediaType::MEDIA_PATH;
        //echo $upload_root . '<br>' . PHP_EOL;

        //
        $upload_path = $this->media_path(
            [
                date('Y'),
                date('m'),
            ],
            $upload_root
        );
        //echo $upload_path . '<br>' . PHP_EOL;

        //
        $file_type = 'jpg';
        if (strpos($img, 'data:image/png;') !== false) {
            $file_type = 'png';
            $img = str_replace('data:image/png;base64,', '', $img);
        } else {
            $img = str_replace('data:image/jpeg;base64,', '', $img);
            $img = str_replace('data:image/jpg;base64,', '', $img);
        }
        $img = str_replace(' ', '+', $img);
        $file_path = $upload_path . $file_name . '.' . $file_type;

        //
        $success = 0;
        $mime_type = $file_type;
        $metadata = [];
        if (!file_exists($file_path)) {
            $success = $this->base_model->eb_create_file($file_path, base64_decode($img));

            // kiểm tra định dạng file -> chỉ chấp nhận định dạng jpeg
            $mime_type = mime_content_type($file_path);

            // nếu là file ảnh
            if (in_array($mime_type, $this->allow_image_type) || in_array($mime_type, $this->allow_media_type)) {
                // tiến hành tạo thumbnail, metadata -> insert vào db
                $metadata = $this->media_attachment_metadata($file_path, $file_type, $upload_path, $mime_type, $upload_root);
            }
            // các file khác thì không cho upload
            else {
                unlink($file_path);

                //
                $this->result_json_type([
                    'in' => __CLASS__,
                    'code' => __LINE__,
                    'mime_type' => $this->MY_post('mime_type', ''),
                    'error' => 'mime type not support! ' . $mime_type
                ]);
            }
        }

        // TEST
        $this->result_json_type([
            //'file_path' => $file_path,
            //'upload_path' => $upload_path,
            //'data' => $_POST,
            //'metadata' => $metadata,
            'mime_input_type' => $this->MY_post('mime_type', ''),
            'mime_type' => $mime_type,
            'success' => $success,
        ]);
    }
}
