<?php

namespace App\Controllers\Sadmin;

// Libraries
use App\Libraries\LanguageCost;
use App\Libraries\ConfigType;
use App\Libraries\DeletedStatus;
use App\Libraries\PHPMaillerSend;
use App\ThirdParty\TelegramBot;

//
class Config404s extends Sadmin
{
    protected $view_edit = 'edit';

    public function __construct()
    {
        parent::__construct();
    }


    public function index()
    {
        // 
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $link_id = $this->MY_post('link_id');
            $link_image = trim($this->MY_post('link_image'));

            // 
            if (!empty($link_image)) {
                $link_image = explode($_SERVER['HTTP_HOST'] . '/', $link_image);
                if (isset($link_image[1])) {
                    $link_image = '/' . $link_image[1];
                } else {
                    $link_image = $link_image[0];
                }
            }

            // 
            $msg_error = '';
            if (!empty($link_id)) {
                $result_id = $this->base_model->update_multiple('links', [
                    // SET
                    'link_image' => $link_image,
                ], [
                    // WHERE
                    'link_id' => $link_id,
                ], [
                    'debug_backtrace' => debug_backtrace()[1]['function'],
                    // hiển thị mã SQL để check
                    // 'show_query' => 1,
                    // trả về câu query để sử dụng cho mục đích khác
                    // 'get_query' => 1,
                    // mặc định sẽ remove các field không có trong bảng, nếu muốn bỏ qua chức năng này thì kích hoạt no_remove_field
                    // 'no_remove_field' => 1
                ]);

                if ($result_id !== false) {
                    $this->base_model->result_json_type([
                        'code' => __LINE__,
                        'msg' => 'Cập nhật thành công!',
                        'link_image' => $link_image,
                        'link_id' => $link_id,
                    ]);
                }
            } else {
                $msg_error = 'link_id không được để trống!';
            }

            // 
            $this->base_model->result_json_type([
                'code' => __LINE__,
                'error' => $msg_error,
            ]);
        }

        // dọn dẹp dữ liệu cũ
        $cleanup = $this->base_model->scache('cleanup_config404s');
        if ($cleanup === null) {
            $this->base_model->delete_multiple('links', [
                // WHERE
                'link_image' => '',
                'link_updated <' => date(EBE_DATETIME_FORMAT, strtotime('-1 month')),
            ], [
                // hiển thị mã SQL để check
                // 'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                // 'get_query' => 1,
            ]);

            // lưu cache -> ko dọn liên tục
            $this->base_model->scache('cleanup_config404s', date('r'), DAY);
        }

        //
        $post_per_page = 100;
        // URL cho phân trang
        $urlPartPage = 'sadmin/config404s?part_type=links';

        // 
        $where_like = [];
        $by_keyword = trim($this->MY_get('s'));
        if ($by_keyword != '') {
            $urlPartPage .= '&s=' . $by_keyword;

            $where_like = [
                'link_name' => $by_keyword,
            ];
        }

        // 
        $where = [
            // các kiểu điều kiện where
        ];

        // 
        $filter = [
            'like_before' => $where_like,
            // hiển thị mã SQL để check
            // 'show_query' => 1,
            // trả về câu query để sử dụng cho mục đích khác
            // 'get_query' => 1,
            // trả về COUNT(column_name) AS column_name
            // 'selectCount' => 'ID',
            // trả về tổng số bản ghi -> tương tự mysql num row
            // 'getNumRows' => 1,
            // 'offset' => 0,
            // 'limit' => $post_per_page,
        ];


        /**
         * phân trang
         */
        $totalThread = $this->base_model->select_count('link_id', 'links', $where, $filter);
        // echo $totalThread . '<br>' . PHP_EOL;

        // chạy vòng lặp gán nốt các thông số khác trên url vào phân trang
        $urlPartPage = $this->base_model->auto_add_params($urlPartPage);

        //
        if ($totalThread > 0) {
            $page_num = $this->MY_get('page_num', 1);

            $totalPage = ceil($totalThread / $post_per_page);
            if ($totalPage < 1) {
                $totalPage = 1;
            }
            // echo $totalPage . '<br>' . PHP_EOL;
            if ($page_num > $totalPage) {
                $page_num = $totalPage;
            } else if ($page_num < 1) {
                $page_num = 1;
            }
            // echo $totalThread . '<br>' . PHP_EOL;
            // echo $totalPage . '<br>' . PHP_EOL;
            $offset = ($page_num - 1) * $post_per_page;

            // chạy vòng lặp gán nốt các thông số khác trên url vào phân trang
            $urlPartPage = $this->base_model->auto_add_params($urlPartPage);

            //
            $pagination = $this->base_model->EBE_pagination($page_num, $totalPage, $urlPartPage, 'page_num=');


            // select dữ liệu từ 1 bảng bất kỳ
            // $filter['show_query'] = 1;
            $filter['offset'] = $offset;
            $filter['limit'] = $post_per_page;
            $filter['order_by'] = array(
                'link_id' => 'DESC',
            );

            // 
            $data = $this->base_model->select(
                [
                    // các trường cần lấy ra
                    'link_id',
                    'link_url',
                    'link_name',
                    'link_image',
                    'link_target',
                    'link_description',
                    'link_rel',
                    'link_updated',
                    'link_notes',
                ],
                'links',
                $where,
                $filter
            );
            // print_r($data);
        } else {
            $data = [];
            $pagination = '';
        }

        // 
        $this->teamplate_admin['content'] = view(
            'vadmin/config404s/' . $this->view_edit,
            array(
                // 'lang_key' => $this->lang_key,
                'data' => $data,
                'by_keyword' => $by_keyword,
                'pagination' => $pagination,
                'totalThread' => $totalThread,
            )
        );
        return view('vadmin/admin_teamplate', $this->teamplate_admin);
    }
}
