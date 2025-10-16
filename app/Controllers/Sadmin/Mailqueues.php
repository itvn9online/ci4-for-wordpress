<?php

namespace App\Controllers\Sadmin;

// Libraries
// use App\Libraries\DeletedStatus;
use App\Libraries\PostType;

//
class Mailqueues extends Sadmin
{
    // tham số dùng để thay đổi URL cho controller (nếu muốn)
    protected $controller_slug = 'mailqueues';
    // số bản ghi trên mỗi trang
    protected $post_per_page = 50;

    public function __construct($for_extends = false)
    {
        parent::__construct();

        // kiểm tra quyền truy cập của tài khoản hiện tại
        $this->check_permision(__CLASS__);
    }

    public function index()
    {
        return $this->lists();
    }
    public function lists($ops = [])
    {
        $mail_id = $this->MY_get('mail_id', 0);
        if ($mail_id > 0) {
            return $this->details($mail_id);
        }

        // URL cho các action dùng chung
        $for_action = '';
        // URL cho phân trang
        $urlPartPage = 'sadmin/' . $this->controller_slug . '?part_type=none';

        // các kiểu điều kiện where
        $where = [];

        //
        $by_is_deleted = $this->MY_get('is_deleted');
        $by_keyword = $this->MY_get('s');

        //
        if ($by_is_deleted != '') {
            $urlPartPage .= '&is_deleted=' . $by_is_deleted;
            $for_action .= '&is_deleted=' . $by_is_deleted;
            $where['status'] = $by_is_deleted;
        }

        // tìm kiếm theo từ khóa nhập vào
        if ($by_keyword != '') {
            $urlPartPage .= '&s=' . $by_keyword;
            $for_action .= '&s=' . $by_keyword;
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
                        'id' => $by_like,
                        'post_id' => $by_like,
                        'order_id' => $by_like,
                    ];
                } else {
                    // nếu có @ -> tìm theo email
                    if (strpos($by_keyword, '@') !== false) {
                        $like_before = explode('@', $by_keyword)[0];
                        if (!empty($like_before)) {
                            $where_or_like = [
                                'mailto' => explode('@', $by_keyword)[0],
                            ];
                        } else {
                            $where_or_like = [
                                'mailto' => $by_keyword,
                            ];
                        }
                    }
                    // còn lại thì có gì tìm hết
                    else {
                        $where_or_like = [
                            'title' => $by_keyword,
                        ];
                    }
                }
            }
        }

        $filter = [
            'or_like' => $where_or_like,
            // hiển thị mã SQL để check
            // 'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 0,
            //'limit' => $this->post_per_page
        ];


        /**
         * phân trang
         */
        $totalThread = $this->base_model->select_count('id', 'mail_queue', $where, $filter);
        // echo $totalThread . '<br>' . "\n";

        //
        if ($totalThread > 0) {
            $totalPage = ceil($totalThread / $this->post_per_page);
            if ($totalPage < 1) {
                $totalPage = 1;
            }
            $page_num = $this->MY_get('page_num', 1);
            //echo $totalPage . '<br>' . "\n";
            if ($page_num > $totalPage) {
                $page_num = $totalPage;
            } else if ($page_num < 1) {
                $page_num = 1;
            }
            $for_action .= $page_num > 1 ? '&page_num=' . $page_num : '';
            //echo $totalThread . '<br>' . "\n";
            //echo $totalPage . '<br>' . "\n";
            $offset = ($page_num - 1) * $this->post_per_page;

            //
            $pagination = $this->base_model->EBE_pagination($page_num, $totalPage, $urlPartPage, 'page_num=');


            // select dữ liệu từ 1 bảng bất kỳ
            $filter['order_by'] = [
                'id' => 'DESC',
            ];
            $filter['offset'] = $offset;
            $filter['limit'] = $this->post_per_page;

            //
            $data = $this->base_model->select('*', 'mail_queue', $where, $filter);
        } else {
            $data = [];
            $pagination = '';
        }

        // 
        $this->teamplate_admin['content'] = view('vadmin/mail_queues/list', array(
            'data' => $data,
            'pagination' => $pagination,
            'totalThread' => $totalThread,
            'for_action' => $for_action,
            'controller_slug' => $this->controller_slug,
        ));
        return view('vadmin/admin_teamplate', $this->teamplate_admin);
    }

    protected function details($mail_id)
    {
        //echo $mail_id . '<br>' . "\n";

        //
        $data = $this->base_model->select('*', 'mail_queue', [
            'id' => $mail_id,
        ], [
            // hiển thị mã SQL để check
            //'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            //'get_query' => 1,
            //'offset' => 0,
            'limit' => 1
        ]);
        // print_r($data);
        if (!empty($data)) {
            $data['created_at'] = date('Y-m-d H:i:s', $data['created_at']);
            if (!empty($data['updated_at'])) {
                $data['updated_at'] = date('Y-m-d H:i:s', $data['updated_at']);
            }
            if (!empty($data['sended_at'])) {
                $data['sended_at'] = date('Y-m-d H:i:s', $data['sended_at']);
            }
        }

        //
        if ($this->debug_enable === true) {
            echo '<!-- ';
            print_r($data);
            echo ' -->';
        }

        //
        $this->teamplate_admin['content'] = view('vadmin/mail_queues/details', array(
            'data' => $data,
            'controller_slug' => $this->controller_slug,
        ));
        return view('vadmin/admin_teamplate', $this->teamplate_admin);
    }
}
